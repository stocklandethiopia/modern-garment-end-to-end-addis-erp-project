# Database schema and data dictionary

**Document ID:** STK-DB-001  
**Application version:** 0.3.0  
**SQLite schema version:** 2  
**Application tables:** 45

## Persistence model

The application uses SQLite through Node.js `node:sqlite`. Foreign-key enforcement is enabled, file-backed databases use WAL mode and a 5,000 ms busy timeout, and integration tests use an in-memory database. `src/db.mjs` initializes core tables; `src/migrations/002_enterprise.sql` creates enterprise tables, indexes, chart-of-accounts seed rows and sets `PRAGMA user_version` to 2.

## Domain map

```text
locations ── users ── sessions
    │         └──── audit_log
    ├── inventory_movements ── products ── brands/categories
    ├── pos_sales ── pos_sale_lines
    ├── import_batches ── import_rows
    ├── purchase_orders ── purchase_order_lines ── goods_receipts
    ├── requisitions / reservations / stock_counts / damages
    ├── sales_returns / wholesale_orders
    └── daily_closings
suppliers ── supplier_bills ── financial_payments
customers ── customer_invoices ── financial_payments
employees ── contracts / attendance / payroll_runs ── payroll_lines
accounts ── journal_entries ── journal_lines
```

## Table catalogue

| Table | Domain |
|---|---|
| `accounting_periods` | Finance / GL |
| `accounts` | Finance / GL |
| `attendance_records` | HR / payroll |
| `audit_log` | Identity / organization |
| `brands` | Product master |
| `categories` | Product master |
| `customer_invoices` | Customers / wholesale / AR |
| `customers` | Customers / wholesale / AR |
| `daily_closings` | Branch operations |
| `damage_report_lines` | Inventory / warehouse |
| `damage_reports` | Inventory / warehouse |
| `employees` | HR / payroll |
| `employment_contracts` | HR / payroll |
| `expenses` | Finance / GL |
| `financial_payments` | Finance / GL |
| `goods_receipt_lines` | Procurement / AP |
| `goods_receipts` | Procurement / AP |
| `import_batches` | Retail POS |
| `import_rows` | Retail POS |
| `inventory_movements` | Inventory / warehouse |
| `journal_entries` | Finance / GL |
| `journal_lines` | Finance / GL |
| `locations` | Identity / organization |
| `number_sequences` | Finance / GL |
| `payroll_lines` | HR / payroll |
| `payroll_runs` | HR / payroll |
| `pos_sale_lines` | Retail POS |
| `pos_sales` | Retail POS |
| `products` | Product master |
| `purchase_order_lines` | Procurement / AP |
| `purchase_orders` | Procurement / AP |
| `requisition_lines` | Inventory / warehouse |
| `requisitions` | Inventory / warehouse |
| `reservation_lines` | Inventory / warehouse |
| `reservations` | Inventory / warehouse |
| `sales_return_lines` | Retail POS |
| `sales_returns` | Retail POS |
| `sessions` | Identity / organization |
| `stock_count_lines` | Inventory / warehouse |
| `stock_counts` | Inventory / warehouse |
| `supplier_bills` | Procurement / AP |
| `suppliers` | Procurement / AP |
| `users` | Identity / organization |
| `wholesale_order_lines` | Customers / wholesale / AR |
| `wholesale_orders` | Customers / wholesale / AR |

## Column-level data dictionary

### `accounting_periods`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `period_code` | `TEXT` | NOT NULL |
| `start_date` | `TEXT` | NOT NULL |
| `end_date` | `TEXT` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `closed_by` | `INTEGER` | nullable |
| `closed_at` | `TEXT` | nullable |

Foreign keys:
- `closed_by` → `users.id` (on delete: NO ACTION)

### `accounts`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `account_code` | `TEXT` | NOT NULL |
| `account_name` | `TEXT` | NOT NULL |
| `account_type` | `TEXT` | NOT NULL |
| `normal_balance` | `TEXT` | NOT NULL |
| `active` | `INTEGER` | NOT NULL, default `1` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

