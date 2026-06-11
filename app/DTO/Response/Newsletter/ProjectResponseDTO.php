<?php

declare(strict_types=1);

namespace App\DTO\Response\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProjectResponse',
    title: 'Project Response',
    required: ["id","name","slug","project_key","is_active","double_opt_in_enabled","locale_default"]
)]
final readonly class ProjectResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        #[OA\Property(description: 'Unique identifier', example: 1)]
        public int $id,
        #[OA\Property(description: 'name', type: 'string')]
        public string $name,
        #[OA\Property(description: 'slug', type: 'string')]
        public string $slug,
        #[OA\Property(description: 'project_key', type: 'string')]
        public string $project_key,
        #[OA\Property(description: 'is_active', type: 'boolean')]
        public bool $is_active,
        #[OA\Property(description: 'smtp_provider', type: 'string')]
        public string $smtp_provider,
        #[OA\Property(description: 'smtp_host', type: 'string')]
        public string $smtp_host,
        #[OA\Property(description: 'smtp_port', type: 'integer')]
        public int $smtp_port,
        #[OA\Property(description: 'smtp_user', type: 'string')]
        public string $smtp_user,
        #[OA\Property(description: 'smtp_pass_encrypted', type: 'string')]
        public string $smtp_pass_encrypted,
        #[OA\Property(description: 'smtp_crypto', type: 'string')]
        public string $smtp_crypto,
        #[OA\Property(description: 'smtp_from_name', type: 'string')]
        public string $smtp_from_name,
        #[OA\Property(description: 'smtp_from_email', type: 'string')]
        public string $smtp_from_email,
        #[OA\Property(description: 'double_opt_in_enabled', type: 'boolean')]
        public bool $double_opt_in_enabled,
        #[OA\Property(description: 'double_opt_in_template_id', type: 'integer', nullable: true)]
        public ?int $double_opt_in_template_id,
        #[OA\Property(description: 'welcome_template_id', type: 'integer', nullable: true)]
        public ?int $welcome_template_id,
        #[OA\Property(description: 'locale_default', type: 'string')]
        public string $locale_default,
        #[OA\Property(description: 'recaptcha_site_key', type: 'string')]
        public string $recaptcha_site_key,
        #[OA\Property(description: 'recaptcha_secret_key', type: 'string')]
        public string $recaptcha_secret_key,
        #[OA\Property(description: 'supported_locales', type: 'string', nullable: true)]
        public ?string $supported_locales = null,
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
            name: (string) ($data['name'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            project_key: (string) ($data['project_key'] ?? ''),
            is_active: (bool) ($data['is_active'] ?? false),
            smtp_provider: (string) ($data['smtp_provider'] ?? ''),
            smtp_host: (string) ($data['smtp_host'] ?? ''),
            smtp_port: (int) ($data['smtp_port'] ?? 0),
            smtp_user: (string) ($data['smtp_user'] ?? ''),
            smtp_pass_encrypted: (string) ($data['smtp_pass_encrypted'] ?? ''),
            smtp_crypto: (string) ($data['smtp_crypto'] ?? ''),
            smtp_from_name: (string) ($data['smtp_from_name'] ?? ''),
            smtp_from_email: (string) ($data['smtp_from_email'] ?? ''),
            double_opt_in_enabled: (bool) ($data['double_opt_in_enabled'] ?? false),
            double_opt_in_template_id: isset($data['double_opt_in_template_id']) ? (int) $data['double_opt_in_template_id'] : null,
            welcome_template_id: isset($data['welcome_template_id']) ? (int) $data['welcome_template_id'] : null,
            locale_default: (string) ($data['locale_default'] ?? ''),
            recaptcha_site_key: (string) ($data['recaptcha_site_key'] ?? ''),
            recaptcha_secret_key: (string) ($data['recaptcha_secret_key'] ?? ''),
            supported_locales: $data['supported_locales'] ?? null,
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
            'supported_locales' => $this->supported_locales,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
