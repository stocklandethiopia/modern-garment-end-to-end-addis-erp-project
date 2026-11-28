# WP-16 — end-to-end verification, installer and release source map

**Disposition:** CI and component verification are active; full product E2E, production-scale quality and release evidence are not complete.

## Canonical source paths
- Workflows: `.github/workflows/ci.yml`, `.github/workflows/wp01-16-completion-verification.yml`.
- Backend tests: `backend/tests/Feature/`, `backend/tests/Unit/`.
- Frontend tests: `frontend/src/app/**/*.spec.ts`.
- Installer/runtime: `Makefile`, `docker-compose.yml`, `docker/postgres/init/010-create-roles.sql`, backend and frontend build manifests/lockfiles.
- Release specification: `docs/release/end-to-end-acceptance-security-deployment.md`, `docs/engineering/development-deployment.md`, `docs/engineering/code-quality.md`.
- Evidence: `docs/verification/WP01-WP16-implementation-and-verification-status-2026-11-26.md`, `docs/work-packages/handover-register.md`.

## Existing verified evidence — refreshed 28 November 2026
- Canonical delivery SHA `3438ab47240c10ce5ea5f1f772c1570cc2191134`: exact ERP CI #46086017090 passed all three jobs (Laravel/PostgreSQL, Angular, dependency-lock reconciliation).
- Master audit PR #30 head `12707a51a76c51110d07e9d4375eac1c2ec37d60`: ERP CI #46122655184 and configured WP/component verification #46122655071 both passed; these are documentation-audit checks, not full 125-task or E2E-001–008 acceptance.
- PR #27 candidate SHA `b6e5486324e0eca18c065b253d1640ea7e6f2416`: ERP CI #46080661268 and component/WP-01 source verification #46080661241 passed. Those validate that candidate branch only; PR #27 is not merged.
- PR #29 candidate SHA `c716757e3b0fe091b2e6223b416237d32e455c8e`: ERP CI #46089028046 and component/WP-01 verification #46089027993 passed; those validate only the PR branch.
- PR #1 → main merge-preview ERP CI #46086022250 checked out synthetic merge SHA `4570039...` and failed at Pint on one style finding in `QualityInspectionReviewStateTest.php`; backend migrations/feature tests were skipped, Angular passed. This does not contradict the direct delivery SHA #46086017090 pass.
- Source-intake local package checks and the Python reference runtime checks are separate evidence, not canonical WP-16 acceptance. Full live PR/migration/evidence inventory is in `docs/audit/corrective-action-execution-ledger-2026-11-28.md`.

## Remaining build / verification
Exact final-SHA backend/frontend/API/E2E, migration upgrade/recovery, RLS adversarial, concurrency/race, idempotency/replay, security/dependency/secret/container, accessibility, performance/load/soak, backup/restore/PITR/DR, installer reproducibility, staging/UAT, rollback and monitoring. Record artifacts and hashes. A green component workflow is not full ERP release approval.
