<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class EmailTemplateEntity extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'project_id' => 'int',
        'name' => 'string',
        'subject' => 'string',
        'html_body' => 'string',
        'text_body' => 'string',
        'type' => 'string',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
