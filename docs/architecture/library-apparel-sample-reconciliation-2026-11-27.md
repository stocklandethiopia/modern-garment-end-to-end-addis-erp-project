# Library Apparel Samples — Greenfield ERP Reconciliation

**Audit date:** 27 November 2026  
**Target repository:** `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`  
**Target baseline:** `delivery/autonomous-wp-01-16` at `fc01eced62b97c4ec27f0075264fc8ba0fb11851`  
**Disposition:** reference contracts assessed; legacy runtime patch rejected for direct reuse; target-specific implementation and test backlog recorded.

## 1. Executive decision

The ChatGPT Library contains useful apparel domain samples, including a PLM implementation patch, a module catalogue patch, a 76-entry apparel supply-chain workspace, a separate eight-document blueprint pack, and 25 proposed JSON Schema contracts.

These samples are **not source code for this greenfield application**. The PHP patches target the older WordPress plugin ERP (WordPress REST, `$wpdb`/MySQL or MariaDB, plugin activation and `dbDelta`). The target application is Laravel 13, Angular 22 and PostgreSQL 18 with tenant-leading keys, forced RLS, restricted runtime grants and explicit Laravel migrations. Copying the old controllers, schema installer, bootstrap hooks or SQL into this repository would create a second, incompatible runtime model.

The useful contribution is the domain contract and control intent. This document maps those ideas to the existing greenfield code and identifies the remaining acceptance gaps. No legacy PHP runtime file or schema installer was copied into the target application.

## 2. Library material inspected and local verification

| Library sample | Exact SHA-256 | Contents / disposition |
|---|---|---|
| `STOCKLAND_APPAREL_ENTERPRISE_WORKSPACE_2026-11-15.zip` | `9593071b85d9a7d3a91a908fe0975106df3aa5977efd6b52de26abb649db30d4` | 76 entries: architecture/workflow docs, 25 proposed JSON Schemas, 9 changeset plans, QA harness and release handoff. Documentation/contracts only. |
| `STOCKLAND_APPAREL_SUPPLY_CHAIN_BLUEPRINT_DOCS_2026-11-15(2).zip` | `25d6af1c279b2a1646cdf6e1d95a2b54d6909e2b14cbce8ea01e6707961f4014` | Eight architecture, gap-map, roadmap and repository-evidence documents. |
| `APPAREL_PLM_LOCAL_OVERLAY.patch` | `2e94ebdeb0d4e26ce6bca046a9db673c4f375760724888fb3a3fcb381353e72a` | Legacy WordPress PHP PLM/schema/REST patch. Review-only; not portable as-is. |
| `APPAREL_SAAS_MODULE_CATALOG.patch` | `a10d16515f4c91726280b525e7f7b9dfdbf5deffa6db3d4ad67b6eaa3d0d63f3` | Legacy PHP declarative module catalogue and static tests. Review-only; provisioning/entitlement enforcement was explicitly not implemented in that sample. |
| `PLM_IMPLEMENTATION_REPORT.md` | Library source file | Describes a local 16.2.18 PLM slice, not an integrated or released installer. |

The workspace's own `release/STATUS.json` records `WORKSPACE_REVIEW_ONLY_NOT_RELEASED`, `runtime_code_added=false`, `schema_contract_state=PROPOSED_NOT_RUNTIME_INTEGRATED`, and states that application, database, Codespaces, GitHub commit/PR and production deployment were not run from that workspace.

**Checks rerun in this session:**
- JSON parsing: **37/37 files passed**.
- Included JSON Schema Draft 2020-12 registry suite: **5/5 tests passed**.
- Included workspace SHA-256 manifest: **all listed entries passed**.
- The five tests validate registry count, JSON Schema meta-schema compliance, unique IDs, required properties, top-level object shape and `additionalProperties: false`. They do not validate live Laravel requests, database migrations, PostgreSQL RLS, business payload fixtures, Angular behavior, or production use.