### `attendance_records`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `employee_id` | `INTEGER` | NOT NULL |
| `work_date` | `TEXT` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `hours` | `REAL` | NOT NULL, default `8` |
| `notes` | `TEXT` | NOT NULL, default `''` |
| `recorded_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `recorded_by` → `users.id` (on delete: NO ACTION)
- `employee_id` → `employees.id` (on delete: NO ACTION)

### `audit_log`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `actor_id` | `INTEGER` | nullable |
| `action` | `TEXT` | NOT NULL |
| `entity_type` | `TEXT` | NOT NULL |
| `entity_id` | `TEXT` | NOT NULL |
| `details_json` | `TEXT` | NOT NULL, default `'{}'` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `actor_id` → `users.id` (on delete: NO ACTION)

### `brands`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `code` | `TEXT` | NOT NULL |
| `name` | `TEXT` | NOT NULL |
| `active` | `INTEGER` | NOT NULL, default `1` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

### `categories`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `parent_id` | `INTEGER` | nullable |
| `code` | `TEXT` | NOT NULL |
| `name` | `TEXT` | NOT NULL |
| `department` | `TEXT` | NOT NULL, default `''` |
| `active` | `INTEGER` | NOT NULL, default `1` |

Foreign keys:
- `parent_id` → `categories.id` (on delete: NO ACTION)

### `customer_invoices`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `invoice_number` | `TEXT` | NOT NULL |
| `customer_id` | `INTEGER` | NOT NULL |
| `invoice_date` | `TEXT` | NOT NULL |
| `due_date` | `TEXT` | nullable |
| `total_cents` | `INTEGER` | NOT NULL |
| `paid_cents` | `INTEGER` | NOT NULL, default `0` |
| `status` | `TEXT` | NOT NULL |
| `source_type` | `TEXT` | NOT NULL, default `'MANUAL'` |
| `source_id` | `TEXT` | NOT NULL, default `''` |
| `journal_entry_id` | `INTEGER` | nullable |
| `created_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `created_by` → `users.id` (on delete: NO ACTION)
- `journal_entry_id` → `journal_entries.id` (on delete: NO ACTION)
- `customer_id` → `customers.id` (on delete: NO ACTION)

### `customers`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `customer_code` | `TEXT` | NOT NULL |
| `name` | `TEXT` | NOT NULL |
| `customer_type` | `TEXT` | NOT NULL, default `'retail'` |
| `contact_name` | `TEXT` | NOT NULL, default `''` |
| `email` | `TEXT` | NOT NULL, default `''` |
| `phone` | `TEXT` | NOT NULL, default `''` |
| `tax_id` | `TEXT` | NOT NULL, default `''` |
| `address` | `TEXT` | NOT NULL, default `''` |
| `credit_limit_cents` | `INTEGER` | NOT NULL, default `0` |
| `opening_balance_cents` | `INTEGER` | NOT NULL, default `0` |
| `active` | `INTEGER` | NOT NULL, default `1` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |
| `updated_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

### `daily_closings`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `closing_number` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `business_date` | `TEXT` | NOT NULL |
| `opening_cash_cents` | `INTEGER` | NOT NULL, default `0` |
| `expected_cash_cents` | `INTEGER` | NOT NULL, default `0` |
| `counted_cash_cents` | `INTEGER` | NOT NULL, default `0` |
| `cash_variance_cents` | `INTEGER` | NOT NULL, default `0` |
| `expected_card_cents` | `INTEGER` | NOT NULL, default `0` |
| `expected_mobile_cents` | `INTEGER` | NOT NULL, default `0` |
| `expected_bank_cents` | `INTEGER` | NOT NULL, default `0` |
| `status` | `TEXT` | NOT NULL |
| `submitted_by` | `INTEGER` | nullable |
| `closed_by` | `INTEGER` | nullable |
| `closed_at` | `TEXT` | nullable |
| `notes` | `TEXT` | NOT NULL, default `''` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `closed_by` → `users.id` (on delete: NO ACTION)
- `submitted_by` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)

### `damage_report_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `damage_report_id` | `INTEGER` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `qty` | `REAL` | NOT NULL |
| `unit_cost_cents` | `INTEGER` | NOT NULL, default `0` |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `damage_report_id` → `damage_reports.id` (on delete: NO ACTION)

