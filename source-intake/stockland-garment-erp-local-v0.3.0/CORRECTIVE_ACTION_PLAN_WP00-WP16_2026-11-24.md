# Corrective Action Plan — WP-00 to WP-16

**Date:** 24 November 2026  
**Status:** prepared for Product Owner review; this plan is not an implementation handover.  
**Canonical baseline:** approved project governance, domain map, apparel operating-system charter, platform standard, security standard, legal register and code/deployment standards on `main`.  
**Reference inputs:** source audit v0.3.0, SHA reconciliation, user-supplied Python Local Reference Runtime report.  
**Disposition:** release remains **BLOCKED / NOT APPROVED** until every WP is delivered in the approved sequence and WP-16 evidence is accepted.

## 1. Evidence reconciliation and principles

Three evidence streams are kept separate:

1. **Stockland v0.3 Node.js/SQLite app:** archive and embedded manifest SHA values match; 106 maintained manifest files pass local size/hash checks; 107 files total; GitHub Actions run #7 passed package checks. The app is v0.3.0, SQLite schema v2 and 45 tables. Its actual TAP run is 15/15, not 16.
2. **Python Local Reference Runtime:** the report states 16 passing tests and a 19-stage replay. The committed Python source archive has now been extracted in GitHub Actions; all seven source-file hashes matched and 16 tests passed again on Python 3.12.16, including the 19-stage replay test. It explicitly does not verify the canonical stack.
3. **Canonical product:** customer-neutral, initiative-first multi-tenant apparel SaaS, using the approved Angular 22 / Laravel 13 / PHP 8.5 / PostgreSQL 18 / Redis baseline. Neither local runtime proves canonical completion or production readiness.

Corrective work must use canonical architecture and the strict numbered WP sequence. The local applications are reference/intake assets only; do not copy their runtime/schema into canonical `backend/` or `frontend/`. No new business scope or architecture deviation is approved by this plan.

## 2. Immediate evidence corrections

| ID | Finding | Action | Exit evidence | State |
|---|---|---|---|---|
| CAP-E01 | Archive identity/manifest could be ambiguous | Reconcile supplied sidecar to archive and embedded manifest; re-hash every file | Archive SHA + manifest SHA + 106/106 per-file match | **DONE** |
| CAP-E02 | Python 16-test and Node 15-test reports could be conflated | Keep separate runtime records and exact test counts | Separate evidence reports identify runtimes and tests | **DONE** |
| CAP-E03 | Stand-in 19-stage replay could be treated as canonical acceptance | Port the verified fixture and control cases to canonical Laravel/PostgreSQL integration tests | Python stand-in replay passes; canonical replay at exact Laravel/PostgreSQL SHA remains required | OPEN — CANONICAL PORT |
| CAP-E04 | Python source hashes were initially declarations without source bytes | Retrieve committed runtime archive and recompute all seven file hashes | GitHub Actions run 45397701554: 7/7 hashes match; 16/16 tests pass | DONE — REFERENCE ONLY |
| CAP-E07 | A source-audit commit message labels the README hash as the verification-package hash | Record README file SHA separately from both archive SHA values | README hash f4f5… identified as README; archive hashes b8b58… and c2124… verified | DONE |
| CAP-E05 | Two different Drive folder IDs could be conflated | Keep each folder ID and retrieval result tied to its own import | Successful `1Kl3...` v0.3 import separated from later `1o4y...` listing failure | **DONE** |
| CAP-E06 | Run #7 summary shell interpreted Markdown backticks, producing harmless `command not found` lines | Use quoted heredoc or safely escaped text in reporting step | Exact-SHA CI log free of shell substitutions from report formatting | OPEN |

## 3. WP-00 to WP-16 corrective action register

The sequence below follows the approved governance agreement. Implementation must not skip, merge or reorder work packages. This plan details later work now but does not authorize starting later implementation before the prior WP handover is accepted.

### WP-00 — Governance and baseline control
**Finding:** local application docs do not replace canonical governance, decision history or Product Owner handover.  
**Actions:** pin the governing documents and exact commit; maintain a decision/change register; record scope, owner approval, impact, implementation commit and tests; standardize PASS/FAIL/NOT RUN/BLOCKED/NOT APPLICABLE/PENDING OWNER APPROVAL labels; keep tenant-local source and stand-ins separate.  
**Acceptance:** every change and release decision links to baseline requirement, approval, commit and test; no material architecture/legal/scope changes occur silently; independent maker-checker applies to restricted actions.  
**Owners:** Product Owner, governance/product lead, chief architect. **Dependency:** none.

