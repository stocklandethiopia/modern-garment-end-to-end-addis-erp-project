# Inventory ledger contract

## Location model

The logical Stockland locations are Central Warehouse, Goro Branch, Gerji Branch, Mexico Branch and Megenagna Branch. Preserve the canonical location IDs already stored in the target database.

## Core invariants

1. On-hand balance is derived from posted ledger entries (or a formally maintained projection reconciled to the ledger). UI forms must not directly overwrite the balance.
2. Every stock movement is traceable to a source document, line, SKU/variant, source location, destination location, quantity, UOM, conversion factor, actor, timestamp and posting batch.
3. Receipt, sale, transfer, return, damage, adjustment, physical-count variance and reservation have distinct movement reasons.
4. Transfers are two-sided and atomic: source decrement and destination increment post together, or neither does.
5. Stock checks and deductions run in one transaction. Lock the affected `(SKU, location)` rows in deterministic order to reduce overselling and deadlocks.
6. For an item with a conversion factor, compare stock-UOM requested quantity with stock-UOM availability.
7. A physical count is a counted observation. Reconcile expected vs counted quantity and post an approved variance; do not erase the movement history.
8. Negative stock is blocked unless a deliberate, audited policy explicitly permits it.
9. Historical movements are immutable. Corrections are reversals or compensating entries, not edits to posted history.
10. Duplicate source document IDs / upload IDs must be rejected or return the prior result, never post twice.
