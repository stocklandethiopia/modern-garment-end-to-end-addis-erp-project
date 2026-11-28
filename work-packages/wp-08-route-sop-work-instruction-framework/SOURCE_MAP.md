# WP-08 — versioned route, SOP and work-instruction framework source map

**Disposition:** route/MES migration and manufacturing service are present; the configurable route graph, conditional-stage execution, SOP versioning and exception control require a complete verified slice.

## Canonical source paths
- `backend/app/Http/Controllers/Manufacturing/ManufacturingController.php`
- `backend/app/Services/Manufacturing/ManufacturingExecutionService.php`
- `backend/database/migrations/2026_11_26_000016_create_route_and_manufacturing_execution.php`
- `docs/manufacturing/route-sop-work-instruction-framework.md`

## Collected source/artifacts
- Local app `docs/WORKFLOWS.md` and `docs/modules/BRANCH_OPERATIONS.md` contain generic operational workflow notes, not canonical route graph definitions.
- The Python reference fixture reports a 19-stage route replay and optional stage 10 skip. That is a separate SQLite/Python test fixture, not production Laravel/PostgreSQL evidence.

## Remaining build / verification
Implement versioned route graphs, ordered stage/operation definitions, optional vs required stages, controlled skip reasons/approval, SOP/work-instruction version attachment, station/machine/skill prerequisites, route release snapshots and rework loops. Test illegal skips, out-of-order transitions, concurrent scans, version changes during active orders, audit evidence and replay.
