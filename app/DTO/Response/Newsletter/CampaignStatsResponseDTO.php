<?php

declare(strict_types=1);

namespace App\DTO\Response\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CampaignStatsResponse',
    title: 'Campaign Stats Response'
)]
final readonly class CampaignStatsResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        #[OA\Property(description: 'Campaign identifier', example: 1)]
        public int $campaign_id,
        #[OA\Property(description: 'Total deliveries created for the campaign', example: 120)]
        public int $total_deliveries,
        #[OA\Property(description: 'Deliveries marked as sent', example: 110)]
        public int $sent_count,
        #[OA\Property(description: 'Deliveries marked as failed', example: 10)]
        public int $failed_count,
        #[OA\Property(description: 'Deliveries whose subscriber bounced', example: 3)]
        public int $bounced_count,
        #[OA\Property(description: 'Deliveries opened at least once', example: 72)]
        public int $opened_count,
        #[OA\Property(description: 'Deliveries clicked at least once', example: 29)]
        public int $clicked_count,
        #[OA\Property(description: 'Open rate as percentage of sent deliveries', example: 65.5)]
        public float $open_rate,
        #[OA\Property(description: 'Click rate as percentage of sent deliveries', example: 26.4)]
        public float $click_rate,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new static(
            campaign_id: (int) ($data['campaign_id'] ?? 0),
            total_deliveries: (int) ($data['total_deliveries'] ?? 0),
            sent_count: (int) ($data['sent_count'] ?? 0),
            failed_count: (int) ($data['failed_count'] ?? 0),
            bounced_count: (int) ($data['bounced_count'] ?? 0),
            opened_count: (int) ($data['opened_count'] ?? 0),
            clicked_count: (int) ($data['clicked_count'] ?? 0),
            open_rate: (float) ($data['open_rate'] ?? 0),
            click_rate: (float) ($data['click_rate'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'campaign_id' => $this->campaign_id,
            'total_deliveries' => $this->total_deliveries,
            'sent_count' => $this->sent_count,
            'failed_count' => $this->failed_count,
            'bounced_count' => $this->bounced_count,
            'opened_count' => $this->opened_count,
            'clicked_count' => $this->clicked_count,
            'open_rate' => $this->open_rate,
            'click_rate' => $this->click_rate,
        ];
    }
}
