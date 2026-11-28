# WP-11 — quality, compliance, CAPA and HSE source map

**Disposition:** quality inspections, state control and CAPA foundation exist; full apparel QC stages, buyer sampling, compliance evidence and HSE workflows remain incomplete.

## Canonical source paths
- `backend/app/Http/Controllers/Quality/QualityController.php`
- `backend/app/Services/Quality/QualityControlService.php`
- `backend/database/migrations/2026_11_26_000017_create_quality_capa_and_release_gates.php`
- `docs/quality/quality-compliance-capa-hse.md`
- Candidate correction on PR #27: `backend/database/migrations/2026_11_27_000038_repair_quality_inspection_review_state.php`, `backend/tests/Feature/QualityInspectionReviewStateTest.php` on branch `work/fix-private-document-storage-rls-inventory-2026-11-27`. This is not yet integrated into delivery and its migration number must be harmonized.

## Collected source/artifacts
- Local v0.3 contains basic returns/damage controls, not full garment incoming/inline/end-line/final/shipment QC.
- Python reference fixture contains rejection/independent reinspection examples; it is not canonical quality-release evidence.

## Remaining build / verification
Build AQL/sample plan versioning and qualified approval, incoming fabric/trim/chemical checks, first-piece/in-line/end-line/final/shipment inspection, defect classification, hold/release, independent reinspection, CAPA, buyer compliance, restricted chemical/SDS/expiry, worker HSE and environmental evidence. Confirm reviewer separation, immutable evidence, no default AQL plan, failed audit blocks release, accepted quantity reconciliation and downstream production/WMS/finance hold. Re-run exact-SHA migrations, RLS and feature tests after migration-train reconciliation.
