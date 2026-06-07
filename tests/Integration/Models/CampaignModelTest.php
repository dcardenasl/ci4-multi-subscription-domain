<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\CampaignModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Smoke tests for CampaignModel. Extend with persistence scenarios as
 * domain behavior solidifies.
 *
 * @internal
 */
final class CampaignModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    public function testModelReportsCorrectTable(): void
    {
        $model = new CampaignModel();

        $this->assertSame('campaigns', $model->getTable());
    }
}
