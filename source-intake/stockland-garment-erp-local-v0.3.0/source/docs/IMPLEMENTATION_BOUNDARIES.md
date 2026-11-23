# Module contracts and service boundaries

**Document ID:** STK-ARC-002  
**Application version:** 0.3.0

## Layer ownership

| Layer | Owns | Public integration point |
|---|---|---|
| HTTP server | HTTP lifecycle, input parsing, session resolution, core route dispatch, static UI delivery | `createApplication()` and `/api/...` |
| Security | Password derivation, token generation, cookie parsing, token hashing | `src/security.mjs` |
| Database | SQLite initialization, schema application, transaction, location and balance primitives | `openDatabase`, `transaction`, `getOnHand` |
| Core domain | Users, products, inventory, POS, import release, reporting and audit | `src/server.mjs` |
| Enterprise domain | Supplier/customer, procurement, stock controls, wholesale, finance, HR and closing | `handleEnterpriseApi` |
| User interface | Navigation, module views, form handling and API calls | `public/app.js` |
| Static shell | HTML, CSS, manifest, icons, service worker and templates | `public/` |
| Tests | Isolated in-memory integration and acceptance scenarios | `tests/erp.test.mjs` |

## Cross-cutting invariants

1. The API is the authority for identity, role, location, price, availability, totals and state transitions.
2. Each business operation records its source identity and actor in the audit trail.
3. Stock is changed only by an inventory movement.
4. Corrections to stock use additional movements; prior movement rows remain intact.
5. Accounting entries are posted through the shared journal service and must balance.
6. Critical workflows group business state, ledger movement, journal and audit changes in a database transaction.
7. UI navigation and disabled buttons are presentation features; the API independently authorizes every operation.
8. Open-source reference code remains isolated beneath `reference-sources/` and its license is retained.

## Adding a new module

- Place domain routes and logic in the responsible module/service file.
- Add schema definitions in a numbered SQL migration under `src/migrations/`.
- Validate all user input at the API boundary.
- Apply role and location checks before data access or mutation.
- Use `transaction()` for multi-record postings.
- Record an audit event for every business state transition.
- Add module documentation under `docs/modules/`.
- Add acceptance cases in `tests/erp.test.mjs`.
- Update `docs/REQUIREMENTS_TRACEABILITY.csv`, `docs/ARTIFACT_INVENTORY.md` and `BUILD_MANIFEST.json`.
