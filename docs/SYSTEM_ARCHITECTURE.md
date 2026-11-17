# System Architecture

## Shape of the application

ZarTrack is a server-rendered Laravel monolith. Blade templates produce HTML, Vite bundles a single CSS and a single JS entry point, and MySQL holds all state. There is no separate frontend application, no client-side router, and no SPA framework. Interactive behaviour is added on top of rendered HTML with small vanilla-JS modules in `resources/js/app.js` plus per-view `@push('scripts')` blocks.

Consequences of that choice:

- Authorization and data scoping live on the server, so hidden UI is never the only protection.
- Each page is a full document request; state lives in the database and session, not in browser memory.
- Progressive enhancement: navigation, forms, and destructive confirmations work from HTML; maps and GPS need JavaScript.

## Request lifecycle

```
HTTP request
  → public/index.php
  → app/Http/Kernel.php        global middleware (cookies, session, CSRF, trimming, proxies)
  → routes/web.php             route match → controller (plus `auth` and `role:*` middleware)
  → App\Http\Controllers\...   query via Eloquent, enforce record-level ownership
  → resources/views/...        Blade layout + view, @vite assets, @stack('scripts')
  → HTML response
```

Two authorization layers run in sequence:

1. **Route-level** — `auth` guarantees a session; `role:a,b,c` (`EnsureRole`) aborts 403 for a missing, inactive, or wrong-role user.
2. **Record-level** — controllers re-check ownership before returning data, for example `abort_unless($order->customer_id === $this->customerId(), 403)` in the customer portal, or `authorizeContainer()` in the driver controller.

## Directory map

```
app/
  Console/Kernel.php
  Exceptions/Handler.php
  Http/
    Controllers/
      Admin/         resource controllers for back-office modules
      Auth/          login, logout, profile, password
      Customer/      PortalController — customer-scoped reads
      Driver/        TaskController — task execution, GPS, uploads, issues
      PublicController.php       marketing pages, quote intake, public tracking
      NotificationController.php notifications list and read state
    Middleware/EnsureRole.php    role gate
  Models/                        domain models (see data model)
  Notifications/                 ShipmentStatusChanged, OperationalAlert
  Providers/AppServiceProvider.php  pagination style + notifCount view composer

config/
  zartrack.php                   roles, status machine, labels, service types,
                                 container sizes, document types

database/
  migrations/2014_*              stock Laravel tables (users, tokens, jobs)
  migrations/2024_01_01_*        full ZarTrack schema in one migration
  seeders/DatabaseSeeder.php     demo customers, admins, drivers, trucks,
                                 orders, containers, timelines, GPS, invoices, quote

resources/
  css/app.css                    design tokens + component layer (light/dark)
  js/app.js                      theme, sidebar, public nav, mini maps,
                                 flash dismissal, confirm guards
  views/
    layouts/   base | admin | portal (customer) | driver
    admin/     dashboard, orders, tracking, customers, invoices, documents,
               drivers, trucks, users, quotes, logs, reports
    customer/  dashboard, orders, order, invoices, invoice, documents
    driver/    dashboard, task
    public/    home, about, services, contact, tracking, _nav, _footer
    auth/      login, profile
    notifications/index
    pdf/invoice    standalone inline-CSS dompdf template
    components/    icon, status

routes/
  web.php      all application routes
  api.php      only /api/user (Sanctum); the tracking JSON route lives in web.php
  channels.php broadcast auth stub

tests/
  Feature/ZarTrackSmokeTest.php  end-to-end smoke coverage
  Feature/ExampleTest.php, Unit/ExampleTest.php
```

## Data model

```
Customer 1─┬─* User (role = customer)
           └─* Order 1─┬─* Container 1─┬─* ShipmentTimeline
                       │               ├─* GpsLocation
                       │               └─* Document
                       ├─* Document
                       └─* Invoice 1─* Payment

Driver 1─1 User (role = driver)
Driver 1─* Container        (assigned driver)
Truck  1─* Container        (assigned truck)

Quote          standalone lead, not linked to Order
ActivityLog    polymorphic subject_type/subject_id
Notification   Laravel database notifications (morph notifiable)
```

