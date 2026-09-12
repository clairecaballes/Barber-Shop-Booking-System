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

### Access from another device (phone/tablet)

```sh
composer dev:lan
```

Serves the app on `http://0.0.0.0:8000`. On the other device (same Wi-Fi) open
`http://<PC-LAN-IP>:8000` and sign in with the registered email — all shop data
lives server-side (SQLite + database sessions), so every device sees the same
bookings/customers. `composer dev` stays bound to localhost only.

## Security (OWASP)

- Global `SecurityHeaders` middleware emits CSP + `X-Frame-Options`/`nosniff`/
  `Referrer-Policy`/`Permissions-Policy` on every response. Alpine needs
  `'unsafe-eval'`; the local Vite HMR origin is allowlisted only in `local` env.
- Login is rate-limited per `email|IP` (`RateLimiter::for('login')`, 5/min, set
  in `AppServiceProvider`). Password-reset routes are throttled too.
- Session cookies: `http_only`, `same_site=lax` (see `.env`). Enable
  `SESSION_SECURE_COOKIE=true` only when serving over HTTPS.
- Changing the password rotates the session id + CSRF token and signs out every
  other device (`AccountController`).

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

## Design system

Dark charcoal base + electric lime accent, bento surfaces, chrome materials. Defined in `resources/css/app.css`.

- **Tokens:** semantic vars (`--page`, `--surface`, `--stroke`, `--ink`, `--muted`, `--accent`, `--radius-bento`) set on `:root` and overridden in `.dark`, exposed to Tailwind via `@theme inline`. Use `bg-page`, `bg-surface`, `text-ink`, `text-muted`, `border-line`, `text-accent`, `bg-accent-soft`, `rounded-tile` — never raw `slate-*`/`amber-*`.
- **Component classes:** `.bento` (panel), `.bento-sunken` (recessed well), `.bento-lit` (lime hairline + bloom), `.chrome` (dark metal rail/topbar), `.btn-accent`/`.btn-metal`/`.btn-ghost`/`.btn-danger`, `.field`, `.label`, `.chip`, `.rail-*`, `.view-tab`, `.numeral` (tabular mono for money/counts/times), `.pole`/`.pole-cap` (CSS barber pole; `.pole-anim` rotates it, login page only).
- **Accent budget:** lime is for active nav, primary CTAs, lit tiles, and status dots — keep it roughly 10% of the paint. Most surfaces stay neutral.
- **Light theme:** still available via the toggle; it uses a deeper lime (`#4d7c0f`) for contrast on white. The rail/topbar stay dark chrome in both themes and keep the true lime.
- **Status colors:** `--st-pending|booked|completed|cancelled|noshow|blocked`, mirrored as event fills in `CalendarController`/`Api\CalendarEventsController` (dark ink text on every fill).
- **Blade primitives:** `x-panel`, `x-btn`, `x-field`, `x-input`, `x-select`, `x-textarea`, `x-alert`, `x-stat-card`, `x-status-badge`, `x-icon`.

## Project status

Full feature set built. Key structure:

- `app/Http/Controllers/Auth/LoginController.php` — login/logout
- `app/Http/Controllers/Auth/PasswordResetController.php` — forgot-password: 6-digit one-time code mailed to a matching Gmail (OWASP: generic responses, hashed expiring tokens, throttled routes, sessions wiped on reset)
- `app/Mail/PasswordResetCode.php` — reset-code email (SMTP via `.env` Gmail app password; codes also fall back to `storage/logs` only if the mailer fails)
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
