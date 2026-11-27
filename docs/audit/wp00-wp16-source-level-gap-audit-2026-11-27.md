# WP-00 to WP-16 — Source-Level Gap Audit and Implementation Checklist

**Audit date:** 27 November 2026  
**Repository:** stocklandethiopia/modern-garment-end-to-end-addis-erp-project  
**Audited canonical integration branch:** delivery/autonomous-wp-01-16  
**Canonical delivery head inspected:** fc01eced62b97c4ec27f0075264fc8ba0fb11851  
**Default branch inspected:** main at adca71a053290eb768987a7644d4b13c267cf2a9  
**Purpose:** identify the source, data, workflow, control and verification work required to complete the canonical production ERP and reproducible master installer.

**Controlling execution register:** [Production Execution Plan — WP-00 to WP-16](../work-packages/production-execution-plan-wp00-wp16.md). Use its PROD-WPnn-nnn task IDs alongside the approved GAR/AT/requirement IDs; this audit records source findings and is not a substitute for the task plan.

---

## 1. Executive conclusion

The repository contains a substantial greenfield ERP platform foundation and bounded application slices across several apparel domains. It does **not** yet contain the complete, integrated garment ERP specified by WP-00 through WP-16.

The important distinction is between:

1. **Design or reference evidence** — approved plans, source archives, legal registers, schemas and architecture documents.
2. **Executable source** — Laravel/Angular code and PostgreSQL migrations that have been committed to a branch.
3. **Integrated implementation** — executable source merged into the canonical delivery branch and connected to its upstream/downstream workflows.
4. **Verified implementation** — exact-source-SHA tests prove the intended acceptance scenario.
5. **Release-ready implementation** — end-to-end, security, load, recovery, UAT, operational and legally required gates have passed.

A design document, migration, controller, unit test, green generic CI run or generated source archive does not by itself prove a complete business workflow. This audit uses that distinction throughout.

### WP-00–WP-16 implementation baseline at the inspected delivery head

| Work package | Source-backed implementation found | Next build focus |
|---|---|---|
| WP-00 Governance | Owner-approved scope, autonomous delivery delegation and the 27 November production-build/workflow amendment are recorded in the governance agreement | Keep every increment tied to approved scope, exact source, evidence and downstream dependencies |
| WP-01 Requirements | R3 package integrity evidence: 128 requirement records, 24 contexts, 45 source-map rows and package tests | Complete the semantic crosswalk to legacy GAR identifiers and map every requirement to code, API/UI, migration, test and handover |
| WP-02 Ethiopia legal foundation | Ethiopia localization standard and authority/source register with explicit unresolved interpretation points | Retain and hash source bytes, reconcile amendments/effective dates and obtain qualified review for statutory rules |
| WP-03 Enterprise/SaaS | Integrated tenant/identity foundation plus an unmerged 39-commit organization-structure runtime candidate (migration 000035, four org tables, Laravel/Angular source and PostgreSQL tests) | Integrate operator-grant/scope hardening, organization runtime, incumbent-to-position assignments, position-based DoA, target-resource authorization, expiry/recovery and SSO/MFA |
| WP-04 Merchandising | Initiative/market insight, brand/collection/range/assortment, forecast/OTB/pricing and account-term service slices | Complete full commercial lifecycle, channel actuals, downstream reconciliation and domain acceptance coverage |
| WP-05 Product development | Product briefs/styles/revisions, measurement/pattern/sample-fit, BOM/technical-pack approvals and release-manifest foundations | Resolve the BOM material-verification dead-end, substitution approvals, verified object assets and dynamic cross-domain release gates |
| WP-06 Industrial engineering | Operation standards, work centres, shifts, capacity slots, plan and workforce-skill/assignment foundations | Enforce operation quantity allocation, site binding, skill scope, worker overlap and calibrated factory planning |
| WP-07 Sourcing/procurement | Supplier/PO/receipt/material-lot service slices and inventory-ledger foundations | Complete RFQ/quote/award, qualification evidence, receipt aggregation, service/subcontract acceptance and AP handoff |
| WP-08 Route/SOP management | Versioned route and step records with approval and production-order linkage | Make route conditions executable and integrate approved SOP/work instructions, deviations and route genealogy |
| WP-09 Material issue and production genealogy | Material receipt/lot, inventory movement, order/operation event and cutting-bundle schema foundations | Implement cutting/lay/bundle service, separate issue/return chains, WIP custody and full roll-to-garment genealogy |
| WP-10 Manufacturing execution/planning | Production order, route events, execution queues and initial output/downtime/skill/capacity records | Complete department-specific MES, scan/offline execution, hourly performance, WIP control and concurrency recovery |
| WP-11 Quality/compliance | Inspection, review, material/operation release, nonconformance, corrective-action and effectiveness service slices | Correct submission/review state, linked deviation approvals, NCR/CAPA atomicity, HSE and end-to-end hold enforcement |
| WP-12 Distribution/commerce | Finished-goods lots/locations/reservations, customer-order, credit-decision and dispatch foundations | Complete allocation/WMS, channels, pick-pack-ship, returns/RMA, delivery integration and sales/stock reconciliation |
| WP-13 Finance/treasury | Finance periods, journal drafts/decisions/posting, customer credit and payroll-review foundations | Freeze approved journal lines, implement reversals, subledgers, AP/AR, costing, FX, bank reconciliation and close |
| WP-14 People/payroll | Canonical payroll-review foundation plus an unmerged 57-commit Ethiopia payroll candidate (calculator, Finance integration, evidence migrations and unit tests); activation flags remain false | Reconcile migration ordering/storage evidence, add API/RLS/concurrency and payroll-to-GL/remittance tests, then build employee, contract, assignment, time, leave, benefits and HR workflows |
| WP-15 Documents/integrations/agents | Document metadata/upload/extraction records, outbox transitions, device scans and agent proposal/approval controls | Complete provider-neutral object storage, verified extraction/import, workers/adapters, device identity, agent runtime and human approval UI |
| WP-16 Quality/installer/release | Component CI, local dependency Compose, Make targets, version pins and release engineering standards | Build the reproducible master installer/application images, worker/scheduler topology and full security/E2E/performance/recovery/UAT evidence |


No overall completion percentage is assigned. The repository does not provide a defensible denominator of independently accepted requirements: WP-01 R3 still needs semantic reconciliation, and the later work-package source folders and evidence matrices are incomplete.

---

## 2. Repository and branch reality

### 2.1 Default branch versus canonical delivery

- The default branch is **main**, SHA **adca71a053290eb768987a7644d4b13c267cf2a9**. It is a small documentation/governance scaffold with 18 tracked files and no executable Laravel/Angular ERP application.
- The working integration branch is **delivery/autonomous-wp-01-16**, SHA **fc01eced62b97c4ec27f0075264fc8ba0fb11851**. It contains approximately 300 tracked files, Laravel 13, Angular 22, PostgreSQL migrations through 000032 and the current executable domain foundations.
- The delivery branch is 813 commits ahead of main. Its first executable platform foundation is still carried by open draft PR #1, with 290 changed files. It has not been merged to main.
- Consequently, cloning main alone does not obtain the current executable ERP foundation. Delivery is a separate integration line, not proof of a production release.

### 2.2 Existing source structure

The delivery branch currently has the following major source groups:

| Source area | What is present |
|---|---|
| backend | Laravel application, controllers, services, routes, configuration, tests and migrations |
| frontend | Angular application with auth, enterprise, merchandising and product-development feature roots |
| database | 33 numbered migrations, including initial foundation and 000001–000032 |
| docs | Architecture, product, engineering, security, HR, localization, verification and release standards |
| work-packages | WP-01 R3 source package and its integrity material; no equivalent source-package folder for WP-02 through WP-16 |
| .github | ERP CI and combined candidate-verification workflows |
| docker / root tooling | PostgreSQL role initialization, docker-compose for local PostgreSQL/Redis infrastructure and a Makefile |

The presence of controllers/services for a domain is not equal to a complete Angular workflow, a full set of domain acceptance tests, operational integrations or production handover.

### 2.3 Migration sequence currently integrated

The current delivery sequence contains migrations for:

- 000001 enterprise foundation;
- 000002 personal access tokens;
- 000003 licence evidence;
- 000004 initiative and market insights;
- 000005 brand, range and assortment planning;
- 000006 product development/style;
- 000007 measurement, pattern and grading;
- 000008 evidence requirements;
- 000009 sample/fit and scoped access;
- 000010 scope verifier;
- 000011 assortment terms;
- 000012 BOM/technical-pack controlled releases;
- 000013 secure tenant membership lifecycle;
- 000014 tenant invitations;
- 000015 sourcing and material traceability;
- 000016 manufacturing routes and execution;
- 000017 quality controls;
- 000018 distribution/order fulfilment;
- 000019 industrial engineering/capacity/workforce controls;
- 000020 finance and payroll review foundation;
- 000021 documents, integrations and agent governance;
- 000022 receipt traceability/immutability;
- 000023 finished-goods inventory/reservations;
- 000024 finished-goods location and append-only controls;
- 000025 finance posting and agent uniqueness;
- 000026 customer credit decisions;
- 000027 tenant domain-role assignments;
- 000028 role revocation on membership change;
- 000029 non-overlapping role periods;
- 000030 locked tenant membership reader;
- 000031 tenant owner succession/exit;
- 000032 platform entitlements and tenant lifecycle.

These migrations are a real foundation. They do not yet demonstrate all of the later commercial, shop-floor, financial, HR, integration and operational control requirements.

---

## 3. WP-00 to WP-16 detailed gap register

### WP-00 — Governance and delivery control

**Current state: accepted governance baseline.**

The repository records the Product Owner's governance and autonomous WP-01–WP-16 delivery direction. The fixed package sequence, customer-neutral greenfield boundary and the distinction between reference systems and new application architecture are established in governance/product documents.

**Evidence locations**
- docs/agent/project-governance-agreement.md
- docs/product/apparel-business-operating-system-charter.md
- docs/architecture/platform-standard.md
- docs/engineering/code-quality.md
- docs/engineering/development-deployment.md

**Remaining actions**
- Preserve WP-00 as the governing standard and do not redefine completion based on the number of merged files or commits.
- Require every promoted increment to carry its source SHA, requirement IDs, code paths, tests, evidence, unresolved defects and explicit release disposition.
- Keep production deployment, statutory activation, external payment/bank execution and destructive production operations behind the separately required human/operator approvals.

### WP-01 — Requirements inventory and traceability

**Current state: source package integrity verified; canonical implementation and crosswalk remain open.**

The R3 package has 128 requirement records, 24 contexts, 45 source-map rows and 20 package tests. The embedded source archive SHA-256 is:

44f69b36d6ed389633d4e305dacfa825fbdffb99d78cc903e3f050761811a6e7

The current visible requirements matrix contains 120 legacy GAR-* identifiers across 13 prefixes. R3 uses a different 24-prefix namespace, including PLT, ORG, IAM, CRM, INI, PLM, CST, PRC, MRP, INV, IEP, MFG, QLT, DST, OMS, LOG, FIN, HRM, PAY, LND, PER, MNT, REG and DWR. There is no direct identifier equality. The numeric difference of eight does not establish that exactly eight requirements are missing.

R3's 128 implementation status values are all **not_started**. The source validator passing proves the package is readable and internally consistent; it does not prove those requirements have been implemented.

**Evidence locations**
- docs/requirements/requirements-traceability.md
- work-packages/wp-01-requirements-traceability/README.md
- work-packages/wp-01-requirements-traceability/REPOSITORY_IMPORT_MANIFEST.md
- work-packages/wp-01-requirements-traceability/source/
- docs/requirements/wp01-r3-reconciliation-2026-11-27.md on PR #17

**Remaining actions**
- Build a semantic crosswalk between each R3 requirement and legacy GAR-* requirement(s), including one-to-many and many-to-many links.
- For each mapping capture exact source, package owner, implementation file(s), migration/table(s), API/UI path, test ID, exact tested SHA, result, dependency, exception and approval.
- Record genuinely new requirements, superseded requirements, split/combined requirements and unmapped requirements; do not silently drop or renumber records.
- Replace ambiguous “complete” labels with separate design/source/integration/verification/acceptance states.
- Make all 128 R3 records traceable to the implementation backlog and WP acceptance ledger.

**Exit criteria:** all 128 records reconciled and assigned; no orphan requirement; all claimed implemented requirements have exact source and passing scenario evidence.

### WP-02 — Ethiopia legal, regulatory and localization foundation

**Current state: internally accepted source register; not a complete legal activation basis.**

The current delivery contains Ethiopian localization/legal-source documents and a regulatory register. Those documents identify applicable instruments and legal interpretation questions. An internal source-register acceptance is not a legal opinion and does not authorize a statutory rate or worker classification to be activated.

**Evidence locations**
- docs/localization/ethiopia-legal-regulatory-register.md
- docs/localization/ethiopia.md
- docs/hr/payroll-and-variable-compensation.md

**Remaining actions**
- Retain actual authoritative legal source documents, not merely URLs or summaries, in controlled evidence storage.
- Hash the exact document bytes and bind each legal source to its authority, title, instrument number, gazette/publication date, effective dates, exact article references, language/version and review record.
- Reconcile amendments, transition/effective dates, regulations and relevant directives.
- Have qualified Ethiopian professionals decide unresolved employment, tax, pension, exemption, withholding, contract and worker-classification questions.
- Make every legal rule effective-dated, scope-aware, independently approved and historically reproducible.
- Keep uncertain classification/rule outcomes in REVIEW_REQUIRED/HOLD rather than inventing a rate or inferring exemption from a component label.

**Exit criteria:** a professionally reviewed source register and controlled legal-evidence process; each live rule has explicit applicability and approval. This package does not itself activate payroll or other statutory execution.

### WP-03 — Enterprise, tenant, identity, authorization and platform administration

**Current state: useful integrated foundation; requirement acceptance remains partial.**

**Integrated source areas**
- backend/app/Http/Controllers/Enterprise/
- backend/app/Services/TenantProvisioningService.php
- backend/app/Services/TenantMembershipService.php
- backend/app/Services/TenantInvitationService.php
- backend/app/Services/ScopedAccessService.php
- backend/app/Services/DomainCapabilityAuthorizer.php
- backend/app/Services/TenantDomainRoleAssignmentService.php
- backend/database/migrations/000001, 000009, 000013–000014 and 000027–000032
- backend/tests/Feature/TenantProvisioningServiceTest.php
- backend/tests/Feature/TenantIsolationTest.php
- backend/tests/Feature/TenantMembershipLifecycleTest.php
- backend/tests/Feature/TenantInvitationLifecycleTest.php
- backend/tests/Feature/DomainRlsSecurityTest.php
- backend/tests/Feature/PlatformAdministrationTest.php

The integrated baseline includes tenant provisioning/context, tenant memberships and invitations, legal entities/sites/locations, licensing evidence, scoped access foundations, domain-role assignment/effective dates/revocation, owner succession/voluntary exit, platform tenant lifecycle, entitlements and platform audit/register foundations.

**Remaining gaps**
- Scope grants are not complete target-resource authorization. Current scope types include BRAND/SITE/MARKET, while legal entity, department, warehouse and channel scope identities/checks remain open.
- DomainCapabilityAuthorizer deliberately denies SCOPED members until concrete target-record checks exist. Preserve this fail-closed behavior.
- Platform operator grant lifecycle, operator workbench and associated hardening on PR #13 are not in the canonical branch.
- Entitlement state exists, but every feature/module must enforce a defined, approved entitlement and quota policy.
- Export/archive, retention, legal hold, data-subject handling and independently approved recovery remain incomplete.
- MFA, enterprise OIDC/SSO, session recovery/rotation and stronger authentication lifecycle remain open.
- Invitation/domain-role/ownership and full operator/bootstrap interfaces need completion.
- A real company organogram and position-based approval authority are not the same as tenant OWNER/ADMIN or a generic “Owner” label.

**WP-03 candidate PR #13**
- Branch: work/wp03-completion-2026-11-27
- Head: 8ae8536bccfd3924017b12905ea57fce2641fb46
- 77 commits; 33 changed files; 2,779 additions / 246 deletions
- Includes migrations 000033 and 000034, platform operator-grant API/service, secure scope mutation functions, negative security tests and Angular platform screens.
- The candidate is 15 commits behind current delivery and has not merged. Migration 000034 is intentionally forward-only; any correction should be a new forward migration, not an attempted rollback.
- The candidate keeps SCOPED access fail-closed, and it still identifies missing entity/department/warehouse/channel target authorization, a scheduled non-forgeable expiry worker, retention/export/recovery and other WP-03 controls.

**Remaining actions**
- Rebase/reconcile PR #13 against the current delivery head. Do not merge the older base as-is.
- Review every SECURITY DEFINER function, fixed search_path, grants/revokes, role and actor derivation, audit atomicity, migration up/down behavior and concurrency lock ordering.
- Execute positive and adversarial database tests as the non-owner app_runtime role.
- Implement every required target scope and check it at the resource boundary in every affected domain.
- Finish lifecycle UI, MFA/SSO, entitlement enforcement, expiry worker, export/retention/recovery and full WP-03 acceptance scenarios.

### WP-04 — Initiative, merchandising, range, assortment and commercial planning

**Current state: bounded executable workbench slices; downstream actuals and full commercial workflows remain open.**

**Source areas**
- backend/app/Http/Controllers/Merchandising/
- backend/app/Services/Merchandising/
- frontend/src/app/features/merchandising/
- migrations 000004, 000005 and 000011
- docs/product/apparel-business-operating-system-charter.md

Present slices include initiative/market-insight evidence, brand/season/collection/range/assortment planning, demand/forecast adjustments, open-to-buy scenarios, price lists, promotions and reseller/account terms.

**Remaining actions**
- Complete full scenario coverage AT-MER-001 through AT-MER-014.
- Integrate actual downstream product-development, sourcing, inventory, order, POS, sales, return and profitability events.
- Complete approved wholesale/reseller pricing and account-term controls, version/supersession behavior and customer/channel/legal-entity applicability.
- Reconcile approved forecast/OTB/price decisions to later real orders, purchases, stock and margin.
- Do not treat own-retail simulations as legally activated retail business without the relevant licensing and entity/site/channel evidence.
- AT-MER-008 and other downstream scenarios remain dependent on WP-12 actuals.

### WP-05 — Product development, technical design, sample, fit, BOM and technical pack

**Current state: product-development foundations exist; controlled bulk-ready product passport is not complete.**

**Source areas**
- backend/app/Http/Controllers/ProductDevelopment/
- backend/app/Services/ProductDevelopment/
- frontend/src/app/features/product-development/
- migrations 000006–000009 and 000012
- docs/architecture/application-folder-code-map.md

Present slices include versioned product briefs/style revisions, measurement specifications, pattern/grade controls, sample/fit observations, BOM/technical-pack workflow, substitution review, asset metadata/rights review and release-manifest controls.

**Remaining actions**
- Complete private object storage, upload verification, retrieval integrity, malware scanning, retention and signed asset-rights controls.
- Complete sample request/round/room/size-set/fit sessions, correction loops and sample approval by type.
- Complete product-specific tech pack, construction, measurements/tolerances, care/label/packaging and customer requirement handling.
- Complete material/BOM quantity, consumption/waste, substitute approval, landed-cost and cost-rollup relationships.
- Add engineering change notices, revision comparison, supersession and controlled product release.
- Add CAD/2D/3D providers through neutral adapters; do not claim a CAD capability from asset metadata alone.
- Block bulk release until the approved product revision, BOM, route, material standards, IE standards, quality plan and cost are jointly ready.

The library apparel reconciliation document on PR #14 maps source reference samples to the greenfield architecture and correctly rejects copying the old WordPress/PHP/MySQL runtime or DDL into this Laravel/PostgreSQL product.

### WP-06 — Industrial engineering, time study, SAM/SMV, capacity and workforce skills

**Current state: backend foundations only; factory-calibrated operational use is not proven.**

**Source areas**
- backend/app/Http/Controllers/IndustrialEngineering/
- backend/app/Services/IndustrialEngineering/
- migrations 000019 and related production/workforce foundations
- docs/engineering/industrial-engineering.md

Present slices cover operation standards/time-study and SAM/capacity/workforce-control foundations.

**Remaining actions**
- Complete an Angular IE/time-study/line-balance workspace.
- Add method version, operation bulletin, machine/attachment, skill requirement, allowance, observed-time sample, rating and approval controls.
- Validate data quality and calibrated time studies at actual factories; establish who can approve effective SAM/SMV.
- Implement line-balance/skill coverage, available minutes, break/changeover/absenteeism and maintenance constraints.
- Reconcile planned target, actual output, WIP, downtime and earned minutes at daily and hourly levels.
- Add overlap, version, capacity and concurrent planning tests and execute factory UAT.

### WP-07 — Supplier qualification, sourcing, procurement, receipt and material traceability

**Current state: sourcing/receipt/lot foundations exist; procurement-to-pay and import/landed-cost chain is incomplete.**

**Source areas**
- backend/app/Http/Controllers/Sourcing/
- backend/app/Services/Sourcing/
- migrations 000015 and 000022
- sourcing/receipt tests are not represented by a dedicated comprehensive feature suite in the current test inventory.

Present slices include supplier qualification, sourcing events/RFQ/PO, receipt, material lots, fabric rolls and trim lots, receipt lineage and append-only inventory event foundations.

**Remaining actions**
- Complete vendor master qualification, sanctions/permit/documents, supplier scorecard and contract lifecycle.
- Complete PO amendments, shipment/import documentation, customs, duty/freight/insurance and landed-cost distribution.
- Complete receipt discrepancies, partial receipt, quarantine, sampling, rejection, return-to-vendor and supplier claims.
- Complete AP invoice matching, three-way match, credit note and payment handoff (finance owns accounting/payment).
- Complete material lot, fabric roll, shade/batch, trim, certificate and test lineage to issue/production/finished goods.
- Add tenant/entity/site authorization, duplicate/retry, concurrency and source-document tests.

### WP-08 — Route versions, SOP and work instructions

**Current state: initial route-version and route-gate foundation.**

**Source areas**
- backend/app/Http/Controllers/Manufacturing/
- backend/app/Services/ManufacturingExecutionService.php
- migrations 000016 and related quality/production foundations

**Remaining actions**
- Complete route-condition/branch engine based on style, material, finish, site, customer and subcontract rules.
- Add versioned operation SOPs, work instructions, images/media, equipment/recipe/parameter requirements and worker acknowledgement.
- Add rework/deviation/waiver paths with independent approval and full genealogy.
- Validate route release against the actual approved technical package, IE operation standards, material readiness and quality plan.
- Build full route tests for skipped/optional/alternate/subcontract operations, stale revisions, holds, retries and authorization.

### WP-09 — Material issue, cutting, bundles, WIP and production genealogy

**Current state: material ledger and initial execution/genealogy foundations; full apparel manufacturing process chain is missing.**

**Source areas**
- backend/app/Services/InventoryLedgerService.php
- backend/app/Services/ManufacturingExecutionService.php
- backend/app/Http/Controllers/Manufacturing/
- migrations 000015, 000016, 000022 and 000023–000024

**Remaining actions**
- Complete fabric relaxation, marker/lay/spreading/cutting, ply, roll-to-lay, panel yield, remnants, recuts and cut-bundle numbering.
- Keep fabric-roll issue, trim/accessory issue and cut-bundle transfer as distinct controlled transaction types.
- Complete separate trim issue/return and consumption at operation/line.
- Complete WIP custody and subcontract issue/return with quantities, losses, accepted quantities and ownership/custody.
- Complete bundle-to-operation-to-piece/carton genealogy, production consumption reconciliation, scrap/yield variance and rework.
- Add no-overissue, duplicate scan, partial transaction, reversal, concurrent issue and full genealogy tests.

### WP-10 — Manufacturing execution, line planning and hourly control

**Current state: initial execution queues/events and workforce/capacity slices; live factory execution not accepted.**

**Source areas**
- backend/app/Http/Controllers/Manufacturing/
- backend/app/Services/ManufacturingExecutionService.php
- backend/app/Services/IndustrialEngineering/
- migrations 000016 and 000019

Present slices include production snapshot/queues, MES events, skill-check starts, output/defect/downtime and completion records.

**Remaining actions**
- Complete cutting, print/embroidery, sewing, washing/dyeing/special process, finishing, pressing, packing and subcontract execution as distinct routed departments.
- Complete machine/attachment/tool, worker skill/assignment, shift, attendance, bundle and WIP transaction controls.
- Integrate planned capacity/SAM to line target, hourly actual output, earned minutes, efficiency, balance and bottleneck/Andon action.
- Add device integration, scan/offline capture, reconciliation and duplicate-event protection.
- Test race conditions, deadlocks, concurrent starts/output/consumption and partial failures.
- Complete factory UAT with real approved line processes and role authority.

### WP-11 — Quality, compliance, HSE, NCR, CAPA and release

**Current state: inspection/defect/NCR/CAPA/release-decision foundations.**

**Source areas**
- backend/app/Http/Controllers/Quality/
- backend/app/Services/Quality/
- migration 000017

**Remaining actions**
- Implement sampling plans/AQL by customer, product, risk and operation, with versioned inspection criteria.
- Complete incoming, inline, end-line, final, measurement, workmanship, packaging and compliance inspections.
- Complete hold/quarantine, disposition, repair/rework, scrap, deviation and independent reinspection controls.
- Complete CAPA root cause, action owner, due date, effectiveness evidence and escalation.
- Add HSE incident/permit/chemical/process/environment controls applicable to each process.
- Ensure quality hold blocks route progression, stock release, shipment and customer dispatch at API/database boundaries.
- Add sampling, defect/rework, hold/release, audit and concurrent disposition tests plus factory UAT.

### WP-12 — Finished goods, WMS, DC/branch, order fulfilment, sales channels and returns

**Current state: finished-goods, reservation, credit, order and shipment foundations; full commerce/distribution is missing.**

**Source areas**
- backend/app/Http/Controllers/Distribution/
- backend/app/Services/Distribution/
- migrations 000018, 000023, 000024 and 000026

**Remaining actions**
- Complete stock ledger, location/bin, handling-unit, carton/pallet/container and transfer workflows.
- Complete quality-released finished-goods receipt and production output reconciliation.
- Complete available-to-promise and reservation/allocation across legal entity, DC, branch, store and fulfilment partner.
- Complete pick/pack/ship, dispatch, carrier manifest, ASN, proof-of-delivery and customer delivery exceptions.
- Complete wholesale/reseller, own retail, POS, D2C/e-commerce, marketplace and branch replenishment channels.
- Complete RMA, customer return, inspection, refund/credit, restock/quarantine/scrap and inventory/finance reconciliation.
- Complete offline QR/barcode/RFID scan capture and safe event replay.
- Integrate actual payment, logistics and commerce providers through approved server-side adapters; no provider-specific credential in frontend.
- Add cross-node, partial dispatch, return, payment, duplicate order and concurrent reservation tests.

### WP-13 — Finance, accounting, AP/AR, costing, treasury, tax, FX and close

**Current state: accounting foundations and journal control; full financial subledgers and integrations are not complete.**

**Source areas**
- backend/app/Http/Controllers/Finance/
- backend/app/Services/Finance/FinanceControlService.php
- migrations 000020, 000025 and 000026

Present sources include finance periods, accounts, journal drafts/lines, balanced posting records, journal approval separation, posting/reversal foundations and customer credit decision evidence.

**Remaining actions**
- Complete legal-entity chart of accounts, dimensions, budgets and financial calendar/versioning.
- Complete supplier AP, customer AR, receipts/invoices, credit notes, cash and bank subledgers and reconciliation.
- Complete purchase-to-pay/three-way match handoff from WP-07 and sales/shipment/invoice/collection handoff from WP-12.
- Complete product/style/route/material/operation cost rollups, WIP valuation, actual cost, variance, scrap/yield and inventory valuation.
- Complete FX rates, currency conversion, customs/tax treatment and landed-cost capitalization.
- Complete period close, accruals, depreciation, allocations, financial statements and audit exports.
- Complete bank/payment files, remittance acknowledgements and reconciliation only through controlled external providers.
- Add duplicate/reversal/idempotency/concurrency and full subledger-to-GL reconciliation tests.
- Prevent direct operational mutation of posted financial balances; corrections must use controlled reversal/adjustment.

### WP-14 — People, HR, time, leave, skills, compensation, benefits and Ethiopian payroll

**Current canonical state: review foundation only. New payroll calculation candidate is not integrated.**

The canonical delivery includes the finance/payroll review foundation in migration 000020. It does not provide a complete employee master, contract, assignment, time, leave, skills, benefits and payroll workflow.

**Unmerged candidate branch**
- Branch: work/wp14-ethiopia-payroll-localization-2026-11-27
- Head: 137667d7970bec9350903aa7792eaedfc7de3385
- 20 commits ahead of delivery; no pull request and no recorded workflow run.
- New source: backend/app/Services/Payroll/EthiopiaPayrollCalculator.php
- Related source: backend/app/Services/Finance/FinanceControlService.php and FinanceControlController.php
- New configuration: backend/config/payroll.php
- Candidate tests: backend/tests/Unit/EthiopiaPayrollCalculatorTest.php
- Legal and release evidence: docs/localization/ethiopia-payroll-localization-2026-11-27.md and docs/localization/ethiopia-payroll-engineering-release-gate.md

**What the candidate does well**
- Separates candidate regression calculations from approved payroll-draft calculations.
- Uses decimal strings/minor-unit arithmetic rather than floating point for calculator monetary inputs.
- Requires explicit tax and pension treatment for each earning component.
- Requires exemption evidence and a rationale for non-standard pensionable salary.
- Separates employee 7% and employer 11% contribution treatment in its candidate rule model; employer pension is not deducted from employee net.
- Excludes RETAINER_SERVICE/nonemployee service from the employee calculator.
- Requires effective-dated tax and pension rule records, independent source review, rounding method and a separate PAYE-base opinion.
- Produces a rule/source snapshot hash and marks a result CALCULATED_DRAFT_NOT_POSTED.
- Both activation flags default false; the source intentionally holds live payroll.

The candidate preserves two ETB 30,000 comparison cases:
- TAXABLE_GROSS: taxable base 30,000.00; PAYE 8,450.00; employee pension 2,100.00; net 19,450.00.
- TAXABLE_GROSS_LESS_EMPLOYEE_PENSION: taxable base 27,900.00; PAYE 7,715.00; employee pension 2,100.00; net 20,185.00.
- Candidate difference in PAYE/net is 735.00 ETB.

Those are engineering candidate calculations, not a professional legal determination. The branch's own documents say the PAYE-base question requires a separate qualified Ethiopian opinion and statutory activation remains HOLD.

**Source-level gaps to close before integration or activation**
- No PR, no exact-head CI and no end-to-end API/database test evidence for this branch.
- No employee master/contract/assignment/statutory-profile resolver or HR UI is present in this change set.
- No HR time, attendance, overtime, leave, benefits, incentive, payslip, employee self-service or payroll-to-accounting/bank/statutory remittance flow is completed.
- The calculator validates a 64-character SHA-256 format and a government-domain URL; it does not itself retrieve the retained legal source bytes and recompute/compare their hash. Build a controlled source-document verification pipeline and store the verified artifact identity.
- Synthetic test fixtures use synthetic justice.gov.et URLs/hashes. Those tests are arithmetic and validation fixtures, not actual legal-source verification.
- The generic gov.et suffix check is not a substitute for a maintained authority allow-list and source-artifact retrieval verification.
- The authorizedDeductions approved path checks approval identity/status/reference but needs the same complete legal-source/hash/article validation standard as other statutory evidence.
- Check tax/pension rule applicability against the selected legal employer/entity and worker status, not only tenant and jurisdiction/date selection.
- A distinct human-readable rule-version identifier should be explicit and immutable; do not infer legal version solely from a mutable rule code.
- The percentage/progressive-tax numerator multiplies minor-unit amounts by basis-point rates using native integer arithmetic. Add explicit overflow-safe arithmetic or strict supported monetary bounds and boundary tests.
- Professional approval must be tied to an authenticated reviewer whose qualification/authority is verified by the authorization model, not only a separate user ID/reference string.
- Add Laravel feature tests for API validation, tenant RLS, rule retrieval/freeze, line persistence, maker-checker, idempotency, concurrent run creation, source-hash tampering and hold behavior.
- Add the missing Angular payroll/HR workflow only after the server API and legal/business gates are fully specified and tested.

**Required action:** create a PR from this branch against current delivery, resolve code/test review findings, execute exact-head CI and preserve the default-off hold. Do not turn either payroll activation setting on until the actual HR master, professional opinions, source documents, full regression, user acceptance and release evidence exist.

### WP-15 — Documents, integrations, devices, analytics and agent-native workflows

**Current state: governance and data foundations; live worker/provider/device/analytics operation is incomplete.**

**Source areas**
- backend/app/Http/Controllers/Integration/
- backend/app/Services/Integration/
- backend/app/Services/DeviceScanService.php
- backend/app/Services/AgentGovernanceService.php
- migrations 000021 and 000025
- frontend feature areas do not yet provide a complete shared agent/device/integration operating console.

Present sources include document metadata/upload/extraction workflow foundations, integration endpoint registry/outbox, device scan events, agent-run audits, action proposals, approval decisions and action-event records.

**Remaining actions**
- Complete queue workers and scheduled processors for outbox, extraction, retries, dead letters, expiry and reconciliation.
- Complete inbox/deduplication, webhook signature/replay controls, per-connector idempotency, credential vault references, rotation and provider health.
- Complete malware scanning, MIME/content validation, private document storage, retention, legal hold and evidence retrieval verification.
- Complete barcode/QR/RFID/camera/mobile adapters, offline queue, device identity, timestamp reconciliation and scan-to-domain event resolution.
- Complete operational KPI/semantic data model, reporting/analytics and controlled Python specialist service contracts.
- Complete agent registry, role/domain-scoped retrieval, model/provider adapter, evaluations, prompt-injection controls, privacy minimization and tool allow-list.
- Every agent write must be a typed proposal through a deterministic domain service, with risk tier, idempotency, preconditions, human approval and immutable execution result. The model must never become the transaction authority.
- Add user-facing agent/chat/notification/approval surfaces and role-specific task UI.
- Validate all integration and agent paths with failure/replay, prompt-injection, authorization, data-leakage and human-override tests.

**Agent-native design material:** PR #3 contains an end-to-end technology/experience blueprint and Codex agent operating model, but it is documentation/design on a branch based on the audit branch, not evidence of deployed agent runtime or device connectors.

### WP-16 — System-wide quality, security, reliability, installer, deployment and release

**Current state: release standard and component CI exist; full system acceptance remains open.**

**Evidence locations**
- docs/release/end-to-end-acceptance-security-deployment.md
- docs/engineering/code-quality.md
- docs/engineering/development-deployment.md
- .github/workflows/ci.yml
- .github/workflows/wp01-16-completion-verification.yml
- Makefile
- docker-compose.yml

**Actual build/verification available**
- ERP CI has Laravel/PostgreSQL and Angular test/build jobs.
- The integrated combined workflow performs migration and a selected forced-RLS inventory check, runs Laravel tests, runs Angular tests/build, verifies the WP-01 archive and creates a combined review-candidate tarball artifact.
- Prior exact delivery SHA f948bb85 had successful backend/PostgreSQL and Angular workflow runs.
- The no-loopback change in PR #12 was tested successfully on its PR head f138cfbe.
- The current merged delivery head fc01eced has latest workflow attempts that failed before runner steps existed; current exact-head code was therefore not tested by those runs.

**What is not proven**
- The combined workflow name says WP-01–WP-16 completion verification, but it does not execute E2E-001 through E2E-008, all domain acceptance suites, browser E2E, penetration testing, performance/soak/concurrency, backup/restore, disaster recovery, production deployment/rollback or business UAT.
- The workflow's explicit forced-RLS assertion list includes 61 tables but omits platform_operator_grants, platform_tenant_entitlements and platform_audit_events created by migration 000032. Migration 000032 itself enables and forces RLS on those tables; the separate CI assertion should nevertheless include them so the test proves the invariant.
- The RLS test list is manually maintained. Add an automated inventory check so new tenant-owned tables cannot be omitted silently.
- No complete set of browser-level critical-workflow E2E tests is present.
- No dedicated full feature-test suites prove sourcing, sample/fit, manufacturing route, MES/cutting/WIP, quality, WMS/returns, finance subledgers or HR/payroll end-to-end.
- No full OpenAPI 3.1 contract set for all domain APIs is present; the current explicit contract is focused on the merchandising dashboard.
- No application Dockerfile, immutable API/frontend images, SBOM/signing pipeline, production manifest or demonstrated deployment artifact exists in the inspected delivery tree.
- docker-compose is local PostgreSQL/Redis infrastructure; it is not a complete ERP installer or production deployment.
- No backup/PITR plus object-storage restore rehearsal, RPO/RTO, queue recovery or disaster-recovery result is recorded.
- No independently executed penetration test, load/soak test or accessibility audit is evidenced.
- No staging or factory/business UAT sign-off is evidenced.
- No live production deployment or production release authorization is evidenced.

**Exit criteria:** the exact candidate SHA passes all required component, domain, contract, RLS, E2E, security, performance, backup/restore, migration, staging/UAT and operational gates; the same immutable artifact is promoted; business/legal/operator activation requirements are met.

---

## 4. Current branch, commit and unapplied-source reconciliation

A Git commit is not part of the canonical ERP until it is integrated into `delivery/autonomous-wp-01-16`, its migrations and contracts are reconciled, and the resulting destination SHA passes the required verification. The following inventory was refreshed against the live Git refs on 27 November 2026.

| Work item | Current branch / PR | Exact head / relationship to delivery | Source already rendered on GitHub | Required disposition |
|---|---|---|---|---|
| Main executable platform | PR #1; `delivery/autonomous-wp-01-16` | Delivery `fc01eced62b97c4ec27f0075264fc8ba0fb11851`; 813 commits ahead of `main`; PR #1 open draft, 290 files | Laravel 13, Angular 22, PostgreSQL migrations 000001–000032, domain controllers/services, tests, docs and base tooling | Keep this as the integration line. Review the complete diff against the 18-file `main` scaffold and qualify the actual merge SHA; do not treat commit count as requirement completion. |
| WP-03 platform operator grant lifecycle and database scope hardening | PR #13; `work/wp03-completion-2026-11-27` | `8ae8536bccfd3924017b12905ea57fce2641fb46`; 77 commits ahead and 15 behind current delivery; PR #13 targets stale base `f948bb85de4194ae18dc8ee8c974c3ad0986623c`; 33 files | Migrations 000033/000034; operator grant controller and Angular screens; authorization/scope service changes; platform administration, PostgreSQL security tests and workflow changes | Rebase onto current delivery; review security-definer functions, grants, actor proof, forced RLS, mutation authorization and migration semantics. Preserve fail-closed scoped access. Add target-resource scope enforcement and expiry/recovery worker. Do not merge the stale head as-is. |
| WP-03 organization structure runtime | `work/wp03-org-master-runtime-2026-11-27`; no PR opened | `da50b7392288c6cbe0c967f9d7310dff28448e59`; 39 commits ahead, 0 behind current delivery; 15 changed files | Migration 000035; four PostgreSQL organization tables; Laravel service/controller/routes; Angular enterprise organization screens; component spec and PostgreSQL feature tests | This is executable candidate source, not a document-only plan. Review it against WP-03 operator/security changes, then open a focused PR against current delivery. Extend it with incumbent/employee-position assignments and runtime DoA before declaring the organization/approval requirement complete. |
| WP-14 Ethiopia payroll localization | `work/wp14-ethiopia-payroll-localization-2026-11-27`; no PR opened | `b6b3fb3094c460e8a09f13283c9b65f75c5469cb`; 57 commits ahead, 0 behind delivery; 20 changed files | EthiopiaPayrollCalculator, Finance controller/service integration, payroll configuration, migrations 000033/000034, calculator unit tests, source/legal/release-gate documents and CSV fixtures | Review as a high-risk candidate. Add API/PostgreSQL/RLS/approval/concurrency/payroll-to-GL feature tests; reconcile migration sequence with WP-03 000033–000035; resolve retained-source/storage evidence; retain both activation flags as false until every professional/legal/employee and acceptance gate is satisfied. |
| Combined WP-03/WP-14 organogram and payroll implementation plan | `work/wp03-wp14-organogram-payroll-plan-2026-11-27`; no PR opened | `0081e4f9ee81e8a213f1b73373ffa9396ca1843c`; 12 commits ahead, 0 behind delivery; 10 documentation files | Org/DoA framework; WP-03/WP-14 implementation sequencing; Ethiopia payroll legal engineering and release-gate docs; payload template and regression fixture | Reconcile as the combined source-of-truth plan. It overlaps PR #16 at `docs/organization/enterprise-organogram-and-delegated-authority.md`; consolidate rather than importing duplicate versions. It contains no runtime implementation. |
| CI runner continuity | `work/wp03-ci-runner-continuity-2026-11-27`; no PR opened | `659e53f324d0a8c3cebd7ab5695a83e5d48f824b`; 4 commits ahead, 0 behind; 4 changed files | ERP CI / combined verification workflow updates and `docs/engineering/ci-runner-continuity.md` | Review the dispatch-only self-hosted fallback and workflow routing. A runner setup is not proved merely by the workflow definition; verify an actual runner and exact-SHA run. Keep every quality job mandatory. |
| WP-01 R3 reconciliation | PR #17; `work/wp01-r3-reconciliation-2026-11-27` | `b57c919a2fb547a3c919e1a05e169cf9eee7dbab`; 3 commits ahead, 0 behind delivery; 3 docs files | Corrected status of the legacy GAR matrix, R3 reconciliation and handover changes | Review/merge as a traceability correction after exact-head checks. The semantic crosswalk is still open; 128 R3 implementation statuses remain `not_started` until executable evidence exists. |
| Enterprise organogram and delegated authority design | PR #16; `work/enterprise-organogram-doa-2026-11-27` | `ad5a5a71e8e46c87081a1f16fb0a723df44c65c0`; 6 commits ahead, 0 behind; 3 docs files | Position-based hierarchy and DoA framework plus architecture navigation/model updates | Keep as design input; reconcile with the combined WP-03/WP-14 plan and WP-03 runtime branch. The design is not a runtime approval engine. |
| Apparel library sample reconciliation | PR #14; `work/library-apparel-sample-reconciliation-2026-11-27` | `b765460a608dd629920b9d1ef4b3512aa0db1eaa`; 1 commit ahead, 0 behind; 1 docs file | 76-entry workspace sample review, 25 proposed JSON Schemas, changeset and legacy-source boundary analysis | Retain as reference reconciliation. Previous checks record 37/37 JSON files parsed, 25/25 schemas validated, 5/5 registry tests and source-manifest checks; this is not live ERP runtime or factory acceptance. Do not copy old WordPress/PHP/MySQL runtime into the Laravel/Angular/PostgreSQL product. |
| Agent-native ERP/Codex blueprint | PR #3; `feature/agent-native-erp-foundation-2026-11-25` | `dfdf503b4aae4b0e787ca1cb3826c4fd97171b20`; branch is based on the earlier corrective-action audit, not the current delivery; comparison shows 46 commits ahead and 813 behind delivery | Agent role definitions, agent skills, operating model, technology/experience blueprint and source-intake workflows | Reconcile the approved design and agent policies onto the delivery line selectively. Do not merge the diverged 157-file comparison as a single runtime change and do not claim that agents, device connectors or agent screens are operational. |
| Provider-neutral private document storage | PR #10 intermediate merge; consolidated fix in PR #27 | PR #27 head `1261451a08c2df108e90ef4d335d3bbbce1989a6`, 20 commits ahead of delivery; 10 changed files across WP-11/WP-15/WP-16 | Configurable private disk, local fallback, RuntimeException import, transactional metadata persistence, upload compensation, unit/feature tests | Review exact-head PHP/PostgreSQL checks and storage-provider behavior; then integrate to delivery. PR #10 source is no longer the patch to port—PR #27 contains the current implementation. |
| Private document / quality-state / RLS remediation | PR #27; `work/fix-private-document-storage-rls-inventory-2026-11-27` | `1261451a08c2df108e90ef4d335d3bbbce1989a6`; 20 commits ahead, 0 behind delivery; 10 files | Private storage transaction/compensation; explicit inspection pending-review state; atomic CAPA; dynamic tenant RLS inventory; workflow scope naming correction | Keep as a focused implementation PR. It is source-rendered and statically reviewed; exact-head CI is NOT RUN due pre-runner failures. Do not merge before executable PHP/PostgreSQL/Angular/source-package checks pass. |\n| Superseded quality-review candidate | PR #26 closed; `fix/wp11-inspection-review-transition-2026-11-27` | `d96f8c3ee343b2ec526745d9d30ce7b5740eaeda`; closed after its core behavior/test was incorporated in PR #27 | Original migration 000034 and focused inspector/reviewer feature test | Do not revive or merge: migration 000034 collides with WP-03/WP-14 candidate sequence. Its release-eligibility assertions are now in PR #27 and its chosen migration is 000038. |\n| No-loopback runtime-default correction | PR #12 merged into delivery | Merged delivery head `fc01eced62b97c4ec27f0075264fc8ba0fb11851`; source PR head `f138cfbeee9adea99fd0d0a2f98afeb9af016788` | Runtime host defaults are placeholders; checker `scripts/check-no-loopback-defaults.php`, CI and Makefile enforcement are integrated | Preserve this change. Loopback addresses used inside isolated CI service environments or local Compose infrastructure are not application runtime defaults. Exact delivery-head workflow evidence must still be executed. |
| Earlier source-intake / SHA corrective-action audit | PR #2; isolated source-intake base | `audit/corrective-action-plan-2026-11-24` at `0d005ab32ab6b0e4c24ac87727f2d9ae3fb12024`; base is `audit/stockland-local-source-v0.3.0` | Intake provenance, source archive inspection and corrective-action documentation | Keep separate from executable ERP runtime. Use source material only through the approved greenfield reconciliation process. |
| This WP-00–WP-16 audit | PR #18; `audit/wp00-wp16-source-gap-audit-2026-11-27` | Current branch head before this amendment: `7260c55a79135f0187ccbb884e34779fe2df346c`; PR targets delivery `fc01eced...`; 26 commits, 2 documentation files | 263 KB source-level gap audit and the workflow agreement amendment | Keep this audit on its separate branch/PR. This amendment updates its branch inventory and adds the newly inspected WP-03, WP-14 and CI source candidates; it does not change ERP runtime code. |

### 4.1 Provider-neutrality defect remains in the current delivery and the payroll candidate

Source comparison confirms:
- Current delivery `backend/config/filesystems.php` does not define `filesystems.private_documents_disk` and defines no S3 disk.
- Current delivery `backend/app/Services/Integration/DocumentIntegrationService.php` resolves `config('filesystems.private_documents_disk', 's3')`; the storage failure path constructs `RuntimeException` without importing the global class.
- PR #10 has a candidate fix: private document disk configuration defaulting to local, local fallback, RuntimeException import and a unit test.
- WP-14 candidate `b6b3fb3094c460e8a09f13283c9b65f75c5469cb` updates DocumentIntegrationService for legal source-document verification, but its `filesystems.php` still has no `private_documents_disk` entry and the service still falls back to `s3`. It does not port the PR #10 storage correction.
- The PR #10 branch's old `.env.example` and configuration include loopback examples; do not copy them wholesale. Apply the storage fix to the current delivery version and preserve the no-loopback guard.
- PR #27 now adds unit coverage for the non-served local default and configured provider-disk selection, plus PostgreSQL feature tests for successful upload and compensation after metadata failure. Exact-head execution is NOT RUN. Additional provider failure, legal source read-back/hash mismatch, malware scanning, worker extraction and import adapter coverage remain.

### 4.2 Workflow execution state and exact-source evidence

- Delivery SHA `fc01eced...`: ERP CI runs #45915268942 and #45915273928 failed before steps; raw job records show `runner_id: 0`, no runner name and zero steps.
- WP-03 operator PR head `8ae8536b...`: run #45915554309 has the same zero-runner/zero-step signature; its combined verification run #45915554192 also failed before tests could execute.
- WP-03 organization runtime head `da50b739...`: CI #46010970370 and combined verification #46010970310; backend and Angular jobs show `runner_id: 0`, no runner name and zero steps. The combined packaging job was skipped.
- WP-14 payroll head `b6b3fb30...`: CI #46008205344 and combined verification #46008205340; backend and Angular jobs show `runner_id: 0`, no runner name and zero steps. The combined packaging job was skipped.
- WP-01 R3 PR head `b57c919a...`: runs #45981018818 and #45981018841 ended as failures; no code-test conclusion is inferred from the status alone.
- CI runner-continuity branch `659e53f3...` has no workflow run associated with the inspected head.\n- PR #27 head `1261451a...`: ERP CI #46023983272 and component/WP-01 verification #46023983506 both have backend/frontend/package jobs with `runner_id: 0`, blank runner names and zero steps; the archive follow-on is skipped. The PR's new PHP/PostgreSQL source tests therefore remain NOT RUN.
- Earlier delivery SHA `f948bb85...` has successful executed test evidence (ERP CI #45901553898/#45901559007). It is useful historical evidence for that SHA only, not for later heads.
- Earlier source-package checks recorded 128 R3 rows, 24 contexts, 45 source maps, 20 package tests, checksum verification and strict audit. These package-integrity results do not make the 128 application requirements implemented.

**Interpretation:** a zero-runner/zero-step failure is a runner-allocation/pre-job failure, not an application test failure and not a pass. The CI continuity work is a compute-routing option, not permission to waive any test, security or exact-SHA gate.

---

## 5. Test inventory and quality standard assessment

### 5.1 What the integrated test layer currently proves

The delivery test inventory contains a limited set of Laravel feature suites focused on:
- Tenant provisioning and isolation;
- Domain RLS security;
- Authentication/personal access tokens;
- Tenant membership and invitations;
- Platform administration;
- Initiative;
- Brand/range;
- Demand/OTB/pricing.

It also has a small set of unit tests such as decimal quantity support. The Angular app has unit specs in merchandising and product-development areas.

The separate WP-14 branch adds one isolated PHPUnit unit suite for the candidate Ethiopia payroll calculator. That is not a Laravel feature suite and does not prove the HTTP/database/tenant/role/storage behavior.

### 5.2 Missing or insufficient test families

Create domain-owned test suites for:
- Enterprise org hierarchy, legal entity/site/warehouse/channel/department target scope, MFA/SSO and entitlement enforcement.
- Product-development sample request/round, fit corrections, engineering change, BOM consumption and release dependency gates.
- IE time-study validity, SAM approval, line balance, capacity conflict and skills.
- Supplier qualification, RFQ/PO lifecycle, partial receipts, claims, import, landed cost and AP matching.
- Route/SOP branching, acknowledgement, operation deviations, rework and subcontract custody.
- Cutting, marker/lay, roll/bundle traceability, trim issue/return, WIP and consumption reconciliation.
- Manufacturing execution concurrency, event idempotency, hourly output/earned minutes, downtime and offline scan reconciliation.
- Quality sampling/AQL, inspection, NCR, hold, disposition, rework, CAPA and effectiveness.
- WMS location, stock availability, allocation, cartonization, dispatch, return/refund and financial reconciliation.
- Finance AP/AR/cash/bank/cost/FX/close, balanced postings, reversal and idempotency.
- HR employee master, employment/assignment, time, leave, overtime, payroll, benefits, statutory evidence and payroll-to-GL/remittance.
- Outbox worker/retry/dead-letter, external connector signature/idempotency, document malware/retrieval, retention and queue recovery.
- Agent risk-tier authorization, prompt injection, retrieval scoping, proposal/approval/execution idempotency and audit.

### 5.3 Required quality practices

The repo's own standards require:
- Explicit typed/testable code and domain ownership; no silent TODO for security, finance, payroll or data integrity.
- Money/quantity precision without binary floating point; UTC persistence; explicit tenant timezone rendering.
- Laravel server-side domain controls and PostgreSQL system of record; Redis only as cache/queue/short-lived coordination.
- Composite tenant foreign keys, forced RLS and non-owner application database credentials.
- Positive, negative, retry, reversal, audit, concurrency and tenant-crossing tests.
- API/OpenAPI contract tests, browser accessibility and critical workflow E2E.
- Dependency/license/vulnerability/secret/SAST/container checks, SBOM and immutable artifact provenance.
- Database migration rehearsal, backup/PITR and restore, queue recovery, deployment smoke, rollback/forward recovery and observability.
- Exact source SHA for every accepted test result.

The current source and tests satisfy only part of this standard.

---

## 6. Ordered implementation checklist

The sequence below is deliberately dependency-aware. Do not build downstream features against unapproved identifiers, schemas or business rules.

### Gate 0 — Stabilize the integration and evidence baseline

- [ ] Freeze the audited delivery head and maintain a branch-to-commit integration ledger.
- [ ] Restore hosted Actions runner execution and obtain actual exact-head backend/Angular/combined results.
- [ ] Ensure the CI report distinguishes runner-allocation failures (NOT RUN) from code-test failures and passes.
- [ ] Port the provider-neutral private-document-storage fix from PR #10 into current delivery without reintroducing loopback defaults.
- [ ] Add a regression test proving a clean installation can upload to the configured private local disk without S3.
- [ ] Add the three migration-000032 platform tables to the combined workflow's explicit forced-RLS verification list.
- [ ] Change the RLS verification to discover/compare all expected tenant-owned tables against a maintained allow-list; fail if a migration introduces an unverified tenant table.
- [ ] Rename or clarify the combined workflow so its name does not imply full WP-01–WP-16 acceptance when it only runs component, WP-01 package and selected RLS checks.
- [ ] Preserve a source SHA, toolchain, test command, result and artifact reference for each check.

### Gate 1 — Close WP-01 and WP-02 inputs

- [ ] Merge/review the PR #17 traceability correction after exact-head documentation checks execute.
- [ ] Build the R3 128-record semantic crosswalk to the legacy 120 GAR records.
- [ ] Assign every requirement to a package, source file/API/UI/data object, acceptance scenario and accountable role.
- [ ] Turn each open requirement into a sequenced engineering issue/task; preserve not_started statuses where implementation evidence is absent.
- [ ] Complete the Ethiopia official legal-source evidence store and hashes.
- [ ] Obtain qualified review of law amendments, applicability, transition periods, employment/contract status, deductions, exemptions and pension coverage.
- [ ] Keep unresolved legal matters on hold; do not populate production statutory defaults from candidate tests.

### Gate 2 — Finish WP-03 enterprise/security control plane

- [ ] Rebase WP-03 PR #13 against current delivery; resolve all file and migration drift.
- [ ] Review migrations 000033/000034 and all SECURITY DEFINER functions, fixed search paths, execute privileges, runtime table DML grants and actor/tenant derivation.
- [ ] Execute PostgreSQL security tests under app_runtime with NOBYPASSRLS.
- [ ] Prove operator grant request/approval/revoke/expiry, two-person bootstrap, cannot-approve-own-request, no last-admin removal and immutable audit.
- [ ] Implement scheduled expiry/status-audit processing with a non-forgeable system actor.
- [ ] Implement legal-entity, site, department, warehouse and channel identities/scopes and enforce target-record authorization for every domain.
- [ ] Keep SCOPED access blocked until the concrete scope path is implemented and tested.
- [ ] Finish entitlement enforcement per module, commercial plan catalogue and quota checks.
- [ ] Implement retention/export/archive/legal hold/data-subject/recovery lifecycle.
- [ ] Implement MFA, OIDC/SSO, recovery and token/session revocation.
- [ ] Implement position-based organizational hierarchy and DoA after the PR #16 model is approved; keep tenant OWNER distinct from business approver.
- [ ] Complete WP-03 acceptance scenarios GAR-ENT-001..010 and GAR-PLT-001/003/004/005.

### Gate 3 — Complete WP-04/WP-05 business source and product passport

- [ ] Complete WP-04 AT-MER-001..014 and close downstream merchandising-to-order/stock/sales reconciliation.
- [ ] Complete product style/revision/sample/fit/measurement/pattern/grade workflow by sample type.
- [ ] Complete secure asset store, malware scan, checksum retrieval verification, rights/retention and audit.
- [ ] Complete product technical pack, BOM, consumption/waste, substitutions, component specification and cost-rollup.
- [ ] Complete engineering change and release/supersession.
- [ ] Link approved product revision/BOM to route, IE standard, material standard, quality plan and cost.
- [ ] Add CAD/2D/3D only through replaceable adapter contracts and record actual provider support honestly.
- [ ] Add feature-level API, UI, database, negative, revision, concurrent-update and UAT coverage.

### Gate 4 — Complete WP-06/WP-07/WP-08 manufacturing inputs

- [ ] Implement IE workbench, time-study sampling, method bulletin, SAM approval, skill/machine requirements and line balance.
- [ ] Validate and approve factory-calibrated time study data.
- [ ] Complete supplier qualification, contract, RFQ/PO and PO amendment controls.
- [ ] Complete receipts, discrepancies, quarantine/inspection, returns and supplier claims.
- [ ] Complete customs/import documents, freight/duty/insurance and landed cost.
- [ ] Complete AP invoice and three-way match integration with WP-13.
- [ ] Complete material lot/roll/shade/trim genealogy from receipt through issue and production.
- [ ] Complete conditional route engine and versioned SOP/work instruction acknowledgement.
- [ ] Prove route release is bound to the approved product/engineering/material/quality revisions.
- [ ] Add source-to-receipt-to-AP integration and acceptance evidence.

### Gate 5 — Complete WP-09/WP-10 shop-floor execution

- [ ] Build cutting-room relaxation/marker/lay/spread/cut/number/yield/recut workflow.
- [ ] Implement separate fabric, trim/accessory and cut-bundle issue/return transactions.
- [ ] Implement style-specific route execution for print, embroidery, sewing, wash/dye/special process, finish/press and packing.
- [ ] Implement subcontract custody and reconciliation.
- [ ] Implement production order, line/shift, skill, machine/attachment, bundle, WIP and operation state machine.
- [ ] Connect daily plan to shift/hourly target, actual, earned minutes, efficiency, bottleneck and Andon actions.
- [ ] Implement QR/barcode/RFID/mobile scan and offline replay with idempotency.
- [ ] Test duplicate event, out-of-order scan, partial completion, yield/scrap, rework, race, deadlock and rollback.
- [ ] Run factory UAT using approved real process routes and roles.

### Gate 6 — Complete WP-11/WP-12 quality, WMS and commerce

- [ ] Implement risk/customer-specific AQL/sampling and full incoming/inline/end-line/final quality checks.
- [ ] Implement hold, quarantine, disposition, repair/rework, scrap, deviation and independent release.
- [ ] Implement NCR/CAPA root cause, action due-date and effectiveness.
- [ ] Integrate quality holds into manufacturing progression, finished-goods availability and shipment release.
- [ ] Complete finished goods location, carton/pallet/container genealogy, replenishment and transfer.
- [ ] Complete ATP/reservation/allocation across DC, branch, store and fulfilment partner.
- [ ] Complete pick/pack/ship/ASN/carrier/POD and delivery exceptions.
- [ ] Complete wholesale/reseller, POS, own retail, D2C/e-commerce and marketplace channel events.
- [ ] Complete returns/RMA, inspection, refunds/credits, restock/quarantine/scrap and finance reconciliation.
- [ ] Add integration adapters for payment/logistics/commerce without embedding provider keys or client-side authority.
- [ ] Prove complete stock/order/quality/financial reconciliation under concurrency and offline retry.

### Gate 7 — Complete WP-13/WP-14 finance and people/payroll

- [ ] Complete AP, AR, cash, bank, cost accounting, WIP, inventory valuation, FX, tax and close.
- [ ] Ensure every posting is balanced, legal-entity scoped, approved, immutable and idempotent.
- [ ] Add controlled reversals/adjustments and subledger-to-GL reconciliation.
- [ ] Create PR for the 20-commit WP-14 branch and review it against current delivery.
- [ ] Fix and test the WP-14 source-document byte/hash verification, authority allow-list and rule-version semantics.
- [ ] Bound monetary arithmetic safely and test maximum supported values, marginal bands and rounding ties.
- [ ] Verify legal-employer applicability, worker classification, contract/assignment and coverage resolution.
- [ ] Complete employee master, contracts, assignment, payroll elements, time, overtime, leave, benefits, incentives and employee self-service.
- [ ] Add Laravel feature tests for payroll rule freeze, source verification, tenant RLS, role permissions, maker-checker, duplicates, concurrency, history and holds.
- [ ] Add payroll-to-GL, bank-file/remittance and reconciliation only after professional/legal approval and operational controls.
- [ ] Maintain default-off payroll activation until all legal, HR, financial and release evidence passes.

### Gate 8 — Complete WP-15 document/integration/device/agent operations

- [ ] Complete durable outbox/inbox workers, scheduler, retry/backoff, dead-letter, replay and reconciliation.
- [ ] Complete connector registration, secret-vault references, rotation, webhook signature/replay, idempotency and sandbox testing.
- [ ] Complete secure private document upload, malware scan, retrieval hash verification, retention and legal hold.
- [ ] Complete scan/device identity, timestamp normalization, offline replay and domain resolution.
- [ ] Complete semantic KPI/data contracts and controlled analytics service.
- [ ] Implement agent registry, provider abstraction, risk policies, retrieval access scope, evaluation and prompt-injection defenses.
- [ ] Constrain model outputs to proposals; execute only through deterministic domain APIs with human approval where required.
- [ ] Complete agent UI, notification, approval inbox, observability, audit, rollback and operator control.
- [ ] Demonstrate privacy, tenant isolation, permission and failure safety for every agent/tool flow.

### Gate 9 — WP-16 integrated release qualification

- [ ] Publish complete OpenAPI 3.1 contracts and compatibility tests for all domain APIs/events.
- [ ] Add full browser/accessibility and critical workflow E2E coverage.
- [ ] Execute E2E-001 own-brand lifecycle; E2E-002 buyer order; E2E-003 subcontract; E2E-004 wholesale/reseller; E2E-005 POS/D2C/return; E2E-006 hourly factory recovery; E2E-007 worker/creator/vendor classification; E2E-008 cross-tenant isolation.
- [ ] Execute additional Miret journeys: product passport genealogy; quality hold/rework/reinspection; monthly capacity-to-hourly output reconciliation; agent capability/human-approval; customer feedback to CAPA/initiative.
- [ ] Perform independent penetration/security review and remediate all blockers.
- [ ] Perform load, burst, soak, concurrency and failure-injection tests using a measured workload/SLO profile.
- [ ] Build production API/frontend images with non-root runtime, SBOM, vulnerability scans, provenance/signature and immutable digest.
- [ ] Build a reproducible canonical installer/deployment bundle; document operator prerequisites and environment configuration.
- [ ] Test forward migrations, compatible rollout, backup/PITR, restore, attachment/object integrity, outbox replay and disaster recovery.
- [ ] Establish logging/metrics/traces, alert ownership, incident runbooks and data/secret redaction.
- [ ] Execute staging deployment, smoke tests, queue/scheduler checks and factory/business UAT.
- [ ] Record qualified legal/statutory approval for any statutory activation; keep all non-approved rules on HOLD.
- [ ] Obtain accountable release operator authorization and verify rollback/forward recovery.
- [ ] Promote the same immutable, exact-SHA artifact through environments; never treat merge or CI alone as production authorization.

---

## 7. Cross-package dependency order

These dependencies must be preserved:

1. WP-00 governance defines promotion and human-only boundaries.
2. WP-01 R3 crosswalk and WP-02 legal evidence define what is required and which facts/rules are legally safe.
3. WP-03 tenant/entity/identity/target authorization is the control plane for every module.
4. WP-04/WP-05 produce approved commercial/product identity, revision, BOM, specification and cost inputs.
5. WP-06/WP-07/WP-08 provide approved standards, materials/supplier evidence and production routes.
6. WP-09/WP-10 consume the above for material genealogy, WIP and factory execution.
7. WP-11 holds/releases production and finished stock.
8. WP-12 consumes approved finished goods and quality release for fulfilment, sales and returns.
9. WP-13 records the financial consequences of procurement, stock, production, order, sale, return and payment.
10. WP-14 uses authoritative employee/contract/assignment facts and WP-02 legal rules; payroll is not a substitute for supplier/AP classification.
11. WP-15 provides secure documents, integration, device, analytics and agent orchestration across bounded domains.
12. WP-16 proves the entire system, not only the existence of each individual module.

---

## 8. Production objective and evidence rules

**Production objective:** complete and qualify the canonical ERP and reproducible master installer against the agreed WP-00–WP-16 plan and engineering standards. The source-level findings and ordered implementation work below are the active build programme toward that objective.

Do not:
- Claim the full ERP is complete based on the 33 migrations or the open draft PR.
- Treat a test package's internal success as application requirement acceptance.
- Merge the WP-03 security candidate without rebase and exact-head review.
- Enable Ethiopian payroll statutory flags based on synthetic fixtures or a candidate calculator.
- Treat the storage-provider fix as integrated until delivery contains the correction and regression test.
- Treat the combined WP-01–WP-16 workflow as full E2E completion.
- Copy legacy WordPress database schema, PHP runtime or customer records into the greenfield product.
- Deploy the candidate or process live financial, payroll, bank or statutory transactions without the relevant approval and operational controls.

For every requirement accepted as implemented, preserve:
1. Requirement ID and source document.
2. Canonical branch, exact commit SHA and changed file paths.
3. Database migration/table and API/UI path.
4. Test ID, test command, exact tested SHA and unambiguous result.
5. Positive, negative, duplicate/retry, concurrency, tenant-boundary and audit evidence as applicable.
6. Dependencies, open defects and approved exceptions.
7. Business/legal/security/operator decision where the requirement requires it.

**The target is one integrated, customer-neutral, production-grade garment ERP with end-to-end traceability from initiative and design through sourcing, factory execution, quality, warehouse, customer fulfilment, finance, people and controlled intelligence—not a set of disconnected CRUD modules or documentation-only completion claims.**


---

## 9. Master installer and clean-install source audit

The approved platform and deployment standards explicitly call for multi-stage application containers, immutable images, non-root runtime, service health checks, separate API/worker/scheduler/specialist processes, reproducible setup, dependency and supply-chain checks, and safe upgrade/recovery. The current repository has useful developer infrastructure, but the inspected tree does not yet include the complete master installer or deployable application image set.

### 9.1 Verified foundation already available

| Source | Verified capability |
|---|---|
| .nvmrc | Pins Node.js 26.13.1 |
| .php-version | Pins PHP 8.5 |
| .python-version | Pins Python 3.15.2 |
| backend/composer.json and backend/composer.lock | Laravel 13 dependencies and PHP lockfile |
| frontend/package.json and frontend/package-lock.json | Angular 22 / TypeScript 6 frontend dependencies |
| docker-compose.yml | Local PostgreSQL 18 and Redis 8.2 infrastructure |
| docker/postgres/init/010-create-roles.sql | Local PostgreSQL migration/runtime role bootstrap |
| backend/.env.example | Backend environment template with non-routable example origins and required host placeholders |
| backend/app/Console/Commands/ProvisionTenantOwner.php | Interactive, controlled first-tenant/owner provisioning command |
| Makefile | Individual developer targets for infrastructure, dependency installation, key generation, migrations, API/frontend serving, lint and tests |
| docs/development/local-development.md | Manual clean developer setup steps |
| .github/workflows/ci.yml | Backend/PostgreSQL and frontend build/test/dependency-audit jobs |
| .github/workflows/wp01-16-completion-verification.yml | Candidate backend/frontend/WP-01 source verification and source archive assembly |

The Makefile targets are infra-up, infra-down, api-install, api-key, api-migrate, api-test, api-lint, api-serve, web-install, web-test, web-build, web-serve, check-no-loopback-defaults and test. This is a helpful command layer, not yet an idempotent installer that configures and starts the complete ERP.

The existing Compose file is intentionally a local developer dependency stack. It starts only PostgreSQL and Redis; it uses disposable local passwords and publishes its ports on IPv6 loopback. It must remain distinct from a production topology. It does not build or run the Laravel API, Angular web app, queue worker, scheduler or Python service.

### 9.2 Master installer elements not present in the inspected delivery tree

The recursive tree at delivery SHA fc01eced62b97c4ec27f0075264fc8ba0fb11851 contains no:
- backend/API Dockerfile;
- frontend/web Dockerfile;
- separate worker, scheduler or Python specialist Dockerfile;
- .dockerignore;
- .devcontainer/devcontainer.json;
- production Compose file or environment overlay;
- Helm/Kubernetes/Kustomize, Terraform or other production deployment manifest;
- application image build/publish/release workflow;
- container SBOM, image signature/provenance or digest-promotion workflow;
- Python application package, pyproject.toml, locked Python dependency file or specialist worker entry point;
- application bootstrap/installer executable or single orchestrated clean-install command;
- dedicated install/upgrade/doctor/preflight/recovery script set.

The Python runtime version is pinned, but no Python implementation/service is in the tree. The platform standard reserves Python for specialist optimization, forecast, document-intelligence and AI workloads; those capabilities must be delivered as bounded services and not as a competing ERP transaction API.

The work-packages/ tree contains the WP-01 R3 package and source archive parts. It does not contain equivalent individually packaged implementation/evidence bundles for WP-02 through WP-16.

### 9.3 Runtime process and asynchronous execution gap

- backend/.env.example defaults to CACHE_STORE=file, QUEUE_CONNECTION=sync and SESSION_DRIVER=file.
- backend/config/queue.php defines sync, database and Redis queue connections, but the inspected source tree has no backend/app/Jobs/, backend/app/Events/ or backend/app/Listeners/ package and no worker/supervisor process definition.
- backend/routes/console.php contains only about:erp; the discovered application console command is the interactive platform:provision-tenant-owner. There are no scheduled application tasks defined there.
- IntegrationOutboxService has claimBatch, markDelivered and markFailed methods, including retry/dead-letter state handling, but no discovered consumer job/command/worker invokes a connector adapter and drives the batch to an external endpoint.
- DocumentIntegrationService::beginExtraction creates a QUEUED extraction run and stageExtractionResult accepts a staged result; no extraction worker/provider runtime is present in the inspected source tree.
- These services and schema are therefore control/data foundations. They must be connected to idempotent queue workers, scheduler, connector adapters, health/lag monitoring and recovery procedures before being represented as continuously operating integrations.

### 9.4 Installer-specific functional source issue

The canonical backend/config/filesystems.php defines local and public disks only. It does not define filesystems.private_documents_disk or an s3 disk. The canonical DocumentIntegrationService::storeUploadedFile nevertheless resolves:

config('filesystems.private_documents_disk', 's3')

Therefore the default document-upload path selects an undefined disk on a clean install. The same method throws RuntimeException without importing it into the App\Services\Integration namespace. This is a second confirmation of the provider-neutral storage defect recorded in Section 4.1. It must be corrected in the canonical delivery branch, with a clean-install upload test, private object permissions, checksum read-back and orphan cleanup on failed metadata persistence.

### 9.5 Single master-installer implementation checklist

**Package/build metadata**
- [ ] Preserve and validate all lockfiles and pinned runtime versions; test supported patch versions.
- [ ] Add exact build contexts and .dockerignore policies for backend, frontend, queue/scheduler and future specialist service.
- [ ] Build multi-stage API and Angular images with production dependencies only, non-root users, read-only runtime where practicable, health checks and graceful termination.
- [ ] Produce immutable image tags/digests, SBOM, vulnerability/license scan and signed provenance linked to exact Git SHA.
- [ ] Keep the local developer dependency Compose file separate from production and staging deployment definitions.

**Environment preflight and secrets**
- [ ] Create a versioned environment schema with required/optional variables, types, allowed formats, secret classification and safe defaults.
- [ ] Validate DB/Redis/mail/object-storage/API/frontend origins, TLS, DNS, clock, storage permissions, disk capacity and supported runtime before any migration.
- [ ] Reject loopback and .invalid placeholders in production profiles; do not place passwords, tokens, private keys or database credentials into committed files or image layers.
- [ ] Integrate the approved secret manager/environment injection mechanism and rotation process.
- [ ] Validate CORS, Sanctum stateful domains, cookie/session domains, mail sending, storage, tenant URL and frontend runtime configuration together.

**Database provisioning and migration**
- [ ] Automate least-privilege database/role/bootstrap preparation without exposing migrator credentials to the application container.
- [ ] Validate RLS role assumptions, forced policies, schema privileges and tenant-table inventory.
- [ ] Run ordered migrations as the migration identity exactly once, record migration version and source/image SHA, and fail atomically on drift.
- [ ] Separate production reference-data initialization from developer/test fixtures; keep fake customers, employees, orders and statutory records out of production.
- [ ] Add tested clean-install, in-place upgrade, retry, interrupted-migration, forward-repair and schema-compatibility procedures.
- [ ] Implement the first platform-owner bootstrap as a controlled one-time process, with idempotency and safe recovery; never expose a reusable default owner password.

**Service orchestration**
- [ ] Start PostgreSQL and Redis or validate approved managed service endpoints.
- [ ] Start API and Angular/web service behind the selected reverse proxy/TLS termination.
- [ ] Start outbox/integration worker, document extraction worker, notification/mail worker, queue worker and scheduler under separate least-privilege process identities.
- [ ] Add Python specialist worker only when its service contract, locked dependencies, API boundary and operational need are implemented.
- [ ] Configure queue retry/backoff/dead-letter, idempotency, graceful shutdown, concurrency limits, schedule locking and worker restart policy.
- [ ] Add readiness/liveness endpoints that check the real dependencies needed by the service without exposing secrets or tenant data.
- [ ] Wait for dependency health and migrations before promoting the API/web processes to ready.

**Installation, upgrade and recovery UX**
- [ ] Create a single documented install/bootstrap entry point for clean installation; support interactive operator use and non-interactive secure automation.
- [ ] Add doctor/preflight and status commands that validate configuration, images, database schema, queue, storage, mail and service health.
- [ ] Add an explicit upgrade command that takes backup/restore evidence, checks compatibility, drains or coordinates workers, runs approved migrations and performs smoke/reconciliation checks.
- [ ] Add safe rollback/forward-fix rules, backup/PITR and object/attachment integrity verification, queue replay and recovery runbooks.
- [ ] Emit a machine-readable installation manifest with product version, exact commit, image digests, schema version, dependency versions, configuration schema version and health result.
- [ ] Test from an empty isolated host and from the prior supported version; independently verify that a second run is idempotent and does not duplicate initial identities/data.
- [ ] Package an installable release bundle, not merely a Git source archive. The current combined CI tarball is explicitly a source/review artifact.

**Installer acceptance**
- [ ] One clean environment can be provisioned from the released bundle using only documented prerequisites and external secrets.
- [ ] Every service reaches healthy/ready state; app and worker identities have least privilege.
- [ ] First tenant bootstrap, login, tenant selection, core navigation and database health work.
- [ ] Storage upload plus hash retrieval works on the selected private storage provider.
- [ ] A queued outbox event is processed once, retried safely on transient failure and enters a visible dead-letter state on terminal failure.
- [ ] A queued document-extraction job runs through the provider adapter and stops at the required human review gate.
- [ ] A real application update preserves business data and produces a verified migration record.
- [ ] Restore rehearsal recovers database, private objects, application configuration references and pending outbox work within measured recovery objectives.
- [ ] Final bundle and every promoted image are tied to the same exact source commit and dependency lockfiles.

---

## 10. API, user-interface and test coverage reconciliation

### 10.1 Backend implementation inventory at the audited delivery SHA

The tracked tree contains 36 HTTP controller files, 26 service files, 4 Eloquent model files, 33 numbered migration files, 13 backend test files and 7 Angular spec files. There are no database seeder or model-factory files in the inspected tree.

Controllers and services exist for enterprise, merchandising, product development, sourcing, planning/industrial engineering, manufacturing, quality, distribution, finance, documents/integrations and agent governance. This confirms that the later work packages are not merely empty design documents: there are real source slices to extend and integrate.

### 10.2 Route registration and read-side completeness

backend/bootstrap/app.php registers backend/routes/api.php. At the bottom of api.php, the application explicitly requires domain-api.php; therefore, the domain routes are registered through the active API route file and should not be incorrectly treated as unreachable.

However, in domain-api.php, the sourcing, quality, manufacturing, planning/workforce, distribution, finance, documents/devices and agent groups are predominantly POST command endpoints. List/read/search/detail, operational dashboard, queue inspection and reconciliation endpoints are sparse or absent for these domains. Some existing endpoints do include reads for tenant/platform entitlements, platform tenant listing and domain-role assignments; the gap is not that every domain has no read capability, but that the operational domain read models and user tasks are incomplete.

Required follow-up:
- [ ] Add paginated list/detail/search/read contracts for every domain-owned aggregate, respecting tenant/entity/site/department/warehouse scope.
- [ ] Add dashboards and queues for approvals, exceptions, holds, shortages, late orders, WIP, capacity, quality and finance reconciliation.
- [ ] Add API schema/version/error/idempotency/authorization contract tests for commands and queries.
- [ ] Add audit history and source-document retrieval under the same resource authorization checks.
- [ ] Ensure frontend read models use API contracts instead of direct database or duplicated business calculations.

### 10.3 Angular user-facing domain coverage

The current Angular route registry and feature tree are concentrated in:
- features/auth/;
- features/enterprise/;
- features/merchandising/;
- features/product-development/;
- shared overview/core API/auth components.

There are no current feature route groups for sourcing/procurement, industrial engineering, manufacturing execution, quality, distribution/WMS, sales channels/POS, finance/treasury, HR/payroll, document operations, integration administration, devices or agent work/approval. The domain services/controllers are therefore ahead of the corresponding operator-facing workflows.

Required follow-up:
- [ ] Add role-specific domain navigation and route guards driven by server-side entitlements/permissions.
- [ ] Build list/search/detail, command forms, exception queues, approval inboxes and audit history for each domain.
- [ ] Build factory/tablet/mobile/scan task surfaces and offline reconciliation only with the device identity/event controls.
- [ ] Build finance, payroll, document, integration and agent administration interfaces with maker-checker and visibility of holds.
- [ ] Add accessible keyboard, screen-reader, focus, contrast and responsive workflows; test on supported browser sizes/devices.
- [ ] Keep domain logic on the server; do not treat hidden UI buttons as authorization.

### 10.4 Verified backend test distribution

The backend test inventory contains 93 test methods across 13 PHP test files. The strongest current coverage is around tenant provisioning, membership/roles, invitation, isolation/RLS, platform administration and merchandising/brand/range/demand/pricing. A decimal-quantity unit suite exists. The Angular test inventory contains seven spec files, concentrated in shared API connection state, merchandising and product development.

At this delivery SHA there are no dedicated backend feature-test files for sourcing, industrial engineering, manufacturing execution, quality/CAPA, finished-goods distribution, finance journal lifecycle, documents/integration outbox, agent action execution or the WP-14 Ethiopia payroll candidate. The WP-14 candidate has its own separate unmerged calculator unit suite, which still needs API/database/tenant/security feature coverage.

Required follow-up:
- [ ] Add one domain test suite per bounded context and test both valid and invalid lifecycle transitions.
- [ ] Cover authorization, tenant/entity/site/warehouse cross-scope denial, append-only audit and database constraints.
- [ ] Cover duplicate event, retry, partial failure, concurrency/locking, reversal and reconciliation.
- [ ] Add contract tests and full vertical tests that execute through the same HTTP/API/database path as the UI.
- [ ] Add browser E2E journeys and factory/user acceptance evidence, not only isolated service tests.
- [ ] Ensure every test report records the exact commit it actually tested.

---

## 11. Documentation synchronization and source-of-truth drift

Three current documentation areas have content that predates the present implementation and should be reconciled after the source baseline is stabilized:

1. **README.md — implementation checkpoint drift.** It names delivery commit bd41c83e141835db3c0f03ac8d711096728a07b4 as the latest integrated commit and describes the PR #9 checkpoint as current. The audited delivery branch head is now fc01eced62b97c4ec27f0075264fc8ba0fb11851 and contains later migrations 000010–000032, controllers, services, APIs and domain tests. The README should identify the current delivery head/checkpoint and make clear which later candidate changes are not integrated.
2. **docs/development/local-development.md — capability drift.** Its final limitation section still describes only the initial platform foundation and says WP-04–WP-15 domain workflows are not implemented. The current source tree contains executable domain slices and the loaded domain-api.php routes. Update it to describe what can actually be started and exercised today, while keeping missing UI/worker/installer capabilities explicit.
3. **docs/release/end-to-end-acceptance-security-deployment.md — historical snapshot wording.** It says no application scaffold/migrations/test/CI had been proven “at the point this specification is authored.” Preserve the historic decision, but attach its original date/source checkpoint and add a current evidence pointer so the historical paragraph is not mistaken for the current source state.

The deployment standard is ahead of the executable installer implementation: it already specifies multi-stage images, .dockerignore, non-root runtime, worker/scheduler/Python separation, CI static analysis and supply-chain controls, and a future .devcontainer. These requirements should be implemented rather than weakened to match the existing developer-only Compose file.

### Documentation synchronization checklist

- [ ] Refresh README's delivery head, merged PRs, workflow links, artifact type and next implementation checkpoint.
- [ ] Update the local development guide with present API routes, actual frontend feature areas, existing commands and known missing processes.
- [ ] Date/annotate historical statements in the WP-16 release standard and link to the current evidence register.
- [ ] Keep the application-folder/code map synchronized with every new backend/frontend/worker/installer file.
- [ ] Add a machine-readable feature/route/requirement/test mapping so progress is derived from evidence rather than manually repeated prose.
- [ ] Keep the WP-00–WP-16 handover register, source package folders and audit findings synchronized with actual merged commits.

---

## 12. Next executable audit/build order

The immediate source-to-build sequence is now:

1. Reconcile storage upload path (private_documents_disk, local/provider adapter, RuntimeException import, metadata failure cleanup) and add exact clean-install tests.
2. Complete the WP-03 candidate rebase and security review as a separate bounded increment.
3. Create the WP-14 payroll PR and integrate only after source/evidence/hash/arithmetic/API/security tests pass; maintain the existing statutory hold.
4. Implement a canonical master-installer skeleton: pinned build inputs, environment schema/preflight, image build contexts, app/worker/scheduler topology, reproducible local clean-install and machine-readable manifest.
5. Implement the durable outbox/extraction worker path before claiming integrations are operational.
6. Build the missing domain read APIs and Angular user journeys in the approved WP dependency order.
7. Complete per-domain automated coverage and synchronize README/local-development/release evidence with the actual source head.
8. Advance the same verified source through WP-16 system scenarios, image provenance, staging/UAT, recovery and final deployment authorization.

No source change to runtime behavior is made by this audit addendum. These items extend the existing implementation backlog with new, file- and behavior-specific evidence from the audited delivery SHA.


---

## 13. Deep domain-service audit — transaction, ledger, approval and event controls

This pass inspected Laravel service implementations, controllers and PostgreSQL constraints at delivery SHA fc01eced62b97c4ec27f0075264fc8ba0fb11851. Findings below are based on source and schema inspection. They are not described as executed reproductions because current GitHub workflow attempts at the audited head had not allocated a runner.

### 13.1 Finished goods can be received cumulatively above completed production output

**Finding:** The finished-goods receipt guard checks the quantity of each individual receipt against the last completed operation's good_quantity, but does not reconcile previously received finished-goods lots or transactions for that production order.

**Source**
- backend/app/Services/Distribution/FinishedGoodsService.php, receiveCompletedProduction() (starts at line 12).
- backend/database/migrations/2026_11_26_000023_create_finished_goods_inventory_and_reservations.php.
- The migration creates finished_goods_order_idx on (tenant_id, production_order_id, status) but does not impose a unique source receipt or cumulative receipt constraint on a production order.

The service locks the production order, which serializes concurrent receipt calls, but it generates a new finished-goods lot ID and a new receipt transaction ID for every call. Its receipt idempotency key includes that newly generated lot ID. It has no caller idempotency key and does not sum earlier receipt quantities before accepting another split lot.

**Example requiring a regression test:** A production order has final good output of 100 pieces. Two requests of 80 pieces each can each satisfy the per-request finished_quantity <= finalGood test. The service has no cumulative 160-versus-100 check.

**Required implementation**
- [ ] Permit legitimate split lots while capping the sum of all posted finished-goods receipts against the authoritative completed production quantity.
- [ ] Add source receipt identity/idempotency tied to the production order and production completion/output reference, not a random newly generated lot identifier.
- [ ] Lock the production order while calculating and posting cumulative receipts; add database enforcement where appropriate.
- [ ] Define explicit treatment for partial output, recut/rework/regrade, cancelled/reversed output and approved output variance.
- [ ] Add tests for exact receipt, multiple valid split lots, cumulative over-receipt, same-key retry, different-payload same-key conflict and two concurrent receipts.

### 13.2 Finished-goods release lacks the release-person separation present in other quality paths

**Finding:** FinishedGoodsService::releaseLot() checks that the linked final inspection passed review and that there are no open nonconformances. It does not compare the release actor to quality_inspections.inspected_by or reviewed_by.

The material-release and production-operation release paths in QualityControlService do explicitly prevent the inspector/reviewer from making the release decision. The finished-goods path should have equivalent control.

**Source**
- backend/app/Services/Distribution/FinishedGoodsService.php, releaseLot() (begins around line 99).
- backend/app/Http/Controllers/Distribution/FinishedGoodsController.php, releaseLot() authorizes quality.release-finished-goods and obtains the current actor.
- backend/app/Services/Quality/QualityControlService.php, decideMaterialRelease() and decideOperationRelease() contain explicit inspector/reviewer separation checks.

**Required implementation**
- [ ] Reject finished-goods release when actor equals the inspector or independent inspection reviewer.
- [ ] Resolve release authority using the approved role/position and legal-entity/site scope; a Gate name alone is not proof of segregation.
- [ ] Persist decision and actor evidence atomically with the inventory movement.
- [ ] Add positive and negative tests for each combination of inspector, reviewer and releaser identities, including cross-tenant and unauthorized location cases.

### 13.3 Customer-order header and line creation is not atomic

**Finding:** FinishedGoodsService::createCustomerOrder() inserts a customer-order header and then inserts its lines in a loop without wrapping the complete operation in a single database transaction.

The header can remain with zero or only some of its lines if a later line insertion fails. The customer_orders migration has a unique (tenant_id, order_number) constraint, but a retried request after a response loss is not explicitly reconciled to the original order.

**Source**
- backend/app/Services/Distribution/FinishedGoodsService.php, createCustomerOrder() (starts around line 172).
- backend/database/migrations/2026_11_26_000018_create_distribution_order_fulfilment.php, unique tenant/order number constraint.

**Required implementation**
- [ ] Put header and every line insert in one transaction.
- [ ] Add a client request idempotency key or a verified tenant/order-number replay rule that returns the original result only when the payload fingerprint matches.
- [ ] Reject same-key or same-order-number requests with a conflicting payload rather than silently returning different business data.
- [ ] Test failure on the second/later line and confirm the transaction leaves no header or orphan lines; test same-payload and conflicting-payload retries.

### 13.4 Reservation idempotency is not implemented at the service boundary

**Finding:** reserveCustomerOrderLine() receives reservation_key and the database enforces a unique (tenant_id, reservation_key), but the service does not first resolve an existing reservation by that key. A client retry after a successful operation may see the reservation already counted in active reserved quantities and then receive a validation/unique-constraint error rather than the original reservation result.

**Source**
- backend/app/Services/Distribution/FinishedGoodsService.php, reserveCustomerOrderLine() (starts around line 319).
- backend/database/migrations/2026_11_26_000023_create_finished_goods_inventory_and_reservations.php, unique reservation-key constraint.

**Required implementation**
- [ ] Resolve an existing reservation key before recalculating available stock.
- [ ] Compare a canonical request fingerprint including order line, lot, location and quantity.
- [ ] Return the original reservation on an identical retry; reject a conflicting retry without creating a second reservation.
- [ ] Add timeout/response-loss, duplicate scan, concurrent reservation and conflict tests.

### 13.5 Inventory idempotency does not detect a conflicting payload

**Finding:** InventoryLedgerService::postMovement() correctly checks the idempotency key before and after the material-lot lock. If a matching key already exists, it returns that movement without comparing the new request's lot, roll/trim, locations, movement type, quantity, unit or source reference.

This prevents duplicate ledger inserts for a repeated key, but does not distinguish an identical retry from a conflicting request that accidentally reuses the same key. The caller may be told an old, different movement was accepted.

**Source**
- backend/app/Services/Inventory/InventoryLedgerService.php, postMovement() (starts at line 15).
- backend/database/migrations/2026_11_26_000015_create_sourcing_material_traceability.php, inventory_transactions stores a unique tenant/idempotency key but no request fingerprint.

**Required implementation**
- [ ] Canonicalize and hash all material movement identity fields, amount, unit, locations, reason/source identity and actor-relevant context.
- [ ] Return the existing row only when the payload fingerprint is identical.
- [ ] Reject conflicting replays with a stable idempotency-conflict error and audit event.
- [ ] Preserve the existing lot lock, post-lock recheck and append-only ledger design.
- [ ] Test identical retry, conflicting same-key retry and two concurrent identical requests.

### 13.6 Invalid inventory decimal handling resolves the wrong exception class

**Finding:** InventoryLedgerService is in the App\Services\Inventory namespace and decimalUnits() throws new RuntimeException(...) but does not import RuntimeException or use the global class. An invalid value reaching this path can therefore resolve as App\Services\Inventory\RuntimeException rather than the global exception intended by the code.

**Source:** backend/app/Services/Inventory/InventoryLedgerService.php, line 214; imports at file head contain DB, Str and ValidationException only.

**Required implementation**
- [ ] Use an explicit global exception class or, preferably, a domain/validation exception consistent with the service contract.
- [ ] Ensure HTTP validation and database constraints prevent invalid values reaching ledger conversion.
- [ ] Add unit tests for malformed decimal, negative quantity, more than six fractional digits and maximum supported precision; assert a stable validation response rather than a class-resolution error.

### 13.7 Deviation approval is represented by a caller-supplied boolean

**Finding:** The quality inspection result controller accepts deviation_approved as a boolean. QualityControlService::submitInspectionResult() turns that flag into PASS_WITH_DEVIATION without requiring a linked, independently approved deviation record or approval reference.

The later inspection review is independent, which is a useful control, but the record does not establish who approved the deviation, against which standard/requirement, under what conditions, and with what effective scope.

**Source**
- backend/app/Http/Controllers/Quality/QualityController.php, submitInspectionResult() validation.
- backend/app/Services/Quality/QualityControlService.php, lines around 73–74.

**Required implementation**
- [ ] Replace the free boolean with a tenant-scoped deviation/waiver record and an independent approval decision.
- [ ] Store the linked deviation ID, standard/revision, affected quantity/lot/order/operation, reason, conditions, expiry/effective period and approving actor.
- [ ] Validate that the deviation covers the exact inspection subject, standard and affected quantity before setting PASS_WITH_DEVIATION.
- [ ] Keep the inspector, deviation approver, inspection reviewer and final releaser distinct where required by the quality authority matrix.
- [ ] Add tests proving an unapproved, expired, wrong-subject or wrong-revision deviation cannot produce a releasable passing result.

### 13.8 CAPA creation is a multi-write operation without a transaction

**Finding:** QualityControlService::createCorrectiveAction() inserts a corrective-action record and then separately updates the related nonconformance status to CAPA_REQUIRED. The pair is not wrapped in one database transaction, and the nonconformance is not locked before the decision.

A failure between writes may leave an action created while the source nonconformance remains in its previous status.

**Source:** backend/app/Services/Quality/QualityControlService.php, createCorrectiveAction() (starts around line 168).

**Required implementation**
- [ ] Lock the nonconformance and validate its current state.
- [ ] Insert the action and update the nonconformance in one transaction.
- [ ] Add an idempotency/action-code replay rule and concurrency handling.
- [ ] Test mid-operation rollback, duplicate action creation, closed/resolved NC and concurrent CAPA creation.

### 13.9 Agent-run finalization is not bound to the initiating actor or trusted worker

**Finding:** AgentGovernanceController::finishRun() authorizes agent.propose-action but does not pass the current actor identity to the service. AgentGovernanceService::finishRun() selects any STARTED run within the current tenant by run ID and updates its terminal status, output hash, token usage and tool trace. It does not require the caller to be the run starter or a separately authorized runtime service identity.

The tenant filter protects cross-tenant isolation, but it does not protect attribution and run integrity within the tenant.

**Source**
- backend/app/Http/Controllers/Agents/AgentGovernanceController.php, finishRun() (around line 44).
- backend/app/Services/Agents/AgentGovernanceService.php, finishRun() (around line 39).
- backend/database/migrations/2026_11_26_000021_create_documents_integrations_and_agent_controls.php, agent_run_audits.started_by.

**Required implementation**
- [ ] Pass authenticated actor/service identity into finalization.
- [ ] Permit only the initiating actor or an authenticated, specifically authorized agent-runtime service principal to finalize the run.
- [ ] Record provider execution reference, runner identity, started/finished times, terminal reason and immutable tool trace.
- [ ] Make finalization idempotent for an identical terminal payload and reject conflicting second finalization.
- [ ] Add a test where one tenant member tries to finalize another member's run, including changing its output hash/tool trace.

### 13.10 Agent REQUEST_CHANGES is misrepresented and has no resubmission path

**Finding:** decideAction() accepts REQUEST_CHANGES and writes that decision to agent_approval_decisions. It then resets the proposal status to PROPOSED, but the agent_action_events record is written as REJECTED for every decision other than APPROVE. The event key is fixed as agent-approval:{proposalId}. The existing API exposes propose, decide and execute; no revise/resubmit operation is exposed for the same proposal.

The database event-type constraint also does not include REQUEST_CHANGES, so the service currently cannot record that distinct event type.

**Source**
- backend/app/Services/Agents/AgentGovernanceService.php, decideAction() (around lines 100–145).
- backend/app/Http/Controllers/Agents/AgentGovernanceController.php.
- backend/database/migrations/2026_11_26_000021_create_documents_integrations_and_agent_controls.php, agent_action_events.event_type and unique tenant/event key.

**Required implementation**
- [ ] Add proposal revision number and a controlled revise/resubmit transition with precondition revalidation.
- [ ] Preserve each decision and proposal revision in the audit trail.
- [ ] Add REQUEST_CHANGES to the supported event vocabulary in a forward migration.
- [ ] Use a per-decision or per-revision event key, not one immutable key for every approval attempt.
- [ ] Add API/UI support for author changes, resubmission, reapproval and expiry; test the complete cycle through execution.

### 13.11 Agent expected preconditions are stored but not evaluated

**Finding:** Proposals persist expected_preconditions, but executeApprovedDraft() does not evaluate that object before invoking the allow-listed domain service. Domain services do enforce some of their own current business invariants, but the proposal's explicitly approved precondition snapshot itself is unused.

**Source:** backend/app/Services/Agents/AgentGovernanceService.php, proposeAction() and executeApprovedDraft().

**Required implementation**
- [ ] Define typed precondition contracts for each capability.
- [ ] Re-read relevant records and compare the approved expected version/state immediately before execution.
- [ ] Reject stale proposals and require a new proposal/approval when assumptions have changed.
- [ ] Persist evaluated facts and a result hash in the execution event.
- [ ] Test stale supplier approval, changed order/inventory/quality state, expired source and concurrent modification.

---

## 14. Deep-service implementation and regression order

The source findings above should be addressed in this dependency order:

1. Inventory ledger exception resolution and idempotency fingerprint.
2. Finished-goods cumulative production-output reconciliation, release separation and reservation idempotency.
3. Customer-order atomic creation and replay semantics.
4. Quality deviation approval records, CAPA atomicity and release authority consistency.
5. Agent run attribution, proposal revision/REQUEST_CHANGES and precondition evaluation.
6. Add dedicated service, PostgreSQL feature and API tests for sourcing, manufacturing, quality, inventory, distribution, finance and agent workflows.
7. Run the PostgreSQL test suite on an actual exact source SHA; keep the source-level findings marked as inspected but unexecuted until the relevant tests have run.

No application service or database migration has been modified by this audit addendum. It adds exact code-path and acceptance-test work to the existing canonical implementation programme.


---

## 15. Finance and industrial-engineering control audit

### 15.1 Approved finance journal lines are protected against mutation in the current source

**Verified current source state: the core immutability control is already integrated.**

The first review of migration 000020 alone suggested that no database trigger froze journal lines after submission. A subsequent inspection of migration 000022 corrected that conclusion. Migration 000022 creates guard_finance_journal_line_mutation() and a BEFORE INSERT OR UPDATE OR DELETE trigger on finance_journal_lines. The trigger locks the parent journal row and permits line mutation only while the parent status is DRAFT; otherwise it raises a database exception.

**Source**
- backend/database/migrations/2026_11_26_000020_create_finance_and_payroll_review_foundation.php: journal and line tables, balanced-journal gate and grants.
- backend/database/migrations/2026_11_26_000022_add_receipt_traceability_links_and_immutability_guards.php: guard_finance_journal_line_mutation() and finance_journal_lines_draft_only.

This is an important existing control and should be retained. The remaining work is verification and test evidence, not reimplementation.

**Remaining actions**
- [ ] Add PostgreSQL feature tests proving INSERT/UPDATE/DELETE succeed in DRAFT and fail immediately after the journal enters UNDER_REVIEW, APPROVED or a later state.
- [ ] Verify the database trigger cannot be bypassed by the runtime role, bulk updates, alternate API paths or concurrent line writes racing submission.
- [ ] Verify the parent-row lock orders line changes against concurrent submit/approve/post transitions without deadlock or stale approval.
- [ ] Retain approved journal source/line provenance and prove reversal uses the immutable original posted snapshot.
- [ ] Keep the trigger in every supported clean-install and upgrade path; do not replace it with an application-only check.

### 15.2 Statutory rule approval does not require retained source-byte identity

**Finding:** statutory_rule_versions.source_sha256 is nullable. The database check for PROFESSIONAL_APPROVED requires verified_by, verified_at and professional_approval_reference, but it does not require a source hash or a retained source asset identity. A rule can therefore be marked PROFESSIONAL_APPROVED while its source_sha256 is NULL.

The WP-14 canonical payroll-draft selector also checks that a rule is effective on period_end. It does not prove that the selected rule covers the whole payroll period or create separate rule segments when a legal change takes effect during the period.

**Source**
- backend/database/migrations/2026_11_26_000020_create_finance_and_payroll_review_foundation.php: statutory_rule_versions definition and PROFESSIONAL_APPROVED check.
- backend/app/Services/Finance/FinanceControlService.php: createPayrollDraft() effective-date query and statutory-rule snapshot.

**Required implementation**
- [ ] Require an immutable source document/asset reference, SHA-256 of retained bytes, authority identity, exact provision/article reference, publication/effective dates and approval metadata before a rule may become PROFESSIONAL_APPROVED.
- [ ] Verify the actual retained source bytes and recompute the hash; a 64-character value or government URL alone is not proof of source authenticity.
- [ ] Define whole-period applicability. Either require a single rule version to cover period_start through period_end or split payroll into effective-date segments with an explicit transition method.
- [ ] Bind each payroll draft to immutable rule IDs, human-readable version IDs and source artifact hashes.
- [ ] Add tests for a rule that starts on period_end, expires before period_start, changes mid-period, has missing hash, has altered source bytes, or belongs to a mismatched jurisdiction/legal employer.

### 15.3 Finance reversal schema exists without an implemented service workflow

**Finding:** migration 000025 creates finance_journal_reversals with unique original-posting reversal and separate requester/approver identities. The inspected FinanceControlService, FinanceControlController and registered finance command routes do not expose a reversal-request/reversal-approval/reversal-posting workflow.

**Required implementation**
- [ ] Implement reversal draft creation linked to one immutable original posting.
- [ ] Validate same legal entity, currency/base-currency policy and an open reversal accounting period.
- [ ] Generate exact inverse lines from the immutable posted snapshot rather than accepting arbitrary client lines.
- [ ] Enforce separate requester/approver and a separate posting actor.
- [ ] Link the reversal journal, reversal posting and original posting with immutable provenance.
- [ ] Test partial and full reversals only where the approved accounting design explicitly allows them, duplicate reversal, concurrency and closed periods.

### 15.4 Finance domain lacks dedicated automated feature-test coverage

The delivery tree has FinanceControlService and FinanceControlController plus migrations 000020 and 000025, but no finance/payroll-specific PHP test file. The existing test inventory therefore does not prove journal maker-checker, balance trigger, immutable post, reversal, period close, statutory rule source verification or payroll-draft lifecycle end-to-end.

**Required implementation**
- [ ] Add PostgreSQL feature suites for journal create/submit/approve/post, line immutability, account/entity alignment, period closure, duplicate posting, reversal and tenant/entity boundary.
- [ ] Add statutory rule source-evidence and whole-period applicability tests.
- [ ] Add payroll draft rule snapshot, duplicate worker line, preparation/review separation, hold and source-hash tests.
- [ ] Reconcile AP/AR/inventory/costing/payroll source documents to journals and posted totals.

### 15.5 IE production planning can schedule an operation more than once or above its order quantity

**Finding:** IndustrialEngineeringService.addPlanLine() checks that an operation exists, belongs to the selected production order and is QUEUED or READY. It checks slot capacity and minimum planned minutes against approved standard minutes. It does not lock the operation, total existing production_plan_lines for that operation, or cap aggregate planned_quantity against the production_order_operations.planned_quantity.

The schema gives production_plan_lines a unique plan sequence, but no uniqueness or cumulative quantity invariant for an operation across plan versions. The same queued operation may therefore be scheduled repeatedly, including across separate plan versions, and planned quantity may exceed the authoritative operation quantity.

**Source**
- backend/app/Services/Planning/IndustrialEngineeringService.php, addPlanLine() (approximately lines 135–230).
- backend/database/migrations/2026_11_26_000019_create_ie_capacity_and_workforce_controls.php, production_plan_lines constraints and indexes.

**Required implementation**
- [ ] Lock the production operation while checking and recording planned quantity.
- [ ] Calculate active/scheduled quantity across plan versions with explicit treatment for superseded/cancelled plans.
- [ ] Reject cumulative scheduled quantity greater than remaining operation quantity.
- [ ] Add database enforcement or a serialized allocation record so concurrent planners cannot both reserve the same remaining quantity.
- [ ] Define replan, supersession and release of previously reserved capacity.
- [ ] Test same-plan duplicate, cross-plan duplicate, superseded plan, partial replan and concurrent planning.

### 15.6 Production plan and workforce assignment do not fully bind work-centre site to production-order site

**Finding:** addPlanLine() verifies the work-centre/capacity-slot relationship and verifies that the operation belongs to the selected production order. It does not compare the work centre's site_id to the production order's site_id.

assignWorkerToOperation() verifies that the chosen work centre and shift belong to the same site, but it does not load the production order and confirm that this site is the one assigned to the production order.

The same-tenant filters prevent cross-tenant lookup, but they do not prevent an intra-tenant cross-site production assignment.

**Required implementation**
- [ ] Resolve the production order's authorized site from its legal entity/order record.
- [ ] Require the work centre, capacity slot, shift and production-order site to match unless an explicit approved inter-site/subcontract transfer exists.
- [ ] Bind the production operation and assignment to site and legal entity in database constraints where feasible.
- [ ] Add positive same-site and negative cross-site tests for plan lines, workforce assignment, operation start and subcontract transfer.

### 15.7 Worker skill scope is stored but not enforced during assignment

**Finding:** workforce_skill_records stores authorized_operation_scope as JSONB. assignWorkerToOperation() validates that the worker has an active, competent and date-valid skill record and that skill_code matches the route step. The method does not evaluate authorized_operation_scope.

This means the record has a field intended to constrain where/how the worker may apply the skill, but the assignment source path currently uses only the skill code and validity dates.

**Required implementation**
- [ ] Define typed, versioned authorized-operation scope semantics (site, department, work centre, machine family, operation/process type, style/category and any mandatory safety qualification).
- [ ] Evaluate the full scope in worker assignment and again when the operation starts.
- [ ] Require a human-approved assessment/qualification source for restricted or safety-critical operations.
- [ ] Add scope-positive/scope-negative, expired qualification and changed-role tests.

### 15.8 Workforce overlap is not revalidated at confirmation

**Finding:** assignWorkerToOperation() takes a tenant/worker advisory transaction lock but searches overlaps only in CONFIRMED and STARTED assignments. Newly created assignments are PLANNED, so more than one overlapping planned assignment can be inserted. confirmWorkerAssignment() locks the individual assignment, checks it is PLANNED and that the confirmer differs from the creator, but it does not take the same worker advisory lock or recheck overlapping assignments before changing status to CONFIRMED.

Two overlapping PLANNED assignments may therefore both be independently confirmed unless an external constraint prevents it; the inspected migration contains no PostgreSQL exclusion constraint over worker time ranges.

**Required implementation**
- [ ] Take the same worker-level advisory lock during confirmation.
- [ ] Recheck overlapping CONFIRMED/STARTED assignments within the transaction immediately before status transition.
- [ ] Define whether overlapping PLANNED assignments are allowed as tentative alternatives; only one compatible allocation may become confirmed.
- [ ] Add a database range/exclusion or serialized invariant appropriate to PostgreSQL.
- [ ] Test two concurrent confirmations for overlapping work, shift boundary, overnight shift, partial cancellation and worker reallocation.

---

## 16. Finance and IE regression sequence

1. Add the finance journal-line immutable-after-submit trigger and approval snapshot hash.
2. Add statutory source-byte identity and whole-period applicability/version segmentation.
3. Implement the reversal request/approval/posting workflow.
4. Add complete finance PostgreSQL feature suites.
5. Add cumulative operation quantity allocation and plan version reservation/release behavior.
6. Enforce production-order/work-centre/shift site identity.
7. Enforce authorized-operation scope for worker skill records.
8. Serialize worker confirmation and prevent overlapping active assignments.
9. Add IE plan, capacity and workforce concurrency tests and run them against PostgreSQL on an exact commit SHA.

No finance, IE or runtime source has been modified by this audit section. It extends the canonical work queue with concrete data-integrity and workflow controls.


---

## 17. Manufacturing and quality source/schema workflow alignment

### 17.1 A passing inspection cannot be submitted before its separate review

**Finding:** The quality inspection table's database constraint and the service's intended two-person workflow are incompatible.

Migration 000017 defines:

- A result_status CHECK list that permits DRAFT, IN_PROGRESS, PASS, PASS_WITH_DEVIATION, FAIL, CONTAINED and CANCELLED.
- A second CHECK requiring reviewed_by and reviewed_at whenever result_status is PASS or PASS_WITH_DEVIATION.

QualityControlService.submitInspectionResult() is the inspector's first submission step. It validates the sample quantities and then updates result_status to PASS or PASS_WITH_DEVIATION while setting inspected_at, but it does not and must not supply reviewer fields. QualityControlService.reviewInspection() is the separate subsequent operation that checks the reviewer is not the inspector and then populates reviewed_by/reviewed_at.

Consequently, a clean PostgreSQL database rejects the initial PASS/PASS_WITH_DEVIATION update before reviewInspection() can be called. The table allows a DRAFT inspection; the incompatible point is the intermediate submitted-pass state.

**Source**
- backend/database/migrations/2026_11_26_000017_create_quality_capa_and_release_gates.php, quality_inspections CHECK constraints around lines 63–64.
- backend/app/Services/Quality/QualityControlService.php, submitInspectionResult() around lines 42–112 and reviewInspection() around lines 114–160.
- Material release and finished-goods receipt both require reviewed passing inspection records, so this condition prevents the normal accepted-quality path from completing.

**Required implementation**
- [ ] Separate inspection-result status from independent review status, or introduce an explicit SUBMITTED_PENDING_REVIEW state.
- [ ] Permit an inspector to save a completed PASS/PASS_WITH_DEVIATION result with reviewer fields absent.
- [ ] Require reviewer identity and time only when the independent review decision is recorded.
- [ ] Keep the transition to releasable PASS gated by the independent reviewer and any applicable deviation approval.
- [ ] Add a PostgreSQL migration/feature test proving DRAFT → inspector submits PASS → different reviewer accepts → material/operation/finished-goods release checks can consume that reviewed result.
- [ ] Add negative tests proving the inspector cannot review their own result and no material/finished-goods release can occur before review.

### 17.2 Site and legal-entity identity are independently tenant-scoped, not mutually bound for production orders

**Finding:** production_orders has separate composite tenant foreign keys for legal_entity_id and site_id. The service inserts both supplied IDs but does not load and verify the selected business site belongs to the selected legal entity. business_sites permits a nullable legal_entity_id and its unique composite key supports explicit entity binding, but production_orders does not use a composite foreign key tying its legal_entity_id to the site's legal_entity_id.

A production order can therefore be associated with a tenant legal entity and a different tenant site unless a separate service/approval invariant is added.

**Source**
- backend/database/migrations/2026_11_22_000001_create_enterprise_foundation_tables.php, business_sites has tenant_id, nullable legal_entity_id and a unique (tenant_id, id, legal_entity_id) key.
- backend/database/migrations/2026_11_26_000016_create_route_and_manufacturing_execution.php, production_orders has independent tenant/legal-entity and tenant/site foreign keys.
- backend/app/Services/Manufacturing/ManufacturingExecutionService.php, createProductionOrder() inserts the supplied legal_entity_id and site_id after checking tech pack/route style revision only.

**Required implementation**
- [ ] Resolve the business site before order creation and require site.legal_entity_id to equal the order legal entity, except for an explicitly defined shared-site model with a separately authorized operating entity.
- [ ] Prefer a composite database relationship/constraint or trigger so another service cannot create the mismatch.
- [ ] Apply the same entity/site binding to work centres, production plans, workforce assignments, material locations and finished-goods locations.
- [ ] Add cross-entity, shared-site and valid entity/site tests.

### 17.3 Route condition expressions are stored but not evaluated

**Finding:** addRouteSteps() requires condition_expression for optional steps and stores it as JSON. recordOperationEvent() checks only whether a route step is optional and whether a skip reason exists. The route execution logic uses a single predecessor_step_no and linear step ordering; it does not evaluate condition_expression to determine applicability, branch selection or whether an optional step may be skipped for the selected style/material/customer/process.

This means the stored condition definition is not yet an executable route policy.

**Required implementation**
- [ ] Define a typed, versioned condition schema and deterministic evaluator.
- [ ] Bind condition inputs to immutable production-order facts and approved product/BOM/material/customer/site context.
- [ ] Evaluate step applicability at route release and again at execution when any relevant precondition could have changed.
- [ ] Record evaluation inputs, result and policy version in the order/route snapshot.
- [ ] Require an approved waiver/deviation for bypass of a required or quality-gated operation.
- [ ] Ensure an optional skipped quality-gate step has an explicit, valid bypass decision before downstream progression.
- [ ] Add route tests for conditional print, embroidery, wash, special process, subcontract, skip/bypass, stale condition input and quality-gate behavior.

### 17.4 Cutting-bundle tables do not yet have a domain service/controller workflow

**Finding:** migration 000016 creates cutting_bundles and bundle_operation_events with tenant RLS, unique bundle/event keys and roll/order references. The inspected source tree has no dedicated CuttingBundle service or controller; the current ManufacturingExecutionService does not create or manage these records and the registered ManufacturingController exposes route, production-order and production-event commands only.

The schema is a foundation, not a working cutting-room bundle traceability workflow.

**Required implementation**
- [ ] Implement cutting order/lay/marker/roll allocation and bundle creation.
- [ ] Bind each bundle to production order, approved style revision, fabric roll, shade/lot, size breakdown, marker/cut plan and cut operator.
- [ ] Validate fabric-roll issue/balance and panel/yield quantities before committing bundle output.
- [ ] Implement bundle scan-in, operation completion, defect, rework, transfer, hold and release events with payload-aware idempotency.
- [ ] Connect bundle genealogy to sewing operation start/output and finished-goods/piece genealogy.
- [ ] Build cutting-room UI and device/offline scan path.
- [ ] Add tests for roll depletion, shade separation, duplicate scan, partial bundle, re-cut, rework, loss, tenant/site boundary and full roll-to-bundle-to-finished-lot genealogy.

### 17.5 Manufacturing event replay needs payload-aware idempotency and a post-lock recheck

**Finding:** recordOperationEvent() first looks up event_key and returns an existing event without comparing the incoming payload. It then locks the order and operation but does not repeat the event-key lookup after those locks. The event table has a unique (tenant_id, event_key) constraint, so an identical concurrent replay can cause the second request to hit a uniqueness error after the first commits; a conflicting replay returns the first event without identifying the payload conflict.

**Required implementation**
- [ ] Add a canonical event payload hash (order, operation, type, quantity, downtime, reason, device, operator and occurred-at context).
- [ ] Recheck event key after taking serialization locks and return the original event only when the payload hash matches.
- [ ] Reject conflicting replays with a deterministic idempotency-conflict result.
- [ ] Make device/offline event replay use the same domain event identity and reconciliation state.
- [ ] Add concurrent identical retry, conflicting retry, out-of-order event and duplicate scan tests.

---

## 18. Manufacturing/quality implementation sequence

1. Correct quality inspection result/review state design and its PostgreSQL constraint.
2. Add deviation approval records and keep the reviewer/releaser separation.
3. Enforce production-order legal-entity/site binding.
4. Implement the route condition evaluator and approved skip/waiver path.
5. Implement cutting-bundle service/controller/UI/scan workflow.
6. Add manufacturing event payload hash, post-lock replay check and deterministic idempotency conflict.
7. Add complete quality and manufacturing PostgreSQL feature suites and execute them on an exact commit SHA.

No manufacturing/quality runtime source has been changed by this audit update. It records the schema/service corrections and acceptance evidence required to complete the corresponding work packages.


---

## 19. Sourcing, material receiving and integration runtime audit

### 19.1 WP-07 has real supplier/PO/receipt/lot slices, but sourcing event and quotation execution is absent

**Verified source**
- backend/app/Services/Sourcing/SourcingService.php
- backend/app/Http/Controllers/Sourcing/SourcingController.php
- backend/routes/domain-api.php, v1/sourcing
- backend/database/migrations/2026_11_26_000015_create_sourcing_material_traceability.php
- docs/procurement/supplier-sourcing-material-traceability.md

The current service implements supplier creation/approval, PO create/submit/decide/send, goods receipt and inspected receipt-line materialization. It does not implement methods for creating/issuing/closing sourcing events, issuing RFQs/RFPs, capturing supplier quotes, evaluating comparative bids, recording an award or managing supplier-quote review. The sourcing_events and supplier_quotes tables exist, but the service/controller/route inventory does not expose these lifecycle operations.

The WP-07 design document describes the full supplier qualification → requisition → RFQ/tender/quote → technical/commercial evaluation → award → PO → receipt → QC → AP/3-way-match lifecycle and explicitly states that operational implementation follows in vertical slices. The current implementation should therefore be treated as the supplier/PO/receipt foundation, not a complete sourcing-to-pay workflow.

**Remaining implementation**
- [ ] Implement versioned sourcing event create/submit/issue/close lifecycle, invitation/response windows and allowed supplier/category scope.
- [ ] Implement supplier quote submission and controlled revisions, source-document evidence, price/lead-time/validity/incoterm data and independent review.
- [ ] Implement technical compliance and commercial comparison as separate typed evaluation results, preserving evaluator inputs and conflicts of interest.
- [ ] Implement award recommendation/decision with requester/buyer/evaluator/approver segregation and a reasoned, immutable decision record.
- [ ] When purchase_orders.sourcing_event_id is supplied, validate the event status and selected approved supplier quote/award; preserve an explicit policy path for legitimate direct purchases and emergency exceptions.
- [ ] Build the Sourcing UI: supplier qualification queues, RFQ workspace, quote comparison, award approvals, PO schedule/change orders and receipt exceptions.
- [ ] Add PostgreSQL feature tests from supplier discovery through quote/award/PO, including expiry, conflict, supplier suspension and tenant/site scope.

### 19.2 Purchase-order rejection and dispatch evidence are not represented accurately

**Finding A — reject versus cancel:** decidePurchaseOrder(..., approve: false, ...) changes the PO status to CANCELLED, while also populating approved_by and approved_at. The schema has no distinct REJECTED decision status or decision actor/reason fields. A rejected submission is therefore represented as a cancellation and an approval-named event, reducing audit clarity.

**Finding B — sent event attribution:** markPurchaseOrderSent() accepts only tenant and order IDs. The controller does not pass the authenticated actor. The PO schema has no sent_by, sent_at, external transmission reference or supplier acknowledgement state. The method changes status to SENT without proving who dispatched which approved revision or whether the supplier received/acknowledged it.

**Required implementation**
- [ ] Separate rejection from post-approval cancellation in the state model and preserve decision, reason, actor and timestamp.
- [ ] Add a versioned PO dispatch event with authenticated sender, exact approved PO revision/hash, transmission channel/provider reference, attempted/sent timestamp and supplier acknowledgement/exception.
- [ ] Make resend/retry idempotent and prevent dispatch of an unapproved or superseded revision.
- [ ] Test reject, cancel, dispatch retry, amendment after approval and supplier acknowledgement.

### 19.3 Goods-receipt status and non-inventory service receipt are incomplete

**Finding A — receipt header lifecycle:** recordGoodsReceipt() creates a goods_receipts row in INSPECTION_PENDING. materializeInspectedReceiptLine() creates a lot and updates receipt-line quantities and PO-line accepted quantities, but does not update the corresponding goods-receipt header to ACCEPTED, PART_ACCEPTED or REJECTED after its lines are inspected/materialized. The table includes those terminal/aggregate states, but the service path does not finish the header lifecycle.

**Finding B — service/subcontract receipt:** PO line schema and controller accept SERVICE and SUBCONTRACT_PROCESS material types. materializeInspectedReceiptLine() explicitly rejects those types because they do not become inventory material lots. The inspected SourcingService has no separate service-performance/quantity acceptance method to complete those receipt lines, update accepted quantities or close a service/subcontract PO.

**Finding C — legal-entity/site binding:** PO stores a legal_entity_id; goods receipt accepts any tenant-scoped destination inventory location of type QUARANTINE. It does not resolve the location's operational node/site and verify that it is authorized for the PO legal entity/delivery site. Tenant isolation alone is not sufficient to enforce the procurement destination and custody boundary.

**Required implementation**
- [ ] Derive receipt header status transactionally from every receipt line's accepted/rejected/held quantities and inspection state; support partial line receipts and multi-line receipts.
- [ ] Add separate non-inventory acceptance for service/subcontract milestones, work evidence, measured quantity, quality/service approval, contract deliverable and AP handoff.
- [ ] Validate destination site/operational node/legal entity against the PO and supplier delivery scope, allowing cross-site exceptions only through an approved transfer/authorization model.
- [ ] Add receipt code/request idempotency with payload fingerprint; identical retries return the original receipt, conflicting retries are rejected.
- [ ] Add tests for mixed material/service POs, partial accepted/rejected quantities, multiple receipt events, closed PO, cancelled receipt, cross-site destination and duplicate receipt retry.

### 19.4 Supplier and PO governance evidence is reference-only and needs source linkage

**Finding:** supplier approval requires two non-empty reference strings and a reviewer different from the supplier creator. The supplier row stores both references, but the inspected approval path does not bind them to verified document assets, document checksums, evidence type, supplier site/category scope, validity period or expiry/review date. It sets both due-diligence and quality status to APPROVED in one operation.

This is a useful initial gate, but the richer WP-07 supplier design requires separate due-diligence, technical/quality, site/process, legal and financial evidence with independent owners and expiry. A reference string is not itself evidence verification.

**Required implementation**
- [ ] Link each approval assertion to tenant-scoped document assets and controlled source metadata/hash.
- [ ] Separate due diligence, technical/quality, legal/site/HSE and financial approval tracks where required by supplier risk/category.
- [ ] Scope qualification to supplier site, material/process family, customer/style restriction, quantity and effective dates.
- [ ] Add expiry, suspension, conditional approval, trial quantity and periodic requalification workflows.
- [ ] Block new PO/award when the relevant qualification is expired, suspended or outside the approved supply scope.

### 19.5 Integration outbox has state transitions but lacks complete delivery runtime and recovery

**Verified source**
- backend/app/Services/Integration/IntegrationOutboxService.php
- backend/app/Services/Integration/DocumentIntegrationService.php
- backend/database/migrations/2026_11_26_000021_create_documents_integrations_and_agent_controls.php
- backend/config/queue.php
- backend/.env.example
- backend/routes/domain-api.php

IntegrationOutboxService implements claimBatch() with FOR UPDATE SKIP LOCKED, markDelivered() and markFailed() with exponential backoff/dead-letter transition. integration_endpoint_registry and integration_outbox_events are tenant-RLS tables. These are real foundations. The inspected source tree has no outbox consumer Job/Command/Listener/worker, and the API route inventory has no integration endpoint/connection administration path to configure and activate connectors. The default application queue is sync.

**Remaining implementation**
- [ ] Implement endpoint/connection lifecycle: tenant/entity scope, provider type, event allow-list, secret-vault reference, permission scopes, environment, approval, verification, activation/suspension and credential rotation.
- [ ] Implement typed event contracts, schema versions, payload validation and compatibility policy.
- [ ] Implement actual outbound adapter execution, response capture, signing/authentication, rate limit, timeout and retry policy.
- [ ] Bind outbox enqueue to the originating business transaction using the transactional outbox pattern.
- [ ] Compare payload hash and canonical request identity on duplicate event_key; return identical event only for identical payload and reject conflicting replay.
- [ ] Add PROCESSING lease expiry, worker identity/fencing token and safe recovery for a worker that crashes after claiming an event.
- [ ] Add per-endpoint delivery metrics, queue lag, alert, dead-letter triage and approved replay UI/API.
- [ ] Add inbox/webhook receipt, signature, timestamp/replay validation and consumer-side deduplication.
- [ ] Add end-to-end tests for publish once, duplicate identical event, conflicting duplicate, timeout-after-remote-success, worker crash, lease recovery, dead letter and replay.

### 19.6 Document upload records metadata but does not yet prove stored-object integrity or complete extraction/import

**Finding A — metadata registration:** registerDocument() validates that a submitted SHA-256 string has the right shape and stores the caller-supplied object-storage reference. It does not retrieve that object, verify its byte size/hash or set checksum_verified_at. The upload path computes the local uploaded-file hash, but it also does not read the stored object back and verify the stored bytes.

**Finding B — failed metadata write:** storeUploadedFile() writes the object first and then inserts the document row. If the metadata insert fails, there is no compensating object cleanup/reconciliation path; orphan private objects can remain.

**Finding C — extraction execution:** beginExtraction() inserts a QUEUED extraction run. There is no extraction worker/provider invocation in the inspected source tree. stageExtractionResult() accepts result payload from an authorized API principal, but does not record a distinct provider worker identity or provider execution reference. reviewExtraction() can set APPROVED_FOR_IMPORT, but the inspected code has no approved-values import adapter that turns those values into a domain record through a typed domain command.

**Required implementation**
- [ ] Verify existence, byte size, media identity and SHA-256 by reading the saved object from the configured private disk before setting checksum_verified_at.
- [ ] Store a canonical storage-object version/reference and immutable document metadata snapshot; detect object replacement or deletion.
- [ ] Add compensating deletion/orphan reconciliation when upload succeeds but metadata persistence fails.
- [ ] Use malware scanning, content disarm/reconstruction where applicable and quarantined file state before normal retrieval/extraction.
- [ ] Add an authenticated extraction worker identity, job claim/retry/lease state, provider/model/version/correlation, input hash, output hash and reproducible source locations.
- [ ] Validate extraction fields against a versioned profile and proposed target-domain schema; reject unknown/low-confidence/malformed values.
- [ ] Implement human-approved import via the appropriate domain service and persist source-document-to-imported-record lineage; approval alone must not imply that a record was imported.
- [ ] Test modified bytes, missing object, orphan upload, unsupported content, malware result, duplicated extraction run, stale worker result, conflicting provider result and approved import replay.

### 19.7 Device scan resolution is intentionally read-only, but device identity and event integrity remain to be built

**Verified behavior:** DeviceScanResolutionService::resolve() performs tenant-scoped lookups for roll, trim, cutting bundle, finished-goods lot, customer order and shipment identifiers. It marks a scan RESOLVED only when exactly one candidate exists, AMBIGUOUS for multiple matches and REJECTED when no match exists. The service explicitly performs no inventory or production action. This is a safe identifier-resolution foundation.

**Remaining implementation**
- [ ] Create a registered device identity and tenant/site/work-centre binding; do not treat the client-supplied device_reference as authentication.
- [ ] Authenticate and authorize device/service credentials separately from a normal human bearer token where applicable.
- [ ] Validate device time skew, clock source, offline sequence and replay window; store raw signed envelope and normalized time.
- [ ] Add payload fingerprint conflict detection for duplicated device_scan_events.event_key.
- [ ] Implement a separate explicit domain command after resolution, with user/device authority, resource state checks and inventory/production idempotency. Resolution alone must never mutate business state.
- [ ] Build the offline queue, reconciliation/exception UI, revoked-device handling, duplicate scan and out-of-order replay tests.

---

## 20. Sourcing/integration implementation order

1. Complete RFQ/quote/evaluation/award workflow and connect it to authorized PO creation.
2. Add distinct PO rejection/cancellation and auditable dispatch/acknowledgement.
3. Complete goods-receipt header state, service/subcontract acceptance and entity/site destination validation.
4. Bind supplier qualification evidence to verified documents and scoped expiry.
5. Build outbox endpoint administration, actual adapter workers, lease recovery and payload-aware idempotency.
6. Complete private object hash verification, orphan cleanup, extraction worker and approved domain-import pipeline.
7. Implement registered/authenticated device identity while preserving read-only scan resolution until an explicit domain command is authorized.
8. Add sourcing and integration PostgreSQL/API/E2E tests and update the WP-07/WP-15 operator UI.


---

## 21. WP-05 product-development source and downstream release audit

### 21.1 Existing WP-05 implementation is a real vertical slice

The audited delivery includes:
- Product brief and style APIs in backend/app/Http/Controllers/ProductDevelopment/ProductDevelopmentController.php.
- Measurement specifications, pattern sets, pattern revisions and grade-rule validation in MeasurementPatternController.php.
- Sample requests, sample rounds, fit sessions, observations and independent fit disposition in SampleFitController.php.
- BOM/technical-pack service and controller in BomTechnicalPackService.php and BomTechnicalPackController.php.
- Angular feature surfaces for product development, technical product development and sample/fit.

The current code includes important controls:
- Briefs derive from a frozen range line/current approved collection and active brand strategy.
- Brief decisions are separated from the brief owner.
- Style creation requires the accepted/current brief version.
- Measurement specs are versioned against style revision; required points need values for every ordered size and base size must exist in the sequence.
- Pattern revisions pin to approved measurement versions and require complete adjacent-size grade rules for every required point.
- Sample rounds are sequenced; fit observations use the pinned measurement version and require every required measurement point before review.
- Fit disposition is independent from the session creator and checks outside-tolerance/major/critical findings against the chosen decision.
- BOM and technical pack approvals are separate from their authors; tech pack creation pins to exact approved BOM, measurement, pattern and fit-approved sample versions.

These are verified source characteristics, not a claim that the full apparel PLM lifecycle or factory acceptance is complete.

### 21.2 Technical-pack release readiness is permanently blocked by unimplemented downstream dependencies

**Finding:** BomTechnicalPackService.releaseReadiness() appends five unconditional blockers for every technical pack:
- OBJECT_STORAGE_NOT_VERIFIED (WP-15);
- INDUSTRIAL_ENGINEERING_NOT_VERIFIED (WP-06);
- ROUTE_AND_SOP_NOT_VERIFIED (WP-08);
- QUALITY_RELEASE_NOT_VERIFIED (WP-11);
- PRODUCT_COSTING_NOT_VERIFIED (WP-13).

They are appended on every call, with no query for linked evidence that could satisfy them and no stored approval/evidence relationship that can clear them. Therefore release_ready can never become true in the present service implementation, and BomTechnicalPackService.release() always returns released=false.

This is an explicit integration hold in the code. The right build action is not to delete the blockers; it is to implement the dependency contracts and clear each blocker only from verified, version-pinned records.

**Source:** backend/app/Services/ProductDevelopment/BomTechnicalPackService.php, releaseReadiness() and release().

**Required implementation**
- [ ] Define versioned readiness contracts for IE operation standards/SAM, route/SOP, quality plan, costing and private asset integrity.
- [ ] Resolve each dependency against the exact style revision, BOM version, sample/measurement/pattern version, site/customer/process scope and current effective approval.
- [ ] Return per-dependency evidence references, approver, effective date, source SHA/document identity and validation result.
- [ ] Replace hard-coded blockers only after each dependent package exposes a real query/command contract and positive/negative tests.
- [ ] Preserve immutable release manifest inputs and record every dependency version/hash in the final technical release manifest.
- [ ] Add a full dependency test where each missing/expired/superseded dependency blocks release and all approved dependencies permit release.

### 21.3 WP-10 production-order creation accepts APPROVED even when tech-pack release is blocked

**Finding:** ManufacturingExecutionService.createProductionOrder() accepts the linked technical pack when its status is either APPROVED or RELEASED. The WP-05 release service does not currently permit any tech pack to reach RELEASED because of the permanent downstream blockers above. Consequently the production-order creation path can proceed using APPROVED, bypassing the technical-pack release gate that was intended to prove the complete product passport is ready for production.

The production-order release method also checks that the route and tech pack are approved/released and that at least one operation exists, but it does not query the WP-06 IE standard, WP-07 verified material resolution, WP-08 released route/SOP snapshot, WP-11 quality plan or WP-13 approved cost snapshot as linked dependency evidence.

**Source**
- backend/app/Services/Manufacturing/ManufacturingExecutionService.php, createProductionOrder() and releaseProductionOrder().
- backend/app/Services/ProductDevelopment/BomTechnicalPackService.php, releaseReadiness() and release().

**Required implementation**
- [ ] Define an explicit transition from APPROVED to RELEASED that records all required downstream approvals.
- [ ] Require the actual production release state (not merely technical approval) before production-order creation or release, once the WP dependency gates are implemented.
- [ ] Validate production order style revision, BOM, measurement, pattern, sample/fit, IE, material, route, quality and cost dependency IDs against the same immutable product passport.
- [ ] Enforce the dependency relationship at the service boundary and add database references/constraints where appropriate.
- [ ] Add a negative E2E test proving an approved but unreleased tech pack cannot start production; add a positive test for a fully released product passport.
- [ ] Preserve any explicitly approved sample/prototype production path as a different order purpose/type with quantity limits and separate route/material controls, rather than silently weakening the bulk-production release gate.

### 21.4 BOM material resolution has no WP-07 bridge in the current source

**Finding:** BOM versions are created with material_resolution_status UNVERIFIED_WP07. The current service requires this state while submitting the BOM, and releaseReadiness() blocks release until the BOM status becomes VERIFIED. The inspected SourcingService creates suppliers, purchase orders, receipts, lots, rolls and trims, but does not resolve BOM component references to approved material master identities, unit conversions, supplier qualification scope, inventory availability or effective material specifications. BomTechnicalPackService also has no material-resolution method.

Thus, the state transition from the BOM placeholder/reference to a WP-07-verified material identity is not implemented in the inspected canonical source.

The additional state-machine inspection establishes why this is not just a missing UI action: the migration's guard_style_bom_version_state() trigger only permits DRAFT → PENDING_APPROVAL and PENDING_APPROVAL → APPROVED/REJECTED transitions, and both branches require material_resolution_status to remain unchanged from OLD. There is no authorized DRAFT → DRAFT material-resolution update or dedicated WP-07 verification transition. createBomVersion() always sets UNVERIFIED_WP07; submitBom() requires that same value; releaseReadiness() later requires VERIFIED. A new WP-07 verification record linked to immutable BOM-line snapshots is therefore safer than mutating an engineering BOM after submission. If a status-column transition is retained, it requires a narrow, authorized, audited forward migration with exact old/new predicates, not a weakening of the existing BOM immutability guard.


**Required implementation**
- [ ] Create a material master/reference model and controlled component mapping for every BOM line.
- [ ] Validate unit of measure, base/issue unit conversion, approved specification/revision, material family, supplier qualification and relevant customer/brand restriction.
- [ ] Reconcile required consumption, allowance/waste and planned production quantity to sourcing demand and available/on-order/in-transit material.
- [ ] Record approved substitution as a separate quality-reviewed link to the BOM line; never overwrite the original component reference.
- [ ] Transition material_resolution_status to VERIFIED only when every required line has a valid approved mapping and source evidence; include mapping snapshot hash.
- [ ] Add tests for unknown material, expired spec, incompatible unit, blocked supplier, unapproved substitute, shortage and superseded BOM.

### 21.5 Technical asset rights approval is metadata-only until the WP-15 document store is integrated

**Finding:** Tech-pack assets store asset_reference, filename, MIME type, size, a SHA-256-shaped value and declared usage-rights metadata. The service computes asset_manifest_sha256 over those metadata records and reviewer decisions. It does not prove that the referenced object exists, that the actual bytes match the declared hash/size, or that an immutable object version is retained. The current document service has a separate document_assets model, but tech_pack_assets does not have an enforced document-asset linkage in the inspected source.

**Required implementation**
- [ ] Link the tech-pack asset row to an immutable tenant-scoped document asset/object version.
- [ ] Recompute the hash from the stored object and verify byte size/MIME before rights review or release.
- [ ] Keep rights-owner, licence/permission basis, territory, purpose, derivative rights, customer restrictions and expiry as a structured review record.
- [ ] Re-verify current rights and storage integrity at release; do not rely only on the metadata manifest created at submission.
- [ ] Add object missing/altered/hash mismatch/expired rights/replaced version and cross-tenant asset tests.

### 21.6 WP-05 backend acceptance tests are absent from the current test tree

The frontend has a product-development service spec, but the inspected backend/tests tree has no dedicated product-development feature test file. The product-development controllers and BomTechnicalPackService therefore have no package-specific PostgreSQL feature suite in the canonical test inventory to prove their multi-table transactions, row locks, approval segregation, version pinning, dependency gates and RLS behavior.

**Required implementation**
- [ ] Add backend Feature suites for brief/style, measurement/pattern, sample/fit and BOM/technical-pack release.
- [ ] Test stale collection/brief/style revision, incomplete size grids, incomplete grade-rule coverage, incorrect unit, stale pattern, missing requested fit size, missing observation and wrong fit disposition.
- [ ] Test creator cannot approve own brief/measurement/pattern/BOM/tech pack/asset/fit; test current accepted revision remains pinned.
- [ ] Test all release dependencies both absent and present, and test a changed dependency invalidates a stale tech pack release attempt.
- [ ] Test tenant isolation, authorization, concurrent version creation and audit-event atomicity.
- [ ] Add browser E2E covering a real product brief → style → measurement → pattern → sample/fit → BOM → technical pack → readiness → release journey.

---


### 21.7 BOM substitution candidates cannot reach quality approval or withdrawal

**Finding:** a substitution candidate is created as PROPOSED with quality_approval_status = PENDING_WP11. The migration constraint permits only PENDING_WP11 for that field. proposeSubstitution() is the only exposed substitution method in BomTechnicalPackService; the inspected QualityControlService and QualityController have no substitution-review/approval action. releaseReadiness() blocks every candidate whose approval status is not APPROVED, so a proposed substitution becomes a release blocker. The candidate status contract includes WITHDRAWN, but no service/controller action performs that transition.

**Required implementation**
- [ ] Add a WP-11 quality review record/decision with reviewer, approved alternative material identity, equivalence/test evidence, affected BOM line/revision, conditions and effective period.
- [ ] Add a forward migration allowing the actual decision states and requiring decision metadata for APPROVED/REJECTED/WITHDRAWN; preserve maker-checker and immutable decision history.
- [ ] Add controlled withdraw/revise/resubmit actions for the proposer or authorized style owner while the BOM is still eligible for revision.
- [ ] Ensure a substitute is not treated as the selected material merely because a candidate was proposed; the selected line and approved alternative must be explicit.
- [ ] Test quality approval/rejection, expired approval, wrong BOM version, withdrawn candidate, changed substitute, tenant boundary and release-readiness impact.

### 21.8 Technical-pack state contract has an unreachable RELEASE_BLOCKED value

**Finding:** migration 000012's tech_pack_versions_decision_chk includes RELEASE_BLOCKED in its branch where decision fields must be null, but tech_pack_versions_status_chk does not include RELEASE_BLOCKED in the allowed status list. The status is therefore not a legal persisted state despite being referenced by another constraint.

**Required implementation**
- [ ] Decide whether release readiness is represented only as a computed response or as a persistent lifecycle status.
- [ ] If computed, remove the unreachable RELEASE_BLOCKED branch in a forward migration and keep release blockers in a dedicated evidence/readiness result.
- [ ] If persisted, add it intentionally to the status constraint and define legal transitions, retry/reassessment, audit and decision-field requirements.
- [ ] Add migration tests that exercise every allowed status and transition.


## 22. WP-05 / WP-10 implementation sequence

1. Implement material master mapping and BOM resolution with WP-07.
2. Complete stored-object integrity/rights verification with WP-15.
3. Implement effective IE, route/SOP, quality-plan and costing dependency contracts.
4. Make technical-pack readiness evidence-based and release only the exact complete product passport.
5. Require released tech-pack state for bulk production order creation/release; provide an explicit bounded prototype route where needed.
6. Add WP-05 PostgreSQL feature suites and the complete product passport browser E2E journey.
7. Add the WP-11 substitution approval/withdrawal lifecycle and its forward migration constraints.
8. Resolve the RELEASE_BLOCKED status/decision constraint mismatch in a forward migration.
9. Add the same exact dependency/hash references to manufacturing order start and finished-goods genealogy.

No WP-05 or WP-10 runtime source has been modified by this audit addendum. It extends the canonical source-to-plan implementation register with cross-package release controls and acceptance evidence.


---

## 23. WP-04 merchandising, forecast, OTB and commercial pricing integration audit

### 23.1 Current WP-04 source includes meaningful planning and governance

The current canonical source exposes initiative, evidence policy, market insight, brand strategy, season/collection, range, assortment, forecast, OTB, price list, promotion and account-term APIs.

Useful controls already present:
- initiative stage decisions and owner/approver separation;
- evidence policies versioned by initiative type, stage and market, with age/confidence/source-reliability/geography/segment/sample-denominator requirements;
- per-initiative evidence sufficiency evaluation rather than a misleading tenant-wide percentage;
- market insight source-event idempotency with payload SHA-256, advisory lock, conflicting payload rejection and duplicate receipt audit;
- frozen range-plan dependency for product briefs;
- OTB policies with effective dates, formula version, consistent budget/valuation basis, commitment and receipt inclusion rules, and non-overlapping active policy periods;
- PostgreSQL NUMERIC OTB calculation avoiding binary floating-point arithmetic;
- OTB scenarios explicitly labelled non-posting planning simulations which do not create purchase orders or accounting entries;
- effective-dated price lists, price floors, independent publication and overlap checks by legal entity/channel/market/currency/brand/account;
- promotions restricted to a published price list, explicit positive eligibility scope, independent approval and no-stack overlap checks;
- assortment account terms with scoped review.

These controls should be preserved as WP-04 is connected to real procurement, inventory, commerce and finance records.

### 23.2 Forecast publication accepts supplied forecast rows; no statistical forecast execution pipeline is present in the inspected source

**Finding:** DemandPlanningController.publishRun() accepts forecast rows from the caller, accepts a method value of MANUAL_CONSENSUS, STATISTICAL_BASELINE or EXTERNAL_MODEL_IMPORT, accepts an optional model_version, and persists the forecast run immediately with status PUBLISHED. The inspected delivery tree has no demand-forecast model service, feature pipeline, model worker, statistical baseline implementation or external-model ingestion adapter.

This is a valid manual forecast publication/import foundation. It is not evidence that a statistical forecast was computed or an external model ran simply because the method string is supplied.

**Source**
- backend/app/Http/Controllers/Merchandising/DemandPlanningController.php, publishRun(), runs() and showRun().
- backend/database/migrations/2026_11_22_000005_create_brand_range_assortment_planning_tables.php, demand forecast run and row tables.
- The inspected service tree has no separate forecast computation service.

**Required implementation**
- [ ] Give each forecast method a method-specific source path: manual consensus, deterministic statistical baseline and external-model import.
- [ ] For MANUAL_CONSENSUS, retain contributor inputs, consensus method, reviewer decision, assumptions and approval identity.
- [ ] For STATISTICAL_BASELINE, implement a repeatable computation service/worker with data cutoff, grain, feature/source snapshot, missing-data and stockout treatment, seasonality, model/formula version, run parameters and output hash.
- [ ] For EXTERNAL_MODEL_IMPORT, record provider/model identity, model artifact/version, source request/reference, signed or retained input/output artifact, import mapping, validation and reviewer.
- [ ] Distinguish DRAFT, GENERATED/IMPORTED, REVIEWED and PUBLISHED states where the selected method requires review; do not label a client-supplied forecast as a computed model result.
- [ ] Store immutable forecast input snapshot and result checksum, source cutoff and generation correlation.
- [ ] Add tests for duplicate dimension/time-bucket rows, missing source data, duplicate run request, altered model output, invalid currency, confidence bounds, stockout distortion and approved reforecast/supersession.
- [ ] Connect actual sales, stockout, returns, channel and inventory observations from WP-12/13 for rolling forecast evaluation.

### 23.3 OTB period and scenario calculations are not sourced from live procurement/inventory/forecast actuals

**Finding:** createPeriod() requires the user to supply beginning inventory, planned sales, planned markdowns, planned ending inventory, on-order purchases, commitments and actual receipts as numeric request fields. It calculates and persists a DRAFT period using these supplied values.

simulateScenario() accepts optional range_plan_id and forecast_run_id; it verifies the IDs are tenant-scoped and frozen/published, then stores them on the scenario. The arithmetic itself begins with the period values and permits request assumptions to override those values. It does not query the referenced range plan to derive the range mix, query forecast rows to derive planned sales, or query PO/open commitment, inventory-ledger and receipt rows to verify on-order purchases, commitments or actual receipts.

The scenario is explicitly a planning-only simulation and its approval explicitly does not create a purchase order or accounting entry. This is a useful safety boundary. The integration work is to make the planning numbers traceable and reproducible, not to convert approval into an automatic PO.

**Source**
- backend/app/Http/Controllers/Merchandising/OtbPlanningController.php, createPeriod(), simulateScenario(), approveScenario() and calculate().
- calculate() uses exact PostgreSQL NUMERIC arithmetic for the stored decimal inputs.
- backend/app/Services/Merchandising/MerchandisingDashboardService.php, unavailableKpis() identifies OTB utilization as unavailable until authoritative procurement and finance data are connected.

**Required implementation**
- [ ] Add a source-backed OTB period builder which resolves the approved legal-entity policy and reads opening inventory valuation, frozen range demand, forecast version, open approved PO commitments, actual receipts, approved markdown assumptions and the policy-defined valuation basis.
- [ ] Persist each source record/version and its as-of timestamp, query cutoff and source hash in an immutable OTB input snapshot.
- [ ] Where a user overrides a derived value, record the derived baseline, override, reason, evidence, author and independent approver separately; do not overwrite source facts.
- [ ] Validate that the supplied range plan and forecast run actually match the OTB period's legal entity, brand/season/channel/market and horizon; reject tenant-only but commercially unrelated references.
- [ ] Recompute a scenario from its frozen inputs and formula version; store the exact policy version and calculation hash.
- [ ] Keep the distinction between an approved planning scenario and a purchase commitment. Only WP-07's PO workflow and its approvals can create purchasing obligations; WP-13 owns the financial posting.
- [ ] Add regression tests for stale forecast, changed PO receipt, duplicated on-order commitment, inventory valuation change, policy change, manual override and cross-entity/mismatched horizon.

### 23.4 Price lists and promotions are authorable but not yet consumed by transactional pricing

**Finding:** CommercialPricingController exposes authoring and review paths for price lists, price-list lines and promotions: list/create/edit-draft/publish price list, list/create promotion, add eligibility and approve promotion. There is no inspected service method or customer-order/commerce endpoint that resolves an effective price for a product/customer/account/channel/location/date or applies an approved promotion to an order.

The WP-12 customer-order creation service accepts unit_price from the caller and stores it on the customer-order line. It does not call a published price list, verify the product's current effective price, calculate promotion eligibility/stacking, apply the price floor or derive a tax profile. This leaves the WP-04 price catalogue disconnected from the WP-12 order transaction.

**Source**
- backend/app/Http/Controllers/Merchandising/CommercialPricingController.php.
- backend/routes/api.php, product price-list and promotion routes.
- backend/app/Services/Distribution/FinishedGoodsService.php, createCustomerOrder() consumes caller-supplied line unit_price.
- price_lists, price_list_lines, promotions and promotion_eligibility are created/approved in WP-04; no price-resolution service is present in the inspected service tree.

**Required implementation**
- [ ] Implement a deterministic PriceResolver service keyed by legal entity, currency, brand, product/style revision, market, channel, account, location and effective timestamp.
- [ ] Define and test precedence when a more-specific price list and a general price list both match; use immutable version selection.
- [ ] Resolve customer eligibility, account terms, discount/promotion inclusion/exclusion, stacking order, floor and approved funding limits.
- [ ] Resolve tax profile through the approved WP-02/WP-13 legal/tax configuration rather than trusting a caller-supplied label.
- [ ] Make order creation call the pricing service server-side; reject client-supplied price mismatches or treat them only as a requested override requiring independent approval.
- [ ] Store immutable price-list/version/line, promotion/version, tax rule, currency and calculation snapshot on the order line so later price changes do not rewrite history.
- [ ] Add quote, order, wholesale, reseller, own-retail, POS, D2C and return/refund price-reconciliation tests.
- [ ] Keep published price-list lines immutable; correction should create a new version/effective period, not alter an already-used transactional price.

### 23.5 Merchandising KPI service explicitly identifies missing operational actuals

MerchandisingDashboardService.unavailableKpis() correctly lists the following as unavailable in the current slice:
- forecast bias and WAPE (requires WP-12 actual sales and stockout treatment);
- sell-through (requires posted sales and available-for-sale inventory);
- gross and contribution margin (requires WP-13 posted revenue and cost);
- OTB utilization (requires WP-07 procurement and WP-13 finance actuals);
- markdown exposure (requires posted markdown history and inventory age);
- returns and fit feedback (requires WP-05 approved fit outcomes and WP-12 returns);
- range-to-capacity fit (requires WP-06 validated factory capacity and WP-07 material availability).

The KPI service is careful not to show fabricated performance indicators. Preserve the unavailable-state behavior until each source has been integrated and independently reconciled.

**Required implementation**
- [ ] Define a canonical KPI semantic contract for numerator/denominator, event status included, currency/valuation basis, time zone/period, source tables and as-of cutoffs.
- [ ] Introduce data freshness/coverage status and distinguish zero activity from missing data or unavailable source.
- [ ] Connect WP-12 posted orders/sales/returns/inventory and WP-13 posted finance/cost actuals.
- [ ] Connect WP-06 approved capacity/SAM and WP-07 material availability to range feasibility.
- [ ] Reconcile each derived KPI to its source transaction ledger and test late-arriving corrections/reversals.

### 23.6 Market-insight source provenance requires more than a reference string

**Finding:** MarketInsightController has strong payload-level deduplication, but InsightSourceController.store() accepts source_reference, a reliability score and permitted-use basis as caller-supplied metadata. It does not link the insight source to a verified retained source artifact, captured bytes, checksum, source version, collection authority or rights/expiry evidence. Market insight evidence sufficiency can qualify a source based on the supplied reliability score; it does not prove the underlying source itself was retained or authentic.

**Required implementation**
- [ ] Link source records to retained document/web capture/event artifacts with immutable ID, retrieval timestamp, content hash, source version and provenance.
- [ ] Separate source reliability assessment from evidence authenticity and permitted-use/legal basis.
- [ ] Retain collection method, source owner/authority, consent/licence basis, geographic and personal-data restrictions and expiry/retention.
- [ ] Preserve source snapshots referenced by initiative decisions so historical evidence can be reproduced even after source records or policies change.
- [ ] Add tamper, source-revocation, expiry, duplicate-event and cross-tenant provenance tests.

---

## 24. WP-04/WP-06 implementation sequence

1. Add forecast method-specific workflows and immutable data/model provenance.
2. Connect OTB source snapshots to frozen range, approved forecast, actual inventory, PO commitments, receipts and finance valuation.
3. Implement server-side effective price/promotion resolution and call it from WP-12 customer-order/commerce flows.
4. Add evidence source artifact verification and immutable initiative decision evidence snapshots.
5. Complete operational KPI source integrations only as the WP-06/WP-07/WP-12/WP-13 ledgers become authoritative.
6. Complete the IE capacity-slot site equality check at slot creation, plus operation quantity reservation, plan revalidation and workforce scope/overlap controls.
7. Add cross-package PostgreSQL feature tests from initiative/range/forecast/OTB/pricing through PO/order/actual/KPI reconciliation.


---

## 25. WP-06 industrial engineering and workforce control — additional source/schema findings

### 25.1 Production planning does not require an approved IE standard

**Finding:** In IndustrialEngineeringService.addPlanLine(), the service queries for an approved operation_standard_versions row matching the production order style/revision and route step. It checks planned_minutes against standard_minutes multiplied by planned_quantity only inside a conditional that runs when a standard exists. When no matching approved standard is found, the service proceeds and inserts the plan line without the SAM/SMV lower-bound control.

This means an operation can be added to an approved production plan without an approved IE standard, even though WP-05 technical-pack release readiness identifies IE as a required downstream dependency.

**Source**
- backend/app/Services/Planning/IndustrialEngineeringService.php, addPlanLine().
- backend/database/migrations/2026_11_26_000019_create_ie_capacity_and_workforce_controls.php, operation_standard_versions.
- backend/app/Services/ProductDevelopment/BomTechnicalPackService.php, releaseReadiness() includes INDUSTRIAL_ENGINEERING_NOT_VERIFIED.

**Required implementation**
- [ ] Require one approved and currently effective IE standard for each production operation before the line can be accepted into a bulk production plan.
- [ ] Keep sample/prototype planning as a separately typed and bounded path if an IE standard is not yet approved.
- [ ] Verify the standard's approved status, exact style revision, exact route version/step, effective date, ergonomic review and immutable method/evidence identity.
- [ ] Fail closed when a mandatory standard is missing, expired, superseded or not applicable; do not silently skip the minutes constraint.
- [ ] Add negative tests for no standard, wrong style revision, wrong route step, future effective date, superseded standard and rejected ergonomic review.

### 25.2 IE standard effective-date selection uses the plan horizon start, not the operation's scheduled date

**Finding:** The standard lookup filters effective_from against production_plan_versions.horizon_start. It then orders by version_no descending. The line already carries planned_start_at/planned_start_date, but the standard resolver does not use that date.

If a standard becomes effective during the planning horizon, a line scheduled after the new standard's effective date can still resolve against the previous standard because the query is anchored to horizon_start. There is also no effective_to on operation_standard_versions to bound a retired standard's applicability.

**Required implementation**
- [ ] Resolve the applicable standard at the operation's actual planned execution date.
- [ ] Add effective_to or a controlled supersession interval and enforce non-overlapping effective versions for the same style/revision/route step.
- [ ] Resolve and persist the exact standard version ID on each plan line, so later re-approval does not silently change historical planned minutes.
- [ ] Revalidate the pinned standard at plan approval and order release.
- [ ] Test a new SAM/SMV effective mid-horizon, overlapping versions, a backdated correction and a line moved across the effective-date boundary.

### 25.3 Capacity-slot creation does not enforce work-centre and shift site equality

**Finding:** createCapacitySlot() checks that the selected work centre and shift are active and tenant-scoped, then inserts the capacity slot. It does not compare work_centres.site_id to work_shift_definitions.site_id. The schema has independent tenant foreign keys to the work centre and shift; it does not constrain them to share the same site.

assignWorkerToOperation() does perform a work-centre/shift site equality check, but the capacity slot can already have been created with mismatched site identities and addPlanLine() does not independently compare those site IDs.

**Required implementation**
- [ ] Enforce work-centre and shift site equality in createCapacitySlot().
- [ ] Add a composite database invariant or trigger so the relationship cannot be bypassed by another writer.
- [ ] Check the production order operating site against the slot's work centre/shift site when adding a plan line.
- [ ] Add positive same-site and negative cross-site tests, including an approved inter-site subcontract/transfer exception if applicable.

### 25.4 Workforce confirmation does not revalidate skill and overlap state

**Finding:** assignWorkerToOperation() checks that the linked skill record is active, competent and valid across the planned interval and takes a worker-level advisory lock while checking active overlap. It then creates a PLANNED assignment. confirmWorkerAssignment() independently checks only that the assignment is PLANNED and the confirmer differs from the creator; it does not take the same worker advisory lock or recheck the worker's active overlapping assignments and skill status/validity.

Between assignment and confirmation, the skill could be revoked/expire, or another overlapping assignment could become CONFIRMED/STARTED. The confirmation path would still promote the stale assignment.

**Required implementation**
- [ ] Take the same tenant/worker advisory lock in confirmation and recheck overlaps before promoting PLANNED to CONFIRMED.
- [ ] Lock and revalidate the skill record, competent assessment, validity period, suspension/revocation and exact route-step skill at confirmation.
- [ ] Revalidate work centre/shift active state and site/production-order binding.
- [ ] Add a PostgreSQL exclusion or serialized invariant preventing two overlapping CONFIRMED/STARTED assignments for one worker.
- [ ] Test revoke/expire-between-plan-and-confirm, two concurrent confirmations, overnight shift, worker change, site transfer and supervisor replay.

### 25.5 Authorized operation scope in skill records is not enforced

**Finding:** workforce_skill_records stores authorized_operation_scope as JSONB and the API allows it to be supplied during skill assessment. assignWorkerToOperation() validates the worker reference, active/competent skill code and date range, then compares required_skill_code to skill_code. It does not evaluate authorized_operation_scope against site, department, work centre, machine family, process or operation.

A skill record may therefore contain a restricted scope that is not considered by the assignment method.

**Required implementation**
- [ ] Define a typed, versioned scope contract and permitted values for site, legal entity, department, work centre, machine family, operation/process and product category.
- [ ] Evaluate the scope during assignment, confirmation and operation start.
- [ ] Resolve restricted/safety-critical qualifications from approved training/assessment evidence rather than free-form client values.
- [ ] Add scope allow/deny and changed-scope tests.

### 25.6 IE and workforce writes lack package-level automated feature coverage and domain audit records

The current delivery has no dedicated backend Feature test for IE work centres, shifts, capacity, operation standards, plan lines, worker skills, assignments or incentives. The inspected IndustrialEngineeringService methods also do not create corresponding audit_events for work-centre/shift/capacity changes, standard approval, production-plan approval, skill assessment or assignment confirmation. These writes therefore have creator/approver columns in some tables, but no package-level test evidence or explicit immutable action/reason event in the service path.

**Required implementation**
- [ ] Add PostgreSQL Feature tests for every IE/workforce state transition, RLS, authorization and concurrency invariant.
- [ ] Emit an immutable audit event in the same transaction as every approval-sensitive or capacity/skill/assignment change.
- [ ] Include actor, action, object version, reason, previous/new state hashes, site/legal-entity scope and correlation ID.
- [ ] Ensure failed writes roll back both the business mutation and audit event.
- [ ] Add factory UAT for time-study, capacity planning, line allocation, skills and worker assignment.

### 25.7 Incentive policy and calculation tables are schema-only in the inspected service/API layer

Migration 000019 creates incentive_policy_versions and incentive_calculation_drafts with eligible roles, measurement basis, quality/safety gates, grievance/appeal rules, effective dates and maker-checker review columns. The inspected IndustrialEngineeringService has no incentive-policy or incentive-calculation methods, and IndustrialEngineeringController/domain-api.php expose no incentive routes.

**Required implementation**
- [ ] Implement effective-dated incentive policy create/review/approve/supersede/suspend lifecycle.
- [ ] Bind measurement basis to approved production output/earned minutes, attendance/time and quality acceptance; reject incentives for unaccepted or reworked output until policy permits it.
- [ ] Define safety, grievance, appeal, wage-floor, tax and payroll handoff controls in conjunction with WP-02/WP-14.
- [ ] Add calculation source snapshots, versioned formulas, maker-checker and independent appeal review.
- [ ] Post approved incentive values only through the authorized payroll/finance process, not direct balance mutation.
- [ ] Add worker consent/privacy, dispute/appeal and source reconciliation tests.

### 25.8 Standard and skill study evidence is stored as caller-supplied JSON without linked evidence verification

**Finding:** createStandardVersion() and recordWorkerSkill() persist study_evidence/assessment_evidence JSON supplied in the request. The methods do not link the contents to verified WP-15 document assets or recalculate hashes of retained evidence. The approval gate does require an accepted ergonomic-risk review, but the stored evidence itself has no source artifact identity or reviewer-verifiable immutable version.

**Required implementation**
- [ ] Link time-study observations, method sheet, work-measurement equipment calibration and ergonomic assessments to verified retained evidence records.
- [ ] Link worker skill assessments to approved training, trainer/assessor identity, practical assessment outcome and qualification expiry.
- [ ] Preserve exact source artifact ID/version/hash and the assessor/approver decision snapshot.
- [ ] Re-verify evidence integrity and validity at plan/assignment approval.
- [ ] Add evidence altered/missing/expired and qualification source revocation tests.

---

## 26. WP-06 implementation sequence

1. Make an approved IE standard mandatory for bulk production plan lines.
2. Correct effective-date resolution and pin the standard to each plan line.
3. Enforce work-centre/shift/production-order site identity at service and database level.
4. Revalidate skill and overlap conditions under the worker advisory lock when confirming assignments.
5. Enforce authorized operation scope at assignment, confirmation and operation start.
6. Add domain audit events and the WP-06 PostgreSQL test suite.
7. Implement incentive policy/calculation flows in conjunction with WP-14, using approved output and quality facts.
8. Link time-study/skills/ergonomic evidence to verified WP-15 assets and add factory UAT.


---

## 27. WP-12 order-to-cash, warehouse, shipment and delivery audit

### 27.1 Existing order/credit/reservation safeguards to retain

The canonical source already implements several valuable controls:
- customer-order confirmation requires a linked CLEAR or APPROVED_EXCEPTION credit decision;
- order creator cannot perform the independent credit review;
- held credit requires a distinct exception approver and reference;
- credit reviewer/exception approver cannot also confirm the order;
- reservation requires a CONFIRMED/ALLOCATED/PART_FULFILLED order;
- lot must be AVAILABLE and match SKU, colour and size;
- available location balance is reduced by active reservations before a new reservation;
- reservation quantity cannot exceed outstanding unfulfilled order demand;
- location and reservation identity/quantity are subsequently protected by migration 000024;
- finished-goods transactions are append-only at database level;
- dispatch checks that it originates from the exact stock-enabled location assigned to the reservation;
- shipment/stock/line/ reservation/order updates run in one database transaction.

These controls should remain and receive PostgreSQL regression tests as the full WMS/commerce model is implemented.

### 27.2 Customer-order creation is not atomic and price/tax are caller supplied

**Finding:** createCustomerOrder() inserts the customer-order header and then inserts each line without a surrounding transaction. The controller accepts unit_price and tax_code_reference from the caller. The service stores them directly without invoking the WP-04 price list/promotion resolver or WP-02/WP-13 tax rule resolver.

**Source:** backend/app/Services/Distribution/FinishedGoodsService.php, createCustomerOrder(); backend/app/Http/Controllers/Distribution/FinishedGoodsController.php, storeCustomerOrder().

**Required implementation**
- [ ] Make header and all lines one transaction with request/order idempotency and payload fingerprint.
- [ ] Use an authoritative server-side product/SKU, customer/account and price/tax resolver.
- [ ] Preserve an approved override path with separate authority, reason and audit; do not trust client totals.
- [ ] Store immutable product, pricing, promotion, tax, currency and customer-account snapshots on the order.
- [ ] Add partial-line-failure rollback, duplicate retry, price mismatch and tax-rule effective-date tests.

### 27.3 Credit decision is a manual reference gate, not a live WP-13 credit exposure calculation

**Finding:** recordCustomerCreditDecision() accepts a CLEAR or HELD decision plus caller-entered decision_reference and reason. It stores the independent reviewer identity and timestamp and has a separate exception-approval flow. It does not calculate customer credit limit, posted AR, overdue exposure, unbilled shipments, open order exposure, deposit/security, currency/FX or aging from WP-13 finance records. The reference string can represent an external decision, but the source does not verify the external record or link it to a retained evidence artifact.

**Required implementation**
- [ ] Define customer/account credit profile, limit, risk/terms, currency and effective-date policy.
- [ ] Compute exposure from authoritative posted AR, pending invoices, open confirmed orders, unbilled shipments, receipts/deposits and approved FX basis.
- [ ] Preserve a manual/external credit decision route, but link a verified source decision/artifact ID and amount/currency/as-of snapshot.
- [ ] Recheck exposure at order confirmation and before dispatch when policy requires it.
- [ ] Store decision formula/source snapshot, reviewer and exception approver independently.
- [ ] Add tests for stale exposure, concurrent order confirmations, FX change, over-limit exception, expired decision and cross-legal-entity account access.

### 27.4 Reservation retry is not payload-aware and returns an error after a successful timeout

**Finding:** reservation_key is uniquely constrained by the database and stored on finished_goods_reservations. reserveCustomerOrderLine() does not look up an existing key before recalculating available stock and creating another reservation. An identical client retry after a committed response loss is not returned as the original reservation; it can fail because the first reservation is already included in reservedElsewhere or hit the unique key.

The schema also does not store a canonical request hash, so reuse of a key with a different order line, lot, location or quantity is not distinguished as a conflicting replay.

**Required implementation**
- [ ] Resolve reservation_key before computing stock and compare a canonical request fingerprint.
- [ ] Return the same reservation on identical replay and return a stable idempotency conflict for a changed payload.
- [ ] Keep stock balance and reservation rows under the same lot/location/order-line locks.
- [ ] Add timeout-after-commit, duplicate scan, conflicting replay and concurrent reserve tests.

### 27.5 Dispatch retry is not idempotent at the service boundary

**Finding:** dispatchReservedLine() locks a reservation and requires status ACTIVE, then inserts a new shipment, shipment line, dispatch transaction, closes the reservation, increments fulfilled quantity and updates the order. The dispatch transaction has a deterministic idempotency key based on reservation ID, but the service checks for an existing dispatch event only after requiring the reservation to be ACTIVE, and a completed first dispatch changes it to FULFILLED.

A client that loses the response after the transaction commits will retry with the same reservation and receive “Only an active reservation can be dispatched” instead of the already-created shipment. The service also does not compare a canonical dispatch payload (shipment number, mode, carrier, tracking, destination, pack reference) to the original dispatch.

**Required implementation**
- [ ] Check the reservation's existing dispatch result before rejecting a FULFILLED reservation.
- [ ] Store the immutable dispatch request hash and link to exactly one shipment.
- [ ] Return the original shipment on identical retry; reject a reused reservation with conflicting dispatch details.
- [ ] Enforce unique shipment-per-reservation and source transaction identity at database level.
- [ ] Add timeout-after-commit, duplicate request, conflicting payload and concurrent dispatch tests.

### 27.6 Packing, release and physical dispatch are collapsed into one actor/path

**Finding:** dispatchReservedLine() creates a shipment directly in PACKED with packed_at set by the same caller, then inserts the dispatch ledger event and changes status to DISPATCHED. In the same update, it sets released_by and dispatched_by to the same actor. The schema supports DRAFT, PICKING, PACKED, RELEASED and DISPATCHED, but the inspected API has one dispatchReservation action and no separate pick, pack, release, gate-out/dispatch or weigh/manifest decision paths.

The current implementation is suitable as an initial bounded dispatch slice, but it does not yet preserve independent warehouse packing, shipment release and gate-out custody.

**Required implementation**
- [ ] Implement separate shipment lifecycle commands: create/pick, pack/close cartons, release/authorize, dispatch/gate-out and transport acknowledgement.
- [ ] Bind each transition to an authorized role/site and capture actor, timestamp, device, reason and immutable package/manifest snapshot.
- [ ] Enforce role/position segregation for picker, packer, shipment releaser and gate-out dispatcher where the DoA/risk model requires it.
- [ ] Revalidate quality release, order credit, reservation state, stock balance, source location and destination before gate-out.
- [ ] Add append-only shipment event history and exact stage-transition constraints/tests.
- [ ] Support consolidated multi-line shipments/cartons/pallets without duplicating stock movements.

### 27.7 Order fulfilment status advances at dispatch, not proof of delivery

**Finding:** after a reservation dispatch, the service updates customer_order_lines.fulfilled_quantity and sets the order status to PART_FULFILLED or FULFILLED when all quantities have been dispatched. The shipment schema includes IN_TRANSIT, DELIVERED and DELIVERY_EXCEPTION, and delivered_at, but the inspected API/service has no delivery confirmation, proof-of-delivery, recipient acknowledgement or delivery-exception operation.

Consequently FULFILLED currently means quantities dispatched, not necessarily delivered to/accepted by the customer. This can misstate customer service, invoice trigger and revenue recognition events.

**Required implementation**
- [ ] Separate allocated, picked, packed, released, dispatched, in-transit, delivered, delivery-exception, customer-accepted and commercially fulfilled states.
- [ ] Add signed carrier/recipient proof-of-delivery or controlled manual confirmation with recipient, timestamp, evidence reference and exception reason.
- [ ] Define when an order line is shipped versus delivered/accepted; preserve partial delivery and customer short/over/claim handling.
- [ ] Trigger invoice/AR handoff only under the approved accounting and delivery terms, not merely on an arbitrary shipment update.
- [ ] Add delivery proof, partial delivery, lost shipment, rejected delivery, short receipt and late carrier event tests.

### 27.8 Returns, refunds and reverse logistics are not implemented in the inspected WP-12 source

The finished-goods transaction type constraint already includes CUSTOMER_RETURN, and the merchandising dashboard explicitly identifies return/fit feedback as a future WP-05/WP-12 dependency. But the inspected FinishedGoodsService/controller and domain API routes do not implement customer RMA creation, return authorization, receipt, inspection, refund/credit, restock/quarantine, replacement shipment or reverse finance posting.

**Required implementation**
- [ ] Implement RMA and return authorization linked to original order/shipment/line and delivery proof.
- [ ] Capture reason, quantity, condition, serial/lot/carton, customer claim, return freight and evidence.
- [ ] Receive returns into a controlled returns/quarantine location; inspect and determine restock, rework, repair, scrap, replacement or supplier claim.
- [ ] Use append-only compensating finished-goods transactions rather than rewriting the original dispatch.
- [ ] Create approved refund/credit-note and AR/AP adjustment handoff to WP-13.
- [ ] Add duplicate return, over-return, wrong-lot, wrong-customer, partial return, customer claim and full reverse genealogy tests.

### 27.9 Finished-goods source identity and location need to remain bound to the production passport

**Finding:** receiveCompletedProduction() accepts sku_code, lot_code, colour_code and size_code from request data. It verifies that the order is COMPLETED and the last completed operation has a good_quantity, but does not resolve the SKU/colour/size against an approved style revision, product passport, production-order size breakdown or legal-entity/site-bound product output record. The quarantine location is checked for QUARANTINE type but is not compared to the production order's operating site/legal entity.

The lot stores style_id/style_revision_no from the order, but its SKU/colour/size values are independently supplied. This can create a finished-goods lot whose declared style revision and SKU/size combination have no approved mapping.

**Required implementation**
- [ ] Resolve SKU/colour/size as approved style/revision/colour/size master identities pinned by the production order.
- [ ] Validate receipt quantity against the cumulative output balance per approved size/colour/SKU allocation, not only the last operation total.
- [ ] Validate quarantine location and all later transfers against the production order legal entity/site and an authorized inventory node.
- [ ] Store the exact BOM/tech-pack/route/quality/IE source versions and production output event IDs on the finished-goods genealogy.
- [ ] Add wrong-SKU, wrong-size, wrong-colour, cross-site location and cumulative receipt tests.

### 27.10 Finished-goods reservation unit and order unit are not checked for compatibility

**Finding:** reserveCustomerOrderLine() checks SKU, colour and size and stores reservation.unit_code from the finished-goods lot. It does not verify that customer_order_lines.unit_code equals the lot unit_code or apply a versioned unit conversion. The quantity comparison then uses DecimalQuantity directly across the two numeric values.

**Required implementation**
- [ ] Require exact unit equality for discrete piece reservations or perform an approved unit conversion.
- [ ] Store the conversion factor/version and normalized base quantity; prevent conversion for incompatible dimension families.
- [ ] Add PCS/DOZEN/BOX/CARTON, fractional pack, rounding and invalid conversion tests.

---

## 28. WP-12 implementation sequence

1. Integrate server-side price/tax and SKU/customer master resolution into order creation.
2. Make order header/lines atomic and idempotent.
3. Complete customer credit profile/exposure and verified external decision integration.
4. Make reservation and dispatch retries payload-aware and replay-safe.
5. Implement independent picking/packing/release/gate-out shipment workflow with shipment event history.
6. Separate dispatch from delivery/acceptance and add proof-of-delivery.
7. Implement RMA/returns/reverse inventory and finance integration.
8. Enforce production passport, SKU/size/colour and legal-entity/site genealogy at finished-goods receipt.
9. Validate reservation unit dimensions and supported conversions.
10. Add end-to-end WP-12 sales/order/credit/ATP/reserve/ship/deliver/return/refund/AR test journeys.


---

## 29. WP-13 finance and canonical payroll-review source audit

### 29.1 Existing journal maker-checker and posting controls are integrated

The existing finance foundation already has:
- legal-entity scoped accounting periods with open/close/lock statuses;
- legal-entity chart accounts, account classes, normal balance, base-only/multi-currency policy and maker-checker active approval columns;
- journal and line records with legal-entity and period references;
- per-line debit/credit exclusivity and positive base-currency side checks;
- PostgreSQL trigger enforcing at least two journal lines and balanced positive base-currency totals before review/approval;
- journal creator/submitter separation from approver;
- required approval/rejection reason;
- posting rechecks period remains OPEN and approval is separate from posting actor;
- one posting row per journal and one unique tenant posting number;
- database trigger from migration 000022 preventing journal-line INSERT/UPDATE/DELETE after the parent leaves DRAFT.

These controls are meaningful foundations and should be retained. The posting record is a separate immutable table; the current journal status constraint does not have a POSTED state. The posting relationship must therefore remain the authoritative evidence of whether a journal has posted.

### 29.2 Finance periods and chart of accounts are defined in schema but lack application administration workflows

**Finding:** finance_periods and finance_accounts are created by migration 000020 and the finance service reads them. FinanceControlController exposes journal and payroll draft actions but no period creation/open/close/lock workflow and no chart-account create/review/activate/block/archive workflow. The inspected service has no corresponding methods, and the delivery tree contains no database seeder/factory package that initializes a production chart or period catalogue.

The journal service therefore depends on externally/manual-populated active accounts and open periods before the application can create useful postings.

**Required implementation**
- [ ] Implement legal-entity finance period create/open/closing/close/lock/reopen-by-controlled-correction lifecycle with maker-checker and audit events.
- [ ] Prevent overlapping active periods for the same legal entity unless the approved accounting calendar explicitly models adjustments.
- [ ] Implement chart-of-accounts create/parent/approve/activate/block/archive, account code/version and control-account mapping.
- [ ] Enforce parent account belongs to the same tenant and legal entity; migration 000020 currently constrains the parent by tenant but does not bind parent legal_entity_id to the child's legal_entity_id.
- [ ] Seed only approved, versioned statutory/base reference data through a controlled tenant/legal-entity initialization path; never insert fabricated finance transactions.
- [ ] Add clean-install, opening-balance, period-close and chart migration/reconciliation tests.

### 29.3 Currency and exchange-rate values are supplied by the journal caller

**Finding:** createJournalDraft() validates that each account is ACTIVE and belongs to the selected tenant/legal entity. It accepts line currency, debit/credit and base debit/credit amounts directly from the request, and stores exchange_rate_reference as optional text. It does not enforce the account's BASE_ONLY versus MULTI currency policy, require a consistent line/journal currency model or resolve the rate from an approved effective-dated FX source.

The PostgreSQL balance gate proves base debit equals base credit. It does not prove the caller-supplied base conversion is consistent with the source-currency amount and approved rate.

**Required implementation**
- [ ] Enforce account currency_policy against each journal line's transaction currency.
- [ ] Resolve transaction/base currency and legal-entity base currency from approved legal-entity configuration.
- [ ] Implement effective-dated FX rate records with provider/source reference, publication time, bid/ask/approved rate, quotation direction, date/time zone, approval and immutable version.
- [ ] Calculate base amounts server-side with the approved precision and rounding method; require independent override approval for permitted manual rates.
- [ ] Verify line currency compatibility, base amount and rate reference before submission and posting.
- [ ] Add foreign-currency, BASE_ONLY account, stale rate, missing rate, wrong direction and tampered base amount tests.

### 29.4 Journal/account/legal-entity relationships are only partly constrained at database level

**Finding:** finance_journal_lines has composite tenant FKs to its journal and account, while the service separately checks that the account belongs to the journal's selected legal entity. The migration does not include a composite FK/trigger tying finance_journal_lines.account_id to finance_journals.legal_entity_id. A different runtime path writing the permitted line table could therefore attach an account from another legal entity within the same tenant.

Similarly, finance_accounts.parent_account_id is constrained by tenant but not by legal entity.

**Required implementation**
- [ ] Add a database-enforced account/legal-entity relationship for journal lines.
- [ ] Add a database-enforced parent-account/legal-entity relationship or a trigger with immutable hierarchy checks.
- [ ] Preserve cross-entity intercompany postings only as explicit balanced paired journals with authorized counterparty/legal-entity references and reconciliation.
- [ ] Add adversarial PostgreSQL tests that attempt same-tenant cross-entity account and parent references using the app_runtime role.

### 29.5 Payroll draft approval does not require validated calculation lines or control totals

**Finding:** the canonical finance/payroll foundation correctly labels its payroll rows as drafts and uses a professional review gate, but the approval service does not require calculation_status = VALIDATED on each payroll_run_line, a completed control_totals calculation or a non-empty verified calculation output hash.

addPayrollDraftLine() accepts caller-supplied gross_amount, total_deductions and component/evidence JSON, calculates only net = gross minus deductions and inserts the line with calculation_status = DRAFT. submitPayrollForProfessionalReview() checks only that at least one line exists. applyProfessionalPayrollDecision() checks that the reviewer differs from the preparer and that a reference/reason exist; it does not aggregate/validate each line or reject DRAFT/HELD/REJECTED calculations before setting the run to APPROVED_FOR_PAYROLL.

This is a review foundation, not a calculated statutory payroll workflow. A manually entered draft line could pass the run-level professional approval operation while its calculation_status remains DRAFT.

**Required implementation**
- [ ] Require every run line to be VALIDATED, with exact calculator version, input hash, rule/source snapshot and calculation result hash before submission.
- [ ] Calculate and persist control totals by currency, earning/deduction category, employee count and statutory component; verify them again at review.
- [ ] Reject or hold the whole run if any line is DRAFT, HELD, REJECTED, source-invalid or fails the calculator's rule/evidence gate.
- [ ] Verify every payroll line resolves to an actual WP-14 employee/worker identity, active legal-employer assignment and in-period contract/status; worker_reference is currently a free string.
- [ ] Bind statutory rule versions to the legal employer, worker classification, jurisdiction and full payroll period, not only a rule effective on period_end.
- [ ] Verify source document bytes/hash and professional authority before status can change to APPROVED_FOR_PAYROLL.
- [ ] Add payroll run tests for draft line, held line, invalid evidence, missing employee, duplicate worker, control-total mismatch, mid-period legal change and maker-checker.

### 29.6 Payroll rule-set snapshot can include a null source hash

Migration 000020 permits statutory_rule_versions.source_sha256 to be NULL. The PROFESSIONAL_APPROVED check requires reviewer identity/time and approval reference, but not a retained source artifact hash or source document reference. createPayrollDraft() serializes the selected rule IDs and their source_sha256 values into statutory_rule_set, so it can freeze a null hash.

**Required implementation**
- [ ] Require a verified source artifact ID/version and exact SHA-256 of retained bytes before PROFESSIONAL_APPROVED.
- [ ] Recompute the SHA from the retained bytes and bind the professional approval to the exact artifact and provision/article.
- [ ] Reject payroll run creation if any selected rule has missing or invalid source identity, even if a legacy row is marked professionally approved.
- [ ] Add a controlled forward migration to tighten rule approval once legacy evidence is reconciled; do not rewrite historical rule snapshots without provenance.

### 29.7 Reversal is schema-only and the current table models only an already approved reversal

Migration 000025 creates finance_journal_reversals with original posting, reversal journal, reason, requested_by, approved_by and approved_at. It enforces a different requester and approver and a unique reversal per original posting. The inspected service/controller have no reversal request/approval/posting methods or routes.

The schema currently requires approved_by and approved_at at insertion, so it represents a completed approval record rather than an unapproved reversal request. It also allows only one reversal record per original posting, which implies a full, single reversal rather than partial/serial reversals.

**Required implementation**
- [ ] Define explicit reversal request, independent review, approval and posting states; persist unapproved requests separately from approved records.
- [ ] Generate inverse journal lines from immutable original posting lines; do not accept caller-created arbitrary reversals.
- [ ] Define whether accounting policy allows full-only or partial/multiple reversals and model remaining reversible balance accordingly.
- [ ] Require same legal entity and approved open period, and preserve source posting and original line hashes.
- [ ] Add request/approve/post/duplicate/concurrent/partial/full/closed-period tests.
- [ ] Add controller/API and finance operator UI paths.

### 29.8 Journal source documents and subledgers are not connected

**Finding:** createJournalDraft() accepts optional source_document_type and source_document_id as request fields and stores them without verifying a corresponding source record. Journal lines accept arbitrary source_reference JSON. The current finance API is manually journal-centric and does not expose accounts receivable, accounts payable, cash/bank, inventory valuation, manufacturing cost, customer billing, supplier invoice, payment, bank statement or reconciliation subledger services.

**Required implementation**
- [ ] Define typed, tenant/legal-entity-scoped source-document references and verify the source record exists in its owning bounded context.
- [ ] Add transactional posting adapters from WP-07 receipt/invoice/three-way match, WP-09/10 WIP and production, WP-11 quality/scrap, WP-12 order/dispatch/return/payment and WP-14 payroll.
- [ ] Implement AP, AR, customer collections, supplier settlements, bank/cash, inventory valuation, WIP/COGS, landed cost, tax/withholding and fixed asset/depreciation subledgers.
- [ ] Ensure source transaction and outbox event commit atomically with the accounting handoff; use stable source event identity and payload hash.
- [ ] Reconcile every subledger control account to GL by entity, currency, period and source category.
- [ ] Add end-to-end procurement-to-AP, order-to-AR, factory-to-WIP/COGS, return-to-credit and payroll-to-GL journeys.

### 29.9 Finance user-facing and automated verification are absent from the current tree

The inspected Angular feature tree has no dedicated finance/treasury/accounts-payable/accounts-receivable feature. The backend has no finance/payroll-specific PHPUnit Feature suite. There are no current application routes for periods, accounts, subledgers, bank reconciliations, close, reversals or payment execution.

**Required implementation**
- [ ] Build finance operator views for periods, chart of accounts, journal drafts, approvals, posting, reversals, subledger queues, reconciliation and close.
- [ ] Build payroll review views separately from calculator/employee processing and display source/legal holds clearly.
- [ ] Add role/position-based separation for preparer, reviewer, approver, poster, period controller and auditor.
- [ ] Add API contract and PostgreSQL Feature tests for all finance lifecycle operations and RLS.
- [ ] Add trial balance, account reconciliation, subledger/GL and period-close E2E evidence.

---

## 30. WP-13 implementation sequence

1. Complete period and chart-of-accounts administration with database-level legal-entity hierarchy.
2. Implement approved FX and currency policy resolution.
3. Strengthen source-document and subledger linkage for all operational posting events.
4. Complete payroll line validation/control totals and source-byte verified legal rule set; integrate WP-14 calculator only after its evidence/tests are complete.
5. Implement the reversal workflow and full/partial policy explicitly.
6. Build AP, AR, treasury, inventory/WIP/COGS, landed-cost and tax subledgers with idempotent transaction-to-GL handoffs.
7. Build finance/treasury/payroll operator UI and full database/API/E2E test suites.

---


## 31. WP-03 / WP-04 — Scoped authorization, forecast lineage and commercial guardrails

### 31.1 Scoped tenant members can reach merchandising routes that do not evaluate their scope

**Finding:** the tenant middleware does not enforce SCOPED mode itself. ResolveTenantContext::handle() validates an active tenant membership, binds the transaction-local tenant/user context and continues the request. It does not load tenant_access_scope_modes, reject SCOPED members, or call ScopedAccessService or DomainCapabilityAuthorizer.

The explicit scope service is used in selected merchandising actions (brand/collection/range/assortment and account-terms workflows). It is not injected or called in the inspected:
- backend/app/Http/Controllers/Merchandising/CommercialPricingController.php;
- backend/app/Http/Controllers/Merchandising/OtbPlanningController.php;
- backend/app/Http/Controllers/Merchandising/DemandPlanningController.php.

These controllers are routed under the general auth:sanctum, token.ability:erp:access, tenant.context group in backend/routes/api.php, not under a target-record authorization middleware. AppServiceProvider defines the role-matrix Gate abilities, but defining a Gate does not enforce it on a controller action that never calls it. DomainCapabilityAuthorizer correctly denies a MEMBER in SCOPED mode when its Gate is evaluated, but these merchandising paths do not evaluate the Gate or exact brand/site/market grants.

**Effect:** a tenant member set to SCOPED can still invoke tenant-wide list/create/simulation actions for prices, promotions, demand forecasts and OTB through these controllers. Tenant RLS still prevents cross-tenant access; it does not enforce intra-tenant brand/site/market grants. This is a same-tenant least-privilege gap.

**Source**
- backend/app/Http/Middleware/ResolveTenantContext.php, handle().
- backend/app/Services/ScopedAccessService.php, assertBrand(), assertSite(), assertMarket() and mode lookup.
- backend/app/Services/Authorization/DomainCapabilityAuthorizer.php, the SCOPED-member fail-closed check.
- backend/app/Providers/AppServiceProvider.php, centrally defined Gates.
- backend/routes/api.php, shared tenant route group.
- The three merchandising controllers above.
- backend/tests/Feature/BrandRangeWorkflowTest.php, test_scoped_administrator_cannot_freeze_a_range_without_brand_and_market_grants() verifies the selected range-freeze path, but there is no corresponding test for pricing, forecast or OTB.

**Required implementation**
- [ ] Apply a common domain authorization boundary to every merchandising list/read/write action, with the exact target context resolved before the service executes.
- [ ] For price lists, resolve brand, legal entity, channel, market and account; enforce READ, CREATE, APPROVE or MANAGE at the exact record scope.
- [ ] For OTB policies/periods/scenarios, resolve legal entity plus collection/range/market/channel dimensions where attached; do not infer authority from tenant membership.
- [ ] For forecasts/adjustments, validate every dimension reference, then require matching brand/market/channel/account/store grants for each row or authorized aggregate.
- [ ] Keep administrative bypass only for the explicitly approved tenant/platform administration roles; do not give domain-wide access to a scoped department or operational member.
- [ ] Add adversarial tests that set a member to SCOPED, grant one brand/site/market, then prove list filtering and read/create/approve actions for other scopes return no data or 403/404 as designed.
- [ ] Add regression coverage for the currently covered range-freeze path and account-terms scopes so a future route cannot omit the scope boundary.

### 31.2 Demand forecast publication accepts unvalidated dimension JSON

**Finding:** DemandPlanningController::publishRun() validates forecasts.*.dimension_refs only as an array. It does not validate the required key set and value type for each grain, nor verify that referenced style/category/channel/account/store records exist, are active, belong to the tenant, or are consistent with each other. The endpoint rejects duplicate JSON dimension/time combinations but that only prevents identical payload keys; it does not establish that those keys identify valid business objects.

The same method writes every supplied run as status = PUBLISHED in one transaction for all three methods (MANUAL_CONSENSUS, STATISTICAL_BASELINE, EXTERNAL_MODEL_IMPORT). model_version is optional, and no source artifact, algorithm parameter hash, training-data window, import checksum or model evaluation record is required for statistical/external results.

**Effect:** a published forecast can contain arbitrary or mismatched dimension references and cannot be reliably joined to product, assortment, channel, account or store master records. The method label alone does not establish statistical or external-model provenance.

**Source**
- backend/app/Http/Controllers/Merchandising/DemandPlanningController.php, publishRun() around lines 61–152.
- backend/database/migrations/2026_11_22_000005_create_brand_range_assortment_planning_tables.php, demand_forecast_runs and demand_forecasts; dimension_refs is JSONB and has no relational dimension constraints.
- backend/tests/Feature/DemandOtbPricingWorkflowTest.php, createForecast() and the forecast tests.

**Required implementation**
- [ ] Define a JSON schema/typed DTO per grain: STYLE_WEEK, STYLE_MONTH, CATEGORY_WEEK, CATEGORY_MONTH, CHANNEL_CATEGORY_WEEK, ACCOUNT_STYLE_WEEK and STORE_STYLE_WEEK.
- [ ] Validate each dimension ID/code against tenant-scoped master data and verify the row's channel, market, account, brand and store hierarchy is internally consistent.
- [ ] Bind the rows to the applicable approved assortment/range/product revision and effective dates when that is part of the selected grain.
- [ ] Keep manual, statistical and imported forecasts distinct; require a model/source version, immutable parameter/input manifest, source/import file hash and provenance record for non-manual methods.
- [ ] Add a deliberate draft → validation → publish/approval lifecycle if consensus approval is required by the planning authority; do not silently classify a raw imported payload as a validated model output.
- [ ] Test each grain schema, cross-tenant dimension, inactive style/store, inconsistent channel/account, unknown reference, duplicate row and source-hash mismatch.

### 31.3 Approved forecast adjustments are not materialized as an effective forecast version

**Finding:** createAdjustment() creates an append-only forecast_adjustments record. approveAdjustment() inserts an APPROVE_ADJUSTMENT row in merchandising_decisions and an audit event but does not set forecast_adjustments.approved_by. showRun() derives an is_approved flag by joining the decisions table, so the decision history is visible; however, the approved adjustment remains a separate record and does not change or supersede the published forecast. There is no service in the inspected demand-planning path that materializes a new approved forecast version or effective-forecast projection from the accepted adjustments.

The current OTB scenario can reference a forecast_run_id, but its calculation reads the manually stored OTB period assumptions and optional scenario assumptions; it does not query the forecast rows or approved adjustments to derive planned sales.

**Effect:** users can approve an adjustment and see it marked approved, while downstream planning still consumes the original published forecast or manually entered OTB numbers. This breaks the intended forecast → approved adjustment → range/OTB planning lineage.

**Source**
- backend/app/Http/Controllers/Merchandising/DemandPlanningController.php, createAdjustment(), approveAdjustment() and showRun().
- backend/database/migrations/2026_11_22_000005_create_brand_range_assortment_planning_tables.php, forecast_adjustments.approved_by.
- backend/app/Http/Controllers/Merchandising/OtbPlanningController.php, simulateScenario() and calculate().
- backend/tests/Feature/DemandOtbPricingWorkflowTest.php, test_forecast_runs_and_adjustments_are_append_only_and_independently_approved() verifies that the original forecast stays unchanged, but there is no test proving an effective adjusted forecast version is generated and consumed by OTB.

**Required implementation**
- [ ] Preserve the original forecast run immutably.
- [ ] Add an effective forecast snapshot/run that pins the exact base run and all approved adjustment IDs/versions/hashes.
- [ ] Store a deterministic adjustment decision result; either populate approved_by consistently with the decision record or remove the redundant column through a forward migration and make the decision join the explicit source of truth.
- [ ] Add a materialization service that rejects pending/rejected adjustments and computes the effective row set using decimal arithmetic.
- [ ] Allow OTB simulation to pin the effective forecast snapshot, derive sales/receipts by the policy's selected valuation basis and retain an explicit override record where planners change it manually.
- [ ] Test exact same base forecast, approved override, duplicate approval, multiple adjustments to one forecast row, conflicting edits, immutable history and OTB snapshot reproducibility.

### 31.4 OTB policy approval thresholds are stored but never evaluated

**Finding:** createPolicy() requires an approval_thresholds array and persists it to otb_policies. activatePolicy() validates the inclusion rule modes, but does not validate or use the threshold structure. approveScenario() only requires a tenant administrator and that the author differs from the approver; it never loads the linked policy's approval_thresholds, computes the relevant amount/variance and routes approvals by threshold.

**Effect:** the stored threshold configuration creates the appearance of configured delegated authority, but it currently has no effect on the approval decision. A scenario requiring a higher-level or multi-step approval would still pass the same tenant-admin check.

**Source**
- backend/app/Http/Controllers/Merchandising/OtbPlanningController.php, createPolicy() around lines 27–89, activatePolicy() around lines 90–169 and approveScenario() around lines 356–400.
- backend/database/migrations/2026_11_22_000005_create_brand_range_assortment_planning_tables.php, otb_policies.approval_thresholds.
- docs/organization/enterprise-organogram-and-delegated-authority.md (PR #16 design) specifies position/incumbent/delegation authority rather than universal tenant-owner approvals.

**Required implementation**
- [ ] Define a versioned, validated threshold schema: currency/basis, amount bands, variance/exception bands, required position/role, approver count, segregation, effective dates and escalation.
- [ ] Integrate the approved WP-03 organization/position/DoA resolver; do not infer business authority from OWNER/ADMIN membership.
- [ ] Snapshot the exact DoA policy version and approval route when the scenario is submitted.
- [ ] Require every approval step, reject self-approval and re-evaluate effective incumbent/delegation status before each decision.
- [ ] Test under/over threshold, mixed currencies, changed policy after submission, delegated approver expiry and conflicting concurrent decisions.

### 31.5 Promotion approval does not enforce margin floor or price outcome

**Finding:** createPromotion() accepts a fixed-amount discount greater than zero without comparing the amount with the linked price-list lines. Only percentage discounts are capped at 100%. It stores optional margin_floor, but approvePromotion() does not evaluate margin_floor, the linked line cost_basis, list/wholesale prices or the post-discount margin. The inspected controller contains no price-resolution method that applies the promotion safely to an order line.

**Effect:** a fixed discount can exceed a product's published price; an approved promotion can violate its configured margin floor. Recording margin_floor does not make it an enforced financial guardrail.

**Additional eligibility finding:** addPromotionEligibility() accepts a free-form scope_reference for CHANNEL, PRODUCT, CATEGORY, ACCOUNT, LOCATION or BRAND. It does not verify that the reference exists in the tenant or is valid for the linked price list. approvePromotion() requires an inclusion row, but its overlap query only joins identical scope_type and scope_reference rows and ignores exclusion scopes. A CHANNEL inclusion and a PRODUCT/CATEGORY inclusion can refer to intersecting real-world items while the equality join reports no overlap; the approval path therefore cannot guarantee no-stack safety across heterogeneous scope types.

**Source**
- backend/app/Http/Controllers/Merchandising/CommercialPricingController.php, createPromotion(), addPromotionEligibility() and approvePromotion().
- backend/database/migrations/2026_11_22_000005_create_brand_range_assortment_planning_tables.php, price_list_lines, promotions and promotion_eligibility.
- backend/tests/Feature/DemandOtbPricingWorkflowTest.php, promotion approval and basic unsafe-value tests.

**Required implementation**
- [ ] Enforce a line-level maximum discount and non-negative final price for fixed-amount promotions.
- [ ] Compute post-discount gross margin using the effective, approved cost basis and currency/valuation basis; enforce the configured margin floor before approval and at transaction execution.
- [ ] Validate each eligibility reference against the right tenant-scoped master (channel, style/product, category, customer/account, operational location, brand).
- [ ] Introduce normalized eligibility predicates/intersection evaluation so overlap considers cross-type intersections and exclusions, not only exact type/reference matches.
- [ ] Implement one deterministic effective-price resolver for price lists, account terms, markdowns, promotions, coupon stacking, time zones, currency and order-line scope.
- [ ] Pin the price/promotion resolver inputs, versions, decision and result to every order quote/order line for reproducibility.
- [ ] Test fixed discount above price, percentage boundary, below-margin outcome, stale cost, cross-type overlap, exclusions, expiry/timezone, currency mismatch, stacking policy and repeat quote calculation.

### 31.6 OTB scenario references are not reconciled to the OTB policy or used as calculation inputs

**Finding:** simulateScenario() validates that optional range_plan_id and forecast_run_id exist in the tenant (and are frozen/published respectively), but it does not verify their legal-entity, brand, market, channel, currency or period relevance to the otb_period and its otb_policy. The formula is calculated from the OTB period's manually entered amounts plus caller-supplied assumptions. The optional forecast and range references are saved as attribution only and do not change those inputs. The endpoint allows arbitrary extra keys in the assumptions map, though the calculation reads only its fixed known keys.

**Effect:** an OTB scenario may cite a forecast or range plan that is unrelated to the period's commercial scope, and the user may reasonably believe that cited forecast/range informed the budget calculation when it did not.

**Required implementation**
- [ ] Resolve the period's policy/legal entity and the forecast/range's full collection/brand/market/channel/currency scope before accepting the scenario.
- [ ] Reject incompatible or stale references; pin compatible source versions and exact input hashes.
- [ ] Derive default sales/receipts/ending inventory from the selected effective forecast and approved assortment, with explicit documented override fields.
- [ ] Validate an allow-list of assumption names and units and reject unknown keys.
- [ ] Preserve a calculation manifest containing formula version, currency, valuation basis, input provenance, overrides and resulting available budget.
- [ ] Add tests for mismatched tenant (already protected), mismatched entity/brand/market/channel/currency/period, stale forecast, unknown assumption and deterministic recomputation.

### 31.7 WP-04 test coverage extension

Existing DemandOtbPricingWorkflowTest covers the happy paths for forecast runs, adjustment approval, OTB policy/period/scenario and price list/promotion approval, plus basic validation. BrandRangeWorkflowTest covers selected brand/range/assortment and scoped range-freeze behavior. The next test increment must add the specific same-tenant scope-bypass, forecast dimension, effective-forecast materialization, DoA-threshold, promotion margin/overlap and OTB source-lineage scenarios above. Tests must execute under the non-owner application database role and be run on the exact candidate SHA.


---


---

## 32. WP-11 quality state machine and WP-15 integration replay audit

This pass inspected the current QualityControlService, QualityController, migration 000017, ManufacturingExecutionService, IntegrationOutboxService and DocumentIntegrationService at delivery SHA fc01eced62b97c4ec27f0075264fc8ba0fb11851.

### 32.1 Quality inspection submission and independent review have an incompatible intermediate database state

**Finding:** The quality inspection schema has a result_status CHECK allowing DRAFT, IN_PROGRESS, PASS, PASS_WITH_DEVIATION, FAIL, CONTAINED and CANCELLED. A second CHECK requires reviewed_by and reviewed_at whenever result_status is PASS or PASS_WITH_DEVIATION.

The intended service sequence is:
1. Assigned inspector submits a completed result and writes PASS/PASS_WITH_DEVIATION with inspected_at.
2. A different reviewer calls reviewInspection() and writes reviewed_by/reviewed_at.
3. Material, operation or finished-goods release can consume the reviewed result.

Step 1 is rejected by the database because the reviewer fields are still NULL. The database therefore blocks the intended two-person flow before the reviewer can perform step 2.

**Source**
- backend/database/migrations/2026_11_26_000017_create_quality_capa_and_release_gates.php, quality_inspections final CHECK constraints.
- backend/app/Services/Quality/QualityControlService.php, submitInspectionResult() and reviewInspection().
- backend/app/Services/Quality/QualityControlService.php, decideMaterialRelease() and decideOperationRelease() require reviewed passing inspections.

**Required implementation**
- [ ] Introduce an explicit SUBMITTED_PENDING_REVIEW status, or separate inspection-result status and review status.
- [ ] Allow inspector PASS/PASS_WITH_DEVIATION submission with reviewer fields absent.
- [ ] Require independent reviewer and reason before the result becomes RELEASEABLE_PASS.
- [ ] Keep failed/contained results on a separate non-release path.
- [ ] Add PostgreSQL tests for draft → inspector submits pass → different reviewer accepts → material/operation/finished-goods release can proceed; prove no release is possible before review.
- [ ] Use a forward-only migration and preserve historical inspection identities; do not change the earlier migration in a way that breaks an installed database.

### 32.2 Deviation and containment approvals are request booleans, not independently evidenced decisions

**Finding A — deviation:** QualityController accepts deviation_approved as a boolean. submitInspectionResult() converts it into PASS_WITH_DEVIATION without a linked waiver/deviation record, applicable standard revision, affected quantity, conditions, expiry, reason or approver identity. The inspection reviewer is still independent, but that reviewer is not a substitute for a distinct deviation authority.

**Finding B — containment:** The controller also accepts containment_confirmed as a boolean. The service writes the nonconformance containment_status as CONTAINED from that flag. It does not require a containment action/evidence record, actual contained quantity/scope, responsible actor or verification. A defect catalog's requires_containment field must not be treated as satisfied merely because a caller supplied true.

**Required implementation**
- [ ] Replace deviation_approved with a tenant-scoped deviation/waiver decision record linked to exact inspection subject, product/process standard revision, affected quantity, reason, conditions and expiry.
- [ ] Record a separate authorized approver and approval evidence; validate that the deviation applies to the exact subject and scope.
- [ ] Replace containment_confirmed with a containment record and evidence, including affected inventory/WIP/shipments, quantity, location, owner, action, timestamp and independent verification where required.
- [ ] Derive PASS_WITH_DEVIATION or CONTAINED from those recorded decisions, not caller truth values.
- [ ] Test unapproved, expired, wrong-subject, wrong-standard, wrong-quantity, cross-tenant and altered-evidence cases.

### 32.3 Nonconformance disposition can assert authority containment without an authority event

**Finding:** setNonconformanceDisposition() updates a locked nonconformance record, but its closure does not capture the actorId even though the method accepts it. The update stores disposition, caller-provided containment_status, notes/root cause and status UNDER_REVIEW; it does not record a disposition requester, reviewer, authority, timestamp, or reasoned decision event. QualityController explicitly allows the caller to set containment_status to RELEASED_BY_AUTHORITY.

The current row has opened_by and resolved_by fields, but no separate disposition actor/approver metadata. As a result, a caller with the generic quality.set-disposition permission can assert RELEASED_BY_AUTHORITY without a distinct authority decision record.

**Required implementation**
- [ ] Persist the initiating actor, request time, reason and exact previous/new state in a nonconformance disposition request.
- [ ] Add separate authorized disposition decisions for USE_AS_IS, CUSTOMER_CONCESSION, RETURN_TO_SUPPLIER, SCRAP and authority-level containment release where required by policy.
- [ ] Derive RELEASED_BY_AUTHORITY only from a linked approved authority record; reject direct request values.
- [ ] Bind approver authority to severity, product/customer requirement, legal entity/site, quantity and risk threshold.
- [ ] Preserve inspector, disposition requester, approver, CAPA implementer and effectiveness verifier separation where required.
- [ ] Add authorization, same-actor, changed-scope, expired approval and audit atomicity tests.

### 32.4 CAPA write and verification evidence still need transactional/evidence strengthening

**Finding A — CAPA creation:** createCorrectiveAction() reads the nonconformance, inserts a corrective action and updates the nonconformance status to CAPA_REQUIRED as separate statements without one transaction and without locking the NC. A failure after the insert can leave the action and NC state inconsistent; two concurrent requests can create duplicate actions.

**Finding B — evidence:** implementCorrectiveAction() and verifyCorrectiveActionEffectiveness() store evidence supplied as JSON/string references. The verification path correctly prevents the creator/implementer from verifying their own action, and resolves the NC only when no other open CAPA remains. However, the evidence_reference is not linked to a verified document asset/version/hash; it is concatenated into the effectiveness_result string.

**Required implementation**
- [ ] Lock the source nonconformance; create action and update NC status in one transaction.
- [ ] Add action idempotency/correlation and reject duplicate/conflicting CAPA submissions.
- [ ] Link implementation and effectiveness evidence to retained WP-15 documents with actual-byte hash, version, reviewer and applicable scope.
- [ ] Retain the existing independent effectiveness verifier and other-open-CAPA check.
- [ ] Add rollback, duplicate, concurrent, missing/altered evidence and NC-resolution tests.

### 32.5 Inspection standard and sampling-plan references are not verified operational policies

**Finding:** createInspection() stores inspection_standard_reference and sampling_plan_reference as nullable strings. The service accounts for the submitted sample_quantity and requires accepted plus rejected quantity to match it, but no inspected source path resolves the reference to an approved, versioned sampling plan or evaluates AQL/sample size/acceptance-rejection thresholds by inspection lot, risk, customer, defect class or process. The service marks PASS whenever rejected quantity is zero and the defects list is empty; no sampling-policy evaluator runs before that result.

**Required implementation**
- [ ] Add versioned inspection standards and sampling plans with customer/style/material/process/site/risk applicability and effective dates.
- [ ] Calculate sample quantity and Ac/Re thresholds from the lot size and approved sampling standard; prevent caller-chosen sample quantities from reducing the required inspection.
- [ ] Resolve defect criticality, measurement tolerances and required checks from the approved product/customer quality plan.
- [ ] Store exact standard/plan IDs, revisions and source hashes on the inspection.
- [ ] Calculate pass/fail/conditional disposition from measured observations and policy thresholds, not only the absence of defect rows.
- [ ] Add AQL boundary, sample-size manipulation, critical-defect, tolerance and stale-standard tests.

### 32.6 Manufacturing quality-gate integration is present and must be preserved

**Verified positive behavior:** ManufacturingExecutionService.recordOperationEvent() checks the predecessor route step. If that predecessor is marked quality_gate, the service requires a quality_release_decisions row for that exact predecessor operation with decision RELEASE or RELEASE_WITH_DEVIATION before the next operation can start.

QualityControlService.decideOperationRelease() requires the linked inspection to be for the exact production operation, reviewed as passing, and the release actor to differ from the inspector and reviewer. It also blocks release while open nonconformances remain.

**Remaining strengthening**
- [ ] Add a composite relation/trigger and feature tests proving the release decision's linked inspection belongs to the same tenant and exact operation and remains an accepted/reviewed inspection.
- [ ] Verify a later REJECT/HOLD/REINSPECT decision or opened new nonconformance cannot be bypassed by an earlier RELEASE decision; model decision supersession/current-effective release status explicitly.
- [ ] Lock the operation, inspection and open NC set consistently while issuing a release decision and starting the dependent operation, so a concurrent hold cannot race progression.
- [ ] Ensure final quality gates, including the final operation/finished-goods stage, are enforced by their explicit release service and not inferred from production-order COMPLETED alone.
- [ ] Test predecessor quality gate, prior release then later hold, concurrent release/hold, opened NC after release, and last-operation final inspection.

### 32.7 Integration outbox claim has no lease expiry or worker fencing

**Finding:** claimBatch() claims only PENDING/RETRY events, moves them to PROCESSING and stores locked_at. markDelivered() and markFailed() require PROCESSING, but no method reclaims PROCESSING events after a worker crash/time-out. A worker that crashes after claim can leave an event permanently PROCESSING. There is no claimed_by worker identity, lease expiry, monotonically increasing claim token/fencing version or compare-and-set guard to stop a stale worker from finalizing a later worker's retry.

**Required implementation**
- [ ] Add claimed_by, lease_expires_at, and a monotonically increasing claim/fencing token to each delivery attempt.
- [ ] Reclaim only expired PROCESSING leases; increment attempt count and preserve prior failure/recovery history.
- [ ] Require matching worker identity and fencing token for markDelivered/markFailed.
- [ ] Make the remote connector call idempotent using endpoint+event key+payload hash, and reconcile timeout-after-remote-success before resending.
- [ ] Add tests for worker crash after claim, expired lease, stale worker completion, concurrent reclaimer, remote success with lost response, and dead-letter replay.
- [ ] Continue using the existing FOR UPDATE SKIP LOCKED batch claim and bounded retry/backoff.

### 32.8 Outbox and scan-event idempotency return existing records without checking payload equality

**Finding A — outbox:** enqueueDomainEvent() validates that payload_sha256 looks like a 64-hex hash, looks up an existing row by tenant/event_key and returns it without comparing the existing payload_sha256 to the incoming hash or comparing canonical event/aggregate identity. Reusing an event key with a different payload is silently treated as an identical retry. There is also a race between the read and insert which can surface as a unique-constraint exception.

**Finding B — device scan:** recordScanEvent() similarly looks up by tenant/event_key and returns the existing scan event without comparing device identity, symbology, scanned value, event context, device timestamp or raw metadata. A conflicting scan-key replay is not distinguished from an identical retry. No canonical scan payload hash is stored.

**Required implementation**
- [ ] Calculate the payload hash server-side from canonicalized typed fields; do not trust a caller-supplied digest without recomputing it.
- [ ] On repeated event_key, return the original record only if event type, aggregate, endpoint/device and payload hash are identical.
- [ ] Raise a stable idempotency conflict on different payload or context.
- [ ] Resolve the concurrent insert race by serializing or catching unique conflicts and performing the same payload comparison.
- [ ] Add identical retry, changed-payload same-key, event-key race and cross-device retry tests.

### 32.9 Document extraction and private-object path still lack complete source verification and worker execution

**Finding:** the document service has lifecycle methods for QUEUED, PROCESSING, EXTRACTED, REVIEW_REQUIRED, APPROVED_FOR_IMPORT and REJECTED, but the inspected repository contains no actual extraction worker/provider invocation or typed import adapter. stageExtractionResult() accepts a result while the run is QUEUED or PROCESSING, but it does not require a dedicated worker service principal, provider execution ID, signed result, model/version, input hash or output hash. reviewExtraction() can set APPROVED_FOR_IMPORT, but approval does not create the proposed domain record through its owning service.

Document registration checks the shape of a supplied SHA-256, not actual bytes. Upload hashes the local temporary file but does not read the stored object back; metadata insert failure has no compensating object delete/orphan reconciliation. These are distinct from the separate storage-disk default defect already recorded in Section 4.1/9.4.

**Required implementation**
- [ ] Add worker identity, job lease, provider request ID, model/version, input artifact ID/hash and output hash to each extraction run.
- [ ] Process extraction using a bounded provider adapter and retryable worker; do not accept an arbitrary client as an extraction worker.
- [ ] Verify stored private bytes/size/MIME and recompute SHA before marking checksum_verified_at.
- [ ] Clean up or reconcile a successfully uploaded object whose metadata insert fails.
- [ ] Validate extraction output against a versioned domain schema and record source page/coordinate/field confidence.
- [ ] Import only after human approval through the owning WP service; bind imported record to exact document and approved field snapshot.
- [ ] Add tests for byte alteration, missing objects, orphan cleanup, worker lease/replay, stale provider results, malformed values and approved import idempotency.

---

## 33. WP-11 / WP-15 implementation sequence

1. Correct inspection submit/review status model using a forward database migration.
2. Implement linked, versioned deviation and containment approval records; remove request booleans as authority.
3. Implement nonconformance disposition authority and actor/evidence audit.
4. Make CAPA creation atomic/idempotent and link implementation/effectiveness evidence to verified documents.
5. Implement versioned inspection standards/AQL/sample-size and defect/tolerance evaluation.
6. Preserve the existing predecessor quality-gate enforcement; add locking/current-decision supersession and final-goods gate tests.
7. Add outbox lease/fencing/reclaim behavior and payload-aware idempotency for outbox/device scans.
8. Implement extraction workers, verified private-object integrity and approved typed domain import.
9. Add WP-11/WP-15 PostgreSQL, API, retry/concurrency and browser workflow test suites; record exact tested SHA.


---

## 34. WP-08 / WP-10 / WP-11 — Route effectiveness, quality release authority and operation-state integrity

### 34.1 Approved route versions are not resolved against effective dates

**Finding:** process_route_versions stores effective_from and effective_to, allows approved/superseded states and checks only that the end date is not before the start date. decideRoute() approves a submitted route without checking whether its effective period overlaps another approved route for the same tenant/style revision. createProductionOrder() selects a route by caller-supplied ID, matching style/revision and status APPROVED; it does not compare the route's effective period to the production order's planned start date. There is no inspected service method that transitions an older route to SUPERSEDED.

**Effect:** two approved route versions can overlap for the same style revision, or an order planned before/after a route's valid interval can use that route. Route identity is pinned to the order, but route applicability to the execution date is not proven.

**Source**
- backend/database/migrations/2026_11_26_000016_create_route_and_manufacturing_execution.php, process_route_versions.effective_from/effective_to, status constraint and unique version key.
- backend/app/Services/Manufacturing/ManufacturingExecutionService.php, createRouteVersion(), decideRoute() and createProductionOrder().
- backend/app/Http/Controllers/Manufacturing/ManufacturingController.php, route and order request contracts.

**Required implementation**
- [ ] Require a non-null effective-from date for routes that can be used for bulk production.
- [ ] Define date interval semantics and enforce non-overlap for APPROVED route versions for the same tenant, style revision and applicable site/process scope.
- [ ] Resolve and pin the route version from the production order planned start date and applicable style/site/customer/process dimensions; do not accept an arbitrary route ID as proof of applicability.
- [ ] Implement an authorized supersession lifecycle with reason, actor, timestamp and the successor route version; preserve historical order pins.
- [ ] Revalidate the pinned route before order release and actual start; a later route change must not rewrite a released order's route snapshot.
- [ ] Add tests for overlapping approval, gap periods, null effective date, future-dated order, superseded route, backdated correction and historical order reproducibility.

### 34.2 A historical quality RELEASE can override a later HOLD/REJECT during route progression

**Finding:** QualityControlService.decideOperationRelease() appends a new quality_release_decisions row for RELEASE, RELEASE_WITH_DEVIATION, HOLD, REJECT or REINSPECT. The schema is append-only for the application role and has an index by subject/date, but no current-decision pointer or unique effective-decision version. In ManufacturingExecutionService.recordOperationEvent() a predecessor quality gate is considered released when a query finds any decision for that operation with decision RELEASE or RELEASE_WITH_DEVIATION. It does not select the latest decision or require the latest decision to remain a release.

**Effect:** if a quality release is recorded and a later decision puts the operation on HOLD, REJECT or REINSPECT, the old positive row still satisfies the predecessor gate. A downstream operation can start even though the later quality disposition has withdrawn permission to proceed.

**Source**
- backend/app/Services/Quality/QualityControlService.php, decideOperationRelease() around lines 405–450.
- backend/database/migrations/2026_11_26_000017_create_quality_capa_and_release_gates.php, quality_release_decisions and quality_release_subject_idx.
- backend/app/Services/Manufacturing/ManufacturingExecutionService.php, recordOperationEvent() START predecessor quality-gate check around lines 286–302.

**Required implementation**
- [ ] Define one effective quality-release state per subject and release stage, versioned over the decision history.
- [ ] When evaluating a gate, resolve the latest effective decision under a row lock or immutable current-decision pointer; proceed only if that decision is RELEASE or RELEASE_WITH_DEVIATION and all conditions remain satisfied.
- [ ] Make HOLD/REJECT/REINSPECT supersede the previous release immediately, including queued downstream work where operationally possible.
- [ ] Require downstream release to revalidate the current linked inspection, open NCR/CAPA state, deviation conditions and exact operation/route revision.
- [ ] Add tests for RELEASE → HOLD, RELEASE → REJECT, RELEASE → REINSPECT, a subsequent authorized re-release, concurrent release/hold decisions and retry/replay.

### 34.3 PASS_WITH_DEVIATION is derived from a caller-supplied boolean, not a verified deviation approval

**Finding:** QualityController.submitInspectionResult() accepts deviation_approved as a client boolean. QualityControlService.submitInspectionResult() sets the inspection result to PASS_WITH_DEVIATION when that boolean is true, without loading a linked deviation/waiver record, approved concession, risk assessment, expiry, affected quantity or authorized approver. reviewInspection() can subsequently ACCEPT the PASS_WITH_DEVIATION result, and decideOperationRelease() accepts either PASS or PASS_WITH_DEVIATION as the basis for RELEASE as well as RELEASE_WITH_DEVIATION. Thus the client-supplied boolean can influence a controlled quality state without a distinct deviation authorization record.

**Effect:** the system cannot prove what requirement was deviated from, who authorized it, the scope/quantity/time/customer applicability, the controls/conditions or whether an approval is still valid. An ordinary inspection submission may be represented as an approved deviation before the separate reviewer/release step.

**Source**
- backend/app/Http/Controllers/Quality/QualityController.php, submitInspectionResult() validation of deviation_approved.
- backend/app/Services/Quality/QualityControlService.php, submitInspectionResult(), reviewInspection() and decideOperationRelease().
- backend/database/migrations/2026_11_26_000017_create_quality_capa_and_release_gates.php, quality_inspections and quality_release_decisions.

**Required implementation**
- [ ] Remove client authority to assert deviation approval. Inspection submission may request a deviation review but must not mark the deviation approved.
- [ ] Add a versioned deviation/concession record linked to exact inspection, product/style revision, operation, customer requirement, defect/NCR, affected quantity/lot, validity period and documented risk/containment conditions.
- [ ] Require an independent position/DoA-authorized reviewer and, where applicable, customer approval before a deviation can be accepted.
- [ ] Permit PASS_WITH_DEVIATION only when the independent deviation decision is valid and pinned; record the deviation ID and decision version on the inspection.
- [ ] At operation/material/finished-goods/shipment release, require RELEASE_WITH_DEVIATION where a deviation is present and verify all stated conditions; do not allow a generic RELEASE to erase the deviation context.
- [ ] Test forged boolean, missing/expired/revoked approval, wrong style/customer/operation/lot, quantity over approved scope, approval by creator/inspector and deviation amendment after release.

### 34.4 BLOCK events can alter completed or skipped operations

**Finding:** in ManufacturingExecutionService.recordOperationEvent(), the BLOCK branch updates the operation status to BLOCKED without validating its prior state. The same event endpoint can therefore move a COMPLETED, SKIPPED or QUEUED operation to BLOCKED. UNBLOCK then changes any BLOCKED operation to IN_PROGRESS without restoring or verifying its prior state. The database status constraint permits the resulting values and does not encode a legal transition graph.

**Effect:** completed work can be reopened by a later generic event, historical completed_at can remain while the state is no longer COMPLETED, and an optional skipped operation can become executable without a controlled reopen/revision decision. This undermines production quantity and genealogy finality.

**Source**
- backend/app/Services/Manufacturing/ManufacturingExecutionService.php, recordOperationEvent() BLOCK and UNBLOCK branches around lines 340–356.
- backend/database/migrations/2026_11_26_000016_create_route_and_manufacturing_execution.php, production_order_operations.status constraint and production event table.
- backend/app/Http/Controllers/Manufacturing/ManufacturingController.php, recordOperationEvent() accepts BLOCK/UNBLOCK through the general manufacturing execution ability.

**Required implementation**
- [ ] Define and enforce an explicit operation state-transition matrix in the domain service and, for critical invariants, in PostgreSQL.
- [ ] Allow BLOCK only from an authorized active state such as QUEUED/READY/IN_PROGRESS; a completed/skipped operation must be corrected through a separate approved correction/reopen workflow.
- [ ] Preserve prior state and block owner/reason; UNBLOCK must restore the permitted prior state after required checks rather than always forcing IN_PROGRESS.
- [ ] Require appropriate supervisor/quality authority for block/unblock and keep actor/reason/condition evidence in the event.
- [ ] Add tests for block from every status, unblock from every block origin, completed/skipped immutability, concurrent block/unblock, event replay and order-completion consistency.

### 34.5 WP-08/WP-10/WP-11 integration acceptance sequence

1. Implement route effective-date and supersession resolution with a forward migration and exact route-version resolver.
2. Implement the latest-effective quality release decision contract and use it at every predecessor/operation/finished-goods/shipment gate.
3. Replace deviation_approved input with a controlled deviation/concession approval lifecycle and exact inspection/release linkage.
4. Implement the production-operation state transition matrix for block, unblock, skip, start, output, defect, downtime and completion.
5. Add PostgreSQL Feature tests under the non-owner application role for every transition, tenant/site boundary, approval separation, duplicate/conflicting replay and concurrency.
6. Add a browser E2E journey for route revision/effectivity → production order → optional/conditional operation → inspection → deviation review (where applicable) → quality release/hold → downstream operation start.
7. Record exact candidate SHA, route/inspection/release snapshots and test evidence in the WP-08/WP-10/WP-11 acceptance ledger.

No manufacturing or quality runtime source/migration was changed by this audit section; it records verified source/schema control gaps and their forward implementation path.


---

## 35. WP-07 / WP-09 — Inventory ledger unit-of-measure and scrap-disposition binding

### 35.1 Inventory balances sum quantities without proving a common unit of measure

**Finding:** inventory_transactions stores a caller-supplied unit_code for each movement. fabric_rolls separately stores its unit_code. However, material_lots has no stock/base unit field, the inspected sourcing migration does not define a material-unit conversion table or versioned conversion contract, and InventoryLedgerService::postMovement() does not compare a movement unit with the relevant roll/trim/material unit. InventoryLedgerService::balanceAt() sums all inbound and outbound numeric quantities for a lot/roll/trim/location without filtering by unit_code or converting them to a common base unit.

**Effect:** a lot can have ledger rows recorded in different units and the balance calculation will add/subtract the raw numeric values as if they were the same dimension. A quantity of metres, kilograms, pieces, boxes or rolls cannot be safely added without an approved conversion and a dimension-compatible base unit. Reservation, issue, receipt and quality-release decisions that depend on this balance can therefore produce a false available quantity.

**Source**
- backend/app/Services/Inventory/InventoryLedgerService.php, postMovement() around lines 15–177 and balanceAt() around lines 179–208.
- backend/database/migrations/2026_11_26_000015_create_sourcing_material_traceability.php: purchase_order_lines.unit_code, goods_receipt_lines.unit_code, fabric_rolls.unit_code and inventory_transactions.unit_code.
- material_lots in migration 000015 has no unit_code/base-unit column; trim_lots has no unit_code field; inventory_transactions unit_code has no foreign key to a canonical UOM identity.
- backend/app/Services/Sourcing/SourcingService.php, materializeInspectedReceiptLine() copies the PO-line unit to the roll, receipt-line materialization and receipt movement; subsequent movement callers can still supply their own unit_code.

**Required implementation**
- [ ] Define a canonical UOM catalogue with dimension family, precision, permitted scale, base-unit designation and effective-dated approved conversion factors.
- [ ] Bind each material master/material lot and each fabric roll/trim lot to an immutable base-unit identity; retain the supplier/PO transaction unit separately.
- [ ] Normalize every posted movement into the lot's base unit on the server, preserving the input quantity/unit and the exact conversion ID/version/hash.
- [ ] Reject incompatible dimensions, unknown units, expired conversions and non-authorized manual conversion overrides.
- [ ] Make balanceAt() aggregate only normalized base quantities or explicitly group by a common validated unit; do not sum different unit codes as raw numerics.
- [ ] Ensure production BOM consumption, material issue, roll-length consumption, trim quantity, stocktake, quality release and finished-goods packaging use the same typed conversion contract.
- [ ] Add tests for m/yd, kg/g, pcs/dozen, roll/length, pack/carton, fractional precision, incompatible dimensions, conversion expiry, rounding and concurrent issues.

### 35.2 SCRAP can use a resolved nonconformance that belongs to a different lot

**Finding:** the SCRAP path in InventoryLedgerService::postMovement() loads quality_nonconformances by tenant and caller-supplied source_document_id, and checks only disposition = SCRAP and status RESOLVED/CLOSED. It does not join the nonconformance to its quality inspection and require that inspection subject_type = MATERIAL_LOT and subject_id equals the material_lot_id being scrapped. The source-document identity can therefore be a valid, resolved SCRAP nonconformance for another material lot in the same tenant.

**Effect:** a valid scrap decision for one lot can be reused as the authorization evidence for scrapping a different lot. The movement remains tenant-isolated and append-only, but the disposition-to-stock genealogy is not bound to the same physical material.

**Source**
- backend/app/Services/Inventory/InventoryLedgerService.php, SCRAP validation in postMovement() around lines 95–108.
- backend/database/migrations/2026_11_26_000015_create_sourcing_material_traceability.php, inventory_transactions.source_document_type/source_document_id and tenant/lot foreign keys.
- backend/database/migrations/2026_11_26_000017_create_quality_capa_and_release_gates.php, quality_nonconformances.inspection_id and quality_inspections.subject_type/subject_id.

**Required implementation**
- [ ] Resolve the quality nonconformance through its linked inspection and require exact MATERIAL_LOT subject identity for the lot being scrapped; use a different typed source flow for operation/bundle/finished-goods scrap.
- [ ] Verify disposition, resolved/closed state, affected quantity, affected reference and approved scrap decision against the requested movement quantity and lot/roll/trim identity.
- [ ] Bind the exact approved disposition decision ID/version and inspection ID in the inventory movement source manifest.
- [ ] Prevent reuse of one approved nonconformance quantity across multiple scrap movements; maintain a consumed/remaining disposition quantity or a one-time decision key.
- [ ] Require the inventory lot/roll/trim to be in an allowed status and location; preserve full source-to-scrap genealogy.
- [ ] Add negative tests for a valid NC from another lot, another material type, another tenant, partial/over-scrap, repeated scrap, wrong roll/trim and concurrent scrap replay.

### 35.3 WP-07/WP-09 material-ledger acceptance sequence

1. Define canonical base UOM identity, versioned conversion and source/entered-quantity model.
2. Add forward schema changes and backfill existing material/roll/trim identities using verified source data; quarantine unresolvable legacy rows rather than guessing.
3. Normalize inventory postMovement() and balanceAt() to one base unit with exact conversion provenance.
4. Bind SCRAP movements to the precise resolved NC, linked inspection subject, lot/roll/trim and approved disposition quantity.
5. Add PostgreSQL Feature tests under app_runtime for every movement type, UOM incompatibility, source mismatch, tenant boundary, payload replay and concurrent issue/scrap.
6. Add receipt → quarantine → inspection → release → roll/trim issue → production consumption → scrap/rework reconciliation journeys with exact unit and genealogy snapshots.
7. Record the accepted UOM and scrap control evidence against WP-07/WP-09 requirement IDs.

No sourcing, inventory, quality or production runtime source/migration was changed by this audit section; it records verified service/schema gaps and the forward build path.


---

## 36. WP-09 / WP-10 — Production material issue, consumption and WIP genealogy are not connected to the inventory ledger

### 36.1 Production execution does not post material reservations, issues, returns or consumption

**Finding:** ManufacturingExecutionService creates production orders and operation snapshots, then records operation START, OUTPUT, DEFECT, DOWNTIME, BLOCK, UNBLOCK, COMPLETE and SKIP events. It does not inject or call InventoryLedgerService and does not expose a material reservation, material issue, return-to-store, operation consumption or scrap/yield posting action. Its operation rows hold planned/good/defective quantities, but no material requirement, issue quantity, consumed quantity, returned quantity or material-lot/roll reference.

InventoryLedgerService supports generic ISSUE, CONSUMPTION, TRANSFER and SCRAP movements, but these are not connected to the production order/operation/bundle lifecycle by an executable WP-09 service path.

**Effect:** a production order can advance through output events without a corresponding authoritative movement of the required fabric roll, trim lot, packaging or chemical. The ledger can show stock balances while production execution independently reports output. The system cannot reliably reconcile BOM requirement → allocated stock → issued roll/trim → operation consumption → returned remainder → scrap/waste → completed garment quantity.

**Source**
- backend/app/Services/Manufacturing/ManufacturingExecutionService.php: createProductionOrder(), releaseProductionOrder(), startProductionOrder() and recordOperationEvent(); inspected public methods contain no material movement command or InventoryLedgerService dependency.
- backend/app/Http/Controllers/Manufacturing/ManufacturingController.php: route/order/operation-event endpoints only; no material issue/return/consumption endpoint.
- backend/app/Services/Inventory/InventoryLedgerService.php: postMovement() supports generic material movement types without production-specific orchestration.
- backend/database/migrations/2026_11_26_000015_create_sourcing_material_traceability.php: inventory_transactions has generic source_document_type/source_document_id/source_line_id fields; those source IDs are not foreign keys to production_orders, production_order_operations or cutting_bundles.
- backend/database/migrations/2026_11_26_000016_create_route_and_manufacturing_execution.php: production_order_operations contains output/status fields but no material issue/consumption ledger link.
- backend/database/migrations/2026_11_22_000012_create_bom_and_tech_pack_controlled_releases.php: BOM lines provide product requirements, but no production-order material reservation/issue transaction is created by the manufacturing service.

**Required implementation**
- [ ] Add a production material requirement snapshot for each order, pinned to exact released tech-pack/BOM version, order quantity, approved substitutions, UOM conversion and wastage/yield assumptions.
- [ ] Calculate required material by BOM line and approved size/colour/quantity allocation; persist the formula/version and input snapshot before order release.
- [ ] Add material reservation and picking by legal entity/site/warehouse/location, fabric roll, shade/dye lot, trim lot, packaging and chemical batch.
- [ ] Add issue-to-production and issue-to-operation commands that post the inventory ledger event atomically with the production material issue record and bind the movement to the exact order, operation, bundle and source BOM line.
- [ ] Add controlled return-to-store, transfer, partial issue, substitute issue, scrap, rework, loss and offcut/remnant paths; preserve original movements and use compensating events rather than edits.
- [ ] Add material consumption/backflush only for the explicitly approved process point and evidence source; never infer consumed stock from completed good quantity alone.
- [ ] Reconcile issued = consumed + returned + WIP-held + approved loss/scrap by material lot/roll/trim, using canonical base UOM.
- [ ] Enforce no issue from quarantine/blocked stock, no excess issue beyond approved tolerance, no cross-site/entity movement and no issue against a superseded BOM.
- [ ] Add production-to-inventory foreign-key or typed source-reference controls so the inventory ledger cannot point to arbitrary/nonexistent production IDs.
- [ ] Add tests for BOM version change after pick, shade mismatch, partial roll issue, cut waste, trim variance, return, substitute, over-issue, cross-site issue, concurrent issue, replay and complete order-to-ledger reconciliation.

### 36.2 WP-09/WP-10 acceptance path

1. Release a production order only after the approved BOM/material requirement snapshot and route/IE/quality dependencies are pinned.
2. Reserve and pick the exact material lots/rolls/shades and trims; preserve the location and custody trail.
3. Issue material to the order and operation through a transactionally linked inventory event.
4. Record cutting/lay/bundle output and trace material allocation to each bundle and downstream operation.
5. Record consumption, return, waste, defect and rework using typed movements and exact base-unit conversion.
6. Reconcile the material ledger and production output against BOM quantity, expected yield and approved variance.
7. Run tenant/site boundary, stock shortage, concurrent issue, payload replay and full roll/trim-to-finished-goods genealogy tests on the same exact candidate SHA.

No production or inventory runtime source/migration was changed by this audit section; it records the missing cross-domain transaction path and its forward build sequence.


---

## 37. WP-11 quality disposition and final-route gate — additional source validation

### 37.1 Defect catalogue lifecycle flags are not enforced on inspection submission

**Finding:** submitInspectionResult() loads a selected quality_defect_catalog row by tenant and ID, then uses its default_severity only as a default. It does not require active = true, and it does not evaluate the catalogue's requires_containment flag. The submitted request's containment_confirmed boolean controls both the inspection result (CONTAINED versus FAIL) and the nonconformance containment_status.

An inactive defect code can therefore still be recorded on a new inspection. A defect code configured to require containment is not independently enforced by the service; the client boolean is treated as proof of containment.

**Source**
- backend/app/Services/Quality/QualityControlService.php, submitInspectionResult().
- backend/database/migrations/2026_11_26_000017_create_quality_capa_and_release_gates.php, quality_defect_catalog.active and requires_containment.
- backend/app/Http/Controllers/Quality/QualityController.php, request fields containment_confirmed and defects.*.defect_catalog_id.

**Required implementation**
- [ ] Require selected defect-catalog entries to be active and effective for the inspection's site/process/product scope.
- [ ] Enforce requires_containment using an actual containment record with affected item/quantity/location, responsible owner, timestamp, action and evidence; never accept a boolean as evidence.
- [ ] Keep severity and criticality derived from the approved catalog/quality plan; an override must have a reason and authorized reviewer.
- [ ] Add inactive-code, required-containment, partial-containment, wrong-quantity and unauthorized-severity-override tests.

### 37.2 Operation-release service requires a passing inspection even for HOLD, REJECT or REINSPECT decisions

**Finding:** QualityController.releaseProductionOperation() accepts RELEASE, RELEASE_WITH_DEVIATION, HOLD, REJECT and REINSPECT. However, QualityControlService.decideOperationRelease() first loads the inspection with result_status restricted to PASS or PASS_WITH_DEVIATION for every one of those decisions. A failed or contained inspection cannot be linked to a HOLD/REJECT/REINSPECT decision, despite those being exposed API choices. The current state model cannot faithfully record the natural rejection/hold decision against a failed inspection.

**Source**
- backend/app/Http/Controllers/Quality/QualityController.php, releaseProductionOperation().
- backend/app/Services/Quality/QualityControlService.php, decideOperationRelease().
- backend/database/migrations/2026_11_26_000017_create_quality_capa_and_release_gates.php, inspection result states and release-decision vocabulary.

**Required implementation**
- [ ] Define which inspection states may support each release decision: passing reviewed inspection for RELEASE; valid linked failure/contained/NCR evidence for HOLD/REJECT/REINSPECT.
- [ ] Keep RELEASE and RELEASE_WITH_DEVIATION gated by independent passing review, approved deviation record, no open blocking NCR and required authority.
- [ ] Allow HOLD/REJECT/REINSPECT only to increase restriction; do not require a passing result merely to record a stop/rejection.
- [ ] Add full transition tests for PASS/FAIL/CONTAINED × RELEASE/HOLD/REJECT/REINSPECT and confirm only valid combinations are accepted.

### 37.3 A final route-step quality gate is not evaluated before production-order completion

**Finding:** ManufacturingExecutionService.recordOperationEvent() checks a predecessor step's quality_gate when a subsequent operation receives START. The gate check is therefore exercised only when there is a downstream operation starting. The COMPLETE branch for the current operation checks only that its status is IN_PROGRESS and good plus defective output equals planned quantity. Once no operation remains outside COMPLETED/SKIPPED, the service marks the production order COMPLETED.

For a final route operation flagged quality_gate, there is no subsequent operation to trigger the predecessor check. That final route-step gate can therefore be omitted from the completion transition. A separate final production-order inspection is required before finished-goods receipt, but that does not prove that the route's own final-step gate was enforced.

**Source**
- backend/app/Services/Manufacturing/ManufacturingExecutionService.php, recordOperationEvent() START predecessor gate, COMPLETE branch and order-completion test.
- backend/database/migrations/2026_11_26_000016_create_route_and_manufacturing_execution.php, process_route_steps.quality_gate.
- backend/app/Services/Quality/QualityControlService.php, decideOperationRelease().

**Required implementation**
- [ ] Evaluate a current effective release decision before completing any quality-gated operation, including the last route step.
- [ ] Separate operation output complete from operation quality released and production order complete; final order completion must reconcile all required quality gates.
- [ ] Require the exact operation, inspection, approved deviation/conditions and absence of blocking NCRs for every gate.
- [ ] Add tests for a quality-gated middle step, final step, later HOLD after release, open NCR after release and concurrent final release/order completion.

---

## 38. WP-15 integration/document and agent governance — database runtime permission and workflow audit

### 38.1 Least-privilege database grants do not permit the agent and device services to perform their updates

**Finding:** migration 2026_11_26_000021_create_documents_integrations_and_agent_controls.php explicitly revokes all grants from app_runtime on agent and device tables, then grants:

- SELECT, INSERT, UPDATE on document_assets, document_extraction_runs, integration_endpoint_registry and integration_outbox_events;
- SELECT, INSERT only on agent_run_audits, agent_action_proposals, agent_approval_decisions, agent_action_events and device_scan_events.

The service code requires UPDATE on three of those read/insert-only tables:
- AgentGovernanceService.finishRun() updates agent_run_audits;
- AgentGovernanceService.decideAction() and executeApprovedDraft() update agent_action_proposals;
- DeviceScanResolutionService.resolve() updates device_scan_events to RESOLVED, AMBIGUOUS or REJECTED.

The later canonical migrations 000022–000032 do not add the missing table/column update grants. Under the repository's intended app_runtime application role, run finalization, proposal decision/execution and scan resolution will fail at the database permission boundary even when the application Gate authorizes the caller. A test suite connected as a schema owner can mask this deployment failure.

**Source**
- backend/database/migrations/2026_11_26_000021_create_documents_integrations_and_agent_controls.php, final GRANT statements.
- backend/app/Services/Agents/AgentGovernanceService.php, finishRun(), decideAction() and executeApprovedDraft().
- backend/app/Services/Integration/DeviceScanResolutionService.php, resolve().
- Migrations 000022–000032 contain no subsequent GRANT UPDATE for these tables.

**Required implementation**
- [ ] Add least-privilege column-level UPDATE grants for only the transition fields required by each service, or use narrowly scoped security-definer transition functions with verified actor/tenant context.
- [ ] Do not broadly grant UPDATE to immutable event/approval tables: agent_approval_decisions and agent_action_events should remain append-only.
- [ ] Add migration-upgrade tests which connect as app_runtime, execute run completion, proposal decision/execution and scan resolution, and prove unrelated columns/rows remain immutable.
- [ ] Include a deployment smoke test against the same non-owner role used by production, not only the migration/schema owner.

### 38.2 A0/A1 agent proposals have no reachable execution transition

**Finding:** proposeAction() creates A0/A1 proposals with status PROPOSED. decideAction() explicitly rejects A0/A1 because they do not enter the material-action approval path. executeApprovedDraft() accepts only status APPROVED. There is no separate direct, bounded A0/A1 draft-execution transition or explicit finalization state. Therefore any A0/A1 proposal created through the current endpoint remains PROPOSED and cannot be executed; A2/A3 follow the human approval route.

The design note says A0/A1 read/draft actions should not enter the material-action approval path, but the service does not define the corresponding safe completion path for an allow-listed draft action.

**Required implementation**
- [ ] Define the precise A0/A1 capability boundary and its authorization policy.
- [ ] For approved low-risk draft-only capabilities, add an explicit non-material execution state and capability-specific validation; do not silently promote arbitrary A0/A1 payloads.
- [ ] Keep A2/A3 human maker-checker approval and keep A4 outside autonomous execution.
- [ ] Add tests proving permitted low-risk draft creation reaches a traceable terminal state and all non-allow-listed or material actions remain blocked.

### 38.3 Document extraction can start before storage integrity and document classification are verified

**Finding:** beginExtraction() rejects only a missing document or a document whose classification_status is REJECTED. It does not require checksum_verified_at to be populated, and it does not require an accepted classification state before creating a QUEUED extraction run. document_assets.checksum_verified_at defaults to NULL and is not set by registerDocument() or storeUploadedFile() in the inspected source.

A caller can therefore create an extraction run against a document whose stored bytes have not been read back and verified and whose classification remains PENDING. This is inconsistent with the intended quarantine/classify/verify-before-extraction sequence.

**Required implementation**
- [ ] Require actual stored-object checksum/size verification and approved file classification before extraction can be queued.
- [ ] Add malware scanning/quarantine status and enforce it as a precondition for retrieval or extraction.
- [ ] Re-check object version/hash at worker claim and before import; fail closed if the object changed or is missing.
- [ ] Add unverified, pending, rejected, restricted, altered-object and verified-document extraction tests.

### 38.4 Document extraction approval does not enforce independent reviewer separation

**Finding:** reviewExtraction() receives reviewerId and stores it as reviewed_by, but it does not compare reviewerId with document_extraction_runs.created_by or with an authenticated extraction worker identity. The same user who queued an extraction can approve its values for import. The controller's documents.approve-import Gate is a useful capability check, but the service itself does not enforce the maker-checker boundary.

**Required implementation**
- [ ] Require a reviewer distinct from the document/extraction requester and distinct from the authenticated extraction service principal.
- [ ] Bind reviewer authority to document classification, target domain, access class and import risk.
- [ ] Keep source worker identity separate from the human reviewer; store provider/model/version/request correlation and output hash.
- [ ] Add same-actor, shared-account, provider-identity, unauthorized-domain and cross-tenant tests.

### 38.5 Outbox enqueue does not enforce endpoint activation or event allow-list

**Finding:** enqueueDomainEvent() inserts endpoint_id as nullable and does not load integration_endpoint_registry to verify that the endpoint exists, is ACTIVE, is OUTBOUND/BIDIRECTIONAL, and explicitly allows the requested event_type. The endpoint table contains status, direction, granted_scopes and allowed_event_types, but the enqueue path does not apply those controls. A row can therefore be queued with no endpoint or an inactive/unapproved endpoint; the delivery worker cannot safely infer permission from the event record alone.

**Required implementation**
- [ ] Require a non-null endpoint for outbound delivery events and resolve it under tenant/legal-entity scope.
- [ ] Verify ACTIVE status, direction, approved scopes and event-type allow-list at enqueue and again immediately before delivery.
- [ ] Record the exact endpoint configuration version/secret reference and event contract version used for each event.
- [ ] Reject suspended/retired endpoint, wrong direction, disallowed event and scope mismatch.
- [ ] Keep endpoint suspension effective for pending events and provide a reasoned reactivation/replay workflow.

### 38.6 Agent/device and document controls need a final runtime-role E2E suite

- [ ] Execute full agent run → proposal → approval → execution under app_runtime; include A0/A1 and A2/A3 pathways, same-actor denial, request changes/revision and output attribution.
- [ ] Execute scan insert → resolve as app_runtime; confirm the scan's resolver actor/time and result are retained.
- [ ] Execute document upload → object read-back checksum → classification → extraction worker → independent review → typed domain import under separate service identities.
- [ ] Execute outbox enqueue → endpoint approval → lease claim → adapter success/retry/dead-letter/reclaim under app_runtime.
- [ ] Include least-privilege denial tests for direct mutation of immutable approval, ledger, audit and event tables.

---

## 39. WP-11 / WP-15 follow-on implementation sequence

1. Enforce defect-catalog activity and real containment evidence.
2. Correct operation release decision/state compatibility and final route-step quality-gate evaluation.
3. Apply narrow runtime UPDATE grants or audited transition functions for agent/proposal/device state changes.
4. Define and implement the safe A0/A1 agent draft lifecycle without weakening A2/A3/A4 controls.
5. Block document extraction until private-object integrity and classification are verified; add independent reviewer separation.
6. Enforce endpoint activation/direction/event scope at enqueue and delivery.
7. Run the relevant PostgreSQL, API and end-to-end suites as app_runtime on an exact candidate SHA and record the actual runner evidence.


---

## 40. WP-03 organization structure runtime candidate — code review and remaining build

**Candidate branch:** `work/wp03-org-master-runtime-2026-11-27`  
**Current exact head:** `f9aebe855a05dec30f0b1f4bc5f1acbd741f385b`  
**Candidate PR:** [#21 WP-03 organization runtime](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/21), draft; 15 changed files. The PR branch is a separate integration candidate, not part of the canonical delivery branch.  
**Latest commit message:** `a11y(wp03): add visually hidden table captions`  
**PR status:** PR #21 is open and remains a draft pending exact-SHA verification and integration review.

This is executable source, not merely the organogram/DoA design document.

### 40.1 Rendered source already on the branch

| Path | Purpose |
|---|---|
| `backend/database/migrations/2026_11_27_000035_create_organization_structure_masters.php` | Organization schema, indexes, tenant RLS and runtime grants |
| `backend/app/Services/Enterprise/EnterpriseOrganizationService.php` | Tenant-scoped unit, job-position, manager-assignment and reporting-relation commands/queries |
| `backend/app/Http/Controllers/Enterprise/EnterpriseOrganizationController.php` | API boundary for organization structure |
| `backend/routes/domain-api.php` | Organization-unit, position, manager-assignment and reporting routes |
| `backend/tests/Feature/EnterpriseOrganizationStructureTest.php` | PostgreSQL-backed tenant, role, integrity and RLS test cases |
| `frontend/src/app/features/enterprise/enterprise-organization.service.ts` | Angular API service |
| `frontend/src/app/features/enterprise/organization-structure.component.ts` | Organization structure workflow component |
| `frontend/src/app/features/enterprise/organization-structure.component.html` | Operator screen |
| `frontend/src/app/features/enterprise/organization-structure.component.css` | Screen styles |
| `frontend/src/app/features/enterprise/organization-structure.component.spec.ts` | Component tests |
| `frontend/src/app/app.routes.ts`, `app.component.ts` | Feature route and navigation integration |
| `docs/architecture/application-folder-code-map.md` | Source-map update |
| `docs/verification/WP03-implementation-acceptance-matrix.md` | WP-03 acceptance status update |
| `docs/work-packages/handover-register.md` | Handover update |

### 40.2 What this branch actually implements

Migration 000035 creates four tenant-owned tables:
- `org_units`: organizational unit hierarchy with legal-entity/site context, parent relationship, status and effective dates.
- `job_positions`: approved job slots linked to organizational units, with position code/title, status and effective dates.
- `org_unit_manager_assignments`: effective-dated manager-position assignment to an organizational unit.
- `position_reporting_relations`: effective-dated position-to-position reporting relationships.

The migration adds indexes, enables and forces row-level security, creates tenant isolation policies and grants constrained table privileges to the runtime role.

The Laravel service exposes organization-unit and position reads/creation, manager assignment/listing and position reporting relation creation/listing. It uses tenant-scoped queries and transactions, checks manager and position membership within the tenant, enforces entity/site consistency, detects overlapping manager/reporting periods and checks solid-line reporting cycles. Writes are accompanied by audit/outbox events.

The feature tests include tenant administrator versus ordinary member access, unit hierarchy/entity/site validation, job positions kept separate from login/employee identity, effective-dated manager assignments, reporting overlap/cycle rejection, cross-tenant list/foreign-key isolation, and runtime table grants/forced RLS.

The Angular feature supplies an operator-facing organization structure screen, API service, route and component test.

### 40.3 What remains before WP-03 organization and DoA acceptance

- [ ] Add effective-dated employee/person-to-position incumbent assignments, including vacancy, acting assignment, multiple permitted assignments, employment status and legal-entity/site/department restrictions. Keep login identity, employee identity, position and domain role separate.
- [ ] Integrate these assignments with the WP-14 employee/employment master; the job-position slot must not be mistaken for its incumbent.
- [ ] Complete organization changes, history, archival, reorganization/version snapshots, matrix/functional reporting and effective-date conflict handling.
- [ ] Implement versioned Delegation of Authority policies: transaction type, legal entity, department, site, cost centre, budget status, currency, amount band, risk, approval count, incompatible duties, escalation and validity dates.
- [ ] Resolve the active position incumbent and valid acting delegate at each approval step. Store the precise DoA policy/route snapshot and reject a changed, expired or unauthorized approver.
- [ ] Connect DoA to purchase requisition/PO, supplier award, budget, payment and other controlled transaction workflows; tenant `OWNER` or `ADMIN` must not become an automatic business approver.
- [ ] Add SSO/MFA and HR lifecycle integration for role changes, leave, separation and delegated access revocation where required by WP-03.
- [ ] Add negative tests for vacant positions, overlapping incumbent assignments, expired acting authority, same-person maker/checker, wrong legal entity/site/cost centre, changed policy after submission, currency mismatch, budget overrun, position/tenant spoofing and concurrent approvals.
- [ ] Update OpenAPI contracts and end-to-end browser workflows; run migrations and tests against PostgreSQL under the restricted runtime role.
- [ ] Rebase/reconcile against the current delivery after WP-03 migrations 000033/000034 are reviewed, then execute exact candidate and post-integration SHA checks.

**CI evidence at the earlier inspected head:** workflow runs #46010970370 and #46010970310 reported no runner/zero steps. The current candidate head has additional commits; its own workflow evidence must be checked separately. Do not treat the earlier run as current-head test evidence.

---

## 41. WP-14 Ethiopia payroll candidate — implementation audit and localization release path

**Candidate branch:** `work/wp14-ethiopia-payroll-localization-2026-11-27`  
**Current exact head:** `77177899e59a1606134b8d70e21104efdb7f03d1`  
**Candidate PR:** [#20 WP-14 Ethiopia payroll localization](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/20), draft; 24 changed files after the latest storage and payroll-integrity updates.  
**Latest commit message:** `docs(WP-14): align handover register to latest payroll branch and CI evidence`  
**PR status:** PR #20 is open and remains a draft; no payroll activation or production posting is authorized by this candidate.

### 41.1 Rendered source and target paths

| Path | Current candidate contribution |
|---|---|
| `backend/app/Services/Payroll/EthiopiaPayrollCalculator.php` | Ethiopia PAYE/pension candidate calculator, source-backed approved-rule path and calculation evidence |
| `backend/app/Services/Finance/FinanceControlService.php` | Payroll draft creation, frozen rule snapshot, source-document verification, calculation evidence hash, line validation and professional review |
| `backend/app/Http/Controllers/Finance/FinanceControlController.php` | Payroll Finance API changes |
| `backend/config/payroll.php` | Explicit Ethiopia employee-master and statutory activation flags |
| `backend/database/migrations/2026_11_27_000033_require_stored_statutory_source_document.php` | Adds source document reference and approved-source constraint/FK |
| `backend/database/migrations/2026_11_27_000034_require_payroll_line_evidence_hash.php` | Adds validated payroll-line calculation evidence hash |
| `backend/tests/Unit/EthiopiaPayrollCalculatorTest.php` | Calculator unit regression tests |
| `backend/app/Services/Integration/DocumentIntegrationService.php` | Legal source-document upload/hash and read-back verification extensions |
| `docs/localization/ethiopia-payroll-engineering-release-gate.md` | Legal/engineering activation gate |
| `docs/localization/ethiopia-payroll-localization-2026-11-27.md` | Candidate payroll localization design and interpretation record |
| `docs/localization/ethiopia-payroll-legal-requirements-matrix.csv` | Legal-source/requirement matrix |
| `docs/localization/fixtures/ethiopia-payroll-candidate-calculations.csv` | Candidate arithmetic scenarios |
| `docs/localization/fixtures/ethiopia-payroll-tax-base-candidate-regression.csv` | Competing PAYE tax-base regression values |
| `docs/localization/templates/ethiopia-payroll-rule-payload-template.json` | Rule-payload template |
| `docs/localization/ethiopia-legal-regulatory-register.md` | Legal register updates |
| `docs/hr/payroll-and-variable-compensation.md`, `docs/hr/workforce-payroll-pension-benefits-compensation.md` | HR/payroll source alignment |
| `docs/architecture/application-folder-code-map.md`, `docs/work-packages/handover-register.md`, `README.md` | Architecture, handover and checkpoint references |

### 41.2 What is actually implemented in the candidate

The calculator exposes `calculateCandidate()` and `calculateApproved()`. Its approved path requires exactly one income-tax and one pension rule, Ethiopia jurisdiction, effective-date coverage, approved rule records and a tax-base decision with a separate reviewer/qualification, credentials, reference, article list, review timestamp and opinion hash. It checks supporting source records and employee/component evidence; excludes `RETAINER_SERVICE` from employee payroll; uses integer/minor-unit decimal handling and explicit rounding; produces a source snapshot/hash; and returns a draft result rather than posting money.

The Finance service connects a payroll draft to frozen statutory rule records, reads and hashes the stored source document, computes a payroll line through the calculator, stores canonical calculation evidence and an evidence SHA-256, and requires every line to be `VALIDATED` before professional review submission. It retains a distinct professional-review decision.

The configuration deliberately remains:
- `employee_master_release_ready = false`
- `statutory_activation_enabled = false`

These flags must remain false until the complete employee master, contract/assignment classification, competent local legal interpretation, authoritative source verification, payroll regression and end-to-end control gates are met.

### 41.3 Legal and payroll decisions that must not be inferred

The candidate records two competing PAYE bases for a gross ETB 30,000 illustration:
- TAXABLE_GROSS: PAYE ETB 8,450; employee pension ETB 2,100; net ETB 19,450.
- TAXABLE_GROSS_LESS_EMPLOYEE_PENSION: PAYE ETB 7,715; employee pension ETB 2,100; net ETB 20,185.
- Difference in PAYE: ETB 735.

This difference demonstrates why the tax-base interpretation must be established by a qualified Ethiopian tax professional against the actual applicable instrument, amendment, effective date and article. The calculator's ability to run a candidate calculation is not itself a legal determination.

### 41.4 Remaining work before WP-14 acceptance

- [ ] Retain authoritative legal source bytes, instrument metadata, title/number, publication/gazette date, article references, language, amendment chain, effective interval and authority; store a cryptographic hash of the exact bytes.
- [ ] Strengthen authority trust to an explicit reviewed authority registry and instrument identity. A URL/domain suffix or caller-supplied hash alone is insufficient.
- [ ] Require actual uploaded-object read-back and SHA-256 equality before a legal source can support an approved rule; reject missing, altered, inaccessible, superseded or wrong-tenant documents.
- [ ] Fix private document storage default/configuration and the unqualified `RuntimeException` path described in Section 4.1. Add upload/database compensation so a database failure does not leave an orphaned private object.
- [ ] Backfill or explicitly quarantine legacy statutory rows before validating the `NOT VALID` check/FK constraints introduced by migrations 000033/000034. Add a forward-only validation migration and prove clean-install plus upgrade-from-existing-database behavior.
- [ ] Re-sequence the WP-14 migrations into the canonical sequence after WP-03 migrations 000033/000034 and organization migration 000035; use 000036/000037 or the next confirmed free canonical sequence. Preserve immutable migration filenames once integrated.
- [ ] Add employee/person master, legal employer, employment contract, effective-dated assignment/position, worker classification, tax residence, pension coverage, bank/payment instructions, consent/privacy and employee lifecycle workflows.
- [ ] Add attendance/time capture, shift/roster, overtime, absence, leave, benefits, loan/advance, variable compensation, final settlement and payroll adjustment workflows.
- [ ] Add payroll run orchestration: pay period, employee population snapshot, earning/deduction component mapping, gross-to-net, statutory employer/employee obligations, control totals, payslips, approval, posting, payment file, remittance and reconciliation.
- [ ] Add API/PostgreSQL feature tests for tenant RLS, employer/entity scope, contract/assignment coverage, reviewer separation, source-byte hash, rule expiry, exemption/coverage evidence, invalid classifications, altered component metadata, duplicate rule records, concurrency, replay and payroll-to-GL balancing.
- [ ] Add payroll browser/UI workflow, employee/HR screens, maker-checker exception queues, traceable payslip and statutory reporting/exports.
- [ ] Add overflow-safe basis-point and percentage arithmetic for every maximum monetary input, and regression tests for boundary/overflow values, rounding, zero/negative/large earnings and deductions.
- [ ] Complete the independent Ethiopian legal/tax/pension opinion and approve the exact rule/source version before any activation change; retain human legal/financial authorization under the project governance agreement.

**Current CI evidence:** runs #46008205344 and #46008205340 failed before execution (`runner_id: 0`, no runner and zero steps). The 370-line calculator unit test file exists, but no Laravel/PostgreSQL feature suite for the API, tenant/RLS, payroll review, concurrency or GL/remittance flow is included in this candidate. Do not interpret the failed pre-runner workflow as a code-test result.

---

## 42. CI runner-continuity candidate — what it solves and what it does not

**Branch:** `work/wp03-ci-runner-continuity-2026-11-27`  
**Exact head:** `659e53f324d0a8c3cebd7ab5695a83e5d48f824b`  
**Comparison against delivery:** 4 commits ahead, 0 behind; 4 changed files. **Candidate PR:** #22, open draft. **Exact head:** 659e53f324d0a8c3cebd7ab5695a83e5d48f824b. **Exact-head run:** combined verification #46014760259 failed before execution; each required job had runner_id 0, blank runner name and zero steps.

Files already rendered:
- `.github/workflows/ci.yml`
- `.github/workflows/wp01-16-completion-verification.yml`
- `docs/engineering/ci-runner-continuity.md`
- `docs/engineering/development-deployment.md`

The candidate makes GitHub-hosted runners the default, offers an explicit manual `workflow_dispatch` choice for a dedicated self-hosted runner with labels `self-hosted,linux,x64,erp-ci`, keeps PR/push events on the hosted route, and narrows automatic delivery PR verification to the combined workflow to reduce duplicate backend/frontend runs. The associated operating note recommends a single-repository, least-privilege, preferably ephemeral runner with Docker and no production credentials. It correctly states that local verification or an earlier green SHA does not replace exact-head evidence.

Remaining actions:
- [ ] Confirm hosted runner allocation root cause from the account/repository settings and GitHub service state; the current tool evidence does not reveal billing/account policy as the cause.
- [ ] Set up and verify a dedicated runner only through the repository owner's secure Settings flow; do not commit runner tokens or production secrets.
- [ ] Run workflow dispatch against a frozen trusted branch/SHA; retain runner name, job steps, logs, test totals and artifact checksums.
- [ ] Keep the same test/security gates on hosted and self-hosted runners; no conditional skipping or reduced suite.
- [ ] Add dynamic RLS inventory verification. The candidate's explicit list still omits the three WP-03 platform tables `platform_operator_grants`, `platform_tenant_entitlements` and `platform_audit_events`, and it does not yet enumerate the organization tables from migration 000035.
- [ ] Change the combined workflow display name/description so it accurately describes the component checks and WP-01 package verification actually executed; do not imply all WP-00–WP-16 end-to-end scenarios pass.
- [ ] Add browser E2E, security, concurrency, performance, restore and installer checks to the workflow as the corresponding WP-16 jobs are implemented.

The workflow branch is an infrastructure-continuity candidate; it does not add ERP domain behavior or independently prove the dedicated runner exists. Run #46014760259 did not select the manual self-hosted route. No evidence confirms that a dedicated runner has been registered or is available.

---

## 43. Consolidated build checklist and canonical source-folder map

This checklist combines existing delivery source, branch-resident candidate source, and the remaining build work. Do not duplicate code into work-package folders: keep runtime code in its domain-owned Laravel/Angular locations and make each WP package folder a traceable manifest of source, requirement IDs, API, migration, UI, tests, evidence and dependencies.

### 43.1 Canonical source locations and domain responsibilities

| WP | Existing source location in delivery | Current candidate/reference source | Main remaining executable build |
|---|---|---|---|
| WP-00 Governance | `docs/agent/project-governance-agreement.md`, product charters, architecture and engineering standards | PR #18 audit and WP-03/WP-14 workflow amendments | Trace every change to owner scope, source SHA, requirements, approvals, risks and acceptance evidence. |
| WP-01 Requirements | `docs/requirements/requirements-traceability.md`, `work-packages/wp-01-requirements-traceability/` | PR #17 and the R3 archive, 128 records/24 contexts/45 source maps | Finish GAR-to-R3 semantic crosswalk; create WP-02–WP-16 package manifests and requirement-to-runtime traceability. |
| WP-02 Ethiopia legal | `docs/localization/ethiopia.md`, `docs/localization/ethiopia-legal-regulatory-register.md`, HR/payroll rule standards | WP-14 localization/legal-source candidate docs | Legal source byte registry, amendment tracking, applicability/effective-dated rule engine, professionally reviewed interpretations and tests. |
| WP-03 Enterprise/SaaS | `backend/app/Http/Controllers/Enterprise/`, tenant/platform services, migrations 000001, 000009–000014, 000027–000032; `frontend/src/app/features/enterprise/` | PR #13, org runtime branch `da50b739...`, PR #16 and combined WP-03/WP-14 plan | Secure operator lifecycle and target scope; company org/positions/incumbents; position-based DoA; SSO/MFA and access lifecycle. |
| WP-04 Merchandising | `backend/app/Http/Controllers/Merchandising/`, `backend/app/Services/Merchandising/`, migrations 000004/000005/000011; `frontend/src/app/features/merchandising/` | Existing demand/OTB/pricing and evidence-policy features | Statistical forecasting, source lineage, effective forecast, OTB reconciliation to actuals, price/promotion resolution and channel integration. |
| WP-05 Product/PLM | `backend/app/Http/Controllers/ProductDevelopment/`, `backend/app/Services/ProductDevelopment/`, migrations 000006/000007/000009/000012; `frontend/src/app/features/product-development/` | Library apparel contract reconciliation PR #14 | BOM-to-material resolution, substitution state machine, CAD/asset rights and verification, costing, product passport and dynamic release dependencies. |
| WP-06 IE/capacity | `backend/app/Http/Controllers/Planning/IndustrialEngineeringController.php`, `backend/app/Services/Planning/IndustrialEngineeringService.php`, migration 000019 | Manufacturing/IE standards and WP-03/WP-14 plan | Effective IE standard selection, capacity/site/shift integrity, skill-scope enforcement, workforce concurrency, incentive policy and IE operator UI. |
| WP-07 Sourcing/procurement | `backend/app/Http/Controllers/Sourcing/SourcingController.php`, `backend/app/Services/Sourcing/SourcingService.php`, inventory ledger, migration 000015 | Procurement/supplier reference standards | RFQ/quotation/award, supplier qualification, PO/receipt correction, service receipts, AP match, supplier claims and returns. |
| WP-08 Route/SOP | Migration 000016 route/version tables; manufacturing route/SOP/work-instruction documentation | Route framework docs | Effective-date route resolver, conditional/parallel/subcontract/rework edges, approved work instructions and acknowledgements. |
| WP-09 Materials/WIP | Inventory and sourcing services, migrations 000015/000016/000023/000024 | Manufacturing/bundle schemas and source reconciliation | Typed UOM, fabric roll/trim issue and return, cutting/lay/marker/bundle service, material ledger links and complete WIP genealogy. |
| WP-10 MES | `backend/app/Http/Controllers/Manufacturing/ManufacturingController.php`, `backend/app/Services/Manufacturing/ManufacturingExecutionService.php`, migrations 000016/000019 | Departmental manufacturing standards | Cutting/printing/embroidery/sewing/wash/finishing/packing workflows, scan/offline events, hourly plan-actual, Andon and labor-minute reconciliation. |
| WP-11 Quality/HSE | `backend/app/Http/Controllers/Quality/QualityController.php`, `backend/app/Services/Quality/QualityControlService.php`, migration 000017 | Quality/CAPA/HSE standards | Fix inspection submit/review state; evidenced deviation/containment; AQL/sampling; CAPA atomicity; effective release/hold and HSE lifecycle. |
| WP-12 WMS/commerce | `backend/app/Http/Controllers/Distribution/FinishedGoodsController.php`, `backend/app/Services/Distribution/FinishedGoodsService.php`, migrations 000018/000023/000024/000026 | WMS/channel and order-to-cash charter | Allocation, pick/pack/ship, DC/branch/store, wholesale/POS/e-commerce, delivery proof, RMA/returns/refunds and reconciliation. |
| WP-13 Finance | `backend/app/Http/Controllers/Finance/FinanceControlController.php`, `backend/app/Services/Finance/FinanceControlService.php`, migrations 000020/000025 | Finance accounting/treasury/tax/costing standard | COA/period administration, subledgers, FX, tax, AP/AR, bank reconciliation, reversals, costing/WIP, close and statements. |
| WP-14 HR/payroll | Existing payroll-review tables/service in migration 000020 and Finance service | Candidate branch `b6b3fb30...`, payroll calculator/unit suite, legal gate and source matrix | Employee/employment/time/leave/benefits; verified Ethiopia rules; payroll run/control totals; payslips, approvals, GL, remittance and reconciliation. |
| WP-15 Documents/integrations/agents | Integration controller/services, migration 000021; document metadata, outbox, device scan and agent governance tables | Agent-native blueprint PR #3; provider-neutral storage PR #10 | Verified private storage, extraction workers/import adapters, fenced outbox, connector retries, trusted devices/offline sync, agent runtime and human approval UI. |
| WP-16 Installer/release | `.github/workflows/`, `Makefile`, `docker-compose.yml`, `docker/postgres/init/`, `scripts/check-no-loopback-defaults.php`, release standards | CI runner-continuity candidate `659e53f3...`; audit Section 9 | Production application/worker/scheduler images, reproducible master installer, clean-install/upgrade, SBOM/signing, E2E/security/load/restore/rollback/UAT and operations handover. |

### 43.2 Ordered implementation actions

**Build sequence 0 — freeze the source-of-truth and traceability**
- [ ] Keep `delivery/autonomous-wp-01-16` as the integration target and record exact branch/SHA for every candidate.
- [ ] Complete the 128-record R3 to 120-record legacy GAR semantic crosswalk; map exact requirement → source path → migration/table → API → UI → test → exact SHA → result.
- [ ] Create `work-packages/wp-00-governance/` and `work-packages/wp-02-legal-localization/` through `work-packages/wp-16-release-installer/` as concise package manifests, each linking to the single canonical domain source rather than duplicating code.
- [ ] Make the handover register derive status from evidence; separate design, source-rendered, integrated, tested, accepted and release-qualified states.
- [ ] Reconcile the README checkpoint, code map, architecture index, migration index and handover register to delivery `fc01eced...`.

**Build sequence 1 — restore exact-SHA verification**
- [ ] Review the runner-continuity branch; keep hosted compute default and manual self-hosted selection restricted to trusted owner-triggered runs.
- [ ] Investigate actual Actions quota, account/repository policy and service status; do not assume billing exhaustion without evidence.
- [ ] Execute ERP CI and combined verification on the same frozen candidate SHA and again on the integration SHA after merge.
- [ ] Add dynamic database inventory tests that enumerate every tenant-owned table, assert RLS enabled and forced, verify runtime role grants and ensure new migrations cannot silently omit RLS coverage.
- [ ] Keep loopback restrictions on runtime templates/config while allowing explicit isolated CI service endpoints.
- [ ] Make workflow names and release artifacts accurately describe the executed test scope.

**Build sequence 2 — finish WP-03 secure platform and organization**
- [ ] Rebase PR #13 onto current delivery; inspect all 000033/000034 SQL functions/grants and prove no caller can forge a platform actor or widen tenant/site/resource scope.
- [ ] Complete scoped access against actual target records (entity, department, warehouse, channel, site), not only membership/scope metadata; add expiry, revocation and reliable worker processing.
- [ ] Reconcile and open a PR for org runtime branch `da50b739...`; retain migration 000035 after the operator migration set.
- [ ] Add position incumbent/acting-assignment records linked to WP-14 employee records.
- [ ] Implement versioned DoA resolver and integrate real PO/budget/approval workflow with maker-checker and auditable approval snapshots.
- [ ] Add full WP-03 API, database, tenant, RLS, Angular and browser E2E coverage.

**Build sequence 3 — complete WP-01/WP-02 inputs to statutory and domain workflows**
- [ ] Approve the requirements crosswalk and acceptance matrix.
- [ ] Build the authoritative Ethiopian legal-document evidence store and rule-version lifecycle; retain exact source bytes/hashes and qualified professional interpretation records.
- [ ] Define legal-employer, worker-classification, tax, pension, customs, VAT/withholding and reporting applicability as versioned evidence-backed rules; retain human approval for statutory activation.

**Build sequence 4 — finish WP-04/WP-05 product and commercial passport**
- [ ] Materialize approved forecast adjustments as an effective immutable forecast; reconcile forecast and OTB scopes to entity/brand/market/channel/currency/period.
- [ ] Implement forecast model/formula run provenance, price/promotion resolver, discount/margin floor, eligibility intersections and transaction-level price snapshot.
- [ ] Resolve BOM material references through WP-07 material masters; ensure approved substitutions can reach quality approval or withdrawal.
- [ ] Repair the technical-pack release state machine and make production-order creation consume a verified released tech-pack/BOM, not a caller-supplied APPROVED flag.
- [ ] Verify technical assets in WP-15 object storage and pin exact file hashes, rights and revision identity.

**Build sequence 5 — finish WP-06/WP-07/WP-08 planning and sourcing inputs**
- [ ] Bind IE standards and capacity slots to the exact effective date, work-centre, shift and site; enforce operation quantities and skill scopes at confirmation.
- [ ] Implement canonical material master and effective UOM conversion; normalize stock ledger in base units and prevent mixed-unit arithmetic.
- [ ] Complete supplier qualification, RFQ/quote/award, PO approval, goods/service receipt, quality hold/release and three-way AP matching.
- [ ] Implement route version/effectivity/supersession resolver and conditional route evaluation; version and acknowledge SOP/work instructions.
- [ ] Add supplier returns, import/customs/duty, landed cost, certificate and claims workflows.

**Build sequence 6 — connect WP-09/WP-10 production material and shop-floor execution**
- [ ] Create exact production material requirement snapshots from released BOM/version, order sizes/colours/quantity, approved substitute and yield/waste assumptions.
- [ ] Add warehouse/roll/trim reservation, issue, return, transfer, consumption, scrap and WIP custody commands linked transactionally to inventory movement and production operations/bundles.
- [ ] Build marker/lay/spread/cut/bundle services, roll-to-bundle genealogy, separate fabric/trim issue and operation-level material reconciliation.
- [ ] Build department-specific MES and worker/machine/device flows for cutting, printing, embroidery, sewing, optional wash/special process, finishing, pressing and packing.
- [ ] Support offline event capture with idempotent synchronization, event ordering, device identity and collision handling.
- [ ] Reconcile planned SAM/SMV minutes, available labor/machine minutes, earned minutes, accepted output, hourly production, WIP, defects, rework and downtime.

**Build sequence 7 — harden WP-11/WP-12 quality and order-to-cash**
- [ ] Repair the submitted-pending-review quality state; independently evidence deviations and containment; bind NC disposition to the exact inspection/lot/operation.
- [ ] Implement versioned AQL/sampling plan, measurement tolerances, first-piece/in-line/final/shipment inspection and effective-current release authority.
- [ ] Make CAPA creation and verification atomic/evidence-linked; prevent historical releases from overriding newer HOLD/REJECT decisions.
- [ ] Implement location- and unit-aware finished-goods allocation, reservation, pick, pack, dispatch, proof-of-delivery and shipment events.
- [ ] Implement channel order pricing, credit exposure from WP-13, wholesale/retail/POS/e-commerce/marketplace connectors, returns/refunds/RMA and exactly-once finance/stock reconciliation.

**Build sequence 8 — complete WP-13 finance and WP-14 people/payroll**
- [ ] Implement finance period and COA administration, source-led subledgers, account/legal-entity constraints, approved FX conversion, payment and bank reconciliation.
- [ ] Implement balanced immutable journals, posting controls, reversals/adjustments, AP/AR, fixed assets, cost accounting, WIP valuation, tax, close and financial statements.
- [ ] Reconcile the WP-14 candidate with WP-03 migration sequence; fix private object storage and validate source document hashes.
- [ ] Complete employee master, employment contracts, position assignment, time, leave, overtime, benefits, loans, training, performance, grievances and separation.
- [ ] Add payroll API/PostgreSQL/RLS/approval/concurrency tests and full payroll-to-GL, payment, remittance and reconciliation; preserve disabled activation flags until the independent legal and release gates pass.

**Build sequence 9 — operationalize WP-15 agents, documents and integrations**
- [ ] Make document object upload/read-back/hash verification provider-neutral and transactionally compensated; add malware scanning, retention, access logging and extraction/import review.
- [ ] Add durable queues, scheduled workers, retry/backoff, dead-letter and reconciliation interfaces; give outbox claims leases, worker identity and fencing tokens.
- [ ] Make idempotency payload-aware for outbox, document imports, device scans, inventory commands and agent actions.
- [ ] Implement connector registry/adapters, OAuth/secret rotation, signed webhooks, inbox/outbox, health/status, replay and tenant-scoped reconciliation.
- [ ] Implement scanner/device provisioning, identity, key rotation, trusted time, offline queue, replay defense and operator UI.
- [ ] Turn agent design into governed runtime capabilities, tool permission boundaries, scoped context, proposal evaluation, human approval, action execution, trace/audit and rollback; agents must not bypass Laravel transaction rules.

**Build sequence 10 — deliver WP-16 canonical master installer and end-to-end qualification**
- [ ] Produce pinned backend, frontend/web, worker and scheduler container images with non-root users, health/readiness checks, minimal base images and immutable image digests.
- [ ] Build a reproducible master installer with validated environment template, secrets bootstrap, PostgreSQL migration/role/RLS setup, Redis/queue, object storage, web/API, worker, scheduler, reverse proxy/TLS and initial platform bootstrap.
- [ ] Add deterministic clean-install, upgrade, seeded reference data, idempotent re-run, configuration validation, backup/restore, disaster recovery and rollback tests.
- [ ] Generate SBOMs, dependency/source provenance, image signing and release attestations; scan PHP/npm/Python/OS/container artifacts and prevent secret leakage.
- [ ] Execute WP-16 AT-REL-001..012 and E2E-001..008 across tenant provisioning → product/BOM release → sourcing/material receipt → inventory issue/cutting → production/QC/CAPA → finished goods → order/dispatch/returns → finance/payroll/reporting.
- [ ] Execute API contract compatibility, accessibility/WCAG 2.2 AA, tenant penetration/RLS bypass, SAST/DAST/dependency/container checks, load/soak/concurrency, outage/retry, backup/restore, deployment/rollback and operator runbook/UAT.
- [ ] Produce an exact-SHA signed release manifest, artifact checksums, CI links, acceptance evidence, open risk register, data migration plan and operational handover. Production activation/deployment remains under the existing human approval boundaries.

### 43.3 Non-negotiable technical acceptance standards already defined in the repository

The project standards require:
- Angular 22, strict TypeScript/templates, feature-owned Angular code, typed API services and server-side authorization; WCAG 2.2 AA, keyboard access, focus visibility, screen-reader support and automated accessibility checks.
- PHP 8.5/Laravel 13, strict typed boundaries, PSR-12, Composer lockfile/audit, controller/service separation, transactions, queue/outbox for external side effects and no floating-point money.
- PostgreSQL 18 as system of record, migration-only schema evolution, tenant-scoped foreign keys/checks/indexes, forced RLS on tenant tables and tests using the restricted runtime role.
- OpenAPI 3.1 versioned REST contracts, stable error envelopes, pagination/correlation IDs and idempotent commands.
- Redis 8.2 for cache/queue coordination; S3-compatible provider-neutral document storage; isolated Python 3.15 specialist compute that cannot write around Laravel domain rules.
- Formatting/lint, static/type analysis, unit, integration, tenant/authorization, E2E, migration/recovery review, dependency/secret/container/SAST checks, production build/container build, deployment smoke and health tests.

These standards are written in `docs/engineering/code-quality.md`, `docs/architecture/platform-standard.md`, `docs/security/saas-security.md`, `docs/engineering/development-deployment.md` and the WP-16 release standard. The gap is the missing execution evidence and end-to-end source coverage across all required journeys, plus the installer/runtime topology and complete test families described above.

---



---

## 44. WP-03 organization runtime candidate — additional least-privilege hardening

The WP-03 organization candidate was reviewed directly at its service, controller, migration, route and feature-test layer.

### 44.1 Verified implementation characteristics

The current candidate implements tenant-scoped organization-unit and job-position creation/read, effective-dated manager assignments, solid-line and functional position reporting, legal-entity/site validation, unit/position period containment, manager-overlap checks and solid-line cycle detection. The reporting graph operations use a tenant-level PostgreSQL advisory transaction lock. Creation writes an audit event and a transactional outbox event in the same transaction.

The organization schema enables and forces RLS on all four tables. The operator controller prohibits person_id, employee_id, user_id and membership_id when creating a position, correctly separating a job slot from an employee/login identity.

### 44.2 Runtime UPDATE privilege expansion found and corrected in candidate

**Original finding:** migration 000035 granted SELECT, INSERT, UPDATE on org_units, job_positions, org_unit_manager_assignments and position_reporting_relations to app_runtime. The candidate service/controller exposes read and create operations only; it has no controlled update operation that would need this privilege. With table-level UPDATE, another runtime code path could rewrite position titles, effective dates, hierarchy or reporting relationships without using the service's audit/outbox event method.

**Candidate fix committed:**
- 7503bf13c0f80d4df29c5a32609119d1ac0f78f1 changes migration 000035 to grant only SELECT, INSERT.
- f9aebe855a05dec30f0b1f4bc5f1acbd741f385b changes the PostgreSQL feature test to assert runtime UPDATE and DELETE are absent, while preserving SELECT, INSERT and forced RLS assertions.

The intent is append-only organization master history for the current create-only API. Future effective-date changes, reorganizations or deactivation must use an explicit versioned command with audit/outbox evidence, not a broad direct UPDATE grant.

### 44.3 Remaining WP-03 acceptance work

- [ ] Keep the position slot separate from its incumbent; implement effective-dated employee/person-to-position and acting assignments only when the WP-14 employee master is authoritative.
- [ ] Complete position-incumbent changes, vacancy, leave/acting cover, expiry, archival and reorganization snapshots.
- [ ] Implement a versioned DoA resolver and connect transaction-specific approval authority to positions, incumbents, acting delegates, legal entity/site/department/cost centre, amount/currency, limits and incompatible duties.
- [ ] Preserve tenant OWNER/ADMIN as platform/tenant administration roles, not automatic business-transaction approvers.
- [ ] Add database-level validation or controlled write functions for critical manager-overlap and solid-line-cycle invariants if additional application writers are introduced; the current overlap/cycle enforcement is performed by the service under its advisory graph lock.
- [ ] Add concurrent API tests for two overlapping manager assignments and two simultaneously-created reporting edges that could form a cycle.
- [ ] Run the current-head feature suite against PostgreSQL under app_runtime; the code change and test assertion have been committed but not yet verified by an executed CI run.

---

## 45. WP-14 Ethiopia payroll candidate — additional source fixes and remaining safeguards

### 45.1 Private document storage now has an explicit clean-install configuration

The WP-14 branch originally used config('filesystems.private_documents_disk', 's3'), but the delivery filesystems config defined only local and public disks and no private-disk key or S3 disk. This prevented a reproducible clean-install document upload/source verification path. storeUploadedFile() also referenced RuntimeException without importing it and could leave a stored object orphaned if document metadata registration failed.

**Candidate fixes committed:**
- 0960ce05bfc6ce19279e18f7e89133ca965e9b2e adds filesystems.private_documents_disk, with the private local disk as the clean-development default and a production preflight requirement to configure an approved private object-storage provider.
- d8aa26c2ad725afea21775b5b42217f47beb629a imports RuntimeException/Throwable, compensates for metadata failure by deleting the uploaded private object where possible, and checks stored byte count as well as SHA-256 during source verification.
- 4a75ec97a88ae56afe94adeb00eb5a98ce20b909 documents PRIVATE_DOCUMENTS_DISK=local in the backend environment example.

The local disk points to the private application storage root and has no public URL. Production acceptance must still verify the configured object-storage driver, private permissions, retention, object versioning, backup and read-back integrity from the actual installer environment.

### 45.2 Payroll calculation line mutation is now guarded at database and service level

**Candidate fixes committed:**
- f65e298a5db6000fc7678990c4075c532c63f9be adds migration 000036: validated payroll line inserts are allowed only while the parent run is DRAFT; a parent-row FOR UPDATE lock serializes insertion against run submission; UPDATE/DELETE are rejected by a trigger; runtime UPDATE/DELETE privileges are revoked.
- 2b8a0278362399280e2c5439dc66c55b9ba8043f adds PayrollRunLineIntegrityTest for runtime-role SELECT/INSERT-only privileges and the installed triggers.
- 77177899e59a1606134b8d70e21104efdb7f03d1 strengthens submission and professional review: recompute each line's canonical evidence SHA-256; verify the calculation status, employee payee type, ETB currency, preparer and period snapshot; compare gross, deductions and net amounts with the embedded server calculation; verify the frozen statutory source snapshot digest; calculate aggregate worker/gross/deduction/net control totals and a line-evidence-set hash at submission; then recompute and compare those totals again before professional decision.

This improves evidence immutability and review integrity. These changes remain on PR #20 and have not yet been executed in the repository's PostgreSQL CI environment.

### 45.3 Release boundaries and remaining WP-14 work

The configuration remains deliberately disabled:
- employee_master_release_ready = false
- statutory_activation_enabled = false

The candidate's calculator requires professionally approved, effective-dated rule records and an explicit reviewed PAYE tax-base opinion. It uses integer/minor-unit amounts and explicit rounding, excludes retainer services from employee payroll, and returns a calculated draft—not a payment, statutory remittance or GL posting.

Remaining work:
- [ ] Replace free-text worker_reference with a tenant/legal-employer-scoped employee/person/contract/assignment identity before any activation. Current candidate configuration must stay disabled until that authoritative HR master exists.
- [ ] Replace manual boolean readiness flags with an owner-approved release gate backed by actual employee-master, legal-source, professional-review, test and end-to-end evidence; do not enable by configuration alone.
- [ ] Add full PostgreSQL API/feature tests for source documents, rule snapshots, private-object tamper, per-line hash, frozen totals, reviewer segregation and runtime-role privileges. Current tests are calculator unit tests plus the new payroll line permission/trigger feature test; they do not prove a complete payroll service lifecycle.
- [ ] Add an executed test proving the insert trigger rejects a line after run submission and the immutable trigger rejects UPDATE/DELETE even when invoked as a privileged test role; the current test checks privileges and trigger installation, not the full trigger behavior.
- [ ] Re-verify all statutory source-document bytes at the review boundary, not only when the payroll rule or line is first created.
- [ ] Correct durable tamper recording: verifyStoredDocumentChecksum() currently attempts to mark a mismatched asset REJECTED and then throws. Its callers execute inside an outer DB transaction, so that status update is rolled back with the failed payroll operation. Preserve the rejection/tamper event after the failed transaction through a durable, separately committed security/audit path.
- [ ] Ensure a production private object-storage driver is installed/configured and validated by WP-16 preflight; the local disk default is for clean development only.
- [ ] Bind the professional payroll decision to the authorized qualified reviewer profile/registration and retained evidence, in addition to the distinct-from-preparer Gate and approval reference.
- [ ] Complete payroll-to-journal/GL, payslip, payment-file, bank reconciliation, statutory remittance and reporting only after WP-13/WP-14 interfaces and human approvals are complete.
- [ ] Run the exact current WP-14 candidate SHA against PostgreSQL and record unit, feature, API and migration-upgrade results. No CI pass is claimed here.



---

## 46. Exact-head GitHub workflow evidence — runner allocation and execution status

The exact workflow runs below were queried by commit SHA and their raw job records inspected. All listed failed jobs report runner_id = 0, an empty runner_name and an empty steps array. This proves no runner was assigned and no application test step executed. The combined source/archive assembly job was skipped after the required jobs failed.

| Source | Exact SHA | Workflow run | Job evidence | Classification |
|---|---|---:|---|---|
| delivery/autonomous-wp-01-16 | fc01eced62b97c4ec27f0075264fc8ba0fb11851 | #45915273928 (ERP CI) | Backend/frontend jobs runner_id 0, no steps; rerun also failed before execution | NOT RUN |
| PR #13 platform/scoped-access candidate | 8ae8536bccfd3924017b12905ea57fce2641fb46 | #45915554309 (ERP CI), #45915554192 (combined) | Required jobs runner_id 0, no steps; repeated attempts did not execute | NOT RUN |
| PR #18 production plan/audit | b9be60c01d77db3275e604c53c31826192d2ff54 | #46014870000 (ERP CI), #46014869903 (combined) | Required jobs runner_id 0, no steps; artifact assembly skipped | NOT RUN |
| PR #20 payroll candidate | db1847c64abbff8b04bc70790b122e082f5d81dd | #46015026266 (ERP CI), #46015026376 (combined) | Required jobs runner_id 0, no steps; artifact assembly skipped | NOT RUN |
| PR #21 organization candidate | f9aebe855a05dec30f0b1f4bc5f1acbd741f385b | #46013596367 (ERP CI), #46013595997 (combined) | Required jobs runner_id 0, no steps; artifact assembly skipped | NOT RUN |
| PR #22 runner-continuity candidate | 659e53f324d0a8c3cebd7ab5695a83e5d48f824b | #46014760259 (combined) | WP-01, Angular and Laravel/PostgreSQL jobs runner_id 0, no steps; artifact assembly skipped | NOT RUN |

### 46.1 What is proven and what remains unknown

- Workflow conclusion: GitHub reports FAILURE for each listed run.
- Runner allocation: PROVEN at the job-record level; runner_id is 0 and no runner name is present.
- Executed application test result: NOT ESTABLISHED; no test steps ran.
- Code correctness: NOT ESTABLISHED by these failed runs; do not describe them as Laravel/Angular test failures.
- Underlying reason no runner was assigned: NOT YET ESTABLISHED. Repository tooling does not expose the owner billing/Actions policy diagnostics or GitHub-hosted runner service diagnostics needed to distinguish account quota, repository policy or service-side provisioning.
- PR #22 is an open draft preparatory change. Its manual self-hosted runner option was not used by run #46014760259; that run had no assigned runner. No evidence yet confirms a dedicated self-hosted runner exists.

### 46.2 Required continuity tasks — quality gates remain unchanged

- [ ] Repository owner/admin checks Actions enabled status, account spending/usage limit, billing/quota, repository/organization policy and GitHub service diagnostics for the failing time window; retain the actual cause/evidence rather than guessing.
- [ ] Provision an isolated, repository-scoped Linux x64 ERP runner through GitHub Settings, with the erp-ci label, least-privilege access, a clean/ephemeral workspace, Docker/test dependencies, no production network or secrets and monitored availability.
- [ ] Review and merge PR #22 only after workflow syntax/security checks pass and the administrator confirms runner configuration; keep GitHub-hosted as default and never send untrusted PR code to self-hosted infrastructure.
- [ ] Dispatch both required workflows on a frozen trusted branch/SHA using the dedicated runner; record runner identity, step logs, exit codes, test counts and artifact checksums.
- [ ] Re-run ERP CI and combined verification for each frozen candidate head; then repeat on the canonical delivery SHA after integration.
- [ ] Keep every backend, PostgreSQL/RLS, frontend, WP-01 integrity, artifact, security and E2E check mandatory; local results or prior green SHAs do not replace exact-head CI.

These records are an infrastructure execution failure, not acceptance. Work that can be safely implemented and reviewed continues, but no affected candidate is promoted as verified until its exact-head checks execute and pass.

---

## 47. WP-03 organization workbench — pagination defect and candidate remediation

### 47.1 Original defect on the inspected canonical delivery baseline

**Finding:** the canonical delivery implementation exposed paginated responses (100 rows per page) for organizational units, job positions, unit-manager assignments and position-reporting relationships. The Angular API service did not accept or pass page/search/filter parameters; the component loaded only the first page and retained response.data without paginator metadata. The screen consequently had no register-level pagination/search and could not show the exact tenant total.

**Effect:** tenants with more than 100 records could not inspect all units, positions, manager history or reporting relationships from the operator workbench. Parent/manager/reporting selectors could silently omit valid records on later pages. A loaded-row count could be mistaken for the full tenant count.

### 47.2 Candidate remediation implemented on the isolated WP-03 branch

**Candidate:** PR #21 — https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/21

**Initial tested candidate head:** 346950a9b5aaef4504161b1d8ba6a9bebedd4178. **Latest inspected candidate head:** 76871d46bc2728e49bfb33310a4ee8a7a5e4f1ab.

**Backend source changed**
- backend/app/Services/Enterprise/EnterpriseOrganizationService.php: paginated search/filter implementations for units, positions, managerAssignments and reportingRelations; stable ordering; default page size 25, hard maximum 100; effective-date and tenant filters.
- backend/app/Http/Controllers/Enterprise/EnterpriseOrganizationController.php: validated page/per_page/search/filter parameters; tenant-scoped UUID validation for position/unit filters; unit ownership check before manager-history query.
- backend/database/migrations/2026_11_27_000035_create_organization_structure_masters.php: remains create-only for this increment. app_runtime has SELECT/INSERT only; UPDATE/DELETE are not granted until an explicitly controlled amendment lifecycle exists.

**Frontend source changed**
- frontend/src/app/features/enterprise/enterprise-organization.service.ts: typed OrganizationListQuery/ApiPage contracts and HttpParams-based requests for all four registers.
- frontend/src/app/features/enterprise/organization-structure.component.ts/html/css: accessible search, first/previous/next/last navigation, server total labels, request-sequence and tenant checks, selected-unit preservation, visited-record caches, active manager/reporting position choices and refreshed page state after creation.
- Parent-unit choices use discovered ACTIVE tenant units; manager-position choices require an ACTIVE position belonging to the selected unit; reporting position choices use tenant-scoped ACTIVE-position search.
- Empty search results are distinguished from an empty tenant register. Child-unit counts are labelled as counts on the currently loaded page, not tenant totals.

**Test/documentation source changed**
- backend/tests/Feature/EnterpriseOrganizationStructureTest.php: unit and position page/search, effective-dated manager-history page/search, reporting relationship page/search, maximum page-size validation and cross-tenant search isolation.
- frontend/src/app/features/enterprise/organization-structure.component.spec.ts: HTTP query contract, later-page navigation, server totals, position/manager/reporting search and active reporting-position lookup.
- docs/architecture/application-folder-code-map.md, docs/verification/WP03-implementation-acceptance-matrix.md and docs/work-packages/handover-register.md now map these paths and acceptance requirements.
- The controlling task is PROD-WP03-008 in docs/work-packages/production-execution-plan-wp00-wp16.md.

### 47.3 Exact-head verification evidence — tests remain NOT RUN

For candidate head 346950a9b5aaef4504161b1d8ba6a9bebedd4178:

- ERP CI run #46017735320: Laravel/PostgreSQL and Angular jobs both have runner_id 0, blank runner name and zero executed steps. The dependency-lockfile commit job was skipped.
- Combined WP verification run #46017735305: Laravel/PostgreSQL, Angular and WP-01 jobs all have runner_id 0, blank runner name and zero executed steps; final candidate artifact assembly was skipped.
- These are workflow failures before execution. No PHPUnit/PostgreSQL, Angular build/Vitest or browser E2E step ran on this candidate head. Application test result is NOT RUN; no code-test failure or pass is inferred.
- The same runner-allocation pattern is present on several intermediate PR #21 heads and the delivery/PR #13/PR #18/PR #20/PR #22 candidates recorded in Section 46. The exact underlying account/organization/repository/GitHub-service cause remains unknown pending owner/admin diagnostics.
- PR #22 is a preparatory manual dedicated-runner route; no evidence shows a dedicated self-hosted runner is registered or has executed a workflow.

### 47.3.1 Latest candidate head confirmation

For latest candidate head 76871d46bc2728e49bfb33310a4ee8a7a5e4f1ab:

- ERP CI run #46018149774: the Laravel/PostgreSQL and Angular jobs each have runner_id 0, blank runner name and zero steps; dependency-lockfile commit was skipped.
- Combined WP verification run #46018149715: Laravel/PostgreSQL, Angular and WP-01 jobs each have runner_id 0, blank runner name and zero steps; artifact assembly was skipped.
- This exact newer head still has no executed application test steps. Test result remains NOT RUN.
- The latest source change adds the rule that parent-unit selectors expose only active tenant units. Existing active-only manager/reporting position selection remains in effect.

### 47.4 Required final acceptance before integration

- [ ] Execute backend PHPUnit/Pint/syntax and migration/RLS tests on the exact final PR #21 SHA with the restricted app_runtime role.
- [ ] Execute Angular 22 build and Vitest on the same exact SHA.
- [ ] Execute browser E2E in an isolated tenant with more than 100 units, more than 100 positions in a unit, more than 100 manager-history records and more than 100 reporting relationships.
- [ ] Prove page 1/middle/last page, exact totals, search/filter, valid-on dates, parent/manager/reporting pickers for records on later pages, newly created record refresh, selected record retention, empty search state, tenant switch and stale-response race.
- [ ] Verify inactive manager/reporting positions are not offered for new relationships; server validation remains authoritative.
- [ ] Confirm no tenant data leakage and no update/delete privilege widening; update remains unavailable until an independently designed amendment lifecycle.
- [ ] Retain the existing person/employee/incumbent and DoA boundaries. Position title or manager-position mapping does not appoint a person or confer financial/statutory approval authority.
- [ ] Update the exact-SHA handover and matrix only after required tests pass, independent review is complete and the change is integrated to canonical delivery.

**Current classification:** implementation candidate, PARTIAL / NOT VERIFIED; exact-head tests NOT RUN. This closes the source/UI pagination gap only at candidate-source level. It does not complete WP-03, establish production verification or authorize progression to WP-04.

## 48. WP-15 runtime grant correction — implementation candidate PR #23

### 48.1 Source defect fixed in a separate candidate branch

The audit at Section 38.1 established that three service paths issue database UPDATE statements against tables whose canonical grants were limited to SELECT/INSERT. A dedicated fix candidate is now committed separately from this documentation PR.

**Candidate:** PR #23 — fix(WP-15): grant least-privilege agent and device transition updates

**Base:** delivery SHA fc01eced62b97c4ec27f0075264fc8ba0fb11851

**Candidate head:** 8497cfc76200e1263f0f5ce47afdb668773758bc

**Files**
- backend/database/migrations/2026_11_27_000033_grant_agent_and_device_runtime_transition_columns.php
- backend/tests/Feature/DomainRlsSecurityTest.php

### 48.2 Database change boundary

The forward migration grants column-specific UPDATE only:

| Table | UPDATE columns required by current services |
|---|---|
| agent_run_audits | status, output_sha256, token_usage, tool_trace, completed_at |
| agent_action_proposals | status |
| device_scan_events | resolution_status, resolution_reference_type, resolution_reference_id, raw_metadata |

It deliberately does not grant table-wide UPDATE or DELETE. agent_approval_decisions and agent_action_events remain append-only to the application role. The migration's down() revokes precisely the column grants it adds.

The added feature-test method checks that each required transition column is updateable and that unrelated source/identity/payload fields, approval/action events and delete privileges remain unavailable.

### 48.3 Verification evidence and remaining gate

- [x] Candidate branch created from the exact audited delivery SHA.
- [x] Forward migration committed.
- [x] PostgreSQL privilege regression assertions committed.
- [x] Changed-file diff inspected: two files only; no unrelated application behavior was modified.
- [x] Local PHP syntax lint passed for the migration and added test-method syntax.
- [ ] PostgreSQL migration-upgrade execution and privilege assertions under the actual app_runtime role.
- [ ] Exact-head ERP CI, backend tests and combined verification.
- [ ] Review migration ordering against any parallel candidate before integration.
- [ ] Merge only through the canonical delivery PR/change-control path after the exact-head tests pass.

**Evidence classification:** source implementation candidate with local syntax verification. No PostgreSQL or CI pass is claimed. PR #23 is open as a draft; the workflow query returned no run for its head at the time of this audit update.

**Scope deliberately not included:** this patch does not resolve agent run starter/finisher attribution, A0/A1 proposal completion, REQUEST_CHANGES revision history, outbox delivery workers/leases or device authentication. Those remain separate source tasks under Sections 32 and 38.


---

## 49. WP-02 legal entity, site, registration and licence activation — cross-package enforcement audit

### 49.1 The enterprise identity schema is present, but legal/entity activation is not an executable lifecycle

**Verified source foundation**
- backend/database/migrations/2026_11_22_000001_create_enterprise_foundation_tables.php creates legal_entities, business_sites, operational_nodes, branch_registrations and inventory_locations, with tenant-composite keys, forced RLS and explicit application grants.
- backend/database/migrations/2026_11_22_000003_create_licensing_evidence_tables.php creates entity_licences with tenant/entity/site binding, effective dates, review-required status, forced RLS and SELECT/INSERT-only runtime grants.
- backend/app/Http/Controllers/Enterprise/LegalEntityController.php exposes index() and store(). store() creates a DRAFT entity and records an audit event in the same transaction.
- backend/app/Http/Controllers/Enterprise/BusinessSiteController.php exposes index(), store(), nodes() and storeNode(). Site, operational-node and inventory-location creation are draft operations.
- backend/app/Http/Controllers/Enterprise/LicensingEvidenceController.php exposes branch-registration and licence evidence listing/creation. Branch registration and entity licence records are created with REVIEW_REQUIRED.
- backend/routes/api.php registers legal-entity/site/node/location and branch/licence GET/POST endpoints. The registered route list contains no approval, rejection, activation, suspension, expiry, renewal or reactivation command for these records.
- The current Angular enterprise feature tree contains legal-entity and business-site components/services, but no licensing/branch-registration review workbench.

This is a useful greenfield tenant/entity/site foundation. It is not yet a complete legally controlled organization activation workflow.

### 49.2 Downstream commands accept a tenant-owned entity without proving activation or licence scope

**Finding A — sourcing:** SourcingService::createPurchaseOrder() verifies the selected legal entity exists in the current tenant. It does not require an active entity, an approved/effective registration, an applicable purchasing activity or an approved operating/delivery site.

**Finding B — manufacturing:** ManufacturingExecutionService::createProductionOrder() inserts the supplied legal_entity_id and site_id into production order fields using independent tenant-scoped references. It does not resolve an active entity/site authorization or an effective manufacturing licence/activity record. The separate site/entity mismatch is already recorded in Section 17.2; this section records the additional activation/legal-scope bypass.

**Finding C — commerce:** FinishedGoodsService::createCustomerOrder() stores a caller-supplied tenant entity ID and the channel code. It does not resolve the selling legal entity's approved/effective activity, channel authorization, site/fulfilment scope or tax registration before creating the order draft.

**Finding D — finance/payroll:** FinanceControlService::createPayrollDraft() checks only that the legal entity exists. Journal creation verifies an OPEN period for the selected entity, but the source path does not prove the entity's legal registration/licence state before accepting financial or payroll preparation.

**Effect:** a DRAFT or legally unverified entity may be used as a reference by downstream sourcing, production, commerce or payroll commands. RLS and composite tenant foreign keys prevent cross-tenant identity substitution, but they do not prove that the entity is legally enabled for a particular activity, site, channel, employment or transaction.

**Required implementation**
- [ ] Define one authoritative LegalEntityActivationService/policy contract with explicit entity lifecycle: DRAFT → EVIDENCE_PENDING → UNDER_REVIEW → ACTIVE, plus REJECTED, SUSPENDED, EXPIRED and controlled reactivation states.
- [ ] Create scoped site lifecycle and approval: DRAFT → EVIDENCE_PENDING → UNDER_REVIEW → ACTIVE, with suspension/closure and historical effective dates.
- [ ] Add independent registration/licence evidence review/decision commands and persist reviewer authority, source artifact, exact source hash, decision reason, effective period, activity code, legal entity, optional site and jurisdiction.
- [ ] Link branch-registration and licence evidence to immutable WP-15 document assets; a free-text reference or unverified document code is not proof of the retained source.
- [ ] Define an effective-dated activity authorization resolver keyed by legal entity, site, activity, jurisdiction, channel, transaction/process type and transaction date.
- [ ] Require the resolver in the relevant downstream commands: purchase-order creation/approval, supplier delivery/receipt, production-order release/start, customer-order confirmation/dispatch, finance-period activation and payroll preparation.
- [ ] Distinguish a draft/preparatory action from an operational commitment. Permit appropriate draft planning where policy allows, but prevent approval, release, stock movement, sale, production or regulated transaction while the required authorization is missing, expired, suspended or out of scope.
- [ ] Preserve an explicit professional-review/exception path for uncertain or special cases; do not hard-code legal activity interpretations or assume one federal record covers all regional/municipal requirements.
- [ ] Ensure the policy is cached only with a bounded freshness/version and revalidated at high-impact state transitions; a licence suspended after draft creation must block subsequent release.
- [ ] Add an immutable decision snapshot to orders/production records showing the entity, site, activity, source registration/licence IDs and versions, applicability date, reviewer/authority and outcome.

### 49.3 Entity/site relationships need stronger operational invariants

The schema already gives operational_nodes a composite foreign key to (tenant_id, business_site_id, legal_entity_id) on business_sites, and branch_registrations and entity_licences have composite site/entity references. These are good foundations and should be retained.

Remaining inconsistencies:
- business_sites.legal_entity_id is nullable, and site creation does not require an entity. That may be useful for an initial location draft, but no node or operating capability should be enabled until ownership is resolved.
- production_orders currently has separate tenant/entity and tenant/site foreign keys rather than a composite constraint tying the selected site to the same legal entity.
- Procurement receipt destinations, finished-goods quarantine/release locations and customer fulfilment locations are checked for tenant and coarse location type/capability, but are not consistently resolved back to the authorized production/PO/customer legal entity and operating site.
- Entity licence and branch-register activity records are free-standing evidence rows. No activation service binds their exact activity/date/site applicability to the operating node capability or downstream transaction.

**Required implementation**
- [ ] Require entity ownership before a business site can activate; retain unassigned site drafts only as non-operational onboarding records.
- [ ] Enforce a database relationship for production order (tenant_id, site_id, legal_entity_id) to the same business-site/entity pair, with a separately authorized and explicitly modelled shared-site/inter-entity arrangement if required.
- [ ] Bind operational node capability to the approved entity/site profile; stock_holding_enabled, PRODUCTION, RETAIL, WHOLESALE or SUBCONTRACTING flags must not independently confer legal permission.
- [ ] Resolve inventory location → operational node → business site → legal entity for material receipt/issue, production, finished goods, dispatch, returns and transfer.
- [ ] Require an explicitly approved inter-site or inter-entity transfer when the source and destination legal scopes differ; retain custody, tax and accounting consequences.
- [ ] Add adversarial same-tenant cross-entity and cross-site tests in addition to existing cross-tenant RLS tests.

### 49.4 Legal source evidence is not yet connected to entity/licence records

docs/localization/ethiopia-legal-regulatory-register.md establishes a sound Ethiopia source hierarchy: Gazette/MoJ publication, competent issuing authority, official implementing directive/notice, regional/city requirements as applicable, then qualified local professional review. It also states that a rule must not become executable until its current text, applicability, effective date and professional approval have been captured.

The current enterprise licensing controller does not yet enforce that evidence discipline. It accepts an evidence_reference string (nullable) and persists licence identity/issuer/activity/effective dates. Branch-registration evidence similarly stores a commercial register reference and activity code array. Neither operation binds the retained source bytes, source authority record, document ID/hash or professional reviewer approval.

**Required implementation**
- [ ] Create a canonical legal-source asset relationship for every entity registration, branch registration and licence decision.
- [ ] Require source issuer/authority, instrument/record type and reference, publication/issue date, effective interval, jurisdiction, exact applicable activity and retained source artifact.
- [ ] Recompute SHA-256 from stored source bytes and verify the object/version before professional review and activation.
- [ ] Record the qualified reviewer identity/role/registration, decision date, opinion/approval reference, applicability scope and conditions.
- [ ] Track source change, expiry, revocation, renewal and superseding records. A new document must not silently rewrite a historical approval or already-authorized transaction snapshot.
- [ ] Keep statutory tax, payroll, customs, FX, labour and environmental rules in their owning WP-02/WP-13/WP-14/operational domain; the entity/site registry stores applicability/evidence and delegates exact calculations to the relevant approved rule engine.

### 49.5 Reference-data validation should be centralized, not inferred from string shape

Legal entity creation currently validates country_code as two uppercase letters and base_currency as three uppercase letters. Business-site creation validates timezone shape against PHP's timezone list. Those are useful syntax checks, but a two-/three-letter string is not proof of a valid, supported, effective country/currency or tax jurisdiction.

**Required implementation**
- [ ] Add controlled country, currency, language, timezone, region and unit reference catalogues with source, version and active dates.
- [ ] Validate legal country and base currency against the approved reference catalogue; do not assume every tenant is Ethiopian or every legal entity uses ETB.
- [ ] Resolve regional/city authority applicability from the entity/site address and registration evidence; never infer a regional licence from a tenant default.
- [ ] Preserve the entity's base currency and historical currency policy; changes must be separately approved and effective-dated.

### 49.6 WP-02 frontend, API and automated acceptance work

The current UI allows the tenant administrator to create draft legal entities and sites, nodes and stock locations. It does not provide an operator workflow for legal evidence review, independent entity/site activation, licence renewal/suspension, activity-scope decisions or the affected downstream holds. There are no dedicated backend feature tests for the legal-entity/site/licensing lifecycle in the inspected test tree.

**Required implementation**
- [ ] Build a legal-entity and site evidence workbench with clear distinction between draft, evidence pending, professional review, active, suspended, expired and rejected.
- [ ] Build reviewer queues for branch registrations, entity licences, site activity and source renewal; show exact entity/site/activity/jurisdiction scope and source document integrity.
- [ ] Display downstream blocked/preparatory states without allowing a hidden UI bypass; all authorization must be repeated server-side.
- [ ] Add list/detail/search, effective-date history, source document viewer and immutable decision/audit history.
- [ ] Add PostgreSQL feature tests for every state transition, same-tenant unauthorized entity/site, wrong activity, wrong site, missing/expired/suspended evidence, source hash tampering and role segregation.
- [ ] Add API contract and E2E journeys: entity draft → registration evidence → legal review/activation → site → branch/licence → operating node/capability → inventory location → PO/production/sales command authorization.
- [ ] Run these tests as the non-owner app_runtime role and record the exact Git SHA; no WP-02 feature-test pass is claimed by source inspection.

---

## 50. WP-02 implementation sequence and cross-package release dependency

1. Implement the versioned legal entity/site/licence evidence lifecycle and reviewer authority.
2. Bind all legal/registration/licence source records to verified private WP-15 document assets and exact hashes.
3. Implement a central effective-dated entity/site/activity authorization resolver.
4. Enforce site/entity identity at database level for production, receiving, inventory and fulfilment records.
5. Integrate authorization resolution into sourcing, manufacturing, sales/distribution, finance and payroll commands at their appropriate approval/release boundaries.
6. Build legal evidence review, renewal and suspension UI/API and operational hold queues.
7. Add PostgreSQL/RLS, cross-entity/site and exact-source E2E tests under app_runtime.
8. Keep legal, tax, payroll, customs, FX and regulated activity calculations disabled until their separate current primary-source and professional approval evidence is complete.

**Dependency rule:** WP-02 legal/entity/site activation is a shared prerequisite for operational WP-07/09/10/12/13/14 transactions. A tenant-scoped UUID or a DRAFT entity record is not itself authorization to purchase, manufacture, sell, employ, pay or post a regulated transaction. This gate must be enforced centrally and by the owning domain at the exact state transition, not by one UI warning.


---

## 51. WP-09/WP-10 manufacturing execution — operator, recorder and device authority audit

### 51.1 One generic API ability authorizes materially different operation events

**Finding:** ManufacturingController.recordOperationEvent() uses one Gate ability, manufacturing.record-execution, for every supported event type: START, OUTPUT, DEFECT, DOWNTIME, BLOCK, UNBLOCK, COMPLETE and SKIP. It validates event data and passes the authenticated actor ID as recorded_by, but does not resolve event-specific authority for the operation state change.

The service performs a confirmed assignment and current skill-record check for START only. For OUTPUT, DEFECT and DOWNTIME it checks that the operation is IN_PROGRESS and validates the quantity/downtime value; it does not require a currently assigned worker or require the authenticated recorder to be the assigned operator/authorized supervisor. COMPLETE, BLOCK, UNBLOCK and SKIP similarly rely on the same generic route Gate and the domain state checks, not a specific operation role/authority.

**Source**
- backend/app/Http/Controllers/Manufacturing/ManufacturingController.php, recordOperationEvent().
- backend/app/Services/Manufacturing/ManufacturingExecutionService.php, recordOperationEvent() event branches.
- backend/routes/domain-api.php, POST manufacturing/production-orders/{orderId}/operations/{operationId}/events.
- backend/database/migrations/2026_11_26_000016_create_route_and_manufacturing_execution.php, production_execution_events.recorded_by, operator_reference, workstation_reference and source_device_reference.

**Effect:** a user authorized to submit one event category receives the same API-level capability for every other event category. Database actor attribution establishes the authenticated user who submitted the event, but not that the user is qualified/assigned to record output or defects, or authorized to skip/block/complete the operation. A supervisor correction and a worker terminal event are indistinguishable in the current command contract except for caller-supplied reason/reference fields.

**Required implementation**
- [ ] Define separate command policies for operation start, output/defect entry, downtime/Andon, block/unblock, optional-step skip and completion.
- [ ] Resolve each command against active tenant membership, legal-entity/site/work-centre scope, approved domain role/position and the current operation state.
- [ ] For normal shop-floor output/defect events, require a current confirmed worker assignment and bind the event to the assignment ID, skill record ID/version and the actual worker/person/employee identity when the WP-14 identity master is approved.
- [ ] Permit an authorized supervisor/quality/maintenance correction path separately, with a mandatory reason, reference evidence and explicit correction/override decision; do not impersonate an operator.
- [ ] Require independent authority for BLOCK/UNBLOCK, SKIP and re-open/correction actions according to the manufacturing/quality delegation matrix.
- [ ] Store actor_user_id, worker/employee ID where available, assignment ID, role/authority decision snapshot, recorder identity, supervisor override ID and source correlation as separate fields.
- [ ] Add authorization tests proving a user with only output permission cannot skip or unblock; an assigned operator cannot release a quality gate; a supervisor correction is separately authorized and audited.

### 51.2 Operator, workstation and device references are untrusted text rather than registered identities

**Finding:** recordOperationEvent() accepts operator_reference, workstation_reference and source_device_reference as optional text supplied in the request. The service only requires operator_reference to be non-empty for START and then finds a matching workforce assignment and skill row by worker_reference. It does not resolve a registered human identity, workstation registry or authenticated device record for any event.

The current DeviceScanResolutionService is intentionally read-only and only resolves scanned identifiers to candidate tenant records. It does not authenticate a device or issue a trusted event credential. Manufacturing execution accepts source_device_reference directly and does not require a scan event ID, registered device ID or device signature.

**Effect:** the event can assert a worker, workstation or terminal reference that is not itself authenticated. The recorded_by field identifies the HTTP user, but it does not prove who physically operated the machine/terminal or whether the event came from an approved factory device. Shared operator accounts and arbitrary terminal identifiers cannot be distinguished from an authenticated user/device workflow.

**Required implementation**
- [ ] Use the approved WP-14 person/employee/assignment identity as the authoritative worker reference; retain an explicit non-employee contractor/subcontractor identity model where legally/operationally required.
- [ ] Introduce a registered device/workstation identity with tenant, legal entity, site, work centre, machine, credential status, public-key/certificate or equivalent trust binding, expiry/revocation and allowed event categories.
- [ ] Authenticate device-originated submissions separately from human sessions and bind device ID to its registered site/work centre; a caller-supplied source_device_reference is metadata only.
- [ ] Link a device scan event to the manufacturing event through a verified scan-event ID and canonical event payload hash; scan resolution alone must not post production quantity or inventory.
- [ ] Capture device event time, server receive time, clock-skew validation, sequence number, offline batch identity, signature/certificate result and replay outcome.
- [ ] Reject revoked, unregistered, wrong-site, wrong-work-centre and unauthorized-event devices. Provide an explicit manual-entry exception with human operator identity, reason and supervisor approval.
- [ ] Add device registration/revocation, shared-terminal, offline replay, sequence gap, altered payload, time-skew, duplicated event and cross-site tests.

### 51.3 Shop-floor event timestamps and sequence are not bounded by the source operation timeline

**Finding:** the event controller accepts optional occurred_at as a general date value. recordOperationEvent() stores the supplied value or server now() but does not require it to be at/after the operation's start, within a permitted device clock-skew window, earlier than the server receive time by a bounded amount, or in sequence with the previous event. The table stores both occurred_at and created_at, but no normalized device sequence, correction reference or event-order invariant is present.

**Effect:** an authorized API user can backdate/future-date a shop-floor event. This can distort hourly efficiency, downtime, operator earned minutes, incentive attribution, production-day cutoffs, WIP aging and traceability. created_at indicates when the database row was inserted, but the business service does not classify late-arriving offline events separately from retroactive manual corrections.

**Required implementation**
- [ ] Define a canonical temporal policy per event source: trusted server event, authenticated connected device, offline queued device or authorized manual correction.
- [ ] Enforce device clock skew and event sequence where device-originated; retain original device time and normalized server receipt time separately.
- [ ] Reject future timestamps outside the approved skew allowance and prevent ordinary events from predating operation start or contradicting the accepted prior event sequence.
- [ ] Handle late offline events through an explicit reconciliation state; do not silently reorder posted output or rewrite completed production facts.
- [ ] Implement correction/reversal events linked to the original event, reason, authorizer and before/after snapshot instead of editing the append-only execution history.
- [ ] Add tests for backdated output, future downtime, offline event arrival, same timestamp tie-break, event sequence gap, correction, reversal and timezone/daylight boundary.

### 51.4 Output event quantities are not attributed to a size/bundle/quality disposition grain

The operation event stores good and defective quantities at the operation level. The current record contract does not capture a stable bundle/piece/size breakdown, panel/bundle genealogy, defect disposition reference or material-consumption reference. Cutting-bundle schema exists, but there is no cutting-bundle service/controller workflow in the inspected delivery branch and no link between bundle operation events and production_execution_events.

**Required implementation**
- [ ] Add quantity allocation by approved production-order size/colour/SKU breakdown and cutting bundle/piece genealogy where the process requires it.
- [ ] Link defects to an exact bundle/piece/size/operation and NCR/quality disposition; do not treat an aggregate defect quantity as sufficient traceability for high-risk defects.
- [ ] Link output to the order/operation/worker assignment/line/shift and the approved quality gate state.
- [ ] Reconcile bundles issued, operation input/output, rework, loss, defects, finished-goods size/colour quantities and material ledger totals.
- [ ] Add split bundle, mixed size, partial operation, rework, defect recode, bundle transfer and full piece-to-finished-lot genealogy tests.

---

## 52. WP-09/WP-10 shop-floor identity and event implementation sequence

1. Define command-specific authorization and role/position scope for every manufacturing event.
2. Connect worker references to the approved WP-14 identity and effective assignment; keep supervisor corrections distinct.
3. Add registered workstation/device identity, credentials, revocation and site/work-centre binding.
4. Link verified device scan events to manufacturing commands and implement offline queue/reconciliation.
5. Enforce time/sequence rules and explicit correction/reversal events.
6. Add bundle/piece/size genealogy and connect the production event stream to material issue/consumption and quality dispositions.
7. Run non-owner PostgreSQL/RLS, authorization, event replay, concurrency, offline-device and full factory E2E suites on the exact candidate SHA.


---

## 53. WP-08/WP-10 route authoring and production-order transition audit

### 53.1 Route-step authorship is not captured by the service or schema

**Finding:** createRouteVersion() records created_by on the route header. addRouteSteps() accepts only tenant ID, route ID and a step array; its controller does not pass the authenticated actor. process_route_steps has no created_by, updated_by, submitted/approved evidence or source revision columns. The step rows are inserted in a transaction, but no audit_events row or transactional outbox event is written for the route-step set. The route-level submit/decision captures actor identity, but it does not prove who authored or changed each instruction/condition.

**Effect:** the approved route version has a route author and approver, but no immutable line-level author/revision attribution for the ordered work instructions, conditional steps, skill requirements, subcontract flag or quality gates.

**Required implementation**
- [ ] Pass the authenticated route author into route-step creation and persist actor, timestamp and step revision identity.
- [ ] Keep route-step sets immutable after submission; corrections require a new route version or controlled revision, not in-place mutation.
- [ ] Write a route-step-set audit event with a canonical hash over ordered step definitions, conditions, predecessor links, quality gates, work instruction and required skill references.
- [ ] Persist the submitted and approved route manifest hashes on the route header; approval must bind to the exact hash that is later copied to production orders.
- [ ] Add tests proving a change to any step field changes the route manifest hash and cannot alter an already-submitted or order-pinned route.

### 53.2 Work-instruction references are strings, not controlled operational document versions

**Finding:** process_route_steps.work_instruction_reference is a nullable varchar. The route API accepts a caller-supplied string, and the manufacturing service stores it without resolving a document asset, controlled revision, effective date, access classification, worker acknowledgement or hash. There is no route step → document asset FK or query in the inspected route lifecycle.

**Effect:** the route can name a work instruction but cannot prove the instruction exists, is the current approved revision, belongs to the correct style/process/site or was available to the operator at execution. This is particularly important for subcontract, special process, safety and quality-gated work.

**Required implementation**
- [ ] Link each mandatory work instruction to a verified private WP-15 document asset and immutable document version/hash.
- [ ] Capture instruction owner, revision, language, site/process/operation applicability, effective period and approval state.
- [ ] Require acknowledgement/training evidence for roles/processes where the SOP or safety policy demands it; record the exact instruction version acknowledged.
- [ ] Revalidate current instruction applicability at route approval, production-order release and operation start; do not rewrite the order's historical pinned instruction snapshot.
- [ ] Add tests for missing, rejected, expired, wrong-style, wrong-process, altered-object and unacknowledged mandatory instruction.

### 53.3 Production-order start has no authenticated actor attribution in the domain service

**Finding:** ManufacturingController.startProductionOrder() authorizes manufacturing.start-production-order but calls ManufacturingExecutionService.startProductionOrder(tenantId, orderId) without passing the current actor. The service changes status to IN_PROGRESS and writes started_at only. production_orders has released_by/released_at and created_by, but no started_by field or separate order-start event. The operation-level START event later records recorded_by, but it is a distinct action and does not establish who initiated the order-level start.

**Effect:** the system cannot trace the authenticated actor who changed the production order from RELEASED to IN_PROGRESS. An order-level start can be attributed only indirectly through later operation events, which may be recorded by a different worker/supervisor/device.

**Required implementation**
- [ ] Pass the authenticated actor or registered device/service identity to startProductionOrder().
- [ ] Persist started_by and a separate immutable production-order transition event with source status, target status, actor, site/work centre, timestamp and correlation.
- [ ] Keep order-start identity separate from operator/worker identity and operation event recorder identity.
- [ ] Define role/position authorization for order start and verify current legal entity/site authorization, released tech-pack/route/IE/quality/material readiness and pre-production checks.
- [ ] Add tests for missing actor, unauthorized site, expired/revoked role, repeated start, start after cancel/hold/complete and order-level actor versus later operation worker identity.

### 53.4 Production-order release does not enforce creator/releaser separation

**Finding:** releaseProductionOrder() requires a DRAFT order and approved route/technical-pack status, then updates released_by/released_at using the caller actor. It does not compare released_by with created_by and there is no separate submitted/awaiting-release state. Whether the same person may create and release a production order is therefore controlled only by the broad Gate ability, not by the service-level segregation check seen in route and journal approval paths.

**Required implementation**
- [ ] Resolve the production order release maker-checker rule from the approved WP-03 delegated-authority/segregation matrix.
- [ ] Where release is a controlled approval, introduce submitter and independent releaser fields/events and reject same-person release.
- [ ] Bind the release decision to the immutable product passport, route manifest, IE standard, quality plan, material availability/issue snapshot, site/legal authorization and effective instruction versions.
- [ ] Preserve a distinct, bounded sample/prototype release policy where bulk-production controls differ; never treat an ordinary tenant-admin Gate as proof of business release authority.
- [ ] Add positive/negative maker-checker tests, stale dependency tests and site/entity scope tests.

### 53.5 WP-08/WP-10 route and production-order tests are absent

The inspected backend test tree contains no dedicated route/manufacturing/production-order lifecycle feature suite. This means route version approval, step immutability, production order create/release/start, operation state transitions, worker assignment and quality gate integration have no package-specific executed PostgreSQL acceptance suite in this baseline.

**Required implementation**
- [ ] Add ManufacturingRouteLifecycleTest for route author/step manifest, route effective dates, approval segregation, SOP links and supersession.
- [ ] Add ProductionOrderLifecycleTest for product passport, legal entity/site pairing, order release/start actors, pre-production dependencies and history.
- [ ] Add ProductionExecutionEventTest for each event type, operator/device authority, timestamp policy, payload-aware idempotency, quantity bounds and immutable correction.
- [ ] Run all tests as app_runtime with forced RLS; include cross-tenant and same-tenant cross-site/entity denial.
- [ ] Add browser E2E from approved technical release through route/IE/quality/material readiness, order release, skilled operator assignment, line execution, quality release and finished-goods receipt.

---

## 54. WP-08/WP-10 route and production transition implementation order

1. Add immutable route-step author and manifest evidence.
2. Link route instructions to verified WP-15 documents and acknowledgement rules.
3. Persist production-order start actor and order transition history.
4. Implement release maker-checker from the approved WP-03 authority matrix.
5. Bind production-order release/start to effective product, route, IE, material, quality and legal/site dependency snapshots.
6. Add package-specific PostgreSQL feature suites and full manufacturing E2E; report exact SHA and actual test execution.


---

## 55. WP-11 inspection result/review transition — implementation candidate PR #26

### 55.1 Code/schema incompatibility addressed in a separate forward migration candidate

The source review in Section 32.1 established that the initial quality inspection result path and the PostgreSQL check constraint could not complete the intended two-person workflow. submitInspectionResult() writes PASS or PASS_WITH_DEVIATION before reviewInspection() is called. Migration 000017 required reviewed_by and reviewed_at at the same instant that the inspector first submitted a passing result. That caused PostgreSQL to reject the intended intermediate state.

**Candidate:** PR #26 — fix(WP-11): allow inspection submission before independent review

**Base:** delivery SHA fc01eced62b97c4ec27f0075264fc8ba0fb11851

**Head:** d96f8c3ee343b2ec526745d9d30ce7b5740eaeda

**Files**
- backend/database/migrations/2026_11_27_000034_allow_inspection_result_pending_independent_review.php
- backend/tests/Feature/QualityInspectionReviewTransitionTest.php

### 55.2 Database change boundary

The forward migration:
- Locates the current inspection PASS review constraint by its PostgreSQL definition instead of relying on an autogenerated constraint name.
- Refuses to continue if it cannot identify that exact constraint.
- Checks existing rows for reviewer ID/time inconsistency and fails closed rather than inventing missing review data.
- Removes only the requirement that PASS/PASS_WITH_DEVIATION must already have review metadata at inspector submission.
- Adds a database check requiring reviewed_by and reviewed_at to be either both NULL or both present.

The existing release services still require a reviewed passing inspection with reviewer identity and time, so an unreviewed PASS is a submitted result awaiting independent review, not a release authorization. The down migration refuses to restore the strict constraint while any PASS/PASS_WITH_DEVIATION rows remain pending review.

### 55.3 Regression test path

QualityInspectionReviewTransitionTest creates isolated PostgreSQL tenant/user/inspection fixtures using the privileged test-only connection, then exercises the actual QualityControlService sequence using the runtime connection and tenant-local RLS context:
1. Inspector submits accepted quantity equal to sample quantity and receives PASS with reviewer fields NULL.
2. The existing release-eligibility predicate remains false before review.
3. A different reviewer accepts the result and both reviewer fields are populated.
4. The release-eligibility predicate becomes true only after that independent review.

The test is committed as source, but has not been executed successfully yet.

### 55.4 Exact-head workflow evidence

At candidate head d96f8c3ee343b2ec526745d9d30ce7b5740eaeda:
- ERP Continuous Integration run #934, ID 46018178170.
- ERP WP-01–WP-16 completion verification run #364, ID 46018178203.

Both runs completed with failure before any job step executed. Every required job has runner_id 0, empty runner name and steps []. The combined follow-on job was skipped. This is runner allocation failure; it is not an application test failure. The migration and feature test result are NOT RUN.

**Evidence classification:** implementation candidate with diff/source inspection; PostgreSQL execution and exact-head test evidence remain outstanding. PR #26 is open as a draft.

### 55.5 Next acceptance actions

- [ ] Execute migration 000034 against a clean PostgreSQL 18 database after all preceding migrations, including migration 000017.
- [ ] Execute migration-upgrade against a database with historical reviewed and unreviewed/non-PASS inspection rows.
- [ ] Run QualityInspectionReviewTransitionTest as app_runtime and verify inspector submission, independent review and release gating.
- [ ] Run the entire backend suite and combined WP-00–WP-16 exact-SHA verification.
- [ ] Add an end-to-end API test from inspection draft through submission, separate review and material/operation/finished-goods release rejection before review.
- [ ] Merge only after exact-head PostgreSQL and CI evidence passes; keep legal deviation approval and release authority as separate controls.

This candidate does not approve a deviation, override an NCR, authorize a material/production/finished-goods release or remove the human reviewer requirement.


---

## 56. WP-03 organization structure — tenant-wide read visibility policy is not explicit

### 56.1 Current source behavior

**Finding:** the organization master GET endpoints execute with authenticated user and tenant context, and PostgreSQL RLS ensures the data belongs to that tenant. The GET methods for organizational units, job positions, unit manager assignments and position reporting relationships do not call an organization-specific Gate or DomainCapabilityAuthorizer. The create endpoints call the controller's tenant-administrator check. The Angular organization route is directly routable and included in general navigation; the component's canManage predicate hides write forms for non-administrators but loadAll() still fetches the registers.

**Current behavior:** an active tenant member with the normal tenant-context API route can read the tenant's full organization registers; only OWNER/ADMIN can create the current organization masters. This is not evidence of cross-tenant access or an RLS bypass. It is a tenant-wide, read-only visibility policy that is currently implicit in the implementation rather than represented as an approved read capability/visibility matrix.

**Sensitive data context:** organization unit hierarchy, job titles/families/grades, manager assignments and reporting relationships can be business-sensitive. Future employee/incumbent, acting appointment and HR-linked views are more sensitive and must not inherit the same read access automatically.

**Source**
- backend/routes/domain-api.php: enterprise organization GET routes share authenticated tenant context and do not add a distinct organization-read capability middleware.
- backend/app/Http/Controllers/Enterprise/EnterpriseOrganizationController.php: units(), positions(), managerAssignments() and reportingRelations() rely on tenant context; storeUnit(), storePosition(), assignUnitManager() and storeReportingRelation() call assertTenantAdministrator().
- backend/app/Services/Authorization/DomainCapabilityAuthorizer.php: no explicit organization-structure read capability exists in the role matrix.
- frontend/src/app/app.component.ts and app.routes.ts: organization structure navigation/route is available without a separate organization-view guard.
- frontend/src/app/features/enterprise/organization-structure.component.ts: canManage hides write operations, while tenant initialization still calls the list endpoints for read display.
- backend/database/migrations/2026_11_27_000035_create_organization_structure_masters.php: forced tenant RLS prevents cross-tenant rows but does not express a same-tenant role/field visibility policy.

### 56.2 Required implementation under PROD-WP03-005

- [ ] Reconcile the approved GAR-ENT and enterprise identity requirements to an explicit organization visibility matrix for MEMBER, line manager, HR, tenant OWNER/ADMIN and platform support.
- [ ] Separate organization directory read from organization administration writes; do not assume tenant setup authority is the correct role for every read, and do not assume all tenant members may read all structures.
- [ ] Identify permitted fields and scope by actor: unit names/hierarchy, position title/family/grade, manager history, solid-line/functional relationships, entity/site/department boundaries and any restricted organizational metadata.
- [ ] If ordinary employees are intended to see a directory, implement that as an explicit read capability with least-data projection and any site/department/manager scope; keep employee personal/contract/payroll data out of this endpoint.
- [ ] If a register or field is restricted, apply a fail-closed read capability and query-level filtering to every list/detail endpoint, not only Angular navigation or button visibility.
- [ ] Reuse the canonical capability/scope resolver and avoid a competing local role system; make same-tenant scoped-member behavior explicit.
- [ ] Add API/RLS tests for each approved role/scope combination, list totals and search leakage, direct URL/API access, platform support purpose, tenant switch and restricted-field omission.
- [ ] Add Angular route/navigation/empty/denied states that mirror the server authorization result; the API remains authoritative.
- [ ] Confirm the final access policy against the existing privacy, HR and data-retention requirements before exposing future person/incumbent and acting appointment fields.

**Acceptance boundary:** retain current tenant RLS and the existing administrator-only write boundary. Do not broaden reads or break legitimate employee directory behavior until the approved requirement-to-actor matrix is reconciled. The access policy and negative tests must be complete before WP-03 is accepted.


---

## 57. PR #27 — executed source remediation replay for WP-11, WP-15 and WP-16

**Candidate branch:** `work/fix-private-document-storage-rls-inventory-2026-11-27`  
**Base:** `delivery/autonomous-wp-01-16` at `fc01eced62b97c4ec27f0075264fc8ba0fb11851`  
**Exact candidate head:** `1261451a08c2df108e90ef4d335d3bbbce1989a6`  
**Pull request:** [#27 — repair private document storage and RLS inventory](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/27)  
**Candidate comparison:** 20 commits ahead, 0 behind; 10 changed files  
**Current disposition:** source implemented on candidate branch; PR open as draft; not merged into delivery.

### 57.1 WP-15 private-document upload/storage defect — source fix rendered

**Source changed**
- `backend/config/filesystems.php`
- `backend/.env.example`
- `backend/app/Services/Integration/DocumentIntegrationService.php`
- `backend/tests/Unit/PrivateDocumentStorageConfigTest.php`
- `backend/tests/Feature/DocumentIntegrationStorageTest.php`

**Fixes applied**
- Add `PRIVATE_DOCUMENTS_DISK` configuration with a default of the non-served local private disk. Preserve the option to select a configured disk by name; do not impose S3 as a mandatory runtime provider.
- Change the document service fallback from the undefined `s3` disk to `local`.
- Import the global `RuntimeException` and `Throwable` classes used in the namespaced service.
- Register document metadata within a database transaction and verify that the row is present after insertion.
- If metadata persistence fails after the object upload, attempt compensating deletion from the exact selected disk; report cleanup failure while preserving/rethrowing the original persistence error.
- Add unit tests for the local private disk default and selection of a configured named disk.
- Add PostgreSQL feature tests for the upload/metadata path and the duplicate-document-code failure path, which verifies that the just-uploaded object is removed and the original metadata record remains.

**Remaining WP-15 work:** exact PHP/PostgreSQL execution; actual S3-compatible provider integration tests where configured; private source read-back/hash verification; malware scanning; document retention/legal hold; extraction workers; typed import adapters; approval/audit trail and operational retry/dead-letter handling. PR #27 fixes the upload/configuration defect but does not claim the entire document lifecycle complete.

### 57.2 WP-11 quality inspection — explicit pending independent review

The earlier source defect was confirmed by the existing migration 000017 and `QualityControlService`: an inspector's first passing submission wrote PASS/PASS_WITH_DEVIATION while the database simultaneously required reviewer ID/time. The reviewer endpoint could not run after the first write because PostgreSQL rejected the intermediate state.

**Fixes applied**
- Add forward migration `backend/database/migrations/2026_11_27_000038_repair_quality_inspection_review_state.php`.
- Add the explicit `SUBMITTED_PENDING_REVIEW` state and `submitted_result_status` snapshot.
- Inspector submission now records a passing proposal as SUBMITTED_PENDING_REVIEW with the original proposed PASS/PASS_WITH_DEVIATION value retained separately.
- Independent review uses that saved proposal. Only a different reviewer can finalize the result to PASS or PASS_WITH_DEVIATION; reviewer metadata is still required by the database for final passing states.
- The migration first fails closed if any historical inspection has reviewer ID/time values that are not both NULL or both present. It then replaces the result-state/reviewer constraint as one defined allowed-state check, enforces the reviewer ID/time pair for every inspection state, checks pending-state consistency and refuses rollback while pending reviews remain.
- Extend `QualityInspectionReviewStateTest` to verify:
  1. inspector submission reaches SUBMITTED_PENDING_REVIEW;
  2. inspector cannot review their own result;
  3. the release eligibility predicate is false before independent review;
  4. a different reviewer accepts and records identity/time;
  5. release eligibility becomes true only after review;\n  6. a database check rejects reviewer ID without reviewer timestamp on any inspection state.
- Existing quality release services continue to require a reviewed passing inspection; this change does not approve deviations or release inventory/production/finished goods.

**Migration ordering:** migration 000038 is deliberately placed after the candidate reconciliation sequence for WP-03 platform migrations 000033/000034, WP-03 organization migration 000035, and WP-14 payroll evidence migrations provisionally sequenced as 000036/000037. This is a planned sequence for candidate integration, not evidence those candidate migrations have been merged. Confirm the final migration tree after rebasing/integration before merging PR #27.

### 57.3 WP-11 CAPA and nonconformance transaction correction

- `createCorrectiveAction()` now runs the nonconformance read, row lock, corrective-action insert and nonconformance state update inside one retryable transaction.
- The service reads back the newly created corrective-action record and fails rather than returning false success if it was not persisted.
- Resolved or closed nonconformances are rejected; a new CAPA cannot silently reopen a previously resolved record. Reopening remains a distinct controlled workflow.
- PostgreSQL feature tests cover atomic successful creation/status update and rejection of CAPA creation against a resolved nonconformance.

**Remaining WP-11 work:** deviation/waiver authority must be a separately evidenced decision, not the current request boolean; containment evidence, disposition authority, AQL/sampling plans, inspection standards, current-effective release/hold, CAPA evidence linkage and concurrency between new holds and route progression remain separate tasks.

### 57.4 WP-03/WP-16 RLS inventory and verification scope correction

- Add the three previously omitted platform control tables to the explicit domain RLS test: `platform_operator_grants`, `platform_tenant_entitlements`, `platform_audit_events`.
- Add dynamic RLS feature-test discovery for all public base/partitioned tables that contain a `tenant_id` column, requiring both `relrowsecurity` and `relforcerowsecurity`.
- Replace the workflow's manually maintained tenant-table list with the same dynamic database inventory approach; retain an explicit check for global `platform_operator_grants`, which has no tenant_id column.
- Rename the combined verification workflow to `ERP component and WP-01 package verification` and the combined source archive to an ERP component candidate. The workflow runs backend/frontend component checks and WP-01 package checks; it does not execute the complete WP-00–WP-16 E2E acceptance matrix. The new name now reflects the work actually performed.

### 57.5 Reconciliation of duplicate PR #26

PR #26 (`fix/wp11-inspection-review-transition-2026-11-27`, head `d96f8c3ee343b2ec526745d9d30ce7b5740eaeda`) contained an earlier alternative migration numbered 000034 and a focused feature test for inspector submission, reviewer separation and release eligibility.

- Its behavior and release-eligibility assertions were incorporated into the stronger PR #27 implementation.
- Its migration 000034 would conflict with WP-03 platform/scope migrations and WP-14 statutory evidence migration candidates using the same sequence.
- PR #26 was commented as superseded by PR #27 and closed. No duplicate quality-state migration should be integrated from PR #26.

### 57.6 Exact-head CI and verification evidence

At PR #27 head `1261451a08c2df108e90ef4d335d3bbbce1989a6`:
- ERP Continuous Integration: run #46023983272.
- ERP component and WP-01 package verification: run #46023983506.

Both runs ended before any test step. Backend, Angular and WP-01 jobs report `runner_id: 0`, empty runner name and zero steps; the archive follow-on was skipped. Therefore:
- Source files, test files, migration and workflow contents have been fetched back from the branch and statically checked for the expected implementation patterns.
- PHP syntax, Pint, Laravel/PostgreSQL migrations, feature tests, Angular build/tests and WP-01 source-package verification are **NOT RUN** on this exact head.
- No test pass or application failure is inferred from the runner allocation response.
- PR #27 remains open as a draft; delivery remains at its existing baseline until the required exact-source verification executes successfully.

### 57.7 Next actions before integration

- [ ] Restore an executable CI runner or an owner-controlled trusted self-hosted runner; preserve every existing test/security gate.
- [ ] Run migration 000038 against a clean PostgreSQL 18 database and an upgrade database containing historic inspection rows.
- [ ] Run `PrivateDocumentStorageConfigTest`, `DocumentIntegrationStorageTest`, `QualityInspectionReviewStateTest`, `DomainRlsSecurityTest` and the complete backend suite as the restricted runtime role.
- [ ] Run Angular tests/build, dependency audits, forced-RLS inventory and WP-01 source package checks on the same exact PR head.
- [ ] Resolve candidate migrations 000033–000037 and verify the final complete migration order before applying PR #27's migration 000038.
- [ ] Execute the same checks on the resulting delivery merge SHA after integration.
- [ ] Continue the remaining WP-11 deviation/containment/AQL and WP-15 extraction/import/retention work; this PR closes only the bounded defects above.

**Evidence classification:** source fix implemented and PR opened; static retrieval/review complete; executable tests NOT RUN due runner allocation; not merged into delivery.

---

\n**Additional reviewer-integrity safeguard:** migration 000038 now preflights historical rows for an unmatched reviewer ID/timestamp pair and enforces the pair all-or-nothing for every inspection state. A PostgreSQL feature test verifies the database rejects incomplete reviewer metadata.\n