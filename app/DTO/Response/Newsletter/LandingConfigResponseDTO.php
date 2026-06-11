<?php

declare(strict_types=1);

namespace App\DTO\Response\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LandingConfigResponse',
    title: 'Landing Config Response',
    required: ['project_key', 'slug', 'locale_default', 'double_opt_in_enabled']
)]
final readonly class LandingConfigResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        #[OA\Property(description: 'project_key', type: 'string')]
        public string $project_key,
        #[OA\Property(description: 'slug', type: 'string')]
        public string $slug,
        #[OA\Property(description: 'locale_default', type: 'string')]
        public string $locale_default,
        #[OA\Property(description: 'double_opt_in_enabled', type: 'integer')]
        public int $double_opt_in_enabled,
        #[OA\Property(description: 'recaptcha_site_key', type: 'string', nullable: true)]
        public ?string $recaptcha_site_key,
        #[OA\Property(description: 'supported_locales', type: 'string', nullable: true)]
        public ?string $supported_locales = null,
    ) {
    }

    public static function fromArray(array $data): static
    {
        return new static(
            project_key: (string) ($data['project_key'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            locale_default: (string) ($data['locale_default'] ?? ''),
            double_opt_in_enabled: (int) ($data['double_opt_in_enabled'] ?? 0),
            recaptcha_site_key: $data['recaptcha_site_key'] ?? null,
            supported_locales: $data['supported_locales'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'project_key'           => $this->project_key,
            'slug'                  => $this->slug,
            'locale_default'        => $this->locale_default,
            'double_opt_in_enabled' => $this->double_opt_in_enabled,
            'recaptcha_site_key'    => $this->recaptcha_site_key,
            'supported_locales'     => $this->supported_locales,
        ];
    }
}
