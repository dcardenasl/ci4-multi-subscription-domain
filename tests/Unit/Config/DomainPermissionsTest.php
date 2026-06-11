<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Config\DomainPermissions;

final class DomainPermissionsTest extends CIUnitTestCase
{
    public function testIncludesLandingAnalyticsReadPermission(): void
    {
        $codes = array_column(DomainPermissions::PERMISSIONS, 'code');

        $this->assertContains('newsletter.analytics.read', $codes);
    }
}
