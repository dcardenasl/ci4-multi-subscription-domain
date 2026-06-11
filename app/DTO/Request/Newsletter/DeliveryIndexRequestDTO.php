<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'DeliveryIndexRequest')]
readonly class DeliveryIndexRequestDTO extends BaseRequestDTO
{
    public int $page;
    public int $per_page;
    public ?string $search;
    public ?int $subscriber_id;
    public ?string $email;
    public string $sort;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'page'          => 'permit_empty|is_natural_no_zero',
            'per_page'      => 'permit_empty|is_natural_no_zero|less_than[101]',
            'search'        => 'permit_empty|string|max_length[100]',
            'subscriber_id' => 'permit_empty|is_natural_no_zero',
            'email'         => 'permit_empty|string|max_length[255]',
            'sort'          => 'permit_empty|max_length[100]',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $filter = is_array($data['filter'] ?? null) ? $data['filter'] : [];

        $this->page = isset($data['page']) ? (int) $data['page'] : 1;
        $this->per_page = isset($data['per_page']) ? (int) $data['per_page'] : 20;
        $this->search = $data['search'] ?? null;
        $this->subscriber_id = $this->extractInt($data, $filter, 'subscriber_id');
        $this->email = $this->extractString($data, $filter, 'email');
        $this->sort = (string) ($data['sort'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'page' => $this->page,
            'per_page' => $this->per_page,
            'search' => $this->search,
            'sort' => $this->sort,
        ];

        if ($this->subscriber_id !== null) {
            $data['filter']['subscriber_id'] = ['eq' => $this->subscriber_id];
        }
        if ($this->email !== null) {
            $data['filter']['email'] = ['eq' => $this->email];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $filter
     */
    private function extractString(array $data, array $filter, string $key): ?string
    {
        $value = $data[$key] ?? $filter[$key] ?? null;
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $filter
     */
    private function extractInt(array $data, array $filter, string $key): ?int
    {
        $value = $data[$key] ?? $filter[$key] ?? null;
        if (! is_scalar($value) || ! is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        return $intValue > 0 ? $intValue : null;
    }
}
