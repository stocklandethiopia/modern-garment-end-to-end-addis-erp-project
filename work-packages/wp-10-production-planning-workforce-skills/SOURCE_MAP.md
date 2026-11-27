# WP-10 — production planning, workforce skills and plan/actual source map

**Disposition:** IE/capacity schema and services exist, and manufacturing/quality controls supply downstream dependencies; a complete operational plan/actual and workforce skill deployment workflow is not evidenced.

## Canonical source paths
- `backend/app/Http/Controllers/Planning/IndustrialEngineeringController.php`
- `backend/app/Services/Planning/IndustrialEngineeringService.php`
- `backend/database/migrations/2026_11_26_000019_create_ie_capacity_and_workforce_controls.php`
- `docs/manufacturing/production-planning-skills-incentives-improvement.md`
- `docs/engineering/industrial-engineering-capacity-standard.md`

## Collected source/artifacts
- Local source `source-intake/.../source/docs/modules/HR_PAYROLL.md`, employee template and attendance/payroll tables provide basic HR data examples only.
- Local source does not implement SAM/SMV-driven line/hour planning, certified skills or full production plan-vs-actual control.
- Reference runtime verification lists approved SAM as a control, but it is stand-in evidence only.

## Remaining build / verification
Connect approved WP-06 methods to factory/department/line/shift/hour targets; skill/qualification matrix and certification; deployment eligibility; attendance/leave/overtime controls; production actuals; downtime/reason codes; efficiency and bottleneck analysis; training/improvement loops; fair-work and safety constraints. Test capacity calculations, approval/version snapshots, under/over target exceptions, missing skills and no target derivation from unapproved SAM.
