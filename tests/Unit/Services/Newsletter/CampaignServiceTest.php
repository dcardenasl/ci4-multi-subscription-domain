<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\Interfaces\Newsletter\CampaignServiceInterface;
use App\Models\CampaignModel;
use App\Models\DeliveryModel;
use App\Models\ProjectModel;
use App\Models\SubscriberModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;
use dcardenasl\Ci4ApiCore\Exceptions\ValidationException;

/**
 * Unit/Integration tests for CampaignService dispatch and cancel features.
 *
 * @internal
 */
final class CampaignServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    private CampaignServiceInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Services::campaignService(false);
    }

    private function createProject(): int
    {
        return (int) model(ProjectModel::class)->insert([
            'name' => 'Test Project',
            'slug' => 'test-project',
            'project_key' => 'test-key',
            'is_active' => true,
            'smtp_provider' => '',
            'smtp_host' => '',
            'smtp_port' => 0,
            'smtp_user' => '',
            'smtp_pass_encrypted' => '',
            'smtp_crypto' => '',
            'smtp_from_name' => '',
            'smtp_from_email' => '',
            'double_opt_in_enabled' => false,
            'locale_default' => 'en',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
        ]);
    }

    private function createSubscriber(int $projectId, string $email, string $status = 'confirmed'): int
    {
        return (int) model(SubscriberModel::class)->insert([
            'project_id' => $projectId,
            'email' => $email,
            'status' => $status,
            'confirm_token' => '',
            'unsubscribe_token' => bin2hex(random_bytes(16)),
            'invitation_code' => '',
            'confirmed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function createCampaign(int $projectId, string $status = 'draft'): int
    {
        return (int) model(CampaignModel::class)->insert([
            'project_id' => $projectId,
            'name' => 'Weekly News',
            'subject' => 'Check this out!',
            'html_body' => 'Hello!',
            'text_body' => 'Hello text',
            'status' => $status,
            'scheduled_at' => date('Y-m-d H:i:s'),
            'send_started_at' => '1000-01-01 00:00:00',
            'sent_at' => '1000-01-01 00:00:00',
            'failed_at' => '1000-01-01 00:00:00',
            'failure_reason' => '',
        ]);
    }

    private function createDelivery(
        int $projectId,
        int $campaignId,
        int $subscriberId,
        string $email,
        string $status = 'sent',
        ?string $openedAt = null,
        ?string $clickedAt = null,
    ): int {
        return (int) model(DeliveryModel::class)->insert([
            'project_id' => $projectId,
            'campaign_id' => $campaignId,
            'subscriber_id' => $subscriberId,
            'email' => $email,
            'status' => $status,
            'attempts' => 1,
            'last_error' => '',
            'provider_message_id' => '',
            'delivery_token' => bin2hex(random_bytes(16)),
            'opened_at' => $openedAt,
            'clicked_at' => $clickedAt,
            'clicks_count' => $clickedAt !== null ? 1 : 0,
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function testServiceImplementsItsInterface(): void
    {
        $this->assertInstanceOf(CampaignServiceInterface::class, $this->service);
    }

    public function testDispatchSuccessFromDraft(): void
    {
        $projectId = $this->createProject();
        $this->createSubscriber($projectId, 'sub1@example.com', 'confirmed');
        $this->createSubscriber($projectId, 'sub2@example.com', 'confirmed');
        $this->createSubscriber($projectId, 'sub3@example.com', 'pending'); // should NOT receive campaign

        $campaignId = $this->createCampaign($projectId, 'draft');

        $result = $this->service->dispatch($campaignId);

        $this->assertEquals('sent', $result->status);

        // Verify campaign model status in DB
        $campaign = model(CampaignModel::class)->find($campaignId);
        $this->assertEquals('sent', $campaign->status);
        $this->assertNotEmpty($campaign->send_started_at);
        $this->assertNotEmpty($campaign->sent_at);

        // Verify deliveries created
        $deliveries = model(DeliveryModel::class)->where('campaign_id', $campaignId)->findAll();
        $this->assertCount(2, $deliveries);

        $emails = array_map(fn ($d) => $d->email, $deliveries);
        $this->assertContains('sub1@example.com', $emails);
        $this->assertContains('sub2@example.com', $emails);
        $this->assertNotContains('sub3@example.com', $emails);
    }

    public function testDispatchFailsForSentCampaign(): void
    {
        $projectId = $this->createProject();
        $campaignId = $this->createCampaign($projectId, 'sent');

        $this->expectException(ValidationException::class);
        $this->service->dispatch($campaignId);
    }

    public function testCancelSuccessFromScheduled(): void
    {
        $projectId = $this->createProject();
        $campaignId = $this->createCampaign($projectId, 'scheduled');

        $result = $this->service->cancel($campaignId);

        $this->assertEquals('cancelled', $result->status);

        $campaign = model(CampaignModel::class)->find($campaignId);
        $this->assertEquals('cancelled', $campaign->status);
    }

    public function testCancelFailsForSentCampaign(): void
    {
        $projectId = $this->createProject();
        $campaignId = $this->createCampaign($projectId, 'sent');

        $this->expectException(ValidationException::class);
        $this->service->cancel($campaignId);
    }

    public function testStatsAggregatesDeliveryMetrics(): void
    {
        $projectId = $this->createProject();
        $campaignId = $this->createCampaign($projectId, 'sent');

        $subscriberA = $this->createSubscriber($projectId, 'a@example.com', 'confirmed');
        $subscriberB = $this->createSubscriber($projectId, 'b@example.com', 'confirmed');
        $subscriberC = $this->createSubscriber($projectId, 'c@example.com', 'confirmed');
        $subscriberD = $this->createSubscriber($projectId, 'd@example.com', 'bounced');

        $this->createDelivery($projectId, $campaignId, $subscriberA, 'a@example.com', 'sent', date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
        $this->createDelivery($projectId, $campaignId, $subscriberB, 'b@example.com', 'sent', date('Y-m-d H:i:s'), null);
        $this->createDelivery($projectId, $campaignId, $subscriberC, 'c@example.com', 'failed', null, null);
        $this->createDelivery($projectId, $campaignId, $subscriberD, 'd@example.com', 'sent', null, null);

        $result = $this->service->stats($campaignId);

        $this->assertSame($campaignId, $result->campaign_id);
        $this->assertSame(4, $result->total_deliveries);
        $this->assertSame(3, $result->sent_count);
        $this->assertSame(1, $result->failed_count);
        $this->assertSame(1, $result->bounced_count);
        $this->assertSame(2, $result->opened_count);
        $this->assertSame(1, $result->clicked_count);
        $this->assertSame(66.7, $result->open_rate);
        $this->assertSame(33.3, $result->click_rate);
    }
}
