<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CampaignTranslationEntity extends Entity
{
    protected $casts = [
        'id'          => 'integer',
        'campaign_id' => 'integer',
        'locale'      => 'string',
        'subject'     => 'string',
        'html_body'   => 'string',
        'text_body'   => 'string',
    ];

    protected $dates = ['created_at', 'updated_at'];
}
