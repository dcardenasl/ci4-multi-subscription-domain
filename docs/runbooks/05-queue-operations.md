# Runbook 05 - Queue workers and scheduled campaign dispatch

**Severity:** Medium (email delivery stalls, logs pile up, scheduled campaigns remain unsent) | **ETA:** 10-20 minutes | **Audit:** H-4 / TASK-20

## When to use

- Confirmation emails are not being sent.
- Campaigns stay in `scheduled` and never move to `sending` / `sent`.
- `jobs` keeps growing, or `failed_jobs` starts increasing.
- You need a repeatable dev/prod procedure for queue workers.

## Queue policy

- Queue storage is database-backed.
- Default queue names:
  - `emails` for double opt-in and campaign sends.
  - `logs` for request logging.
  - `audit` for asynchronous audit writes.
- Retry policy:
  - `QUEUE_MAX_ATTEMPTS=3`
  - `QUEUE_RETRY_AFTER=90`
- Jobs rethrow on failure so the queue manager can retry them until they hit the attempt cap.
- When a job exhausts the attempt cap, it should land in `failed_jobs` for operator review.

## Pre-flight checks

```bash
php spark migrate

mysql -e "SELECT COUNT(*) AS pending_jobs FROM jobs;" "$DB_NAME"
mysql -e "SELECT COUNT(*) AS failed_jobs FROM failed_jobs;" "$DB_NAME"
```

If the `jobs` table is empty, first trigger a flow that enqueues work:

- Subscribe a user to queue a double opt-in email.
- Dispatch a scheduled campaign to queue delivery jobs.
- Hit an endpoint with request logging enabled.

## Development workflow

Use one terminal for the app and a second terminal for the queue worker.

```bash
php spark serve --port 8090
php spark queue:work --queue=emails --once
```

Recommended while debugging:

```bash
php spark queue:work --queue=logs --once
php spark queue:work --queue=audit --once
```

For continuous processing during manual testing:

```bash
php spark queue:work --queue=emails
php spark queue:work --queue=logs
php spark queue:work --queue=audit
```

## Production workflow

Run one worker per queue under a process supervisor such as systemd, supervisor, or PM2.

### systemd worker example

```ini
[Unit]
Description=ci4 domain queue worker (emails)
After=network.target mysql.service

[Service]
Type=simple
WorkingDirectory=/var/www/ci4-multi-subscription-domain
ExecStart=/usr/bin/php spark queue:work --queue=emails --sleep=3
Restart=always
RestartSec=5
User=www-data

[Install]
WantedBy=multi-user.target
```

Create equivalent units for `logs` and `audit`.

### Cron dispatcher example

Run the dispatcher every minute so scheduled campaigns are picked up automatically:

```cron
* * * * * cd /var/www/ci4-multi-subscription-domain && /usr/bin/php spark campaign:dispatch >> writable/logs/campaign-dispatch.log 2>&1
```

## Verification

1. Trigger a queueing event.
2. Confirm the row appears in `jobs`.
3. Run `php spark queue:work --queue=emails --once`.
4. Confirm the row disappears from `jobs`.
5. Confirm the delivery record moved to `sent`, or to `failed` with a useful `last_error`.
6. If failures accumulate, inspect `failed_jobs` and the app logs before requeueing.

## Troubleshooting

- Jobs never start:
  - The worker is not running, or the queue name is wrong.
- Jobs keep retrying:
  - Check SMTP config, BFF reachability, or a bad template payload.
- Jobs hit `failed_jobs`:
  - Read `last_error`, fix the underlying issue, and requeue only after the fix is verified.
- Scheduled campaigns are not dispatching:
  - Confirm the cron entry for `campaign:dispatch` is active and the server time is correct.

