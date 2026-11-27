# Application architecture

## Runtime topology

```text
Browser / installable PWA shell
          │ same-origin HTTP(S)
          ▼
Node.js HTTP server — src/server.mjs
   ├── identity, session and CSRF
   ├── Product, Inventory, POS, Import, Report and Audit routes
   ├── Enterprise domain dispatcher
   └── static browser/PWA delivery
          │
          ▼
Enterprise services — src/modules/enterprise.mjs
   ├── masters and procurement
   ├── requisitions, reservations and counts
   ├── returns, damage and wholesale
   ├── finance, GL, AP and AR
   └── HR, payroll and daily close
          │
          ▼
SQLite — src/db.mjs + src/migrations/
   ├── 45 relational tables
   ├── immutable inventory movement ledger
   ├── double-entry journal records
   └── audit history
```

## Source ownership

- `src/server.mjs`: server lifecycle, request parsing, session authentication, core APIs, POS sale construction and static asset delivery.
- `src/security.mjs`: password hashing, tokens, cookie helpers and cryptographic primitives.
- `src/db.mjs`: SQLite connection, core schema, transaction helper, location seed and stock-balance query.
- `src/migrations/002_enterprise.sql`: enterprise tables, indexes, seed accounts and schema version 2.
- `src/modules/enterprise.mjs`: enterprise routes, domain validation, stock calculations, journal helper and operational workflows.
- `public/`: application shell, responsive UI, PWA manifest, service worker and icon.
- `templates/`: CSV worksheets for POS, physical count, products, suppliers/purchase orders and employees.
- `tests/`: HTTP + SQLite acceptance suite.

## Data ownership and financial integrity

`inventory_movements` is the source of truth for on-hand quantities. `journal_entries` and `journal_lines` provide the double-entry financial record. Business documents preserve source IDs and actor references. Audit events record key state transitions.

## Transaction boundaries

The synchronous SQLite transaction helper uses `BEGIN IMMEDIATE`, commits after the complete callback and rolls back on exceptions. POS submission, POS import release, transfer, receiving, physical count release, damage, return, wholesale confirmation, payroll and payment operations group related records within transactions.

## Frontend

`public/index.html` loads `app.css` and `app.js`. The client renders role-aware navigation and forms, performs JSON requests with CSRF tokens, and registers `sw.js`. The service worker caches the static application shell and keeps `/api/` calls network-only.

## Extension contracts

New modules follow the validation, role/location, transaction and audit helpers; add schema through a numbered SQL file, module documentation, acceptance tests and an updated SHA-256 build manifest.
