<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'EmailTemplateCreateRequest')]
readonly class EmailTemplateCreateRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'project_id', type: 'integer')]
    public int $project_id;
    #[OA\Property(description: 'name', type: 'string')]
    public string $name;
    #[OA\Property(description: 'subject', type: 'string')]
    public string $subject;
    #[OA\Property(description: 'html_body', type: 'string')]
    public string $html_body;
    #[OA\Property(description: 'text_body', type: 'string')]
    public string $text_body;
    #[OA\Property(description: 'type', type: 'string')]
    public string $type;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|integer',
            'name' => 'required|string|max_length[255]',
            'subject' => 'required|string|max_length[255]',
            'html_body' => 'required|string',
            'text_body' => 'permit_empty|string',
            'type' => 'required|string|max_length[255]',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->project_id = (int) ($data['project_id'] ?? 0);
        $this->name = (string) ($data['name'] ?? '');
        $this->subject = (string) ($data['subject'] ?? '');
        $this->html_body = (string) ($data['html_body'] ?? '');
        $this->text_body = (string) ($data['text_body'] ?? '');
        $this->type = (string) ($data['type'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'name' => $this->name,
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'text_body' => $this->text_body,
            'type' => $this->type,
        ];
    }
}
