<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\DTO\Request\Newsletter\PublicSubscribeRequestDTO;
use App\Interfaces\Newsletter\SubscriberServiceInterface;
use App\Models\ProjectModel;
use App\Models\SubscriberModel;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use dcardenasl\Ci4ApiCore\Exceptions\ValidationException;

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

    public function testSubscribeRejectsInvalidRecaptchaWhenProjectHasSecret(): void
    {
        model(ProjectModel::class)->insert([
            'name' => 'Protected Project',
            'slug' => 'protected-project',
            'project_key' => 'protected-project',
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
            'recaptcha_site_key' => 'site-key',
            'recaptcha_secret_key' => 'secret-key',
        ]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn(json_encode(['success' => false], JSON_THROW_ON_ERROR));

        $called = [];
        $curl = $this->createMock(CURLRequest::class);
        $curl->method('post')->willReturnCallback(
            function (string $url, array $options) use (&$called, $response): ResponseInterface {
                $called[] = ['url' => $url, 'options' => $options];

                return $response;
            }
        );

        Services::injectMock('curlrequest', $curl);

        try {
            $service = Services::subscriberService(false);

            try {
                $service->subscribe(new PublicSubscribeRequestDTO([
                    'project_key' => 'protected-project',
                    'email' => 'bot@example.com',
                    'locale' => 'en',
                    'recaptcha_token' => str_repeat('r', 32),
                ], Services::validation()));

                $this->fail('Expected invalid reCAPTCHA to reject the subscription.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('recaptcha_token', $e->getErrors());
                $this->assertSame(0, model(SubscriberModel::class)->where('email', 'bot@example.com')->countAllResults());
            }
        } finally {
            Services::resetSingle('curlrequest');
        }

        $this->assertSame('https://www.google.com/recaptcha/api/siteverify', $called[0]['url'] ?? null);
    }
}
