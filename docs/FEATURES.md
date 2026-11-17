# Features

Everything marked **Implemented** exists in the current codebase and is exercisable in the running application. **Partial** means the feature works but is narrower than a full production implementation. **Planned** means it is not built yet; see [ROADMAP.md](ROADMAP.md).

## Summary

| Module | State |
|---|---|
| Public website | Implemented |
| Authentication & profile | Implemented |
| Role-based authorization | Implemented |
| Admin dashboard | Implemented |
| Master data (customers, drivers, fleet) | Implemented |
| Orders & multi-container management | Implemented |
| Dispatch & status machine | Implemented |
| GPS tracking & shipment timeline | Implemented |
| Document management & POD | Implemented |
| Invoicing, payments & PDF | Implemented |
| Reports | Implemented (basic) |
| Notifications | Implemented (database only) |
| Activity log | Implemented |
| Customer portal | Implemented |
| Driver portal | Implemented |
| Quote pipeline | Implemented (status only) |
| UI system (themes, responsive) | Implemented |

---

## Public website

**Implemented**

- Home page with photo-forward hero (operational photography from `public/images/photos/`), asymmetric capability mosaic with per-image focal points, dark operational-detail section with validated status flow, tracking band, and quote CTA. Supports light and dark themes; images are lazy-loaded with descriptive alt text.
- About page with a photo banner, working-principles cards, and an operating-model section.
- Services page with the same photo-forward treatment: photo banner plus the capability mosaic reused from the landing page.
- Contact page with company details, tracking lookup, and the quote request form.
- Quote request form (`POST /quote`, throttled to 5 requests per minute) capturing name, company, email, phone, service type, origin, destination, cargo details, and message. Submitting creates a `Quote` and notifies active super admins and ops admins in-app.
- Public shipment tracking at `/track` and `/track/{ref}`: looks up a container by `tracking_ref`, shows route, customer, order number, size, status, interactive Leaflet map, and the status timeline. No login required.
- Read-only tracking JSON at `GET /api/track/{ref}` (throttled to 30 requests per minute), returning status, latest location, and the last 10 timeline entries for containers currently in `pickup` or `in_transit`.

**Notes** — the contact page has no separate message-inbox feature; enquiries are captured through the quote form. The tracking JSON endpoint is exposed for external or future clients and is not consumed by the current UI.

## Authentication & profile

**Implemented**

- Session login at `/login`, throttled to 10 attempts per minute.
- Inactive accounts are rejected after credential verification and logged out immediately.
- Login and logout are written to the activity log.
- Post-login redirect resolves per role: admins to `/admin`, drivers to `/driver`, customers to `/customer`.
- Profile page for updating name, email, and phone; separate form for changing password with current-password verification and minimum length of 8.
- Eye-icon show/hide toggle on every password input (login and the three profile password fields). Toggles the input between `password` and `text`, swaps the icon between `eye` and `eye-off`, preserves the typed value, and updates `aria-label` / `aria-pressed` for screen readers.

**Notes** — there is no self-service registration, password reset flow, email verification, or two-factor authentication. Accounts are provisioned by an administrator.

## Authorization

**Implemented**

- Six roles: `super_admin`, `ops_admin`, `doc_admin`, `finance`, `driver`, `customer`.
- `EnsureRole` middleware aborts with 403 for missing role or inactive user; registered as the `role` alias.
- Admin routes are grouped by capability, so finance cannot open orders and doc_admin cannot create invoices.
- Cross-tenant protection inside controllers: customer portal aborts 403 when the record belongs to another company; driver endpoints abort 403 when the container is assigned to another driver.
- Invoice PDF and document download re-check ownership, allowing admins or the owning customer only.

See [BUSINESS_RULES.md](BUSINESS_RULES.md) for the full matrix.

## Admin dashboard

**Implemented**

- Live metric strip: orders total and active, active containers, containers in transit, active customers, available drivers, available trucks, current-month revenue, receivable balance, and new quotes.
- Recent orders table and active shipment list (containers in `pickup` or `in_transit`) with driver and truck.
- Container status breakdown rendered as a proportional bar list.
- Navigation is filtered per role, so each admin only sees sections they can open.

