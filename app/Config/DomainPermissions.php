<?php

declare(strict_types=1);

namespace Config;

/**
 * Source of truth for the permissions exposed by this domain app.
 *
 * Add an entry here, then run:
 *
 *     php spark domain:sync-permissions
 *
 * to register them in the hub. The command is idempotent — pre-existing codes
 * are left untouched.
 *
 * Permission codes use `.` as separator (NOT `:`) because CodeIgniter splits
 * filter arguments on `:` (`permission:foo:bar` would be parsed as filter=foo,
 * arg=[bar], silently dropping the rest).
 */
class DomainPermissions
{
    /**
     * @var list<array{code: string, resource: string, action: string, description?: string}>
     */
    public const PERMISSIONS = [
        ['code' => 'newsletter.projects.read',    'resource' => 'projects',    'action' => 'read',   'description' => 'Read Newsletter Projects'],
        ['code' => 'newsletter.projects.write',   'resource' => 'projects',    'action' => 'write',  'description' => 'Create or update Newsletter Project'],
        ['code' => 'newsletter.projects.delete',  'resource' => 'projects',    'action' => 'delete', 'description' => 'Delete Newsletter Project'],
        ['code' => 'newsletter.subscribers.read', 'resource' => 'subscribers', 'action' => 'read',   'description' => 'Read Newsletter Subscribers'],
        ['code' => 'newsletter.subscribers.write','resource' => 'subscribers', 'action' => 'write',  'description' => 'Create or update Newsletter Subscriber'],
        ['code' => 'newsletter.subscribers.delete','resource' => 'subscribers', 'action' => 'delete', 'description' => 'Delete Newsletter Subscriber'],
        ['code' => 'newsletter.campaigns.read',   'resource' => 'campaigns',   'action' => 'read',   'description' => 'Read Newsletter Campaigns'],
        ['code' => 'newsletter.campaigns.write',  'resource' => 'campaigns',   'action' => 'write',  'description' => 'Create or update Newsletter Campaign'],
        ['code' => 'newsletter.campaigns.delete', 'resource' => 'campaigns',   'action' => 'delete', 'description' => 'Delete Newsletter Campaign'],
        ['code' => 'newsletter.deliveries.read',  'resource' => 'deliveries',  'action' => 'read',   'description' => 'Read Newsletter Deliveries'],
        ['code' => 'newsletter.deliveries.write', 'resource' => 'deliveries',  'action' => 'write',  'description' => 'Create or update Newsletter Delivery'],
        ['code' => 'newsletter.deliveries.delete','resource' => 'deliveries',  'action' => 'delete', 'description' => 'Delete Newsletter Delivery'],
        ['code' => 'newsletter.emailtemplates.read', 'resource' => 'email-templates', 'action' => 'read', 'description' => 'Read EmailTemplates'],
        ['code' => 'newsletter.emailtemplates.write', 'resource' => 'email-templates', 'action' => 'write', 'description' => 'Create or update EmailTemplate'],
        ['code' => 'newsletter.emailtemplates.delete', 'resource' => 'email-templates', 'action' => 'delete', 'description' => 'Delete EmailTemplate'],
        ['code' => 'newsletter.analytics.read', 'resource' => 'analytics', 'action' => 'read', 'description' => 'Read Landing Analytics'],
    ];
}
