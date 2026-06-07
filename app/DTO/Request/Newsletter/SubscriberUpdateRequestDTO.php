<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'SubscriberUpdateRequest')]
readonly class SubscriberUpdateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'project_id', type: 'integer', nullable: true)]
    public ?int $project_id;
    #[OA\Property(description: 'email', type: 'string', nullable: true)]
    public ?string $email;
    #[OA\Property(description: 'status', type: 'string', nullable: true)]
    public ?string $status;
    #[OA\Property(description: 'confirm_token', type: 'string', nullable: true)]
    public ?string $confirm_token;
    #[OA\Property(description: 'unsubscribe_token', type: 'string', nullable: true)]
    public ?string $unsubscribe_token;
    #[OA\Property(description: 'invitation_code', type: 'string', nullable: true)]
    public ?string $invitation_code;
    #[OA\Property(description: 'confirmed_at', type: 'string', format: 'date-time', nullable: true)]
    public ?string $confirmed_at;
    #[OA\Property(description: 'unsubscribed_at', type: 'string', format: 'date-time', nullable: true)]
    public ?string $unsubscribed_at;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'permit_empty|integer',
            'email' => 'permit_empty|string|max_length[255]',
            'status' => 'permit_empty|string|max_length[255]',
            'confirm_token' => 'permit_empty|string|max_length[255]',
            'unsubscribe_token' => 'permit_empty|string|max_length[255]',
            'invitation_code' => 'permit_empty|string|max_length[255]',
            'confirmed_at' => 'permit_empty|valid_date',
            'unsubscribed_at' => 'permit_empty|valid_date',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->project_id = isset($data['project_id']) ? (int) $data['project_id'] : null;
        $this->email = $data['email'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->confirm_token = $data['confirm_token'] ?? null;
        $this->unsubscribe_token = $data['unsubscribe_token'] ?? null;
        $this->invitation_code = $data['invitation_code'] ?? null;
        $this->confirmed_at = $data['confirmed_at'] ?? null;
        $this->unsubscribed_at = $data['unsubscribed_at'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'project_id' => $this->project_id,
            'email' => $this->email,
            'status' => $this->status,
            'confirm_token' => $this->confirm_token,
            'unsubscribe_token' => $this->unsubscribe_token,
            'invitation_code' => $this->invitation_code,
            'confirmed_at' => $this->confirmed_at,
            'unsubscribed_at' => $this->unsubscribed_at,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
