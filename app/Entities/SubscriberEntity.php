<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class SubscriberEntity extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'project_id' => 'int',
        'email' => 'string',
        'status' => 'string',
        'confirm_token' => 'string',
        'unsubscribe_token' => 'string',
        'invitation_code' => 'string',
        'confirmed_at' => 'string',
        'unsubscribed_at' => 'string',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
