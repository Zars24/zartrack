# AI Agent Guide

Working instructions for AI coding agents operating on this repository. These rules are binding unless the user explicitly overrides them.

## Before starting

1. Read this file, [PROJECT_OVERVIEW.md](PROJECT_OVERVIEW.md), and [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md).
2. Read [BUSINESS_RULES.md](BUSINESS_RULES.md) before touching shipment status, invoicing, or authorization.
3. Audit the actual code — structure, dependencies, database, existing features — rather than assuming from documentation.
4. Do not repeat planning, requirements, or explanations that are already documented. Build on what exists.

## Scope discipline

5. Do only what the user asked. No unrequested refactors, renames, file moves, or dependency additions.
6. Work in stages with a clear scope. Do not begin the next stage without instruction.
7. Do not delete, overwrite, or change working features without a stated reason and approval.
8. Never run migrations, database resets, or other destructive commands without explicit permission.
9. Avoid over-engineering, unnecessary dependencies, and duplicated code. Prefer the standard library, the framework, and code already in the repository.
10. If the task is clear, start working. Ask only when blocked or when a decision would have significant impact.

## Preserve the invariants

11. Container status must only change through `Container::transitionTo()`. Direct writes to `status` bypass validation, timeline, order rollup, and notifications.
12. Order status is derived by `Order::syncStatusFromContainers()`. Never assign it directly.
13. Invoice `status` and `paid_amount` are derived by `Invoice::recalculate()`, triggered by `Payment` model hooks. Never set them by hand.
14. Authorization is enforced in two layers: the `role` middleware on routes, and record-level ownership checks inside controllers. Both layers must stay intact for any new route.
15. New write paths that matter must call `ActivityLog::record()`; there is no observer that does it automatically.
16. Domain enums live in `config/zartrack.php`. Do not duplicate status, role, service-type, container-size, or document-type lists in views or controllers.

## Implementation quality

17. Implement validation, authorization, security, and error handling as part of the change, not as a follow-up.
18. Reuse existing UI primitives in `resources/css/app.css` (`.card`, `.toolbar`, `.form-section`, `.chip`, `.list-row`, `.kv`, `.route`, `.stat-card`, `.empty-state`, `.map-frame`, `.timeline`, `.hero--page`, `.pub-page`, `.page-head`, `.cap-card`) and the `<x-icon>` / `<x-status>` components rather than adding one-off styles or markup.
19. Preserve light and dark theme support: use the semantic tokens, never hard-code colours that assume one theme.
20. Keep layouts consistent: `admin` for back office, `portal` for customers, `driver` for the mobile-first driver portal, `base` for public and auth pages.
21. Preserve accessibility: labelled form controls, `aria-*` on toggles, focus states, and `prefers-reduced-motion` behaviour.
22. Follow existing conventions: thin controllers, domain logic on models, flash messages on `success`, Blade escaping by default.

## Verification

23. After implementing, run the relevant checks:
    - `php artisan test` for behaviour and regressions.
    - `npm run build` if CSS or JS changed.
    - `php artisan view:cache` if Blade templates changed, to catch compile errors.
24. If a change affects a documented rule, update the relevant document in `docs/` in the same change.
25. Report concisely: what changed, main files touched, tests run, and any remaining limitation.

## Documentation rules

26. `README.md` stays at the repository root and stays short — it is the GitHub landing page, in English.
27. Detailed material belongs in `docs/`: overview, features, business rules, architecture, roadmap, and this guide. Do not duplicate content between files; link instead.
28. Never claim a feature exists unless it is implemented and verifiable in the codebase. If it is partial, say so.
29. Keep documents in English. Application UI copy is Indonesian; that is intentional and should not be translated without instruction.

## Things that will break the application

- Writing `$container->status` directly instead of calling `transitionTo()`.
- Setting order `status` without going through `syncStatusFromContainers()`.
- Setting invoice `status` or `paid_amount` without going through `recalculate()`.
- Adding a route without the correct `role:` middleware, or a controller that returns customer or driver data without an ownership check.
- Removing `hasRole()` checks in the admin layout navigation while leaving the routes open, or vice versa — the UI filter and the middleware must stay aligned.
- Replacing the `notifCount` view composer or changing which layouts register it; the admin and customer shells read that variable.
- Bumping Bootstrap or removing its CSS import, since the design system layers on top of it.
- Dropping `storage:link`; document downloads and uploads depend on the `public` disk.

## Current known gaps

Do not treat these as bugs to fix unprompted — they are documented decisions. See [ROADMAP.md](ROADMAP.md) for detail.

- No email or push notifications (database channel only).
- No password reset or self-registration.
- No automatic invoice creation, no payment gateway.
- No CSV or Excel export; reports aggregate in memory.
- No background GPS tracking once the driver page closes.
- No multi-company tenancy.
- Single migration for the whole domain schema.