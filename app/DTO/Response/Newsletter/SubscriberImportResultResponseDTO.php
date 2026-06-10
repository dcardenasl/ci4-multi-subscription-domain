<?php

declare(strict_types=1);

namespace App\DTO\Response\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SubscriberImportResult',
    title: 'Subscriber Import Result'
)]
final readonly class SubscriberImportResultResponseDTO implements DataTransferObjectInterface
{
    /**
     * @param list<array{row: int, email: string, reason: string}> $errors
     */
    public function __construct(
        #[OA\Property(description: 'Rows received in the request', example: 100)]
        public int $total,
        #[OA\Property(description: 'Subscribers created', example: 95)]
        public int $imported,
        #[OA\Property(description: 'Rows skipped (invalid or duplicate)', example: 5)]
        public int $skipped,
        #[OA\Property(
            description: 'Per-row skip detail. Reason codes: invalid_row, invalid_email, duplicate_in_file, invalid_status, already_subscribed, persist_failed.',
            type: 'array',
            items: new OA\Items(type: 'object')
        )]
        public array $errors,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            total: (int) ($data['total'] ?? 0),
            imported: (int) ($data['imported'] ?? 0),
            skipped: (int) ($data['skipped'] ?? 0),
            errors: array_values((array) ($data['errors'] ?? [])),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}
