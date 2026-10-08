# APAO Renewal System — Laravel

The application now runs on Laravel 13 with native routing, controllers, middleware, sessions, CSRF protection, Blade views and Eloquent models. Existing personnel and inspection business rules are retained in application services.

The migration is on the separate migration/laravel branch. The deployed main branch has not been changed.

## Requirements

- PHP 8.4 or newer with fileinfo, mbstring and pdo_mysql
- Composer 2
- MySQL 8
- Web server document root: public/

## Setup

Preserve an existing .env and database. For a fresh installation, copy .env.example and configure the database credentials.

    composer install
    php artisan key:generate
    php artisan migrate
    php artisan serve --host=127.0.0.1 --port=8082

Generate APP_KEY once and preserve it. Existing accounts and password hashes remain compatible. Do not commit .env.

Create a new administrator if needed:

    php artisan apao:create-admin admin@example.com "Administrator Name"

New administrator accounts must choose a new password on first sign-in. Existing command-line creation, reset and import tools in bin/ now bootstrap Laravel.

## Project structure

- app/Http/Controllers: request handlers
- app/Http/Middleware: session, security and role access
- app/Models: database models
- app/Services: personnel, inspection, notification and account business rules
- resources/views: active Blade templates
- routes/web.php: application routes
- database/migrations: existing schema adoption and fresh installation
- public: web entry point, scripts, styles and images
- tests/Feature: Laravel integration tests

The old src/ and views/ directories remain as migration references and are not loaded by the Laravel web application. Old standalone tests target the previous vanilla runtime; use the Laravel feature suite for this branch.

## Verification

    php artisan route:list
    php artisan view:cache
    php vendor/phpunit/phpunit/phpunit tests/Feature/LaravelMigrationTest.php

Tests require a separate local apao_laravel_test MySQL database. They refuse to run against another database and roll back their test records.

## Render

The Dockerfile installs Laravel dependencies and PHP extensions. Configure APP_KEY, APP_URL, APP_ENV=production, APP_DEBUG=false, database credentials, DB_SSL_CA, and the existing verified Brevo sender settings.

Back up the database before php artisan migrate --force. File sessions/cache are configured for one application instance. Shared session/cache storage is needed for multiple instances.

See docs/LARAVEL-MIGRATION.md for migration scope, checks and deployment limitations.