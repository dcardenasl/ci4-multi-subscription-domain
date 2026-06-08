<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ProjectCreateRequest')]
readonly class ProjectCreateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'name', type: 'string')]
    public string $name;
    #[OA\Property(description: 'slug', type: 'string')]
    public string $slug;
    #[OA\Property(description: 'project_key', type: 'string')]
    public string $project_key;
    #[OA\Property(description: 'is_active', type: 'boolean')]
    public bool $is_active;
    #[OA\Property(description: 'smtp_provider', type: 'string')]
    public string $smtp_provider;
    #[OA\Property(description: 'smtp_host', type: 'string')]
    public string $smtp_host;
    #[OA\Property(description: 'smtp_port', type: 'integer')]
    public int $smtp_port;
    #[OA\Property(description: 'smtp_user', type: 'string')]
    public string $smtp_user;
    #[OA\Property(description: 'smtp_pass_encrypted', type: 'string')]
    public string $smtp_pass_encrypted;
    #[OA\Property(description: 'smtp_crypto', type: 'string')]
    public string $smtp_crypto;
    #[OA\Property(description: 'smtp_from_name', type: 'string')]
    public string $smtp_from_name;
    #[OA\Property(description: 'smtp_from_email', type: 'string')]
    public string $smtp_from_email;
    #[OA\Property(description: 'double_opt_in_enabled', type: 'boolean')]
    public bool $double_opt_in_enabled;
    #[OA\Property(description: 'double_opt_in_template_id', type: 'integer', nullable: true)]
    public ?int $double_opt_in_template_id;
    #[OA\Property(description: 'welcome_template_id', type: 'integer', nullable: true)]
    public ?int $welcome_template_id;
    #[OA\Property(description: 'locale_default', type: 'string')]
    public string $locale_default;
    #[OA\Property(description: 'recaptcha_site_key', type: 'string')]
    public string $recaptcha_site_key;
    #[OA\Property(description: 'recaptcha_secret_key', type: 'string')]
    public string $recaptcha_secret_key;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max_length[255]',
            'slug' => 'required|string|max_length[255]',
            'project_key' => 'required|string|max_length[255]',
            'is_active' => 'required|boolean_like',
            'smtp_provider' => 'permit_empty|string|max_length[255]',
            'smtp_host' => 'permit_empty|string|max_length[255]',
            'smtp_port' => 'permit_empty|integer',
            'smtp_user' => 'permit_empty|string|max_length[255]',
            'smtp_pass_encrypted' => 'permit_empty|string',
            'smtp_crypto' => 'permit_empty|string|max_length[255]',
            'smtp_from_name' => 'permit_empty|string|max_length[255]',
            'smtp_from_email' => 'permit_empty|string|max_length[255]',
            'double_opt_in_enabled' => 'required|boolean_like',
            'double_opt_in_template_id' => 'permit_empty|integer',
            'welcome_template_id' => 'permit_empty|integer',
            'locale_default' => 'required|string|max_length[255]',
            'recaptcha_site_key' => 'permit_empty|string|max_length[255]',
            'recaptcha_secret_key' => 'permit_empty|string|max_length[255]',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->name = (string) ($data['name'] ?? '');
        $this->slug = (string) ($data['slug'] ?? '');
        $this->project_key = (string) ($data['project_key'] ?? '');
        $this->is_active = (bool) ($data['is_active'] ?? false);
        $this->smtp_provider = (string) ($data['smtp_provider'] ?? '');
        $this->smtp_host = (string) ($data['smtp_host'] ?? '');
        $this->smtp_port = (int) ($data['smtp_port'] ?? 0);
        $this->smtp_user = (string) ($data['smtp_user'] ?? '');
        $this->smtp_pass_encrypted = (string) ($data['smtp_pass_encrypted'] ?? '');
        $this->smtp_crypto = (string) ($data['smtp_crypto'] ?? '');
        $this->smtp_from_name = (string) ($data['smtp_from_name'] ?? '');
        $this->smtp_from_email = (string) ($data['smtp_from_email'] ?? '');
        $this->double_opt_in_enabled = (bool) ($data['double_opt_in_enabled'] ?? false);
        $this->double_opt_in_template_id = isset($data['double_opt_in_template_id']) && $data['double_opt_in_template_id'] !== '' ? (int) $data['double_opt_in_template_id'] : null;
        $this->welcome_template_id = isset($data['welcome_template_id']) && $data['welcome_template_id'] !== '' ? (int) $data['welcome_template_id'] : null;
        $this->locale_default = (string) ($data['locale_default'] ?? '');
        $this->recaptcha_site_key = (string) ($data['recaptcha_site_key'] ?? '');
        $this->recaptcha_secret_key = (string) ($data['recaptcha_secret_key'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'project_key' => $this->project_key,
            'is_active' => $this->is_active,
            'smtp_provider' => $this->smtp_provider,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_user' => $this->smtp_user,
            'smtp_pass_encrypted' => $this->smtp_pass_encrypted,
            'smtp_crypto' => $this->smtp_crypto,
            'smtp_from_name' => $this->smtp_from_name,
            'smtp_from_email' => $this->smtp_from_email,
            'double_opt_in_enabled' => $this->double_opt_in_enabled,
            'double_opt_in_template_id' => $this->double_opt_in_template_id,
            'welcome_template_id' => $this->welcome_template_id,
            'locale_default' => $this->locale_default,
            'recaptcha_site_key' => $this->recaptcha_site_key,
            'recaptcha_secret_key' => $this->recaptcha_secret_key,
        ];
    }
}
