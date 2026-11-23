# REST API reference

**Document ID:** STK-API-001  
**Application version:** 0.3.0  
**API base path:** `/api`  
**Transport:** same-origin HTTP; use HTTPS when exposed beyond localhost.

## Request conventions

Authenticated requests use the `stockland_session` HttpOnly cookie. Authenticated business mutations include the `X-CSRF-Token` returned by `/api/auth/login` or `/api/auth/session`. Session initialization and teardown use the defined authentication lifecycle routes. JSON endpoints use `Content-Type: application/json`. The server enforces a request-body limit of 3 MiB; POS CSV content has a 2 MiB business limit.

Amounts are supplied in ETB major units and stored as integer cents. Quantity accepts up to three decimal places. The API returns JSON. Common errors are `{ "error": "message", "details": ... }`.

## Authentication and company setup

| Method | Path | Access | Operation |
|---|---|---|---|
| GET | `/api/health` | Public | Runtime version, database connectivity and setup state |
| POST | `/api/setup` | One-time setup | Create first Owner and initialize the five standard locations |
| POST | `/api/auth/login` | Public | Validate credentials and create session + CSRF token |
| POST | `/api/auth/logout` | Authenticated session | Revoke current session and clear session cookie |
| GET | `/api/auth/session` | Public/optional session | Current account, CSRF token and setup state |
| GET | `/api/locations` | Authenticated | List canonical locations |
| GET | `/api/users` | Owner, Admin | List user accounts |
| POST | `/api/users` | Owner, Admin | Create an assigned user account |
| PATCH | `/api/users/{id}` | Owner, Admin | Update profile, role, location, activation or password |

Roles are `owner`, `admin`, `branch_manager`, `warehouse_manager`, `cashier`, `accountant` and `auditor`.

## Dashboard, product master and inventory

| Method | Path | Access | Operation |
|---|---|---|---|
| GET | `/api/dashboard` | Authenticated | Dashboard metrics and stock/sales summary |
| GET | `/api/products` | Authenticated | Search/list active product SKUs |
| POST | `/api/products` | Owner, Admin, Warehouse Manager | Create garment SKU and variant attributes |
| PATCH | `/api/products/{sku}` | Owner, Admin, Warehouse Manager | Update product fields |
| GET | `/api/inventory?location={code}` | Authenticated, location-scoped | Read ledger-derived location stock |
| POST | `/api/inventory/receipts` | Owner, Admin, Warehouse Manager | Post a direct stock receipt and accounting impact |
| POST | `/api/inventory/transfers` | Owner, Admin, Warehouse Manager | Post source and destination movement legs atomically |
| POST | `/api/inventory/adjustments` | Owner, Admin | Post a reasoned signed stock adjustment and journal |

Product fields: `sku`, `name`, `brand`, `category`, `department`, `ageGroup`, `size`, `color`, `barcode`, `unit`, `cost`, `retailPrice`, `wholesalePrice`.

## Reference masters and procurement

| Method | Path | Access | Operation |
|---|---|---|---|
| GET / POST | `/api/brands` | Authenticated read / Owner or Admin write | Brand code and name records |
| GET / POST | `/api/categories` | Authenticated read / Owner or Admin write | Hierarchical product categories |
| GET / POST | `/api/suppliers` | Operational/report roles read / Owner, Admin, Accountant write | Supplier master and opening balance |
| GET / POST | `/api/customers` | Operational/report roles read / Owner, Admin, Accountant write | Retail, wholesale, reseller or corporate customer master |
| GET / POST | `/api/procurement/orders` | Owner, Admin, Warehouse Manager; Accountant/Auditor read | Purchase orders and order lines |
| POST | `/api/procurement/orders/{id}/receive` | Owner, Admin, Warehouse Manager | Partial/full goods receipt, stock posting, supplier bill and journal |

Purchase order line input includes `sku`, `qty` and `unitCost`. Receiving can accept explicit `lines` with SKU and quantity; when omitted, remaining quantities are received.

## Stock control and branch operations

| Method | Path | Access | Operation |
|---|---|---|---|
| GET / POST | `/api/inventory/requisitions` | Authenticated read / operational roles write | Create branch/warehouse requisitions |
| POST | `/api/inventory/requisitions/{id}/approve` | Owner, Admin, Branch Manager, Warehouse Manager | Approve submitted request under location scope |
| POST | `/api/inventory/requisitions/{id}/reject` | Same operational roles | Reject submitted request |
| POST | `/api/inventory/requisitions/{id}/fulfill` | Same operational roles | Transfer stock through paired ledger legs |
| GET / POST | `/api/inventory/reservations` | Authenticated read / operational roles write | Reserve available stock |
| POST | `/api/inventory/reservations/{id}/release` | Owner, Admin, Branch Manager, Warehouse Manager | Release reservation |
| POST | `/api/inventory/reservations/{id}/fulfill` | Same operational roles | Mark reservation fulfilled |
| GET / POST | `/api/inventory/counts` | Authenticated read / operational roles write | Create physical count worksheet with expected and counted quantities |
| POST | `/api/inventory/counts/{id}/release` | Owner, Admin, Branch Manager, Warehouse Manager | Reconcile variance to movement ledger and GL |
| GET / POST | `/api/inventory/damages` | Authenticated read / operational roles write | Record damaged stock and post stock/GL effects |

