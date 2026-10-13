# Capstone 2 system revisions

Changes apply to the Laravel application in this folder, not the older vanilla PHP parent folder. Existing personnel, inspection, PAR, renewal, authentication and user-management workflows remain available.

## Features

- Daily Gmail-compatible SMTP reminders at 08:00 Asia/Manila, exactly 30, 15, 7 and 0 days before expiration. Checks the original personnel pistol validity and additional firearm expiration dates; excludes archived personnel. Each email contains the personnel name, firearm, serial, ATP number, expiration date and action.
- Notification history for automated and manual personnel email, with actor/recipient, subject, body, send time and outcome. Existing manual Brevo delivery remains available when SMTP is not selected.
- Audit module fields and before/after values for personnel, firearm, ammunition and account changes, with passwords, OTPs and images omitted. Existing login/logout and PDF events continue through the same writer. Browser report exports record an export request; this does not prove the browser saved the file.
- Admin firearm/ammunition CRUD with server validation and personnel foreign keys. Staff retain their existing limited registration, PAR and manual notification permissions. Chief accounts use the existing admin or super_admin role.
- Personnel ATP field and backend validation for required values, valid email domain, birth date, serial format and duplicates. Updates reject future birth dates. The exact duplicate ATP message is: “This ATP Serial Number already exists.”
- Personnel, firearm and ammunition CSV/XLSX imports, downloadable CSV templates, per-row validation, duplicate detection, summaries and persistent import history. A valid row and its audit event save together; database/system errors roll back the entire upload. Invalid/duplicate rows are skipped without overwriting existing data.
- Existing password hashing/verification, prepared queries, native Laravel CSRF, active-account/session checks and role middleware are retained. New text inputs are trimmed and HTML tags removed; output is escaped. MySQL advisory locking serializes application record writes so validation across original and additional firearm records cannot race.

## Database migration

Run the application migrations after backing up your application database:

    php artisan migrate

New migration: database/migrations/2026_10_12_000001_add_monitoring_and_import_records.php

It adds:

- audit_logs.module; existing audit_logs.old_values/new_values and user foreign key are reused.
- personnel.atp_serial_number, nullable for existing records and uniquely indexed.
- firearms: personnel foreign key, unique ATP and firearm serial, nomenclature, type, expiration.
- ammunition: personnel foreign key, lot, caliber, quantity; one unique lot/caliber per personnel.
- notification_logs: user/personnel/firearm relationships and a unique record/expiration/milestone key.
- data_import_logs: user relationship, filename, module, total/imported/failed/duplicate counts, row errors and upload date.

Historical records keep their existing pistol and qty_ammo fields. The new firearms/ammunition tables represent additional assigned firearms and ammunition lots; they do not duplicate or automatically backfill the original allocation. Existing renewal analytics and reports retain their original personnel-based scope. Additional records can be viewed in their new management screens, and additional firearm expirations participate in reminders. Do not import the original pistol again as an additional firearm; serial validation rejects it.

Existing personnel ATP values remain null until staff enter the actual values; no identifiers are fabricated. New registrations/imports require ATP. When editing a historical personnel record in the original screen, fill its ATP field. Personnel deletion continues to archive records and supports restore, preserving inspection/PAR history.

Migration rollback removes the new tables and ATP data; it leaves the original personnel/account tables intact. Use a backup for deployment recovery.

## Gmail SMTP configuration

Keep credentials in your existing private .env. Do not replace it with .env.example. Add or set:

    MAIL_MAILER=smtp
    MAIL_SCHEME=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=your-office-account@gmail.com
    MAIL_PASSWORD=your-google-app-password
    MAIL_FROM_ADDRESS=your-office-account@gmail.com
    MAIL_FROM_NAME="10th APAO Renewal System"
    MAIL_TIMEOUT=30