### `damage_reports`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `damage_number` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `reported_by` | `INTEGER` | nullable |
| `reason` | `TEXT` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `reported_by` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)

### `employees`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `employee_number` | `TEXT` | NOT NULL |
| `full_name` | `TEXT` | NOT NULL |
| `email` | `TEXT` | NOT NULL, default `''` |
| `phone` | `TEXT` | NOT NULL, default `''` |
| `department` | `TEXT` | NOT NULL |
| `job_title` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | nullable |
| `hire_date` | `TEXT` | NOT NULL |
| `employment_status` | `TEXT` | NOT NULL |
| `base_salary_cents` | `INTEGER` | NOT NULL, default `0` |
| `bank_name` | `TEXT` | NOT NULL, default `''` |
| `bank_account_mask` | `TEXT` | NOT NULL, default `''` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |
| `updated_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `location_id` → `locations.id` (on delete: NO ACTION)

### `employment_contracts`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `employee_id` | `INTEGER` | NOT NULL |
| `contract_number` | `TEXT` | NOT NULL |
| `contract_type` | `TEXT` | NOT NULL |
| `start_date` | `TEXT` | NOT NULL |
| `end_date` | `TEXT` | nullable |
| `salary_cents` | `INTEGER` | NOT NULL |
| `terms` | `TEXT` | NOT NULL, default `''` |
| `status` | `TEXT` | NOT NULL |
| `created_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `created_by` → `users.id` (on delete: NO ACTION)
- `employee_id` → `employees.id` (on delete: NO ACTION)

### `expenses`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `expense_number` | `TEXT` | NOT NULL |
| `expense_date` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | nullable |
| `account_id` | `INTEGER` | NOT NULL |
| `paid_from_account_id` | `INTEGER` | NOT NULL |
| `amount_cents` | `INTEGER` | NOT NULL |
| `description` | `TEXT` | NOT NULL |
| `reference` | `TEXT` | NOT NULL, default `''` |
| `journal_entry_id` | `INTEGER` | nullable |
| `created_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `created_by` → `users.id` (on delete: NO ACTION)
- `journal_entry_id` → `journal_entries.id` (on delete: NO ACTION)
- `paid_from_account_id` → `accounts.id` (on delete: NO ACTION)
- `account_id` → `accounts.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)

### `financial_payments`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `payment_number` | `TEXT` | NOT NULL |
| `payment_type` | `TEXT` | NOT NULL |
| `party_id` | `INTEGER` | NOT NULL |
| `payment_date` | `TEXT` | NOT NULL |
| `amount_cents` | `INTEGER` | NOT NULL |
| `payment_method` | `TEXT` | NOT NULL |
| `reference` | `TEXT` | NOT NULL, default `''` |
| `supplier_bill_id` | `INTEGER` | nullable |
| `customer_invoice_id` | `INTEGER` | nullable |
| `journal_entry_id` | `INTEGER` | nullable |
| `created_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `created_by` → `users.id` (on delete: NO ACTION)
- `journal_entry_id` → `journal_entries.id` (on delete: NO ACTION)
- `customer_invoice_id` → `customer_invoices.id` (on delete: NO ACTION)
- `supplier_bill_id` → `supplier_bills.id` (on delete: NO ACTION)

### `goods_receipt_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `goods_receipt_id` | `INTEGER` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `qty` | `REAL` | NOT NULL |
| `unit_cost_cents` | `INTEGER` | NOT NULL |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `goods_receipt_id` → `goods_receipts.id` (on delete: NO ACTION)

### `goods_receipts`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `receipt_number` | `TEXT` | NOT NULL |
| `purchase_order_id` | `INTEGER` | nullable |
| `supplier_id` | `INTEGER` | nullable |
| `location_id` | `INTEGER` | NOT NULL |
| `receipt_date` | `TEXT` | NOT NULL |
| `received_by` | `INTEGER` | nullable |
| `notes` | `TEXT` | NOT NULL, default `''` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `received_by` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)
- `supplier_id` → `suppliers.id` (on delete: NO ACTION)
- `purchase_order_id` → `purchase_orders.id` (on delete: NO ACTION)