The physical-count browser workflow accepts Excel-compatible CSV files with `sku` and `countedQty` columns. A blank workbook can be downloaded from `/physical-count-template.csv`.

## Retail POS and import

| Method | Path | Access | Operation |
|---|---|---|---|
| GET / POST | `/api/pos/sales` | Owner, Admin, Branch Manager, Cashier; Accountant/Auditor read | List or post retail POS sales |
| GET / POST | `/api/pos/imports` | Operational/report roles read / Owner, Admin, Branch Manager stage | List or validate and stage a CSV import |
| GET | `/api/pos/imports/{id}` | Assigned operational/report roles | Read batch reconciliation and row results |
| POST | `/api/pos/imports/{id}/release` | Assigned Branch Manager only | Final approval and automatic atomic posting |
| GET | `/api/pos/sales/{id}/lines` | Authenticated, location-scoped | Returnable lines from the original sale |
| GET / POST | `/api/pos/returns` | Authenticated read / Owner, Admin, Branch Manager, Cashier write | Record original-sale-linked returned quantities and refund journal |

Retail sale posting requires an `Idempotency-Key` header or `externalSaleId`. Retail price is resolved from Product Master. Supported payment methods are `CASH`, `CARD`, `MOBILE_MONEY` and `BANK_TRANSFER`.

POS import CSV headers: `branch_code,external_sale_id,sku,qty,unit_price,payment_method,sale_time`. The upload body includes `fileName`, `csv` and mandatory `declaredTotal`. Upload creates a staged batch; it does not post sales or stock.

## Wholesale and finance

| Method | Path | Access | Operation |
|---|---|---|---|
| GET / POST | `/api/wholesale/orders` | Authenticated read / Owner, Admin, Branch Manager, Cashier write | Draft wholesale order for a customer |
| POST | `/api/wholesale/orders/{id}/confirm` | Owner, Admin, Branch Manager | Confirm, deduct stock, post revenue/COGS and AR when on credit |
| GET | `/api/finance/accounts` | Owner, Admin, Accountant, Auditor | Active chart of accounts |
| GET / POST | `/api/finance/journals` | Finance roles read / Owner, Admin, Accountant write | Read or post balanced manual journals |
| GET / POST | `/api/finance/expenses` | Finance roles read / Owner, Admin, Accountant write | Record operating expense with journal |
| GET / POST | `/api/finance/ap-bills` | Finance roles read / Owner, Admin, Accountant write | Read or create supplier bills |
| GET / POST | `/api/finance/ar-invoices` | Finance roles read / Owner, Admin, Accountant write | Read or create customer invoices |
| POST | `/api/finance/ap-bills/{id}/payments` | Owner, Admin, Accountant | Record supplier payment and journal |
| POST | `/api/finance/ar-invoices/{id}/payments` | Owner, Admin, Accountant | Record customer receipt and journal |
| GET | `/api/finance/trial-balance` | Owner, Admin, Accountant, Auditor | Aggregate posted account debit/credit balances |

## HR and daily operations

| Method | Path | Access | Operation |
|---|---|---|---|
| GET / POST | `/api/hr/employees` | Owner, Admin, Accountant, Auditor read / Owner, Admin write | Employee master |
| GET / POST | `/api/hr/contracts` | Owner, Admin, Accountant, Auditor read / Owner, Admin write | Employment contract records |
| GET / POST | `/api/hr/attendance` | Owner, Admin, Accountant, Auditor read / Owner, Admin write | Daily attendance entry/update |
| GET / POST | `/api/hr/payroll` | Owner, Admin, Accountant, Auditor read / Owner, Admin create | Payroll draft and line calculations |
| POST | `/api/hr/payroll/{id}/post` | Owner, Admin, Accountant | Post payroll journal and run status |
| GET / POST | `/api/operations/daily-closings` | Authenticated read / Owner, Admin, Branch Manager write | Submit branch close and payment-channel reconciliation |
| POST | `/api/operations/daily-closings/{id}/close` | Owner, Admin, Branch Manager, location-scoped | Close submitted branch day |
| GET | `/api/operations/module-summary` | Authenticated | Operational counters by authorized scope |

## Reports and audit

| Method | Path | Access | Operation |
|---|---|---|
| GET | `/api/reports/stock` | Owner, Admin, Branch Manager, Warehouse Manager, Accountant, Auditor | Location stock and cost value |
| GET | `/api/reports/sales` | Authenticated report roles, scope applied | Sales totals by location |
| GET | `/api/audit` | Owner, Admin, Auditor | Recent audit records |

## Response and error semantics

| HTTP | Meaning |
|---|---|
| 200 | Read, existing idempotent result or completed state transition |
| 201 | Record or business transaction created |
| 400 | Input or business validation error |
| 401 | Authentication required or credentials invalid |
| 403 | Role, CSRF or location-scope denial |
| 404 | Route, entity or location not found |
| 409 | Duplicate identity, insufficient stock or disallowed state transition |
| 413 | Request size limit exceeded |
| 415 | Unsupported request content type |
| 422 | Imported row or domain validation error |
| 429 | Login attempt throttle |
| 500 | Unexpected server error |

## Route implementation map

Core routes are implemented in `src/server.mjs`. Enterprise routes are dispatched through `src/modules/enterprise.mjs`. All data mutation endpoints apply server-side validation, authorization, audit and the relevant database transaction boundary.
