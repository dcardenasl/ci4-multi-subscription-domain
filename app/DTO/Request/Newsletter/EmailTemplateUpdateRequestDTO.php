<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'EmailTemplateUpdateRequest')]
readonly class EmailTemplateUpdateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'project_id', type: 'integer', nullable: true)]
    public ?int $project_id;
    #[OA\Property(description: 'name', type: 'string', nullable: true)]
    public ?string $name;
    #[OA\Property(description: 'subject', type: 'string', nullable: true)]
    public ?string $subject;
    #[OA\Property(description: 'html_body', type: 'string', nullable: true)]
    public ?string $html_body;
    #[OA\Property(description: 'text_body', type: 'string', nullable: true)]
    public ?string $text_body;
    #[OA\Property(description: 'type', type: 'string', nullable: true)]
    public ?string $type;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'permit_empty|integer',
            'name' => 'permit_empty|string|max_length[255]',
            'subject' => 'permit_empty|string|max_length[255]',
            'html_body' => 'permit_empty|string',
            'text_body' => 'permit_empty|string',
            'type' => 'permit_empty|string|max_length[255]',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->project_id = isset($data['project_id']) ? (int) $data['project_id'] : null;
        $this->name = $data['name'] ?? null;
        $this->subject = $data['subject'] ?? null;
        $this->html_body = $data['html_body'] ?? null;
        $this->text_body = $data['text_body'] ?? null;
        $this->type = $data['type'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'project_id' => $this->project_id,
            'name' => $this->name,
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'text_body' => $this->text_body,
            'type' => $this->type,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
