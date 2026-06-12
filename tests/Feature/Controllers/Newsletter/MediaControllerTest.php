<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Newsletter;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class MediaControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    public function testUploadUnauthenticated(): void
    {
        $result = $this->post('/api/v1/newsletter/media/upload', []);

        $result->assertStatus(401);
    }
}
