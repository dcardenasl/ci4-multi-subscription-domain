<?php

declare(strict_types=1);

namespace App\DTO\Response\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SubscriberResponse',
    title: 'Subscriber Response',
    required: ["id","project_id","email","status"]
)]
final readonly class SubscriberResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        #[OA\Property(description: 'Unique identifier', example: 1)]
        public int $id,
        #[OA\Property(description: 'project_id', type: 'integer')]
        public int $project_id,
        #[OA\Property(description: 'email', type: 'string')]
        public string $email,
        #[OA\Property(description: 'first_name', type: 'string')]
        public string $first_name,
        #[OA\Property(description: 'locale', type: 'string')]
        public string $locale,
        #[OA\Property(description: 'status', type: 'string', enum: ['pending', 'confirmed', 'unsubscribed', 'bounced'])]
        public string $status,
        #[OA\Property(description: 'confirm_token', type: 'string')]
        public string $confirm_token,
        #[OA\Property(description: 'unsubscribe_token', type: 'string')]
        public string $unsubscribe_token,
        #[OA\Property(description: 'invitation_code', type: 'string')]
        public string $invitation_code,
        #[OA\Property(description: 'confirmed_at', type: 'string', format: 'date-time')]
        public string $confirmed_at,
        #[OA\Property(description: 'unsubscribed_at', type: 'string', format: 'date-time')]
        public string $unsubscribed_at,
        #[OA\Property(property: 'created_at', description: 'Creation timestamp', example: '2026-02-26 12:00:00', nullable: true)]
        public ?string $createdAt = null,
        #[OA\Property(property: 'updated_at', description: 'Last update timestamp', example: '2026-02-26 12:00:00', nullable: true)]
        public ?string $updatedAt = null,
        #[OA\Property(property: 'project_name', description: 'Project Name', example: 'My Project', nullable: true)]
        public ?string $project_name = null
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
            email: (string) ($data['email'] ?? ''),
            first_name: (string) ($data['first_name'] ?? ''),
            locale: (string) ($data['locale'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            confirm_token: (string) ($data['confirm_token'] ?? ''),
            unsubscribe_token: (string) ($data['unsubscribe_token'] ?? ''),
            invitation_code: (string) ($data['invitation_code'] ?? ''),
            confirmed_at: (string) ($data['confirmed_at'] ?? ''),
            unsubscribed_at: (string) ($data['unsubscribed_at'] ?? ''),
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
            project_name: isset($data['project_name']) ? (string) $data['project_name'] : null,
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
            'email' => $this->email,
            'first_name' => $this->first_name,
            'locale' => $this->locale,
            'status' => $this->status,
            'confirm_token' => $this->confirm_token,
            'unsubscribe_token' => $this->unsubscribe_token,
            'invitation_code' => $this->invitation_code,
            'confirmed_at' => $this->confirmed_at,
            'unsubscribed_at' => $this->unsubscribed_at,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'project_name' => $this->project_name,
        ];
    }
}
