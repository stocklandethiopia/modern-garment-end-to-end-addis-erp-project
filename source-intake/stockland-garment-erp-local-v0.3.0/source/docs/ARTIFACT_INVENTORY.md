# Complete artifact inventory

**Application:** Stockland Garment ERP  
**Version:** 0.3.0  
**Maintained files:** 107 (including this inventory and the generated build manifest)  
**Data schema:** SQLite v2 · 45 application tables

Every maintained project file is listed by its exact repository-relative path. `BUILD_MANIFEST.json` records the byte size and SHA-256 hash of every maintained file other than itself. The SQLite application database is initialized by the bootstrap and migration flow.

## File inventory

### `.github/`

| Exact path | Purpose |
|---|---|
| [`.github/README.md`](../.github/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`.github/workflows/README.md`](../.github/workflows/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) | GitHub Actions automation for runtime, syntax, manifest, integration and release gates. |

### `docs/`

| Exact path | Purpose |
|---|---|
| [`docs/API_REFERENCE.md`](API_REFERENCE.md) | REST methods, paths, access roles, requests, responses and error conventions. |
| [`docs/ARCHITECTURE.md`](ARCHITECTURE.md) | Runtime topology, service ownership, data flow and transaction boundaries. |
| `docs/ARTIFACT_INVENTORY.md` | File-by-file source, documentation, tests, templates and workflow inventory. |
| [`docs/DATABASE_SCHEMA.md`](DATABASE_SCHEMA.md) | 45-table relational catalogue with columns, types, constraints and foreign keys. |
| [`docs/DECISIONS.md`](DECISIONS.md) | Recorded architecture and business workflow decisions. |
| [`docs/IMPLEMENTATION_BOUNDARIES.md`](IMPLEMENTATION_BOUNDARIES.md) | Layer ownership, service contracts, shared invariants and module extension rules. |
| [`docs/KNOWN_LIMITATIONS.md`](KNOWN_LIMITATIONS.md) | Integration catalogue and stable extension points for future platform services. |
| [`docs/OPEN_SOURCE_PROVENANCE.md`](OPEN_SOURCE_PROVENANCE.md) | Upstream project, included source paths, licenses and adaptation boundaries. |
| [`docs/OPERATIONS_RUNBOOK.md`](OPERATIONS_RUNBOOK.md) | Bootstrap, operation, user administration, backup, restore and maintenance procedures. |
| [`docs/POS_IMPORT_RELEASE.md`](POS_IMPORT_RELEASE.md) | CSV format, validation, staging, reconciliation, final approval and atomic posting. |
| [`docs/PROJECT_CHARTER.md`](PROJECT_CHARTER.md) | Company context, roles, locations, functional scope and business invariants. |
| [`docs/README.md`](README.md) | Engineering documentation index and module guide. |
| [`docs/RELEASE_GATES.md`](RELEASE_GATES.md) | Required local validation sequence, artifact checks and CI workflow contract. |
| [`docs/RELEASE_NOTES.md`](RELEASE_NOTES.md) | Versioned application feature and engineering change summary. |
| [`docs/REQUIREMENTS_TRACEABILITY.csv`](REQUIREMENTS_TRACEABILITY.csv) | Requirement-to-source-to-test evidence mapping. |
| [`docs/RISK_REGISTER.md`](RISK_REGISTER.md) | Operational, access, stock, POS, accounting and source-integrity safeguards. |
| [`docs/SECURITY_MODEL.md`](SECURITY_MODEL.md) | Authentication, sessions, CSRF, role/location permissions and data integrity controls. |
| [`docs/TECHNICAL_REQUIREMENTS.md`](TECHNICAL_REQUIREMENTS.md) | Node runtime, browser, data storage, network and environment configuration. |
| [`docs/TEST_STRATEGY.md`](TEST_STRATEGY.md) | Test layers, business acceptance scenarios and repeatable execution commands. |
| [`docs/VERIFICATION_REPORT.md`](VERIFICATION_REPORT.md) | Executed local verification commands, test scenarios and integrity evidence. |
| [`docs/WORKFLOWS.md`](WORKFLOWS.md) | End-to-end business process contracts for all delivered ERP modules. |
| [`docs/modules/BRANCH_OPERATIONS.md`](modules/BRANCH_OPERATIONS.md) | Canonical locations, role scope, requisition and daily-close workflows. |
| [`docs/modules/FINANCE.md`](modules/FINANCE.md) | Chart of accounts, journal, expense, AP/AR, payment and trial balance contract. |
| [`docs/modules/HR_PAYROLL.md`](modules/HR_PAYROLL.md) | Employee, contract, attendance, payroll and journal posting contract. |
| [`docs/modules/INVENTORY.md`](modules/INVENTORY.md) | Ledger, availability, reservations, counts, transfer and damage contracts. |
| [`docs/modules/POS.md`](modules/POS.md) | Retail basket, server pricing, sale identity, payment and import interfaces. |
| [`docs/modules/PROCUREMENT.md`](modules/PROCUREMENT.md) | Supplier, purchase order, receiving, bill and AP workflow contract. |
| [`docs/modules/PRODUCT_CATALOG.md`](modules/PRODUCT_CATALOG.md) | Garment SKU, brand, size, colour, barcode, unit and price contract. |
| [`docs/modules/README.md`](modules/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`docs/modules/WHOLESALE_CUSTOMERS.md`](modules/WHOLESALE_CUSTOMERS.md) | Customer master, wholesale orders, price and receivable contract. |
| [`docs/standards/ENGINEERING_DOC_TEMPLATE.md`](standards/ENGINEERING_DOC_TEMPLATE.md) | Standard format for module, API and architecture documentation. |
| [`docs/standards/README.md`](standards/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`docs/standards/RELEASE_EVIDENCE_TEMPLATE.md`](standards/RELEASE_EVIDENCE_TEMPLATE.md) | Standard format for build, test and release evidence. |

