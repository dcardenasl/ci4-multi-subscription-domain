<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Newsletter;

use App\Models\CampaignModel;
use App\Models\DeliveryModel;
use App\Models\ProjectModel;
use App\Models\SubscriberModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Feature test for TrackingController.
 *
 * @internal
 */
final class TrackingControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = true;
    protected $namespace   = 'App';

    public function testTrackingOpenAndClick(): void
    {
        $projectModel = model(ProjectModel::class);
        $campaignModel = model(CampaignModel::class);
        $subscriberModel = model(SubscriberModel::class);
        $deliveryModel = model(DeliveryModel::class);

        $projectId = $projectModel->insert([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'project_key' => 'test-proj-key',
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

        $campaignId = $campaignModel->insert([
            'project_id' => $projectId,
            'name' => 'Test Campaign',
            'subject' => 'Test Subject',
            'html_body' => '<p>Hello world!</p>',
            'text_body' => '',
            'status' => 'sent',
            'scheduled_at' => date('Y-m-d H:i:s'),
            'send_started_at' => date('Y-m-d H:i:s'),
            'sent_at' => date('Y-m-d H:i:s'),
            'failed_at' => date('Y-m-d H:i:s'),
            'failure_reason' => '',
        ]);

        $subscriberId = $subscriberModel->insert([
            'project_id' => $projectId,
            'email' => 'subscriber@test.com',
            'status' => 'confirmed',
            'unsubscribe_token' => 'unsub-token-123',
        ]);

        $token = 'test-delivery-token-abc';
        $deliveryId = $deliveryModel->insert([
            'project_id' => $projectId,
            'campaign_id' => $campaignId,
            'subscriber_id' => $subscriberId,
            'email' => 'subscriber@test.com',
            'status' => 'pending',
            'attempts' => 1,
            'last_error' => '',
            'provider_message_id' => '',
            'delivery_token' => $token,
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        // 1. Test Open tracking
        $response = $this->get("/api/v1/newsletter/track/open/{$token}");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/gif');

        // Check DB for open
        $delivery = $deliveryModel->find($deliveryId);
        $this->assertNotEmpty($delivery->opened_at);

        $campaign = $campaignModel->find($campaignId);
        $this->assertEquals(1, $campaign->opened_count);

        // 2. Test Click tracking
        $targetUrl = 'https://google.com/test';
        $response = $this->get("/api/v1/newsletter/track/click/{$token}?url=" . urlencode($targetUrl));
        $response->assertRedirect();
        $response->assertHeader('Location', $targetUrl);

        // Check DB for click
        $delivery = $deliveryModel->find($deliveryId);
        $this->assertNotEmpty($delivery->clicked_at);
        $this->assertEquals(1, $delivery->clicks_count);

        $campaign = $campaignModel->find($campaignId);
        $this->assertEquals(1, $campaign->clicked_count);
    }
}
