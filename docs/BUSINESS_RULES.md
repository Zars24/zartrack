# Business Rules

Operational and authorization rules as enforced by the code. Each rule names where it lives, because these are real constraints rather than conventions.

## Roles and access

Roles are defined in `config/zartrack.php` (`roles`) and enforced by `App\Http\Middleware\EnsureRole`, registered as the `role` alias in `app/Http/Kernel.php`.

| Capability | super_admin | ops_admin | doc_admin | finance | driver | customer |
|---|---|---|---|---|---|---|
| Admin dashboard | yes | yes | yes | yes | — | — |
| Customers (CRUD) | yes | yes | — | — | — | — |
| Drivers (CRUD) | yes | yes | — | — | — | — |
| Trucks (CRUD) | yes | yes | — | — | — | — |
| Orders & containers (CRUD, assign) | yes | yes | — | — | — | — |
| Container status transition | yes | yes | — | — | own tasks | — |
| Tracking (read) | yes | yes | yes | yes | own tasks | own orders |
| Documents (read/download) | yes | yes | yes | yes | own tasks | own company |
| Documents (create/delete) | yes | yes | yes | — | POD upload for own tasks | — |
| Invoices (read, PDF) | yes | yes | yes | yes | — | own company |
| Invoices (create/edit/delete) | yes | — | — | yes | — | — |
| Payments | yes | — | — | yes | — | — |
| Finance report | yes | — | — | yes | — | — |
| Operations report | yes | yes | yes | — | — | — |
| Quotes (read, update status) | yes | yes | yes | yes | — | — |
| Users (CRUD) | yes | — | — | — | — | — |
| Activity log | yes | — | — | — | — | — |
| Profile & password | yes | yes | yes | yes | yes | yes |
| Notifications | yes | yes | yes | yes | yes | yes |

Additional rules:

- Every authenticated role passes through `EnsureRole`, which aborts 403 when the user is missing, inactive, or outside the allowed roles.
- Login is refused for inactive accounts even with correct credentials; the session is terminated immediately.
- Landing page after login depends on role: admins to `/admin`, drivers to `/driver`, customers to `/customer`.
- A user cannot deactivate or delete their own account (422).
- Customer data is isolated by `customer_id`: the portal aborts 403 on orders, invoices, and documents belonging to another company.
- Drivers are isolated by `driver_id`: task pages and all task actions abort 403 for containers assigned to another driver.
- Invoice PDF and document download re-verify ownership at the point of download, not only in the listing.

## Quote intake

- Anyone, authenticated or not, may submit a quote request. The endpoint is throttled to 5 submissions per minute per client.
- Required fields: name, email, phone, service type, origin, destination. Company, cargo details, and message are optional.
- Service type must be one of `trucking`, `freight`, `warehousing`, `logistics`.
- A quote is always created with status `new` and **never** becomes an order automatically. Conversion is a manual operations decision.
- Quote status values: `new`, `contacted`, `quoted`, `won`, `lost`.
- Submitting a quote notifies active super admins and ops admins in-app.

## Customers

- Status is `active` or `inactive`. Only active customers can be selected when creating an order.
- A customer with any order cannot be deleted (422).
- Customer accounts are provisioned by an administrator; there is no public registration.
- A customer user must be linked to a customer record; the link is required when the assigned role is `customer`.
- Customers are soft-deleted.

## Drivers and fleet

- Driver status values: `available`, `on_duty`, `inactive`.
- Truck status values: `available`, `in_use`, `maintenance`.
- Only `available` and `on_duty` drivers, and `available` and `in_use` trucks, appear in the assignment picker.
- Assigning a container sets the driver to `on_duty` and the truck to `in_use`.
- Reaching `delivered` releases both back to `available`.
- A driver with any task not in `completed` or `cancelled` cannot be deleted.
- A truck with any container not in `completed` or `cancelled` cannot be deleted.
- A driver record is optional in relation to a login account, but the driver portal requires the link; without it the dashboard aborts 403.
- Creating or updating a driver with an email address creates or updates the matching `driver` user account.
- Drivers are soft-deleted; trucks are hard-deleted.

## Orders and containers

- An order belongs to exactly one customer and contains zero or more containers.
- Service type must be one of `trucking`, `freight`, `warehousing`, `logistics`.
- Delivery date must be on or after the pickup date.
- Order numbers are generated as `ORD-{yymmdd}-{5 random characters}` and are unique.
- Container sizes are restricted to `20ft`, `40ft`, `40hc`, `45hc`.
- Container tracking references are generated as `ZT` + 10 random characters, unique, and used by public tracking.
- A new container always starts at `pending` and receives an initial `pending` timeline entry.
- An order with invoices cannot be deleted.
- A container can only be deleted while `pending` or `cancelled`; active containers must be cancelled first.
- An order's status is derived from its containers rather than set directly.

### Order status rollup

`Order::syncStatusFromContainers()` runs on every container transition. It discards cancelled containers and selects the least-advanced remaining container status using this rank:

`pending` 0, `confirmed` 1, `assigned` 2, `pickup` 3, `in_transit` 4, `delivered` 5, `completed` 6, `on_hold` 7, `returning_empty` 8, `exception` 9.

When all containers are cancelled, or none exist, the order becomes `cancelled`.

Consequence of the ranking: an order is only as advanced as its slowest container, so with one container in transit and one pending the order stays `pending`. Side states rank above `completed`, which means a container on hold or in exception does not by itself hold back an order that still has an earlier-stage container.