### `import_batches`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `digest` | `TEXT` | NOT NULL |
| `source_name` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `row_count` | `INTEGER` | NOT NULL |
| `valid_rows` | `INTEGER` | NOT NULL |
| `invalid_rows` | `INTEGER` | NOT NULL |
| `expected_total_cents` | `INTEGER` | NOT NULL |
| `reconciled_total_cents` | `INTEGER` | NOT NULL |
| `created_by` | `INTEGER` | nullable |
| `released_by` | `INTEGER` | nullable |
| `released_at` | `TEXT` | nullable |
| `posted_at` | `TEXT` | nullable |
| `last_error` | `TEXT` | NOT NULL, default `''` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `released_by` → `users.id` (on delete: NO ACTION)
- `created_by` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)

### `import_rows`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `batch_id` | `INTEGER` | NOT NULL |
| `row_number` | `INTEGER` | NOT NULL |
| `external_sale_id` | `TEXT` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `qty` | `REAL` | NOT NULL |
| `unit_price_cents` | `INTEGER` | NOT NULL |
| `payment_method` | `TEXT` | NOT NULL |
| `sale_time` | `TEXT` | NOT NULL |
| `row_status` | `TEXT` | NOT NULL |
| `errors_json` | `TEXT` | NOT NULL, default `'[]'` |
| `raw_json` | `TEXT` | NOT NULL |

Foreign keys:
- `batch_id` → `import_batches.id` (on delete: CASCADE)

### `inventory_movements`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `sku` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `movement_type` | `TEXT` | NOT NULL |
| `qty_delta` | `REAL` | NOT NULL |
| `unit_cost_cents` | `INTEGER` | NOT NULL, default `0` |
| `source_type` | `TEXT` | NOT NULL |
| `source_id` | `TEXT` | NOT NULL |
| `note` | `TEXT` | NOT NULL, default `''` |
| `created_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `created_by` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)
- `sku` → `products.sku` (on delete: NO ACTION)

### `journal_entries`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `journal_number` | `TEXT` | NOT NULL |
| `entry_date` | `TEXT` | NOT NULL |
| `source_type` | `TEXT` | NOT NULL |
| `source_id` | `TEXT` | NOT NULL |
| `description` | `TEXT` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `debit_total_cents` | `INTEGER` | NOT NULL, default `0` |
| `credit_total_cents` | `INTEGER` | NOT NULL, default `0` |
| `created_by` | `INTEGER` | nullable |
| `posted_by` | `INTEGER` | nullable |
| `posted_at` | `TEXT` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `posted_by` → `users.id` (on delete: NO ACTION)
- `created_by` → `users.id` (on delete: NO ACTION)

### `journal_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `journal_entry_id` | `INTEGER` | NOT NULL |
| `account_id` | `INTEGER` | NOT NULL |
| `description` | `TEXT` | NOT NULL, default `''` |
| `debit_cents` | `INTEGER` | NOT NULL, default `0` |
| `credit_cents` | `INTEGER` | NOT NULL, default `0` |

Foreign keys:
- `account_id` → `accounts.id` (on delete: NO ACTION)
- `journal_entry_id` → `journal_entries.id` (on delete: NO ACTION)

### `locations`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `code` | `TEXT` | NOT NULL |
| `name` | `TEXT` | NOT NULL |
| `kind` | `TEXT` | NOT NULL |
| `active` | `INTEGER` | NOT NULL, default `1` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

### `number_sequences`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `sequence_key` | `TEXT` | primary key |
| `next_value` | `INTEGER` | NOT NULL, default `1` |

### `payroll_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `payroll_run_id` | `INTEGER` | NOT NULL |
| `employee_id` | `INTEGER` | NOT NULL |
| `basic_cents` | `INTEGER` | NOT NULL |
| `allowance_cents` | `INTEGER` | NOT NULL, default `0` |
| `overtime_cents` | `INTEGER` | NOT NULL, default `0` |
| `tax_cents` | `INTEGER` | NOT NULL, default `0` |
| `pension_cents` | `INTEGER` | NOT NULL, default `0` |
| `other_deduction_cents` | `INTEGER` | NOT NULL, default `0` |
| `gross_cents` | `INTEGER` | NOT NULL |
| `deduction_cents` | `INTEGER` | NOT NULL |
| `net_cents` | `INTEGER` | NOT NULL |

