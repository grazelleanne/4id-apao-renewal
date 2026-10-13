# Deploy the updated system

Deploy the main branch using the repository Dockerfile. It includes imports (CSV, XLSX and XLSM), automated renewal reminders, notification history, personnel detail fields, PAR workflow and audit UI/export/retention changes.

## Before deploying

Back up the production database. Configure APP_ENV=production, APP_DEBUG=false, APP_KEY, APP_URL, database credentials and the verified Brevo sender/API settings in the host's secret environment settings. Preserve the existing production APP_KEY. Never upload the local .env to Git.

## Schema update

Run once against the production database after backup:

    php artisan migrate --force

Migrations add the required tables and fields; they do not copy local personnel data. Transfer the current local database with a private SQL export/import if the deployment must contain the same personnel, dates, emails and PAR records. Review the destination data before importing; a full database restore can replace existing records.

## Automatic jobs

Keep a scheduler process running with the same production environment and database:

    php artisan schedule:work

Alternatively, configure server cron to run php artisan schedule:run every minute. On Render, configure an appropriate scheduled service or persistent scheduler process; the Apache web container alone does not execute scheduled jobs. Reminders run at 08:00 and audit cleanup at 03:00 Asia/Manila. Logs older than 90 days are removed.

## Verification

    php artisan migrate:status
    php artisan schedule:list
    php artisan apao:renewal-reminders --dry-run
    php artisan apao:prune-audit-logs --dry-run

Check admin/staff login, report imports, personnel details, PAR issuance and CSV export in staging. Verify Brevo delivery with a controlled test recipient. File cache/session storage supports a single instance; multiple instances require shared storage. Docker build and live hosting verification must run on the deployment host when unavailable locally.
