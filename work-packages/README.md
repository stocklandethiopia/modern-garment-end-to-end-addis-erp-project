# WP-00–WP-16 source and implementation map

This directory is the **work-package index and evidence map**, not a second application source tree. Canonical runtime code remains in `backend/`, `frontend/`, `backend/database/migrations/`, `backend/tests/` and the corresponding CI workflows. The maps point to exact paths and identify unmerged candidate branches separately; they do not duplicate source code or imply completion.

## Work-package index
| WP | Map | Current disposition |
|---|---|---|
| 00 | `wp-00-governance-and-delivery-control/SOURCE_MAP.md` | Owner-accepted governance; maintain the sequence and evidence |
| 01 | `wp-01-requirements-traceability/SOURCE_MAP.md` | R3 source package verified; canonical implementation trace remains open |
| 02 | `wp-02-ethiopia-legal-localization/SOURCE_MAP.md` | Source register/design exists; qualified approval and effective-dated activation remain open |
| 03 | `wp-03-enterprise-saas-identity-organization/SOURCE_MAP.md` | Partial canonical foundation; platform/org candidates require reconciliation and exact-head acceptance |
| 04 | `wp-04-initiative-merchandising-range-planning/SOURCE_MAP.md` | Partial canonical workflow and tests |
| 05 | `wp-05-plm-design-studio-product-development/SOURCE_MAP.md` | Partial PLM/BOM/sample foundation |
| 06 | `wp-06-industrial-engineering-capacity-readiness/SOURCE_MAP.md` | IE foundation; full study/line balance/readiness evidence open |
| 07 | `wp-07-sourcing-procurement-material-traceability/SOURCE_MAP.md` | Partial sourcing/traceability foundation |
| 08 | `wp-08-route-sop-work-instruction-framework/SOURCE_MAP.md` | Route/MES foundation; route/SOP graph and exception gates open |
| 09 | `wp-09-departmental-manufacturing-execution/SOURCE_MAP.md` | Partial MES; full departmental WIP replay and genealogy open |
| 10 | `wp-10-production-planning-workforce-skills/SOURCE_MAP.md` | Partial IE/workforce; plan/actual and skill deployment open |
| 11 | `wp-11-quality-compliance-capa-hse/SOURCE_MAP.md` | Quality/CAPA foundation; full buyer QC/compliance/HSE open |
| 12 | `wp-12-warehouse-distribution-retail-commerce/SOURCE_MAP.md` | Finished-goods and fulfilment foundations; WMS/POS/channel commerce open |
| 13 | `wp-13-finance-accounting-costing-treasury-tax/SOURCE_MAP.md` | Finance posting foundation; costing/treasury/tax/FX open |
| 14 | `wp-14-people-payroll-benefits-compensation/SOURCE_MAP.md` | Payroll review foundation; full HR lifecycle and legally approved runtime open |
| 15 | `wp-15-analytics-integrations-agent-governance/SOURCE_MAP.md` | Outbox/device/agent foundations; connector/KPI/event governance open |
| 16 | `wp-16-end-to-end-verification-release/SOURCE_MAP.md` | CI component gates exist; full E2E, recovery, performance and release gates open |

## Source custody and architecture boundary
- Collected material is preserved under `source-intake/stockland-garment-erp-local-v0.3.0/`; its manifest/provenance and known limitations are described in `docs/audit/source-intake-to-wp-disposition-register-2026-11-28.md`.
- The Stockland v0.3.0 package is a Node.js/SQLite local application with a 45-table schema. It is source/reference material to analyze and port; it is not a replacement for the approved Angular/Laravel/PostgreSQL SaaS architecture.
- The X POS reference collection is a small, licensed selection of six source modules/tests. It is not a complete Stockland POS, WMS or manufacturing application.
- Never copy a source screen or CRUD table and call it a completed WP. Implement business controls, authorization, durable records, ledger effects, audit, failure handling and tests in the canonical application.
- Candidate branches and PRs remain candidates until their exact source, migrations, dependencies, tests and merge order have been reconciled against the current `delivery` head.

## Package acceptance
Every WP map must be completed with: source path and provenance, target code/schema/UI/API paths, requirement IDs, normal/negative/partial/retry/concurrency/reversal scenarios as applicable, exact-SHA CI evidence, operational documents, known legal/professional gates and handover status. A workflow that did not execute the relevant job is NOT RUN, not PASS. A passing component build does not equal WP acceptance or production approval.

**Canonical integration branch:** `delivery/autonomous-wp-01-16`.  
**Production promotion branch:** `main` only after WP-16 and accountable release authorization.  


## Detailed legacy table migration map
The complete 45-table legacy SQLite-to-canonical PostgreSQL disposition, including source column inventory, target WP, existing schema equivalents, missing subledgers and data-migration controls, is recorded in `docs/audit/stockland-v0.3-45-table-port-disposition-2026-11-28.md`. Do not create or import a generic 45-table clone; implement the decomposed domain models and acceptance gates in the WP maps.
