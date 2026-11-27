# Stockland module mapping

This mapping relates the selected upstream concepts to the Stockland application's functional service boundaries. The existing project architecture and domain contracts determine exact source locations during future integration work.

| Domain | X POS reference | Stockland adaptation boundary |
|---|---|---|
| Inventory availability | `xpos/x_pos/api/item_processing/stock.py` | Stock read service scoped by SKU variant and warehouse; transaction and deterministic row locks before final stock validation |
| Barcode / scale labels | `xpos/x_pos/api/item_processing/barcode.py` | Configured Stockland barcode parser resolving one SKU/variant; server-side price policy remains authoritative |
| POS sale submission | `frontend/src/services/invoiceSubmission.ts` | Durable sale-intent record before submit; stable unique sale ID; retries reuse the original sale ID |
| Offline policy | `frontend/src/services/offlinePolicy.ts` | Explicit rules for transaction eligibility, payment mode, catalogue scope and local storage state |
| Regression tests | `xpos/api/tests/test_stock_guards.py`, `test_invoice_retry.py` | Business scenarios represented by Stockland's Node test suite |

## Adapter conventions

- Reuse business invariants and acceptance cases while implementing against the Stockland runtime and data model.
- Keep one authoritative service for each business operation and persisted record.
- Preserve the inventory movement ledger and double-entry accounting boundaries.
- Preserve Branch Manager as the final and sole business approver for branch POS imports.
- Keep upstream license and attribution notices with any future copied or derived source.
