# Stockland v0.3.0 — 45-table source-to-canonical data model disposition

**Review date:** 28 November 2026  
**Source:** `source-intake/stockland-garment-erp-local-v0.3.0/source/docs/DATABASE_SCHEMA.md`, `src/db.mjs`, `src/migrations/002_enterprise.sql`  
**Source schema:** SQLite v2, 45 application tables  
**Target architecture:** canonical Laravel 13 + PostgreSQL 18 + Angular 22 + Redis 8.2

## 1. Decision

Do not execute the local `002_enterprise.sql` against the canonical database and do not create a parallel 45-table schema by renaming columns. This source is a valuable Stockland business-workflow and legacy-data dictionary. Its tables use single-company/location scope, integer surrogate identifiers, mixed POS/warehouse/finance/HR semantics, and several directly mutable source records. The canonical application is multi-tenant and uses legal entities, sites, operational nodes, inventory locations, controlled releases, append-only ledgers, RLS and role-separated PostgreSQL functions.

The correct implementation is to port each business concept into the appropriate bounded context, with tenant composite keys, immutable/append-only evidence where needed, explicit workflow states, permission boundaries, controlled reversals, event idempotency, and ledger reconciliation. The crosswalk below records the target and the gap. “Analog exists” does not mean the whole source workflow is implemented.

## 2. Canonical data foundations inspected

The current canonical delivery branch has these relevant schema families:

- WP-03: `tenants`, `tenant_memberships`, `legal_entities`, `business_sites`, `operational_nodes`, `branch_registrations`, `inventory_locations`, `audit_events`, identity/domain access and platform entitlements.
- WP-04: `brands`, brand strategy versions, seasons, collections, range/assortment planning, demand forecast, OTB, pricing/promotion and evidence records.
- WP-05: product briefs/versions/decisions, styles/revisions, measurement-spec versions/points/values, pattern sets/revisions/pieces, grade rules, sample requests/rounds, fit sessions/observations/decisions.
- WP-07: `supplier_accounts`, sourcing events, supplier quotes, `purchase_orders`, `purchase_order_lines`, `goods_receipts`, `goods_receipt_lines`, `material_lots`, `fabric_rolls`, `trim_lots`, `inventory_transactions`.
- WP-08/09: process route versions/steps, production orders/operations, execution events, cutting bundles and bundle-operation events.
- WP-11: quality defect catalogue, inspections, nonconformances, corrective actions and release decisions.
- WP-12: customer orders/lines, shipments/lines, finished-goods lots/transactions/reservations.
- WP-06/10: operation standard versions, work centres, shift definitions, capacity slots, production plan versions/lines, workforce skill records/assignments, incentive policy versions/calculation drafts.
- WP-13/14: finance periods/accounts/journals/journal lines, statutory rule versions, payroll run drafts/lines, posting and reversal controls, customer-credit decisions.
- WP-15: document assets/extraction runs, integration endpoint registry/outbox events, agent run/action audit/proposals/approvals/events and device scan events.

The baseline currently does **not** include every subledger or UI implied by the source app. Where a canonical foundation is only a review/draft structure, this crosswalk says so.

## 3. Exact 45-table crosswalk

### Identity, organization and catalog