### WP-01 — Requirements and traceability
**Finding:** v0.3's 30 traceability rows are not a complete canonical domain/role/workflow/data/control/evidence/acceptance/owner map.  
**Actions:** decompose the 12-domain value stream and all WPs into stable requirement IDs; map each to persona/role, trigger, state flow, exception/rework, data owner, entity/API/event, permissions, legal source, audit, KPI, test and owner; record assumptions and exclusions. Include initiative-first own-brand/own-store flows as well as buyer-order flows.  
**Acceptance:** 100% of approved requirements have trace links; no orphan critical tests or unowned critical requirements; gaps are reported honestly. **Owners:** product analyst, domain owners, QA. **Dependency:** WP-00.

### WP-02 — Ethiopia legal-source verification
**Finding:** no complete qualified, effective-dated Ethiopia rules library in v0.3; canonical legal register has unresolved items including duplicate catalogue metadata/date for Tax Administration Amendment No. 1434/2026.  
**Actions:** verify primary Gazette/authority instruments for income tax/PAYE, VAT, withholding/invoicing, pension, labour/working time/leave/OSH, licensing, customs/FX/import/export, environment/chemicals/waste, privacy and product compliance; store instrument, clause, authority, publication/commencement/effective dates, applicability, amendments, transitions, reviewer, tests and approval; distinguish law from buyer standards and company policy; block unverified rule activation.  
**Acceptance:** all executable rules have primary-source evidence and qualified local approval; date-boundary, transition and historical transaction tests pass; ambiguity returns BLOCKED, not a guessed rate. **Owners:** local legal/tax/payroll professionals, localization lead. **Dependency:** WP-01.

### WP-03 — Enterprise and tenant foundations
**Finding:** v0.3 is Stockland-branded local SQLite, not customer-neutral SaaS; Python stand-in's cross-tenant tests do not verify PostgreSQL RLS.  
**Actions:** model tenant, subscription/entitlement, legal entity, business unit, brand, factory, department, work centre, DC, branch, store and channel distinctly; PostgreSQL 18 system of record; tenant-aware FKs/unique keys; forced RLS; server-derived tenant context; scoped API, job, cache, file, export, log and event; MFA/SSO-ready identity, least privilege and audited break-glass.  
**Acceptance:** adversarial tenant A/B tests prove no read/infer/mutate/export/delete/job/cache/file leakage under restricted PostgreSQL roles; migrations and tenant lifecycle pass; concurrency tests show no cross-tenant or lost-update defects. **Owners:** platform, backend, database/security leads. **Dependency:** WP-00–WP-02.

### WP-04 — Initiative, brand, range and merchandising
**Finding:** basic product/customer data does not cover initiative-first business strategy, brand, collection, assortment and merchandising.  
**Actions:** opportunity/idea intake; market/customer/trend insight; target wearer, price, margin and demand; portfolio stage gates; brand architecture, season/drop, collection, capsule, range/line plan, size curves, colorways, channel/store assortment, open-to-buy, replenishment and product lifecycle; commercial critical path and store/sell-through feedback.  
**Acceptance:** own-brand collection progresses from idea to approved range without a buyer PO; buyer-led development also works; quantities, price, margin, capacity and material exposure reconcile; approvals retain rationale. **Owners:** merchandising, product strategy, commercial. **Dependency:** WP-03.

### WP-05 — Design studio and product development / PLM
**Finding:** v0.3 has basic item data, not full design studio/PLM/CAD/sample/fit/BOM/asset release. The stand-in's technical-release gate is only a control reference.  
**Actions:** design briefs/concepts/flats/artwork; pattern library, 2D CAD, grading, pattern-piece identity and marker revisions; 3D visualization with version/comments and physical-fit rules; sample-room requests, capacity, fit/measurement review and approvals; technical packs, construction, measurement tolerances, component BOM, approved substitute, costing handover and revision control; tenant-scoped object storage with digest, access, malware scan and rights evidence; immutable bulk-production snapshot.  
**Acceptance:** production cannot consume draft/superseded style data; revision impact maps BOM/route/marker/cost/quality; asset upload/download security checks pass; sample and fit approvals are auditable. **Owners:** PLM/design/technical/CAD/sample leads. **Dependency:** WP-04.