## Shipment status machine

Transitions are declared in `config/zartrack.php` (`transitions`) and enforced by `Container::transitionTo()`. Any transition not listed is rejected with an invalid-transition error.

```
pending        → confirmed | cancelled | on_hold
confirmed      → assigned  | cancelled | on_hold
assigned       → pickup    | cancelled | on_hold
pickup         → in_transit | exception | on_hold
in_transit     → delivered | exception | on_hold | returning_empty
delivered      → completed
completed      → (terminal)
cancelled      → (terminal)
on_hold        → pending | confirmed | assigned | cancelled
returning_empty → completed | exception
exception      → in_transit | returning_empty | cancelled | on_hold
```

Rules that apply to every transition:

- `transitionTo()` is the only supported write path for container status. It validates, updates the container, writes a timeline row with actor, note, and optional coordinates, rolls up the order status, and dispatches notifications.
- Entering `pickup` stamps `pickup_at`; entering `delivered` stamps `delivered_at` and releases the driver and truck.
- Entering `exception` notifies active super admins and ops admins with a link to the tracking detail.
- Every transition notifies active customer users belonging to the owning company.
- An invalid transition must never be written; the admin and driver endpoints both catch the error and return a validation message.
- `completed` and `cancelled` are terminal — no further transitions are accepted.

## Assignment

- Assigning a driver and truck is required before a container can reach `pickup`.
- Assigning from `pending` advances to `confirmed` and then to `assigned` in the same action.
- Assigning from `confirmed` advances straight to `assigned`.
- Re-assignment does not regress the status; only the driver and truck change.

## GPS

- GPS submissions are accepted only while the container is `pickup` or `in_transit`. Any other state returns 422.
- Latitude must be within −90..90 and longitude within −180..180.
- Optional speed (0–300) and heading (0–360) are recorded when provided.
- Each submission stores container, driver, coordinates, speed, heading, and recording timestamp.
- The driver portal uses `watchPosition` with a 30-second polling fallback and sends coordinates with the next status update when the watch is active.
- Position data is never accepted for containers assigned to a different driver.

## Documents

- Document types: `do` (Delivery Order), `surat_jalan`, `pod` (Proof of Delivery), `invoice`, `other`.
- Admin uploads accept PDF, JPG, JPEG, PNG, DOC, and DOCX up to 10 MB.
- Driver uploads accept only `pod`, `surat_jalan`, or `other`, and only PDF or image formats, up to 10 MB.
- Files are stored on the `public` disk; a storage link is required for browser access.
- Download requires admin role or ownership of the related order; missing files return 404.
- Deleting a document removes the stored file as well as the record.
- Documents may attach to an order, a container, or both.

## Invoicing and payments

- An invoice belongs to exactly one order and requires issue date and due date; the due date cannot precede the issue date.
- Tax is entered manually and cannot exceed the total. Subtotal is derived as total − tax.
- Invoice numbers are generated as `INV-{yymmdd}-{4 random characters}` and are unique.
- Invoice status is derived, never set by hand: `unpaid` when nothing is paid, `partial` when paid is greater than zero but below total, `paid` when paid reaches or exceeds total.
- Recording a payment updates `paid_amount` and recomputes the status via `Invoice::recalculate()`.
- A payment cannot exceed the outstanding balance; amounts must be greater than zero.
- Payment methods: `transfer`, `cash`, `cheque`.
- An invoice with any payment cannot be deleted.
- Balance is never negative: `max(0, total − paid_amount)`.
- PDF invoices are available to admins and to the customer who owns the order.

## Reports

- Both reports default to the current calendar month when no range is supplied.
- The finance report aggregates by invoice issue date; the operations report aggregates by order creation date.
- Report totals: invoiced, paid, outstanding, and invoice count for finance; order counts by service, status, and customer for operations.
- Reports are read-only and never mutate data.

## Notifications

- Channel is database only; no email or push is sent.
- Shipment status changes notify active `customer` users of the owning company.
- Exceptions notify active `super_admin` and `ops_admin` users.
- New quote requests notify active `super_admin` and `ops_admin` users.
- Marking a notification read redirects to the record it references.
- Only the owning user can read or mark their own notifications.

## Audit trail

- The activity log is written explicitly at the action site, not by a model observer. New write paths must call `ActivityLog::record()`.
- Recorded actions: `auth.login`, `auth.logout`, `customer.create`, `customer.update`, `order.create`, `order.update`, `container.create`, `container.assign`, `container.status`, `driver.status`, `driver.issue`, `document.create`, `invoice.create`, `payment.create`, `user.create`.
- Each entry stores actor, action, subject type and id, description, IP address, and timestamp.
- The log viewer is restricted to `super_admin`. Entries are not editable or deletable from the UI.

## Rate limits

| Endpoint | Limit |
|---|---|
| `POST /quote` | 5 per minute |
| `POST /login` | 10 per minute |
| `GET /api/track/{ref}` | 30 per minute |

## Invariants worth preserving

1. Container status only changes through `transitionTo()`.
2. Order status is always derived, never assigned directly.
3. Invoice status is always derived from the sum of payments.
4. A container is never visible to a driver other than the assigned one.
5. A record is never visible to a customer outside their own company.
6. Terminal container states (`completed`, `cancelled`) accept no further transitions.
7. Money fields are decimal with two places; balances never go negative.