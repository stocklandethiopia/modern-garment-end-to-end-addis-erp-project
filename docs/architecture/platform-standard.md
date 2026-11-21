# Platform Architecture Standard

Status: Initial engineering baseline  
Product: Modern Garment End-to-End Addis ERP  
Decision date: 2026-11-22

## 1. Architecture decision

Adopt a modular monolith for the transactional ERP, with separately deployable specialist compute services only where a clear operational reason exists.

The platform is a multi-tenant SaaS product. Each business domain owns its rules and data boundaries. Modules communicate through application interfaces and versioned domain events; they must not reach into another module's private tables or internal classes.

Do not begin with dozens of microservices. A modular monolith is easier to develop, test, deploy, observe and operate while the product and tenant load are being established. Extract a service only when scale, isolation, security, team ownership or independent release cadence proves the need.

## 2. Technology baseline

| Layer | Baseline | Responsibility |
|---|---|---|
| Web application | Angular 22.x + Angular CLI 22.x | Enterprise web UI, routing, forms, accessibility and client state |
| Web toolchain | Node.js 26.13.1 LTS, TypeScript 6.0.x, npm lockfile | Angular CLI, build, lint, test and asset compilation |
| Main API | PHP 8.5 + Laravel 13.x | Transactional business rules, API, authorization, workflows and jobs |
| Database | PostgreSQL 18.x, production patch pinned | System of record, constraints, transactions and tenant-scoped data |
| Cache and queue broker | Redis 8.2 extended-support line | Cache, queue coordination, rate-limit counters and ephemeral state |
| Specialist services | Python 3.15.x | Optimization, forecasting, document processing and AI adapters |
| Containers | Docker Engine + Compose v2 | Reproducible local, CI and container runtime |
| API contracts | REST + OpenAPI 3.1 | Stable, documented integration boundary |
| Source and CI/CD | GitHub + GitHub Actions | Protected branches, automated quality gates and immutable artifacts |
| Object storage | S3-compatible interface | Documents, images, technical packs, attachments and generated exports |

### Compatibility and release policy

- Angular 22 officially supports Node 22.22.3, 24.15.0 and 26.0.0 version lines; use Node 26.13.1 LTS as the initial toolchain baseline. Angular 22 requires TypeScript 6.0.x.
- Laravel 13 requires PHP 8.3 or later and supports PHP 8.5.
- Node is the Angular build/tooling runtime, not a second competing ERP API.
- Python is an isolated specialist service, not a second owner of finance, payroll, stock or production transactions.
- PostgreSQL 19 has only just reached general availability on the decision date. Start on the mature PostgreSQL 18 line, then evaluate 19 after extension, backup, migration and load-test compatibility is proven.
- Redis 8.2 is selected for its extended-support lifecycle rather than adopting a short-support feature line by default.
- Pin container images and production dependencies to reviewed versions/digests. Do not use floating latest tags, release candidates or unmaintained packages in production.
- Patch versions must be refreshed through a reviewed dependency-update process. This document is not permission to freeze vulnerable packages.

## 3. Why this stack

Angular provides a structured, long-lived enterprise frontend and CLI build system. Laravel provides a mature PHP application platform for large transactional domains, queues, validation, authorization, migrations and scheduled work. PostgreSQL provides relational integrity and transactional consistency for inventory, costing, payroll and double-entry finance. Redis supports fast ephemeral state and background work. Python is reserved for computation that benefits from its data/ML ecosystem.

This uses the requested Node.js, PHP, Python and Docker technologies without creating multiple overlapping APIs or duplicating business logic.

## 4. Runtime topology

Browser / Mobile Web → HTTPS / WAF → Angular Web (static assets or SSR where justified) → Laravel API/Application.

Laravel connects to PostgreSQL (system of record), Redis (cache and queues), S3-compatible object storage and an outbox publisher. Isolated Python workers receive validated, tenant-authorized tasks for optimization and AI. They return recommendations or computed results through versioned service contracts. Laravel remains the authority for approving and committing business transactions.

The API is the authority for business transactions. PostgreSQL is the authority for persisted ERP state. Redis is never the only copy of a business record. Object storage holds binary documents; metadata and access control remain in the ERP database.

