<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'PublicSubscribeRequest')]
readonly class PublicSubscribeRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'email', type: 'string', format: 'email')]
    public string $email;
    #[OA\Property(description: 'project_key', type: 'string')]
    public string $project_key;
    #[OA\Property(description: 'invitation_code', type: 'string', nullable: true)]
    public ?string $invitation_code;

    public function rules(): array
    {
        return [
            'email'           => 'required|valid_email|max_length[255]',
            'project_key'     => 'required|string|max_length[255]',
            'invitation_code' => 'permit_empty|string|max_length[255]',
        ];
    }

    protected function map(array $data): void
    {
        $this->email           = (string) ($data['email'] ?? '');
        $this->project_key     = (string) ($data['project_key'] ?? '');
        $this->invitation_code = $data['invitation_code'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'email'           => $this->email,
            'project_key'     => $this->project_key,
            'invitation_code' => $this->invitation_code,
        ];
    }
}
