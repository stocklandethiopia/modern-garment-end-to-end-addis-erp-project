# Local verification report

**Verification date:** 2026-11-22  
**Application version:** 0.3.0  
**Runtime:** Node.js v22.16.0  
**Package manager:** npm 10.9.2  
**Operating environment:** Linux local container  
**Database:** SQLite schema v2 with 45 application tables

## Executed validation gates

| Gate | Result | Evidence |
|---|---|---|
| JavaScript/MJS syntax | PASS | `npm run check` — 12 files parsed successfully |
| API and SQLite integration suite | PASS | `npm test` — 15 tests reported, 0 failures; 14 named workflow scenarios |
| Required source and documentation artifacts | PASS | `npm run release:gate` — 78 required artifacts checked |
| Build manifest integrity | PASS | `npm run verify:manifest` — 106 file paths, byte sizes and SHA-256 hashes match |
| Requirements traceability | PASS | 30 requirements mapped to source and local acceptance evidence |
| Markdown links | PASS | Relative links across all project Markdown files resolve within the project |
| Runtime version | PASS | Node v22.16.0 satisfies the declared `>=22.5.0` engine |
| Package metadata | PASS | Version 0.3.0, private package scope and validation commands verified |
| Database initialization | PASS | Schema v2 initializes 45 relational application tables |
| Database documentation | PASS | Every live application table has one column-level dictionary section |
| HTTP health | PASS | `/api/health` returns HTTP 200 and connected database status |
| Browser workspace | PASS | Application root, JavaScript, stylesheet and all 19 module navigation entries validated |
| PWA assets | PASS | Manifest, service worker, SVG icon and physical-count worksheet return HTTP 200 |
| Security response headers | PASS | CSP, frame denial, `nosniff`, content type validation verified |
| GitHub Actions workflow contract | PASS | Node 22.x workflow contains the same local quality commands |
| ZIP archive integrity | PASS | Archive CRC, member inventory and SHA-256 validated during package assembly |

## Business workflows exercised

1. Initial Owner setup, one-time initialization and five canonical Stockland locations.
2. CSRF enforcement, user provisioning, required Warehouse Manager assignment and location isolation.
3. Garment product variant creation/update, goods receipt and ledger-derived stock.
4. Server-resolved POS pricing, idempotent retry and aggregate same-SKU oversell protection.
5. POS import control total, automatic validation, duplicate identity, staging and reconciliation.
6. Assigned Branch Manager final release, automatic posting, duplicate release and branch scope.
7. Journal-write failure injection, full POS import transaction rollback and one-time retry posting.
8. Atomic two-leg inventory transfer and source availability validation.
9. Supplier master, purchase order, partial goods receiving, inventory movement and AP creation.
10. Customer master, wholesale order confirmation, stock deduction and accounting posting.
11. Reservations, physical-count reconciliation and accounting-backed variance posting.
12. Damage posting, linked sales return/refund, adjustment and location requisition workflows.
13. Balanced finance journals, supplier/customer payments, expenses, employee/contract/attendance records, payroll posting and daily close.
14. Immutable movement/audit history and frontend/PWA/template asset delivery.

## Transaction-integrity evidence

The import failure-injection scenario creates a database trigger that raises an error during the POS sale journal insertion. The test confirms that the batch remains staged and that the sale and inventory transaction is rolled back as one unit. The trigger is then removed, the same batch is retried, and the sale posts once with the related ledger and balanced journal records.

## Data model

The schema initializes 45 application tables across identity, product master, inventory, POS/import, procurement, reservations, requisitions, physical counts, returns/damage, wholesale, finance/GL/AP/AR, HR/payroll and branch operations. The complete column and foreign-key dictionary is in [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md).

## Reproduce the local run

```bash
node --version
npm run check
npm test
npm run manifest:build
npm run verify:manifest
npm run release:gate
```

The integration suite uses isolated in-memory SQLite and an ephemeral HTTP listener. The build manifest records the exact source/documentation file set, sizes and SHA-256 hashes. The delivered ZIP has its own SHA-256 checksum.
