<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\Interfaces\Newsletter\SubscriberServiceInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Smoke tests for SubscriberService. Extend with domain-specific assertions
 * as business rules accumulate in the service.
 *
 * @internal
 */
final class SubscriberServiceTest extends CIUnitTestCase
{
    public function testServiceImplementsItsInterface(): void
    {
        $service = Services::subscriberService(false);

        $this->assertInstanceOf(SubscriberServiceInterface::class, $service);
    }
}