| Source table | Source fields (abbreviated only by retaining every field name) | Target WP / canonical disposition |
|---|---|---|
| `locations` | `id, code, name, kind, active, created_at` | WP-03/WP-07/WP-12. Decompose into `tenants`, `legal_entities`, `business_sites`, `operational_nodes` and `inventory_locations`. A local branch/warehouse is not necessarily a legal entity or a tenant. |
| `users` | `id, email, full_name, password_hash, role, location_id, active, created_at` | WP-03. Map global identity to `users`; tenant-scoped role to `tenant_memberships` / domain-role assignments; location access to scoped access grants. Do not migrate local passwords or flatten tenant/location roles. |
| `sessions` | `token_hash, user_id, csrf_token, expires_at, created_at` | WP-03. Replace with canonical auth/Sanctum/session mechanism and secure token hashes. Do not port the local session table as a new tenant table. |
| `audit_log` | `id, actor_id, action, entity_type, entity_id, details_json, created_at` | WP-03/WP-16. Map events into tenant-scoped `audit_events` plus action-specific audit evidence. Add actor/tenant/correlation/scope/reason, immutability and retention. |
| `brands` | `id, code, name, active, created_at` | WP-04. Canonical `brands` exists with brand strategy versioning and approval. Compare identity/code semantics; do not import duplicate masters without tenant resolution. |
| `categories` | `id, parent_id, code, name, department, active` | WP-04/WP-05. A hierarchical, versioned product taxonomy is not evidenced as a direct canonical table. Determine approved taxonomy/department/family model and add tenant-scoped masters with effective-dated references; don't use an ungoverned JSON string as a permanent substitute. |
| `products` | `id, sku, name, brand, category, department, age_group, size, color, barcode, unit, cost_cents, retail_cents, wholesale_cents, active, created_at, updated_at` | WP-04/WP-05/WP-12/WP-13. Decompose into style/revision, product/variant/size-colour SKU, barcode, UOM, price lists/channel prices, cost/valuation and lifecycle state. Current style and BOM foundations do not prove a complete SKU/variant master or retail price source. |

### Procurement, inventory and fulfillment

| Source table | Source fields | Target WP / canonical disposition |
|---|---|---|
| `suppliers` | `id, supplier_code, name, contact_name, email, phone, tax_id, address, opening_balance_cents, active, created_at, updated_at` | WP-07/WP-13. Canonical `supplier_accounts` exists; map supplier identity, legal/tax evidence, contacts and terms. Opening balance must be migrated through approved subledger journals, not copied into a master. |
| `purchase_orders` | `id, po_number, supplier_id, location_id, order_date, expected_date, status, subtotal_cents, notes, created_by, created_at, updated_at` | WP-07. Canonical `purchase_orders` exists. Align PO scope with legal employer/entity/site, supplier quote, approval version, currencies/tax, material types and change history. |
| `purchase_order_lines` | `id, purchase_order_id, sku, ordered_qty, received_qty, unit_cost_cents, line_total_cents` | WP-07. Canonical `purchase_order_lines` exists. Resolve SKU to material/trim/chemical/packaging item and UOM; protect received/accepted balances and partial receipts with transactional controls. |
| `goods_receipts` | `id, receipt_number, purchase_order_id, supplier_id, location_id, receipt_date, received_by, notes, created_at` | WP-07. Canonical `goods_receipts` exists. Reconcile GRN to PO, inspection/accepted quantity, lot and roll identifiers, delivery and source document evidence. |
| `goods_receipt_lines` | `id, goods_receipt_id, sku, qty, unit_cost_cents` | WP-07. Canonical `goods_receipt_lines` exists. Port only after accepted quantity, batch/lot/roll, shade, UOM conversion, inspection and valuation semantics are explicit. |
| `inventory_movements` | `id, sku, location_id, movement_type, qty_delta, unit_cost_cents, source_type, source_id, note, created_by, created_at` | WP-07/WP-09/WP-12/WP-13. Canonical `inventory_transactions` and `finished_goods_transactions` provide separate foundations. Split material, WIP, subcontract, and finished-goods custody; use immutable source-linked events and exactly-once posting. Do not create one generic stock ledger spanning every inventory type. |
| `requisitions` | `id, requisition_number, from_location_id, to_location_id, status, requested_by, approved_by, approved_at, notes, created_at` | WP-07/WP-12. No equivalent full location-requisition workflow is confirmed in canonical migrations. Build an approval and transfer request aggregate, source/destination controls, reservations, dispatch and receipt, with audit and concurrency tests. |
| `requisition_lines` | `id, requisition_id, sku, qty` | WP-07/WP-12. Build tenant-scoped lines with material/FG item identity, UOM, requested/approved/issued/received/rejected quantities and ledger linkage. |
| `reservations` | `id, reservation_number, location_id, customer_id, status, reserved_until, notes, created_by, created_at` | WP-12. `finished_goods_reservations` is an existing FG-only foundation, not proof of all-source reservation lifecycle. Model reservation headers, allocation lines, expiry/release, order linkage and stock availability atomically. |
| `reservation_lines` | `id, reservation_id, sku, qty` | WP-12. Ensure per-line item/UOM/lot allocation and reserved/fulfilled/released quantities; prevent oversell with locks and idempotent transitions. |
| `stock_counts` | `id, count_number, location_id, status, counted_by, released_by, released_at, notes, created_at` | WP-12. No full stock-count aggregate is confirmed in canonical migrations. Build blind/controlled count session, freeze snapshot, approvals, variance reason and controlled adjustment. |
| `stock_count_lines` | `id, stock_count_id, sku, expected_qty, counted_qty, variance_qty` | WP-12. Build counted lines with expected snapshot, recount/approval, UOM, lot/roll, blind-count support and a single posted variance movement. |
| `damage_reports` | `id, damage_number, location_id, reported_by, reason, status, created_at` | WP-11/WP-12. Decompose into quality nonconformance/inspection and inventory damage/write-off decision. A damaged unit must have condition, disposition, evidence and approved stock/financial movement. |
| `damage_report_lines` | `id, damage_report_id, sku, qty, unit_cost_cents` | WP-11/WP-12/WP-13. Link defect/lot/production or customer-return source, accepted/rework/scrap disposition and valuation. Do not post write-off without approved disposition. |

