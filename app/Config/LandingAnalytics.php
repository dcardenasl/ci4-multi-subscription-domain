<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Centralized privacy and retention defaults for landing analytics.
 */
class LandingAnalytics extends BaseConfig
{
    /**
     * Raw landing events are short-lived and should be pruned after 180 days.
     */
    public int $rawEventRetentionDays = 180;
}
