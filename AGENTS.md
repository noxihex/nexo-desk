# Repository Guidelines

## Project Structure & Module Organization

Nexo Desk is a Laravel 12 ticket-management application. Backend code lives in `app/`: controllers are under `app/Http/Controllers`, models under `app/Models`, and API v2 resources under `app/Http/Resources/Api/V2`. Define endpoints in `routes/web.php` and `routes/api.php`. Database migrations, factories, and seeders belong in `database/`. Blade templates and frontend sources are in `resources/views`, `resources/js`, and `resources/css`; Vite writes production assets to `public/build/`. API documentation is in `docs/`. Tests are divided into `tests/Unit` and `tests/Feature`.

## Build, Test, and Development Commands

- `composer install` installs PHP dependencies.
- `npm install` installs frontend build dependencies.
- `php artisan key:generate` creates the application key after copying `.env.example` to `.env`.
- `php artisan migrate` applies database migrations; configure `DB_*` values first.
- `php artisan serve` starts the local development server.
- `npm run dev` starts the Vite development server with HMR.
- `npm run build` creates optimized production assets.
- `php artisan test` runs the complete PHPUnit suite. Use `php artisan test --filter=ApiV2Test` for a focused run.

## Coding Style & Naming Conventions

Follow `.editorconfig`: UTF-8, LF endings, final newline, four-space indentation, and two spaces for YAML. Use Laravel/PSR conventions: PascalCase classes (`TicketController`), camelCase methods, snake_case database columns, and timestamped migration names. Blade components use kebab-case, such as `status-badge.blade.php`. StyleCI applies the Laravel preset and checks JavaScript and CSS.

## Testing Guidelines

PHPUnit 11 is configured in `phpunit.xml`. Name test classes and files with the `Test` suffix. Place isolated logic in `tests/Unit`; use `tests/Feature` for routes, authorization, views, and database behavior. Add regression coverage with bug fixes. No minimum percentage is enforced, but `app/` is included in coverage reports. Keep tests deterministic.

## Commit & Pull Request Guidelines

Recent commits use concise Portuguese summaries such as `Correcao de bug nome de empresa` and `Remocao de notificacoes`. Keep subjects short, specific, and focused on one change; avoid mixing refactors with behavior changes. Pull requests should explain the problem and solution, note migrations or configuration changes, link the relevant issue, and list verification commands. Include screenshots for Blade or navigation changes, and document API contract changes in `docs/`.

## Security & Configuration

Never commit `.env`, credentials, tokens, database dumps, or generated logs. Add new environment keys to `.env.example` with safe placeholders. Treat `php artisan migrate:fresh` as destructive and use it only against disposable local databases.
