<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'SubscriberCreateRequest')]
readonly class SubscriberCreateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'project_id', type: 'integer')]
    public int $project_id;
    #[OA\Property(description: 'email', type: 'string')]
    public string $email;
    #[OA\Property(description: 'status', type: 'string')]
    public string $status;
    #[OA\Property(description: 'confirm_token', type: 'string')]
    public string $confirm_token;
    #[OA\Property(description: 'unsubscribe_token', type: 'string')]
    public string $unsubscribe_token;
    #[OA\Property(description: 'invitation_code', type: 'string')]
    public string $invitation_code;
    #[OA\Property(description: 'confirmed_at', type: 'string', format: 'date-time')]
    public string $confirmed_at;
    #[OA\Property(description: 'unsubscribed_at', type: 'string', format: 'date-time')]
    public string $unsubscribed_at;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|integer',
            'email' => 'required|string|max_length[255]',
            'status' => 'required|string|max_length[255]',
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
        $this->project_id = (int) ($data['project_id'] ?? 0);
        $this->email = (string) ($data['email'] ?? '');
        $this->status = (string) ($data['status'] ?? '');
        $this->confirm_token = (string) ($data['confirm_token'] ?? '');
        $this->unsubscribe_token = (string) ($data['unsubscribe_token'] ?? '');
        $this->invitation_code = (string) ($data['invitation_code'] ?? '');
        $this->confirmed_at = (string) ($data['confirmed_at'] ?? '');
        $this->unsubscribed_at = (string) ($data['unsubscribed_at'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'email' => $this->email,
            'status' => $this->status,
            'confirm_token' => $this->confirm_token,
            'unsubscribe_token' => $this->unsubscribe_token,
            'invitation_code' => $this->invitation_code,
            'confirmed_at' => $this->confirmed_at,
            'unsubscribed_at' => $this->unsubscribed_at,
        ];
    }
}
