# Finance and accounting

## Purpose

Record the company's chart of accounts, posted journals, expenses, supplier bills, customer invoices, payments and trial balance.

## Core accounting rules

- Monetary amounts are integer cents in storage.
- A journal contains at least two lines and exact equality between debit and credit totals.
- POS tender and revenue are posted with cost of goods sold and inventory relief.
- Goods receipts create inventory and supplier liability records.
- Expenses debit an expense account and credit a selected cash/bank asset account.
- AP payments debit accounts payable and credit the settlement account.
- AR receipts debit settlement and credit accounts receivable.
- Payroll posts gross expense and net/statutory deduction liabilities.

## Seeded accounts

The migration seeds cash, bank, card clearing, mobile money, accounts receivable, inventory, accounts payable, goods-received liability, wages/pension/tax liabilities, equity, sales, returns, inventory gain, COGS, payroll expense, operating expense, shrinkage/damage and store-credit liability.

## Tables

`accounts`, `journal_entries`, `journal_lines`, `expenses`, `supplier_bills`, `customer_invoices`, `financial_payments`, `accounting_periods`, `number_sequences`.

## API

`/api/finance/accounts`, `/api/finance/journals`, `/api/finance/expenses`, `/api/finance/ap-bills`, `/api/finance/ar-invoices`, invoice payment routes and `/api/finance/trial-balance`.