Foreign keys:
- `employee_id` → `employees.id` (on delete: NO ACTION)
- `payroll_run_id` → `payroll_runs.id` (on delete: CASCADE)

### `payroll_runs`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `payroll_number` | `TEXT` | NOT NULL |
| `period_month` | `TEXT` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `gross_total_cents` | `INTEGER` | NOT NULL, default `0` |
| `deduction_total_cents` | `INTEGER` | NOT NULL, default `0` |
| `net_total_cents` | `INTEGER` | NOT NULL, default `0` |
| `journal_entry_id` | `INTEGER` | nullable |
| `created_by` | `INTEGER` | nullable |
| `posted_by` | `INTEGER` | nullable |
| `posted_at` | `TEXT` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `posted_by` → `users.id` (on delete: NO ACTION)
- `created_by` → `users.id` (on delete: NO ACTION)
- `journal_entry_id` → `journal_entries.id` (on delete: NO ACTION)

### `pos_sale_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `sale_id` | `TEXT` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `qty` | `REAL` | NOT NULL |
| `unit_price_cents` | `INTEGER` | NOT NULL |
| `line_total_cents` | `INTEGER` | NOT NULL |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `sale_id` → `pos_sales.id` (on delete: NO ACTION)

### `pos_sales`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `TEXT` | primary key |
| `external_sale_id` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `cashier_id` | `INTEGER` | nullable |
| `source_type` | `TEXT` | NOT NULL |
| `payment_method` | `TEXT` | NOT NULL |
| `total_cents` | `INTEGER` | NOT NULL |
| `sale_time` | `TEXT` | NOT NULL |
| `import_batch_id` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `cashier_id` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)

### `products`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `sku` | `TEXT` | NOT NULL |
| `name` | `TEXT` | NOT NULL |
| `brand` | `TEXT` | NOT NULL, default `''` |
| `category` | `TEXT` | NOT NULL, default `''` |
| `department` | `TEXT` | NOT NULL, default `''` |
| `age_group` | `TEXT` | NOT NULL, default `''` |
| `size` | `TEXT` | NOT NULL, default `''` |
| `color` | `TEXT` | NOT NULL, default `''` |
| `barcode` | `TEXT` | nullable |
| `unit` | `TEXT` | NOT NULL, default `'piece'` |
| `cost_cents` | `INTEGER` | NOT NULL, default `0` |
| `retail_cents` | `INTEGER` | NOT NULL, default `0` |
| `wholesale_cents` | `INTEGER` | NOT NULL, default `0` |
| `active` | `INTEGER` | NOT NULL, default `1` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |
| `updated_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

### `purchase_order_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `purchase_order_id` | `INTEGER` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `ordered_qty` | `REAL` | NOT NULL |
| `received_qty` | `REAL` | NOT NULL, default `0` |
| `unit_cost_cents` | `INTEGER` | NOT NULL |
| `line_total_cents` | `INTEGER` | NOT NULL |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `purchase_order_id` → `purchase_orders.id` (on delete: CASCADE)

### `purchase_orders`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `po_number` | `TEXT` | NOT NULL |
| `supplier_id` | `INTEGER` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `order_date` | `TEXT` | NOT NULL |
| `expected_date` | `TEXT` | nullable |
| `status` | `TEXT` | NOT NULL |
| `subtotal_cents` | `INTEGER` | NOT NULL, default `0` |
| `notes` | `TEXT` | NOT NULL, default `''` |
| `created_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |
| `updated_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `created_by` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)
- `supplier_id` → `suppliers.id` (on delete: NO ACTION)

### `requisition_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `requisition_id` | `INTEGER` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `qty` | `REAL` | NOT NULL |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `requisition_id` → `requisitions.id` (on delete: CASCADE)

### `requisitions`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `requisition_number` | `TEXT` | NOT NULL |
| `from_location_id` | `INTEGER` | NOT NULL |
| `to_location_id` | `INTEGER` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `requested_by` | `INTEGER` | nullable |
| `approved_by` | `INTEGER` | nullable |
| `approved_at` | `TEXT` | nullable |
| `notes` | `TEXT` | NOT NULL, default `''` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `approved_by` → `users.id` (on delete: NO ACTION)
- `requested_by` → `users.id` (on delete: NO ACTION)
- `to_location_id` → `locations.id` (on delete: NO ACTION)
- `from_location_id` → `locations.id` (on delete: NO ACTION)

