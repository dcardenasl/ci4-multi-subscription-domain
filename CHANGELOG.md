# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **`LANDING_URL` config** — confirm and unsubscribe links in outgoing emails now point to the public landing pages (`Config\Project::$landingUrl`) instead of raw BFF API endpoints.
- **`WebhookSignatureService`** — verifies provider signatures on inbound newsletter webhooks (SendGrid ECDSA, generic HMAC, shared-token fallback) driven by the new `Config\NewsletterWebhooks`; `WebhookController` rejects unsigned or invalid payloads.
- **`SyncPermissions` command** — auto-clears local and admin caches after sync in development environments (DX improvement).
- **`SyncPermissions` command** — auto-mints a temporary superadmin token from the Hub database in development, so `--admin-token` is no longer required for local runs when `hub.adminToken` is not set.
- **`CampaignService::dispatch()`** — sends a campaign to all confirmed subscribers for the project: creates delivery rows, queues `SendCampaignJob` per delivery, and transitions campaign status from `draft|scheduled` → `sending` → `sent`.
- **`CampaignService::cancel()`** — cancels a campaign in `draft` or `scheduled` status, transitioning it to `cancelled`.
- **`POST /api/v1/newsletter/campaigns/{id}/dispatch`** and **`DELETE .../cancel`** — new endpoints exposing dispatch and cancel to authenticated callers.
- **Queue operations runbook** (`docs/runbooks/05-queue-operations`) — covers worker setup, monitoring, and failure recovery for campaign dispatch jobs.
