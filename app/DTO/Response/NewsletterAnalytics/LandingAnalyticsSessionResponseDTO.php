<?php

declare(strict_types=1);

namespace App\DTO\Response\NewsletterAnalytics;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'LandingAnalyticsSessionResponse', title: 'Landing Analytics Session Response')]
final readonly class LandingAnalyticsSessionResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        public int $id,
        public string $analytics_session_id,
        public int $project_id,
        public string $project_key,
        public ?int $subscriber_id,
        public ?string $visitor_locale,
        public ?string $invitation_code,
        public ?string $device_type,
        public ?string $referrer,
        public ?string $page_path,
        public ?string $started_at,
        public ?string $last_seen_at,
        public ?string $converted_at,
        public ?string $abandoned_at,
        public int $active_seconds,
        public int $event_count,
        public int $pageview_count,
        public int $section_count,
        public int $scroll_count,
        public int $form_start_count,
        public int $form_submit_count,
        public int $error_count,
        public int $conversion_count,
        public ?array $metadata = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            id: (int) ($data['id'] ?? 0),
            analytics_session_id: (string) ($data['analytics_session_id'] ?? ''),
            project_id: (int) ($data['project_id'] ?? 0),
            project_key: (string) ($data['project_key'] ?? ''),
            subscriber_id: isset($data['subscriber_id']) ? (int) $data['subscriber_id'] : null,
            visitor_locale: $data['visitor_locale'] ?? null,
            invitation_code: $data['invitation_code'] ?? null,
            device_type: $data['device_type'] ?? null,
            referrer: $data['referrer'] ?? null,
            page_path: $data['page_path'] ?? null,
            started_at: isset($data['started_at']) ? (string) $data['started_at'] : null,
            last_seen_at: isset($data['last_seen_at']) ? (string) $data['last_seen_at'] : null,
            converted_at: isset($data['converted_at']) ? (string) $data['converted_at'] : null,
            abandoned_at: isset($data['abandoned_at']) ? (string) $data['abandoned_at'] : null,
            active_seconds: (int) ($data['active_seconds'] ?? 0),
            event_count: (int) ($data['event_count'] ?? 0),
            pageview_count: (int) ($data['pageview_count'] ?? 0),
            section_count: (int) ($data['section_count'] ?? 0),
            scroll_count: (int) ($data['scroll_count'] ?? 0),
            form_start_count: (int) ($data['form_start_count'] ?? 0),
            form_submit_count: (int) ($data['form_submit_count'] ?? 0),
            error_count: (int) ($data['error_count'] ?? 0),
            conversion_count: (int) ($data['conversion_count'] ?? 0),
            metadata: isset($data['metadata']) ? (is_array($data['metadata']) ? $data['metadata'] : json_decode((string) $data['metadata'], true)) : null,
            created_at: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updated_at: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'analytics_session_id' => $this->analytics_session_id,
            'project_id' => $this->project_id,
            'project_key' => $this->project_key,
            'subscriber_id' => $this->subscriber_id,
            'visitor_locale' => $this->visitor_locale,
            'invitation_code' => $this->invitation_code,
            'device_type' => $this->device_type,
            'referrer' => $this->referrer,
            'page_path' => $this->page_path,
            'started_at' => $this->started_at,
            'last_seen_at' => $this->last_seen_at,
            'converted_at' => $this->converted_at,
            'abandoned_at' => $this->abandoned_at,
            'active_seconds' => $this->active_seconds,
            'event_count' => $this->event_count,
            'pageview_count' => $this->pageview_count,
            'section_count' => $this->section_count,
            'scroll_count' => $this->scroll_count,
            'form_start_count' => $this->form_start_count,
            'form_submit_count' => $this->form_submit_count,
            'error_count' => $this->error_count,
            'conversion_count' => $this->conversion_count,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
