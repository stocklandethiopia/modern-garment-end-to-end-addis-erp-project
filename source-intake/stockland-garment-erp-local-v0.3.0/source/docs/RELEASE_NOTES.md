# Release notes — Stockland Garment ERP 0.3.0

**Release date:** 2026-11-22  
**Package:** local full-stack source release

## 0.3.0 — Enterprise application and documentation release

### Application modules

- Command centre, account setup, seven role types and assigned location access.
- Product master with garment SKU and size/colour attributes.
- Supplier, customer, brand and category masters.
- Purchase orders, partial goods receipts, supplier bills and journal posting.
- Central Warehouse and branch stock ledger, direct receipts, adjustments and two-sided transfers.
- Requisitions, reservations, physical counts, damage and return processing.
- Retail POS with server-resolved retail price and idempotent sale identity.
- POS CSV staging, source-control reconciliation, Branch Manager release and atomic posting.
- Wholesale orders and customer account invoices.
- Chart of accounts, balanced journals, expenses, AP, AR, payments and trial balance.
- Employee records, employment contracts, attendance, payroll and daily branch close.
- Sales/stock reports, audit trail and PWA application shell.

### Engineering and verification

- SQLite schema version 2 with 45 relational tables.
- Folder-level documentation and module specifications.
- Open-source reference provenance, upstream MIT license and source manifest.
- GitHub Actions CI for syntax, integration, documentation/runtime gates and build manifest.
- Integration coverage includes injected POS import posting failure and retry after rollback.

## Runtime

- Node.js 22.5.0 or newer; verified with Node v22.16.0.
- npm 10.9.2 in the local validation environment.
- No external npm runtime dependencies.
