<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class LandingAnalyticsEventModel extends Model
{
    protected $table = 'landing_analytics_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'analytics_session_id',
        'session_id',
        'project_id',
        'project_key',
        'event_name',
        'page_path',
        'section_key',
        'form_key',
        'occurred_at',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $validationRules = [
        'analytics_session_id' => 'required|string|max_length[64]',
        'session_id' => 'permit_empty|integer',
        'project_id' => 'required|integer',
        'project_key' => 'required|string|max_length[255]',
        'event_name' => 'required|string|max_length[64]',
        'page_path' => 'permit_empty|string|max_length[255]',
        'section_key' => 'permit_empty|string|max_length[255]',
        'form_key' => 'permit_empty|string|max_length[255]',
        'occurred_at' => 'required|valid_date',
        'metadata' => 'permit_empty|string',
    ];
}
