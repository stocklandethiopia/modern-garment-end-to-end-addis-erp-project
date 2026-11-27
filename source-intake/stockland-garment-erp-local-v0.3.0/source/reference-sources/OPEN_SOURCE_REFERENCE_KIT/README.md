# Stockland Open-Source ERP Reference Kit

**Prepared:** 2026-11-22  
**Purpose:** curated open-source source intake and adaptation reference for Stockland Ethiopia ERP/POS.

## Included source

The `reference-sources/xpos/` folder contains selected source files from the MIT-licensed **X POS** project, branch `develop`, commit:

`4f65d681f766c0dcc73fcbb2debc7bd105c8223f`

Selected implementation topics:

- stock availability and concurrency-aware row locking;
- configurable scale/barcode parsing;
- durable POS submission and idempotent recovery;
- explicit offline-sale policy;
- regression tests for stock guards, units of measure, returns and retry identity.

The source files are retained under their upstream-relative paths. `reference-sources/xpos/LICENSE`, `ORIGIN.md` and `SOURCE_MANIFEST.json` preserve license and attribution details.

## Stockland engineering alignment

1. Product identity supports brand, style, SKU, size, colour, department, age group, barcode and unit.
2. Stock is tracked at Central Warehouse and Goro, Gerji, Mexico and Megenagna branches.
3. Inventory movements are ledger-backed and auditable.
4. POS import follows upload → automatic validation → staging → reconciliation → Branch Manager release → automatic atomic posting.
5. Duplicate detection, row validation, calculations, reconciliation and audit capture are automated.
6. Branch Manager release is the final and sole business approval for branch POS imports.
7. Offline checkout applies explicit payment, catalogue, local storage and stock policies.

## Technology integration boundary

The upstream snippets use Frappe/ERPNext Python and TypeScript. They remain isolated from the Stockland Node.js/SQLite runtime. The design contracts in `stockland-adaptation/` translate the useful business invariants into Stockland's service boundaries.

## Folder guide

- `reference-sources/xpos/` — source, MIT license and origin record.
- `stockland-adaptation/` — Stockland module mapping and domain contracts.
- `source-review/` — source selection and license register explanation.
- `tests/` — Stockland acceptance matrix for adapted concepts.

## Source links

- X POS: https://github.com/kodlyft/xpos
- Included snapshot: https://github.com/kodlyft/xpos/tree/4f65d681f766c0dcc73fcbb2debc7bd105c8223f
