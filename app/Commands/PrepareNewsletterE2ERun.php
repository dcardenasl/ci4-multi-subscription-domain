<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class PrepareNewsletterE2ERun extends BaseCommand
{
    protected $group = 'Newsletter';
    protected $name = 'newsletter:e2e:prepare';
    protected $description = 'Prepare an isolated newsletter browser E2E run using a unique run id.';
    protected $usage = 'newsletter:e2e:prepare [--run-id RUN_ID] [--project-key PROJECT_KEY] [--email EMAIL] [--cleanup-only]';

    /** @var array<string, string> */
    protected $options = [
        '--run-id'      => 'Unique E2E run id. Defaults to e2e-YYYYmmdd-HHMMSS-random.',
        '--project-key' => 'Existing project_key to use, or create if missing. Defaults to test-project-key.',
        '--email'       => 'Optional controlled inbox email to seed as confirmed for campaign delivery.',
        '--cleanup-only' => 'Only remove data for the run id; do not create seed data.',
    ];

    /**
     * @param array<int, string> $params
     */
    public function run(array $params)
    {
        $runId = $this->option($params, 'run-id', $this->generateRunId());
        $projectKey = $this->option($params, 'project-key', 'test-project-key');
        $email = $this->option($params, 'email', null);
        $cleanupOnly = $this->hasFlag($params, 'cleanup-only');

        $result = $this->prepare($runId, $projectKey, $email, $cleanupOnly);

        CLI::write('Newsletter E2E run prepared.', 'green');
        CLI::write('RUN_ID=' . $result['run_id']);
        CLI::write('PROJECT_KEY=' . $result['project_key']);
        CLI::write('PROJECT_ID=' . $result['project_id']);

        if ($cleanupOnly) {
            CLI::write('Cleanup only; no seed data created.', 'yellow');
            return EXIT_SUCCESS;
        }

        CLI::write('CAMPAIGN_ID=' . $result['campaign_id']);
        if ($result['subscriber_id'] !== null) {
            CLI::write('SUBSCRIBER_ID=' . $result['subscriber_id']);
        }

        return EXIT_SUCCESS;
    }

    /**
     * @return array{run_id: string, project_key: string, project_id: int, campaign_id: int|null, subscriber_id: int|null}
     */
    public function prepare(string $runId, string $projectKey = 'test-project-key', ?string $email = null, bool $cleanupOnly = false): array
    {
        $runId = trim($runId);
        $projectKey = trim($projectKey);

        if ($runId === '') {
            throw new \InvalidArgumentException('runId cannot be empty.');
        }

        if ($projectKey === '') {
            throw new \InvalidArgumentException('projectKey cannot be empty.');
        }

        $db = Database::connect();
        $this->cleanupRun($db, $runId);
        $projectId = $this->ensureProject($db, $projectKey, $runId);

        if ($cleanupOnly) {
            return [
                'run_id' => $runId,
                'project_key' => $projectKey,
                'project_id' => $projectId,
                'campaign_id' => null,
                'subscriber_id' => null,
            ];
        }

        $subscriberId = $email !== null && trim($email) !== ''
            ? $this->createSubscriber($db, $projectId, trim($email), $runId)
            : null;

        $campaignId = $this->createCampaign($db, $projectId, $runId);

        return [
            'run_id' => $runId,
            'project_key' => $projectKey,
            'project_id' => $projectId,
            'campaign_id' => $campaignId,
            'subscriber_id' => $subscriberId,
        ];
    }

    private function cleanupRun(\CodeIgniter\Database\BaseConnection $db, string $runId): void
    {
        $campaignIds = array_map('intval', array_column(
            $db->table('campaigns')
                ->select('id')
                ->groupStart()
                    ->like('name', $runId)
                    ->orLike('subject', $runId)
                ->groupEnd()
                ->get()
                ->getResultArray(),
            'id'
        ));

        $subscriberIds = array_map('intval', array_column(
            $db->table('subscribers')
                ->select('id')
                ->like('first_name', $runId)
                ->get()
                ->getResultArray(),
            'id'
        ));

        $deliveryIds = [];
        if ($campaignIds !== []) {
            $deliveryIds = array_map('intval', array_column(
                $db->table('deliveries')
                    ->select('id')
                    ->whereIn('campaign_id', $campaignIds)
                    ->get()
                    ->getResultArray(),
                'id'
            ));
        }

        $this->deleteQueuedEmailJobs($db, $subscriberIds, $deliveryIds);

        if ($deliveryIds !== []) {
            $db->table('deliveries')->whereIn('id', $deliveryIds)->delete();
        }

        if ($campaignIds !== []) {
            $db->table('campaigns')->whereIn('id', $campaignIds)->delete();
        }

        if ($subscriberIds !== []) {
            $db->table('subscribers')->whereIn('id', $subscriberIds)->delete();
        }
    }

    /**
     * @param list<int> $subscriberIds
     * @param list<int> $deliveryIds
     */
    private function deleteQueuedEmailJobs(\CodeIgniter\Database\BaseConnection $db, array $subscriberIds, array $deliveryIds): void
    {
        if ($subscriberIds === [] && $deliveryIds === []) {
            return;
        }

        $jobs = $db->table('jobs')
            ->select('id, payload')
            ->where('queue', 'emails')
            ->get()
            ->getResultArray();

        $deleteIds = [];
        foreach ($jobs as $job) {
            $payload = json_decode((string) $job['payload'], true);
            $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

            if (isset($data['subscriber_id']) && in_array((int) $data['subscriber_id'], $subscriberIds, true)) {
                $deleteIds[] = (int) $job['id'];
                continue;
            }

            if (isset($data['delivery_id']) && in_array((int) $data['delivery_id'], $deliveryIds, true)) {
                $deleteIds[] = (int) $job['id'];
            }
        }

        if ($deleteIds !== []) {
            $db->table('jobs')->whereIn('id', $deleteIds)->delete();
        }
    }

    private function ensureProject(\CodeIgniter\Database\BaseConnection $db, string $projectKey, string $runId): int
    {
        $project = $db->table('projects')
            ->select('id')
            ->where('project_key', $projectKey)
            ->get()
            ->getRowArray();

        if ($project !== null) {
            return (int) $project['id'];
        }

        $db->table('projects')->insert([
            'name' => 'E2E Project ' . $runId,
            'slug' => 'e2e-project-' . $this->slugPart($runId),
            'project_key' => $projectKey,
            'is_active' => 1,
            'smtp_provider' => '',
            'smtp_host' => '',
            'smtp_port' => 0,
            'smtp_user' => '',
            'smtp_pass_encrypted' => '',
            'smtp_crypto' => '',
            'smtp_from_name' => '',
            'smtp_from_email' => '',
            'double_opt_in_enabled' => 1,
            'locale_default' => 'en',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'supported_locales' => 'en,es',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $db->insertID();
    }

    private function createSubscriber(\CodeIgniter\Database\BaseConnection $db, int $projectId, string $email, string $runId): int
    {
        $db->table('subscribers')->insert([
            'project_id' => $projectId,
            'email' => $email,
            'first_name' => 'E2E ' . $runId,
            'locale' => 'en',
            'status' => 'confirmed',
            'confirm_token' => '',
            'unsubscribe_token' => bin2hex(random_bytes(16)),
            'invitation_code' => $runId,
            'confirmed_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $db->insertID();
    }

    private function createCampaign(\CodeIgniter\Database\BaseConnection $db, int $projectId, string $runId): int
    {
        $db->table('campaigns')->insert([
            'project_id' => $projectId,
            'name' => 'Campaign ' . $runId,
            'subject' => 'E2E Campaign ' . $runId,
            'html_body' => '<p>E2E campaign ' . htmlspecialchars($runId, ENT_QUOTES, 'UTF-8') . '</p><p><a href="{{unsubscribe_url}}">Unsubscribe</a></p>',
            'text_body' => 'E2E campaign ' . $runId . ' {{unsubscribe_url}}',
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

        return (int) $db->insertID();
    }

    private function generateRunId(): string
    {
        return 'e2e-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3));
    }

    private function slugPart(string $value): string
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?? '');
        return trim($slug, '-') ?: 'run';
    }

    /**
     * @param array<int, string> $params
     */
    private function option(array $params, string $name, ?string $default): ?string
    {
        $native = CLI::getOption($name);
        if ($native !== null && $native !== false) {
            return (string) $native;
        }

        $prefix = '--' . $name . '=';
        foreach ($params as $param) {
            if (str_starts_with($param, $prefix)) {
                return substr($param, strlen($prefix));
            }
        }

        return $default;
    }

    /**
     * @param array<int, string> $params
     */
    private function hasFlag(array $params, string $name): bool
    {
        if (CLI::getOption($name) !== null) {
            return true;
        }

        return in_array('--' . $name, $params, true);
    }
}
