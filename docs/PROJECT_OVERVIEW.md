# Project Overview

## What ZarTrack is

ZarTrack is a web application for a container trucking and logistics company. It covers the full commercial and operational cycle in one system: a public company profile that generates quote requests, an operations back office that plans and dispatches container shipments, a mobile-first driver portal that reports progress and GPS position, and a customer portal that exposes shipments, documents, and invoices.

It is built as a portfolio-grade Laravel application with production-shaped domain rules — not a CRUD demo. Role separation, a validated shipment state machine, data isolation between customers, and an audit trail are enforced in the backend rather than only hidden in the UI.

## Problem it addresses

Container trucking operators typically run on spreadsheets, chat groups, and paper documents. That produces three recurring failures:

1. **No single source of truth for shipment status.** Operations, drivers, and customers each hold a different version of where a container is.
2. **Manual document handling.** Delivery orders, surat jalan, and proof of delivery get lost or duplicated between drivers and the office.
3. **Billing disconnected from operations.** Invoices are raised from memory of what was delivered, and receivables are tracked separately.

ZarTrack keeps order, container, assignment, status history, GPS trail, documents, and invoice in one relational model, so each stage of the operation feeds the next.

## Goals

- Model the real workflow end to end: quote → order → container → assignment → transit → delivery → invoice → payment.
- Enforce authorization on the server, so a customer or driver cannot reach another party's data even by manipulating URLs.
- Give each role a purpose-built interface: dense operational screens for admins, a mobile-first task view for drivers, and a read-mostly portal for customers.
- Keep the shipment status model explicit and validated, with a complete timeline per container.
- Produce a codebase that demonstrates architecture, security, and UI craft to a technical reviewer.

## Scope

**In scope**

- Public website: home, about, services, contact, quote request, public shipment tracking.
- Administration for customers, drivers, fleet, orders, containers, tracking, documents, invoices, payments, quotes, users, and reports.
- Driver portal for task execution, status updates, GPS sharing, document upload, and issue reporting.
- Customer portal for shipment visibility, invoices, and documents.
- Role-based access control, notifications, and activity logging.
- Responsive layout with light and dark themes.

**Out of scope**

- Accounting system integration, tax filing, and general ledger.
- Route optimisation, freight rate engines, and customs filing.
- Native mobile applications and offline-first sync.
- Multi-tenant company separation (the system models one operating company and its customers).

## Users

| Audience | What they use ZarTrack for |
|---|---|
| Prospective customer | Reads services, requests a quote, tracks a shipment by reference without logging in |
| Customer staff | Monitors their own shipments, downloads documents and invoices |
| Operations admin | Creates orders, adds containers, assigns drivers and trucks, drives status forward |
| Document admin | Manages the document library and shipment paperwork, monitors operations |
| Finance | Issues invoices, records payments, reviews receivables and finance reports |
| Driver | Works a task list, updates status, shares GPS, uploads POD, reports issues |
| Super admin | Manages accounts and reviews the audit trail |

## Success criteria

The application is considered complete for its purpose when:

- A quote submitted on the public site reaches the admin quote list and can be progressed.
- An order with multiple containers can be dispatched, tracked, delivered, invoiced, and paid without leaving the system.
- A container's full journey is reconstructable from its timeline, GPS history, and documents.
- Each role only reaches data it is entitled to, verified by automated tests.
- The interface works on desktop, tablet, and phone, in both themes.

## Related documents

- [FEATURES.md](FEATURES.md) — what is implemented, module by module.
- [BUSINESS_RULES.md](BUSINESS_RULES.md) — operational and authorization rules.
- [SYSTEM_ARCHITECTURE.md](SYSTEM_ARCHITECTURE.md) — code structure and data model.
- [ROADMAP.md](ROADMAP.md) — delivered, in-progress, and planned work.