<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\DTO\Request\Newsletter\PublicSubscribeRequestDTO;
use App\Interfaces\Newsletter\SubscriberServiceInterface;
use App\Models\ProjectModel;
use App\Models\SubscriberModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;

/**
 * Smoke tests for SubscriberService. Extend with domain-specific assertions
 * as business rules accumulate in the service.
 *
 * @internal
 */
final class SubscriberServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testServiceImplementsItsInterface(): void
    {
        $service = Services::subscriberService(false);

        $this->assertInstanceOf(SubscriberServiceInterface::class, $service);
    }

    public function testSubscribeQueuesDoubleOptInEmailWhenEnabled(): void
    {
        model(ProjectModel::class)->insert([
            'name' => 'E2E Queue Project',
            'slug' => 'e2e-queue-project',
            'project_key' => 'e2e-queue-project',
            'is_active' => true,
            'smtp_provider' => '',
            'smtp_host' => '',
            'smtp_port' => 0,
            'smtp_user' => '',
            'smtp_pass_encrypted' => '',
            'smtp_crypto' => '',
            'smtp_from_name' => '',
            'smtp_from_email' => '',
            'double_opt_in_enabled' => true,
            'locale_default' => 'en',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
        ]);

        $service = Services::subscriberService(false);
        $result = $service->subscribe(new PublicSubscribeRequestDTO([
            'project_key' => 'e2e-queue-project',
            'email' => 'queued-double-opt-in@example.com',
            'first_name' => 'Queued',
            'locale' => 'en',
        ], Services::validation()));

        $this->assertSame('pending', $result->status);

        $subscriber = model(SubscriberModel::class)->where('email', 'queued-double-opt-in@example.com')->first();
        $this->assertNotNull($subscriber);
        $this->assertNotEmpty($subscriber->confirm_token);

        $queuedJobs = \Config\Database::connect()->table('jobs')->where('queue', 'emails')->get()->getResultArray();
        $this->assertCount(1, $queuedJobs);

        $payload = json_decode((string) $queuedJobs[0]['payload'], true);
        $this->assertSame(\App\Queue\Jobs\SendDoubleOptInEmailJob::class, $payload['job'] ?? null);
        $this->assertSame($subscriber->id, $payload['data']['subscriber_id'] ?? null);
    }
}
