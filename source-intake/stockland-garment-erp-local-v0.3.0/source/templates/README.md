# `templates/` — controlled import and operations worksheets

**Owner:** Inventory, POS Integration, HR and Finance  
**Application version:** 0.3.0

## Template inventory

| File | Purpose |
|---|---|
| `pos-import-template.csv` | Source branch POS sales for automated validation, staging and reconciliation |
| `physical-count-template.csv` | SKU-level counted quantity by Stockland location |
| `imports/product-variant-template.csv` | Product, brand, size, colour, barcode and price master import |
| `imports/supplier-purchase-order-template.csv` | Supplier purchase order lines and delivery quantities |
| `imports/employee-template.csv` | Employee master import fields |

## POS CSV contract

`branch_code, external_sale_id, sku, qty, unit_price, payment_method, sale_time`

- `branch_code`: canonical Stockland branch code.
- `external_sale_id`: stable source sale identity for duplicate protection.
- `sku`: active Stockland saleable SKU.
- `qty`: positive quantity, up to three decimal places.
- `unit_price`: source transaction unit price in decimal ETB format.
- `payment_method`: `CASH`, `CARD`, `MOBILE_MONEY` or `BANK_TRANSFER`.
- `sale_time`: valid date/time; explicit timezone is preferred.

The source control total is supplied to the import API and reconciled against the imported line total. Upload creates a staged batch. The assigned Branch Manager performs final release and the application posts the reconciled transactions automatically.

## Template change conventions

Version a changed column contract, update the relevant API and module specification, and add fixtures for each supported export format. Use synthetic sample rows in documentation and tests.

## Related documents

- [POS import release contract](../docs/POS_IMPORT_RELEASE.md)
- [Product catalogue specification](../docs/modules/PRODUCT_CATALOG.md)
- [Inventory specification](../docs/modules/INVENTORY.md)
- [API reference](../docs/API_REFERENCE.md)
