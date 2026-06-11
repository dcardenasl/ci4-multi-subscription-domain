<?php

declare(strict_types=1);

namespace App\DTO\Response\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'EmailTemplateResponse',
    title: 'EmailTemplate Response',
    required: ["id","project_id","name","subject","html_body","type"]
)]
final readonly class EmailTemplateResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        #[OA\Property(description: 'Unique identifier', example: 1)]
        public int $id,
        #[OA\Property(description: 'project_id', type: 'integer')]
        public int $project_id,
        #[OA\Property(description: 'name', type: 'string')]
        public string $name,
        #[OA\Property(description: 'subject', type: 'string')]
        public string $subject,
        #[OA\Property(description: 'html_body', type: 'string')]
        public string $html_body,
        #[OA\Property(description: 'text_body', type: 'string')]
        public string $text_body,
        #[OA\Property(description: 'type', type: 'string')]
        public string $type,
        #[OA\Property(property: 'created_at', description: 'Creation timestamp', example: '2026-02-26 12:00:00', nullable: true)]
        public ?string $createdAt = null,
        #[OA\Property(property: 'updated_at', description: 'Last update timestamp', example: '2026-02-26 12:00:00', nullable: true)]
        public ?string $updatedAt = null,
        #[OA\Property(description: 'translations', type: 'array', items: new OA\Items(type: 'object'))]
        public array $translations = []
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
            name: (string) ($data['name'] ?? ''),
            subject: (string) ($data['subject'] ?? ''),
            html_body: (string) ($data['html_body'] ?? ''),
            text_body: (string) ($data['text_body'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            translations: (function () use ($data) {
                $t = $data['translations'] ?? null;
                if ($t === null && isset($data['id'])) {
                    $t = model(\App\Models\EmailTemplateTranslationModel::class)->where('email_template_id', $data['id'])->findAll();
                    $t = array_map(fn ($x) => is_object($x) ? $x->toArray() : $x, $t);
                }
                return $t ?? [];
            })()
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
            'name' => $this->name,
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'text_body' => $this->text_body,
            'type' => $this->type,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'translations' => $this->translations,
        ];
    }
}
