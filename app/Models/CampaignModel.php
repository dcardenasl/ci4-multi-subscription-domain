<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\CampaignEntity;
use dcardenasl\Ci4ApiCore\Models\BaseAuditableModel;
use dcardenasl\Ci4ApiCore\Models\Traits\Filterable;
use dcardenasl\Ci4ApiCore\Models\Traits\Searchable;

class CampaignModel extends BaseAuditableModel
{
    use Filterable;
    use Searchable;

    protected $table = 'campaigns';
    protected $primaryKey = 'id';
    protected $returnType = CampaignEntity::class;
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;

    protected $allowedFields = ['project_id', 'name', 'subject', 'html_body', 'text_body', 'status', 'scheduled_at', 'send_started_at', 'sent_at', 'failed_at', 'failure_reason', 'opened_count', 'clicked_count'];

    /**
     * Intentionally empty — see note in SubscriberModel::$searchableFields.
     *
     * @var array<int, string>
     */
    protected array $searchableFields = [];

    /** @var array<int, string> */
    protected array $filterableFields = ['id', 'project_id', 'status'];

    /** @var array<int, string> */
    protected array $sortableFields = ['id', 'name', 'subject', 'project_id', 'status', 'scheduled_at', 'created_at'];

    protected $validationRules = [
        'project_id' => 'required|integer',
        'name' => 'required|string|max_length[255]',
        'subject' => 'required|string|max_length[255]',
        'html_body' => 'required|string',
        'text_body' => 'permit_empty|string',
        'status' => 'required|string|max_length[255]',
        'scheduled_at' => 'permit_empty|valid_date',
        'send_started_at' => 'permit_empty|valid_date',
        'sent_at' => 'permit_empty|valid_date',
        'failed_at' => 'permit_empty|valid_date',
        'failure_reason' => 'permit_empty|string',
        'opened_count' => 'permit_empty|integer',
        'clicked_count' => 'permit_empty|integer',
    ];
}
