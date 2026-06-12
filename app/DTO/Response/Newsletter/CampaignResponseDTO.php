<?php

declare(strict_types=1);

namespace App\DTO\Response\Newsletter;

use dcardenasl\Ci4ApiCore\Dto\DataTransferObjectInterface;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CampaignResponse',
    title: 'Campaign Response',
    required: ["id","project_id","name","subject","html_body","status"]
)]
final readonly class CampaignResponseDTO implements DataTransferObjectInterface
{
    public function __construct(
        #[OA\Property(description: 'Unique identifier', example: 1)]
        public int $id,
        #[OA\Property(description: 'project_id', type: 'integer')]
        public int $project_id,
        #[OA\Property(description: 'template_id', type: 'integer', nullable: true)]
        public ?int $template_id,
        #[OA\Property(description: 'name', type: 'string')]
        public string $name,
        #[OA\Property(description: 'subject', type: 'string')]
        public string $subject,
        #[OA\Property(description: 'html_body', type: 'string')]
        public string $html_body,
        #[OA\Property(description: 'text_body', type: 'string')]
        public string $text_body,
        #[OA\Property(description: 'status', type: 'string')]
        public string $status,
        #[OA\Property(description: 'scheduled_at', type: 'string', format: 'date-time')]
        public string $scheduled_at,
        #[OA\Property(description: 'send_started_at', type: 'string', format: 'date-time')]
        public string $send_started_at,
        #[OA\Property(description: 'sent_at', type: 'string', format: 'date-time')]
        public string $sent_at,
        #[OA\Property(description: 'failed_at', type: 'string', format: 'date-time')]
        public string $failed_at,
        #[OA\Property(description: 'failure_reason', type: 'string')]
        public string $failure_reason,
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
            template_id: isset($data['template_id']) && $data['template_id'] !== '' ? (int) $data['template_id'] : null,
            name: (string) ($data['name'] ?? ''),
            subject: (string) ($data['subject'] ?? ''),
            html_body: (string) ($data['html_body'] ?? ''),
            text_body: (string) ($data['text_body'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            scheduled_at: (string) ($data['scheduled_at'] ?? ''),
            send_started_at: (string) ($data['send_started_at'] ?? ''),
            sent_at: (string) ($data['sent_at'] ?? ''),
            failed_at: (string) ($data['failed_at'] ?? ''),
            failure_reason: (string) ($data['failure_reason'] ?? ''),
            createdAt: isset($data['created_at']) ? (string) $data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string) $data['updated_at'] : null,
            translations: (function () use ($data) {
                $t = $data['translations'] ?? null;
                if ($t === null && isset($data['id'])) {
                    $t = model(\App\Models\CampaignTranslationModel::class)->where('campaign_id', $data['id'])->findAll();
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
            'template_id' => $this->template_id,
            'name' => $this->name,
            'subject' => $this->subject,
            'html_body' => $this->html_body,
            'text_body' => $this->text_body,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at,
            'send_started_at' => $this->send_started_at,
            'sent_at' => $this->sent_at,
            'failed_at' => $this->failed_at,
            'failure_reason' => $this->failure_reason,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'translations' => $this->translations,
        ];
    }
}