### POS, customers, wholesale and import

| Source table | Source fields | Target WP / canonical disposition |
|---|---|---|
| `pos_sales` | `id, external_sale_id, location_id, cashier_id, source_type, payment_method, total_cents, sale_time, import_batch_id, created_at` | WP-12/WP-13/WP-15. No complete canonical retail POS sale aggregate is confirmed. Build a sale header with tenant/site/register/cashier, channel, price/tax snapshots, idempotency key, tender/reconciliation status and posted journal/stock references. |
| `pos_sale_lines` | `id, sale_id, sku, qty, unit_price_cents, line_total_cents` | WP-12/WP-13. Build immutable sale-line snapshots with product variant, applied price/discount/tax, unit, quantity, return balance and stock/journal posting links. |
| `import_batches` | `id, digest, source_name, location_id, status, row_count, valid_rows, invalid_rows, expected_total_cents, reconciled_total_cents, created_by, released_by, released_at, posted_at, last_error, created_at` | WP-12/WP-15. The generic integration registry/outbox is not a POS batch importer. Build a separate staged import aggregate with file hash, control totals, row errors, reviewer/role, final release and exactly-once posting. |
| `import_rows` | `id, batch_id, row_number, external_sale_id, sku, qty, unit_price_cents, payment_method, sale_time, row_status, errors_json, raw_json` | WP-12/WP-15. Build row-level normalized and raw evidence, duplicate/payload conflict control, stable source key and validation diagnostics. Keep raw PII and source payload access restricted. |
| `customers` | `id, customer_code, name, customer_type, contact_name, email, phone, tax_id, address, credit_limit_cents, opening_balance_cents, active, created_at, updated_at` | WP-12/WP-13. A full customer/channel-account master is not confirmed. Build tenant/customer party, legal/tax identity, contacts, delivery addresses, channel account, terms, credit profile and privacy controls. Opening balances require controlled migration journals. |
| `wholesale_orders` | `id, order_number, customer_id, location_id, status, payment_method, total_cents, notes, created_by, confirmed_by, confirmed_at, created_at` | WP-12. Canonical `customer_orders` exists as an order/fulfillment foundation. Align channel/order type, customer terms, credit approval, pricing snapshot, reserve/allocate, release and fulfillment state. |
| `wholesale_order_lines` | `id, wholesale_order_id, sku, qty, unit_price_cents, line_total_cents` | WP-12/WP-13. Canonical `customer_order_lines` exists. Preserve variant/size, approved price, discount/tax, allocated and fulfilled quantity and return balance. |
| `sales_returns` | `id, return_number, sale_id, location_id, customer_id, reason, refund_method, refund_total_cents, received_by, created_at` | WP-11/WP-12/WP-13. No complete return/credit/refund workflow is confirmed. Build return authorization, inspection/condition, disposition/restock/scrap, tender refund/store credit and linked credit-note/journal. |
| `sales_return_lines` | `id, return_id, sale_line_id, sku, qty, unit_price_cents, line_total_cents` | WP-11/WP-12. Build partial-return balance and serial/lot/size/variant checks, immutable original-sale link and duplicate return prevention. |