### WP-06 — IE, product engineering and pre-production
**Finding:** source audit found no complete IE/SMV/SAM evidence. Stand-in says approved SAM is required but does not prove full IE/capacity.  
**Actions:** observed time-study cycles, rating, allowance, sample/outlier policy, work element breakdown, method revision/effective dates; operation bulletins, machine/attachment, skill and ergonomic needs; SAM/SMV calculation; line balancing, bottleneck, changeover, learning curve and capacity by work centre/shift; style/site-specific pre-production gate.  
**Acceptance:** SAM reproducible from approved observations; unapproved time studies cannot be used by planning; capacity calculation is explainable; pre-production cannot release with missing tech/sample/BOM/material/route/quality/packaging/capacity approval. **Owners:** IE, production engineering, workforce skill. **Dependency:** WP-05.

### WP-07 — Procurement, suppliers and material traceability
**Finding:** v0.3 has partial PO/receipt. The Python stand-in source is now available in the isolated GitHub reference archive and its tests pass, but the local database/runtime remains a control reference rather than a canonical WP-07 implementation.  
**Actions:** supplier qualification, RFQ/quote, PO/ASN/GRN, receiving/inspection/3-way match; four distinct non-combinable ledgers for fabric, trim/accessory, chemical and packaging; fabric mill/construction/GSM/width/color/dye lot/shade/roll/length/shrinkage/defect genealogy; trim artwork/compatibility/lot; chemical SDS/restricted-substance/expiry/storage/batch; packaging label/pack revision; acceptance/quarantine/reject/conditional/reserved/issued/returned/consumed/scrap states; strict UOM and material-master resolution; append-only movements with one authorized reversal; import/FX/customs/landed-cost evidence.  
**Acceptance:** separate-ledger fixture reconciles; units and cost basis explicit; original events immutable; one reversal only; rejected/expired/missing-SDS/over-issue/UOM-mismatch/unapproved-substitute blocks; supplier-to-FG genealogy traceable; duplicate receipts/invoices do not duplicate stock/AP. **Owners:** procurement, material control, quality, EHS, finance. **Dependency:** WP-06.

### WP-08 — Department SOP and route framework
**Finding:** no canonical versioned route/SOP engine. The Python stand-in route-order and conditional-stage tests have now been independently re-run and pass.  
**Actions:** versioned conditional/parallel/optional/subcontract/rework route graphs selected by style/material/finish/buyer/site; department SOP/work-instructions with inputs/outputs/method/equipment/skills/parameters/quality gates/roles/exceptions; release route pinned to approved style revision. A skipped step must be absent from the approved route or have controlled authorization; no silent stage jump.  
**Acceptance:** premature operation blocked; correct order enforced; 19-stage fixture reaches stage 19 with stage 10 skipped only because that route excludes it; route revisions do not mutate open orders. **Owners:** process architect, department engineering, quality. **Dependency:** WP-07.

### WP-09 — Manufacturing execution by department
**Finding:** v0.3 lacks end-to-end departmental MES; stand-in replay does not prove split/merge, reservations or full WMS.  
**Actions:** MPS/MRP/reservation/issue; fabric relaxation/roll shade allocation, marker/spread/lay/cut/panel defects/recut/remnants/scrap; QR bundles with split/merge/marriage; separate trim issue/return; print/embroidery/artwork/first-off; sewing line setup/operator/machine/quality/WIP; wet/special processes with recipe/chemical/time/temp/pH/water/energy/effluent; finishing/pressing/rework; packing/ratio/carton; subcontract custody; append-only idempotent scan events.  
**Acceptance:** fixture replay 100 cut / 99 good / 1 reject / 99 FG / 1 outbox; bundle reconciliation and split/merge pass; duplicate same-payload scan has one effect; changed-payload key reuse is rejected; partial/rework/recut/route skip and station failure preserve genealogy. **Owners:** factory operations and MES/domain engineering. **Dependency:** WP-08.

### WP-10 — Production planning and workforce capability
**Finding:** v0.3 has employee/attendance/payroll records but not line/shift/hour planning, SMV efficiency, skills or bottleneck control.  
**Actions:** schedule by plant/department/work centre/line/shift/day/hour using approved SAM, productive minutes, target efficiency, skills, machine capacity, materials, maintenance and due dates; plan-vs-actual, earned minutes, efficiency, DHU, rework, downtime and WIP age; competency/certification expiry and training; optional policy-versioned incentives with fair-work and safety guardrails; Kaizen/5S/Andon/before-after improvements.  
**Acceptance:** unapproved SAM blocks target; plan/actual/efficiency matches approved vectors; skill expiry governs deployment; incentives cannot reward unsafe speed or defect concealment; historical standards remain effective-dated. **Owners:** planning, IE, HR/learning, qualified labour reviewers. **Dependency:** WP-09.