### `reservation_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `reservation_id` | `INTEGER` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `qty` | `REAL` | NOT NULL |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `reservation_id` → `reservations.id` (on delete: CASCADE)

### `reservations`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `reservation_number` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `customer_id` | `INTEGER` | nullable |
| `status` | `TEXT` | NOT NULL |
| `reserved_until` | `TEXT` | nullable |
| `notes` | `TEXT` | NOT NULL, default `''` |
| `created_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `created_by` → `users.id` (on delete: NO ACTION)
- `customer_id` → `customers.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)

### `sales_return_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `return_id` | `INTEGER` | NOT NULL |
| `sale_line_id` | `INTEGER` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `qty` | `REAL` | NOT NULL |
| `unit_price_cents` | `INTEGER` | NOT NULL |
| `line_total_cents` | `INTEGER` | NOT NULL |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `sale_line_id` → `pos_sale_lines.id` (on delete: NO ACTION)
- `return_id` → `sales_returns.id` (on delete: NO ACTION)

### `sales_returns`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `return_number` | `TEXT` | NOT NULL |
| `sale_id` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `customer_id` | `INTEGER` | nullable |
| `reason` | `TEXT` | NOT NULL |
| `refund_method` | `TEXT` | NOT NULL |
| `refund_total_cents` | `INTEGER` | NOT NULL |
| `received_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `received_by` → `users.id` (on delete: NO ACTION)
- `customer_id` → `customers.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)
- `sale_id` → `pos_sales.id` (on delete: NO ACTION)

### `sessions`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `token_hash` | `TEXT` | primary key |
| `user_id` | `INTEGER` | NOT NULL |
| `csrf_token` | `TEXT` | NOT NULL |
| `expires_at` | `INTEGER` | NOT NULL |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `user_id` → `users.id` (on delete: CASCADE)

### `stock_count_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `stock_count_id` | `INTEGER` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `expected_qty` | `REAL` | NOT NULL |
| `counted_qty` | `REAL` | NOT NULL |
| `variance_qty` | `REAL` | NOT NULL |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `stock_count_id` → `stock_counts.id` (on delete: CASCADE)

### `stock_counts`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `count_number` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `counted_by` | `INTEGER` | nullable |
| `released_by` | `INTEGER` | nullable |
| `released_at` | `TEXT` | nullable |
| `notes` | `TEXT` | NOT NULL, default `''` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `released_by` → `users.id` (on delete: NO ACTION)
- `counted_by` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)

### `supplier_bills`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `bill_number` | `TEXT` | NOT NULL |
| `supplier_id` | `INTEGER` | NOT NULL |
| `bill_date` | `TEXT` | NOT NULL |
| `due_date` | `TEXT` | nullable |
| `total_cents` | `INTEGER` | NOT NULL |
| `paid_cents` | `INTEGER` | NOT NULL, default `0` |
| `status` | `TEXT` | NOT NULL |
| `journal_entry_id` | `INTEGER` | nullable |
| `created_by` | `INTEGER` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `created_by` → `users.id` (on delete: NO ACTION)
- `journal_entry_id` → `journal_entries.id` (on delete: NO ACTION)
- `supplier_id` → `suppliers.id` (on delete: NO ACTION)

