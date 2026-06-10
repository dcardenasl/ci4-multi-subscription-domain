<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'SubscriberImportRequest')]
readonly class SubscriberImportRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'Target project id', type: 'integer')]
    public int $project_id;

    /**
     * @var list<array<string, mixed>>
     */
    #[OA\Property(
        description: 'Rows to import. Each row: email (required), first_name, locale, status, invitation_code.',
        type: 'array',
        items: new OA\Items(type: 'object')
    )]
    public array $rows;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|is_natural_no_zero',
            'rows'       => 'required',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->project_id = (int) ($data['project_id'] ?? 0);

        $rows = $data['rows'] ?? [];
        $this->rows = is_array($rows) ? array_values($rows) : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'rows' => $this->rows,
        ];
    }
}