### WP-11 — Quality, compliance and HSE
**Finding:** v0.3 only has basic returns/damage; stand-in reports quality gates but not full canonical buyer sampling, inspection or HSE.  
**Actions:** buyer/style/route/material/process-specific revisioned control plans; sampling level/lot band/sample/critical-major-minor/Ac-Re from approved contract standards; incoming, first-piece, inline, end-line, special-process, final and shipment inspection; calibrated equipment and inspector competence; immutable defects/evidence; hold/containment/NCR/root cause/CAPA/rework/reinspection/disposition; HSE for machinery/ergonomics/fire/chemical/PPE/incident/waste/effluent/restricted substances.  
**Acceptance:** no default AQL; missing/unapproved plan blocks; failed audit blocks carton/FG release; independent reinspection preserves original fail; pack quantity <= accepted good; expired chemical or failed process control creates hold; CAPA closure requires verified effectiveness. **Owners:** quality, buyer quality, HSE/chemical compliance. **Dependency:** WP-10.

### WP-12 — Warehouse, distribution and channel commerce
**Finding:** v0.3 has some inventory transfers/POS/wholesale/returns, not full DC/branch/store/reseller/ecommerce/fulfilment/reverse logistics. Stand-in verifies FG receipt only.  
**Actions:** owner/custody/location/status model for raw/WIP/FG/returns; bins, put-away, replenishment, cycle count, pick/pack/ship, proof of delivery; central/regional DCs, depots, branches, stores, 3PL and reseller nodes; ATP/CTP; wholesale, retail POS, ecommerce, reseller policy, pricing/promotions, delivery, offline sync and reverse logistics/refund/credit.  
**Acceptance:** factory-to-DC-to-store/wholesale/D2C quantities and ownership reconcile; duplicate receiving/picking/dispatch/return does not duplicate stock or finance; ATP excludes quarantined/damaged/reserved stock; partial shipment, backorder and return disposition pass. **Owners:** WMS/distribution, retail/wholesale, logistics, finance. **Dependency:** WP-11.

### WP-13 — Finance, accounting, treasury and tax
**Finding:** basic GL/AP/AR exist in v0.3, but full manufacturing costing/WIP/FG valuation/tax/VAT/withholding/FX/customs/treasury/intercompany are absent.  
**Actions:** tenant/legal-entity COA and source-linked subledgers; four separate material movement ledgers; standard/actual cost rollup for material/labour/overhead/subcontract/utilities/freight/duty/yield/waste/rework/scrap; WIP/FG valuation and variance; budget/commitment, AP/AR/payment, bank/treasury/reconciliation, multi-currency/FX/intercompany; effective-dated Ethiopian rules from WP-02.  
**Acceptance:** balanced journal links source/tenant/entity/currency/rule version; material-ledger fixture reconciles with explicit unit and costing base; manufacturing order cost and FG valuation reconcile; reversal/period close retains history; unapproved statutory rule blocks posting. **Owners:** controller/cost accountant/tax/treasury/backend. **Dependency:** WP-12 and WP-02.

### WP-14 — HR, payroll, benefits and external compensation
**Finding:** employee/contract/attendance/basic payroll is partial; full lifecycle, skills/training, benefits/statutory rules and distinct compensation classifications are missing.  
**Actions:** workforce lifecycle, position/job grades, contract, attendance, leave, shift/overtime, pension/benefits, exit and restricted document access; skill/training links to WP-10; separate employee/line incentive/store commission/wholesale commission/reseller rebate/creator income/agent/supplier AP; effective-dated laws with qualified approval; grievance/appeal/safety/fair-work safeguards.  
**Acceptance:** qualified local payroll vectors; worker vs contractor/creator/supplier classification is explicit; incentives quality/safety-aware; HR/payroll fields secured across API/report/export; prior payroll not silently rewritten. **Owners:** HR/payroll/legal/finance/security. **Dependency:** WP-13 and WP-02.

