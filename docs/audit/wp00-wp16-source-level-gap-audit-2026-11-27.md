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

### Overall status by work package

| Work package | Assessment at the audited delivery head | Production disposition |
|---|---|---|
| WP-00 Governance | Owner-approved governance and autonomous delivery direction recorded | Accepted governance baseline |
| WP-01 Requirements | R3 evidence archive verified; requirements implementation and identifier reconciliation remain open | Not implementation-complete |
| WP-02 Ethiopia legal foundation | Legal-source register internally accepted; professional legal interpretation and activation remain separate | Source register only; legal holds remain |
| WP-03 Enterprise/SaaS foundation | Tenant, entity, membership, role and lifecycle foundations integrated; authorization and platform-operator gaps remain | Partial; scoped access fail-closed |
| WP-04 Merchandising | Initiative, insight, range, assortment, OTB, price and promo slices | Partial |
| WP-05 Product development | Style, measurement, pattern, sample/fit, BOM and tech-pack foundations | Partial |
| WP-06 Industrial engineering | SAM/time study, capacity and workforce-control foundations | Partial |
| WP-07 Sourcing/procurement | Supplier, sourcing, PO, receipt and material-lot traceability foundations | Partial |
| WP-08 Route/SOP management | Process-route versions and initial route gates | Partial |
| WP-09 Material issue and production genealogy | Issue/return/consumption and initial bundle/route genealogy | Partial |
| WP-10 Manufacturing execution/planning | Initial production queues, events, skills, output and capacity slices | Partial |
| WP-11 Quality/compliance | Inspection, defect, NCR, CAPA and release-decision foundations | Partial |
| WP-12 Distribution/commerce | Finished-goods lots, locations, reservations, credit decisions, orders and shipment foundations | Partial |
| WP-13 Finance/treasury | Accounts, periods, journals, balanced posting and reversal foundations | Partial |
| WP-14 People/payroll | Existing payroll-review foundation; new Ethiopia calculator is on a separate unmerged branch | Candidate-only; payroll activation HOLD |
| WP-15 Documents/integrations/agents | Document, extraction, outbox, scan and agent-governance records; live processing is incomplete | Partial |
| WP-16 System quality/release | Release design and component CI exist; complete E2E/security/performance/recovery/UAT/deployment evidence does not | Release NOT ACCEPTED |

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

## 8. Final release decision and evidence rules

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