### Tables

| Table | Purpose |
|---|---|
| `customers` | Company master data; soft deletes; `status` active/inactive |
| `users` | Login accounts with `role`, `phone`, `customer_id`, `is_active` |
| `drivers` | Driver master data linked optionally to a user; soft deletes |
| `trucks` | Fleet master data; `plate_number` unique |
| `orders` | Commercial shipment order; `order_number` unique; soft deletes |
| `containers` | Physical container unit; holds assignment, status, `tracking_ref` |
| `shipment_timelines` | Append-only status history per container with actor and coordinates |
| `gps_locations` | Position pings per container with driver, speed, heading, timestamp |
| `documents` | File metadata, attachable to order and/or container |
| `invoices` | Billing document with derived `paid_amount` and `status` |
| `payments` | Payment rows; saving or deleting recalculates the invoice |
| `quotes` | Public lead capture with pipeline status |
| `activity_logs` | Manual audit entries with actor, subject, description, IP |
| `notifications` | Laravel database notifications |

Full constraints and derived-field rules: [BUSINESS_RULES.md](BUSINESS_RULES.md).

## Key classes

### `App\Models\Container`

Owns the shipment state machine.

- Generates `tracking_ref` on create (`ZT` + 10 random characters).
- `transitionTo($to, $actor, $note, $lat, $lng)` validates against `config('zartrack.transitions')`, updates the container, writes the timeline row, rolls up order status, notifies customer users, escalates exceptions, and releases driver and truck on delivery.
- Relations: order, driver, truck, timelines, locations, documents, `latestLocation()`.

Any new status-changing feature must route through this method; writing `status` directly bypasses validation, timeline, rollup, and notifications.

### `App\Models\Order`

- Generates `order_number` on create.
- `syncStatusFromContainers()` derives order status from the least-advanced non-cancelled container.

### `App\Models\Invoice` and `App\Models\Payment`

- `Invoice::recalculate()` sums payments and derives `paid_amount` and `status`.
- `Payment` model hooks (`saved`, `deleted`) call `recalculate()`, so payment writes cannot leave a stale invoice.
- `Invoice::getBalanceAttribute()` clamps at zero.

### `App\Models\ActivityLog`

- `ActivityLog::record($action, $subject, $description)` is called explicitly from controllers. There is no observer, so a new write path must call it deliberately.

### `App\Http\Middleware\EnsureRole`

- Accepts variadic role names, rejects inactive accounts, and aborts 403 on mismatch.

## Configuration as domain source

`config/zartrack.php` is the single source for enums that appear in validation, UI labels, and the state machine:

| Key | Contents |
|---|---|
| `roles` | six role keys mapped to display labels |
| `admin_roles` | `super_admin`, `ops_admin`, `doc_admin`, `finance` |
| `transitions` | allowed status transitions, the state machine |
| `status_labels` | human labels for each status |
| `service_types` | trucking, freight, warehousing, logistics |
| `container_sizes` | 20ft, 40ft, 40hc, 45hc |
| `document_types` | do, surat_jalan, pod, invoice, other |

Views and controllers read these arrays rather than hard-coding values, so adding a status or document type is a config change plus a label.

## Presentation layer

**Layouts**

| Layout | Used by | Notes |
|---|---|---|
| `layouts/base` | public pages, auth | theme bootstrap script, font links, Vite tags, `@stack('head')`, `@stack('scripts')` |
| `layouts/admin` | all `/admin` pages | sidebar, breadcrumb topbar, notifications, user dropdown, theme toggle |
| `layouts/portal` | `/customer` | customer navigation shell |
| `layouts/driver` | `/driver` | mobile-first, bottom tab bar |

`AppServiceProvider` shares `notifCount` with the admin and portal layouts only, because only those shells render the unread badge.

**Components**

- `<x-icon name="..." size="..." />` — inline SVG icon set defined in `components/icon.blade.php`.
- `<x-status :status="..." />` — status badge that maps the status key to `config('zartrack.status_labels')` and applies `st-{status}` for colouring.

