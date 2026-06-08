<?php

declare(strict_types=1);

namespace App\Queue\Jobs;

use App\Entities\ProjectEntity;
use App\Models\CampaignModel;
use App\Models\DeliveryModel;
use App\Models\ProjectModel;
use App\Models\SubscriberModel;
use CodeIgniter\Email\Email;
use Config\Services;
use dcardenasl\Ci4ApiCore\Queue\Job;
use Throwable;

class SendCampaignJob extends Job
{
    public function handle(): void
    {
        $deliveryId = $this->data['delivery_id'] ?? null;
        if ($deliveryId === null) {
            log_message('error', '[SendCampaignJob] No delivery_id provided.');
            return;
        }

        $deliveryModel = model(DeliveryModel::class);
        $delivery = $deliveryModel->find($deliveryId);

        if ($delivery === null) {
            log_message('warning', "[SendCampaignJob] Delivery not found: {$deliveryId}");
            return;
        }

        $subscriberModel = model(SubscriberModel::class);
        $subscriber = $subscriberModel->find($delivery->subscriber_id);

        $campaignModel = model(CampaignModel::class);
        $campaign = $campaignModel->find($delivery->campaign_id);

        $projectModel = model(ProjectModel::class);
        /** @var ProjectEntity|null $project */
        $project = $projectModel->find($delivery->project_id);

        if ($subscriber === null || $campaign === null || $project === null) {
            $error = 'Missing dependencies for delivery: ' . json_encode([
                'subscriber' => $subscriber !== null,
                'campaign' => $campaign !== null,
                'project' => $project !== null,
            ]);
            log_message('error', "[SendCampaignJob] {$error}");

            $deliveryModel->update($deliveryId, [
                'status' => 'failed',
                'attempts' => $delivery->attempts + 1,
                'last_error' => $error,
            ]);
            return;
        }

        // Increment attempts first
        $newAttempts = $delivery->attempts + 1;
        $deliveryModel->update($deliveryId, [
            'attempts' => $newAttempts,
        ]);

        try {
            $email = $this->getMailer($project);

            $fromEmail = !empty($project->smtp_from_email) ? $project->smtp_from_email : env('EMAIL_FROM_EMAIL', 'noreply@multisubscription.local');
            $fromName = !empty($project->smtp_from_name) ? $project->smtp_from_name : env('EMAIL_FROM_NAME', $project->name);

            $email->setFrom($fromEmail, $fromName);
            $email->setTo($subscriber->email);
            $email->setSubject($campaign->subject);

            $renderer = new \App\Services\Newsletter\TemplateRendererService();
            $htmlBody = $renderer->render($campaign->html_body, $project, $subscriber);
            $textBody = $campaign->text_body ? $renderer->render($campaign->text_body, $project, $subscriber) : '';

            // Click tracking URL rewriting
            if (!empty($delivery->delivery_token)) {
                $htmlBody = preg_replace_callback('/href="([^"]+)"/i', function ($matches) use ($bffUrl, $delivery) {
                    $url = $matches[1];
                    if (preg_match('/^https?:\/\//i', $url) && !str_contains($url, '/unsubscribe/')) {
                        return 'href="' . $bffUrl . '/api/v1/newsletter/track/click/' . $delivery->delivery_token . '?url=' . urlencode($url) . '"';
                    }
                    return $matches[0];
                }, $htmlBody);

                // Open tracking transparent pixel
                $openTrackingUrl = $bffUrl . '/api/v1/newsletter/track/open/' . $delivery->delivery_token;
                $htmlBody .= '<img src="' . $openTrackingUrl . '" width="1" height="1" style="display:none;" alt="" />';
            }

            $email->setMessage($htmlBody);
            if ($textBody !== '') {
                $email->setAltMessage($textBody);
            }

            if (!$email->send()) {
                $debugger = $email->printDebugger(['headers', 'subject', 'body']);
                throw new \RuntimeException("SMTP send failed: " . $debugger);
            }

            // Update delivery as sent
            $deliveryModel->update($deliveryId, [
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
                'provider_message_id' => '',
            ]);

            log_message('info', "[SendCampaignJob] Campaign email successfully sent to {$subscriber->email} for delivery {$deliveryId}");

        } catch (Throwable $e) {
            log_message('error', "[SendCampaignJob] Error sending campaign email for delivery {$deliveryId}: " . $e->getMessage());

            $deliveryModel->update($deliveryId, [
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ]);

            // Re-throw so the QueueManager knows the job failed and schedules retry
            throw $e;
        }
    }

    protected function getMailer(ProjectEntity $project): Email
    {
        $email = Services::email();

        if (empty($project->smtp_host)) {
            $config = [
                'protocol'     => 'smtp',
                'SMTPHost'     => env('EMAIL_SMTP_HOST', 'localhost'),
                'SMTPPort'     => (int) env('EMAIL_SMTP_PORT', 1025),
                'SMTPUser'     => env('EMAIL_SMTP_USER', ''),
                'SMTPPass'     => env('EMAIL_SMTP_PASS', ''),
                'SMTPCrypto'   => env('EMAIL_SMTP_CRYPTO', ''),
                'mailType'     => 'html',
                'charset'      => 'utf-8',
                'wordWrap'     => true,
                'validate'     => true,
            ];
        } else {
            $config = [
                'protocol'     => 'smtp',
                'SMTPHost'     => $project->smtp_host,
                'SMTPPort'     => (int) $project->smtp_port,
                'SMTPUser'     => $project->smtp_user,
                'SMTPPass'     => $project->getSmtpPassDecrypted(),
                'SMTPCrypto'   => $project->smtp_crypto,
                'mailType'     => 'html',
                'charset'      => 'utf-8',
                'wordWrap'     => true,
                'validate'     => true,
            ];
        }

        $email->initialize($config);
        return $email;
    }
}
