# `src/` — backend application

**Owner:** Application Engineering  
**Application version:** 0.3.0  
**Contract:** server-side identity, authorization, location scope, input validation, product pricing, stock calculations, accounting and workflow posting.

## File inventory

| File | Responsibility |
|---|---|
| `server.mjs` | HTTP server, API route dispatch, sessions, CSRF, role checks, location scope, request validation, POS operations, import release, reports and static file delivery |
| `db.mjs` | SQLite connection, core schema, indexes, immutable-history triggers, transaction helpers, standard locations and stock balance queries |
| `security.mjs` | Scrypt password hashing, session token generation/digests, cookie parsing and cookie headers |
| `modules/enterprise.mjs` | Supplier/customer masters, purchasing, receiving, transfers, requisitions, reservations, counts, returns, damage, wholesale, finance, HR/payroll and daily closing |
| `migrations/002_enterprise.sql` | Versioned enterprise table, index, account seed and schema-version migration |

## Engineering conventions

1. The server calculates authoritative values for price, cost, availability, totals and approval identity.
2. SQL statements bind user values through parameters.
3. Role and location authorization execute in the API before protected data access or mutation.
4. Compound inventory, POS, import and accounting operations use database transactions.
5. Audit evidence is written with the business state transition.
6. Posted movement and audit records are append-only; corrections use compensating business events.
7. API errors use structured responses and server logs retain diagnostic context.
8. Every business workflow has success and access/validation regression coverage.

## Extension convention

Add a numbered SQL migration, service route, acceptance test, module specification, traceability entry and generated build manifest entry for each new domain capability.

## Related documents

- [Architecture](../docs/ARCHITECTURE.md)
- [API reference](../docs/API_REFERENCE.md)
- [Database schema](../docs/DATABASE_SCHEMA.md)
- [Security model](../docs/SECURITY_MODEL.md)
- [Module specifications](../docs/modules/README.md)