### `suppliers`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `supplier_code` | `TEXT` | NOT NULL |
| `name` | `TEXT` | NOT NULL |
| `contact_name` | `TEXT` | NOT NULL, default `''` |
| `email` | `TEXT` | NOT NULL, default `''` |
| `phone` | `TEXT` | NOT NULL, default `''` |
| `tax_id` | `TEXT` | NOT NULL, default `''` |
| `address` | `TEXT` | NOT NULL, default `''` |
| `opening_balance_cents` | `INTEGER` | NOT NULL, default `0` |
| `active` | `INTEGER` | NOT NULL, default `1` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |
| `updated_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

### `users`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `email` | `TEXT` | NOT NULL |
| `full_name` | `TEXT` | NOT NULL |
| `password_hash` | `TEXT` | NOT NULL |
| `role` | `TEXT` | NOT NULL |
| `location_id` | `INTEGER` | nullable |
| `active` | `INTEGER` | NOT NULL, default `1` |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `location_id` → `locations.id` (on delete: NO ACTION)

### `wholesale_order_lines`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `wholesale_order_id` | `INTEGER` | NOT NULL |
| `sku` | `TEXT` | NOT NULL |
| `qty` | `REAL` | NOT NULL |
| `unit_price_cents` | `INTEGER` | NOT NULL |
| `line_total_cents` | `INTEGER` | NOT NULL |

Foreign keys:
- `sku` → `products.sku` (on delete: NO ACTION)
- `wholesale_order_id` → `wholesale_orders.id` (on delete: CASCADE)

### `wholesale_orders`

| Column | SQLite type | Constraints / role |
|---|---|---|
| `id` | `INTEGER` | primary key |
| `order_number` | `TEXT` | NOT NULL |
| `customer_id` | `INTEGER` | NOT NULL |
| `location_id` | `INTEGER` | NOT NULL |
| `status` | `TEXT` | NOT NULL |
| `payment_method` | `TEXT` | NOT NULL |
| `total_cents` | `INTEGER` | NOT NULL, default `0` |
| `notes` | `TEXT` | NOT NULL, default `''` |
| `created_by` | `INTEGER` | nullable |
| `confirmed_by` | `INTEGER` | nullable |
| `confirmed_at` | `TEXT` | nullable |
| `created_at` | `TEXT` | NOT NULL, default `CURRENT_TIMESTAMP` |

Foreign keys:
- `confirmed_by` → `users.id` (on delete: NO ACTION)
- `created_by` → `users.id` (on delete: NO ACTION)
- `location_id` → `locations.id` (on delete: NO ACTION)
- `customer_id` → `customers.id` (on delete: NO ACTION)

## Important integrity contracts

- `inventory_movements` records signed quantities and source references. Its update/delete triggers preserve history.
- `audit_log` stores actor, action, entity and JSON details; update/delete triggers preserve event history.
- `pos_sales` has a unique `(location_id, external_sale_id)` business identity.
- `import_batches.digest` is unique, and `import_rows` is unique by `(batch_id, row_number)`.
- Monetary storage fields use integer cents; API adapters convert to ETB major units.
- `journal_entries` stores debit and credit control totals; `journal_lines` stores account-level postings. The application posting service validates exact balance.
- `number_sequences` allocates document numbers for PO, receipt, requisition, reservation, count, damage, return, wholesale, journal, payments, payroll and close records.

## Stock calculation

For SKU `S` at location `L`, on-hand is the sum of `inventory_movements.qty_delta` for the pair. Positive movement types increase quantity; negative movement types reduce quantity. Active, unexpired reservations are subtracted to calculate available quantity.

## Transaction contract

`transaction(db, callback)` uses `BEGIN IMMEDIATE`, commits when the callback completes and rolls back if an exception is raised. Sales, POS import release, transfers, receiving, physical-count release, damage, returns, wholesale confirmation, payroll posting and payment posting use this boundary for their core records.

## Query and index inventory

- `audit_log`: `idx_audit_created`
- `customers`: `idx_customer_name`
- `daily_closings`: `idx_closings_date`
- `employees`: `idx_employee_status`
- `import_rows`: `idx_import_rows_batch`
- `inventory_movements`: `idx_movements_source`, `idx_movements_created`, `idx_movements_sku_location`
- `journal_entries`: `idx_journal_date`
- `payroll_runs`: `idx_payroll_period`
- `pos_sales`: `idx_pos_sales_time`
- `products`: `idx_products_brand`, `idx_products_name`
- `purchase_orders`: `idx_purchase_order_status`
- `sessions`: `idx_sessions_expiry`
- `suppliers`: `idx_supplier_name`

## Migration and operations

The runtime applies the core schema and the enterprise SQL file when the application opens the database. Schema version is `2`. Back up the database before changing schema files or upgrading an operational data file. See [Operations runbook](OPERATIONS_RUNBOOK.md).