### WP-15 — Analytics, integrations and intelligent operations
**Finding:** v0.3 has operational reports only; stand-in outbox dedupe is one local test, not canonical event/connector proof.  
**Actions:** KPI dictionary across initiative/product/sourcing/IE/production/quality/HSE/workforce/WMS/channel/finance; versioned REST/OpenAPI; connector registry/webhooks/EDI; tenant-scoped outbox/inbox with idempotency/retry/dead-letter/replay/reconciliation; machine/IoT and 3PL/supplier/buyer integrations; isolated Python optimization/AI returning recommendations; human authorization for consequential actions.  
**Acceptance:** event carries tenant/schema/source/time/idempotency and produces one business effect under retry; no cross-tenant event leak; KPI ties to ledgers; AI cannot post or approve consequential records without authorized human approval. **Owners:** integration/analytics/data/AI governance. **Dependency:** WP-14.

### WP-16 — End-to-end verification and release
**Finding:** both local runtimes explicitly omit canonical-stack proof, PostgreSQL RLS, real concurrency, canonical HTTP/UI/auth, broad costing/WMS/retail, security/performance/backup/restore and exact-SHA canonical CI. The Python stand-in's own exact-SHA reference verification has now passed, but that is not canonical WP-16 acceptance.  
**Actions:** run exact approved Angular 22/Laravel 13/PHP 8.5/PostgreSQL 18/RLS/Redis 8.2 stack; pin dependencies/images; CI exact SHA for static analysis, unit/integration/API/UI/accessibility, migration forward/rollback, RLS/tenant adversarial, concurrency, outbox/idempotency, security/dependency/secret/container scan and SBOM; full cross-domain scenarios incl. 19-stage replay and four subledgers; performance/load/security/penetration review; encrypted backup/restore/PITR/DR and migration rollback; role UAT; immutable build promotion staging→production; monitor/rollback plan.  
**Acceptance:** all required checks green at exact SHA; restricted DB roles prove RLS; concurrency and E2E ledgers reconcile; security/performance/recovery/UAT gates pass; staging deploy/rollback demonstrated; Product Owner separately approves production release. **Owners:** QA/release/security/platform/domain owners/Product Owner. **Dependency:** WP-00–WP-15 accepted in sequence.

## 4. Runtime control-to-WP acceptance map

| Reference control | WP | Canonical proof |
|---|---|---|
| Route order and route-specific stage 10 skip | 08/09 | API + persisted route transition tests |
| Technical release and approved SAM | 05/06/10 | Immutable released style snapshot and approved study |
| `UNVERIFIED_WP07` gate | 07 | BOM/material resolution cannot be bypassed |
| Maker-checker | 00/06/11/13 | Separate actor/role tests |
| Four separate append-only ledgers, single reversal | 07/13 | PostgreSQL subledgers and one-reversal constraint |
| Lot acceptance, stock, over-issue | 07/12 | Concurrency-aware lot/roll/reservation tests |
| Chemical SDS/expiry | 07/11 | Missing/expired SDS blocks issue/process |
| Cutting/bundle reconciliation | 09 | QR genealogy, split/merge/marriage tests |
| Idempotent scan/key reuse rejection | 09/15 | Same payload one effect; changed payload conflict |
| Independent reinspection / failed AQL blocks | 11 | Qualified independent inspector and hold gate |
| Pack <= accepted-good | 09/11/12 | Quantity constraint and carton release test |
| FG receipt exactly once/outbox dedupe | 12/13/15 | Concurrent retry with one inventory and event effect |
| Cross-tenant isolation | 03/16 | PostgreSQL RLS adversarial suite |
| SAM approval before planning | 06/10 | Unapproved method cannot drive capacity targets |

## 5. Handover and release stops

Every WP handover includes scope/requirement IDs, real workflow and exception paths, schemas/API/events, security scope, tests (normal/negative/partial/reversal/retry/concurrency as appropriate), updated operator/admin guidance, exact commit/artifact SHA, reproducible commands, limitations/risks and Product Owner review.

Do not replace canonical source, merge to `main`, activate statutory calculations, migrate live data or release to production while a prior WP is unaccepted; any legal or architecture decision is unapproved; tenant isolation/RLS fails; material/quality/cost/GL reconciliation fails; a retry duplicates an effect; a required CI check is skipped; exact-SHA build evidence is missing; or performance/security/backup/restore/UAT/rollback evidence is absent.

**Current status:** existing v0.3 archive SHA/manifest integrity PASS; Python stand-in source hashes 7/7 PASS and tests 16/16 PASS in GitHub Actions; canonical WP-00–WP-16 corrective implementation OPEN in approved sequence.
