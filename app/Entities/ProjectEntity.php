<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ProjectEntity extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'slug' => 'string',
        'project_key' => 'string',
        'is_active' => 'bool',
        'smtp_provider' => 'string',
        'smtp_host' => 'string',
        'smtp_port' => 'int',
        'smtp_user' => 'string',
        'smtp_pass_encrypted' => 'string',
        'smtp_crypto' => 'string',
        'smtp_from_name' => 'string',
        'smtp_from_email' => 'string',
        'double_opt_in_enabled' => 'bool',
        'locale_default' => 'string',
        'recaptcha_site_key' => 'string',
        'recaptcha_secret_key' => 'string',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
