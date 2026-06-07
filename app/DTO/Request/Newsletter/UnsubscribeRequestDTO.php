<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'UnsubscribeRequest')]
readonly class UnsubscribeRequestDTO extends BaseRequestDTO
{
    #[OA\Property(description: 'token', type: 'string')]
    public string $token;

    public function rules(): array
    {
        return [
            'token' => 'required|string|min_length[16]',
        ];
    }

    protected function map(array $data): void
    {
        $this->token = (string) ($data['token'] ?? '');
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}
