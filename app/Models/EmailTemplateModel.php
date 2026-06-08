<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\EmailTemplateEntity;
use dcardenasl\Ci4ApiCore\Models\BaseAuditableModel;
use dcardenasl\Ci4ApiCore\Models\Traits\Filterable;
use dcardenasl\Ci4ApiCore\Models\Traits\Searchable;

class EmailTemplateModel extends BaseAuditableModel
{
    use Filterable;
    use Searchable;

    protected $table = 'email_templates';
    protected $primaryKey = 'id';
    protected $returnType = EmailTemplateEntity::class;
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;

    protected $allowedFields = ['project_id', 'name', 'subject', 'html_body', 'text_body', 'type'];

    /** @var array<int, string> */
    protected array $searchableFields = ['name', 'subject'];

    /** @var array<int, string> */
    protected array $filterableFields = ['id'];

    /** @var array<int, string> */
    protected array $sortableFields = ['id', 'created_at', 'name', 'subject'];

    protected $validationRules = [
        'project_id' => 'required|integer',
        'name' => 'required|string|max_length[255]',
        'subject' => 'required|string|max_length[255]',
        'html_body' => 'required|string',
        'text_body' => 'permit_empty|string',
        'type' => 'required|string|max_length[255]',
    ];
}
