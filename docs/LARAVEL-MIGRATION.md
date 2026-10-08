# Laravel migration

Work is isolated on `migration/laravel`. Application migration is implemented; verify a staging deployment before replacing the live service.

## Implemented

- Laravel 13 application, native routes, controllers, middleware and Blade rendering.
- Eloquent models and Laravel database connections; existing prepared SQL business rules are retained in services.
- Laravel sessions, CSRF protection, authentication guard synchronization and account access checks.
- Migrations adopt existing tables without deleting personnel or user records.
- Existing personnel, inspection, notifications, archive, report and account screens are migrated.
- Docker installs Composer dependencies and required PHP extensions.
- Obsolete vanilla `src/`, duplicate `views/`, standalone router and legacy tests are removed; their history remains in Git.
- PAR issuance, updates and replacement history now persist in MySQL.
- Approved password recovery uses hashed OTPs, expiration, rate limits, session binding and one-time reset.

## Local setup

Use PHP 8.4 or newer with fileinfo, mbstring and pdo_mysql enabled. XAMPP PHP 8.0 cannot run this application.

```powershell
& "C:\php8.5\php.exe" -d extension=fileinfo "C:\ProgramData\ComposerSetup\bin\composer.phar" install
& "C:\php8.5\php.exe" -d extension=fileinfo artisan key:generate
& "C:\php8.5\php.exe" -d extension=fileinfo artisan migrate
& "C:\php8.5\php.exe" -d extension=fileinfo artisan serve --host=127.0.0.1 --port=8082
```

Preserve existing database credentials in `.env`. Generate the application key once and preserve it. Never commit `.env`.

For a fresh installation, create an administrator with `php artisan apao:create-admin email@example.com "Administrator Name"`.

## Deployment requirements

Set `APP_KEY`, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`, database credentials and the existing database CA path in Render. Keep `SESSION_DRIVER=file`, `CACHE_STORE=file`, and `QUEUE_CONNECTION=sync` for a single instance. Multiple instances require shared session/cache storage.

Back up the deployed database before running `php artisan migrate --force`. The baseline skips existing tables; it does not repair missing columns in older databases. Check their schema before rollout. Deployment does not automatically run migrations.

## Verified

`artisan route:list`, `artisan view:cache`, Composer validation, PHP lint (59 files), and parsing all seven inline staff dashboard scripts pass. Ten Laravel feature tests pass with 62 assertions. Tests use the separate local `apao_laravel_test` database and roll back their records. They cover login/logout, page rendering, roles, temporary passwords, staff submission, approval, printed renewal dates, notification removal, PAR history, new-user OTP delivery and password recovery. Email tests simulate Brevo delivery and do not send real emails.

## Remaining before rollout

- Verify the Docker build in CI or Render staging; Docker is not installed on this workstation.
- Compare paper layout in browser print preview and check real email delivery using the verified sender.
- Check older deployment schema compatibility and back up its data before applying migrations.
- PAR records and activity summaries are database-backed. Existing browser-only PAR overrides are not imported automatically; issue or update those records through the migrated workspace.
- Review and push the migration branch, then deploy after staging checks pass.

This migration has not been pushed or deployed. Existing production data has not been modified.
