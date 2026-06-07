<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'CampaignCreateRequest')]
readonly class CampaignCreateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'project_id', type: 'integer')]
    public int $project_id;
    #[OA\Property(description: 'name', type: 'string')]
    public string $name;
    #[OA\Property(description: 'subject', type: 'string')]
    public string $subject;
    #[OA\Property(description: 'html_body', type: 'string')]
    public string $html_body;
    #[OA\Property(description: 'text_body', type: 'string')]
    public string $text_body;
    #[OA\Property(description: 'status', type: 'string')]
    public string $status;
    #[OA\Property(description: 'scheduled_at', type: 'string', format: 'date-time')]
    public string $scheduled_at;
    #[OA\Property(description: 'send_started_at', type: 'string', format: 'date-time')]
    public string $send_started_at;
    #[OA\Property(description: 'sent_at', type: 'string', format: 'date-time')]
    public string $sent_at;
    #[OA\Property(description: 'failed_at', type: 'string', format: 'date-time')]
    public string $failed_at;
    #[OA\Property(description: 'failure_reason', type: 'string')]
    public string $failure_reason;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|integer',
            'name' => 'required|string|max_length[255]',
            'subject' => 'required|string|max_length[255]',
            'html_body' => 'required|string',
            'text_body' => 'permit_empty|string',
            'status' => 'required|string|max_length[255]',
            'scheduled_at' => 'permit_empty|valid_date',
            'send_started_at' => 'permit_empty|valid_date',
            'sent_at' => 'permit_empty|valid_date',
            'failed_at' => 'permit_empty|valid_date',
            'failure_reason' => 'permit_empty|string',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->project_id = (int) ($data['project_id'] ?? 0);
        $this->name = (string) ($data['name'] ?? '');
        $this->subject = (string) ($data['subject'] ?? '');
        $this->html_body = (string) ($data['html_body'] ?? '');
        $this->text_body = (string) ($data['text_body'] ?? '');
        $this->status = (string) ($data['status'] ?? '');
        $this->scheduled_at = (string) ($data['scheduled_at'] ?? '');
        $this->send_started_at = (string) ($data['send_started_at'] ?? '');
        $this->sent_at = (string) ($data['sent_at'] ?? '');
        $this->failed_at = (string) ($data['failed_at'] ?? '');
        $this->failure_reason = (string) ($data['failure_reason'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
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
        ];
    }
}
