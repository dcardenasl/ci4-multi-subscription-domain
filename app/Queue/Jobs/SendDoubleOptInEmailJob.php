<?php

declare(strict_types=1);

namespace App\Queue\Jobs;

use App\Entities\ProjectEntity;
use App\Models\ProjectModel;
use App\Models\SubscriberModel;
use CodeIgniter\Email\Email;
use Config\Services;
use dcardenasl\Ci4ApiCore\Queue\Job;

class SendDoubleOptInEmailJob extends Job
{
    public function handle(): void
    {
        $subscriberId = $this->data['subscriber_id'] ?? null;
        if ($subscriberId === null) {
            log_message('error', '[SendDoubleOptInEmailJob] No subscriber_id provided.');
            return;
        }

        $subscriberModel = model(SubscriberModel::class);
        $subscriber = $subscriberModel->find($subscriberId);

        if ($subscriber === null) {
            log_message('warning', "[SendDoubleOptInEmailJob] Subscriber not found: {$subscriberId}");
            return;
        }

        if ($subscriber->status !== 'pending' || empty($subscriber->confirm_token)) {
            log_message('info', "[SendDoubleOptInEmailJob] Subscriber {$subscriberId} is not pending or has no confirm token.");
            return;
        }

        $projectModel = model(ProjectModel::class);
        /** @var ProjectEntity|null $project */
        $project = $projectModel->find($subscriber->project_id);

        if ($project === null) {
            log_message('error', "[SendDoubleOptInEmailJob] Project not found for subscriber {$subscriberId}: {$subscriber->project_id}");
            return;
        }

        $email = $this->getMailer($project);

        $fromEmail = !empty($project->smtp_from_email) ? $project->smtp_from_email : env('EMAIL_FROM_EMAIL', 'noreply@multisubscription.local');
        $fromName = !empty($project->smtp_from_name) ? $project->smtp_from_name : env('EMAIL_FROM_NAME', $project->name);
        $bffUrl = env('BFF_URL', 'http://localhost:8088');
        $confirmUrl = "{$bffUrl}/api/v1/newsletter/subscribers/confirm/" . $subscriber->confirm_token;

        $subscriberLocale = !empty($subscriber->locale) ? $subscriber->locale : ($project->locale_default ?? 'en');

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($subscriber->email);
        $email->setSubject(lang('Subscribers.confirm_subscription_subject', [], $subscriberLocale) ?? 'Confirm your subscription');

        $htmlBody = lang('Subscribers.confirm_subscription_body_html', [$confirmUrl], $subscriberLocale);
        $textBody = lang('Subscribers.confirm_subscription_body_text', [$confirmUrl], $subscriberLocale);

        $email->setMessage($htmlBody);
        $email->setAltMessage($textBody);

        if (!$email->send()) {
            throw new \RuntimeException('Failed to send double opt-in email: ' . $email->printDebugger(['headers', 'subject', 'body']));
        }

        log_message('info', "[SendDoubleOptInEmailJob] Double opt-in email successfully sent to {$subscriber->email}");
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
