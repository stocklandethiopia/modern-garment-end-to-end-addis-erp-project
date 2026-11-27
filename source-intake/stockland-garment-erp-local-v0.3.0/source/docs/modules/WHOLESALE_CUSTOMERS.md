# Wholesale and customer management

## Customer master

Customer type supports `retail`, `wholesale`, `reseller` and `corporate`. Master fields include code, contact, email, phone, tax identifier, address, credit limit and opening balance.

## Wholesale process

Create a draft order with customer, branch, payment mode and product lines. Confirmation checks available stock, creates sale movements, records revenue/COGS journal effects and creates an AR invoice when the payment method is `CREDIT`.

## Tables

`customers`, `wholesale_orders`, `wholesale_order_lines`, `customer_invoices`, `financial_payments`, `pos_sales` (retail only), `inventory_movements`, `journal_entries`, `journal_lines`.

## API

`GET/POST /api/customers`, `GET/POST /api/wholesale/orders`, `POST /api/wholesale/orders/{id}/confirm`, `GET/POST /api/finance/ar-invoices` and customer payment route.
