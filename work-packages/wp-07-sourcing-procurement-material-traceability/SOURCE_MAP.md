# WP-07 — sourcing, procurement and material traceability source map

**Disposition:** sourcing/material traceability and immutable receipt links exist in canonical code; garment-grade separate material ledgers, genealogy and full supplier controls require completion and acceptance.

## Canonical source paths
- Controller/service: `backend/app/Http/Controllers/Sourcing/SourcingController.php`, `backend/app/Services/Sourcing/SourcingService.php`, `backend/app/Services/Inventory/InventoryLedgerService.php`.
- Migrations: `2026_11_26_000015_create_sourcing_material_traceability.php`, `000022_add_receipt_traceability_links_and_immutability_guards.php`.
- Specification: `docs/procurement/supplier-sourcing-material-traceability.md`.

## Collected source/artifacts
- `source-intake/.../source/docs/modules/PROCUREMENT.md`
- `source-intake/.../source/docs/modules/INVENTORY.md`
- Local source SQL/schema and `templates/imports/supplier-purchase-order-template.csv`, `product-variant-template.csv`, and `physical-count-template.csv`.
- Reference contracts: `INVENTORY_LEDGER_CONTRACT.md`, `CATALOG_VARIANT_CONTRACT.md`.

## Remaining build / verification
Complete supplier qualification, RFQ/quotation/PO amendments, receipts/inspection, supplier returns, import/FX/customs evidence and four separate append-only fabric/trim/chemical/packaging custody chains. Prove lot/roll/shade/batch genealogy, UOM conversions, accepted-only issue, expiry/SDS gate, shortage/over-issue prevention, concurrency, single reversal and ledger reconciliation. Local SQLite movements are reference only.
