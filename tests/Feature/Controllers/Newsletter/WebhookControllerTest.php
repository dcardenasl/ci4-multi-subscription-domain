<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Newsletter;

use App\Models\ProjectModel;
use App\Models\SubscriberModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Feature test for WebhookController.
 *
 * @internal
 */
final class WebhookControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    private function createProjectAndSubscriber(string $email, string $status = 'confirmed'): int
    {
        $projectModel = model(ProjectModel::class);
        $subscriberModel = model(SubscriberModel::class);

        $projectId = $projectModel->insert([
            'name' => 'Webhook Test Project',
            'slug' => 'webhook-test',
            'project_key' => 'webhook-test-key',
            'is_active' => 1,
            'double_opt_in_enabled' => 0,
            'locale_default' => 'en',
            'smtp_provider' => '',
            'smtp_host' => '',
            'smtp_port' => 0,
            'smtp_user' => '',
            'smtp_pass_encrypted' => '',
            'smtp_crypto' => '',
            'smtp_from_name' => '',
            'smtp_from_email' => '',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
        ]);

        return $subscriberModel->insert([
            'project_id' => $projectId,
            'email' => $email,
            'status' => $status,
            'confirm_token' => 'conf-123',
            'unsubscribe_token' => 'unsub-123',
            'invitation_code' => '',
            'confirmed_at' => date('Y-m-d H:i:s'),
            'unsubscribed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function testSesWebhookBounce(): void
    {
        $email = 'bounce-ses@example.com';
        $subscriberId = $this->createProjectAndSubscriber($email);

        $payload = [
            'notificationType' => 'Bounce',
            'bounce' => [
                'bounceType' => 'Permanent',
                'bouncedRecipients' => [
                    ['emailAddress' => $email]
                ]
            ]
        ];

        $response = $this->withBody(json_encode($payload))->post('/api/v1/newsletter/webhooks/ses');

        $response->assertStatus(200);

        $subscriber = model(SubscriberModel::class)->find($subscriberId);
        $this->assertEquals('bounced', $subscriber->status);
        $this->assertNotEmpty($subscriber->unsubscribed_at);
    }

    public function testSesWebhookComplaint(): void
    {
        $email = 'complaint-ses@example.com';
        $subscriberId = $this->createProjectAndSubscriber($email);

        $payload = [
            'notificationType' => 'Complaint',
            'complaint' => [
                'complainedRecipients' => [
                    ['emailAddress' => $email]
                ]
            ]
        ];

        $response = $this->withBody(json_encode($payload))->post('/api/v1/newsletter/webhooks/ses');

        $response->assertStatus(200);

        $subscriber = model(SubscriberModel::class)->find($subscriberId);
        $this->assertEquals('unsubscribed', $subscriber->status);
        $this->assertNotEmpty($subscriber->unsubscribed_at);
    }

    public function testSesSnsConfirmation(): void
    {
        $payload = [
            'Type' => 'SubscriptionConfirmation',
            'SubscribeURL' => 'https://example.com/confirm-sns'
        ];

        $response = $this->withBody(json_encode($payload))->post('/api/v1/newsletter/webhooks/ses');

        $response->assertStatus(200);
        $this->assertStringContainsString('Subscription confirmed', $response->getJSON() ?: '');
    }

    public function testSendgridWebhook(): void
    {
        $emailBounce = 'bounce-sg@example.com';
        $emailSpam = 'spam-sg@example.com';

        $sub1Id = $this->createProjectAndSubscriber($emailBounce);
        $sub2Id = $this->createProjectAndSubscriber($emailSpam);

        $payload = [
            [
                'event' => 'bounce',
                'email' => $emailBounce
            ],
            [
                'event' => 'spamreport',
                'email' => $emailSpam
            ]
        ];

        $response = $this->withBody(json_encode($payload))->post('/api/v1/newsletter/webhooks/sendgrid');

        $response->assertStatus(200);

        $sub1 = model(SubscriberModel::class)->find($sub1Id);
        $sub2 = model(SubscriberModel::class)->find($sub2Id);

        $this->assertEquals('bounced', $sub1->status);
        $this->assertEquals('unsubscribed', $sub2->status);
    }

    public function testMailgunWebhookBounce(): void
    {
        $email = 'bounce-mg@example.com';
        $subscriberId = $this->createProjectAndSubscriber($email);

        $payload = [
            'event-data' => [
                'event' => 'failed',
                'severity' => 'permanent',
                'recipient' => $email
            ]
        ];

        $response = $this->withBody(json_encode($payload))->post('/api/v1/newsletter/webhooks/mailgun');

        $response->assertStatus(200);

        $subscriber = model(SubscriberModel::class)->find($subscriberId);
        $this->assertEquals('bounced', $subscriber->status);
    }

    public function testMailgunWebhookComplaint(): void
    {
        $email = 'complaint-mg@example.com';
        $subscriberId = $this->createProjectAndSubscriber($email);

        $payload = [
            'event-data' => [
                'event' => 'complained',
                'recipient' => $email
            ]
        ];

        $response = $this->withBody(json_encode($payload))->post('/api/v1/newsletter/webhooks/mailgun');

        $response->assertStatus(200);

        $subscriber = model(SubscriberModel::class)->find($subscriberId);
        $this->assertEquals('unsubscribed', $subscriber->status);
    }
}
