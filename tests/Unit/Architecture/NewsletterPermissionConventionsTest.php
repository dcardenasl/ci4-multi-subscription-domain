<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guardrail to keep newsletter controller permission checks aligned with the
 * hub's registered domain permissions.
 */
final class NewsletterPermissionConventionsTest extends CIUnitTestCase
{
    public function testCampaignAndDeliveryControllersUseNamespacedPermissions(): void
    {
        $root = rtrim((string) ROOTPATH, DIRECTORY_SEPARATOR);
        $checks = [
            $root . '/app/Controllers/Api/V1/Newsletter/CampaignController.php' => [
                'required' => [
                    'newsletter.campaigns.read',
                    'newsletter.campaigns.write',
                    'newsletter.campaigns.delete',
                ],
                'forbidden' => [
                    'campaign.read',
                    'campaign.write',
                    'campaign.delete',
                ],
            ],
            $root . '/app/Controllers/Api/V1/Newsletter/DeliveryController.php' => [
                'required' => [
                    'newsletter.deliveries.read',
                    'newsletter.deliveries.write',
                    'newsletter.deliveries.delete',
                ],
                'forbidden' => [
                    'delivery.read',
                    'delivery.write',
                    'delivery.delete',
                ],
            ],
            $root . '/app/Controllers/Api/V1/Newsletter/SubscriberController.php' => [
                'required' => [
                    'newsletter.subscribers.read',
                    'newsletter.subscribers.write',
                    'newsletter.subscribers.delete',
                ],
                'forbidden' => [
                    'subscriber.read',
                    'subscriber.write',
                    'subscriber.delete',
                ],
            ],
        ];

        $violations = [];

        foreach ($checks as $path => $rules) {
            $relative = ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
            $source = file_get_contents($path);

            if (!is_string($source) || $source === '') {
                $violations[] = "{$relative}: unable to read controller source";
                continue;
            }

            foreach ($rules['required'] as $permission) {
                if (!str_contains($source, $permission)) {
                    $violations[] = "{$relative}: missing required permission {$permission}";
                }
            }

            foreach ($rules['forbidden'] as $permission) {
                if (str_contains($source, $permission)) {
                    $violations[] = "{$relative}: still contains legacy permission {$permission}";
                }
            }
        }

        $this->assertSame([], $violations, "Newsletter permission convention violations:\n- " . implode("\n- ", $violations));
    }
}
