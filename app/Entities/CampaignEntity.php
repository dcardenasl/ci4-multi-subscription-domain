<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CampaignEntity extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'project_id' => 'int',
        'name' => 'string',
        'subject' => 'string',
        'html_body' => 'string',
        'text_body' => 'string',
        'status' => 'string',
        'scheduled_at' => 'string',
        'send_started_at' => 'string',
        'sent_at' => 'string',
        'failed_at' => 'string',
        'failure_reason' => 'string',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