All schema IDs use the reserved `schemas.stockland.invalid` namespace. They are review contracts, not published API endpoints.

## 3. Reuse decision: business rules yes, old runtime code no

### Reusable engineering patterns

1. **Version-controlled product definition:** a tech pack pins a style revision, measurement specification, size grade, pattern revision, BOM and operation routing. Approved snapshots are immutable and superseded by a new revision rather than silently edited.
2. **Sample/fit controls:** sample request → sample round → fit session → measurement observations → fit decision. Preserve the actual measured value, unit, nominal value, tolerance, issue/severity and corrective action.
3. **Material and production genealogy:** identify fabric rolls and trim lots separately; bind material consumption and cutting bundles to production orders and operation events.
4. **Quality at source:** inspection evidence, defect classification, containment, non-conformance, CAPA, independent effectiveness review and release decision.
5. **Warehouse and channel discipline:** carton/pack specification, advance shipping notice, explicit pick/short-pick reasons, source-channel SKU alias resolution, unknown-code quarantine and controlled conversion to an ERP order.
6. **Module optionality:** an outsourced brand, wholesaler or retailer must not be forced to enable factory MES/SFC. Manufacturing capabilities can be enabled when the customer's operating model requires them.
7. **Control invariants:** idempotent commands, maker-checker, immutable decisions, tenant isolation, auditable state transitions and a single authoritative inventory ledger.

### Explicitly not reusable

Do not copy the legacy patch's `stockland-erp-app/includes/**` PHP classes, WordPress REST registration, plugin activation hooks, `$wpdb` gateway, `dbDelta`, WordPress role checks, MySQL DDL or Bootstrap/PWA assets. Do not import the old 16.2.18 WordPress installer as an installer for the Laravel/Angular application. Reimplement required behavior through Laravel domain services/controllers, PostgreSQL migrations/RLS and Angular role-specific workflows.

## 4. Crosswalk to existing greenfield code

| Library contract sample | Existing target implementation | Assessment and remaining work |
|---|---|---|
| `design-brief`, `design-review`, `design-asset` | `ProductDevelopmentController`; migrations `000006`; document metadata in WP-15 | Product brief/style foundation exists. Verify asset lifecycle, document custody and approved review-to-style transition end to end. |
| `tech-pack`, `bom-material-line` | `BomTechnicalPackController`, `BomTechnicalPackService`; migration `000012` | BOM/tech-pack version and approval foundation exists. Reconcile every required schema field to API validation and DB constraints; verify immutable release manifests, substitution approval and idempotent replay. |
| `measurement-spec`, `size-grade`, `pattern-grade`, `pattern-piece` | `MeasurementPatternController`; migration `000006`; linked constraints in `000009` and `000012` | Measurement/pattern source exists. CAD interchange, graded pattern-piece completeness and file/attachment handling remain acceptance items. |
| `sample-fit-session` | `SampleFitController`; migration `000009` (`sample_requests`, `sample_rounds`, `fit_sessions`, `fit_observations`, `fit_decisions`) | Core sample/fit lifecycle exists. Validate measurement/tolerance boundaries, fit decision separation, revision pinning and complete UI journey. |
| `marker-plan`, `lay-request`, `cutting-defect-map` | Departmental manufacturing design; partial cutting/bundle structures in migration `000016` | **Not complete.** Full marker planning/optimization, lay/spread execution, fabric utilization/yield reconciliation and defect-map acceptance need implementation and tests. |
| `bundle`, `production-order`, `operation-event`, `subcontract-order` | `ManufacturingExecutionService`; migration `000016` (`process_route_*`, `production_orders`, `production_order_operations`, `production_execution_events`, `cutting_bundles`, `bundle_operation_events`) | Route/MES and bundle-event foundations exist. Full sewing/printing/embroidery/washing, subcontract lifecycle, replay/concurrency and shop-floor device E2E remain open. |
| `quality-inspection`, `quality-capa` | `QualityControlService`; migration `000017` | Inspection, non-conformance, CAPA and release gate foundations exist. Sampling/AQL/customer quality-plan configuration and regression/UAT evidence remain open. |
| `material-roll-lot` | `SourcingService`; migration `000015`; receipt genealogy/immutability migrations `000022`–`000024` | Separate material lots, fabric rolls and trim lots exist. AP/three-way match, complete receiving/inspection/put-away and material consumption/yield reconciliation remain open. |
| `carton`, `pack-spec`, `advance-shipping-notice` | `FinishedGoodsService`; migration `000018` and finished-goods migrations `000023`–`000024` | Customer orders, reservations, shipments and finished-goods ledger foundations exist. Cartonization, assortment/packing verification, ASN and carrier/partner workflows are not accepted as complete. |
| `cost-sheet` | `FinanceControlService`; migration `000020` | Journal and payroll-draft controls are a finance foundation. Style/order actual costing, labor/overhead allocation, subcontract cost and variance reconciliation remain missing or unaccepted. |
| `event-envelope` | `IntegrationOutboxService`; `DocumentIntegrationService`; migration `000021` | Outbox, document and device scan intake foundations exist. A production worker, external connector contracts, retry/dead-letter operational monitoring and end-to-end delivery remain open. |

