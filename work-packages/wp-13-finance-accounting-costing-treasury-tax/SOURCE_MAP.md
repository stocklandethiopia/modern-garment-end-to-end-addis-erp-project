# WP-13 — finance, accounting, costing, treasury and tax source map

**Disposition:** finance posting controls, GL foundation, customer credit and payroll-review foundation exist; full manufacturing costing, stock valuation, treasury, tax/VAT/withholding, FX and intercompany accounting are incomplete.

## Canonical source paths
- `backend/app/Http/Controllers/Finance/FinanceControlController.php`
- `backend/app/Services/Finance/FinanceControlService.php`
- Migrations: `2026_11_26_000020_create_finance_and_payroll_review_foundation.php`, `000025_create_finance_posting_and_agent_uniqueness_controls.php`, `000026_create_customer_credit_decision_controls.php`.
- Specification: `docs/finance/finance-accounting-treasury-tax-costing.md`.

## Collected source/artifacts
- Local app `src/modules/enterprise.mjs`, `src/migrations/002_enterprise.sql`, `docs/modules/FINANCE.md`, and `docs/DATABASE_SCHEMA.md` contain basic GL/AP/AR/expense/payment/payroll journal examples in SQLite.
- Local source explicitly has a simpler single-company ledger; it is not evidence of full multi-entity garment costing or Ethiopian statutory compliance.

## Remaining build / verification
Complete chart/accounting dimensions, subledgers, supplier/customer settlements, bank and cash reconciliation, period close, budgets, landed cost, fabric/trim/WIP/FG valuation, standard/actual cost variance, intercompany, FX and statutory reports. Connect every stock/manufacturing/payroll transaction to balanced source-linked journals; add concurrency, reversal, cutoff, tax-rule version and period-close tests. Keep statutory calculation activation blocked until WP-02 qualified source approval.
