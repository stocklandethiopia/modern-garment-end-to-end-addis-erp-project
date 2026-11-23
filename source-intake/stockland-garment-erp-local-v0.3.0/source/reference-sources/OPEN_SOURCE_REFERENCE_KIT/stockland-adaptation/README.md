# Stockland adaptation contracts

This folder contains the Stockland-facing domain contracts derived from the source review:

- `MODULE_MAPPING.md` — selected upstream concepts and Stockland service boundaries.
- `contracts/CATALOG_VARIANT_CONTRACT.md` — unique garment SKU and variant identity.
- `contracts/INVENTORY_LEDGER_CONTRACT.md` — warehouse, stock movement and count rules.
- `contracts/POS_IMPORT_RELEASE_CONTRACT.md` — branch POS import state machine and final release authority.
- `contracts/OFFLINE_POS_CONTRACT.md` — durable local submission and offline eligibility rules.

These contracts are designed to remain stable as implementation details and repository structure evolve.