This crosswalk is based on source path and migration inspection at the target baseline SHA above. File presence is not feature acceptance.

## 5. Verification gap exposed by the crosswalk

The target branch's current backend feature-test tree contains tenant, initiative/merchandising, platform and identity tests, but no dedicated Laravel feature suites for:
- BOM/tech-pack release and material substitution;
- sample request/round/fit measurement/decision;
- manufacturing route, bundle genealogy, MES output, downtime and replay;
- inspection, non-conformance, CAPA and quality release;
- material receiving/roll/trim genealogy and finished-goods carton/ASN journeys;
- device-scan replay/resolution and outbox delivery/retry.

The frontend contains a product-development service test, but the full cross-role browser journeys for PLM, shop-floor, QC, warehouse and channel fulfilment are not evidenced by the current test inventory.

**Solution:** write database-backed PostgreSQL feature tests against the restricted `app_runtime NOBYPASSRLS` connection and dedicated fixture-admin connection before treating these existing domain foundations as accepted. Add negative cross-tenant tests, role/record-scope denial tests, maker-checker tests, idempotency/replay tests, concurrent duplicate-post tests, ledger/quantity reconciliation and Angular journey tests. Keep SQLite as non-equivalent to PostgreSQL RLS verification.

## 6. Recommended implementation order

1. Reconcile the 25 reference schemas against WP-01 requirements and the existing Laravel request/DB contracts. Keep the original files as reference only until the field mapping is approved by source/test evidence.
2. Add test suites for current PLM/BOM/sample-fit foundations; fix and rerun before changing their API/schema.
3. Add missing cutting/marker/lay/bundle genealogy as a dedicated WP-09 slice; pin every order to approved style, BOM, tech-pack and route revisions.
4. Add MES/SFC and device-scan replay/concurrency acceptance; do not create a second stock ledger.
5. Add inspection-plan/sample rules and full CAPA-to-release tests before claiming WP-11 acceptance.
6. Add cartons/packing/ASN/channel staging and returns with explicit reconciliation to the existing finished-goods and inventory ledger.
7. Add actual production costing only after material, labor, operation and subcontract events are authoritative and reconciled.

## 7. Scope and release boundary

This commit records a library audit and source-to-target crosswalk. It does not import or activate legacy WordPress code, add production features, alter database schema, seed business data, run migrations, use Codespaces, change secrets, or deploy. The exact runtime test matrix remains the responsibility of the Laravel/Angular repository CI and later full WP-16 acceptance.

**No production installer, full ERP completion, security certification, statutory approval or production deployment is claimed.**
