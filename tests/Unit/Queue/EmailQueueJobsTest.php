<?php

declare(strict_types=1);

namespace Tests\Unit\Queue;

use App\Commands\CampaignDispatch;
use App\Models\CampaignModel;
use App\Models\DeliveryModel;
use App\Models\ProjectModel;
use App\Models\SubscriberModel;
use App\Queue\Jobs\SendCampaignJob;
use App\Queue\Jobs\SendDoubleOptInEmailJob;
use CodeIgniter\Email\Email;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;

/**
 * @internal
 */
final class EmailQueueJobsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the Email service
        $mockEmail = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmail->method('send')->willReturn(true);
        $mockEmail->method('printDebugger')->willReturn('Mocked SMTP success');
        Services::injectMock('email', $mockEmail);
    }

    protected function tearDown(): void
    {
        Services::reset();
        parent::tearDown();
    }

    private function createProject(array $overrides = []): int
    {
        $projectModel = model(ProjectModel::class);

        $data = array_merge([
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
            'double_opt_in_enabled' => true,
            'locale_default' => 'en',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
        ], $overrides);

        return (int) $projectModel->insert($data);
    }

    private function createSubscriber(int $projectId, array $overrides = []): int
    {
        $subscriberModel = model(SubscriberModel::class);

        $data = array_merge([
            'project_id' => $projectId,
            'email' => 'test@example.com',
            'status' => 'pending',
            'confirm_token' => 'abc123confirm',
            'unsubscribe_token' => 'abc123unsub',
            'invitation_code' => '',
            'confirmed_at' => date('Y-m-d H:i:s'),
            'unsubscribed_at' => date('Y-m-d H:i:s'),
        ], $overrides);

        return (int) $subscriberModel->insert($data);
    }

    private function createCampaign(int $projectId, array $overrides = []): int
    {
        $campaignModel = model(CampaignModel::class);

        $data = array_merge([
            'project_id' => $projectId,
            'name' => 'Weekly News',
            'subject' => 'Check this out!',
            'html_body' => 'Hello, unsubscribe here: {{unsubscribe_url}}',
            'text_body' => 'Hello text',
            'status' => 'draft',
            'scheduled_at' => date('Y-m-d H:i:s'),
            'send_started_at' => date('Y-m-d H:i:s'),
            'sent_at' => date('Y-m-d H:i:s'),
            'failed_at' => date('Y-m-d H:i:s'),
            'failure_reason' => '',
        ], $overrides);

        return (int) $campaignModel->insert($data);
    }

    public function testSendDoubleOptInEmailJob(): void
    {
        $projectId = $this->createProject();
        $subscriberId = $this->createSubscriber($projectId);

        $job = new SendDoubleOptInEmailJob([
            'subscriber_id' => $subscriberId,
        ]);

        $job->handle();

        // If no exception was thrown, the job ran successfully!
        $this->assertTrue(true);
    }

    public function testSendCampaignJob(): void
    {
        $projectId = $this->createProject([
            'smtp_host' => 'localhost',
            'smtp_port' => 1025,
            'smtp_user' => 'user',
            'smtp_pass_encrypted' => 'pass',
        ]);

        $subscriberId = $this->createSubscriber($projectId, [
            'status' => 'confirmed',
            'confirm_token' => '',
            'unsubscribe_token' => 'unsub-token-xyz',
        ]);

        $campaignId = $this->createCampaign($projectId);

        $deliveryModel = model(DeliveryModel::class);
        $deliveryId = $deliveryModel->insert([
            'project_id' => $projectId,
            'campaign_id' => $campaignId,
            'subscriber_id' => $subscriberId,
            'email' => 'test@example.com',
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => '',
            'provider_message_id' => '',
            'sent_at' => '1000-01-01 00:00:00',
        ]);

        $job = new SendCampaignJob([
            'delivery_id' => $deliveryId,
        ]);

        $job->handle();

        $updatedDelivery = $deliveryModel->find($deliveryId);
        $status = is_array($updatedDelivery) ? $updatedDelivery['status'] : $updatedDelivery->status;
        $attempts = is_array($updatedDelivery) ? $updatedDelivery['attempts'] : $updatedDelivery->attempts;
        $providerMessageId = is_array($updatedDelivery) ? $updatedDelivery['provider_message_id'] : $updatedDelivery->provider_message_id;

        $this->assertEquals('sent', $status);
        $this->assertEquals(1, $attempts);
        $this->assertEquals('', $providerMessageId);
    }

    public function testCampaignDispatchCommand(): void
    {
        $projectId = $this->createProject([
            'double_opt_in_enabled' => false,
        ]);

        $this->createSubscriber($projectId, [
            'email' => 'subscriber1@example.com',
            'status' => 'confirmed',
            'confirm_token' => '',
            'unsubscribe_token' => 'unsub1',
        ]);
        $this->createSubscriber($projectId, [
            'email' => 'subscriber2@example.com',
            'status' => 'confirmed',
            'confirm_token' => '',
            'unsubscribe_token' => 'unsub2',
        ]);

        $campaignId = $this->createCampaign($projectId, [
            'status' => 'scheduled',
            'scheduled_at' => date('Y-m-d H:i:s', time() - 3600), // scheduled 1 hour ago
        ]);

        // Run the command
        $command = new CampaignDispatch(Services::logger(), Services::commands());
        $command->run([]);

        // Check if the campaign status changed to sent
        $campaign = $campaignModel = model(CampaignModel::class)->find($campaignId);
        $status = is_array($campaign) ? $campaign['status'] : $campaign->status;
        $sendStartedAt = is_array($campaign) ? $campaign['send_started_at'] : $campaign->send_started_at;
        $sentAt = is_array($campaign) ? $campaign['sent_at'] : $campaign->sent_at;

        $this->assertEquals('sent', $status);
        $this->assertNotEmpty($sendStartedAt);
        $this->assertNotEmpty($sentAt);

        // Check if delivery rows were created
        $deliveryModel = model(DeliveryModel::class);
        $deliveries = $deliveryModel->where('campaign_id', $campaignId)->findAll();
        $this->assertCount(2, $deliveries);

        // Check if jobs were pushed to database table
        $db = \Config\Database::connect();
        $jobsCount = $db->table('jobs')->countAllResults();
        $this->assertGreaterThan(0, $jobsCount);
    }

    public function testSendCampaignJobParsesVariablesAndTranslations(): void
    {
        $projectId = $this->createProject([
            'locale_default' => 'en',
        ]);

        $subscriberId = $this->createSubscriber($projectId, [
            'status' => 'confirmed',
            'first_name' => 'Alice',
            'locale' => 'es',
            'unsubscribe_token' => 'unsub-alice-123',
        ]);

        $campaignId = $this->createCampaign($projectId, [
            'html_body' => 'Hola {{first_name}}, {{lang:Subscribers.confirm_subscription_title}}! Click {{unsubscribe_url}}',
        ]);

        $deliveryModel = model(DeliveryModel::class);
        $deliveryId = $deliveryModel->insert([
            'project_id' => $projectId,
            'campaign_id' => $campaignId,
            'subscriber_id' => $subscriberId,
            'email' => 'alice@example.com',
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => '',
            'provider_message_id' => '',
            'sent_at' => '1000-01-01 00:00:00',
        ]);

        $capturedHtml = '';
        $mockEmail = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmail->method('send')->willReturn(true);
        $mockEmail->expects($this->once())
            ->method('setMessage')
            ->with($this->callback(function ($msg) use (&$capturedHtml) {
                $capturedHtml = $msg;
                return true;
            }));
        Services::injectMock('email', $mockEmail);

        $job = new SendCampaignJob([
            'delivery_id' => $deliveryId,
        ]);
        $job->handle();

        $this->assertStringContainsString('Hola Alice', $capturedHtml);
        $landingUrl = config('Project')->landingUrl;
        $this->assertStringContainsString("{$landingUrl}/es/unsubscribe?token=unsub-alice-123", $capturedHtml);

        $expectedTitle = lang('Subscribers.confirm_subscription_title', [], 'es');
        $this->assertStringContainsString($expectedTitle, $capturedHtml);
    }

    public function testSendCampaignJobRewritesTrackedLinksAndPreservesUnsubscribeUrl(): void
    {
        $projectId = $this->createProject();

        $subscriberId = $this->createSubscriber($projectId, [
            'status' => 'confirmed',
            'unsubscribe_token' => 'unsub-track-123',
        ]);

        $campaignId = $this->createCampaign($projectId, [
            'html_body' => '<a href="https://example.com/article?x=1">Read</a> <a href="{{unsubscribe_url}}">Unsubscribe</a>',
        ]);

        $deliveryModel = model(DeliveryModel::class);
        $deliveryId = $deliveryModel->insert([
            'project_id' => $projectId,
            'campaign_id' => $campaignId,
            'subscriber_id' => $subscriberId,
            'email' => 'tracked@example.com',
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => '',
            'provider_message_id' => '',
            'delivery_token' => 'delivery-token-abc',
            'sent_at' => '1000-01-01 00:00:00',
        ]);

        $capturedHtml = '';
        $mockEmail = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmail->method('send')->willReturn(true);
        $mockEmail->expects($this->once())
            ->method('setMessage')
            ->with($this->callback(function ($msg) use (&$capturedHtml) {
                $capturedHtml = $msg;
                return true;
            }));
        Services::injectMock('email', $mockEmail);

        $job = new SendCampaignJob([
            'delivery_id' => $deliveryId,
        ]);
        $job->handle();

        $bffUrl = rtrim((string) config('Project')->bffUrl, '/');
        $landingUrl = config('Project')->landingUrl;

        $this->assertStringContainsString($bffUrl . '/api/v1/newsletter/track/click/delivery-token-abc?url=' . urlencode('https://example.com/article?x=1'), $capturedHtml);
        $this->assertStringContainsString($bffUrl . '/api/v1/newsletter/track/open/delivery-token-abc', $capturedHtml);
        $this->assertStringContainsString("{$landingUrl}/unsubscribe?token=unsub-track-123", $capturedHtml);
        $this->assertStringNotContainsString(urlencode("{$landingUrl}/unsubscribe?token=unsub-track-123"), $capturedHtml);
    }

    public function testSendCampaignJobWrapsInLayoutTemplate(): void
    {
        $projectId = $this->createProject();

        $subscriberId = $this->createSubscriber($projectId, [
            'status' => 'confirmed',
            'unsubscribe_token' => 'unsub-wrap-123',
        ]);

        // Create layout template
        $templateModel = model(\App\Models\EmailTemplateModel::class);
        $templateId = $templateModel->insert([
            'project_id' => $projectId,
            'name' => 'Fancy Layout',
            'subject' => 'Default Template Subject',
            'html_body' => '<html><body><div class="layout-header">Header</div><div class="content">{{content}}</div></body></html>',
            'text_body' => 'Template text: {{content}}',
            'type' => 'campaign_layout',
        ]);

        $campaignId = $this->createCampaign($projectId, [
            'template_id' => $templateId,
            'html_body' => '<p>My Campaign Content</p>',
            'text_body' => 'My plain campaign content',
        ]);

        $deliveryModel = model(DeliveryModel::class);
        $deliveryId = $deliveryModel->insert([
            'project_id' => $projectId,
            'campaign_id' => $campaignId,
            'subscriber_id' => $subscriberId,
            'email' => 'wrapped@example.com',
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => '',
            'provider_message_id' => '',
            'sent_at' => '1000-01-01 00:00:00',
        ]);

        $capturedHtml = '';
        $capturedText = '';
        $mockEmail = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEmail->method('send')->willReturn(true);
        $mockEmail->expects($this->once())
            ->method('setMessage')
            ->with($this->callback(function ($msg) use (&$capturedHtml) {
                $capturedHtml = $msg;
                return true;
            }));
        $mockEmail->expects($this->once())
            ->method('setAltMessage')
            ->with($this->callback(function ($msg) use (&$capturedText) {
                $capturedText = $msg;
                return true;
            }));
        Services::injectMock('email', $mockEmail);

        $job = new SendCampaignJob([
            'delivery_id' => $deliveryId,
        ]);
        $job->handle();

        $this->assertStringContainsString('<html><body><div class="layout-header">Header</div><div class="content"><p>My Campaign Content</p></div></body></html>', $capturedHtml);
        $this->assertStringContainsString('Template text: My plain campaign content', $capturedText);
    }
}
