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

## CAP-002 — R3 ↔ legacy GAR identity reconciliation

- Preserved R3 archive SHA-256: `44f69b36d6ed389633d4e305dacfa825fbdffb99d78cc903e3f050761811a6e7`; source facts: 128 requirements, 24 bounded contexts, 45 candidate-source mappings, 20 package tests.
- Preserved legacy GAR inventory: 120 unique IDs across 13 prefixes. This is a distinct earlier identifier register, not a one-to-one count expectation for R3.
- Draft candidate relationship artifact: `docs/requirements/wp01-r3-gar-crosswalk-2026-11-28.tsv.gz`; uncompressed SHA-256 `5c485ab7dc4ddf5e48a754acfa1ae5857841b4e52bf36b3f43c06bfb6b3cd628`; gzip SHA-256 `2e5f368bc6b2675269a5ca99b75ff8890954194091c880721b14a51023de0bf3`.
- Proposal: 231 candidate edges; 101 SPLIT, 16 PARTIAL, 9 CONSOLIDATED, 2 R3_ADDITION, 0 EXACT. The labels describe provisional graph topology, not semantic equivalence approval; every edge remains draft review. All 120 GAR IDs and 128 R3 IDs are retained. PLT-002 and DWR-006 are explicit additions without a direct legacy GAR predecessor.
- Method: `docs/requirements/wp01-r3-gar-crosswalk-method-2026-11-28.md`. Repository validator: `scripts/verify-wp01-r3-gar-crosswalk.mjs`; it is invoked by `.github/workflows/wp01-16-completion-verification.yml` in `wp01-source-package`.
- Local source-package evidence on 28 November: source validator PASS; 20/20 tests PASS; strict audit A01–A10/F01–F07 PASS. Exact final PR-head evidence: ERP CI [#46154426395](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426395) PASS; full WP verification [#46154426366](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426366) PASS; dedicated R3 package [#46154426369](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426369) PASS. Exact tested head `6e8788be6849a78115ee138eb68a607cbaea1c03` merged to delivery as `bc442495df842f8fb06ae8b8d540145c69155023`. The full backend suite reported 93 Laravel tests / 1,081 assertions; Angular, PostgreSQL 18 migrations/forced RLS, tenant security and the crosswalk validator passed. **CAP-002 is closed for structural identity reconciliation.** Every edge remains `DRAFT_REVIEW_REQUIRED`; classes are graph-shape labels only, and all R3 implementation statuses remain `not_started`.

## CAP-002 structural handover — verified and integrated

- Merge: `bc442495df842f8fb06ae8b8d540145c69155023`; tested parent/head: `6e8788be6849a78115ee138eb68a607cbaea1c03`.
- Exact runs: ERP CI #46154426395 PASS; combined WP workflow #46154426366 PASS; R3 source package #46154426369 PASS.
- Dataset: 128 R3 rows, 120 legacy GAR IDs, 231 candidate edges. Full seven-column metadata validated against R3 source. Gzip SHA-256 `2e5f368bc6b2675269a5ca99b75ff8890954194091c880721b14a51023de0bf3`.
- Boundary remains: no requirement deletion/renumbering; no accepted equivalence/supersession/retirement; all R3 implementation states `not_started`; every edge awaits complete requirement-level review.

## Remaining build / verification
Review each proposed edge against both full requirement statements, actors, workflow, controls, evidence and acceptance boundaries under the delegated WP-01 acceptance authority after exact PR-head validation. Preserve all 120 legacy IDs and all 128 R3 IDs. No mapping changes implementation state. CAP-004 must link each R3 requirement and acceptance scenario to accountable owner, WP task, executable workflow, data/schema, migrations, APIs, UI, events, automated tests, evidence and operational runbooks. Keep all implementation statuses at `not_started` until exact code paths and passing acceptance evidence exist.
