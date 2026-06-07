<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'DeliveryUpdateRequest')]
readonly class DeliveryUpdateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'project_id', type: 'integer', nullable: true)]
    public ?int $project_id;
    #[OA\Property(description: 'campaign_id', type: 'integer', nullable: true)]
    public ?int $campaign_id;
    #[OA\Property(description: 'subscriber_id', type: 'integer', nullable: true)]
    public ?int $subscriber_id;
    #[OA\Property(description: 'email', type: 'string', nullable: true)]
    public ?string $email;
    #[OA\Property(description: 'status', type: 'string', nullable: true)]
    public ?string $status;
    #[OA\Property(description: 'attempts', type: 'integer', nullable: true)]
    public ?int $attempts;
    #[OA\Property(description: 'last_error', type: 'string', nullable: true)]
    public ?string $last_error;
    #[OA\Property(description: 'provider_message_id', type: 'string', nullable: true)]
    public ?string $provider_message_id;
    #[OA\Property(description: 'sent_at', type: 'string', format: 'date-time', nullable: true)]
    public ?string $sent_at;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'permit_empty|integer',
            'campaign_id' => 'permit_empty|integer',
            'subscriber_id' => 'permit_empty|integer',
            'email' => 'permit_empty|string|max_length[255]',
            'status' => 'permit_empty|string|max_length[255]',
            'attempts' => 'permit_empty|integer',
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
        $this->project_id = isset($data['project_id']) ? (int) $data['project_id'] : null;
        $this->campaign_id = isset($data['campaign_id']) ? (int) $data['campaign_id'] : null;
        $this->subscriber_id = isset($data['subscriber_id']) ? (int) $data['subscriber_id'] : null;
        $this->email = $data['email'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->attempts = isset($data['attempts']) ? (int) $data['attempts'] : null;
        $this->last_error = $data['last_error'] ?? null;
        $this->provider_message_id = $data['provider_message_id'] ?? null;
        $this->sent_at = $data['sent_at'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'project_id' => $this->project_id,
            'campaign_id' => $this->campaign_id,
            'subscriber_id' => $this->subscriber_id,
            'email' => $this->email,
            'status' => $this->status,
            'attempts' => $this->attempts,
            'last_error' => $this->last_error,
            'provider_message_id' => $this->provider_message_id,
            'sent_at' => $this->sent_at,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
