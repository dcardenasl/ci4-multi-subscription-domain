<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class DeliveryEntity extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'project_id' => 'int',
        'campaign_id' => 'int',
        'subscriber_id' => 'int',
        'email' => 'string',
        'status' => 'string',
        'attempts' => 'int',
        'last_error' => 'string',
        'provider_message_id' => 'string',
        'delivery_token' => 'string',
        'opened_at' => 'string',
        'clicked_at' => 'string',
        'clicks_count' => 'int',
        'sent_at' => 'string',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
