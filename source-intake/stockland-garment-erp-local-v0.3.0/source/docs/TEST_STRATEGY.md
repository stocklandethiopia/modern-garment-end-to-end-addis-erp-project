# Test strategy and evidence standard

**Document ID:** STK-TEST-001  
**Application version:** 0.3.0

## Test layers

| Layer | Command / location | Coverage |
|---|---|---|
| JavaScript syntax | `npm run check` | All `.js` and `.mjs` files in project scope |
| Integration acceptance | `npm test` | HTTP API, SQLite transactions, business workflows and security boundaries |
| Local release gate | `npm run release:gate` | Required files, docs links, traceability, Node version, package metadata and HTTP/PWA/security smoke |
| Source inventory integrity | `npm run verify:manifest` | Exact file set, size and SHA-256 hashes |
| Continuous integration | `.github/workflows/ci.yml` | Repeats all four gates on Node 22.x for pushes, pull requests and manual dispatch |

## Acceptance scenarios

1. Empty database health, initial Owner setup, five locations and one-time setup lockout.
2. CSRF rejection, user provisioning restrictions, assignment rules and Warehouse Manager location isolation.
3. Product variant creation/update, stock receipt and ledger-derived availability.
4. Retail POS pricing, idempotent retry, returned sale-line lookup and aggregate same-SKU oversell protection.
5. POS import source total validation, staging-only behavior, reconciliation variance and duplicate upload identity.
6. Assigned Branch Manager final release, automatic posting, duplicate release and branch report scope.
7. POS import posting failure injected at journal insertion, transaction rollback, no partial sale/stock effects and retry success.
8. Atomic two-leg transfer and source shortage rejection.
9. Supplier master, purchase order, goods receiving, inventory and AP document generation.
10. Customer master, wholesale order confirmation, stock and accounting posting.
11. Reservations, physical count variance release and journal evidence.
12. Damage posting, linked return/refund, manual stock adjustment and requisition fulfilment.
13. Expenses, AP/AR payments, journal balancing, employee/contracts/attendance, payroll posting and daily closing.
14. Ledger and audit immutability plus browser, PWA manifest, service worker, icon and count worksheet delivery.

## Test evidence rules

A result record includes: command, runtime version, test name, expected behavior, actual behavior, exit code, file/package SHA-256 and timestamp. For financial or stock movements, include database assertions for movement/journal rows and final balance. For approval workflows, include actor role, location assignment, state transition and audit row.

## Reproduce locally

```bash
npm run check
npm test
npm run release:gate
npm run verify:manifest
```

The integration suite starts an ephemeral local HTTP server and in-memory SQLite database. It does not use customer or branch operational data.