### Finance and accounting

| Source table | Source fields | Target WP / canonical disposition |
|---|---|---|
| `accounting_periods` | `id, period_code, start_date, end_date, status, closed_by, closed_at` | WP-13. Canonical `finance_periods` exists. Extend with entity, calendar, authorization, open/close checklist, source cut-off and controlled reopening. |
| `accounts` | `id, account_code, account_name, account_type, normal_balance, active, created_at` | WP-13. Canonical `finance_accounts` exists. Align chart version, legal entity, ledger dimensions, posting rules and mapping; no direct ID copy. |
| `journal_entries` | `id, journal_number, entry_date, source_type, source_id, description, status, debit_total_cents, credit_total_cents, created_by, posted_by, posted_at, created_at` | WP-13. Canonical `finance_journals` exists; posting and reversal controls exist. Require balanced posting, source idempotency, period validation, maker-checker, append-only posted state and controlled reversal. |
| `journal_lines` | `id, journal_entry_id, account_id, description, debit_cents, credit_cents` | WP-13. Canonical `finance_journal_lines` exists. Add entity/currency/FX/dimensions/tax/cost centre and strict one-sided line checks if not already covered. |
| `expenses` | `id, expense_number, expense_date, location_id, account_id, paid_from_account_id, amount_cents, description, reference, journal_entry_id, created_by, created_at` | WP-13. A complete expense/AP payment source subledger is not confirmed. Build authorized expense claims/bills/receipts and link the approved transaction to a posted journal. |
| `supplier_bills` | `id, bill_number, supplier_id, bill_date, due_date, total_cents, paid_cents, status, journal_entry_id, created_by, created_at` | WP-07/WP-13. Supplier master and finance journals exist; dedicated AP invoice/subledger, matching and aging are not confirmed. Build three-way match, source invoice asset, due date, payment allocations, credit note and aging. |
| `customer_invoices` | `id, invoice_number, customer_id, invoice_date, due_date, total_cents, paid_cents, status, source_type, source_id, journal_entry_id, created_by, created_at` | WP-12/WP-13. Customer order and finance journal foundations exist; full AR invoice, delivery proof, tax, receivable aging and customer payment allocation must be built/verified. |
| `financial_payments` | `id, payment_number, payment_type, party_id, payment_date, amount_cents, payment_method, reference, supplier_bill_id, customer_invoice_id, journal_entry_id, created_by, created_at` | WP-13. Finance journal posting is not a payment subledger. Build AP/AR allocation, bank/cash instrument, reconciliation/receipt evidence, idempotency, void/reversal and period control. |
| `number_sequences` | `sequence_key, next_value` | WP-13/WP-16. Local sequential counters are not a universal identifier strategy. Decide tenant/entity/site-scoped human document-number sequences with locking, gaps/reversal policy and idempotency; UUIDs remain internal IDs. |

### HR, payroll and branch operations

