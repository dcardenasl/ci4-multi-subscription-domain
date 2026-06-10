<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\Services\Newsletter\WebhookSignatureService;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\NewsletterWebhooks;

/**
 * @internal
 */
final class WebhookSignatureServiceTest extends CIUnitTestCase
{
    /**
     * @param array<string, string> $headers
     * @param array<string, string> $get
     */
    private function makeRequest(array $headers = [], array $get = []): IncomingRequest
    {
        $config  = new App();
        $request = new IncomingRequest($config, new SiteURI($config, 'webhook'), null, new UserAgent());

        foreach ($headers as $name => $value) {
            $request->setHeader($name, $value);
        }
        $request->setGlobal('get', $get);

        return $request;
    }

    private function makeConfig(): NewsletterWebhooks
    {
        $config = new NewsletterWebhooks();
        $config->sesVerifySnsSignature = true;
        $config->sesSharedToken        = '';
        $config->sendgridPublicKey     = '';
        $config->sendgridSharedToken   = '';
        $config->mailgunSigningKey     = '';
        $config->mailgunSharedToken    = '';
        $config->timestampTolerance    = 300;

        return $config;
    }

    // ---- Secure by default -------------------------------------------------

    public function testRejectsAllProvidersWhenNothingConfigured(): void
    {
        $service = new WebhookSignatureService($this->makeConfig());
        $request = $this->makeRequest();

        $this->assertFalse($service->verifySes($request, ['notificationType' => 'Bounce']));
        $this->assertFalse($service->verifySendgrid($request, '[]'));
        $this->assertFalse($service->verifyMailgun($request, ['event-data' => []]));
    }

    // ---- Shared token fallback ---------------------------------------------

    public function testSharedTokenAcceptedViaHeader(): void
    {
        $config = $this->makeConfig();
        $config->sesSharedToken = 'secret-token';

        $service = new WebhookSignatureService($config);
        $request = $this->makeRequest(['X-Webhook-Token' => 'secret-token']);

        $this->assertTrue($service->verifySes($request, ['notificationType' => 'Bounce']));
    }

    public function testSharedTokenAcceptedViaQueryParam(): void
    {
        $config = $this->makeConfig();
        $config->sendgridSharedToken = 'secret-token';

        $service = new WebhookSignatureService($config);
        $request = $this->makeRequest([], ['token' => 'secret-token']);

        $this->assertTrue($service->verifySendgrid($request, '[]'));
    }

    public function testSharedTokenRejectedWhenWrong(): void
    {
        $config = $this->makeConfig();
        $config->mailgunSharedToken = 'secret-token';

        $service = new WebhookSignatureService($config);
        $request = $this->makeRequest(['X-Webhook-Token' => 'wrong']);

        $this->assertFalse($service->verifyMailgun($request, []));
    }

    // ---- Mailgun HMAC ------------------------------------------------------

    public function testMailgunHmacSignatureAccepted(): void
    {
        $config = $this->makeConfig();
        $config->mailgunSigningKey = 'mg-signing-key';

        $timestamp = (string) time();
        $token     = bin2hex(random_bytes(16));
        $signature = hash_hmac('sha256', $timestamp . $token, 'mg-signing-key');

        $service = new WebhookSignatureService($config);
        $payload = ['signature' => ['timestamp' => $timestamp, 'token' => $token, 'signature' => $signature]];

        $this->assertTrue($service->verifyMailgun($this->makeRequest(), $payload));
    }

    public function testMailgunHmacSignatureRejectedWhenTampered(): void
    {
        $config = $this->makeConfig();
        $config->mailgunSigningKey = 'mg-signing-key';

        $timestamp = (string) time();
        $token     = bin2hex(random_bytes(16));
        $signature = hash_hmac('sha256', $timestamp . $token, 'another-key');

        $service = new WebhookSignatureService($config);
        $payload = ['signature' => ['timestamp' => $timestamp, 'token' => $token, 'signature' => $signature]];

        $this->assertFalse($service->verifyMailgun($this->makeRequest(), $payload));
    }

    public function testMailgunHmacSignatureRejectedWhenStale(): void
    {
        $config = $this->makeConfig();
        $config->mailgunSigningKey = 'mg-signing-key';

        $timestamp = (string) (time() - 3600);
        $token     = bin2hex(random_bytes(16));
        $signature = hash_hmac('sha256', $timestamp . $token, 'mg-signing-key');

        $service = new WebhookSignatureService($config);
        $payload = ['signature' => ['timestamp' => $timestamp, 'token' => $token, 'signature' => $signature]];

        $this->assertFalse($service->verifyMailgun($this->makeRequest(), $payload));
    }

