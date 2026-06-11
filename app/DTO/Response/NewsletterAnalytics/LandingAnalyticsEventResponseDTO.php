<?php

declare(strict_types=1);

namespace App\DTO\Response\NewsletterAnalytics;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'LandingAnalyticsEventResponse', title: 'Landing Analytics Event Response')]
final readonly class LandingAnalyticsEventResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        public int $id,
        public string $analytics_session_id,
        public ?int $session_id,
        public int $project_id,
        public string $project_key,
        public string $event_name,
        public ?string $page_path,
        public ?string $section_key,
        public ?string $form_key,
        public ?string $occurred_at,
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
            session_id: isset($data['session_id']) ? (int) $data['session_id'] : null,
            project_id: (int) ($data['project_id'] ?? 0),
            project_key: (string) ($data['project_key'] ?? ''),
            event_name: (string) ($data['event_name'] ?? ''),
            page_path: $data['page_path'] ?? null,
            section_key: $data['section_key'] ?? null,
            form_key: $data['form_key'] ?? null,
            occurred_at: isset($data['occurred_at']) ? (string) $data['occurred_at'] : null,
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
            'session_id' => $this->session_id,
            'project_id' => $this->project_id,
            'project_key' => $this->project_key,
            'event_name' => $this->event_name,
            'page_path' => $this->page_path,
            'section_key' => $this->section_key,
            'form_key' => $this->form_key,
            'occurred_at' => $this->occurred_at,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
