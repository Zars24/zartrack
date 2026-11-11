# ZarTrack — Setup

## Requirements
PHP 8.1+, Composer, Node 18+, MySQL/MariaDB.

## Install
```bash
composer install
cp .env.example .env
php artisan key:generate
# set DB_DATABASE=zartrack (buat database dulu)
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

## Demo accounts (password: `password`)
| Role | Email |
|---|---|
| super_admin | super@zartrack.id |
| ops_admin | ops@zartrack.id |
| doc_admin | doc@zartrack.id |
| finance | finance@zartrack.id |
| driver | driver@zartrack.id |
| customer | customer@zartrack.id |

## Test
```bash
php artisan test
```
Tests use in-memory SQLite (`phpunit.xml`), production DB untouched.

## Structure
- `app/Models` — domain: Order → Container → ShipmentTimeline / GpsLocation / Document; Invoice → Payment.
- `app/Http/Controllers/Admin|Driver|Customer` — role-scoped controllers.
- `config/zartrack.php` — roles, status machine (`transitions`), labels, service & document types.
- `resources/views/layouts` — `admin`, `portal` (customer), `driver` (mobile-first), `base` (public/auth).
- `resources/css/app.css` — design tokens + components (light/dark via `data-theme`).

## Status machine
`pending → confirmed → assigned → pickup → in_transit → delivered → completed`
plus `cancelled`, `on_hold`, `returning_empty`, `exception`.
All changes go through `Container::transitionTo()` which validates, writes the timeline, rolls up the order status, and notifies the customer.
