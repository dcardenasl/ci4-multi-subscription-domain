<?php

declare(strict_types=1);

namespace App\DTO\Response\NewsletterAnalytics;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'LandingAnalyticsIngestResponse')]
readonly class LandingAnalyticsIngestResponseDTO implements DataTransferObjectInterface
{
    public int $accepted;
    public int $rejected;
    public int $sessions_touched;
    public int $events_inserted;

    /**
     * @var array<array<string, mixed>>
     */
    public array $errors;

    /**
     * @param array<array<string, mixed>> $errors
     */
    public function __construct(
        int $accepted,
        int $rejected,
        int $sessions_touched,
        int $events_inserted,
        array $errors = []
    ) {
        $this->accepted = $accepted;
        $this->rejected = $rejected;
        $this->sessions_touched = $sessions_touched;
        $this->events_inserted = $events_inserted;
        $this->errors = $errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'accepted' => $this->accepted,
            'rejected' => $this->rejected,
            'sessions_touched' => $this->sessions_touched,
            'events_inserted' => $this->events_inserted,
            'errors' => $this->errors,
        ];
    }
}
