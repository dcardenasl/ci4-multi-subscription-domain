<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\Interfaces\Newsletter\ProjectServiceInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * Smoke tests for ProjectService. Extend with domain-specific assertions
 * as business rules accumulate in the service.
 *
 * @internal
 */
final class ProjectServiceTest extends CIUnitTestCase
{
    public function testServiceImplementsItsInterface(): void
    {
        $service = Services::projectService(false);

        $this->assertInstanceOf(ProjectServiceInterface::class, $service);
    }
}
