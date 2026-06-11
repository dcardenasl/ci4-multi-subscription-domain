<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Newsletter;

use App\Entities\ProjectEntity;
use App\Entities\SubscriberEntity;
use App\Services\Newsletter\TemplateRendererService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TemplateRendererServiceTest extends CIUnitTestCase
{
    public function testRenderReplacesPlaceholdersCorrectly(): void
    {
        $renderer = new TemplateRendererService();

        $project = new ProjectEntity([
            'name' => 'My Test Project',
            'locale_default' => 'en',
        ]);

        $subscriber = new SubscriberEntity([
            'email' => 'jane.doe@example.com',
            'first_name' => 'Jane',
            'confirm_token' => 'conf123',
            'unsubscribe_token' => 'unsub456',
        ]);

        $template = "Hello {{first_name}}! Welcome to {{project_name}}. Confirm here: {{confirm_url}} or unsubscribe here: {{unsubscribe_url}}. Your email is {{email}}.";
        $rendered = $renderer->render($template, $project, $subscriber);

        $landingUrl = config('Project')->landingUrl;
        $expected = "Hello Jane! Welcome to My Test Project. Confirm here: {$landingUrl}/confirm/conf123 or unsubscribe here: {$landingUrl}/unsubscribe?token=unsub456. Your email is jane.doe@example.com.";

        $this->assertSame($expected, $rendered);
    }

    public function testRenderReplacesPlaceholdersWithLocaleCorrectly(): void
    {
        $renderer = new TemplateRendererService();

        $project = new ProjectEntity([
            'name' => 'My Test Project',
            'locale_default' => 'es',
        ]);

        $subscriber = new SubscriberEntity([
            'email' => 'jane.doe@example.com',
            'first_name' => 'Jane',
            'confirm_token' => 'conf123',
            'unsubscribe_token' => 'unsub456',
            'locale' => 'es',
        ]);

        $template = "Confirm here: {{confirm_url}} or unsubscribe here: {{unsubscribe_url}}.";
        $rendered = $renderer->render($template, $project, $subscriber);

        $landingUrl = config('Project')->landingUrl;
        $expected = "Confirm here: {$landingUrl}/es/confirm/conf123 or unsubscribe here: {$landingUrl}/es/unsubscribe?token=unsub456.";

        $this->assertSame($expected, $rendered);
    }

    public function testRenderFallbackFirstName(): void
    {
        $renderer = new TemplateRendererService();

        $project = new ProjectEntity([
            'name' => 'My Test Project',
        ]);

        $subscriber = new SubscriberEntity([
            'email' => 'jane.doe@example.com',
            'first_name' => '',
        ]);

        $template = "Hello {{first_name}}!";
        $rendered = $renderer->render($template, $project, $subscriber);

        $this->assertSame("Hello jane.doe!", $rendered);
    }

    public function testRenderTranslationParsing(): void
    {
        $renderer = new TemplateRendererService();

        $project = new ProjectEntity([
            'name' => 'My Test Project',
            'locale_default' => 'en',
        ]);

        $subscriber = new SubscriberEntity([
            'email' => 'jane.doe@example.com',
            'locale' => 'en',
        ]);

        // Subscribers language file has confirm_subscription_subject: "Confirm your subscription"
        // Let's test language placeholder parsing
        $template = "Subject: {{lang:Subscribers.confirm_subscription_subject}}";
        $rendered = $renderer->render($template, $project, $subscriber);

        $this->assertStringContainsString('Please confirm your subscription', $rendered);
    }
}
