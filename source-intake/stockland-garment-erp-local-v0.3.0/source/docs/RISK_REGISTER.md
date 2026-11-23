# Integrity and operational safeguard matrix

**Document ID:** STK-CTRL-001  
**Application version:** 0.3.0

| Control area | Implemented safeguard | Source of evidence |
|---|---|---|
| Account access | Scrypt password hashes, opaque session tokens, role checks and location scope | `src/security.mjs`, `src/server.mjs`, authentication tests |
| Browser request integrity | CSRF tokens, JSON content-type validation, bounded request bodies and security headers | HTTP middleware and release gate |
| SKU uniqueness | Unique SKU and optional barcode constraints | `src/db.mjs`, product acceptance tests |
| Stock integrity | Derived quantities, immutable movements and atomic paired transfer legs | Database triggers, movement ledger and transfer tests |
| POS identity | Location/external-sale uniqueness and stable idempotency key | POS route and retry acceptance test |
| POS import reconciliation | SHA-256 digest, exact source total, row validation and branch identity | Import batch/row records and acceptance tests |
| Final approval | Assigned Branch Manager is final and sole POS import approver | Release role guard, state machine and audit event |
| Atomic posting | Import, sale, movements and journal post in one transaction | Injected journal failure rollback test |
| Accounting integrity | Integer-cent values and balanced journal validation | Journal posting helper and trial-balance acceptance test |
| Historical traceability | Immutable audit entries and source-linked business records | SQLite triggers and audit acceptance test |
| Data recovery | WAL-aware database backup and restore steps | `OPERATIONS_RUNBOOK.md` |
| Source integrity | Per-file SHA-256 manifest and archive checksum | `BUILD_MANIFEST.json` and release checksum |
| Continuous verification | Repeatable GitHub Actions workflow | `.github/workflows/ci.yml` |

These controls are part of the engineering and operating contract. New modules follow the same validation, authorization, transaction, audit, documentation and acceptance-test patterns.
