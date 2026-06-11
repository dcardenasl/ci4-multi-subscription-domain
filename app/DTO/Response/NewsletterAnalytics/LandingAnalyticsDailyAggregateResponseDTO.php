<?php

declare(strict_types=1);

namespace App\DTO\Response\NewsletterAnalytics;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'LandingAnalyticsDailyAggregateResponse', title: 'Landing Analytics Daily Aggregate Response')]
final readonly class LandingAnalyticsDailyAggregateResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        public int $id,
        public int $project_id,
        public string $project_key,
        public string $aggregate_date,
        public int $sessions_count,
        public int $pageviews_count,
        public int $section_views_count,
        public int $scroll_events_count,
        public int $form_starts_count,
        public int $form_submits_count,
        public int $error_events_count,
        public int $conversions_count,
        public int $confirmations_count,
        public int $unsubscribes_count,
        public int $active_seconds_total,
        public int $abandoned_sessions_count,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: (int) ($data['id'] ?? 0),
            project_id: (int) ($data['project_id'] ?? 0),
            project_key: (string) ($data['project_key'] ?? ''),
            aggregate_date: (string) ($data['aggregate_date'] ?? ''),
            sessions_count: (int) ($data['sessions_count'] ?? 0),
            pageviews_count: (int) ($data['pageviews_count'] ?? 0),
            section_views_count: (int) ($data['section_views_count'] ?? 0),
            scroll_events_count: (int) ($data['scroll_events_count'] ?? 0),
            form_starts_count: (int) ($data['form_starts_count'] ?? 0),
            form_submits_count: (int) ($data['form_submits_count'] ?? 0),
            error_events_count: (int) ($data['error_events_count'] ?? 0),
            conversions_count: (int) ($data['conversions_count'] ?? 0),
            confirmations_count: (int) ($data['confirmations_count'] ?? 0),
            unsubscribes_count: (int) ($data['unsubscribes_count'] ?? 0),
            active_seconds_total: (int) ($data['active_seconds_total'] ?? 0),
            abandoned_sessions_count: (int) ($data['abandoned_sessions_count'] ?? 0),
            created_at: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updated_at: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'project_key' => $this->project_key,
            'aggregate_date' => $this->aggregate_date,
            'sessions_count' => $this->sessions_count,
            'pageviews_count' => $this->pageviews_count,
            'section_views_count' => $this->section_views_count,
            'scroll_events_count' => $this->scroll_events_count,
            'form_starts_count' => $this->form_starts_count,
            'form_submits_count' => $this->form_submits_count,
            'error_events_count' => $this->error_events_count,
            'conversions_count' => $this->conversions_count,
            'confirmations_count' => $this->confirmations_count,
            'unsubscribes_count' => $this->unsubscribes_count,
            'active_seconds_total' => $this->active_seconds_total,
            'abandoned_sessions_count' => $this->abandoned_sessions_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
