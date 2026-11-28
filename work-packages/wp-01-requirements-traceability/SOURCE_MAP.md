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
- Exact PR #32 head `6e8788be6849a78115ee138eb68a607cbaea1c03` is integrated at delivery commit `bc442495df842f8fb06ae8b8d540145c69155023`; candidate and merge trees are identical (`404363a4c89638019a6379500d95da7a89481a95`). ERP CI [#46154426395](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426395) PASS: 93 Laravel tests / 1,081 assertions, 27 Angular tests / 7 spec files, migrations/RLS and PHP checks. Combined WP verification [#46154426366](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426366) PASS: backend, Angular, WP-01 integrity/crosswalk validator and artifact assembly. Dedicated WP-01 source verification [#46154426369](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426369) PASS: archive/checksums, 20/20 tests and strict audits A01–A10/F01–F07.

## Remaining build / verification
The automated identity-preservation and structural-integrity implementation is integrated and verified. The 231 candidate associations remain `DRAFT_REVIEW_REQUIRED`: topology labels do not establish semantic equivalence, supersession or retirement. Review each association against both complete requirement statements, actors, triggers, workflows, controls, evidence and acceptance boundaries. All 128 R3 implementation statuses remain `not_started`; all 120 legacy GAR IDs remain preserved. CAP-002 remains open for this semantic review; CAP-004 must not treat draft associations as approved mappings. CAP-004 must subsequently link each accepted R3 requirement and scenario to accountable owner, WP task, executable workflow, data/schema, migrations, APIs, UI, events, automated tests, evidence and operational runbooks. Keep implementation statuses at `not_started` until exact code paths and passing acceptance evidence exist.

## WP-00 governance hand-off dependency — 28 November 2026

CAP-003's register on branch work/wp00-cap-003-governance-register-current-delivery-2026-11-28 records the cross-WP authority and source boundaries applicable to this package. CAP-002 is integrated only for structural identifier reconciliation: all 231 R3↔GAR candidate associations remain DRAFT_REVIEW_REQUIRED, and the requirement-level semantic decision is still open. The 128 R3 IDs and 120 legacy GAR IDs remain intact; all R3 implementation statuses remain not_started. CAP-004 stays held until semantic review is recorded; neither the governance register nor automated graph validation authorizes requirement retirement or marks implementation complete. See docs/governance/decision-assumption-risk-exception-register-2026-11-28.md and the WP-00 source map for the exact baseline and authority boundaries.