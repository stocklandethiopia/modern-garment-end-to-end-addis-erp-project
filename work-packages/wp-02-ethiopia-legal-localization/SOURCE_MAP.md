# WP-02 — Ethiopia legal and regulatory localization source map

**Disposition:** legal-source and localization material exists; qualified local legal/payroll/tax approval and production rule activation are not established by this source inventory.

## Collected source/artifacts
- `docs/localization/ethiopia.md`
- `docs/localization/ethiopia-legal-regulatory-register.md`
- `docs/hr/workforce-payroll-pension-benefits-compensation.md`
- `docs/hr/payroll-and-variable-compensation.md`
- `source-intake/stockland-garment-erp-local-v0.3.0/source/docs/modules/HR_PAYROLL.md`
- Candidate localization evidence on `work/wp03-wp14-organogram-payroll-plan-2026-11-27` and `work/wp14-ethiopia-payroll-localization-2026-11-27`: legal requirements matrix, engineering release gate, candidate calculation fixtures and rule-payload template. These candidate files are not yet canonical delivery files.

## Canonical target paths
- `docs/localization/` — source register, jurisdiction/effective-date notes and approval history.
- `backend/app/Services/Localization/` (create only when the approved bounded context requires a distinct service).
- `backend/database/migrations/` — effective-dated rules, immutable source evidence, and approval state.
- `backend/tests/Feature/` and `backend/tests/Unit/` — source version, effective-date boundary and approval-gate tests.
- `frontend/src/app/features/localization/` — authorized legal-rule review/configuration UI, not a silent rule editor.

## Remaining build / verification
Inventory every applicable federal/regional authority, source instrument, article, publication/effective date, amendment and official evidence. Store source hash, version, status, supersession, reviewer and qualified approval. Add date-boundary tests and make unverified/expired rules non-activatable. Do not calculate/activate statutory payroll or tax rules from illustrative sample CSVs alone.
