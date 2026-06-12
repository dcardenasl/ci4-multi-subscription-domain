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
    #[OA\Property(description: 'first_name', type: 'string', nullable: true)]
    public ?string $first_name;
    #[OA\Property(description: 'locale', type: 'string', nullable: true)]
    public ?string $locale;
    #[OA\Property(description: 'analytics_session_id', type: 'string', nullable: true)]
    public ?string $analytics_session_id;
    #[OA\Property(description: 'recaptcha_token', type: 'string', nullable: true)]
    public ?string $recaptcha_token;

    public function rules(): array
    {
        return [
            'email'                 => 'required|valid_email|max_length[255]',
            'project_key'           => 'required|string|max_length[255]',
            'invitation_code'       => 'permit_empty|string|max_length[255]',
            'first_name'            => 'permit_empty|string|max_length[255]',
            'locale'                => 'permit_empty|string|max_length[10]',
            'analytics_session_id'  => 'permit_empty|string|max_length[64]',
            'recaptcha_token'       => 'permit_empty|string|max_length[4096]',
        ];
    }

    protected function map(array $data): void
    {
        $this->email               = (string) ($data['email'] ?? '');
        $this->project_key         = (string) ($data['project_key'] ?? '');
        $this->invitation_code     = $data['invitation_code'] ?? null;
        $this->first_name          = $data['first_name'] ?? null;
        $this->locale              = $data['locale'] ?? null;
        $this->analytics_session_id = $data['analytics_session_id'] ?? null;
        $this->recaptcha_token     = $data['recaptcha_token'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'email'                 => $this->email,
            'project_key'           => $this->project_key,
            'invitation_code'       => $this->invitation_code,
            'first_name'            => $this->first_name,
            'locale'                => $this->locale,
            'analytics_session_id'  => $this->analytics_session_id,
            'recaptcha_token'       => $this->recaptcha_token,
        ];
    }
}
