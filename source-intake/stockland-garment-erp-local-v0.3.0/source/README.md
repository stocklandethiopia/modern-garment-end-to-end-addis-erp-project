# Stockland Garment ERP — Full-Stack Local Application

**Application version:** 0.3.0  
**Documentation revision:** 3.0  
**Release package:** Stockland Ethiopia Garment ERP local source release  
**Runtime:** Node.js 22.5+ with built-in SQLite

## Purpose

A full-stack operating workspace for Stockland Ethiopia's garment retail, wholesale, warehouse and company administration activities. It combines a browser-based application, REST API, relational data model, inventory ledger, accounting journals, operational workflows, reporting, test suite and engineering documentation.

## Functional modules

| Module | Main functions |
|---|---|
| Command centre | Business indicators, stock summary, sales and pending import overview |
| Identity and access | First-owner setup, login, sessions, seven roles, location assignment and user administration |
| Product master | SKU, brand, category, department, age group, size, colour, barcode, unit, cost, retail and wholesale prices |
| Suppliers and customers | Supplier master, retail/wholesale/reseller/customer profiles, contacts and balances |
| Procurement | Purchase orders, partial receipts, goods receipts and accounts-payable generation |
| Inventory | Central Warehouse and branch balances, receipts, two-leg transfers, adjustments and immutable movement ledger |
| Requisitions | Branch requests, approval and atomic source/destination movement |
| Reservations | Customer reservation, stock availability protection, release and fulfilment |
| Physical counts | Manual or CSV worksheet entry, ledger reconciliation and accounting-backed variance release |
| Retail POS | Server-priced sales, payment method, duplicate protection, COGS, revenue and inventory posting |
| POS imports | CSV upload, automatic validation, duplicate detection, staging, reconciliation and Branch Manager release |
| Returns and damage | Original-sale-linked returns, refund methods, store credit and damage write-off |
| Wholesale | Customer-specific wholesale orders, confirmation, stock movements, AR credit and accounting |
| Finance | Chart of accounts, balanced journals, expenses, AP, AR, receipts/payments and trial balance |
| HR and payroll | Employees, employment contracts, attendance, payroll calculations and journal posting |
| Daily closing | Branch cash reconciliation, payment-channel totals and close status |
| Reports and audit | Sales, stock valuation, activity history and immutable audit records |

## Stockland locations

- `CENTRAL_WAREHOUSE` — Central Warehouse
- `GORO_BRANCH` — Goro Branch
- `GERJI_BRANCH` — Gerji Branch
- `MEXICO_BRANCH` — Mexico Branch
- `MEGENAGNA_BRANCH` — Megenagna Branch

## Technology requirements

- Node.js 22.5.0 or later
- npm 10 or later (included with supported Node.js releases)
- Current Chromium, Firefox or Safari browser
- Local persistent directory writable by the application process

The backend uses Node's native HTTP server, SQLite, cryptographic primitives and test runner. The frontend uses semantic HTML, CSS and browser JavaScript. Runtime dependencies are native Node modules; no dependency installation step is required.

## Quick start

```bash
node --version
npm run bootstrap
npm start
```

Open `http://127.0.0.1:3000`. The setup screen creates the first Owner account and initializes the five Stockland locations. Use a unique password with at least 12 characters.

For a development watcher:

```bash
npm run dev
```

## Local quality commands

```bash
npm run manifest:build
npm run check
npm test
npm run verify:manifest
npm run release:gate
```

The release gate checks application assets, required documentation, traceability, relative Markdown links, the in-memory database, HTTP health, PWA assets and browser security headers. The integration suite exercises 14 business workflow scenarios against a fresh SQLite database, including an injected accounting-post failure and a successful retry.

## POS import release rule

`UPLOAD → AUTO-VALIDATE → STAGE → RECONCILE → BRANCH MANAGER RELEASE → AUTOMATIC ATOMIC POST`

Uploading a file is intake only. The assigned Branch Manager is the final and sole business approver. Successful release automatically posts the reconciled sales, payment and stock movements. The workflow does not insert an additional Owner/Management approval after Branch Manager release.

## Project structure

```text
STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK/
├── .github/                 CI workflow and workflow guide
├── docs/                    Engineering, module and release documentation
├── public/                  Browser UI, stylesheet, PWA shell and icons
├── reference-sources/       Isolated licensed open-source reference code
├── scripts/                 Bootstrap, static checks and release gates
├── src/                     HTTP API, security, database and domain modules
│   ├── migrations/           Versioned SQLite schema migrations
│   └── modules/              Procurement, inventory, finance, HR and operations
├── templates/               CSV worksheets and import templates
└── tests/                   End-to-end API and SQLite acceptance tests
```

Every implementation folder has a `README.md`. The `docs/` folder contains detailed contracts, API routes, all 45 relational table definitions, module specifications, acceptance cases, local verification evidence and future extension interfaces.

## Data and integrity principles

- Money uses integer minor units (ETB cents) in storage.
- Quantities use up to three decimal places.
- Stock balances are derived from inventory movements.
- Transfers post both sides together in one database transaction.
- Posted journals must balance exactly.
- POS and import submissions use stable identities and duplicate protection.
- Posted inventory ledger and audit records are immutable.
- Role and location authorization are applied in API handlers.
- Import and reconciliation stages are kept separate from final posting.

## License and source attribution

Stockland-authored application code is contained in the project folders. Selected MIT-licensed X POS reference files are isolated beneath `reference-sources/OPEN_SOURCE_REFERENCE_KIT/`; the upstream MIT license and source manifest are kept alongside those files. See `docs/OPEN_SOURCE_PROVENANCE.md`.

## Verification entry points

- [Documentation index](docs/README.md)
- [Technical requirements](docs/TECHNICAL_REQUIREMENTS.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Database schema](docs/DATABASE_SCHEMA.md)
- [API reference](docs/API_REFERENCE.md)
- [Workflow contracts](docs/WORKFLOWS.md)
- [POS import release contract](docs/POS_IMPORT_RELEASE.md)
- [Test strategy](docs/TEST_STRATEGY.md)
- [Release gates](docs/RELEASE_GATES.md)
- [Verification report](docs/VERIFICATION_REPORT.md)
