<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Credentials used to authenticate inbound bounce/complaint webhooks.
 *
 * Per provider, verification picks the strongest configured mechanism:
 *
 *  - SES:      SNS message signature (cert chain + signature) when the payload
 *              carries one, otherwise the shared token.
 *  - SendGrid: Signed Event Webhooks (ECDSA public key, base64 DER) when
 *              configured, otherwise the shared token.
 *  - Mailgun:  HMAC-SHA256 with the signing key when configured, otherwise
 *              the shared token.
 *
 * Shared tokens are matched against the `X-Webhook-Token` header or a
 * `?token=` query param (constant-time compare). A provider with no
 * mechanism configured rejects every request — secure by default.
 */
class NewsletterWebhooks extends BaseConfig
{
    public bool $sesVerifySnsSignature = true;
    public string $sesSharedToken = '';

    /** Base64-encoded DER public key from the SendGrid Signed Event Webhook settings. */
    public string $sendgridPublicKey = '';
    public string $sendgridSharedToken = '';

    /** HTTP webhook signing key from the Mailgun dashboard. */
    public string $mailgunSigningKey = '';
    public string $mailgunSharedToken = '';

    /** Max accepted clock skew (seconds) for timestamped signatures (SendGrid/Mailgun). */
    public int $timestampTolerance = 300;

    public function __construct()
    {
        parent::__construct();

        $this->sesVerifySnsSignature = filter_var(env('newsletter.webhooks.sesVerifySnsSignature', true), FILTER_VALIDATE_BOOL);
        $this->sesSharedToken        = (string) env('newsletter.webhooks.sesSharedToken', '');
        $this->sendgridPublicKey     = (string) env('newsletter.webhooks.sendgridPublicKey', '');
        $this->sendgridSharedToken   = (string) env('newsletter.webhooks.sendgridSharedToken', '');
        $this->mailgunSigningKey     = (string) env('newsletter.webhooks.mailgunSigningKey', '');
        $this->mailgunSharedToken    = (string) env('newsletter.webhooks.mailgunSharedToken', '');
        $this->timestampTolerance    = (int) env('newsletter.webhooks.timestampTolerance', 300);
    }
}
