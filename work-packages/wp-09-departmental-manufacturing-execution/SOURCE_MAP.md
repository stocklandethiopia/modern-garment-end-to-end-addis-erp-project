# WP-09 — departmental manufacturing execution and WIP source map

**Disposition:** canonical MES execution foundation exists; full departmental execution, WIP genealogy and cross-stage business replay are not yet proven by the delivery evidence reviewed.

## Canonical source paths
- `backend/app/Http/Controllers/Manufacturing/ManufacturingController.php`
- `backend/app/Services/Manufacturing/ManufacturingExecutionService.php`
- `backend/database/migrations/2026_11_26_000016_create_route_and_manufacturing_execution.php`
- `docs/manufacturing/departmental-manufacturing-execution.md`
- `docs/manufacturing/route-sop-work-instruction-framework.md`

## Collected source/artifacts
- `source-intake/stockland-garment-erp-local-v0.3.0/source/docs/WORKFLOWS.md` and local inventory/operations modules are supporting business reference, not a garment-factory MES.
- `source-intake/stockland-garment-erp-local-v0.3.0/LOCAL_REFERENCE_RUNTIME_VERIFICATION_2026-11-24.md` records the separate Python/SQLite 19-stage route fixture. Its source archives and seven file hashes were verified in run `45397701554`; the 16 Python tests and fixture do not verify Laravel/PostgreSQL, concurrent production execution, or all factory departments.

## Canonical target paths
`backend/app/Services/Manufacturing/`, `backend/app/Http/Controllers/Manufacturing/`, `backend/database/migrations/`, `frontend/src/app/features/manufacturing/`, `backend/tests/Feature/`, and `docs/manufacturing/`.

## Remaining build / verification
Implement and evidence cutting/spreading/marker/bundle split/merge, sewing operations, subcontract issue/return, special processes, finishing, packing/carton traceability, production order WIP, nonconformance/rework and shift/scan ingestion. Test stage order, authorized conditional skip, component genealogy, idempotency, payload reuse rejection, concurrency, holds, accepted-good quantities and exactly-once FG receipt/outbox posting. Port the reference scenarios as canonical test inputs, not as proof.