Use a Google App Password where your account policy permits it, with 2-Step Verification enabled. Workspace accounts may need administrator approval/settings. The sender must match the authorized sending identity. See [Google SMTP settings](https://support.google.com/mail/answer/7104828) and [Google Workspace sending guidance](https://support.google.com/a/answer/176600).

After updating configuration:

    php artisan config:clear
    php artisan apao:renewal-reminders --dry-run

Dry-run counts due/skipped records but writes no notification history and sends no email. To verify actual Gmail delivery, use a controlled test personnel record and recipient with validity today, then run:

    php artisan apao:renewal-reminders

Check /admin/notification-history and the receiving inbox. Sent means the SMTP server accepted the message; delivery to an inbox is checked separately. Credentials and actual Gmail delivery were not tested in this implementation session. A local SMTP sink verified transport and the required message fields.

Failed attempts can retry when the command runs again on the same milestone date. Sent attempts are skipped. Atomic database claims prevent concurrent attempts for the same milestone. If a process stops with Sending status, verify the provider outcome before changing it to Failed; automatically retrying an unknown result could duplicate a message. The daily check does not backfill milestones missed while the server was offline.

## Automatic scheduling

Laravel's scheduler must be invoked every minute by the host. Linux cron example:

    * * * * * cd /path/to/laravel-app && php artisan schedule:run >> /dev/null 2>&1

On Windows, create a Task Scheduler task that repeats every minute with:

- Program: your PHP 8.4+ executable.
- Arguments: artisan schedule:run
- Start in: the nested vanilla-php-app Laravel folder.

For local development, keep this running in a terminal:

    php artisan schedule:work

Inspect the registered schedule with php artisan schedule:list. The reminder time is explicitly Asia/Manila even if the host uses another timezone. See [Laravel scheduling documentation](https://laravel.com/framework/docs/13.x/scheduling). No permanent OS task was installed during implementation.

## Import use

Open /admin/imports as an administrator. Download the correct template. CSV templates can be opened in Excel and saved as .xlsx.

- Maximum 5 MB / 1,000 data records per upload.
- XLSX requires PHP zip and SimpleXML extensions; enable extension=zip in the PHP runtime serving the application and CLI. CSV does not require ZIP.
- First XLSX worksheet is read. Formula cells are rejected; paste values first.
- Dates must be text in YYYY-MM-DD format. Native Excel numeric date cells are rejected rather than guessed.
- Keep serials and IDs as text to preserve leading zeros.
- Import personnel first, then use their database IDs from /admin/records/personnel for firearm/ammunition personnel_id. Item number and database ID are different identifiers.
- A personnel import creates its pending inspection and retains the original pistol/ammunition allocation. Additional records use the separate import types.
- The template defines supported columns. Headers must be unique and present; blanks/unknown record types are rejected.
- Summary displays invalid rows separately from duplicate rows. History failed_records includes both; total = imported + failed.
- Original personnel item/AFP/ATP/pistol serial/email uniqueness and additional firearm ATP/serial uniqueness are checked. Ammunition duplicates use personnel ID + lot + caliber.

## Verification

The full suite ran successfully against a newly initialized, isolated MariaDB/MySQL-compatible server: **27 tests, 156 assertions**. No application database was modified. The suite includes authentication/session behavior, role restrictions, existing personnel registration/update/archive/restore, all new CRUD types, import summaries/relationships, XLSX formula rejection, audit creation, reminder timing and duplicate suppression. SMTP transport and required email content were also checked using a local test SMTP server.

On a local test MySQL database named apao_laravel_test, apply migrations using a separate test environment before running the existing phpunit.xml suite. Never run tests against your operational database.

    php artisan route:list
    php artisan view:cache
    php vendor/phpunit/phpunit/phpunit

An isolated SQLite in-memory configuration is included for the new feature tests and existing unit tests:

    php -d extension=zip -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit -c tests/phpunit.capstone.xml

The legacy personnel writer test is skipped in SQLite because that writer uses MySQL functions and advisory locks. The full MySQL suite includes it. No real email is sent by these suites; Laravel mailables are faked.

## Added files

- app/Http/Controllers/RecordManagementController.php
- app/Http/Middleware/RecordWriteLock.php
- app/Mail/PersonnelNotice.php
- app/Models/Ammunition.php
- app/Models/DataImportLog.php
- app/Models/Firearm.php
- app/Models/NotificationLog.php
- app/Services/ImportReader.php
- app/Services/PersonnelEmailService.php
- app/Services/RecordAudit.php
- app/Services/RecordValidation.php
- app/Services/RenewalReminderService.php
- database/migrations/2026_10_12_000001_add_monitoring_and_import_records.php
- resources/views/admin_imports.blade.php
- resources/views/admin_notification_history.blade.php
- resources/views/admin_records.blade.php
- resources/views/emails/renewal_notice.blade.php
- resources/views/partials/record_fields.blade.php
- resources/views/partials/record_navigation.blade.php
- resources/views/partials/record_sidebar_links.blade.php
- resources/views/partials/records_layout_start.blade.php
- tests/Feature/CapstoneRevisionsTest.php
- tests/phpunit.capstone.xml
- docs/CAPSTONE-REVISIONS.md

## Modified files

- .env.example
- README.md
- app/Models/Personnel.php
- app/Models/User.php
- app/Services/AuditService.php
- app/Services/PersonnelService.php
- app/Services/UserManagementService.php
- app/Support/domain.php
- config/mail.php
- resources/views/admin_archive.blade.php
- resources/views/admin_audit.blade.php
- resources/views/admin_dashboard.blade.php
- resources/views/admin_inspection.blade.php
- resources/views/admin_personnel.blade.php
- resources/views/admin_reports.blade.php
- resources/views/admin_users.blade.php
- resources/views/staff_dashboard.blade.php
- routes/console.php
- routes/web.php

## Client XLSM workbook support

Reports → Import Records accepts .csv, .xlsx and .xlsm. XLSM template workbooks use the first sheet. For Pistol-Renewal-Reporting-Automation.xlsm, the reader selects Database for Pistol, 9mm Glock, maps its available personnel/firearm columns, ignores report/control columns and converts Excel numeric dates. Macros are never executed. The actual client file was read successfully (500 rows); no client data was saved during verification.

The original workbook does not provide separate first/last name and rank columns, email, birth dates, ATP numbers, pistol type and issuer for all records. Missing values remain blank and fail ordinary validation; combined names are not guessed and contact details are not fabricated. Add the required columns to its database sheet or complete the personnel template before importing. Select Personnel for this combined client dataset. Additional firearm/ammunition imports still use their own templates.

## Client XLSX compatibility fix

The importer now recognizes Pistol Records in client XLSX files as well as the client XLSM database sheet. It maps client column labels, converts numeric Excel dates, parses only unambiguous RANK FIRST LAST AFP-SERIAL names, and derives pistol caliber from an explicit caliber in the nomenclature. Ambiguous names still need separate name columns. Historical client personnel imports allow missing ATP numbers and issuer values to remain null; ordinary templates and new registrations keep their existing required-field rules.

If ZIP is disabled, SpreadsheetArchive uses PHP PharData to read workbook ZIP entries without executing macros. Expansion/entry limits and XML protections remain enforced. The actual Dummy_Pistol_Renewal_Data_1000_Personnels.xlsx upload passed against an isolated in-memory database: 1,000 personnel, 1,000 pending inspections, zero invalid or duplicate rows, with ZIP disabled. No operational records were imported during verification. This compatibility section supersedes earlier instructions that require enabling ZIP or completing ATP/issuer fields for historical client imports.
