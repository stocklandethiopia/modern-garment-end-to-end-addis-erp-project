# WP-06 — industrial engineering, capacity and readiness source map

**Disposition:** canonical IE/workforce control tables and an IE service/controller exist; a full approved method-study, line-balance and production-readiness workbench is not demonstrated in the current delivery tree.

## Canonical source paths
- Controller/service: `backend/app/Http/Controllers/Planning/IndustrialEngineeringController.php`, `backend/app/Services/Planning/IndustrialEngineeringService.php`.
- Migration: `backend/database/migrations/2026_11_26_000019_create_ie_capacity_and_workforce_controls.php`.
- Specification: `docs/engineering/industrial-engineering-capacity-standard.md`.
- Adjacent dependencies: WP05 measurement/operation standards; WP08 route/SOP; WP10 workforce skills and attendance; WP09 production actuals.

## Collected source/artifacts
- Local v0.3 source describes employee, attendance and payroll records but does not demonstrate garment IE time studies, SAM/SMV, operation bulletins or line balancing.
- Stand-in reference notes mention approved SAM as a planning control, but do not prove canonical IE execution.

## Remaining build / verification
Build effective-dated method studies with observed cycle samples, rating/allowances and approvals; SAM/SMV snapshots; operation bulletins, machine/skill requirements, line balancing, capacity simulations, bottleneck/rework impacts and readiness gate. Test that unapproved methods cannot drive targets or release and that historical standards remain reproducible.