**Design direction**

The visual target is *modern industrial × premium SaaS*: an original interface rather than an off-the-shelf dashboard template, a consistent professional palette, clear typographic hierarchy, subtle motion, and a distinct composition for the operational dashboard. Light theme is the default; dark theme is a first-class equal. The driver portal is mobile-first; all other surfaces are responsive from desktop down to phone. Usability, accessibility, and performance take precedence over decorative effect.

**Styling**

- `resources/css/app.css` (~2000 lines) is the design system: colour scales, semantic tokens (`--bg`, `--surface`, `--tx`, …), shell, primitives, and product surfaces.
- Dark mode is a token override block on `[data-theme="dark"]`, not a separate stylesheet.
- Bootstrap 5.3 supplies grid, forms, tables, dropdowns, and modals; its Sass is not recompiled, only its compiled CSS is imported and complemented.
- Fonts load from fonts.bunny.net: Bricolage Grotesque (display), Instrument Sans (body), JetBrains Mono (numeric and reference strings).

**JavaScript**

`resources/js/app.js` is a single ~95-line module:

- theme toggle with `localStorage` persistence and a `zt:theme` event
- off-canvas sidebar with backdrop, Escape, and body scroll lock
- public navigation scroll shadow and mobile panel
- `.map-mini` Leaflet initialisation from `data-lat` / `data-lng`
- flash auto-dismiss, `data-confirm` guards, search-on-enter, bar-list animation

Page-specific scripts stay in the view: the tracking map in `public/tracking.blade.php`, the GPS watch loop in `driver/task.blade.php`.

## External services

| Service | Use | Requirement |
|---|---|---|
| OpenStreetMap tiles | all maps | outbound HTTPS from the browser |
| fonts.bunny.net | webfonts | outbound HTTPS from the browser |
| Browser Geolocation API | driver position | HTTPS or localhost, user permission |
| dompdf | invoice PDF | server-side, no external service |

No payment gateway, SMS, or email provider is wired in. Mail is configured in `.env` but nothing in the application sends mail.

## Testing

`phpunit.xml` overrides the environment for tests: SQLite in-memory, array cache, array session, array mail, sync queue.

`tests/Feature/ZarTrackSmokeTest.php` seeds the database and covers:

- all public pages return 200
- public tracking resolves a reference; quote submission persists
- role guards: guests redirected, cross-role access forbidden, restricted admin route blocked
- every admin page renders for super admin, including PDF generation
- full flow: create order → add container → assign → pickup → in transit → delivered, plus invalid-transition rejection, GPS rejection outside travel, invoice creation and payment settlement
- customer isolation across companies
- driver isolation across tasks
- per-role landing pages and read-only invoice access for doc_admin
- notification creation on status change

Run with `php artisan test`.

## Conventions

- Controllers stay thin: validate, authorize, delegate to a model method, redirect with a flash message. Business rules that touch several tables live on the model (`transitionTo`, `syncStatusFromContainers`, `recalculate`).
- Domain enums live in `config/zartrack.php`, never duplicated in views.
- Every write path that matters calls `ActivityLog::record()`.
- New UI work reuses primitives in `app.css` (`.card`, `.toolbar`, `.form-section`, `.chip`, `.list-row`, `.kv`, `.route`) and tokens, rather than adding one-off styles.
- Flash messages use `success` / error keys and the shared alert partials in the layouts.
- Blade escaping is default; `{!! !!}` is not used for user data.
- Money is stored as `decimal(15,2)` and formatted at render time.

## Known architectural limits

- Single migration for the whole domain schema; convenient for a fresh install, not for incremental evolution.
- Reports aggregate in PHP after loading rows; fine at demo volume, not at scale.
- No queues in practice (`QUEUE_CONNECTION=sync`), so notifications are written inline with the request.
- No API layer for the domain — `/api/track/{ref}` is the only data endpoint, and it is unauthenticated by design (public reference lookup).
- No multi-company tenancy; one operating company with many customers is assumed throughout.