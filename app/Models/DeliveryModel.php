<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\DeliveryEntity;
use dcardenasl\Ci4ApiCore\Models\BaseAuditableModel;
use dcardenasl\Ci4ApiCore\Models\Traits\Filterable;
use dcardenasl\Ci4ApiCore\Models\Traits\Searchable;

class DeliveryModel extends BaseAuditableModel
{
    use Filterable;
    use Searchable;

    protected $table = 'deliveries';
    protected $primaryKey = 'id';
    protected $returnType = DeliveryEntity::class;
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;

    protected $allowedFields = ['project_id', 'campaign_id', 'subscriber_id', 'email', 'status', 'attempts', 'last_error', 'provider_message_id', 'sent_at'];

    /** @var array<int, string> */
    protected array $searchableFields = [];

    /** @var array<int, string> */
    protected array $filterableFields = ['id'];

    /** @var array<int, string> */
    protected array $sortableFields = ['id', 'created_at'];

    protected $validationRules = [
        'project_id' => 'required|integer',
        'campaign_id' => 'required|integer',
        'subscriber_id' => 'required|integer',
        'email' => 'required|string|max_length[255]',
        'status' => 'required|string|max_length[255]',
        'attempts' => 'required|integer',
        'last_error' => 'permit_empty|string',
        'provider_message_id' => 'permit_empty|string|max_length[255]',
        'sent_at' => 'permit_empty|valid_date',
    ];
}
