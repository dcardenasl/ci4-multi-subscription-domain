<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'CampaignUpdateRequest')]
readonly class CampaignUpdateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'project_id', type: 'integer', nullable: true)]
    public ?int $project_id;
    #[OA\Property(description: 'name', type: 'string', nullable: true)]
    public ?string $name;
    #[OA\Property(description: 'subject', type: 'string', nullable: true)]
    public ?string $subject;
    #[OA\Property(description: 'html_body', type: 'string', nullable: true)]
    public ?string $html_body;
    #[OA\Property(description: 'text_body', type: 'string', nullable: true)]
    public ?string $text_body;
    #[OA\Property(description: 'status', type: 'string', nullable: true)]
    public ?string $status;
    #[OA\Property(description: 'scheduled_at', type: 'string', format: 'date-time', nullable: true)]
    public ?string $scheduled_at;
    #[OA\Property(description: 'send_started_at', type: 'string', format: 'date-time', nullable: true)]
    public ?string $send_started_at;
    #[OA\Property(description: 'sent_at', type: 'string', format: 'date-time', nullable: true)]
    public ?string $sent_at;
    #[OA\Property(description: 'failed_at', type: 'string', format: 'date-time', nullable: true)]
    public ?string $failed_at;
    #[OA\Property(description: 'failure_reason', type: 'string', nullable: true)]
    public ?string $failure_reason;
    #[OA\Property(description: 'translations', type: 'array', items: new OA\Items(type: 'object'))]
    public ?array $translations;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'permit_empty|integer',
            'name' => 'permit_empty|string|max_length[255]',
            'subject' => 'permit_empty|string|max_length[255]',
            'html_body' => 'permit_empty|string',
            'text_body' => 'permit_empty|string',
            'status' => 'permit_empty|string|max_length[255]',
            'scheduled_at' => 'permit_empty|valid_date',
            'send_started_at' => 'permit_empty|valid_date',
            'sent_at' => 'permit_empty|valid_date',
            'failed_at' => 'permit_empty|valid_date',
            'failure_reason' => 'permit_empty|string',
            'translations' => 'permit_empty|matches_array',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->project_id = isset($data['project_id']) ? (int) $data['project_id'] : null;
        $this->name = $data['name'] ?? null;
        $this->subject = $data['subject'] ?? null;
        $this->html_body = $data['html_body'] ?? null;
        $this->text_body = $data['text_body'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->scheduled_at = $data['scheduled_at'] ?? null;
        $this->send_started_at = $data['send_started_at'] ?? null;
        $this->sent_at = $data['sent_at'] ?? null;
        $this->failed_at = $data['failed_at'] ?? null;
        $this->failure_reason = $data['failure_reason'] ?? null;
        $this->translations = $data['translations'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'project_id' => $this->project_id,
            'name' => $this->name,
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'text_body' => $this->text_body,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at,
            'send_started_at' => $this->send_started_at,
            'sent_at' => $this->sent_at,
            'failed_at' => $this->failed_at,
            'failure_reason' => $this->failure_reason,
            'translations' => $this->translations,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
