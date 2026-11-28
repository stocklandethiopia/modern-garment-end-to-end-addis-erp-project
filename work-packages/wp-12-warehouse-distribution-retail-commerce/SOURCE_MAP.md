# WP-12 — warehouse, distribution, retail and channel commerce source map

**Disposition:** finished-goods, reservation and fulfilment foundations exist; full multi-warehouse WMS, POS, retail, wholesale, e-commerce and reverse logistics remain partial or unimplemented in canonical Angular/Laravel code.

## Canonical source paths
- `backend/app/Http/Controllers/Distribution/FinishedGoodsController.php`
- `backend/app/Services/Distribution/FinishedGoodsService.php`
- `backend/app/Services/Inventory/InventoryLedgerService.php`
- Migrations: `2026_11_26_000018_create_distribution_order_fulfilment.php`, `000023_create_finished_goods_inventory_and_reservations.php`, `000024_add_finished_goods_location_and_append_only_controls.php`.
- Specification: `docs/distribution/wms-order-management-channel-commerce.md`.

## Collected source/artifacts
- Local app source: `source-intake/.../source/src/server.mjs`, `src/modules/enterprise.mjs`, `src/db.mjs`, `src/migrations/002_enterprise.sql`, `public/app.js`, `docs/modules/INVENTORY.md`, `POS.md`, `WHOLESALE_CUSTOMERS.md`, and templates under `source/templates/`.
- X POS reference: only six MIT-licensed selected source files and their test/manifest/licence evidence. See `source-intake/.../source/reference-sources/OPEN_SOURCE_REFERENCE_KIT/`. This is not a full POS application.

## Remaining build / verification
Implement location/bin topology, receiving/putaway/pick/pack/ship, cycle count, reservation/ATP, lot/serial/roll custody, order allocation, transfer/dispatch, transporter/3PL, retail POS, wholesale account orders, D2C/e-commerce connectors, returns/reverse logistics, offline sale intents and reconciliation. Preserve server-authoritative pricing, payload-aware idempotency, exact control totals, stage/release gates and immutable paired stock movements. Validate all workflows with tenant isolation, concurrency and WMS/finance reconciliation.
