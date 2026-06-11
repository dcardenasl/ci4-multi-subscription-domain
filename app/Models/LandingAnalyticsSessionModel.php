<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class LandingAnalyticsSessionModel extends Model
{
    protected $table = 'landing_analytics_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'analytics_session_id',
        'project_id',
        'project_key',
        'subscriber_id',
        'visitor_locale',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'invitation_code',
        'device_type',
        'page_path',
        'started_at',
        'last_seen_at',
        'converted_at',
        'abandoned_at',
        'active_seconds',
        'event_count',
        'pageview_count',
        'section_count',
        'scroll_count',
        'form_start_count',
        'form_submit_count',
        'error_count',
        'conversion_count',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $validationRules = [
        'analytics_session_id' => 'required|string|max_length[64]',
        'project_id' => 'required|integer',
        'project_key' => 'required|string|max_length[255]',
        'subscriber_id' => 'permit_empty|integer',
        'visitor_locale' => 'permit_empty|string|max_length[10]',
        'referrer' => 'permit_empty|string|max_length[2048]',
        'utm_source' => 'permit_empty|string|max_length[255]',
        'utm_medium' => 'permit_empty|string|max_length[255]',
        'utm_campaign' => 'permit_empty|string|max_length[255]',
        'utm_content' => 'permit_empty|string|max_length[255]',
        'utm_term' => 'permit_empty|string|max_length[255]',
        'invitation_code' => 'permit_empty|string|max_length[255]',
        'device_type' => 'permit_empty|string|max_length[32]',
        'page_path' => 'permit_empty|string|max_length[255]',
        'started_at' => 'required|valid_date',
        'last_seen_at' => 'required|valid_date',
        'converted_at' => 'permit_empty|valid_date',
        'abandoned_at' => 'permit_empty|valid_date',
        'active_seconds' => 'permit_empty|integer',
        'event_count' => 'permit_empty|integer',
        'pageview_count' => 'permit_empty|integer',
        'section_count' => 'permit_empty|integer',
        'scroll_count' => 'permit_empty|integer',
        'form_start_count' => 'permit_empty|integer',
        'form_submit_count' => 'permit_empty|integer',
        'error_count' => 'permit_empty|integer',
        'conversion_count' => 'permit_empty|integer',
        'metadata' => 'permit_empty|string',
    ];
}
