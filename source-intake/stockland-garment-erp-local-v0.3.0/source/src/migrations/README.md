# Database schema migrations

`002_enterprise.sql` extends the core schema with enterprise reference, procurement, stock-control, wholesale, finance, HR and operations tables, indexes and chart-of-accounts seed rows. `src/db.mjs` initializes the core tables and applies this migration. Schema version is tracked with `PRAGMA user_version` (current version 2). New schema changes use a new ordered SQL file and update the schema version.
