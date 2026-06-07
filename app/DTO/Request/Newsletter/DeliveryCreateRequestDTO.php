<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'DeliveryCreateRequest')]
readonly class DeliveryCreateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'project_id', type: 'integer')]
    public int $project_id;
    #[OA\Property(description: 'campaign_id', type: 'integer')]
    public int $campaign_id;
    #[OA\Property(description: 'subscriber_id', type: 'integer')]
    public int $subscriber_id;
    #[OA\Property(description: 'email', type: 'string')]
    public string $email;
    #[OA\Property(description: 'status', type: 'string')]
    public string $status;
    #[OA\Property(description: 'attempts', type: 'integer')]
    public int $attempts;
    #[OA\Property(description: 'last_error', type: 'string')]
    public string $last_error;
    #[OA\Property(description: 'provider_message_id', type: 'string')]
    public string $provider_message_id;
    #[OA\Property(description: 'sent_at', type: 'string', format: 'date-time')]
    public string $sent_at;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|integer',
            'campaign_id' => 'required|integer',
            'subscriber_id' => 'required|integer',
            'email' => 'required|string|max_length[255]',
            'status' => 'required|string|max_length[255]',
            'attempts' => 'required|integer',
            'last_error' => 'permit_empty|string',
            'provider_message_id' => 'permit_empty|string|max_length[255]',
            'sent_at' => 'permit_empty|valid_date',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->project_id = (int) ($data['project_id'] ?? 0);
        $this->campaign_id = (int) ($data['campaign_id'] ?? 0);
        $this->subscriber_id = (int) ($data['subscriber_id'] ?? 0);
        $this->email = (string) ($data['email'] ?? '');
        $this->status = (string) ($data['status'] ?? '');
        $this->attempts = (int) ($data['attempts'] ?? 0);
        $this->last_error = (string) ($data['last_error'] ?? '');
        $this->provider_message_id = (string) ($data['provider_message_id'] ?? '');
        $this->sent_at = (string) ($data['sent_at'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'campaign_id' => $this->campaign_id,
            'subscriber_id' => $this->subscriber_id,
            'email' => $this->email,
            'status' => $this->status,
            'attempts' => $this->attempts,
            'last_error' => $this->last_error,
            'provider_message_id' => $this->provider_message_id,
            'sent_at' => $this->sent_at,
        ];
    }
}
