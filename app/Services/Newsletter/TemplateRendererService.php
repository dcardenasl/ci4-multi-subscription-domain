<?php

declare(strict_types=1);

namespace App\Services\Newsletter;

use App\Entities\ProjectEntity;
use App\Entities\SubscriberEntity;

class TemplateRendererService
{
    /**
     * Renders a template string by replacing placeholders with subscriber and project data.
     */
    /** @param array<string, string> $additionalReplacements */
    public function render(string $content, ProjectEntity $project, SubscriberEntity $subscriber, array $additionalReplacements = []): string
    {
        $landingUrl = config('Project')->landingUrl;

        $confirmUrl = !empty($subscriber->confirm_token)
            ? "{$landingUrl}/confirm/" . $subscriber->confirm_token
            : '';

        $unsubscribeUrl = !empty($subscriber->unsubscribe_token)
            ? "{$landingUrl}/unsubscribe?token=" . $subscriber->unsubscribe_token
            : '';

        $firstName = !empty($subscriber->first_name)
            ? $subscriber->first_name
            : explode('@', $subscriber->email)[0];

        $replacements = array_merge([
            '{{first_name}}'      => $firstName,
            '{{email}}'           => $subscriber->email,
            '{{confirm_url}}'     => $confirmUrl,
            '{{unsubscribe_url}}' => $unsubscribeUrl,
            '{{project_name}}'    => $project->name,
        ], $additionalReplacements);

        $rendered = $content;
        foreach ($replacements as $search => $replace) {
            $rendered = str_replace($search, $replace, $rendered);
        }

        // Regex translation parsing for {{lang:Group.key}}
        $subscriberLocale = !empty($subscriber->locale) ? $subscriber->locale : ($project->locale_default ?? 'en');

        $rendered = preg_replace_callback('/\{\{lang:([^\}]+)\}\}/i', function ($matches) use ($subscriberLocale) {
            return lang($matches[1], [], $subscriberLocale);
        }, $rendered) ?? $rendered;

        return $rendered;
    }
}
