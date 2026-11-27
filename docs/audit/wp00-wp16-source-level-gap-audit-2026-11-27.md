# WP-00 to WP-16 — Source-Level Gap Audit and Implementation Checklist

**Audit date:** 27 November 2026  
**Repository:** stocklandethiopia/modern-garment-end-to-end-addis-erp-project  
**Audited canonical integration branch:** delivery/autonomous-wp-01-16  
**Canonical delivery head inspected:** fc01eced62b97c4ec27f0075264fc8ba0fb11851  
**Default branch inspected:** main at adca71a053290eb768987a7644d4b13c267cf2a9  
**Purpose:** identify the source, data, workflow, control and verification work required to complete the canonical production ERP and reproducible master installer.

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
| WP-03 Enterprise/SaaS | Tenant, legal entity, membership, invitations, scoped-access foundation, domain roles, ownership succession, entitlements and tenant lifecycle migrations/services | Complete platform-operator grant lifecycle, target-resource scope enforcement, expiry/recovery, SSO/MFA and entitlement enforcement |
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
| WP-14 People/payroll | Canonical payroll-review foundation; Ethiopia calculator and legal-engineering evidence exist on a separate candidate branch | Reconcile and review candidate source, complete HR/time/leave/benefits, statutory evidence and payroll-to-GL/remittance paths |
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

## 4. Unmerged, mis-targeted and unapplied source reconciliation

This section is important because a commit existing in GitHub does not mean it has landed in the correct canonical branch or folder.

| Item | Branch / PR | Verified state | Action |
|---|---|---|---|
| Main executable platform | PR #1, delivery/autonomous-wp-01-16 | Open draft; 813 commits; 290 files; target main | Keep as mainline integration/release decision. Do not call main application-complete until PR is reviewed and release criteria are met. |
| WP-03 operator lifecycle and scope DB hardening | PR #13, work/wp03-completion-2026-11-27 | Open draft; 77 commits; 33 changed files; 15 commits behind current delivery; includes migrations 000033/000034 | Rebase/reconcile and security-review. Execute exact-head CI before merge. Do not merge stale branch as-is. |
| WP-14 Ethiopia payroll candidate | work/wp14-ethiopia-payroll-localization-2026-11-27 | 20 commits ahead; 0 behind; 11 files; no PR; no workflow run found | Create PR against current delivery, review arithmetic/evidence/API, add feature tests and execute exact-head CI. Keep activation flags false. |
| Provider-neutral document storage fix | PR #10 merged into work/wp01-16-completion-2026-11-26 | Merged on an intermediate branch, not on current delivery | Port the relevant fix to delivery: add PRIVATE_DOCUMENTS_DISK config defaulting to local, change service fallback to local, import RuntimeException, add tests, preserve non-loopback environment defaults. |
| Apparel library sample reconciliation | PR #14 | Open draft; one documentation file | Review/merge as documentation only after exact-head checks execute. Do not treat schemas as live runtime contracts or copy legacy WordPress source. |
| Company organogram and delegated authority | PR #16 | Open; six commits; three documentation files | Review/merge as architecture documentation only. Then implement position/effective-date/amount/budget/scope/acting-delegation approval model and tests. |
| WP-01 R3 crosswalk correction | PR #17 | Open; three commits; three documentation files | Review/merge as traceability correction. The semantic crosswalk itself remains open and implementation statuses remain not_started until evidence is supplied. |
| Agent-native experience/Codex blueprint | PR #3 | Open draft; six commits; 30 changed files; base is corrective-action audit branch, not delivery | Reconcile document direction with current product standard; cherry-pick/rebase documentation only if accepted. Do not claim agents/devices/UI are implemented by the blueprint. |
| Earlier SHA/Drive corrective-action audit | PR #2 | Open draft against isolated source-intake branch | Keep separate from runtime. Use as provenance/audit record only; do not merge its intake artifacts into application runtime without a specific approved reason. |
| No-loopback runtime default correction | PR #12 | Closed/merged into delivery as fc01eced | Code change is integrated. Exact merge-SHA CI remains NOT RUN due runner-allocation failure; rerun once Actions runners are available. |

### 4.1 Provider-neutrality defect in current delivery

Source comparison confirms:
- Current delivery backend/config/filesystems.php does not define filesystems.private_documents_disk and defines no S3 disk.
- Current delivery backend/app/Services/Integration/DocumentIntegrationService.php uses config('filesystems.private_documents_disk', 's3').
- Current delivery service uses RuntimeException in the storage-failure path without importing it.
- PR #10 contains the missing private_documents_disk setting (default local), local fallback and RuntimeException import; it also adds a unit test and provider-neutral documentation.
- The PR #10 branch's older .env/config contents contain localhost examples and must not be copied wholesale. Apply the storage fix to the current delivery version, preserving the current no-loopback environment guard.

This is a functional upload-path issue, not just a documentation preference.

