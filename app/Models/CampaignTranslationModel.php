<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\CampaignTranslationEntity;
use dcardenasl\Ci4ApiCore\Models\BaseAuditableModel;
use dcardenasl\Ci4ApiCore\Models\Traits\Filterable;
use dcardenasl\Ci4ApiCore\Models\Traits\Searchable;

class CampaignTranslationModel extends BaseAuditableModel
{
    use Filterable;
    use Searchable;

    protected $table = 'campaign_translations';
    protected $primaryKey = 'id';
    protected $returnType = CampaignTranslationEntity::class;
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = ['campaign_id', 'locale', 'subject', 'html_body', 'text_body'];

    /** @var array<int, string> */
    protected array $searchableFields = ['subject'];

    /** @var array<int, string> */
    protected array $filterableFields = ['id', 'campaign_id', 'locale'];

    /** @var array<int, string> */
    protected array $sortableFields = ['id', 'campaign_id', 'locale', 'created_at'];

    protected $validationRules = [
        'campaign_id' => 'required|integer',
        'locale'      => 'required|string|max_length[10]',
        'subject'     => 'required|string|max_length[255]',
        'html_body'   => 'required|string',
        'text_body'   => 'permit_empty|string',
    ];
}
