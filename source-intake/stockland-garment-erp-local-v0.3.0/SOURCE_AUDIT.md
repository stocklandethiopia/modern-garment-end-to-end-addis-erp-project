# Source audit — Stockland Garment ERP local source v0.3.0

**Audit state:** INTAKE / STRUCTURAL REVIEW COMPLETE / ORIGINAL ZIP UPLOAD PENDING / AUTOMATED CI PENDING  
**Package:** `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip`  
**Submitted:** 23 November 2026  
**Canonical repository:** `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`  
**Intake branch:** `audit/stockland-local-source-v0.3.0`

## 1. Package identity and integrity

| Item | Finding |
|---|---|
| Source package declares | Stockland Garment ERP — Full-Stack Local Application |
| Package version | 0.3.0 |
| Application runtime | Node.js native HTTP server, browser HTML/CSS/JavaScript, built-in SQLite |
| Declared Node engine | `>=22.5.0` |
| Package dependencies | Runtime described as native Node modules; no dependency installation required |
| Archive inventory | 107 files; 497,599 uncompressed bytes |
| Internal manifest | 106 tracked file entries plus `BUILD_MANIFEST.json` |
| Manifest verification | PASS — all tracked paths, sizes and SHA-256 digests matched the supplied manifest during intake |
| Archive checksum | PASS — SHA-256 `5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1` |
| Build manifest checksum | PASS — SHA-256 `0d7c03dac0be52df3a4699fc9db93b4da8b45cb853980dcc0a62a598568722c0` |
| Secrets / live DB scan | No `.env`, obvious secret-key file or SQLite database file found in the archive inventory |
| ZIP structure | PASS — no absolute or parent-traversal archive paths found |
| Source provenance | Six selected X POS reference files are isolated in a separate MIT reference kit with licence and source manifest; reuse still requires file-by-file provenance/licence review |

The ZIP is retained without editing. The archive's own `docs/VERIFICATION_REPORT.md` reports 15 tests, 14 workflow scenarios and local static/release checks on Node 22.16.0. Those are **author-supplied claims**, not independent test results. GitHub Actions is being used to independently execute the package's declared quality commands.

## 2. Architectural comparison with the approved baseline

The canonical product documents define a customer-neutral, configurable, multi-tenant apparel operating system. The approved architecture is Angular 22 + Laravel 13 + PostgreSQL 18, with Redis for queues/cache and Python only for specialist compute.

The submitted v0.3.0 package is a different application profile: a Stockland-branded, single-instance local Node.js/SQLite application. It has practical retail, warehouse, procurement, wholesale, finance and HR workflows, but it is not an implementation of the canonical architecture and its own README hard-codes Stockland company locations.

**Decision:** keep this package as an isolated audit/reference intake. Do not replace or merge it into the canonical `backend/` or `frontend/` application. Any decision to port selected business workflows or change the approved architecture requires explicit Product Owner change control under WP-00.

## 3. Functional coverage against the WP-00…WP-16 plan

The statuses below distinguish present partial capabilities from full work-package acceptance. A screen, table or basic transaction does not satisfy a complete work-package definition of done.

