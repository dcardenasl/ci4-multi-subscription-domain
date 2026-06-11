<?php

declare(strict_types=1);

namespace App\DTO\Request\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\BaseRequestDTO;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'LandingAnalyticsEventsRequest')]
readonly class LandingAnalyticsEventsRequestDTO extends BaseRequestDTO
{
    /**
     * @var array<array<string, mixed>>
     */
    public array $events;

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'events' => 'required|is_array',
            'events.*.analytics_session_id' => 'required|string|max_length[64]',
            'events.*.project_key' => 'required|string|max_length[255]',
            'events.*.event_name' => 'required|string|max_length[64]',
            'events.*.page_path' => 'permit_empty|string|max_length[255]',
            'events.*.section_key' => 'permit_empty|string|max_length[255]',
            'events.*.form_key' => 'permit_empty|string|max_length[255]',
            'events.*.occurred_at' => 'required|valid_date',
            'events.*.metadata' => 'permit_empty|string',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function map(array $data): void
    {
        $this->events = is_array($data['events'] ?? null) ? $data['events'] : [];
    }

    /**
     * @return array<string, array<array<string, mixed>>>
     */
    public function toArray(): array
    {
        return ['events' => $this->events];
    }
}
