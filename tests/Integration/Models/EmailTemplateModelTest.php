<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\EmailTemplateModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Smoke tests for EmailTemplateModel. Extend with persistence scenarios as
 * domain behavior solidifies.
 *
 * @internal
 */
final class EmailTemplateModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    public function testModelReportsCorrectTable(): void
    {
        $model = new EmailTemplateModel();

        $this->assertSame('email_templates', $model->getTable());
    }
}
