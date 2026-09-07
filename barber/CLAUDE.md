# AGENTS.md

## Project

Laravel 13 barber shop booking system. PHP 8.3+, SQLite (dev), Vite 8, Tailwind CSS v4, Alpine.js.

The Laravel app lives entirely in `barber/`. All PHP/artisan commands run from that directory.

## First-time setup

```sh
composer setup
npm install
npm run build
```

This installs PHP dependencies, copies `.env`, generates an app key, runs migrations, installs npm, and builds assets.

If PHP/Composer are missing, install them via https://php.new.

## Dev server

```sh
composer dev
```

Runs `php artisan dev` with Vite HMR (frontend + backend together).

## Seed database

```sh
php artisan migrate:fresh --seed
```

Seeds: admin owner account (`owner@barbershop.test` / `password`), default Haircut ₱150/30min service, and business settings.

## Test

```sh
composer test
```

Clears config cache, then runs `php artisan test`. Uses in-memory SQLite — no external DB needed.

Run a single test class:

```sh
php artisan test --filter=BookingTest
```

## Lint / format

Laravel Pint (PSR-12 defaults, no custom config):

```sh
./vendor/bin/pint
```

## Project status

Auth + dashboard are built. Key structure:

- `app/Http/Controllers/Auth/LoginController.php` — login/logout
- `app/Http/Controllers/DashboardController.php` — dashboard stats
- `app/Http/Controllers/AccountController.php` — profile/password management
- `app/Models/` — User, Service, Customer, Booking, BusinessSetting
- `app/Enums/BookingStatus.php` — pending/booked/completed/cancelled/no_show
- `app/Services/SalesService.php` — all financial metric queries
- `app/Support/helpers.php` — `money()` formatter (autoloaded via composer)
- `routes/web.php` — all routes; auth+guest middleware groups

**Price convention:** all prices stored as integer centavos (₱150 = `15000`). Use `money($cents)` in views.

**Timezone note:** `config/app.php` defaults to UTC. Set `APP_TIMEZONE=Asia/Manila` in production to display correct local times.

## Laravel Boost

`laravel/boost` is not yet installed. If the user requests it:

```sh
composer require laravel/boost --dev
php artisan boost:install
```
