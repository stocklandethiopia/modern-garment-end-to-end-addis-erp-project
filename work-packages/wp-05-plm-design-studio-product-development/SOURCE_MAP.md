# WP-05 — PLM, design studio and product development source map

**Disposition:** canonical product-development, measurement/pattern, sample/fit and BOM/technical-pack foundations exist; full PLM and external design asset lifecycle remain incomplete.

## Canonical source paths
- Controllers: `backend/app/Http/Controllers/ProductDevelopment/{ProductDevelopmentController,MeasurementPatternController,SampleFitController,BomTechnicalPackController}.php`.
- Service: `backend/app/Services/ProductDevelopment/BomTechnicalPackService.php`.
- Migrations: `2026_11_22_000006_create_product_development_brief_and_style_tables.php`, `000007_create_measurement_spec_and_pattern_grading_tables.php`, `000009_create_sample_room_and_fit_workflow_tables.php`, `000012_create_bom_and_tech_pack_controlled_releases.php`.
- Frontend: `frontend/src/app/features/product-development/`.
- Product definition: `docs/product/plm-design-studio-product-development.md`.

## Collected source/artifacts
- Local source `docs/modules/PRODUCT_CATALOG.md` and product variant import template inform catalog/variant fields only.
- No evidence in the local v0.3 archive of a complete CAD/2D/3D design or pattern system; do not infer that it exists.

## Remaining build / verification
Finish revisioned style/color/size matrices, measurement standards and grade rules, pattern/CAD asset metadata and rights, sample request/fit/approval loop, BOM versioning, costing links, tech-pack generation and released snapshots. Add end-to-end design-to-approved-production-release tests; prevent draft/superseded revision use downstream.
