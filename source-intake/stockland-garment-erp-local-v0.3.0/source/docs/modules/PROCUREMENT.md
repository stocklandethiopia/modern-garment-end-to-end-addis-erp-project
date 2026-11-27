# Procurement and supplier management

## Purpose

Track supplier masters, purchase orders, ordered quantities, partial receiving, goods receipt notes, supplier bills and accounting effects.

## Sequence

`SUPPLIER → PURCHASE ORDER → GOODS RECEIPT → INVENTORY MOVEMENT → SUPPLIER BILL → JOURNAL`

## Rules

- Each PO line references an active SKU.
- Partial receipts update received quantity and order state.
- A receipt quantity cannot exceed the outstanding order quantity.
- Receiving creates a stock movement, bill and journal in one transaction.
- Supplier bill balances are updated by payment posting.

## Tables

`suppliers`, `purchase_orders`, `purchase_order_lines`, `goods_receipts`, `goods_receipt_lines`, `supplier_bills`, `financial_payments`, `journal_entries`, `journal_lines`.

## API

`GET/POST /api/suppliers`, `GET/POST /api/procurement/orders`, `POST /api/procurement/orders/{id}/receive`, `GET /api/finance/ap-bills`, `POST /api/finance/ap-bills`, and supplier payment route.
