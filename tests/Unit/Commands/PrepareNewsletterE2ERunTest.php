<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\Commands\PrepareNewsletterE2ERun;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use dcardenasl\Ci4ApiCore\Queue\QueueManager;

/**
 * @internal
 */
final class PrepareNewsletterE2ERunTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testPrepareCreatesIsolatedRunDataAndKeepsManualRows(): void
    {
        $command = new PrepareNewsletterE2ERun(service('logger'), service('commands'));

        $first = $command->prepare('e2e-alpha', 'test-project-key', 'alpha@example.test');
        $second = $command->prepare('e2e-beta', 'test-project-key', 'beta@example.test');

        $db = Database::connect();

        $this->assertSame('e2e-alpha', $first['run_id']);
        $this->assertSame($first['project_id'], $second['project_id']);
        $this->assertNotSame($first['campaign_id'], $second['campaign_id']);
        $this->assertNotSame($first['subscriber_id'], $second['subscriber_id']);

        $this->assertSame(1, $this->countActiveCampaigns($db, 'e2e-alpha'));
        $this->assertSame(1, $this->countActiveCampaigns($db, 'e2e-beta'));
        $this->assertSame(1, $this->countActiveSubscribers($db, 'e2e-alpha'));
        $this->assertSame(1, $this->countActiveSubscribers($db, 'e2e-beta'));

        $db->table('campaigns')->insert([
            'project_id' => $first['project_id'],
            'name' => 'Manual Campaign',
            'subject' => 'Manual Subject',
            'html_body' => '<p>Manual</p>',
            'text_body' => 'Manual',
            'status' => 'draft',
            'scheduled_at' => '1000-01-01 00:00:00',
            'send_started_at' => '1000-01-01 00:00:00',
            'sent_at' => '1000-01-01 00:00:00',
            'failed_at' => '1000-01-01 00:00:00',
            'failure_reason' => '',
            'opened_count' => 0,
            'clicked_count' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $manualCampaignId = (int) $db->insertID();

        $db->table('subscribers')->insert([
            'project_id' => $first['project_id'],
            'email' => 'manual@example.test',
            'first_name' => 'Manual',
            'locale' => 'en',
            'status' => 'confirmed',
            'confirm_token' => '',
            'unsubscribe_token' => 'manual-token',
            'invitation_code' => '',
            'confirmed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $manualSubscriberId = (int) $db->insertID();

        $command->prepare('e2e-alpha', 'test-project-key', 'alpha-rerun@example.test');

        $this->assertSame(1, $this->countActiveCampaigns($db, 'e2e-alpha'));
        $this->assertSame(1, $this->countActiveCampaigns($db, 'e2e-beta'));
        $this->assertSame(1, $this->countActiveSubscribers($db, 'e2e-alpha'));
        $this->assertSame(1, $this->countActiveSubscribers($db, 'e2e-beta'));
        $this->assertSame(1, $db->table('campaigns')->where('id', $manualCampaignId)->where('deleted_at', null)->countAllResults());
        $this->assertSame(1, $db->table('subscribers')->where('id', $manualSubscriberId)->where('deleted_at', null)->countAllResults());
    }

    public function testCleanupOnlyRemovesRunRowsAndQueuedEmailJobs(): void
    {
        $command = new PrepareNewsletterE2ERun(service('logger'), service('commands'));
        $result = $command->prepare('e2e-cleanup', 'test-project-key', 'cleanup@example.test');

        $db = Database::connect();
        $deliveryId = $this->createDelivery($db, (int) $result['project_id'], (int) $result['campaign_id'], (int) $result['subscriber_id']);

        $queue = new QueueManager();
        $queue->push(\App\Queue\Jobs\SendDoubleOptInEmailJob::class, ['subscriber_id' => $result['subscriber_id']], 'emails');
        $queue->push(\App\Queue\Jobs\SendCampaignJob::class, ['delivery_id' => $deliveryId], 'emails');
        $queue->push(\App\Queue\Jobs\SendCampaignJob::class, ['delivery_id' => 999999], 'emails');

        $cleanup = $command->prepare('e2e-cleanup', 'test-project-key', null, true);

        $this->assertNull($cleanup['campaign_id']);
        $this->assertNull($cleanup['subscriber_id']);
        $this->assertSame(0, $this->countActiveCampaigns($db, 'e2e-cleanup'));
        $this->assertSame(0, $this->countActiveSubscribers($db, 'e2e-cleanup'));
        $this->assertSame(0, $db->table('deliveries')->where('id', $deliveryId)->where('deleted_at', null)->countAllResults());
        $this->assertSame(1, $db->table('jobs')->where('queue', 'emails')->countAllResults());
    }

    private function createDelivery(\CodeIgniter\Database\BaseConnection $db, int $projectId, int $campaignId, int $subscriberId): int
    {
        $db->table('deliveries')->insert([
            'project_id' => $projectId,
            'campaign_id' => $campaignId,
            'subscriber_id' => $subscriberId,
            'email' => 'cleanup@example.test',
            'status' => 'pending',
            'attempts' => 0,
            'last_error' => '',
            'provider_message_id' => '',
            'delivery_token' => 'cleanup-token',
            'opened_at' => null,
            'clicked_at' => null,
            'clicks_count' => 0,
            'sent_at' => '1000-01-01 00:00:00',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $db->insertID();
    }

    private function countActiveCampaigns(\CodeIgniter\Database\BaseConnection $db, string $runId): int
    {
        return (int) $db->table('campaigns')
            ->like('name', $runId)
            ->where('deleted_at', null)
            ->countAllResults();
    }

    private function countActiveSubscribers(\CodeIgniter\Database\BaseConnection $db, string $runId): int
    {
        return (int) $db->table('subscribers')
            ->like('first_name', $runId)
            ->where('deleted_at', null)
            ->countAllResults();
    }
}
