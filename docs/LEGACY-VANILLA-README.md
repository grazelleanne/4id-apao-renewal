# APAO Renewal System — Vanilla PHP

This standalone PHP project does not require Laravel or Composer. It includes
the original system UI (login, staff dashboard/PAR workspace, and all admin
screens), an empty MySQL database schema, secure login, personnel CSV import,
and personnel, inspection, and PAR PDF downloads. It does not include the
existing system's personnel data or credentials.

## Requirements

- PHP 8.2+ with `pdo_mysql`
- MySQL 8+
- Configure your web server document root as `public/`

## Setup

1. Import `database/schema.sql` into MySQL as an administrator. It creates the
   empty `apao_vanilla` database.
2. Create a least-privilege MySQL account for that database.
3. Copy `.env.example` to `.env`, then set the database credentials.
4. Make `storage/` writable by PHP and keep it outside the public web root.
5. Create the first administrator in an interactive terminal:

   ```powershell
   php bin/create-admin.php admin@example.com "System Administrator"
   ```

   Use a unique password with at least 12 characters, upper- and lowercase
   letters, a number, and a symbol.
6. Run locally from this folder:

   ```powershell
   php -S 127.0.0.1:8080 -t public public/router.php
   ```

Use HTTPS in production. Set up your mail transport before relying on password
reset email.

## Brevo transactional email

Login requires a correct password and security-question answer. Five failed
attempts lock that email/IP combination for three minutes. Login does not require
an email verification code or a working email inbox.
New passwords require at least eight characters, including uppercase, lowercase,
a number, and a symbol. Brevo remains the transport for personnel notifications.

Create and verify a sender in Brevo, then add these environment variables to
the Render web service:

```text
BREVO_API_KEY=xkeysib-your-api-key
BREVO_SENDER_EMAIL=verified-sender@example.com
BREVO_SENDER_NAME=APAO Renewal System
BREVO_REPLY_TO_EMAIL=optional-reply-address@example.com
```

Personnel notification emails are sent with Brevo's transactional email API.
The recipient address is always loaded from the personnel registration record;
the browser cannot override it. Never commit the API key to `.env` or Git.

## Importing personnel

For a personnel-only CSV, use `php bin/import-personnel.php file.csv`. The CSV
must have `item_number`, `first_name`, and `last_name` columns. Other accepted
columns are listed in the importer. The import is transactional and rolls back
on invalid rows or duplicate identifiers. Keep source data and backups
protected.

To migrate related records, carefully export and review selected tables from
the existing database, then import data in foreign-key order. Exclude session,
cache, queue, and outstanding password-reset data. Back up and verify data
before migration. The included schema is an empty import target, not an
automatic migration or a complete Laravel feature port.

## Included safeguards

Prepared PDO statements, CSRF tokens, HTTP-only/SameSite session cookies,
session ID rotation and inactivity expiry, server-side active-account and role
checks, one-use login CAPTCHA, throttling, hashed OTPs, password complexity,
audit events, output escaping, security headers, and private/no-store responses.

## Checks

```powershell
Get-ChildItem tests -Filter '*.php' | Where-Object { $_.Name -ne 'report-ui-fixture.php' } | ForEach-Object { php $_.FullName; if ($LASTEXITCODE -ne 0) { throw "Failed: $($_.Name)" } }
node tests/admin-report-ui.cjs
```

## UI templates

All templates are framework-free PHP files under `views/`. The application
does not use Laravel, Blade, Composer, or a template compilation step.

Implemented server workflows include personnel creation/edit/archive/restore,
inspection approval and staff notifications, profile updates, account creation,
account activation/deactivation, and administrator password resets. Login uses
password and CAPTCHA; password changes and account creation require email OTP.
New accounts and administrator-reset passwords require a personal password
before dashboard access. OTP delivery requires a working Brevo configuration.

Known incomplete workflows: the Forgot Password page calls recovery endpoints
that are not implemented; PAR issuance/replacement changes are currently saved
in browser localStorage rather than persisted through a server API. Scheduled
jobs and some original branded/signature PDF features are also not fully ported.
Do not describe these as completed or verified database-backed features.

The automated checks use mocks for most database and mail operations. Passing
checks do not replace testing against MySQL, live Brevo delivery, or browser
camera permissions on the deployed site.