## Master data

**Implemented**

- **Customers** — list with search and status filter, create/edit form, detail page with recent orders and linked user accounts. Deletion blocked while orders exist. Soft deletes.
- **Drivers** — list with search and status filter (`available`, `on_duty`, `inactive`), create/edit form including licence number and expiry, optional linked login account created or updated in the same request. Deletion blocked while active tasks exist. Soft deletes.
- **Trucks** — list with search and status filter (`available`, `in_use`, `maintenance`), create/edit with plate, type, brand, model, and capacity.
- **Users** — super-admin only. List with role filter and search, create/edit with role assignment and customer linkage, activation toggle, password set/reset. A user cannot deactivate or delete their own account.

## Orders & containers

**Implemented**

- Order list with search by order number or customer name, status filter, and container count.
- Order form capturing customer, service type, origin, destination, pickup and delivery dates, total price, and notes. Delivery date must not precede pickup date.
- Order detail page acting as the dispatch workspace: container list with status, driver, truck, and actions.
- Add containers to an existing order (number, size from `20ft` / `40ft` / `40hc` / `45hc`, seal number, weight, notes). A tracking reference `ZT` + 10 characters is generated automatically.
- Assignment of a driver and truck per container, which advances the container to `confirmed` then `assigned` and sets the driver to `on_duty` and the truck to `in_use`.
- Container deletion restricted to `pending` or `cancelled` containers; order deletion blocked while invoices exist.
- Order status is rolled up automatically from its containers (see business rules).

## Dispatch & status machine

**Implemented**

- Single write path for status: `Container::transitionTo()` validates the transition against `config/zartrack.php`, writes a timeline row, rolls up the order status, notifies customer users, escalates exceptions to super admins and ops admins, and releases the driver and truck on delivery.
- Forward-only flow `pending → confirmed → assigned → pickup → in_transit → delivered → completed`, plus side states `cancelled`, `on_hold`, `returning_empty`, and `exception`.
- Invalid transitions raise an error and surface as a validation message; the API returns a redirect back with errors.
- Admin transition endpoint is registered for `super_admin`, `ops_admin`, and `doc_admin`, but the controller additionally requires `canManageOperations()`, so only `super_admin` and `ops_admin` can actually move a container.
- `pickup_at` and `delivered_at` timestamps are set automatically on the corresponding transitions.

## GPS tracking & timeline

**Implemented**

- Driver portal shares position through the browser Geolocation API: `watchPosition` plus a 30-second polling fallback, posted to `/driver/tasks/{container}/ping`.
- GPS posts are rejected with 422 unless the container is `pickup` or `in_transit`, enforcing "GPS only during an assigned journey".
- Captured fields: latitude, longitude, speed, heading, recording driver, and timestamp.
- Admin tracking list shows all containers with search and status filter, plus a separate live list of containers currently moving with a known location.
- Container tracking detail shows driver, truck, customer, map, status timeline with actor and note, GPS history (last 100 points), and attached documents.
- Public and customer tracking maps render the position trail with Leaflet and OpenStreetMap tiles.
- Status updates submitted by a driver attach the current coordinates when available.

## Documents & proof of delivery

**Implemented**

- Shared document library across orders and containers with types `do` (Delivery Order), `surat_jalan`, `pod` (Proof of Delivery), `invoice`, and `other`.
- Admin upload accepting PDF, JPG, JPEG, PNG, DOC, and DOCX up to 10 MB, stored on the `public` disk, metadata recorded with uploader.
- Driver upload of POD, surat jalan, or other documents directly against a task, restricted to PDF and image formats up to 10 MB.
- Download endpoint enforcing authorization: admins, or the customer owning the related order. Missing files return 404; deleting a document removes the stored file.
- Documents are visible in the customer portal and on the relevant order and tracking screens.

## Invoicing & payments

**Implemented**