## 5. Domain boundaries

Each module owns its application services, policies, validation, persistence mappings, events, API resources and tests. Cross-domain changes use explicit application contracts.

Initial bounded contexts:

1. Platform administration, tenant provisioning and subscription/billing.
2. Organization, legal entities, branches, factories, locations and calendars.
3. Identity, authentication, authorization, roles, delegated administration and audit.
4. CRM, customer, supplier, agent, buyer and partner management.
5. Merchandising, enquiry, quotation, order, style, collection and critical path.
6. Product lifecycle, tech packs, measurements, grading, BOM, routing and revisions.
7. Costing, pricing, margin, quotation and profitability.
8. Procurement, supplier sourcing, contracts, purchase requisitions and purchase orders.
9. Materials planning, MRP, demand, reservations and shortage management.
10. Warehouse, inventory, lot/batch, roll, shade, bin, transfer and stock valuation.
11. Production planning, capacity, line balancing, work centres and subcontracting.
12. Cutting, spreading, marker, bundle, sewing, WIP, finishing and packing execution.
13. Quality, inspection, defects, rework, CAPA and compliance evidence.
14. Logistics, dispatch, export documentation, delivery and returns.
15. Sales, distribution, wholesale, retail, POS and e-commerce integration.
16. Finance, general ledger, AP, AR, cash, bank, tax, fixed assets, budgets and close.
17. Human resources, workforce planning, recruitment, onboarding, employee relations and separation.
18. Time, attendance, shifts, leave, overtime, payroll, benefits, loans and final settlement.
19. Learning and development, training catalogue, competency, certification, skills matrix and training effectiveness.
20. Performance, goals, appraisals, succession, engagement, occupational health and safety.
21. Maintenance, assets, utilities, energy, calibration and facilities.
22. Documents, workflow, approvals, notifications, reporting, BI and integration management.

## 6. Multi-tenant model

- Every tenant-owned record is scoped to a tenant and, where applicable, legal entity, site and operating unit.
- Tenant identity is derived from a verified authenticated context, never trusted from an arbitrary request body.
- Use PostgreSQL row-level security as defence in depth, with explicit tenant-aware repository/service policies.
- All unique keys, foreign keys, background jobs, exports, cache keys, object paths and audit records must preserve tenant boundaries.
- Cross-tenant support access is privileged, time-bound, reason-coded and audited.
- Enterprise isolation options may include dedicated database/schema or dedicated deployment, without changing domain APIs.
- Automated tests must prove that one tenant cannot read, modify, export, infer or delete another tenant's records.

## 7. API and event contracts

- Use /api/v1 versioned endpoints and OpenAPI 3.1 as the published contract.
- Validate every request at the boundary; return consistent error envelopes and correlation IDs.
- Use idempotency keys for payment, posting, stock movement, payroll run, order submission and other retryable commands.
- Use an outbox pattern for reliable event publication after database commit.
- Events are versioned, tenant-scoped, timestamped and traceable; consumers must be idempotent.
- Never expose ORM entities or internal database structure directly as public API contracts.
- Breaking API changes require a compatibility plan and deprecation period.

## 8. Financial and inventory integrity

- Finance uses a double-entry ledger. Posted journals balance; posted records are immutable and corrected through reversal/adjustment workflows.
- Inventory movements are ledgered and traceable to source documents, lots/rolls, locations, units, ownership and valuation.
- Quantity, unit-of-measure, currency and exchange-rate precision are explicit. No floating-point arithmetic for money.
- Costing must preserve material, labour, overhead, subcontract, freight, duty and variance components.
- Period close, stock close, payroll close and year-end operations are permissioned, auditable and reversible only through controlled correction workflows.

## 9. Architecture guardrails

- No business logic in controllers, UI components, database triggers alone, or Python notebooks.
- No direct cross-module table writes.
- No tenant-specific names, branches, warehouses, rates or employee records hard-coded into product code.
- No secrets, production data, tokens or private certificates in source control.
- No production deployment from an unreviewed branch.
- No module is marked complete without acceptance tests and operational documentation.
