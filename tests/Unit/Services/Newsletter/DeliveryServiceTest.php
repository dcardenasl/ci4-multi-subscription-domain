<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\Interfaces\Newsletter\DeliveryServiceInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Smoke tests for DeliveryService. Extend with domain-specific assertions
 * as business rules accumulate in the service.
 *
 * @internal
 */
final class DeliveryServiceTest extends CIUnitTestCase
{
    public function testServiceImplementsItsInterface(): void
    {
        $service = Services::deliveryService(false);

        $this->assertInstanceOf(DeliveryServiceInterface::class, $service);
    }
}
