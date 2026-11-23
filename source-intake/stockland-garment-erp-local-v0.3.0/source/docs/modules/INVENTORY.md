# Inventory and warehouse

## Purpose

Control stock at Central Warehouse, Goro, Gerji, Mexico and Megenagna using one immutable, source-linked movement ledger.

## Workflows

- Receipt: add stock and accounting impact.
- Transfer: source decrement and destination increment in one transaction.
- Adjustment: reasoned positive or negative movement and balanced accounting entry.
- Reservation: ring-fence stock and reduce sellable availability.
- Requisition: submit, approve and fulfil cross-location stock requests.
- Physical count: record expected and actual quantity, calculate variance and release adjustment.
- Damage: reduce damaged quantity and post shrinkage expense.
- Return: restore stock from an original sale line and record refund/credit accounting.

## Invariants

On-hand quantity is the sum of signed ledger quantities. Ledger rows and audit rows are protected from update/delete. Available stock is on-hand less active, unexpired reservations. Paired transfers post together.

## Tables

`locations`, `inventory_movements`, `requisitions`, `requisition_lines`, `reservations`, `reservation_lines`, `stock_counts`, `stock_count_lines`, `damage_reports`, `damage_report_lines`, `sales_returns`, `sales_return_lines`.

## Entry points

`src/server.mjs`, `src/modules/enterprise.mjs`, `src/db.mjs`, `src/migrations/002_enterprise.sql`, `public/app.js`.
