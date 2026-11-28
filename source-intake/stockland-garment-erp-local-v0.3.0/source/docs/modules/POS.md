# Retail POS and POS import

## Retail sale

A cashier submits branch, payment method, idempotency key and SKU/quantity lines. The server resolves retail prices, checks available quantities including reservations, creates the sale and sale lines, posts stock deductions and writes balanced revenue/tender and COGS journals.

## POS import

The CSV intake service validates branch identity, external sale IDs, SKU, quantity, price, payment method and sale date/time. It stores row results and reconciliation totals without posting. The assigned Branch Manager's release posts all grouped sales in one transaction.

## Critical release rule

Branch Manager release is the final and sole business approval. The upload is not approval. There is no further Owner/Management release step.

## Tables

`pos_sales`, `pos_sale_lines`, `import_batches`, `import_rows`, `inventory_movements`, `journal_entries`, `journal_lines`, `audit_log`.

## API

`GET/POST /api/pos/sales`, `GET/POST /api/pos/imports`, `GET /api/pos/imports/{id}`, `POST /api/pos/imports/{id}/release`, `GET /api/pos/sales/{id}/lines`, `GET/POST /api/pos/returns`.
