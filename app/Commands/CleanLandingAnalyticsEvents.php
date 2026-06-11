<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\LandingAnalytics;

class CleanLandingAnalyticsEvents extends BaseCommand
{
    protected $group = 'Newsletter';
    protected $name = 'landing-analytics:clean';
    protected $description = 'Clean old landing analytics events based on retention policy';
    protected $usage = 'landing-analytics:clean';
    protected $arguments = [];
    protected $options = [];

    public function run(array $params = []): void
    {
        $config = new LandingAnalytics();
        $retentionDays = $config->eventRetentionDays ?? 180;
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));

        $db = \Config\Database::connect();
        $deleted = $db->table('landing_analytics_events')
            ->where('created_at <', $cutoffDate)
            ->delete();

        CLI::write("Cleaned {$deleted} analytics events older than {$retentionDays} days.", 'green');
    }
}
