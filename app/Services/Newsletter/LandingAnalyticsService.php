<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\DTO\Response\NewsletterAnalytics\LandingAnalyticsIngestResponseDTO;
use App\Interfaces\Newsletter\LandingAnalyticsServiceInterface;
use Config\Database;
use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use dcardenasl\Ci4ApiCore\Dto\SecurityContext;
use dcardenasl\Ci4ApiCore\Exceptions\ValidationException;
use dcardenasl\Ci4ApiCore\Repositories\RepositoryInterface;
use dcardenasl\Ci4ApiCore\Services\HandlesTransactions;

class LandingAnalyticsService implements LandingAnalyticsServiceInterface
{
    use HandlesTransactions;

    /**
     * @param RepositoryInterface<object> $projectRepository
     * @param RepositoryInterface<object> $sessionRepository
     * @param RepositoryInterface<object> $eventRepository
     * @param RepositoryInterface<object> $aggregateRepository
     */
    public function __construct(
        private readonly RepositoryInterface $projectRepository,
        private readonly RepositoryInterface $sessionRepository,
        private readonly RepositoryInterface $eventRepository,
        private readonly RepositoryInterface $aggregateRepository,
    ) {
    }

    public function ingest(DataTransferObjectInterface $dto, ?SecurityContext $context = null): DataTransferObjectInterface
    {
        return $this->wrapInTransaction(function () use ($dto) {
            $data = $dto->toArray();
            /** @var array<mixed> $events */
            $events = $data['events'] ?? [];

            if (!is_array($events) || empty($events)) {
                throw new ValidationException(lang('Api.validationFailed'), ['events' => lang('Validation.events_required')]);
            }

            $accepted = 0;
            $rejected = 0;
            $sessionsTouched = 0;
            $eventsInserted = 0;
            /** @var array<array<string, mixed>> $errors */
            $errors = [];

            foreach ($events as $index => $event) {
                if (!is_array($event)) {
                    $rejected++;
                    $errors[] = ['row' => (int) $index + 1, 'reason' => 'invalid_event'];
                    continue;
                }

                $normalized = $this->normalizeEvent($event);
                if (!empty($normalized['errors'])) {
                    $rejected++;
                    $errors[] = ['row' => (int) $index + 1, 'reason' => 'validation_failed', 'errors' => $normalized['errors']];
                    continue;
                }

                /** @var object|null $project */
                $project = $this->projectRepository->getModel()->where('project_key', $normalized['project_key'])->first();
                if (!is_object($project)) {
                    $rejected++;
                    $errors[] = ['row' => (int) $index + 1, 'reason' => 'project_not_found'];
                    continue;
                }

                $session = $this->upsertSession($project, $normalized);
                if ($session['touched']) {
                    $sessionsTouched++;
                }

                $eventId = $this->eventRepository->insert([
                    'analytics_session_id' => $normalized['analytics_session_id'],
                    'session_id' => $session['id'],
                    'project_id' => (int) $project->id,
                    'project_key' => (string) $project->project_key,
                    'event_name' => $normalized['event_name'],
                    'page_path' => $normalized['page_path'],
                    'section_key' => $normalized['section_key'],
                    'form_key' => $normalized['form_key'],
                    'occurred_at' => $normalized['occurred_at'],
                    'metadata' => !empty($normalized['metadata']) ? json_encode($normalized['metadata'], JSON_THROW_ON_ERROR) : null,
                ]);

                if (empty($eventId)) {
                    $rejected++;
                    $errors[] = ['row' => (int) $index + 1, 'reason' => 'persist_failed'];
                    continue;
                }

                $this->updateAggregate($project, $normalized, !$session['existing']);
                $accepted++;
                $eventsInserted++;
            }

            if ($accepted === 0) {
                /** @var array<string, list<string>|string> $errData */
                $errData = ['events' => lang('Validation.no_valid_events')];
                throw new ValidationException(lang('Api.validationFailed'), $errData);
            }

            return new LandingAnalyticsIngestResponseDTO(
                accepted: $accepted,
                rejected: $rejected,
                sessions_touched: $sessionsTouched,
                events_inserted: $eventsInserted,
                errors: $errors,
            );
        });
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function overview(array $filters = [], ?SecurityContext $context = null): array
    {
        return [
            'summary' => $this->getSessionSummary($filters),
            'funnel' => $this->buildFunnel($filters),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function funnel(array $filters = [], ?SecurityContext $context = null): array
    {
        return ['stages' => $this->buildFunnel($filters)];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function sessions(array $filters = [], ?SecurityContext $context = null): array
    {
        $db = Database::connect();
        $builder = $db->table('landing_analytics_sessions s');
        $this->applySessionFilters($builder, $filters);

        $total = (int) $builder->countAllResults(false);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($filters['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $builder->orderBy('s.started_at', 'DESC')->orderBy('s.id', 'DESC');
        $result = $builder->get($perPage, $offset);
        $rows = (is_object($result)) ? $result->getResultArray() : [];

        return [
            'items' => array_map([$this, 'formatSession'], $rows),
            'pagination' => ['total' => $total, 'page' => $page, 'per_page' => $perPage],
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function session(int $id, array $filters = [], ?SecurityContext $context = null): array
    {
        $db = Database::connect();
        $result = $db->table('landing_analytics_sessions')->where('id', $id)->get();
        $row = (is_object($result)) ? $result->getRowArray() : null;

        if (!is_array($row) || empty($row)) {
            return ['session' => null, 'events' => []];
        }

        $result = $db->table('landing_analytics_events')
            ->where('session_id', $id)
            ->orderBy('occurred_at', 'ASC')
            ->get();
        $events = (is_object($result)) ? $result->getResultArray() : [];

        return [
            'session' => $this->formatSession($row),
            'events' => array_map([$this, 'formatEvent'], $events),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function journey(int $subscriberId, array $filters = [], ?SecurityContext $context = null): array
    {
        $db = Database::connect();
        $result = $db->table('subscribers')->where('id', $subscriberId)->get();
        $subscriber = (is_object($result)) ? $result->getRowArray() : null;

        if (!is_array($subscriber) || empty($subscriber)) {
            return ['subscriber' => null, 'session' => null, 'events' => []];
        }

        $result = $db->table('landing_analytics_sessions')
            ->where('subscriber_id', $subscriberId)
            ->orderBy('started_at', 'DESC')
            ->get();
        $session = (is_object($result)) ? $result->getRowArray() : null;

        if (!is_array($session) || empty($session)) {
            return ['subscriber' => $subscriber, 'session' => null, 'events' => []];
        }

        $result = $db->table('landing_analytics_events')
            ->where('session_id', $session['id'])
            ->orderBy('occurred_at', 'ASC')
            ->get();
        $events = (is_object($result)) ? $result->getResultArray() : [];

        return [
            'subscriber' => $subscriber,
            'session' => $this->formatSession($session),
            'events' => array_map([$this, 'formatEvent'], $events),
        ];
    }

    /**
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    private function normalizeEvent(array $event): array
    {
        $errors = [];
        $eventName = trim((string) ($event['event_name'] ?? ''));
        $sessionId = trim((string) ($event['analytics_session_id'] ?? ''));
        $projectKey = trim((string) ($event['project_key'] ?? ''));

        if (empty($eventName)) {
            $errors['event_name'] = 'Required';
        }
        if (empty($sessionId)) {
            $errors['analytics_session_id'] = 'Required';
        }
        if (empty($projectKey)) {
            $errors['project_key'] = 'Required';
        }

        $occurredAt = $event['occurred_at'] ?? null;
        if ($occurredAt === null || !$this->isValidDate((string) $occurredAt)) {
            $errors['occurred_at'] = 'Invalid';
        }

        return [
            'analytics_session_id' => $sessionId,
            'project_key' => $projectKey,
            'event_name' => $eventName,
            'page_path' => trim((string) ($event['page_path'] ?? '')),
            'section_key' => trim((string) ($event['section_key'] ?? '')),
            'form_key' => trim((string) ($event['form_key'] ?? '')),
            'occurred_at' => (string) $occurredAt,
            'metadata' => is_array($event['metadata'] ?? null) ? $event['metadata'] : [],
            'errors' => $errors,
        ];
    }

    /**
     * @param object $project
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    private function upsertSession(object $project, array $event): array
    {
        $db = Database::connect();
        $result = $db->table('landing_analytics_sessions')
            ->where('analytics_session_id', $event['analytics_session_id'])
            ->where('project_id', (int) $project->id)
            ->get();
        $existing = (is_object($result)) ? $result->getRowArray() : null;

        if (is_array($existing) && !empty($existing)) {
            $db->table('landing_analytics_sessions')
                ->where('id', $existing['id'])
                ->update(['last_seen_at' => date('Y-m-d H:i:s')]);

            return ['id' => (int) $existing['id'], 'existing' => true, 'touched' => false];
        }

        $sessionId = $this->sessionRepository->insert([
            'analytics_session_id' => $event['analytics_session_id'],
            'project_id' => (int) $project->id,
            'project_key' => (string) $project->project_key,
            'visitor_locale' => $event['metadata']['locale'] ?? null,
            'referrer' => $event['metadata']['referrer'] ?? null,
            'page_path' => $event['page_path'],
            'started_at' => $event['occurred_at'],
            'last_seen_at' => $event['occurred_at'],
            'event_count' => 1,
        ]);

        return ['id' => $sessionId, 'existing' => false, 'touched' => true];
    }

    /**
     * @param object $project
     * @param array<string, mixed> $event
     * @param bool $isNew
     */
    private function updateAggregate(object $project, array $event, bool $isNew): void
    {
        $date = substr($event['occurred_at'], 0, 10);
        $db = Database::connect();

        $result = $db->table('landing_analytics_daily_aggregates')
            ->where('project_id', (int) $project->id)
            ->where('aggregate_date', $date)
            ->get();
        $agg = (is_object($result)) ? $result->getRowArray() : null;

        if (is_array($agg) && !empty($agg)) {
            $db->table('landing_analytics_daily_aggregates')
                ->where('id', $agg['id'])
                ->update(['sessions_count' => (int) ($agg['sessions_count'] ?? 0) + ($isNew ? 1 : 0)]);
        } else {
            $this->aggregateRepository->insert([
                'project_id' => (int) $project->id,
                'project_key' => (string) $project->project_key,
                'aggregate_date' => $date,
                'sessions_count' => $isNew ? 1 : 0,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function getSessionSummary(array $filters = []): array
    {
        $db = Database::connect();
        $builder = $db->table('landing_analytics_sessions');
        $this->applySessionFilters($builder, $filters);

        $total = (int) $builder->countAllResults(false);

        return [
            'total_sessions' => $total,
            'conversion_rate' => 0,
            'avg_active_seconds' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<array<string, mixed>>
     */
    private function buildFunnel(array $filters = []): array
    {
        $db = Database::connect();
        $result = $db->table('landing_analytics_events')
            ->select('event_name, COUNT(*) as count')
            ->groupBy('event_name')
            ->orderBy('COUNT(*)', 'DESC')
            ->get();
        $rows = (is_object($result)) ? $result->getResultArray() : [];

        return array_map(function ($row) {
            return ['stage' => $row['event_name'], 'count' => (int) $row['count']];
        }, $rows);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatSession(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'analytics_session_id' => $row['analytics_session_id'],
            'project_id' => (int) $row['project_id'],
            'started_at' => $row['started_at'],
            'last_seen_at' => $row['last_seen_at'],
            'event_count' => (int) ($row['event_count'] ?? 0),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatEvent(array $row): array
    {
        return [
            'event_name' => $row['event_name'],
            'page_path' => $row['page_path'],
            'occurred_at' => $row['occurred_at'],
        ];
    }

    /**
     * @param mixed $builder
     * @param array<string, mixed> $filters
     */
    private function applySessionFilters($builder, array $filters = []): void
    {
        if (isset($filters['project_id']) && !empty($filters['project_id'])) {
            $builder->where('project_id', (int) $filters['project_id']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('DATE(started_at) >=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $builder->where('DATE(started_at) <=', $filters['date_to']);
        }
    }

    private function isValidDate(string $date): bool
    {
        return strtotime($date) !== false;
    }
}