- Invoice creation tied to an order, with issue date, due date, tax, total, and notes. Subtotal is derived as total minus tax. Invoice numbers are generated as `INV-{yymmdd}-{suffix}`.
- Invoice list with status filter and search by invoice number or customer name.
- Invoice detail with order, containers, payment history, balance, and recorder for each payment.
- Payment recording with amount capped at the outstanding balance, method (`transfer`, `cash`, `cheque`), reference, paid date, and notes. Saving or deleting a payment recalculates `paid_amount` and derives status `unpaid` / `partial` / `paid`.
- Invoice deletion blocked once payments exist.
- PDF invoice download via dompdf, authorized for admins or the owning customer, available from both admin and customer portals.
- Quote management: list with status filter and inline status update across `new`, `contacted`, `quoted`, `won`, `lost`.

**Notes** — invoices are raised manually by finance; there is no automatic invoice generation from a completed order. Tax is a manual figure, not a calculated rate.

## Reports

**Implemented (basic)**

- **Finance report** over a date range (defaults to current month): invoiced total, paid total, outstanding balance, invoice count, breakdown by status, and top 10 customers by invoiced value.
- **Operations report** over a date range: order list with container counts, breakdown by service type, breakdown by status, and top 10 customers by order count.

**Notes** — reports are rendered from in-memory collections rather than aggregate queries; suitable for current volumes, not for large datasets. No export to Excel or CSV.

## Notifications

**Implemented (database only)**

- Laravel database notifications, shown in the admin and customer shells with an unread counter.
- Notification list with pagination, mark-as-read with redirect to the referenced record, and mark-all-as-read.
- Status changes notify active customer users of the owning company.
- Reported exceptions notify active super admins and ops admins with a link to the tracking detail.
- New quote requests notify active super admins and ops admins.

**Notes** — no email, SMS, or push delivery. The channel is `database` only; queue connection is `sync`.

## Activity log

**Implemented**

- `ActivityLog::record()` captures actor, action key, subject type and id, description, IP address, and timestamp.
- Recorded events include auth login/logout, customer and order create/update, container create/assign/status, invoice create, payment create, user create, document upload, and driver status or issue reports.
- Super-admin-only log viewer with pagination.

**Notes** — the log is append-only from the application and written at explicit call sites, not through model observers, so any new write path must call it deliberately.

## Customer portal

**Implemented**

- Dashboard with order totals, active shipments, and latest invoices.
- Order list and order detail with per-container status, timeline, mini map, and linked documents and invoices.
- Invoice list, invoice detail, and PDF download.
- Document list with download.

All queries are scoped to the authenticated user's `customer_id`, and direct record access is re-checked.

## Driver portal

**Implemented**

- Mobile-first layout with a bottom tab bar.
- Task dashboard listing assigned containers ordered by operational urgency, with completed-versus-total progress.
- Task detail with order and customer context, container data, truck, timeline, and documents.
- Status update form with optional note; coordinates are attached when GPS is running.
- GPS start/stop control with live coordinate and accuracy feedback and send status.
- Document upload and issue reporting; reporting an issue transitions the container to `exception` and alerts operations.

## UI system

**Implemented**

- Custom CSS design system in `resources/css/app.css`: colour, spacing, radius, and elevation tokens; light and dark themes via `data-theme` on the root element; no framework theme overrides required.
- Bootstrap 5 used for grid, forms, and base components; Leaflet for maps.
- Theme preference persisted in `localStorage` and applied before paint to avoid a flash.
- Responsive layouts for desktop, tablet, and mobile; sidebar becomes an off-canvas panel with backdrop, focus-safe escape handling, and scroll lock.
- Accessible patterns: skip link, `aria-expanded` on toggles, labelled form controls, visible focus states, and `prefers-reduced-motion` support.
- Micro-interactions: scroll-aware public navigation, animated status bars, flash message auto-dismiss, and confirmation prompts on destructive actions.

## Deliberate gaps

These are known and intentional for the current scope: no email delivery, no password reset, no CSV/Excel export, no server-side aggregate reporting, no payment gateway, no offline driver mode, no multi-company tenancy, and no API authentication for the tracking endpoint beyond public reference lookup.