### 4.2 Workflow execution state

- Delivery head fc01eced: ERP CI runs #45915268942 and #45915273928 show both Angular and Laravel jobs completed as failures with runner_id 0, blank runner names and zero steps.
- WP-03 candidate head 8ae8536b: run #45915554309 likewise has zero-step jobs and runner_id 0.
- Earlier delivery head f948bb85: ERP CI runs #45901553898 and #45901559007 executed backend migration/RLS/test and Angular test/build steps successfully.
- PR #14, #16 and #17 also record recent retries that failed before job execution with runner_id 0 and zero steps.
- WP-14 branch has no run evidence in the inspected workflow-run listing.

**Interpretation:** the most recent failures described above are runner-allocation/pre-job failures, not proof that the code failed tests. Equally, they are not proof the code passed. Restore exact-head executable CI as a prerequisite to merging candidate work.

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

## 22. WP-05 / WP-10 implementation sequence

1. Implement material master mapping and BOM resolution with WP-07.
2. Complete stored-object integrity/rights verification with WP-15.
3. Implement effective IE, route/SOP, quality-plan and costing dependency contracts.
4. Make technical-pack readiness evidence-based and release only the exact complete product passport.
5. Require released tech-pack state for bulk production order creation/release; provide an explicit bounded prototype route where needed.
6. Add WP-05 PostgreSQL feature suites and the complete product passport browser E2E journey.
7. Add the same exact dependency/hash references to manufacturing order start and finished-goods genealogy.

No WP-05 or WP-10 runtime source has been modified by this audit addendum. It extends the canonical source-to-plan implementation register with cross-package release controls and acceptance evidence.


---

## 21. WP-05 BOM material resolution and technical-release state audit

### 21.1 BOM material verification has no permitted transition to VERIFIED

**Finding:** the current BOM lifecycle creates a permanent mismatch between the WP-07 material-resolution handoff and the WP-05 release-readiness check.

**Source evidence**
- backend/app/Services/ProductDevelopment/BomTechnicalPackService.php, createBomVersion() line 68, always creates the BOM with material_resolution_status = UNVERIFIED_WP07.
- The same service's submitBom() around lines 204–246 requires the material-resolution status to still be UNVERIFIED_WP07 at submission.
- releaseReadiness() around lines 598–655 requires the approved BOM's material-resolution status to be VERIFIED.
- backend/database/migrations/2026_11_22_000012_create_bom_and_tech_pack_controlled_releases.php, line 26, defines the field default as UNVERIFIED_WP07; lines 248–249 allow only UNVERIFIED_WP07 or VERIFIED.
- The guard_style_bom_version_state() trigger around lines 297–336 allows only DRAFT → PENDING_APPROVAL and PENDING_APPROVAL → APPROVED/REJECTED transitions, and both transition branches explicitly require NEW.material_resolution_status IS NOT DISTINCT FROM OLD.material_resolution_status.
- The trigger has no permitted DRAFT → DRAFT material-resolution update or a separate WP-07 verification transition.
- The inspected SourcingService methods contain supplier, PO and receipt actions but no method that verifies a BOM's material references or writes a controlled resolution record. BomTechnicalPackService also has no material-resolution command.

**Effect:** a BOM created through the current service remains UNVERIFIED_WP07 through submission and approval. The release-readiness method can only pass the material-resolution check if the value becomes VERIFIED, but the service exposes no transition and the database trigger prevents changing it on the existing row. This is a dead-end in the source state machine.

**Required implementation**
- [ ] Define WP-07's material-resolution contract for every BOM line: approved material master, component identity, UOM conversion, supplier qualification/scope, legal entity/site, effective dates, procurement/stock status, MOQ/lead-time and evidence references.
- [ ] Prefer a versioned child resolution record (for example, bom_material_resolution_reviews) bound to tenant, BOM version and BOM-line snapshot, rather than mutating the immutable engineering BOM.
- [ ] Record one decision per BOM revision with resolver, independent reviewer where required, decision time, source evidence IDs/hash, unresolved lines, exceptions and an immutable resolution-manifest hash.
- [ ] If retaining the status column, use a forward migration to introduce one narrow, authorized, audited state transition with strict old/new predicates; do not edit the already-applied migration or weaken the general BOM immutability trigger.
- [ ] Make the material-resolution action callable only by the WP-07 service role/capability and bind the resolved material identities to the exact approved BOM line/version.
- [ ] Recheck resolution freshness when a supplier/material is suspended, a site/entity changes or an effective-date rule changes; invalidate or require a new resolution rather than silently reusing stale approval.
- [ ] Add PostgreSQL feature tests for unresolved, partially resolved, fully resolved, stale, wrong-tenant, wrong-site/entity, revoked supplier and concurrent resolution attempts.
- [ ] Add a full WP-05 → WP-07 → WP-11 → WP-08/WP-06 → WP-13 release-readiness test and verify the exact accepted BOM snapshot.

