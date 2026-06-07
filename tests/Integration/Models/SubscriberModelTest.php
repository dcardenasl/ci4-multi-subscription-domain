<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\SubscriberModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Smoke tests for SubscriberModel. Extend with persistence scenarios as
 * domain behavior solidifies.
 *
 * @internal
 */
final class SubscriberModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    public function testModelReportsCorrectTable(): void
    {
        $model = new SubscriberModel();

        $this->assertSame('subscribers', $model->getTable());
    }
}
