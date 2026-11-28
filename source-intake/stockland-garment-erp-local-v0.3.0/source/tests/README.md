# `tests/` — acceptance and regression suite

**Owner:** Application Engineering / Quality Engineering  
**Runner:** Node.js built-in `node:test`  
**Command:** `npm test`

## Test inventory

| File | Scope |
|---|---|
| `erp.test.mjs` | End-to-end HTTP + SQLite acceptance suite for identity, access, product variants, stock receipts, retail POS, imports, release, accounting, procurement, warehouse operations, wholesale, HR/payroll, daily close, reporting and immutable history |

## Test conventions

- Use an in-memory SQLite database and ephemeral HTTP port for business integration scenarios.
- Generate synthetic local fixture records for customers, employees, products, sales and payroll.
- Assert HTTP response, returned payload, database records, final balances and audit effects.
- Verify upload, staging, reconciliation and final release as separate transitions.
- Verify duplicate submissions return the original transaction identity.
- Verify location-assigned users remain within their authorized scope.
- Verify paired transfer movements and financial journal entries are atomic and balanced.
- Verify Branch Manager is the final and sole business release authority for branch POS imports.
- Inject a journal write failure to verify import rollback and retry behavior.

## Current acceptance suite

The parent suite contains 14 named workflow scenarios. The Node test runner reports 15 tests including the parent suite. See [Verification report](../docs/VERIFICATION_REPORT.md) for the latest executed command output and runtime details.

## Related documents

- [Test strategy](../docs/TEST_STRATEGY.md)
- [Release gates](../docs/RELEASE_GATES.md)
- [Acceptance matrix](../docs/modules/README.md)
