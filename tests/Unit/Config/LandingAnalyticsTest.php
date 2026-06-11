<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use CodeIgniter\Test\CIUnitTestCase;

final class LandingAnalyticsTest extends CIUnitTestCase
{
    public function testRawEventRetentionDefaultsTo180Days(): void
    {
        $config = config('LandingAnalytics');

        $this->assertSame(180, $config->rawEventRetentionDays);
    }
}
