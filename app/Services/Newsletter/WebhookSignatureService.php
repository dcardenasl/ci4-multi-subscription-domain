<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use CodeIgniter\HTTP\IncomingRequest;
use Config\NewsletterWebhooks;

/**
 * Authenticates inbound bounce/complaint webhooks per provider.
 *
 * Strategy per provider (see {@see NewsletterWebhooks}): cryptographic
 * verification when the provider credentials are configured, shared-token
 * fallback otherwise, and rejection when neither is configured.
 */
class WebhookSignatureService
{
    private const SNS_CERT_HOST_PATTERN = '/^sns\.[a-z0-9-]+\.amazonaws\.com(\.cn)?$/';

    public function __construct(private readonly NewsletterWebhooks $config)
    {
    }

    /**
     * @param array<string, mixed> $payload Decoded SNS/SES JSON payload.
     */
    public function verifySes(IncomingRequest $request, array $payload): bool
    {
        // SNS-wrapped payloads carry their own signature.
        if ($this->config->sesVerifySnsSignature && isset($payload['Signature'], $payload['SigningCertURL'])) {
            return $this->verifySnsSignature($payload);
        }

        return $this->matchesSharedToken($request, $this->config->sesSharedToken);
    }

    public function verifySendgrid(IncomingRequest $request, string $rawBody): bool
    {
        if ($this->config->sendgridPublicKey !== '') {
            $signature = $request->getHeaderLine('X-Twilio-Email-Event-Webhook-Signature');
            $timestamp = $request->getHeaderLine('X-Twilio-Email-Event-Webhook-Timestamp');

            return $this->verifySendgridSignature($rawBody, $signature, $timestamp);
        }

        return $this->matchesSharedToken($request, $this->config->sendgridSharedToken);
    }

    /**
     * @param array<string, mixed> $payload Decoded Mailgun JSON payload.
     */
    public function verifyMailgun(IncomingRequest $request, array $payload): bool
    {
        if ($this->config->mailgunSigningKey !== '') {
            $signature = $payload['signature'] ?? [];

            return is_array($signature) && $this->verifyMailgunSignature(
                (string) ($signature['timestamp'] ?? ''),
                (string) ($signature['token'] ?? ''),
                (string) ($signature['signature'] ?? ''),
            );
        }

        return $this->matchesSharedToken($request, $this->config->mailgunSharedToken);
    }

    /**
     * Validates an SNS message signature: trusted cert URL, then RSA verify
     * of the canonical string (SignatureVersion 1 = SHA1, 2 = SHA256).
     *
     * @param array<string, mixed> $payload
     */
    private function verifySnsSignature(array $payload): bool
    {
        $certUrl = (string) ($payload['SigningCertURL'] ?? '');
        if (! $this->isTrustedSnsCertUrl($certUrl)) {
            return false;
        }

        $signature = base64_decode((string) ($payload['Signature'] ?? ''), true);
        if ($signature === false || $signature === '') {
            return false;
        }

        $certificate = $this->fetchSnsCertificate($certUrl);
        if ($certificate === null) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($certificate);
        if ($publicKey === false) {
            return false;
        }

        $algorithm = ((string) ($payload['SignatureVersion'] ?? '1')) === '2'
            ? OPENSSL_ALGO_SHA256
            : OPENSSL_ALGO_SHA1;

        return openssl_verify($this->buildSnsCanonicalString($payload), $signature, $publicKey, $algorithm) === 1;
    }

    private function isTrustedSnsCertUrl(string $certUrl): bool
    {
        $parts = parse_url($certUrl);
        if ($parts === false) {
            return false;
        }

        return ($parts['scheme'] ?? '') === 'https'
            && preg_match(self::SNS_CERT_HOST_PATTERN, $parts['host'] ?? '') === 1
            && str_ends_with($parts['path'] ?? '', '.pem');
    }

    private function fetchSnsCertificate(string $certUrl): ?string
    {
        $cache    = cache();
        $cacheKey = 'sns_cert_' . md5($certUrl);

        $certificate = $cache->get($cacheKey);
        if (is_string($certificate) && $certificate !== '') {
            return $certificate;
        }

        try {
            $response = service('curlrequest')->get($certUrl, ['timeout' => 5, 'http_errors' => false]);
            if ($response->getStatusCode() !== 200) {
                return null;
            }
            $certificate = (string) $response->getBody();
        } catch (\Throwable $e) {
            log_message('error', '[Webhook] SNS cert fetch failed: {message}', ['message' => $e->getMessage()]);

            return null;
        }

        if ($certificate === '') {
            return null;
        }

        $cache->save($cacheKey, $certificate, 3600);

        return $certificate;
    }

    /**
     * Canonical string per the SNS verification spec: sorted key/value lines
     * for the fields that participate in the signature for the message type.
     *
     * @param array<string, mixed> $payload
     */
    private function buildSnsCanonicalString(array $payload): string
    {
        $type = (string) ($payload['Type'] ?? '');

        $fields = $type === 'Notification'
            ? ['Message', 'MessageId', 'Subject', 'Timestamp', 'TopicArn', 'Type']
            : ['Message', 'MessageId', 'SubscribeURL', 'Timestamp', 'Token', 'TopicArn', 'Type'];

        $canonical = '';
        foreach ($fields as $field) {
            if (array_key_exists($field, $payload)) {
                $canonical .= $field . "\n" . $payload[$field] . "\n";
            }
        }

        return $canonical;
    }

    private function verifySendgridSignature(string $rawBody, string $signature, string $timestamp): bool
    {
        if ($signature === '' || $timestamp === '' || ! $this->isFreshTimestamp($timestamp)) {
            return false;
        }

        $decodedSignature = base64_decode($signature, true);
        if ($decodedSignature === false || $decodedSignature === '') {
            return false;
        }

        $publicKeyPem = "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split($this->config->sendgridPublicKey, 64, "\n")
            . '-----END PUBLIC KEY-----';

        $publicKey = openssl_pkey_get_public($publicKeyPem);
        if ($publicKey === false) {
            return false;
        }

        return openssl_verify($timestamp . $rawBody, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    private function verifyMailgunSignature(string $timestamp, string $token, string $signature): bool
    {
        if ($timestamp === '' || $token === '' || $signature === '' || ! $this->isFreshTimestamp($timestamp)) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . $token, $this->config->mailgunSigningKey);

        return hash_equals($expected, $signature);
    }

    private function isFreshTimestamp(string $timestamp): bool
    {
        if (! ctype_digit($timestamp)) {
            return false;
        }

        return abs(time() - (int) $timestamp) <= $this->config->timestampTolerance;
    }

    private function matchesSharedToken(IncomingRequest $request, string $expectedToken): bool
    {
        if ($expectedToken === '') {
            return false;
        }

        $provided = $request->getHeaderLine('X-Webhook-Token');
        if ($provided === '') {
            $queryToken = $request->getGet('token');
            $provided   = is_string($queryToken) ? $queryToken : '';
        }

        return $provided !== '' && hash_equals($expectedToken, $provided);
    }
}
