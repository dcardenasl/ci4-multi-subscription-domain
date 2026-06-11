<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\EmailTemplateTranslationEntity;
use dcardenasl\Ci4ApiCore\Models\BaseAuditableModel;
use dcardenasl\Ci4ApiCore\Models\Traits\Filterable;
use dcardenasl\Ci4ApiCore\Models\Traits\Searchable;

class EmailTemplateTranslationModel extends BaseAuditableModel
{
    use Filterable;
    use Searchable;

    protected $table = 'email_template_translations';
    protected $primaryKey = 'id';
    protected $returnType = EmailTemplateTranslationEntity::class;
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = ['email_template_id', 'locale', 'subject', 'html_body', 'text_body'];

    /** @var array<int, string> */
    protected array $searchableFields = ['subject'];

    /** @var array<int, string> */
    protected array $filterableFields = ['id', 'email_template_id', 'locale'];

    /** @var array<int, string> */
    protected array $sortableFields = ['id', 'email_template_id', 'locale', 'created_at'];

    protected $validationRules = [
        'email_template_id' => 'required|integer',
        'locale'            => 'required|string|max_length[10]',
        'subject'           => 'required|string|max_length[255]',
        'html_body'         => 'required|string',
        'text_body'         => 'permit_empty|string',
    ];
}
