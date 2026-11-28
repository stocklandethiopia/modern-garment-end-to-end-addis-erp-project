# WP-04 — initiative, market insight, merchandising, range and assortment source map

**Disposition:** a real, tested canonical slice exists; full initiative-first merchandising, controls and user journeys remain partial.

## Canonical source paths
- Controllers: `backend/app/Http/Controllers/Merchandising/{InitiativeController,InsightSourceController,MarketInsightController,EvidencePolicyController,MerchandisingPlanningController,MerchandisingReportingController,OtbPlanningController,CommercialPricingController,AssortmentAccountTermsController}.php`
- Services: `backend/app/Services/Merchandising/{EvidencePolicyService,EvidenceSufficiencyService,MerchandisingDashboardService,AssortmentAccountTermsService}.php`
- Migrations: `2026_11_22_000004_create_initiative_and_market_insight_tables.php`, `000005_create_brand_range_assortment_planning_tables.php`, `000008_create_initiative_evidence_requirement_policies.php`, `000011_create_assortment_account_terms.php`.
- Frontend: `frontend/src/app/features/merchandising/` (initiative, market insight, dashboard, evidence policy, planning, range components/services).
- Tests: `backend/tests/Feature/{InitiativeWorkflowTest,BrandRangeWorkflowTest,DemandOtbPricingWorkflowTest}.php`.
- Product definition: `docs/product/initiative-merchandising-range-planning.md`.

## Collected source/artifacts
- `source-intake/.../source/docs/modules/PRODUCT_CATALOG.md`, `WHOLESALE_CUSTOMERS.md`, `WORKFLOWS.md`, `REQUIREMENTS_TRACEABILITY.csv`.
- Reference contracts under `source-intake/.../source/reference-sources/OPEN_SOURCE_REFERENCE_KIT/stockland-adaptation/contracts/` are input patterns, not canonical implementation.

## Remaining build / verification
Complete owner-portfolio/initiative lifecycle, evidence provenance and sufficiency, collection/season/range/assortment governance, commercial account terms, OTB/forecast reconciliation, pricing/promotion and decision permissions. Exercise rejection, revision, expiry, conflicting approvals, idempotent events and tenant adversarial tests. A source screen or table does not establish acceptance.
