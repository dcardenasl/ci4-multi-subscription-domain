<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class LandingAnalyticsDailyAggregateModel extends Model
{
    protected $table = 'landing_analytics_daily_aggregates';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'project_id',
        'project_key',
        'aggregate_date',
        'sessions_count',
        'pageviews_count',
        'section_views_count',
        'scroll_events_count',
        'form_starts_count',
        'form_submits_count',
        'error_events_count',
        'conversions_count',
        'confirmations_count',
        'unsubscribes_count',
        'active_seconds_total',
        'abandoned_sessions_count',
        'created_at',
        'updated_at',
    ];

    protected $validationRules = [
        'project_id' => 'required|integer',
        'project_key' => 'required|string|max_length[255]',
        'aggregate_date' => 'required|valid_date[Y-m-d]',
        'sessions_count' => 'permit_empty|integer',
        'pageviews_count' => 'permit_empty|integer',
        'section_views_count' => 'permit_empty|integer',
        'scroll_events_count' => 'permit_empty|integer',
        'form_starts_count' => 'permit_empty|integer',
        'form_submits_count' => 'permit_empty|integer',
        'error_events_count' => 'permit_empty|integer',
        'conversions_count' => 'permit_empty|integer',
        'confirmations_count' => 'permit_empty|integer',
        'unsubscribes_count' => 'permit_empty|integer',
        'active_seconds_total' => 'permit_empty|integer',
        'abandoned_sessions_count' => 'permit_empty|integer',
    ];
}
