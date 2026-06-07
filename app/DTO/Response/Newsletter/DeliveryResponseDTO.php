<?php

declare(strict_types=1);

namespace App\DTO\Response\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeliveryResponse',
    title: 'Delivery Response',
    required: ["id","project_id","campaign_id","subscriber_id","email","status","attempts"]
)]
final readonly class DeliveryResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        #[OA\Property(description: 'Unique identifier', example: 1)]
        public int $id,
        #[OA\Property(description: 'project_id', type: 'integer')]
        public int $project_id,
        #[OA\Property(description: 'campaign_id', type: 'integer')]
        public int $campaign_id,
        #[OA\Property(description: 'subscriber_id', type: 'integer')]
        public int $subscriber_id,
        #[OA\Property(description: 'email', type: 'string')]
        public string $email,
        #[OA\Property(description: 'status', type: 'string')]
        public string $status,
        #[OA\Property(description: 'attempts', type: 'integer')]
        public int $attempts,
        #[OA\Property(description: 'last_error', type: 'string')]
        public string $last_error,
        #[OA\Property(description: 'provider_message_id', type: 'string')]
        public string $provider_message_id,
        #[OA\Property(description: 'sent_at', type: 'string', format: 'date-time')]
        public string $sent_at,
        #[OA\Property(property: 'created_at', description: 'Creation timestamp', example: '2026-02-26 12:00:00', nullable: true)]
        public ?string $createdAt = null,
        #[OA\Property(property: 'updated_at', description: 'Last update timestamp', example: '2026-02-26 12:00:00', nullable: true)]
        public ?string $updatedAt = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: (int) ($data['id'] ?? 0),
            project_id: (int) ($data['project_id'] ?? 0),
            campaign_id: (int) ($data['campaign_id'] ?? 0),
            subscriber_id: (int) ($data['subscriber_id'] ?? 0),
            email: (string) ($data['email'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            attempts: (int) ($data['attempts'] ?? 0),
            last_error: (string) ($data['last_error'] ?? ''),
            provider_message_id: (string) ($data['provider_message_id'] ?? ''),
            sent_at: (string) ($data['sent_at'] ?? ''),
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'campaign_id' => $this->campaign_id,
            'subscriber_id' => $this->subscriber_id,
            'email' => $this->email,
            'status' => $this->status,
            'attempts' => $this->attempts,
            'last_error' => $this->last_error,
            'provider_message_id' => $this->provider_message_id,
            'sent_at' => $this->sent_at,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