| Source table | Source fields | Target WP / canonical disposition |
|---|---|---|
| `employees` | `id, employee_number, full_name, email, phone, department, job_title, location_id, hire_date, employment_status, base_salary_cents, bank_name, bank_account_mask, created_at, updated_at` | WP-14. A full canonical employee master is not present in the current delivery migration inventory. Build person/employment/contract/assignment/position/profile with tenant, legal employer, privacy classes and restricted payroll details. |
| `employment_contracts` | `id, employee_id, contract_number, contract_type, start_date, end_date, salary_cents, terms, status, created_by, created_at` | WP-14. Build effective-dated, versioned contract and change approval; source terms are too simple for current local legal controls. |
| `attendance_records` | `id, employee_id, work_date, status, hours, notes, recorded_by, created_at` | WP-10/WP-14. No full time-event and attendance ledger is confirmed. Build raw event source/device/timezone/correction, roster/shift, leave/overtime approval, payable-time derivation and exception queue. |
| `payroll_runs` | `id, payroll_number, period_month, status, gross_total_cents, deduction_total_cents, net_total_cents, journal_entry_id, created_by, posted_by, posted_at, created_at` | WP-14/WP-13. Canonical `payroll_run_drafts` is a review foundation, not a completed statutory payroll run or payment/remittance system. Candidate WP-14 code must be re-sequenced, legally approved and verified. |
| `payroll_lines` | `id, payroll_run_id, employee_id, basic_cents, allowance_cents, overtime_cents, tax_cents, pension_cents, other_deduction_cents, gross_cents, deduction_cents, net_cents` | WP-14. Canonical `payroll_run_lines` exists as a draft/review object. Requires calculation evidence hash, source/rule version, classification, time inputs, gross-to-net reconciliation, GL posting and remittance evidence. |
| `daily_closings` | `id, closing_number, location_id, business_date, opening_cash_cents, expected_cash_cents, counted_cash_cents, cash_variance_cents, expected_card_cents, expected_mobile_cents, expected_bank_cents, status, submitted_by, closed_by, closed_at, notes, created_at` | WP-12/WP-13/WP-16. No canonical register/till close workflow is confirmed. Build register shifts, tender expectations vs counts, sales/returns/float/paid-out links, variance authorization, immutable close and bank reconciliation. |

## 4. Import/reimplementation controls

1. Do not import local SQLite row IDs as canonical IDs. Use canonical UUID and tenant-scoped business keys, with a source-key crosswalk only where migration data is actually supplied and approved.
2. Do not create employee payroll records, customer masters, opening balances or financial transactions from illustrative templates, synthetic acceptance examples or test fixtures.
3. Map every valid source row to a legal entity/tenant/site and resolve duplicates before any seed/migration.
4. Keep source document hash, original ID, source system/version, import batch, validation result, reviewer and reconciliation totals.
5. Journal/stock effects must be exactly-once, balanced, append-only after posting, traceable to source and reversible only through a controlled offset.
6. Convert SQLite `REAL` quantities and cents into canonical decimal/money semantics with explicit currency/UOM/precision; do not copy floating-point behavior.
7. Every table mapping must have target owner WP, target migration, RLS/permissions, API/UI, audit event, normal/negative/concurrent/retry/reversal tests and owner sign-off.

## 5. Highest-priority missing schema slices

The largest uncovered areas are not a new global table dump. They are coherent canonical capabilities:
- SKU/variant/taxonomy and retail sellable inventory (WP04/05/12).
- Retail POS, staged import, cash/tender close, returns and reconciliation (WP12/13/15).
- Customer master, AR/AP subledgers, payments, invoice/credit notes and aging (WP12/13).
- Warehouse requests/transfers, cycle count, damage/quality disposition (WP07/11/12).
- Employee/person/contract/assignment/time/attendance/benefit/payroll run and remittance (WP10/14).
- Integration event/inbox/replay and connector reliability, plus BI event/KPI contracts (WP15).
- End-to-end, installer, backup/restore, security/performance/UAT/release proof (WP16).

This port map is a source analysis and target-design artefact; it does not claim that the missing tables have already been created or that production data has been loaded.
