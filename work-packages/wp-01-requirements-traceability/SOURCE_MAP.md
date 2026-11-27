# WP-01 — requirements and traceability source map

**Disposition:** source-package integrity and package tests are verified; canonical ERP implementation is **not claimed complete**. The R3 package itself states every canonical implementation status as `not_started`.

## Collected source/artifacts
- `work-packages/wp-01-requirements-traceability/README.md`
- `work-packages/wp-01-requirements-traceability/REPOSITORY_IMPORT_MANIFEST.md`
- `work-packages/wp-01-requirements-traceability/source/WP01_R3_SOURCE_2026-11-26.tar.xz.b64.part-00` through `part-17`
- `source-intake/stockland-garment-erp-local-v0.3.0/source/docs/REQUIREMENTS_TRACEABILITY.csv` — earlier local-app requirements, not a replacement for R3.
- `source-intake/stockland-garment-erp-local-v0.3.0/CORRECTIVE_ACTION_TRACEABILITY_2026-11-24.csv` — historical corrective-action register.

## Verified package facts
R3 declares 128 requirements across 24 bounded contexts, 45 candidate-source map rows, 20 Node tests and strict audit A01–A10/F01–F07. Archive SHA-256: `44f69b36d6ed389633d4e305dacfa825fbdffb99d78cc903e3f050761811a6e7`. These are requirements-package results only; they do not prove downstream Laravel/Angular/PostgreSQL code is implemented.

## Canonical target paths
- `docs/requirements/requirements-traceability.md`
- `docs/product/domain-map.md`
- `docs/work-packages/production-execution-plan-wp00-wp16.md`
- `backend/app/Http/Controllers/{Domain}/`
- `backend/app/Services/{Domain}/`
- `backend/database/migrations/`
- `frontend/src/app/features/{domain}/`
- `backend/tests/Feature/`, `backend/tests/Unit/`, `frontend/src/app/**/*.spec.ts`

## Remaining build / verification
Reconcile the 128 R3 records with approved GAR requirements and accepted amendments, resolve the recorded count/identifier deltas, bind each requirement to role/workflow/data/security/API/table/migration/UI/event/test/owner, and eliminate orphan requirements/tests. Keep implementation status at `not_started` until exact code paths and passing acceptance evidence exist.
