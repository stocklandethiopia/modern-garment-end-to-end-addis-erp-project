# Open-source source provenance

**Document ID:** STK-OS-001  
**Reference collection:** `reference-sources/OPEN_SOURCE_REFERENCE_KIT/`

## Included upstream project

| Field | Value |
|---|---|
| Project | X POS |
| Repository | https://github.com/kodlyft/xpos |
| Branch snapshot | `develop` |
| Commit | `4f65d681f766c0dcc73fcbb2debc7bd105c8223f` |
| Snapshot commit date | 2026-11-19 |
| License | MIT |
| License text | `reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/LICENSE` |

## Included source paths

- `frontend/src/services/invoiceSubmission.ts`
- `frontend/src/services/offlinePolicy.ts`
- `xpos/api/tests/test_invoice_retry.py`
- `xpos/api/tests/test_stock_guards.py`
- `xpos/x_pos/api/item_processing/barcode.py`
- `xpos/x_pos/api/item_processing/stock.py`

The files preserve their upstream-relative paths beneath the reference collection. Stockland-authored application code resides under `src/` and `public/`. The upstream MIT license, origin record, source manifest and blob identifiers are stored beside the selected code.

## Technical adaptation

The X POS snippets use Frappe/ERPNext Python and TypeScript. Stockland's local application uses Node.js, SQLite and browser JavaScript. The reference contributes domain patterns for stable transaction identity, inventory availability checks, barcode parsing and explicit offline policies. Stockland's own implementation and acceptance tests are maintained in the application source tree.

## Other discovery references

`reference-sources/OPEN_SOURCE_REFERENCE_KIT/SOURCE_REGISTER.csv` records the other clothing, garment, textile and POS projects reviewed, their observed license declarations and technical roles. License terms remain attached to their respective source components. GPL-3.0, Apache-2.0, MIT and CC0 license families are identified separately in the register.