### `public/`

| Exact path | Purpose |
|---|---|
| [`public/README.md`](../public/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`public/app.css`](../public/app.css) | Responsive application layout, navigation, forms, tables, dialogs and visual styling. |
| [`public/app.js`](../public/app.js) | Role-aware ERP navigation, module screens, forms, data tables and API integration. |
| [`public/icons/README.md`](../public/icons/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`public/icons/stockland.svg`](../public/icons/stockland.svg) | Stockland application icon and PWA visual identity. |
| [`public/index.html`](../public/index.html) | Browser application entry point and PWA metadata links. |
| [`public/manifest.webmanifest`](../public/manifest.webmanifest) | Installable PWA metadata and application identity. |
| [`public/physical-count-template.csv`](../public/physical-count-template.csv) | Blank CSV worksheet for physical stock count upload. |
| [`public/sw.js`](../public/sw.js) | Application-shell service worker and cache lifecycle. |

### `reference-sources/`

| Exact path | Purpose |
|---|---|
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/LICENSE_COMPLIANCE.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/LICENSE_COMPLIANCE.md) | The selected files from `kodlyft/xpos` are distributed under the MIT License declared by the upstream repository. The complete license text is pr |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/SOURCE_MANIFEST.json`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/SOURCE_MANIFEST.json) | Upstream snapshot metadata, file paths, blob identifiers and license record. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/SOURCE_REGISTER.csv`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/SOURCE_REGISTER.csv) | Open-source repository, license and engineering-context register. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/LICENSE`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/LICENSE) | Original upstream license text governing the adjacent source files. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/ORIGIN.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/ORIGIN.md) | - Project: X POS |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/src/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/src/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/src/services/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/src/services/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/src/services/invoiceSubmission.ts`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/src/services/invoiceSubmission.ts) | Upstream MIT browser reference source retained at its upstream-relative path. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/src/services/offlinePolicy.ts`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/frontend/src/services/offlinePolicy.ts) | Upstream MIT browser reference source retained at its upstream-relative path. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/api/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/api/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/api/tests/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/api/tests/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/api/tests/test_invoice_retry.py`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/api/tests/test_invoice_retry.py) | Upstream MIT reference source retained at its upstream-relative path. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/api/tests/test_stock_guards.py`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/api/tests/test_stock_guards.py) | Upstream MIT reference source retained at its upstream-relative path. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/api/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/api/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/api/item_processing/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/api/item_processing/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/api/item_processing/barcode.py`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/api/item_processing/barcode.py) | Upstream MIT reference source retained at its upstream-relative path. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/api/item_processing/stock.py`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/xpos/x_pos/api/item_processing/stock.py) | Upstream MIT reference source retained at its upstream-relative path. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/source-review/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/source-review/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/source-review/SOURCE_REVIEW.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/source-review/SOURCE_REVIEW.md) | Repository review notes, upstream source context and license register explanation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/MODULE_MAPPING.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/MODULE_MAPPING.md) | This mapping relates the selected upstream concepts to the Stockland application's functional service boundaries. The existing project architectu |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/CATALOG_VARIANT_CONTRACT.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/CATALOG_VARIANT_CONTRACT.md) | A saleable garment must resolve to a unique Stockland SKU. At minimum, the catalogue needs: |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/INVENTORY_LEDGER_CONTRACT.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/INVENTORY_LEDGER_CONTRACT.md) | The logical Stockland locations are Central Warehouse, Goro Branch, Gerji Branch, Mexico Branch and Megenagna Branch. Preserve the canonical loca |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/OFFLINE_POS_CONTRACT.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/OFFLINE_POS_CONTRACT.md) | Offline mode is a bounded capability, not a promise of global real-time inventory. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/POS_IMPORT_RELEASE_CONTRACT.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/POS_IMPORT_RELEASE_CONTRACT.md) | This is the required Stockland sequence: |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/tests/README.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/tests/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`reference-sources/OPEN_SOURCE_REFERENCE_KIT/tests/STOCKLAND_ACCEPTANCE_MATRIX.md`](../reference-sources/OPEN_SOURCE_REFERENCE_KIT/tests/STOCKLAND_ACCEPTANCE_MATRIX.md) | These cases define the Stockland acceptance criteria and identify the expected behavior for each workflow. |
| [`reference-sources/README.md`](../reference-sources/README.md) | Folder ownership, contents, extension rules and related documentation. |

### `scripts/`

| Exact path | Purpose |
|---|---|
| [`scripts/README.md`](../scripts/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`scripts/bootstrap.mjs`](../scripts/bootstrap.mjs) | Interactive initial Owner account and canonical location setup. |
| [`scripts/build-manifest.mjs`](../scripts/build-manifest.mjs) | Builds the exact SHA-256 file inventory for the package. |
| [`scripts/check.mjs`](../scripts/check.mjs) | JavaScript and MJS syntax verification runner. |
| [`scripts/release-gate.mjs`](../scripts/release-gate.mjs) | Documentation, traceability, runtime, UI, HTTP, PWA and security release-gate runner. |
| [`scripts/verify-manifest.mjs`](../scripts/verify-manifest.mjs) | Verifies file paths, byte sizes and SHA-256 hashes against the build manifest. |

### `src/`

| Exact path | Purpose |
|---|---|
| [`src/README.md`](../src/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`src/db.mjs`](../src/db.mjs) | SQLite initialization, core relational schema, migrations, transaction and stock-balance helpers. |
| [`src/migrations/002_enterprise.sql`](../src/migrations/002_enterprise.sql) | Versioned enterprise schema migration, relational tables, indexes and chart-of-accounts seed. |
| [`src/migrations/README.md`](../src/migrations/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`src/modules/README.md`](../src/modules/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`src/modules/enterprise.mjs`](../src/modules/enterprise.mjs) | Supplier/customer, procurement, stock controls, wholesale, finance, HR and branch-close services. |
| [`src/security.mjs`](../src/security.mjs) | Password hashing, session-token generation and secure cookie/token utilities. |
| [`src/server.mjs`](../src/server.mjs) | HTTP server, session lifecycle, security middleware, REST API and core retail workflows. |

### `templates/`

| Exact path | Purpose |
|---|---|
| [`templates/README.md`](../templates/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`templates/imports/README.md`](../templates/imports/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`templates/imports/employee-template.csv`](../templates/imports/employee-template.csv) | Employee master import field template. |
| [`templates/imports/product-variant-template.csv`](../templates/imports/product-variant-template.csv) | Product/variant master import field template. |
| [`templates/imports/supplier-purchase-order-template.csv`](../templates/imports/supplier-purchase-order-template.csv) | Supplier and purchase-order import field template. |
| [`templates/physical-count-template.csv`](../templates/physical-count-template.csv) | Reusable physical-count worksheet with SKU and counted quantity. |
| [`templates/pos-import-template.csv`](../templates/pos-import-template.csv) | POS source file schema with branch, sale, SKU, quantity, price and payment. |

### `tests/`

| Exact path | Purpose |
|---|---|
| [`tests/README.md`](../tests/README.md) | Folder ownership, contents, extension rules and related documentation. |
| [`tests/erp.test.mjs`](../tests/erp.test.mjs) | End-to-end HTTP, SQLite and business-workflow acceptance suite. |

### Root files

| Exact path | Purpose |
|---|---|
| [`.env.example`](../.env.example) | Environment-variable template for server, database and session configuration. |
| [`.gitignore`](../.gitignore) | Source-control exclusions for secrets, local database files, installed modules and logs. |
| [`BUILD_MANIFEST.json`](../BUILD_MANIFEST.json) | Generated per-file sizes and SHA-256 integrity manifest. |
| [`README.md`](../README.md) | Project overview, module coverage, runtime setup, data-integrity rules and quick start. |
| [`package.json`](../package.json) | Application version, Node engine and executable project commands. |

## Integrity and packaging

`BUILD_MANIFEST.json` captures the maintained source, documentation, templates and workflow files. Delivery ZIP files are created outside the project tree and each archive receives an independent SHA-256 checksum.

The full source archive contains the backend, browser UI/PWA, database migrations, all engineering documentation, licensed source references, import templates, tests and CI workflow.
