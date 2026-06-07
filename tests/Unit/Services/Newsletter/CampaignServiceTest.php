<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\Interfaces\Newsletter\CampaignServiceInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Smoke tests for CampaignService. Extend with domain-specific assertions
 * as business rules accumulate in the service.
 *
 * @internal
 */
final class CampaignServiceTest extends CIUnitTestCase
{
    public function testServiceImplementsItsInterface(): void
    {
        $service = Services::campaignService(false);

        $this->assertInstanceOf(CampaignServiceInterface::class, $service);
    }
}
