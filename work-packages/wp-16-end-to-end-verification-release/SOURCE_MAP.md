# WP-16 — end-to-end verification, installer and release source map

**Disposition:** CI and component verification are active; full product E2E, production-scale quality and release evidence are not complete.

## Canonical source paths
- Workflows: `.github/workflows/ci.yml`, `.github/workflows/wp01-16-completion-verification.yml`.
- Backend tests: `backend/tests/Feature/`, `backend/tests/Unit/`.
- Frontend tests: `frontend/src/app/**/*.spec.ts`.
- Installer/runtime: `Makefile`, `docker-compose.yml`, `docker/postgres/init/010-create-roles.sql`, backend and frontend build manifests/lockfiles.
- Release specification: `docs/release/end-to-end-acceptance-security-deployment.md`, `docs/engineering/development-deployment.md`, `docs/engineering/code-quality.md`.
- Evidence: `docs/verification/WP01-WP16-implementation-and-verification-status-2026-11-26.md`, `docs/work-packages/handover-register.md`.

## Existing verified evidence
- Delivery merge SHA `dbdf112272f7f1e0d1ccf933e45061a79cc5e59b`: ERP CI run `46080457557` passed (backend/PostgreSQL and Angular).
- PR #27 candidate SHA `b6e5486324e0eca18c065b253d1640ea7e6f2416`: ERP CI `46080661268` and component/WP-01 source verification `46080661241` passed. Those validate that candidate branch only; PR #27 is not merged.
- Source-intake local package checks and the Python reference runtime checks are separate evidence, not canonical WP-16 acceptance.

## Remaining build / verification
Exact final-SHA backend/frontend/API/E2E, migration upgrade/recovery, RLS adversarial, concurrency/race, idempotency/replay, security/dependency/secret/container, accessibility, performance/load/soak, backup/restore/PITR/DR, installer reproducibility, staging/UAT, rollback and monitoring. Record artifacts and hashes. A green component workflow is not full ERP release approval.
