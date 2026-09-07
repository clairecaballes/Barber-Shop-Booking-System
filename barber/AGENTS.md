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

Full feature set built. Key structure:

- `app/Http/Controllers/Auth/LoginController.php` — login/logout
- `app/Http/Controllers/DashboardController.php` — dashboard stats
- `app/Http/Controllers/AccountController.php` — profile/password management
- `app/Http/Controllers/CalendarController.php` — FullCalendar page
- `app/Http/Controllers/QuickBookingController.php` — fast walk-in booking
- `app/Http/Controllers/BookingController.php` — CRUD + status/reschedule (resource, `except create/store`)
- `app/Http/Controllers/CustomerController.php` — CRUD + profile/history/stats
- `app/Http/Controllers/ServiceController.php` — service CRUD
- `app/Http/Controllers/SalesController.php` — financial metrics + charts
- `app/Http/Controllers/BusinessSettingsController.php` — shop name/currency/hours
- `app/Http/Controllers/ExpenseController.php` — expense CRUD (store/update/destroy); listed in the Expenses module on the Sales page
- `app/Http/Controllers/Api/` — `CalendarEventsController`, `AvailabilityController`, `QuickBookingSlotsController`
- `app/Models/` — User, Service, Customer, Booking, Expense, BusinessSetting
- `app/Enums/BookingStatus.php` — pending/booked/completed/cancelled/no_show
- `app/Services/SalesService.php` — all financial metric queries
- `app/Services/BookingService.php` — overlap prevention, available slots, working-day/hours, atomic create/reschedule
- `app/Support/helpers.php` — `money()` formatter (autoloaded via composer)
- `routes/web.php` — auth+guest middleware groups
- `routes/api.php` — `api.*` routes under `auth` middleware (prefix added automatically by `api` routing)

**Frontend (Vite):** `resources/js/app.js` bundles Alpine, FullCalendar (`@fullcalendar/core`), and html2canvas, exposed as `window.FullCalendar`, `window.html2canvas`. Views register Alpine components via `Alpine.data()` on the `alpine:init` event; global functions (`salesCharts()`, etc.) are defined in inline view scripts and resolved before `Alpine.start()` because ES-module scripts are deferred.

**Price convention:** all prices stored as integer centavos (₱150 = `15000`). Use `money($cents)` in views. Service CRUD multiplies input pesos by 100 on store/update.

**Timezone note:** `config/app.php` defaults to UTC. Set `APP_TIMEZONE=Asia/Manila` in production to display correct local times.

`SalesService` uses SQLite-specific `strftime` in `busiestDay`/`busiestTime` — revisit before switching to Postgres in production.

## Laravel Boost

`laravel/boost` is not yet installed. If the user requests it:

```sh
composer require laravel/boost --dev
php artisan boost:install
```
