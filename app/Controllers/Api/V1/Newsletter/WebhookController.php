<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1\Newsletter;

use App\Services\Newsletter\WebhookSignatureService;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use dcardenasl\Ci4ApiCore\Exceptions\AuthenticationException;
use dcardenasl\Ci4ApiCore\Http\ApiController;

class WebhookController extends ApiController
{
    protected function resolveDefaultService(): object
    {
        return Services::subscriberService();
    }

    private function signatureService(): WebhookSignatureService
    {
        return Services::webhookSignatureService();
    }

    private function incomingRequest(): IncomingRequest
    {
        assert($this->request instanceof IncomingRequest);

        return $this->request;
    }

    public function ses(): ResponseInterface
    {
        return $this->handleRequest(function (): array {
            $rawBody = $this->request->getBody() ?? '';
            $data = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return ['success' => false, 'message' => 'Invalid JSON payload'];
            }

            if (! $this->signatureService()->verifySes($this->incomingRequest(), $data)) {
                throw new AuthenticationException(lang('Api.invalidWebhookSignature'));
            }

            // AWS SNS Subscription Confirmation
            if (isset($data['Type']) && $data['Type'] === 'SubscriptionConfirmation') {
                if (isset($data['SubscribeURL'])) {
                    // Automatically confirm SNS subscription
                    @file_get_contents($data['SubscribeURL']);
                }
                return ['success' => true, 'message' => 'Subscription confirmed'];
            }

            // Soportar tanto payload directo de SES como anidado en SNS (campo Message)
            $payload = $data;
            if (isset($data['Type']) && $data['Type'] === 'Notification' && isset($data['Message'])) {
                $decodedMessage = json_decode($data['Message'], true);
                if (is_array($decodedMessage)) {
                    $payload = $decodedMessage;
                }
            }

            $subscriberService = Services::subscriberService();

            // Procesar Rebotes (Bounce)
            if (isset($payload['notificationType']) && $payload['notificationType'] === 'Bounce') {
                $bounce = $payload['bounce'] ?? [];
                $bounceType = $bounce['bounceType'] ?? '';

                if ($bounceType === 'Permanent') {
                    $recipients = $bounce['bouncedRecipients'] ?? [];
                    foreach ($recipients as $recipient) {
                        if (isset($recipient['emailAddress'])) {
                            $subscriberService->handleBounce($recipient['emailAddress']);
                        }
                    }
                }
            }

            // Procesar Quejas (Complaint)
            if (isset($payload['notificationType']) && $payload['notificationType'] === 'Complaint') {
                $complaint = $payload['complaint'] ?? [];
                $recipients = $complaint['complainedRecipients'] ?? [];
                foreach ($recipients as $recipient) {
                    if (isset($recipient['emailAddress'])) {
                        $subscriberService->handleComplaint($recipient['emailAddress']);
                    }
                }
            }

            return ['success' => true];
        });
    }

    public function sendgrid(): ResponseInterface
    {
        return $this->handleRequest(function (): array {
            $rawBody = $this->request->getBody() ?? '';

            if (! $this->signatureService()->verifySendgrid($this->incomingRequest(), $rawBody)) {
                throw new AuthenticationException(lang('Api.invalidWebhookSignature'));
            }

            $events = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($events)) {
                return ['success' => false, 'message' => 'Invalid JSON payload'];
            }

            $subscriberService = Services::subscriberService();

            foreach ($events as $event) {
                if (!is_array($event) || empty($event['email'])) {
                    continue;
                }

                $eventType = $event['event'] ?? '';
                $email = $event['email'];

                if ($eventType === 'bounce') {
                    $subscriberService->handleBounce($email);
                } elseif ($eventType === 'spamreport') {
                    $subscriberService->handleComplaint($email);
                }
            }

            return ['success' => true];
        });
    }

    public function mailgun(): ResponseInterface
    {
        return $this->handleRequest(function (): array {
            $rawBody = $this->request->getBody() ?? '';
            $data = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return ['success' => false, 'message' => 'Invalid JSON payload'];
            }

            if (! $this->signatureService()->verifyMailgun($this->incomingRequest(), $data)) {
                throw new AuthenticationException(lang('Api.invalidWebhookSignature'));
            }

            $eventData = $data['event-data'] ?? [];
            if (empty($eventData)) {
                return ['success' => false, 'message' => 'Missing event-data'];
            }

            $subscriberService = Services::subscriberService();

            $event = $eventData['event'] ?? '';
            $email = $eventData['recipient'] ?? '';

            if (empty($email) && isset($eventData['message']['headers']['to'])) {
                $email = $eventData['message']['headers']['to'];
            }

            if (!empty($email)) {
                if ($event === 'failed') {
                    $severity = $eventData['severity'] ?? '';
                    if ($severity === 'permanent') {
                        $subscriberService->handleBounce($email);
                    }
                } elseif ($event === 'complained') {
                    $subscriberService->handleComplaint($email);
                }
            }

            return ['success' => true];
        });
    }
}
