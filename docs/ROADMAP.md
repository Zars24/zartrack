# Roadmap

Status vocabulary used here:

- **Delivered** — implemented in the current codebase and covered by the smoke test.
- **In progress** — partially implemented or deliberately deferred with a known gap.
- **Planned** — not implemented; a candidate for future work.

This file describes direction only. What exists is documented in [FEATURES.md](FEATURES.md); rules are in [BUSINESS_RULES.md](BUSINESS_RULES.md).

## Delivered

The application was built in stages. All of the following are complete in the current code:

| Stage | Scope | State |
|---|---|---|
| 1 | Project foundation: Laravel 10, Vite, Bootstrap, base configuration | Delivered |
| 2 | Database schema and domain models: customer, driver, truck, order, container, timeline, GPS, document, invoice, payment, quote, activity log | Delivered |
| 3 | Authentication, session login, profile and password management, role middleware | Delivered |
| 4 | Public company profile: home, about, services, contact, quote intake, public tracking | Delivered |
| 5 | Admin dashboard and master-data management | Delivered |
| 6 | Orders and multi-container management, driver and truck assignment | Delivered |
| 7 | Dispatch flow and driver portal with task execution | Delivered |
| 8 | Live GPS tracking: browser geolocation, location history, Leaflet maps | Delivered |
| 9 | Customer portal: shipments, invoices, documents, isolated by company | Delivered |
| 10 | Documents and POD library, invoice generation, PDF export, payment recording | Delivered |
| 11 | Database notifications and basic finance/operations reports | Delivered |
| 12 | Test suite, responsive UI, light/dark theme, activity logging | Delivered |

Stage 12 did not include a production deployment pipeline; the project runs locally and the smoke suite is the verification gate.

## In progress / partially implemented

Honest gaps in things that exist today:

| Area | Current state | What is missing |
|---|---|---|
| Quote pipeline | Statuses `new` → `contacted` → `quoted` → `won` → `lost` can be updated | No conversion action that creates an order from a quote; conversion is manual data entry |
| Notifications | Database channel only | Email and push delivery; per-user notification preferences |
| Reports | Rendered from loaded collections, current-month defaults | Aggregate SQL queries for large datasets, CSV/Excel export, scheduled report delivery |
| Invoicing | Manually created per order, manual tax figure | Automatic invoice generation on delivery, tax rate configuration, recurring or batch billing |
| GPS | Browser geolocation while the task page is open | Background tracking after the screen closes, device or telematics integration, geofencing alerts |
| Driver documents | Upload of POD, surat jalan, other | Photo capture workflow, compression, OCR extraction |
| Tracking API | `GET /api/track/{ref}` returns status, location, and timeline | Token-based partner access, webhooks on status change |
| Audit log | Written explicitly at each call site | Broader coverage via observers, retention policy, export |

## Planned

Ordered by value to a real operation. None of these are implemented.

**Near term**

1. **Quote-to-order conversion** — an action on the quote detail that pre-fills the order form from the lead, closing the loop between marketing and operations.
2. **Email notifications** — a mail channel on `ShipmentStatusChanged` and `OperationalAlert`, with queued delivery.
3. **Password reset** — standard reset flow for admin, customer, and driver accounts, since accounts are currently provisioned only.
4. **Report exports** — CSV download for both finance and operations reports, plus aggregate queries instead of in-memory grouping.
5. **Automatic invoicing** — generate a draft invoice when a container reaches `delivered`, using the order's total price.

**Medium term**

6. **Payment gateway integration** — online payment for customers with callback handling and reconciliation against the `payments` table.
7. **Customer user management** — allow a customer company to manage its own sub-accounts, scoped to its `customer_id`.
8. **Document versioning** — keep superseded versions of DO, surat jalan, and POD instead of replacing records.
9. **Operational alerting rules** — notify operations when a container sits in a state past a threshold (for example, `pickup` exceeding a defined window).
10. **Dashboard filters** — date range and customer filters on the admin dashboard and the tracking list.
11. **Bulk actions** — batch status transitions and batch assignment for multi-container orders.

**Longer term**

12. **Offline-capable driver portal** — service worker, local queue for status updates and GPS pings, background sync on reconnect.
13. **Route and distance tracking** — accumulate travelled distance per container from GPS history, and surface it on the tracking detail.
14. **Multi-company tenancy** — a company scope above customers, if the system were offered as a shared service.
15. **Broadcast updates** — use the existing broadcast scaffolding for live dashboard and tracking refresh without polling.
16. **Analytics** — cycle-time metrics from timeline data: average pickup-to-delivery duration by route and by customer.

## Explicit non-goals

Recorded so scope stays clear:

- General ledger, tax filing, or accounting-system integration.
- Customs filing or freight-rate engines.
- Native iOS/Android applications.
- Multi-currency accounting; amounts are single-currency with render-time formatting.
- Public self-registration for any role.

## How to extend safely

If a change touches shipment state, invoice state, or authorization, read [BUSINESS_RULES.md](BUSINESS_RULES.md) first. The invariants at the end of that file are the ones most likely to be broken by a well-intentioned change:

- container status only through `Container::transitionTo()`
- order status always derived
- invoice status always derived from payments
- record-level ownership checks in every controller that returns customer or driver data
- `ActivityLog::record()` on new write paths