| Work package | Coverage found in submitted app | Assessment against canonical plan |
|---|---|---|
| WP-00 Governance | Package README, local technical docs and release notes | PARTIAL. Does not replace the canonical governance agreement, approval history or owner handover gate |
| WP-01 Requirements & traceability | `REQUIREMENTS_TRACEABILITY.csv` with 30 rows | GAP. Not a complete domain/role/workflow/data/control/evidence/acceptance/owner traceability map for the approved product |
| WP-02 Ethiopia legal-source verification | Limited application documentation only | GAP. No complete authority-by-authority, versioned/effective-dated legal rules library, primary-source evidence and qualified activation approvals |
| WP-03 Enterprise/SaaS foundation | Local first-owner setup, seven roles, location assignment | GAP. Fixed Stockland organisation and local SQLite boundary; no canonical tenant isolation, tenant entitlements, legal entity/site model, forced PostgreSQL RLS or SaaS lifecycle |
| WP-04 Initiative, brand, range & merchandising | Product master, supplier/customer profiles, PO and wholesale | PARTIAL. Lacks initiative-first market insight, brand/collection/season/range/assortment governance and complete buyer/account commercial planning |
| WP-05 Design studio & PLM | Product master and basic item data | GAP. No complete design studio, style revisions, CAD/2D/3D workflow, pattern/grading, sample/fit, tech pack, BOM, costing, asset rights and immutable release chain |
| WP-06 IE & pre-production | No complete evidence identified | GAP. No approved method/time studies, SMV/SAM, operation bulletins, machine/skill needs, line balancing, capacity or readiness control |
| WP-07 Procurement & material traceability | Supplier master, purchase orders, partial receipt and stock receipts | PARTIAL. Lacks garment-grade fabric roll/lot/shade genealogy, trim lots, inspection disposition, supplier qualification, separate fabric/trim issue-return chains and controlled import/FX evidence |
| WP-08 SOP and route framework | General workflow documentation | GAP. No configurable, versioned product/process route and operation-level SOP/work-instruction engine |
| WP-09 Manufacturing execution | No complete manufacturing execution modules identified | GAP. No departmental cutting/spreading/marker/bundling, sewing, special processing, finishing, packing, subcontract processing and WIP traceability |
| WP-10 Production planning & workforce capability | Employee, attendance and payroll records | PARTIAL. Lacks production line/shift/day/hour plan-vs-actual, SMV-based efficiency, skill matrix, certified deployment, bottleneck and continuous improvement controls |
| WP-11 Quality, compliance & HSE | Basic returns/damage handling | GAP. No complete incoming, first-piece, inline, end-line, final/shipment inspection, CAPA, restricted chemistry, environmental or worker-safety evidence |
| WP-12 Warehouse, distribution & commerce | Inventory ledger, transfers, requisitions, reservations, POS, returns, wholesale | PARTIAL. Some core transactions exist, but not the complete network/DC topology, ATP/fulfilment, reseller, e-commerce, transport, reverse logistics and intercompany custody model |
| WP-13 Finance, accounting, treasury & tax | Basic GL, AP, AR, expenses, payment and trial-balance functions | PARTIAL. Not complete cost accounting, inventory valuation, budgeting, treasury, tax/VAT/withholding, FX, customs, intercompany and statutory reporting |
| WP-14 HR/payroll/benefits/compensation | Employee, contract, attendance and basic payroll | PARTIAL. No full workforce lifecycle, effective-dated legal rules, benefits/pension, learning/skills/HSE, or separate production/store/wholesale/creator/reseller/supplier compensation classifications |
| WP-15 Analytics/integrations/intelligent operations | Operational reports | PARTIAL. No full role-based BI catalogue, event/outbox integration, connector registry, optimization and human-governed AI workflow |
| WP-16 End-to-end verification & release | Local test suite and package release gate are included | PENDING. Author's local test report is not production acceptance; independent CI, security, tenant-isolation, performance, backup/restore, staging/UAT and deploy/rollback evidence remain required |

## 4. Security and operational limitations requiring deeper review

The included security model documents password hashing, sessions, CSRF, role/location checks and append-only inventory/audit history. These are useful local-application controls. They do not demonstrate the approved SaaS requirements for database-enforced tenant isolation, legal-entity/site/brand scopes, PostgreSQL row-level security, restricted database roles, tenant-aware composite foreign keys, MFA/SSO readiness, central audit and per-tenant operational controls.

Further independent review is still required for:
- Authentication/session lifecycle, cookie flags, password reset/rotation and brute-force controls.
- CSRF coverage for every mutation route and CORS/origin behavior.
- Every API route's authentication, role and location authorization.
- SQL parameterization, user-controlled file parsing, CSV/formula injection, output encoding and CSP.
- Transaction boundaries, idempotency, concurrent stock allocation and replay/reversal behavior.
- Database backup/restore, schema migration forward/recovery and operational data retention.
- Logging/redaction, PII handling, audit immutability and least privilege.
- Accessibility, browser compatibility, deployment hardening and monitoring.

No claim of independent penetration test, statutory compliance sign-off or production readiness is made.

## 5. GitHub-hosted verification to run

The root workflow `.github/workflows/source-intake-audit.yml` is dedicated to this isolated intake. After the original ZIP is uploaded at the exact path in the intake README, it will:
1. Verify the original ZIP committed unchanged at `source-intake/stockland-garment-erp-local-v0.3.0/STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip`.
2. Verify the archive SHA-256 against the supplied checksum.
3. Extract into an ephemeral GitHub-hosted runner directory.
4. Run `npm run check`, `npm test`, `npm run verify:manifest` and `npm run release:gate` with Node 22.16.0.
5. Upload the expanded original source as a downloadable GitHub Actions artifact, whether the checks pass or fail.

A green run confirms only that the submitted local application passes its own package checks. It does **not** satisfy the canonical ERP's WP-00…WP-16 acceptance gates.

## 6. Release and integration disposition

**Release disposition: BLOCKED / NOT APPROVED.**

Reasons:
- Architecture and tenant model do not match the canonical approved baseline.
- Scope coverage is partial across the complete WP-00…WP-16 product.
- Independent GitHub-hosted checks are pending.
- Deeper code/security and business-control review remains pending.
- No Product Owner change approval exists to replace the canonical architecture or merge a parallel local app into the runtime.

**Safe next step:** complete source-intake CI and review its actual logs, then decide—under explicit owner change control—whether selected workflows should inform or be ported into the canonical application. Preserve this original archive and its checksums regardless of that decision.

No production deployment, release tag, merge to `main`, live data migration or statutory-rule activation has been performed.
