# Stockland Garment ERP — project charter

**Document ID:** STK-PRJ-001  
**Application version:** 0.3.0

## Business purpose

Provide Stockland Ethiopia with a unified local workspace for garment product master data, retail/wholesale sales, central and branch inventory, suppliers, procurement, finance, HR and branch operations.

## Organization and locations

The company operating model includes one Central Warehouse and four retail branches:

- Central Warehouse — `CENTRAL_WAREHOUSE`
- Goro Branch — `GORO_BRANCH`
- Gerji Branch — `GERJI_BRANCH`
- Mexico Branch — `MEXICO_BRANCH`
- Megenagna Branch — `MEGENAGNA_BRANCH`

## User roles

`owner`, `admin`, `branch_manager`, `warehouse_manager`, `cashier`, `accountant`, `auditor`.

The API applies role permissions and location scope. A Branch Manager and Cashier work within their assigned branch. A Warehouse Manager works within their assigned location. Finance and audit roles have company-level functions according to their permission set.

## Functional modules

Command Centre, user administration, Product Master, suppliers, customers/resellers, procurement, receiving, inventory ledger, transfers, requisitions, reservations, physical counts, retail POS, POS import/reconciliation, returns, damage, wholesale, finance/GL, AP/AR, HR, attendance, payroll, daily close, reporting and audit.

## Business invariants

- Inventory is derived from immutable movements.
- Every transfer has both source and destination legs.
- POS sales use server-side retail pricing.
- POS sale identities are idempotent within a branch.
- Imported POS files are staged before release.
- The assigned Branch Manager is the final and sole business approver for a branch import; successful release automatically posts.
- Journals balance exactly.
- Operational access is bounded by assigned location.
- Audit and posted inventory history are append-only.

## Deliverable contents

The project contains the Node.js HTTP/API application, SQLite schema, browser/PWA interface, templates, automated tests, GitHub Actions workflow, open-source provenance, module specifications and operational documentation.
