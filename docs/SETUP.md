# Setup

Local installation, configuration, and verification for ZarTrack.

## Requirements

| Tool | Version |
|---|---|
| PHP | 8.1 or newer, with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` |
| Composer | 2.x |
| Node.js | 18 or newer (Vite 4) |
| Database | MySQL 8 / MariaDB, or SQLite for a throwaway run |

## Install

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the database, then point `.env` at it:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zartrack
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

The application is now at `http://localhost:8000`.

For frontend work, use `npm run dev` in a second terminal and leave `php artisan serve` running.

### Why each step matters

| Step | Reason |
|---|---|
| `key:generate` | Encryption key for sessions and cookies; without it the app fails to boot |
| `migrate --seed` | Creates the schema and demo data in one go |
| `storage:link` | Symlinks `public/storage` to `storage/app/public`; document upload and download break without it |
| `npm run build` | Compiles `resources/css/app.css` and `resources/js/app.js`; Blade renders `@vite` tags that require the build manifest |

## Demo accounts

Seeded by `DatabaseSeeder`. All use the password `password`.

| Role | Email |
|---|---|
| super_admin | super@zartrack.id |
| ops_admin | ops@zartrack.id |
| doc_admin | doc@zartrack.id |
| finance | finance@zartrack.id |
| driver | driver@zartrack.id |
| driver (second) | driver2@zartrack.id |
| customer | customer@zartrack.id |

The seed creates three customer companies, two drivers with linked trucks, five orders across different statuses with timelines, GPS history for moving containers, three invoices (one fully paid), and one pending quote. This is enough to exercise every screen without entering data manually.

## Configuration

| Variable | Purpose |
|---|---|
| `APP_NAME` | Shown in the browser title and shell |
| `APP_URL` | Used to build the public storage URL; set it correctly or document links resolve to the wrong host |
| `APP_DEBUG` | Keep `true` locally, `false` anywhere shared |
| `DB_*` | Database connection |
| `FILESYSTEM_DISK` | Leave as `local`; uploads explicitly use the `public` disk |
| `QUEUE_CONNECTION` | `sync` by default; notifications are written inline |
| `MAIL_*` | Present in `.env` but unused — nothing in the application sends mail |

`config/zartrack.php` holds domain values (roles, status transitions, service types, container sizes, document types). It is configuration of business rules, not environment configuration; edit it in code, not in `.env`.

## Test

```bash
php artisan test
```

Tests run against SQLite in-memory, configured in `phpunit.xml`, so the development database is untouched. The suite seeds itself.

Current coverage: 11 tests, 83 assertions. See [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md#testing) for what the smoke test checks.

Useful checks while developing:

```bash
php artisan route:list          # confirm routes and middleware
php artisan view:cache          # catch Blade compile errors
php artisan view:clear
php artisan test --filter=ZarTrack
```

## Project structure

```
app/
  Http/Controllers/Admin|Auth|Customer|Driver   role-scoped controllers
  Models/                                        Order → Container → Timeline / GpsLocation / Document
                                                 Invoice → Payment
  Middleware/EnsureRole.php                      role gate
config/zartrack.php                              roles, status machine, labels, service & document types
resources/views/layouts                          admin, portal (customer), driver (mobile-first), base
resources/css/app.css                            design tokens + components (light/dark via data-theme)
resources/js/app.js                              theme, sidebar, public nav, mini maps
database/migrations/2024_01_01_000000_*          full domain schema
database/seeders/DatabaseSeeder.php              demo dataset
```

Full detail: [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md).

## Shipment status machine

```
pending → confirmed → assigned → pickup → in_transit → delivered → completed
```

Side states: `cancelled`, `on_hold`, `returning_empty`, `exception`.

All changes go through `Container::transitionTo()`, which validates the transition against `config('zartrack.transitions')`, writes the timeline row, rolls up the order status, and notifies the customer. Invalid transitions are rejected and surface as a validation error.

Rules and the full transition table: [BUSINESS_RULES.md](BUSINESS_RULES.md).

## Troubleshooting

**`Vite manifest not found`** — run `npm run build` (or `npm run dev`). `public/build` is git-ignored, so a fresh clone has no assets.

**`Class "Vite" not found` / blank page after deploy** — the build manifest is missing or stale; rebuild.

**Document upload succeeds but download 404s** — run `php artisan storage:link`. Verify `APP_URL` matches the host you browse.

**Maps do not render** — Leaflet tiles come from OpenStreetMap over HTTPS in the browser. Check network access and the browser console; a blocked tile request renders an empty frame rather than an error page.

**GPS never sends** — the Geolocation API requires a secure context. Use `http://localhost` or HTTPS; a LAN IP over plain HTTP is rejected by the browser. Also confirm the container is in `pickup` or `in_transit`, since other states return 422 by design.

**Login works but every page is 403** — the account is inactive. Activate it from `/admin/users` as a super admin, or check the `is_active` column.

**Driver portal says the account is not linked** — the `drivers` row must reference the user through `user_id`. Fix it via the driver form with an email address, which creates or links the account.

**Seeded data looks wrong after re-running migrations** — `migrate --seed` appends; use `migrate:fresh --seed` for a clean dataset. That command is destructive and drops all tables.