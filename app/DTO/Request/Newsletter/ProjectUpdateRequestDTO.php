<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ProjectUpdateRequest')]
readonly class ProjectUpdateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'name', type: 'string', nullable: true)]
    public ?string $name;
    #[OA\Property(description: 'slug', type: 'string', nullable: true)]
    public ?string $slug;
    #[OA\Property(description: 'project_key', type: 'string', nullable: true)]
    public ?string $project_key;
    #[OA\Property(description: 'is_active', type: 'boolean', nullable: true)]
    public ?bool $is_active;
    #[OA\Property(description: 'smtp_provider', type: 'string', nullable: true)]
    public ?string $smtp_provider;
    #[OA\Property(description: 'smtp_host', type: 'string', nullable: true)]
    public ?string $smtp_host;
    #[OA\Property(description: 'smtp_port', type: 'integer', nullable: true)]
    public ?int $smtp_port;
    #[OA\Property(description: 'smtp_user', type: 'string', nullable: true)]
    public ?string $smtp_user;
    #[OA\Property(description: 'smtp_pass_encrypted', type: 'string', nullable: true)]
    public ?string $smtp_pass_encrypted;
    #[OA\Property(description: 'smtp_crypto', type: 'string', nullable: true)]
    public ?string $smtp_crypto;
    #[OA\Property(description: 'smtp_from_name', type: 'string', nullable: true)]
    public ?string $smtp_from_name;
    #[OA\Property(description: 'smtp_from_email', type: 'string', nullable: true)]
    public ?string $smtp_from_email;
    #[OA\Property(description: 'double_opt_in_enabled', type: 'boolean', nullable: true)]
    public ?bool $double_opt_in_enabled;
    #[OA\Property(description: 'locale_default', type: 'string', nullable: true)]
    public ?string $locale_default;
    #[OA\Property(description: 'recaptcha_site_key', type: 'string', nullable: true)]
    public ?string $recaptcha_site_key;
    #[OA\Property(description: 'recaptcha_secret_key', type: 'string', nullable: true)]
    public ?string $recaptcha_secret_key;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'permit_empty|string|max_length[255]',
            'slug' => 'permit_empty|string|max_length[255]',
            'project_key' => 'permit_empty|string|max_length[255]',
            'is_active' => 'permit_empty|boolean_like',
            'smtp_provider' => 'permit_empty|string|max_length[255]',
            'smtp_host' => 'permit_empty|string|max_length[255]',
            'smtp_port' => 'permit_empty|integer',
            'smtp_user' => 'permit_empty|string|max_length[255]',
            'smtp_pass_encrypted' => 'permit_empty|string',
            'smtp_crypto' => 'permit_empty|string|max_length[255]',
            'smtp_from_name' => 'permit_empty|string|max_length[255]',
            'smtp_from_email' => 'permit_empty|string|max_length[255]',
            'double_opt_in_enabled' => 'permit_empty|boolean_like',
            'locale_default' => 'permit_empty|string|max_length[255]',
            'recaptcha_site_key' => 'permit_empty|string|max_length[255]',
            'recaptcha_secret_key' => 'permit_empty|string|max_length[255]',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->name = $data['name'] ?? null;
        $this->slug = $data['slug'] ?? null;
        $this->project_key = $data['project_key'] ?? null;
        $this->is_active = isset($data['is_active']) ? (bool) $data['is_active'] : null;
        $this->smtp_provider = $data['smtp_provider'] ?? null;
        $this->smtp_host = $data['smtp_host'] ?? null;
        $this->smtp_port = isset($data['smtp_port']) ? (int) $data['smtp_port'] : null;
        $this->smtp_user = $data['smtp_user'] ?? null;
        $this->smtp_pass_encrypted = $data['smtp_pass_encrypted'] ?? null;
        $this->smtp_crypto = $data['smtp_crypto'] ?? null;
        $this->smtp_from_name = $data['smtp_from_name'] ?? null;
        $this->smtp_from_email = $data['smtp_from_email'] ?? null;
        $this->double_opt_in_enabled = isset($data['double_opt_in_enabled']) ? (bool) $data['double_opt_in_enabled'] : null;
        $this->locale_default = $data['locale_default'] ?? null;
        $this->recaptcha_site_key = $data['recaptcha_site_key'] ?? null;
        $this->recaptcha_secret_key = $data['recaptcha_secret_key'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
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
            'locale_default' => $this->locale_default,
            'recaptcha_site_key' => $this->recaptcha_site_key,
            'recaptcha_secret_key' => $this->recaptcha_secret_key,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
