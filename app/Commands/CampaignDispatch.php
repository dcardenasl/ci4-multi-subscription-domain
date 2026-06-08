<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\CampaignModel;
use App\Models\DeliveryModel;
use App\Models\SubscriberModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class CampaignDispatch extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Campaign';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'campaign:dispatch';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Dispatch scheduled campaigns by queueing send jobs';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'campaign:dispatch';

    /**
     * Run the command
     *
     * @param array<int, string> $params
     * @return void
     */
    public function run(array $params): void
    {
        CLI::write('Searching for scheduled campaigns...', 'yellow');

        $campaignModel = model(CampaignModel::class);
        $subscriberModel = model(SubscriberModel::class);
        $deliveryModel = model(DeliveryModel::class);
        $queueManager = Services::queueManager();

        $now = date('Y-m-d H:i:s');

        // Find campaigns scheduled for now or in the past
        $campaigns = $campaignModel->where('status', 'scheduled')
            ->where('scheduled_at <=', $now)
            ->findAll();

        if (empty($campaigns)) {
            CLI::write('No scheduled campaigns found to dispatch.', 'green');
            return;
        }

        foreach ($campaigns as $campaign) {
            CLI::write("Dispatching campaign: ID {$campaign->id} - \"{$campaign->subject}\"", 'cyan');

            // 1. Mark campaign as sending
            $campaignModel->update($campaign->id, [
                'status' => 'sending',
                'send_started_at' => $now,
            ]);

            // 2. Fetch all confirmed subscribers for the project
            $subscribers = $subscriberModel->where('project_id', $campaign->project_id)
                ->where('status', 'confirmed')
                ->findAll();

            if (empty($subscribers)) {
                CLI::write("No confirmed subscribers for project ID {$campaign->project_id}. Marking campaign as sent (empty).", 'yellow');
                $campaignModel->update($campaign->id, [
                    'status' => 'sent',
                    'sent_at' => $now,
                ]);
                continue;
            }

            $queuedCount = 0;
            foreach ($subscribers as $subscriber) {
                // 3. Create delivery row
                $deliveryData = [
                    'project_id'          => $campaign->project_id,
                    'campaign_id'         => $campaign->id,
                    'subscriber_id'       => $subscriber->id,
                    'email'               => $subscriber->email,
                    'status'              => 'pending',
                    'attempts'            => 0,
                    'last_error'          => '',
                    'provider_message_id' => '',
                    'delivery_token'      => bin2hex(random_bytes(16)),
                    'sent_at'             => '1000-01-01 00:00:00',
                ];

                $deliveryId = $deliveryModel->insert($deliveryData);

                if ($deliveryId) {
                    // 4. Push job to queue
                    $queueManager->push(\App\Queue\Jobs\SendCampaignJob::class, [
                        'delivery_id' => $deliveryId,
                    ]);
                    $queuedCount++;
                }
            }

            // 5. Update campaign status to sent (all emails queued)
            $campaignModel->update($campaign->id, [
                'status' => 'sent',
                'sent_at' => $now,
            ]);

            CLI::write("Queued {$queuedCount} emails for campaign ID {$campaign->id}.", 'green');
        }
    }
}