### 21.2 BOM substitution candidates cannot reach quality approval or withdrawal

**Finding:** a substitution candidate is created as PROPOSED with quality_approval_status = PENDING_WP11. Migration 000012 constrains quality_approval_status to exactly PENDING_WP11 (around lines 269–270). proposeSubstitution() is the only exposed substitution method in BomTechnicalPackService; the inspected QualityControlService and QualityController have no substitution-review/approval action. releaseReadiness() counts any candidate whose approval status is not APPROVED, so a proposed substitution becomes a permanent release blocker once material resolution is marked verified. The table status supports WITHDRAWN, but no service/controller action performs that transition either.

**Required implementation**
- [ ] Add a WP-11 quality review record/decision with reviewer, approved alternative material identity, equivalence/test evidence, affected BOM line/revision, conditions and effective period.
- [ ] Add forward migration constraints that permit only the real lifecycle states and require decision metadata for APPROVED/REJECTED/WITHDRAWN; preserve maker-checker and immutable decision history.
- [ ] Add controlled withdraw/revise/resubmit actions for the proposer or authorized style owner while the BOM is still eligible for revision.
- [ ] Ensure a substitute is not treated as the selected material merely because a candidate was proposed; the selected line and approved alternative must be explicit.
- [ ] Test quality approval/rejection, expired approval, wrong BOM version, withdrawn candidate, changed substitute, tenant boundary and release-readiness impact.

### 21.3 Technical-pack release readiness contains permanent placeholder blockers

**Finding:** releaseReadiness() adds the following blockers unconditionally:
- OBJECT_STORAGE_NOT_VERIFIED (WP-15);
- INDUSTRIAL_ENGINEERING_NOT_VERIFIED (WP-06);
- ROUTE_AND_SOP_NOT_VERIFIED (WP-08);
- QUALITY_RELEASE_NOT_VERIFIED (WP-11);
- PRODUCT_COSTING_NOT_VERIFIED (WP-13).

This correctly prevents a technical pack from being treated as fully ready while those cross-domain capabilities are not evidenced. However, these entries are static code additions rather than queries to versioned approvals/contracts. Even after the dependent modules are implemented, the method will continue to return release_ready = false until the placeholder logic is replaced.

**Required implementation**
- [ ] Retain fail-closed behavior while each dependency is absent.
- [ ] Replace each permanent blocker with a typed readiness adapter and versioned evidence lookup: verified storage manifest; approved IE operation/method/SAM standard; approved manufacturing route/SOP revision; approved quality plan/critical-to-quality checks; approved cost roll-up.
- [ ] Pin each evidence record to the exact style revision, BOM, measurement, pattern, fit-approved sample, legal entity/site, customer and effective date as applicable.
- [ ] Snapshot the exact dependency IDs, versions and hashes into the release manifest; re-evaluate immediately before release under transaction locks.
- [ ] Return actionable blockers with the missing evidence reference and owning WP, not just fixed placeholder strings.
- [ ] Test that missing evidence blocks release and that adding complete, current, authorized evidence enables it; changing/revoking/superseding any dependency invalidates readiness.

### 21.4 Technical-pack state contract has an unreachable RELEASE_BLOCKED value

**Finding:** migration 000012's tech_pack_versions_decision_chk includes RELEASE_BLOCKED in its branch where decision fields must be null, but tech_pack_versions_status_chk does not include RELEASE_BLOCKED in the allowed status list. The status is therefore not a legal persisted state despite being referenced by another constraint.

**Required implementation**
- [ ] Decide whether release readiness is represented only as a computed response or as a persistent lifecycle status.
- [ ] If computed, remove the unreachable RELEASE_BLOCKED branch in a forward migration and keep release blockers in a dedicated evidence/readiness result.
- [ ] If persisted, add it intentionally to the status constraint and define legal transitions, retry/reassessment, audit and decision-field requirements.
- [ ] Add migration tests that exercise every allowed status and transition.

### 21.5 WP-05 regression and integration sequence

1. Define and implement the immutable WP-07 BOM material-resolution record and service handoff.
2. Add the WP-11 substitution approval/withdrawal record and API lifecycle.
3. Replace fixed technical-pack dependency blockers with versioned readiness adapters, retaining fail-closed behavior.
4. Correct the RELEASE_BLOCKED constraint mismatch using a forward migration.
5. Add backend PostgreSQL feature tests for BOM lifecycle, source material verification, substitutions, asset verification and complete release-manifest creation.
6. Add Angular product-development/BOM/tech-pack workbench tests for line resolution, substitution state, approval queues and readiness blockers.
7. Execute a cross-domain production-intent journey using the exact release candidate SHA; preserve the complete evidence manifest.

No WP-05/WP-07/WP-11 runtime source or database migration has been modified by this audit section. It records the verified state-machine defect and the required forward implementation path.


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