    public function testMailgunIgnoresSharedTokenWhenSigningKeyConfigured(): void
    {
        $config = $this->makeConfig();
        $config->mailgunSigningKey  = 'mg-signing-key';
        $config->mailgunSharedToken = 'secret-token';

        $service = new WebhookSignatureService($config);
        $request = $this->makeRequest(['X-Webhook-Token' => 'secret-token']);

        // Signing key takes precedence: a payload without a valid HMAC must fail.
        $this->assertFalse($service->verifyMailgun($request, []));
    }

    // ---- SendGrid ECDSA ----------------------------------------------------

    public function testSendgridEcdsaSignatureAccepted(): void
    {
        [$publicKeyBase64, $privateKey] = $this->generateEcKeyPair();

        $config = $this->makeConfig();
        $config->sendgridPublicKey = $publicKeyBase64;

        $timestamp = (string) time();
        $rawBody   = '[{"event":"bounce","email":"a@b.c"}]';

        openssl_sign($timestamp . $rawBody, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $service = new WebhookSignatureService($config);
        $request = $this->makeRequest([
            'X-Twilio-Email-Event-Webhook-Signature' => base64_encode($signature),
            'X-Twilio-Email-Event-Webhook-Timestamp' => $timestamp,
        ]);

        $this->assertTrue($service->verifySendgrid($request, $rawBody));
    }

    public function testSendgridEcdsaSignatureRejectedWhenBodyTampered(): void
    {
        [$publicKeyBase64, $privateKey] = $this->generateEcKeyPair();

        $config = $this->makeConfig();
        $config->sendgridPublicKey = $publicKeyBase64;

        $timestamp = (string) time();

        openssl_sign($timestamp . '[{"event":"bounce"}]', $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $service = new WebhookSignatureService($config);
        $request = $this->makeRequest([
            'X-Twilio-Email-Event-Webhook-Signature' => base64_encode($signature),
            'X-Twilio-Email-Event-Webhook-Timestamp' => $timestamp,
        ]);

        $this->assertFalse($service->verifySendgrid($request, '[{"event":"spamreport"}]'));
    }

    public function testSendgridRejectedWhenSignatureHeadersMissing(): void
    {
        [$publicKeyBase64] = $this->generateEcKeyPair();

        $config = $this->makeConfig();
        $config->sendgridPublicKey = $publicKeyBase64;

        $service = new WebhookSignatureService($config);

        $this->assertFalse($service->verifySendgrid($this->makeRequest(), '[]'));
    }

    // ---- SES / SNS ---------------------------------------------------------

    public function testSesRejectsUntrustedSigningCertUrl(): void
    {
        $config = $this->makeConfig();
        $config->sesSharedToken = 'secret-token';

        $service = new WebhookSignatureService($config);
        $request = $this->makeRequest(['X-Webhook-Token' => 'secret-token']);

        // A signed payload pointing at an attacker-controlled cert must fail
        // even when the shared token matches: signature path takes precedence.
        $payload = [
            'Type'           => 'Notification',
            'Signature'      => base64_encode('fake'),
            'SigningCertURL' => 'https://evil.example.com/cert.pem',
        ];

        $this->assertFalse($service->verifySes($request, $payload));
    }

    public function testSesFallsBackToSharedTokenForUnsignedPayload(): void
    {
        $config = $this->makeConfig();
        $config->sesSharedToken = 'secret-token';

        $service = new WebhookSignatureService($config);

        $this->assertTrue($service->verifySes(
            $this->makeRequest(['X-Webhook-Token' => 'secret-token']),
            ['notificationType' => 'Bounce'],
        ));
        $this->assertFalse($service->verifySes(
            $this->makeRequest(),
            ['notificationType' => 'Bounce'],
        ));
    }

    /**
     * @return array{0: string, 1: \OpenSSLAsymmetricKey} base64 DER public key + private key
     */
    private function generateEcKeyPair(): array
    {
        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name'       => 'prime256v1',
        ]);
        $this->assertNotFalse($privateKey, 'OpenSSL must support EC keys to run this test');

        $details = openssl_pkey_get_details($privateKey);
        $this->assertIsArray($details);

        $publicKeyPem = $details['key'];
        $publicKeyBase64 = str_replace(
            ["-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----", "\n", "\r"],
            '',
            $publicKeyPem,
        );

        return [$publicKeyBase64, $privateKey];
    }
}
