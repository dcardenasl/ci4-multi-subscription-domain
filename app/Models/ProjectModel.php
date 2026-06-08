<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\ProjectEntity;
use dcardenasl\Ci4ApiCore\Models\BaseAuditableModel;
use dcardenasl\Ci4ApiCore\Models\Traits\Filterable;
use dcardenasl\Ci4ApiCore\Models\Traits\Searchable;

class ProjectModel extends BaseAuditableModel
{
    use Filterable;
    use Searchable;

    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $returnType = ProjectEntity::class;
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;

    protected $allowedFields = ['name', 'slug', 'project_key', 'is_active', 'smtp_provider', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass_encrypted', 'smtp_crypto', 'smtp_from_name', 'smtp_from_email', 'double_opt_in_enabled', 'double_opt_in_template_id', 'welcome_template_id', 'locale_default', 'recaptcha_site_key', 'recaptcha_secret_key'];

    /** @var array<int, string> */
    protected array $searchableFields = [];

    /** @var array<int, string> */
    protected array $filterableFields = ['id'];

    /** @var array<int, string> */
    protected array $sortableFields = ['id', 'created_at'];

    protected $validationRules = [
        'name' => 'required|string|max_length[255]',
        'slug' => 'required|string|max_length[255]',
        'project_key' => 'required|string|max_length[255]',
        'is_active' => 'required|boolean_like',
        'smtp_provider' => 'permit_empty|string|max_length[255]',
        'smtp_host' => 'permit_empty|string|max_length[255]',
        'smtp_port' => 'permit_empty|integer',
        'smtp_user' => 'permit_empty|string|max_length[255]',
        'smtp_pass_encrypted' => 'permit_empty|string',
        'smtp_crypto' => 'permit_empty|string|max_length[255]',
        'smtp_from_name' => 'permit_empty|string|max_length[255]',
        'smtp_from_email' => 'permit_empty|string|max_length[255]',
        'double_opt_in_enabled' => 'required|boolean_like',
        'double_opt_in_template_id' => 'permit_empty|integer',
        'welcome_template_id' => 'permit_empty|integer',
        'locale_default' => 'required|string|max_length[255]',
        'recaptcha_site_key' => 'permit_empty|string|max_length[255]',
        'recaptcha_secret_key' => 'permit_empty|string|max_length[255]',
    ];
}
