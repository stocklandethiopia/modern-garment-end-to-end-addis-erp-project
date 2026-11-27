# Production Execution Plan — WP-00 to WP-16

**Project:** Modern Garment End-to-End Addis ERP  
**Purpose:** Single strict execution register for completing the approved customer-neutral, enterprise garment ERP and reproducible master installer.  
**Governing baseline:** Project Governance Agreement, AGENTS.md, approved product/domain specifications, platform standard, Ethiopia localization standard and WP-01 requirements package.  
**Package order:** WP-00 → WP-01 → WP-02 → WP-03 → WP-04 → WP-05 → WP-06 → WP-07 → WP-08 → WP-09 → WP-10 → WP-11 → WP-12 → WP-13 → WP-14 → WP-15 → WP-16.  
**Task-ID prefix:** PROD-WPnn-nnn. These are execution-plan identifiers; approved GAR/AT/requirement identifiers remain authoritative and must be cross-linked, not renamed or replaced.  
**Status rule:** Every task starts by reconciling current code, migrations, tests, branches and prior evidence. Existing work is reused and completed; it is never counted as accepted only because a file exists. The handover register remains the evidence-backed status source.

---

## 1. Production objective and boundary

Deliver one connected apparel operating system across business initiative, merchandising, product design and engineering, sourcing, IE, route/SOP management, departmental manufacturing, quality/HSE, warehouse, distribution, commerce, finance, HR/payroll, documents, integrations, analytics and governed agents. Deliver the reproducible master installer and operational lifecycle as a first-class product output.

The platform must remain customer-neutral and multi-tenant/multi-company. It must support factories, brands, legal entities, warehouses, DCs, branches, wholesale/reseller, owned retail and digital channels, local and international transactions, in-house and outsourced processes, different garment categories and configurable departmental routes.

This plan is bounded by the approved scope. It does not authorize a replacement architecture, unrelated products, sample-only implementations, customer-specific hard-coding, generic CRUD in place of business workflows, reduced domain coverage, guessed legal rules, weakened isolation or bypassed quality gates.

### 1.1 Governing technical standard

- Angular 22 and strict TypeScript for the enterprise web application.
- PHP 8.5 and Laravel 13 modular monolith for transactional ERP services and APIs.
- PostgreSQL 18 as transactional source of truth, including tenant RLS, database constraints and transactional invariants.
- Redis 8.2 for cache, queue coordination and ephemeral state.
- Python 3.15 as isolated specialist compute for optimization, forecasting, document intelligence and AI adapters; it does not own ERP transactions.
- Versioned REST/OpenAPI 3.1 contracts, typed domain commands/events, transactional outbox, request idempotency and immutable source lineage.
- Docker multi-stage images, pinned dependencies/digests, Compose v2 for reproducible development and immutable-image production delivery.
- Provider-neutral object storage through the approved S3-compatible contract.
- One authoritative code location and one canonical integration branch; no competing Node business API, parallel product architecture or untracked ZIP source.

### 1.2 Cross-package quality gates

Every task that creates or changes executable source must satisfy all applicable gates below. A gate may be marked NOT APPLICABLE only with a recorded technical reason.

**QG-01 — Requirement traceability.** Link task → approved requirement/GAR → domain owner → source path/API/UI/migration/event → automated acceptance scenario → handover evidence. No untraceable feature or silent requirement omission.

**QG-02 — Business workflow completeness.** Implement actors, authority, lifecycle/state transitions, required data, validations, exceptions, maker-checker, audit, operational queues and downstream hand-offs. A table or CRUD endpoint alone is insufficient.

**QG-03 — Tenancy and authorization.** Enforce tenant isolation in application and PostgreSQL, forced RLS, correct restricted runtime roles, legal-entity/site/domain scope, role/position/delegation authority, separation of duties and fail-closed defaults. Test same-tenant scope violations as well as cross-tenant access.

**QG-04 — Data and transaction integrity.** Use database constraints and transactions for business invariants; typed identifiers; canonical units/currency; immutable snapshots; concurrency/locking; append-only ledgers; effective-dated versions; payload-aware idempotency; deterministic replay and compensating events.

**QG-05 — Security and privacy.** Validate input and output boundaries, secrets, object access, device/service identity, audit evidence, least privilege, file scanning, retention and privacy. No production credentials or customer data in developer/test environments.

**QG-06 — Automated verification.** Add relevant unit, service, PostgreSQL migration/RLS, API/contract, integration, concurrency/idempotency, frontend and browser E2E tests. Run exact candidate SHA. Do not infer test passage from another branch or earlier commit.

**QG-07 — Accessibility and operator UX.** Deliver role-appropriate Angular workflows, clear state and exception messaging, accessible forms/navigation/keyboard behavior, safe confirmation for high-impact actions, responsive layouts and useful empty/error/loading states.

**QG-08 — Observability and audit.** Record actor/service identity, correlation, source version, reason, before/after state or hash, timestamps, decision evidence and traceable failures. Add metrics/logs/alerts and operational queues where applicable.

**QG-09 — Documentation and source map.** Update the application folder/code map, API/OpenAPI contracts, schemas, SOPs/runbooks, test commands and handover register in the same change as implementation.

**QG-10 — Exact-SHA delivery evidence.** Record branch, commit SHA, changed paths, migration result, required CI workflow/job results, test counts, artifact identity and remaining task IDs. A missing runner is NOT RUN, never PASS; use an alternate supported verification route without weakening the check.

**QG-11 — End-to-end business proof.** Every vertical slice is exercised from its authoritative upstream source through downstream transaction and reconciliation, including negative/exception cases. Package completion requires its full accepted scenario set, not just component tests.

**QG-12 — Operations and release.** Applicable tasks include build reproducibility, upgrade/rollback, health checks, monitoring, backup/restore, recovery, load/soak, security review, staging/UAT and approved deployment evidence.

### 1.3 Constraint and approval handling

1. Tool quotas, free-tier limits, runner allocation, session boundaries, local compute limits, missing optional workspace integrations and temporary network failures are delivery-continuity issues. They do not narrow requirements or alter the acceptance criteria.
2. Resolve such issues through the approved repository workflow and equivalent verification routes: another eligible runner, a local reproducible command, separate exact-SHA test, staged/isolated execution or supported tool. Keep the original quality gate.
3. If a particular check cannot run, preserve its status as NOT RUN with the precise cause and next executable verification task. Continue all independent work. Never invent a pass, test count, artifact, approval or deployment.
4. A genuine external professional/legal/customer sign-off, missing external credential or accountable high-impact approval blocks only the specific activation/transaction/deployment action that requires it. It does not stop design, implementation, migration authoring, tests, documentation, non-production verification or unrelated package tasks.
5. No requirement may be removed, weakened, replaced or indefinitely deferred because it is difficult. A dependency becomes a task with an ID, predecessor, resolution route, accountable role and acceptance evidence.
6. Do not use a planning document as a substitute for implementation. After each plan/task reconciliation, execute the next eligible engineering task in the fixed package order.

---

## 2. WP-00 — Governance, product baseline and delivery control

**Purpose:** preserve the approved product objective, boundary, architecture, requirements, task order and owner delegation as the single governing baseline.

- [ ] **PROD-WP00-001 — Baseline and repository reconciliation.** Fetch the live canonical delivery head, active work branches, open PRs, merged commits, source-package manifests and latest accepted handover. Reconcile existing code and parallel changes before implementation; record exact SHAs and duplicate/conflict resolution.
  - **Acceptance:** a reviewed branch/commit inventory identifies the canonical base, active package branch, open integration work, unapplied commits and source-of-truth paths. No duplicate implementation is started without reconciliation.
- [ ] **PROD-WP00-002 — Scope-to-task control.** Maintain this plan and map each PROD task to existing GAR, AT, R3, domain, legal, security and platform requirements. Record task dependencies, affected files, acceptance evidence and change-control references.
  - **Acceptance:** every approved requirement maps to one or more tasks and every task maps back to approved scope; no orphan requirement or scope-expanding task.
- [ ] **PROD-WP00-003 — Architecture and bounded-context enforcement.** Keep the approved modular-monolith boundary, domain ownership, API/event contracts, source-of-truth rules and specialist-service restrictions current. Record decisions without creating competing technology or service boundaries.
  - **Acceptance:** architecture/code review confirms data ownership, permitted dependency direction and API/event communication for each cross-domain integration.
- [ ] **PROD-WP00-004 — Decision, risk and change register.** Maintain explicit engineering decisions, risk owner, mitigation, exception path, expiration and change approval. Separate technical assumptions from approved policy and legal interpretation.
  - **Acceptance:** every material decision is linked to evidence and authority; scope changes use the established approval route.
- [ ] **PROD-WP00-005 — Evidence-led handover and autonomous progression.** Maintain task-level PASS/PARTIAL/BLOCKED/FAIL/NOT RUN/NOT APPLICABLE/PENDING OWNER APPROVAL, exact SHA, tests, evidence and next task. Complete self-review and internally approve in-scope package work on evidence without routine owner pauses.
  - **Acceptance:** handover register matches repository state; no task is promoted by narrative assertion; genuine human-only actions are isolated and independent work continues.

**Exit:** governance and task baseline are current; fixed WP sequence and delegated execution are followed; all change/evidence records are in GitHub.

---

## 3. WP-01 — Requirements inventory, traceability and acceptance model

**Purpose:** ensure every approved product, operational, security, localization and quality requirement has a testable implementation and evidence path.

- [ ] **PROD-WP01-001 — R3 and visible matrix reconciliation.** Reconcile the 128-record R3 package, 24 contexts, 45 source-map rows and package tests against the visible GAR requirement matrix and all accepted amendments. Resolve the documented 120-versus-128 and identifier delta without deleting valid records.
  - **Acceptance:** one authoritative reconciled requirement catalogue, duplicate/alias map, change history and checksum; every delta has a disposition and evidence.
- [ ] **PROD-WP01-002 — Requirement decomposition and ownership.** For every requirement, record domain, actor, accountable authority, preconditions, workflow/state changes, business data, outputs, exceptions, legal/security constraints, downstream consumers and acceptance scenario.
  - **Acceptance:** no requirement remains as an untestable title or generic statement; ownership and upstream/downstream relation are defined.
- [ ] **PROD-WP01-003 — Functional and non-functional requirement coverage.** Reconcile workflow behavior, concurrency, scale, availability, recovery, privacy, auditability, accessibility, usability, performance, localization, interoperability and data-retention obligations.
  - **Acceptance:** measurable NFR targets and the test/monitoring evidence required to prove each target are recorded in the approved NFR catalogue.
- [ ] **PROD-WP01-004 — Full source-to-test trace.** Map each requirement to backend service/command, API/OpenAPI operation, Angular route/component, database tables/constraints/RLS, events/jobs/integrations, unit/feature/E2E tests and operator documentation.
  - **Acceptance:** every requirement has exact repository paths and at least one positive and applicable negative acceptance scenario; missing paths become implementation tasks.
- [ ] **PROD-WP01-005 — Business scenario and exception catalogue.** Define standard, exception, rejection, retry, cancellation, reversal, hold, rework, expiry, concurrency and recovery journeys for every domain. Preserve actual apparel practice, not only happy-path CRUD.
  - **Acceptance:** named scenarios have preconditions, actors, input snapshots, expected state/ledger/event outputs and negative controls.
- [ ] **PROD-WP01-006 — Requirement verification and change trace.** Validate test results on exact SHAs, maintain requirement status history and record accepted product changes through formal approval.
  - **Acceptance:** WP-01 verification matrix is complete and consistent with the source package, domain specs, handover register and CI evidence.

**Exit:** the requirements catalogue is reconciled and fully testable; each other WP can be accepted against a known task and scenario set.

---

## 4. WP-02 — Ethiopia legal and regulatory localization

**Purpose:** deliver a configurable country pack and verified legal-source framework without guessing or activating unapproved statutory rules.

- [ ] **PROD-WP02-001 — Authority and source inventory.** Reconcile every relevant tax, VAT, withholding, payroll, employment, pension, customs, FX, commercial registration, procurement, privacy, HSE, environmental and product-compliance subject to the current official Gazette and competent authority.
  - **Acceptance:** each subject has authority, official source, instrument identifier, source URL/reference, publication/effective date and source status.
- [ ] **PROD-WP02-002 — Immutable source capture and legal provenance.** Retain source bytes or authoritative snapshots, checksum, retrieval/publication date, document identity, version, provision/article and reviewer annotation. Track supersession and amendments without overwriting historic sources.
  - **Acceptance:** adopted rule references resolve to immutable source evidence and reproducible hashes; missing/uncertain source remains held.
- [ ] **PROD-WP02-003 — Configurable effective-dated rule model.** Implement country/region/authority, rule identifier, applicability, calculation base, thresholds, rates, rounding, exemptions, effective-from/to, version, reviewer and approval records.
  - **Acceptance:** historic and current rule versions can be reproduced for the same transaction date; no rate or threshold is embedded as an unexplained literal.
- [ ] **PROD-WP02-004 — Ethiopia locale and calendar pack.** Implement Africa/Addis_Ababa time-zone handling, ETB and multi-currency support, Gregorian/Ethiopian date display/input as required, canonical timestamps, local numbering, addresses and English/Amharic resource handling.
  - **Acceptance:** date conversions, boundary times, DST/time-zone assumptions, calendar presentation, Unicode and number/document formats pass regression tests; stored canonical dates remain unambiguous.
- [ ] **PROD-WP02-005 — Employment, payroll and pension rules.** Model employee classification, taxable earnings, deductions, pension applicability, overtime/leave/severance and employer obligations only from verified current instruments and professional-reviewed test vectors.
  - **Acceptance:** rule calculators use pinned source versions and pass approved local professional scenarios; unresolved interpretation remains non-activated and cannot be silently guessed.
- [ ] **PROD-WP02-006 — Tax, invoice, withholding and finance rules.** Cover taxpayer/legal-entity category, VAT status, goods/services, domestic/import/export, withholding-agent role, transaction date, currency, tax base and official invoice/reporting process.
  - **Acceptance:** tax decisions and postings retain the exact legal basis and transaction facts; rate or exemption is not derived from generic supplier/customer labels.
- [ ] **PROD-WP02-007 — Customs, FX, licensing and compliance rules.** Implement source-linked HS classification, origin, customs value, duty/permit, FX controls, business licence and environmental/product compliance rule references.
  - **Acceptance:** trade/permit decisions are effective-dated, linked to verified documents and blocked when required authority evidence expires or is missing.
- [ ] **PROD-WP02-008 — Legal review, activation and change monitoring.** Provide a formal review queue, reviewer qualification, written decision, source/version link, test evidence, activation approval, suspension and change-impact workflow.
  - **Acceptance:** no statutory rule reaches live execution without the defined qualified review/approval; a legal hold blocks only affected statutory execution while unrelated engineering continues.

**Exit:** Ethiopia localization framework, source records, rule engine and approval/activation lifecycle are implemented and tested; professional sign-off remains explicit for each applicable rule.

---

## 5. WP-03 — Enterprise SaaS, tenancy, organization, identity and authorization

**Purpose:** provide a secure multi-tenant enterprise control plane for every downstream domain.

- [ ] **PROD-WP03-001 — Tenant and enterprise lifecycle.** Complete tenant creation, onboarding, setup, subscription/plan, feature entitlements, ACTIVE/SUSPENDED/CLOSED states, archive/export, retention, legal hold and controlled recovery.
  - **Acceptance:** tenant lifecycle is transactional, audited and tested for invalid transitions, expiry, isolation and recovery; suspended/closed tenants cannot execute prohibited business commands.
- [ ] **PROD-WP03-002 — Legal entities and operational organization.** Complete group/company/legal-entity, business site/factory/branch, warehouse/DC/store, department, cost centre, work centre and operating-calendar hierarchy with effective dates and cross-entity governance.
  - **Acceptance:** composite constraints and service checks prevent cross-tenant and invalid entity/site/department/location relationships; shared-site exceptions are explicit and approved.
- [ ] **PROD-WP03-003 — Identity, membership and invitation lifecycle.** Complete user identity, tenant membership, invitations, acceptance/expiry/revocation, recovery, deactivation and user-to-employee linkage without conflating login identity and employment record.
  - **Acceptance:** invitation replay, expiry, revoked membership, duplicate membership, offboarding and recovery scenarios are tested; no orphan privileged membership.
- [ ] **PROD-WP03-004 — Roles, positions, capabilities and segregation.** Complete tenant domain roles, position/incumbent model, capability grants, effective dates, temporary delegation, maker-checker, conflict-of-interest and role revocation.
  - **Acceptance:** authority resolves at action time against active identity, position, scope, effective period and delegation; self-approval is denied where prohibited.
- [ ] **PROD-WP03-005 — Scoped resource authorization.** Enforce target brand, site, legal entity, market, channel, account, store, warehouse and record scope on every read/write/action, including list filtering and aggregation.
  - **Acceptance:** SCOPED users cannot use an unscoped endpoint to access or mutate unauthorized same-tenant records; cross-tenant RLS tests run as app_runtime.
- [ ] **PROD-WP03-006 — Platform administration and operator controls.** Complete PLATFORM_ADMIN/PLATFORM_SUPPORT separation, time-limited grants, two-person approval where specified, restricted tenant register/read audit, purpose/reason evidence and emergency access.
  - **Acceptance:** platform authority cannot be obtained from tenant-owner role; grants expire/revoke and every privileged read/write is auditable.
- [ ] **PROD-WP03-007 — Authentication assurance.** Complete MFA, OIDC/SSO integration, session/device policy, password/recovery controls, token ability/scope, credential rotation and secure service principals.
  - **Acceptance:** account takeover, stale token, disabled user, unauthorized client, expired session and recovery abuse tests pass.
- [ ] **PROD-WP03-008 — Enterprise operator UI and tenant acceptance.** Complete accessible onboarding, member/invitation, legal entity/site, department/warehouse, organization hierarchy, job-position/reporting, role/scope, delegation, entitlement, audit and platform operator journeys. Implement server-side search, pagination and accurate total counts for every enterprise register and relationship list; preserve selected record, tenant context and effective-date filters across page changes.
  - **Acceptance:** operators can perform authorized lifecycle tasks end-to-end; restricted users see only permitted records and actionable denial reasons.

**Exit:** enterprise control plane is complete across backend, database, UI, audit, recovery and security acceptance; downstream WPs use it rather than inventing local authorization.

---

## 6. WP-04 — Initiative, market insight, merchandising and commercial planning

**Purpose:** connect business intent and demand evidence to product range, assortment, OTB, margin, pricing and channel decisions.

- [ ] **PROD-WP04-001 — Initiative and opportunity lifecycle.** Complete idea intake, sponsor, problem/opportunity, strategic fit, screening, evidence sufficiency, portfolio scoring, approval, park/reject/review and downstream brief creation.
  - **Acceptance:** every decision carries accountable actor, rationale, source evidence, expected benefit and review date; an unsupported idea cannot be represented as approved demand.
- [ ] **PROD-WP04-002 — Market and consumer insight.** Complete source-linked market/consumer/store/buyer research, geography/segment, recency, confidence, evidence rights, validation and feedback from sell-through/returns/fit/quality.
  - **Acceptance:** insight cannot be promoted to trusted evidence without source identity and recency; feedback links to product/range/market decisions.
- [ ] **PROD-WP04-003 — Brand, season and collection planning.** Complete brand architecture, season, category hierarchy, collection/capsule, target wearer/market/channel, concept, revision, approval, freeze and supersession.
  - **Acceptance:** immutable approved collection versions bind to their owner, brand and effective dates; overlapping/conflicting versions are rejected.
- [ ] **PROD-WP04-004 — Range plans and assortment.** Complete range line, style options, colourways, size curves, target quantities, store/account/channel/geographic assortment, revision, freeze, allocation assumptions and downstream PLM hand-off.
  - **Acceptance:** approved range and assortment remain consistent across brand, collection, market, account and channel; line freeze and changes are reasoned and audited.
- [ ] **PROD-WP04-005 — Demand forecast and consensus.** Complete typed grain definitions, dimension validation, manual/statistical/external provenance, model version/parameters, training/source window, import hash, scenario, adjustment and consensus approval.
  - **Acceptance:** every forecast row references valid tenant-scoped master data; published runs are immutable and reproducible; stale or unapproved model inputs cannot be published as verified forecast.
- [ ] **PROD-WP04-006 — Effective forecast and OTB.** Materialize approved adjustments into a versioned effective forecast; connect range, sales assumptions, stock, receipts, lead times, budget, cash and capacity into policy-driven OTB scenarios.
  - **Acceptance:** OTB pins compatible source versions and calculation formula, rejects wrong entity/market/channel/currency/period, validates assumptions and reproduces exact results.
- [ ] **PROD-WP04-007 — Pricing, margin and promotions.** Complete effective-dated price lists, account terms, currency/FX, cost basis, price waterfall, markdown, discount, promotion, eligibility, exclusions, stacking, margin floor and override approval.
  - **Acceptance:** fixed and percentage promotions cannot create negative price or violate approved floor; eligibility references are validated; cross-type overlap and stacking are tested; quotes/order lines retain price provenance.
- [ ] **PROD-WP04-008 — Merchandising dashboards and commercial E2E.** Complete KPI definitions and actuals from sales/returns/stock/cost; target vs actual, sell-through, WAPE, margin, markdown and OTB variance; link product launch to channel performance.
  - **Acceptance:** KPIs reconcile to authoritative downstream transactions; simulated data is explicitly labelled; complete initiative → range → PLM → sourcing/production → sale/return journey is tested.

**Exit:** merchandising can prove how and why a product is selected, forecast, funded, priced, allocated and measured, with exact source lineage and no disconnected assumptions.

---

## 7. WP-05 — Design studio, PLM, technical development, sampling, BOM and costing

**Purpose:** create the immutable product passport that controls sourcing, IE, routes and production.

- [ ] **PROD-WP05-001 — Product brief and style architecture.** Complete approved brief intake from WP-04, target consumer/use, material/fit/price/quality intent, style/revision, design ownership, design status and controlled change.
  - **Acceptance:** a style cannot progress without an approved source brief; each revision pins its source and downstream change impact.
- [ ] **PROD-WP05-002 — Creative assets and design rights.** Complete sketch/image/artwork/technical drawing, reference rights, licence scope, author/owner, usage territory/term, approvals and secure immutable object versions.
  - **Acceptance:** no unlicensed or expired asset can be released; storage bytes, metadata, rights and version hashes are verified.
- [ ] **PROD-WP05-003 — Technical design, measurement and sizing.** Complete construction details, size range, measurement points, graded values, tolerances, fit model basis, specification units and measurement approval.
  - **Acceptance:** measurement matrix is complete by size/point; tolerance/grade checks and fit-critical acceptance are executable and version-pinned.
- [ ] **PROD-WP05-004 — Pattern, grading and 2D/3D workflow.** Complete pattern blocks, pieces, grain/seam allowance, grade rules, CAD/2D/3D adapter contracts, simulation/review, sample state and digital evidence.
  - **Acceptance:** imports/exports have validated schema/provenance; approved pattern/grade versions reconcile to measurement and exact style revision.
- [ ] **PROD-WP05-005 — Sample-room and fit cycle.** Complete sample request, sample BOM/issue/return, assigned room/operator, planned/actual dates, construction/measurement/photo evidence, fit model, fit session, corrections and reinspection.
  - **Acceptance:** every sample is traceable to materials, pattern, measurement and style revision; failed fit cannot progress to final approval.
- [ ] **PROD-WP05-006 — BOM, consumption and substitutions.** Complete multi-level BOM by style/colour/size/operation, material identity, base UOM, quantity/waste/yield, approved alternative, sourcing resolution, expiry and independent quality approval.
  - **Acceptance:** WP-07 verifies every line against material master/supplier/site/effective date; unresolved/stale lines block release; substitutes require separate evidenced WP-11 approval.
- [ ] **PROD-WP05-007 — Technical pack and costing.** Complete construction, operation, route/quality requirements, trims/artwork, label/care/pack, packaging, standards, target/actual cost, FX, labour, overhead, freight, duty, margin and maker-checker approval.
  - **Acceptance:** technical pack release is driven by versioned, current dependency evidence from WP-06/07/08/11/13/15; no permanent placeholder blocker or false release-ready state.
- [ ] **PROD-WP05-008 — Engineering change and immutable product release.** Complete change request, impact analysis, affected orders/material/route/quality/cost, new revision, re-approval, release manifest, hash and supersession.
  - **Acceptance:** released product snapshots cannot be silently edited; production orders pin the exact approved product/BOM/asset/route inputs and historic reproducibility is tested.

**Exit:** the approved product passport and immutable release manifest can safely drive material planning, IE, route, quality and manufacturing.

---

## 8. WP-06 — Industrial engineering, methods, time study, SAM/SMV and capacity

**Purpose:** turn product operations into approved measurable methods, standards and feasible factory capacity.

- [ ] **PROD-WP06-001 — Operation library and method study.** Complete versioned operation families, workstation/machine/tooling, method sequence, work elements, safety/ergonomic review and evidence.
  - **Acceptance:** every route operation resolves to an applicable approved method or an explicitly permitted non-bulk prototype path.
- [ ] **PROD-WP06-002 — Time study, SAM/SMV and standard approval.** Capture observed cycles, rating, allowances, fatigue/personal/delay factors, standard time, sample size, assessor and calibration evidence.
  - **Acceptance:** calculation/formula version and raw study evidence are reproducible; no caller-supplied SAM is treated as approved without review.
- [ ] **PROD-WP06-003 — Work centres, machines, shifts and calendars.** Complete site/entity-owned centres, machine capability, shift schedules, breaks, downtime, working calendar and maintenance compatibility.
  - **Acceptance:** site/entity relationships and shift availability are database/service-enforced; inactive or out-of-service machine is unavailable for scheduling.
- [ ] **PROD-WP06-004 — Capacity and operation planning.** Complete finite/infinite capacity policy, labour minutes, machine minutes, bottleneck, planned load, line balance, changeover and constraint-aware slot allocation.
  - **Acceptance:** no operation quantity/minute allocation exceeds order or approved capacity; overlapping shifts/slots and effective-dated standards are tested.
- [ ] **PROD-WP06-005 — Workforce competence and assignment control.** Complete skill requirements, assessment/training evidence, operation/site/work-centre scope, expiry, planned/confirmed assignment and overlap control.
  - **Acceptance:** competence/scope/validity is rechecked under lock at confirmation and start; revoked/expired/overlapping qualification blocks assignment.
- [ ] **PROD-WP06-006 — IE dashboards, audit and UAT.** Complete SAM variance, earned minutes, capacity/utilization, method change, ergonomics, planner exception and factory study workflows.
  - **Acceptance:** standards, capacity and assignments are tested with real representative factory scenarios and independently reconciled; changes carry audit/evidence.

**Exit:** every bulk-production operation has a current approved method/standard, feasible capacity and qualified workforce path.

---

## 9. WP-07 — Supplier governance, sourcing, procurement and material traceability

**Purpose:** connect demand and BOM to qualified suppliers, authorized purchasing, inspected receipts, inventory and payables.

- [ ] **PROD-WP07-001 — Supplier identity and qualification.** Complete supplier master, legal/due-diligence, technical/quality, site/process/category scope, licences, certifications, HSE, financial and expiry/suspension lifecycle.
  - **Acceptance:** approval assertions link to verified document bytes and hashes; PO/award is blocked when the supplier is not qualified for the exact material/process/site/date.
- [ ] **PROD-WP07-002 — Requisition and sourcing event.** Complete requisition, budget check, RFQ/RFP, invited suppliers, bid window, quote submission/revision, supplier response and event close.
  - **Acceptance:** source event and supplier participation are auditable; unauthorized supplier or late quote cannot enter the evaluation.
- [ ] **PROD-WP07-003 — Quote evaluation and award.** Complete separate technical/commercial evaluation, comparable currency/UOM/incoterm/lead-time, conflict declaration, recommendation, approval, rejection and award record.
  - **Acceptance:** maker/buyer/evaluator/approver segregation and DoA thresholds are enforced; every award pins winning quote and evidence.
- [ ] **PROD-WP07-004 — PO and supplier communication.** Complete PO revision/version, schedule, change order, approval, dispatch evidence, supplier acknowledgement, resend/retry and cancellation/rejection distinction.
  - **Acceptance:** only the approved PO revision is sent; authenticated sender/transmission/acknowledgement evidence is recorded; rejection is not mislabelled cancellation.
- [ ] **PROD-WP07-005 — Goods, service and subcontract receipt.** Complete partial/multiple receipts, excess/shortage, discrepancy, inspection link, accepted/rejected/held quantity, service milestone acceptance and subcontract returned-output acceptance.
  - **Acceptance:** receipt header/line/PO state reconcile; no stock movement is duplicated; service/subcontract delivery can complete without creating false inventory lots.
- [ ] **PROD-WP07-006 — Material master, UOM and lot genealogy.** Complete material identity/specification, canonical UOM/base unit, approved conversion versions, supplier lot, dye lot, shade, width, GSM, roll, trim, chemical/SDS and expiry.
  - **Acceptance:** movements normalize to one compatible unit; every roll/trim/chemical lot links to PO/receipt/source evidence and its own separate ledger chain.
- [ ] **PROD-WP07-007 — Import, customs, landed cost and AP handoff.** Complete import file, HS/origin, customs value, duty/freight/insurance/FX, clearance/permits, landed-cost allocation, invoice and three-way match.
  - **Acceptance:** stock receipt is not duplicated by invoice posting; AP liability reconciles with PO, accepted receipt and invoice; statutory rules require verified sources.

**Exit:** a production BOM can be sourced and received with complete qualification, commercial decision, custody, inspection, UOM and finance lineage.

---

## 10. WP-08 — Executable routes, SOPs and work instructions

**Purpose:** supply a versioned, conditional, effective and executable route graph and department instructions.

- [ ] **PROD-WP08-001 — SOP and work-instruction catalogue.** Complete owner, revision, process family, department, task sequence, equipment, material, safety, quality characteristic, competence, acknowledgement and controlled release/supersession.
  - **Acceptance:** an operator sees only the instruction version effective for the exact route/operation/site/date; obsolete instructions cannot be acknowledged as current.
- [ ] **PROD-WP08-002 — Route graph model and templates.** Support required, optional, conditional, parallel, alternative, loop/rework and outsourced steps with typed predecessor/dependency rules.
  - **Acceptance:** route graph validation rejects cycles/invalid dependency, unreachable required steps and incomplete handoff; valid parallel/alternative flows are covered by tests.
- [ ] **PROD-WP08-003 — Condition evaluator and route simulation.** Evaluate style, BOM, material, customer, market, machine, skill, quality and site conditions against pinned input snapshots.
  - **Acceptance:** conditions are executed deterministically, explain why a step applies/does not apply, and cannot be overridden by arbitrary client input.
- [ ] **PROD-WP08-004 — Effective-dated route governance.** Complete draft, review, approval, effective-from/to, no-overlap, supersession, emergency deviation, rework version and historical pinning.
  - **Acceptance:** order route selection is valid on planned/execution date; later route changes never rewrite an existing order's route snapshot.
- [ ] **PROD-WP08-005 — Route instance and hand-off contract.** Instantiate approved route per order/lot/batch, resolve eligible queue, prerequisites, material/skill/equipment readiness, quality gates and handoff evidence.
  - **Acceptance:** downstream operation is enabled only by the latest effective release and completed prerequisites, not any historical positive decision.
- [ ] **PROD-WP08-006 — SOP/route UI, audit and acceptance.** Build route editor/simulator, review/approval, instruction library, deviation/rework and operator acknowledgement workflows.
  - **Acceptance:** conditional print/embroidery/wash/subcontract/pack variants and rework paths are exercised through API, browser and PostgreSQL tests.

**Exit:** a released route is an executable, versioned process graph, not a static ordered list.

---

## 11. WP-09 — Departmental manufacturing, material issue, cutting, WIP and genealogy

**Purpose:** execute each garment department with quantity, material, custody, quality and cost reconciliation.

- [ ] **PROD-WP09-001 — Production readiness and dispatch.** Consume the approved product/BOM, IE standard, route, material availability, quality plan, site capacity and qualified workforce to dispatch work.
  - **Acceptance:** orders cannot start with missing mandatory product, material, route, IE, skill or quality evidence; readiness reports actionable blockers.
- [ ] **PROD-WP09-002 — Fabric store, relaxing, spreading and lay.** Receive eligible roll, verify shade/width/GSM/defect/relaxation, build shade-grouped lay, marker/ply plan, spread tension and actual roll usage/remnant.
  - **Acceptance:** each lay traces to exact rolls/lot, marker and pattern revision; roll balance and lay consumption reconcile in base UOM.
- [ ] **PROD-WP09-003 — Cutting, panel and bundle genealogy.** Build cut order, marker/lay, cut-ticket, panel/size/color breakdown, bundle IDs, bundle split/merge and cut defect/scrap.
  - **Acceptance:** every panel/bundle traces back to roll/lot/lay/pattern/style revision; split/merge and scans preserve source genealogy and quantity.
- [ ] **PROD-WP09-004 — Separate trim/accessory issue and return.** Reserve/pick/issue trims by BOM line, style/order/size/colour and trim lot; record line custody, shortage, return, scrap and variance.
  - **Acceptance:** trim is never deducted from fabric balance; issue, return and accepted use reconcile independently by trim lot and UOM.
- [ ] **PROD-WP09-005 — Print, embroidery and embellishment.** Execute approved artwork/placement, strike-off/sample approval, recipe, machine/ink/thread/chemical lots, parameter setting and inspection/rework.
  - **Acceptance:** unapproved artwork/recipe or failed quality condition blocks the batch; output traces to exact artwork, input bundle and process settings.
- [ ] **PROD-WP09-006 — Sewing and operation bundles.** Dispatch cut bundles by route/operation/line/shift/worker/machine; record input, good, defect, rework, transfer, loss and handoff.
  - **Acceptance:** bundle WIP and operation quantities reconcile at every department transfer; only valid skilled assignments and current instructions can execute work.
- [ ] **PROD-WP09-007 — Wet processing, finishing and packing.** Support conditional wash/dye/chemical batches, recipe/HSE controls, finish/press/measurement, final repair/reinspection, carton assortment/labels/seal/weigh.
  - **Acceptance:** only routed styles enter wet processes; every carton reconciles to approved SKU/size/colour/order and finished-goods lots.
- [ ] **PROD-WP09-008 — Subcontract custody, WIP and device execution.** Dispatch/acknowledge outsourced operation, reconcile input/output/scrap/return, scan offline events, handle duplicate/out-of-order/revoked devices and maintain Andon/shift handover.
  - **Acceptance:** missing subcontract quantity raises a controlled case; offline replay is payload-aware; complete roll → lay → panel → bundle → operation → carton/finished-lot genealogy is proven.

**Exit:** every department has executable queue, role, SOP, material, quantity, quality, traceability, WIP and exception behavior.

---

## 12. WP-10 — Production planning, line performance, workforce and improvement

**Purpose:** translate demand and standards into feasible daily/hourly production and measurable improvement.

- [ ] **PROD-WP10-001 — Production and line planning.** Complete order/line/shift/operation scheduling with demand priority, due date, capacity, materials, skills, setup/changeover, subcontract and bottleneck constraints.
  - **Acceptance:** plan cannot over-allocate order quantity, worker time, machine/shift capacity or material; feasible/ infeasible scenarios are explainable.
- [ ] **PROD-WP10-002 — Target, SAM, earned-minute and efficiency calculation.** Calculate available labour minutes, target efficiency, line target, earned minutes, actual good output, rework and efficiency by order/operation/line/shift.
  - **Acceptance:** calculations pin approved standard and target versions; mixed-style line and rejected/reworked output do not double count.
- [ ] **PROD-WP10-003 — Hourly production and Andon.** Capture plan/actual, WIP, defects, downtime, shortage, quality hold, machine/skill/method/attendance constraints and forecast completion.
  - **Acceptance:** events produce auditable alert/action ownership and hour-to-order/operation reconciliation.
- [ ] **PROD-WP10-004 — Workforce deployment and competence.** Integrate WP-06 skills, schedule, assignment, attendance, training and site/operation authorization; revalidate at confirmation and start.
  - **Acceptance:** expired, revoked, out-of-scope or overlapping assignment cannot execute; workers are not blamed for verified upstream constraints.
- [ ] **PROD-WP10-005 — Maintenance and continuous improvement.** Integrate machine availability/preventive maintenance, daily huddle, 5S, Kaizen, suggestion, root-cause/countermeasure and effectiveness.
  - **Acceptance:** out-of-service machine cannot be capacity; improvement closure requires measured before/after result and effectiveness review.
- [ ] **PROD-WP10-006 — Incentive governance and factory UAT.** Build optional transparent, effective-dated incentive simulation and approval with accepted-output, quality, safety, fairness, wage and appeal controls; hand off approved result to WP-14/WP-13.
  - **Acceptance:** no unsafe/defective/skipped-QC output earns an incentive contrary to policy; factory scenarios reconcile hourly data, labour and quality.

**Exit:** daily factory plans and performance are measurable, explainable, safe and tied to actual accepted execution.

---

## 13. WP-11 — Quality, compliance, product safety, NCR/CAPA and HSE

**Purpose:** assure quality at source from supplier/material through operation, finished goods, shipment and customer feedback.

- [ ] **PROD-WP11-001 — Quality master, control and sampling plans.** Complete defect catalogue, specification/CTQ, control plan, AQL/customer sampling, method, sample size/frequency, acceptance criteria and reaction plan.
  - **Acceptance:** inspection standard and sampling plan IDs resolve to approved, current, versioned rules; inactive/expired rules cannot be used.
- [ ] **PROD-WP11-002 — Incoming and first-piece inspection.** Complete material/receipt/lot, first-piece, setup approval, inline/end-line/final/pre-shipment and customer-return inspection workflows.
  - **Acceptance:** inspector submission and independent review are separate legal states; accepted, failed, contained and deviation results are correctly represented in PostgreSQL.
- [ ] **PROD-WP11-003 — Defect, containment and disposition.** Complete defect origin/severity/catalogue, containment quantity/location, authority/waiver, use-as-is, rework, repair, sort, return, scrap and customer concession.
  - **Acceptance:** caller booleans cannot assert authority decisions; exact subject/lot/bundle/order/operation/quantity and disposition are bound and tested.
- [ ] **PROD-WP11-004 — NCR, root cause and CAPA.** Complete nonconformance lifecycle, evidence, accountable action, due dates, implementation, independent effectiveness check, ineffectiveness/reopen and closure.
  - **Acceptance:** multi-write transitions are transactional, versioned and audited; open NCR/CAPA blocks affected release.
- [ ] **PROD-WP11-005 — Quality release and deviation.** Complete current-effective release state by subject/stage, HOLD/REJECT/REINSPECT supersession, deviations/concessions, expiry, customer approval and conditional release.
  - **Acceptance:** historical release cannot override later hold; release actor is separated from inspector/reviewer; each gate verifies current linked evidence.
- [ ] **PROD-WP11-006 — HSE and environmental compliance.** Complete risk assessment, hazard/incident/near miss, PPE/training, stop-work, permit, chemical/SDS/compatibility, wastewater/effluent and hazardous-waste manifest.
  - **Acceptance:** high-risk unsafe conditions prevent affected work; permits and chemical controls are effective-dated and source-linked.
- [ ] **PROD-WP11-007 — Quality analytics and end-to-end acceptance.** Complete defect rate/PPM, first-pass yield, rework/scrap, supplier quality, process capability, audit finding and customer complaint analysis.
  - **Acceptance:** KPIs derive from authoritative inspection/production/return facts; full incoming → operation → final → shipment → return journey is tested.

**Exit:** quality gates are authoritative, current, independent, auditable and enforced by all stock/production/shipment transitions.

---

## 14. WP-12 — Warehouse, distribution, orders, sales channels and reverse logistics

**Purpose:** control stock and customer fulfilment across warehouses, DCs, branches, wholesale, retail, reseller and digital channels.

- [ ] **PROD-WP12-001 — Network and inventory location model.** Complete legal-entity/site/node/warehouse/zone/bin, raw/WIP/finished/quarantine/consignment/in-transit states and location capability.
  - **Acceptance:** all stock moves resolve to an authorized source/destination and entity/site; cross-node custody remains explicit.
- [ ] **PROD-WP12-002 — Inventory, handling units and ATP.** Complete base UOM, roll/trim/bundle/pallet/carton/serial handling units, available-to-promise, stock status, holds, ageing, reservations and cycle counts.
  - **Acceptance:** ATP excludes held/unlocated/quality-blocked and already reserved stock; quantities reconcile to immutable ledger and genealogy.
- [ ] **PROD-WP12-003 — Replenishment and transfers.** Complete min/max, demand-driven replenishment, transfer request/approval, dispatch, in-transit, receipt, variance and destination reconciliation.
  - **Acceptance:** in-transit remains distinct until destination receipt; duplicate transfer/scan cannot move stock twice.
- [ ] **PROD-WP12-004 — Customer/account and order management.** Complete buyer, wholesale, reseller, owned retail and D2C account terms, customer credit, sales order, line, confirmation, allocation and change control.
  - **Acceptance:** account, legal entity, product, price, tax, credit and inventory decisions are server-resolved and snapshotted; order header/lines are atomic and idempotent.
- [ ] **PROD-WP12-005 — Price, payment and channel integration.** Consume WP-04 price/terms/promotions, WP-02 tax, payment provider intent/webhook and approved channel policies for POS/e-commerce/marketplace.
  - **Acceptance:** duplicated or conflicting external webhook is handled safely; no payment/stock/order double posting; no generic payroll classification for non-employee payments.
- [ ] **PROD-WP12-006 — Pick, pack, ship and carrier.** Complete allocation, wave/pick, cartonization, pack verification, label/manifest, independent release/gate-out, carrier tender, tracking and dispatch event.
  - **Acceptance:** packing/release/dispatch are separate accountable states; shipment, reservation, stock and order quantities reconcile on one transaction boundary.
- [ ] **PROD-WP12-007 — Delivery, proof and customer acceptance.** Complete in-transit milestones, proof of delivery, recipient acknowledgement, partial/failed/lost delivery, exception, claim and commercial fulfilment state.
  - **Acceptance:** dispatched is not mislabeled delivered/fulfilled; invoice/AR handoff follows approved delivery/accounting terms.
- [ ] **PROD-WP12-008 — Returns, exchanges and reverse logistics.** Complete RMA, authorization, receipt, inspection, quarantine, restock/rework/repair/scrap, replacement, refund/credit note and customer feedback.
  - **Acceptance:** every return links to original shipment/order/lot; original dispatch is not edited; inventory and finance use compensating transactions and reconcile.

**Exit:** complete order-to-cash and return journeys operate across each approved channel and network node.

---

## 15. WP-13 — Finance, accounting, AP/AR, treasury, costing and tax

**Purpose:** make financial records the reconciled consequence of operational business events, with immutable approval and posting.

- [ ] **PROD-WP13-001 — Legal-entity finance foundation.** Complete chart of accounts, fiscal calendar/periods, dimension policy, base currency, consolidation and account hierarchy by legal entity.
  - **Acceptance:** database-level relationships prohibit invalid cross-entity journals and parent accounts; period open/close/lock/reopen is controlled and tested.
- [ ] **PROD-WP13-002 — Journal and GL.** Complete source-derived journal creation, line validation, debit/credit balance, account/dimension/currency checks, independent approval, posting, immutable posting number and audit.
  - **Acceptance:** approved journal lines cannot mutate; posting is atomic/idempotent, balanced and period-authorized; source event cannot post twice.
- [ ] **PROD-WP13-003 — FX and currency.** Complete approved effective-dated FX source/rate, quote direction, timestamp, precision, rounding, realized/unrealized gain/loss and override approval.
  - **Acceptance:** base amounts are calculated server-side; client-supplied conversion values cannot bypass approved rate or account currency policy.
- [ ] **PROD-WP13-004 — Accounts payable and procurement match.** Complete supplier invoice, goods/service receipt match, PO tolerance, tax/withholding, liability, payment proposal, remittance and settlement.
  - **Acceptance:** 2/3-way match exceptions route to approval; invoice cannot create stock receipt; service/subcontract amounts are not treated as inventory.
- [ ] **PROD-WP13-005 — Accounts receivable and customer credit.** Complete customer invoice, credit note, receipts, collections, ageing, deposits, exposure, write-off/approval and reconciliation with WP-12 orders/delivery.
  - **Acceptance:** credit exposure uses authoritative posted/unbilled amounts and current FX; dispatch/invoice/receipt are distinct and idempotent.
- [ ] **PROD-WP13-006 — Inventory, manufacturing cost and profitability.** Complete raw material valuation, landed cost, WIP, standard/actual labour, overhead absorption, scrap/rework variance, finished goods, COGS and margin.
  - **Acceptance:** cost flows reconcile BOM/issue/consumption/return/production output and legal entity/currency/period; cost snapshots are reproducible.
- [ ] **PROD-WP13-007 — Treasury, bank, cash and close.** Complete cash book, bank statement import, reconciliation, payment control, FX exposure, liquidity, trial balance, subledger/GL reconciliation, period close and audit pack.
  - **Acceptance:** payment execution remains within approved provider/authority gate; no unreconciled subledger control account is silently closed.
- [ ] **PROD-WP13-008 — Tax, fixed assets, journals and reversal controls.** Complete VAT/withholding/tax invoice, filing reports, fixed assets/depreciation, controlled full/partial reversal and source event linkage.
  - **Acceptance:** tax output uses WP-02 approved law version; reversal is separately requested/approved/posted and tied to original immutable posting; complete AP→GL and AR→GL reconciliation is tested.

**Exit:** operational subledgers and GL reconcile by legal entity, currency, period and source; all financial actions retain maker-checker, authority and reproducible source evidence.

---

## 16. WP-14 — People, HR, time, leave, skills, compensation and Ethiopian payroll

**Purpose:** provide a complete employment/workforce lifecycle and legally verified payroll without confusing employee compensation with supplier, creator or reseller payments.

- [ ] **PROD-WP14-001 — People and employment records.** Complete person/worker, employee identity, legal employer, contract, job/position, department/site, work location, classification, effective dates and lifecycle.
  - **Acceptance:** employment state, legal entity and worker identity are authoritative and separate from login account, supplier, subcontractor and reseller.
- [ ] **PROD-WP14-002 — Time, attendance, roster and overtime.** Complete shift/roster, punch/device event, attendance exception, overtime request/approval, time sheet, absence and reconciliation to planned work.
  - **Acceptance:** device identity and event replay are verified; time/attendance adjustments are approved and audit-bound; payroll period facts are reproducible.
- [ ] **PROD-WP14-003 — Leave, benefits and employee services.** Complete leave types/accrual/balance/approval, holiday/calendar, benefits, eligibility, enrolment, claims and end-of-service events.
  - **Acceptance:** rules are effective-dated and source-linked; leave/time/payroll calculations use the same authoritative employee and period.
- [ ] **PROD-WP14-004 — Skills, learning and qualification.** Complete training plans, attendance, assessment, certificate/evidence, expiry, retraining, performance and operation authorization handoff to WP-06/WP-10.
  - **Acceptance:** qualification is scoped to actual operation/site/machine/process; expired/revoked skill blocks restricted assignment.
- [ ] **PROD-WP14-005 — Compensation and payroll components.** Complete salary structure, allowances, earning/deduction, benefits, taxable/non-taxable classification, effective-dated contract changes and payroll period snapshot.
  - **Acceptance:** each element is classified and linked to law/contract/policy; non-employee supplier/creator/reseller payouts are excluded from payroll unless employment classification is explicitly established.
- [ ] **PROD-WP14-006 — Ethiopia statutory payroll calculator.** Implement gross-to-net, income tax, pension, employer contributions, overtime/leave/severance and other applicable rules only from WP-02 verified sources and approved test vectors.
  - **Acceptance:** exact rule/source/employee/period/input/formula/output hashes reproduce; no guessed rate or unreviewed rule can become active.
- [ ] **PROD-WP14-007 — Payroll run, controls and professional review.** Complete employee eligibility, time/input validation, calculator run, line status, control totals by currency/component, variance, hold/reject, independent professional review and approval.
  - **Acceptance:** no DRAFT/HELD/invalid line can pass run approval; source evidence and calculator version are pinned; preparer/reviewer/approver duties are separated.
- [ ] **PROD-WP14-008 — Payroll accounting and remittance handoff.** Complete approved payroll-to-GL liability, statutory remittance file, payslip/report, payment instruction preparation, reconciliation and correction/reversal process.
  - **Acceptance:** payroll/GL/statutory totals reconcile; real payment/remittance execution requires its designated accountable approval; test and staging runs are clearly separated from real execution.

**Exit:** the complete people-to-payroll lifecycle is implemented with current law/source evidence, professional review, financial reconciliation and correct worker/payment classification.

---

## 17. WP-15 — Documents, integrations, devices, analytics and governed agents

**Purpose:** supply secure evidence storage and durable event execution for all domains.

- [ ] **PROD-WP15-001 — Document and asset store.** Complete provider-neutral private object storage, metadata, tenant/entity/site access, immutable object version, checksum/byte verification, retention, legal hold, deletion policy and rights.
  - **Acceptance:** stored bytes are read back and hashed before verification; object replacement/deletion is detected; failed metadata write produces safe compensation/orphan reconciliation.
- [ ] **PROD-WP15-002 — File security and controlled retrieval.** Complete type/content validation, malware scan, quarantine, safe preview/download, encryption, access audit and retention/deletion workflow.
  - **Acceptance:** unverified or unsafe object is never available to ordinary users or downstream import; unauthorized tenant/site retrieval is denied and audited.
- [ ] **PROD-WP15-003 — Extraction and document intelligence.** Complete queue/worker identity, job lease/retry, provider/model/version, input/output hash, confidence/field schema, source-page/region evidence and stale-result rejection.
  - **Acceptance:** extracted values are proposals only until a human-authorized domain import command succeeds; exact input/output/model is reproducible.
- [ ] **PROD-WP15-004 — Domain import and evidence lineage.** Complete typed mapping from approved extraction to supplier, material, PO, invoice, employee, quality or product domain; review, correction, import replay and source linkage.
  - **Acceptance:** approval alone never implies import; one source document maps to explicit resulting records and a duplicate import does not duplicate transactions.
- [ ] **PROD-WP15-005 — Integration endpoint and adapter framework.** Complete tenant/entity scope, provider, secret-vault reference, event allow-list, schema/version, auth/signature, rate limit, timeout, activation/suspension and credential rotation.
  - **Acceptance:** outbox cannot deliver to inactive/unapproved endpoint or unauthorized event; secrets never appear in logs or source.
- [ ] **PROD-WP15-006 — Outbox/inbox and worker runtime.** Complete transactional enqueue, claim lease, worker identity/fencing, lease expiry/recovery, payload hash, retries/backoff/dead letter, inbox/webhook signature/replay protection and operator replay.
  - **Acceptance:** timeout-after-remote-success, worker crash, expired lease, duplicate/conflicting payload and dead-letter replay are deterministic and tested.
- [ ] **PROD-WP15-007 — Device identity and offline scan.** Complete registered device, tenant/site/work-centre binding, device credential, time/sequence validation, signed raw envelope, offline queue, scan resolution and explicit domain command.
  - **Acceptance:** device reference is not authentication; revoked device/clock skew/replay/conflicting scan are rejected; resolution by itself never mutates stock or production.
- [ ] **PROD-WP15-008 — Governed agents and analytics.** Complete approved capability registry, risk tier, proposal/precondition snapshot, human approval, revision/request-changes, runtime identity, execution, token/tool trace, finalization and rollback/compensation. Deliver cross-domain analytics from authoritative data.
  - **Acceptance:** agent cannot exceed its allow-list, stale preconditions block execution, REQUEST_CHANGES is accurately represented, run ownership is enforced and recommendation is not confused with approval or transaction.

**Exit:** document, integration, device, analytics and agent workflows are secure, durable, observable, replay-safe and human-governed.

---

## 18. WP-16 — System quality, master installer, deployment and release qualification

**Purpose:** produce the reproducible installable and operable enterprise system from immutable source to recoverable deployment.

- [ ] **PROD-WP16-001 — Canonical clean-source and dependency baseline.** Reconcile path map, lockfiles, manifests, pinned runtime versions, migration order, generated artifacts, license/security inventory and branch protections.
  - **Acceptance:** fresh checkout reproduces the same dependency graph and build inputs; no untracked artifact, secret or ambiguous source branch is required.
- [ ] **PROD-WP16-002 — Container build system.** Build pinned multi-stage API, Angular web, queue worker, scheduler and Python specialist images; non-root runtime, minimal layers, health checks, graceful shutdown, resource limits and SBOM.
  - **Acceptance:** images build from immutable commit, pass vulnerability/license review and run with no embedded secrets or development tooling.
- [ ] **PROD-WP16-003 — Master installer and orchestration.** Deliver the approved install entrypoint and production topology for database, cache/queue, API, web, workers, scheduler, storage adapter, observability and specialist compute.
  - **Acceptance:** clean install provisions configured services with preflight validation, safe secret handling, migration/seed orchestration, health/readiness check and clear operator output.
- [ ] **PROD-WP16-004 — Database lifecycle and upgrade.** Complete ordered migrations, production-safe expand/contract, tenant bootstrap, authorized reference seed, migration lock, version compatibility and rollback/forward-fix rules.
  - **Acceptance:** clean-install, upgrade from each supported version, interrupted migration recovery and no destructive downgrade are tested on disposable environments.
- [ ] **PROD-WP16-005 — Environment and secret management.** Complete typed environment schema, secret references, external secret injection, rotation, least-privilege DB roles, endpoint policy and production/development config separation.
  - **Acceptance:** missing/invalid secret fails safely, credentials never enter image/build logs/repo, and no hard-coded loopback/host-specific production endpoint exists.
- [ ] **PROD-WP16-006 — CI/CD and immutable artifacts.** Complete static checks, backend/frontend/Python tests, migrations/RLS, API contracts, integration/E2E, security/SBOM, image signing/provenance and release artifact retention.
  - **Acceptance:** every artifact identifies exact source SHA, dependency lock and digest; all mandatory checks are green on the same candidate before promotion.
- [ ] **PROD-WP16-007 — Security, privacy and accessibility qualification.** Complete threat model, penetration/security review, dependency/container scans, RLS/privileged-role verification, OWASP controls, privacy/retention, audit protection and WCAG 2.2 AA checks.
  - **Acceptance:** all critical/high findings are resolved or have an authorized documented disposition; no tenant bypass, secret leak or inaccessible critical workflow remains.
- [ ] **PROD-WP16-008 — Performance and resilience.** Complete realistic concurrency, load/soak, queue backlog, retry storms, DB contention, failover, dependency outage, rate limits, worker loss and recovery behavior.
  - **Acceptance:** approved capacity/SLO targets are met under recorded workload; failure modes preserve transaction integrity and recover without silent data loss.
- [ ] **PROD-WP16-009 — Backup, restore, DR and operational readiness.** Complete encrypted backup, retention, restore drill, point-in-time recovery, RPO/RTO evidence, rollback, incident runbook, monitoring, alerting, operator training and support ownership.
  - **Acceptance:** restore is demonstrated into a clean environment and reconciled to source transaction hashes; runbooks are actionable and tested.
- [ ] **PROD-WP16-010 — Factory/customer UAT and accountable production promotion.** Complete E2E-001–E2E-008 and all applicable WP acceptance, staging/UAT, legal/professional/customer sign-offs, immutable artifact verification, cutover/recovery rehearsal and deployment approval.
  - **Acceptance:** exact release SHA, image digest, migration state, UAT, security, performance, DR and accountable approvals are recorded. Live production deployment occurs only through the approved authorization; this gate does not stop non-production engineering.

**Exit:** reproducible installer, immutable artifacts, complete application process topology, clean install/upgrade/recovery and full production qualification evidence exist.

---

## 19. Required end-to-end acceptance journeys

The following cross-package journeys are mandatory. They are not substitutes for each WP's own acceptance scenarios.

1. **E2E-001 — Initiative to approved product:** initiative/source evidence → brand/collection/range/assortment → forecast/OTB/price → approved brief → design/pattern/measurement/sample/fit → BOM/cost/technical-pack release.
2. **E2E-002 — Product to procurement and receipt:** approved BOM → sourcing demand → qualified supplier/RFQ/quote/award → PO → supplier dispatch → receipt → incoming inspection → accepted material lot/roll/trim → AP match.
3. **E2E-003 — Factory readiness and planning:** released product/BOM → approved IE standard/SAM → effective route/SOP → site/work-centre/shift capacity → qualified worker → production plan/order readiness.
4. **E2E-004 — Material to cut bundle:** material reservation/picking → roll issue → relax/spread/lay/marker → cut panels → bundle creation/split/merge → roll remnant, defect and scrap reconciliation.
5. **E2E-005 — Departmental production:** bundle dispatch → print/embroidery (where routed) → trim issue → sewing operations → wash/dye (where routed) → finishing/press → quality/rework → pack/carton genealogy.
6. **E2E-006 — Quality and finished goods:** incoming/first-piece/inline/final inspection → independent review → NCR/CAPA/deviation handling → current effective release → finished-goods quarantine/lot → release to stock.
7. **E2E-007 — Order to delivery and return:** customer/channel order → authoritative price/tax/credit → ATP/reservation → pick/pack/release/dispatch → tracking/POD → invoice/AR/collection → RMA/return/inspection/refund or restock.
8. **E2E-008 — Finance, people and close:** attendance/leave/approved payroll → professional review → payroll-to-GL; procurement/AP, sales/AR, inventory/WIP/COGS, bank and tax → subledger reconciliation → trial balance and period close.

Each journey must include the positive path, at least one authorization denial, invalid/stale evidence, duplicate and conflicting replay, concurrency where relevant, rollback/compensation and full source genealogy.

---

## 20. Fixed execution and promotion order

1. Reconcile WP-00/01 baseline and the exact canonical head.
2. Complete the active WP-03 enterprise/security control-plane tasks already authorized; do not restart completed source slices. Close the remaining scope, target-access, operator UI, lifecycle, MFA/SSO and recovery tasks with tests.
3. Complete WP-01 traceability reconciliation and WP-02 legal-source/rule framework in parallel only where the established handover and dependencies permit; neither can be marked complete from a package design alone.
4. Complete WP-04 and WP-05 as the product-commercial-to-product-passport vertical slice.
5. Complete WP-06 and WP-07 inputs, then WP-08 executable routes.
6. Complete WP-09 and WP-10 departmental execution and planning with material/WIP genealogy.
7. Complete WP-11 and WP-12 release, warehouse, order, delivery and reverse flows.
8. Complete WP-13 and WP-14 financial/people/payroll integrations.
9. Complete WP-15 evidence/integration/device/agent runtime and cross-domain observability.
10. Complete WP-16 installer, full E2E, security/performance/resilience/DR/UAT and accountable release promotion.

Dependencies may be developed in parallel on separate work branches when this fixed package sequence and package acceptance gates are preserved. Integration promotion is still sequential and based on the canonical branch.

---

## 21. Mandatory branch, commit and handover workflow

- Fetch the latest canonical delivery branch and reconcile PRs/branches before work.
- Create or continue the dedicated work/wpNN-* branch specified by AGENTS.md for the active WP. Do not commit working changes directly to delivery or main.
- Make the smallest coherent task increment, update the application folder/code map and affected specifications, add tests and run exact-head checks.
- Open an integration PR only when the task ID, source path map, tests and appropriate security review are complete.
- Merge only after all mandatory checks pass on the exact candidate. A runner-allocation issue is handled through a valid alternate route, not by waiving the check.
- Update handover register with task IDs, statuses, counts, evidence, commits, workflows, remaining dependencies and next executable task.
- Keep main unchanged until final WP-16 production promotion has its exact release evidence and accountable authorization.

### 21.1 Handover record required for every task

| Field | Required record |
|---|---|
| Task identity | PROD task ID and mapped approved GAR/AT/requirement IDs |
| Scope | Exact bounded workflow and actors |
| Source | Branch, commit SHA, changed paths, migration/API/UI/event paths |
| Data | New/changed tables, constraints, RLS, indexes, seeds and migration evidence |
| Controls | Authorization, separation, legal rule, audit and idempotency behavior |
| Tests | Exact command/workflow, job result, count and SHA |
| Acceptance | Positive/negative scenario evidence and cross-domain link |
| Status | PASS, PARTIAL, BLOCKED, FAIL, NOT RUN, NOT APPLICABLE or PENDING OWNER APPROVAL |
| Remaining | Concrete next task and dependency, never a vague limitation |

### 21.2 Task status semantics

- **PASS:** implementation and all applicable acceptance evidence passed on the recorded exact SHA.
- **PARTIAL:** some bounded outputs exist, but the task's full definition of done is not met.
- **BLOCKED:** a specific required dependency/authority/permission is unavailable; identify exactly one affected action and the safe work that continues.
- **FAIL:** a required check or acceptance scenario executed and failed; record the defect and repair task.
- **NOT RUN:** required test or verification did not execute; give precise cause and next command/route.
- **NOT APPLICABLE:** justified by the task's technical scope and reviewed, not used to avoid implementation.
- **PENDING OWNER APPROVAL:** only for a genuine action reserved to the accountable Product Owner or other named authority.

A task cannot be called complete by a green unrelated workflow, a different source SHA, a code file existing, a generated ZIP, a design document or a claim that a tool limitation prevented execution.

---

## 22. Explicit out-of-boundary exclusions

The following are not authorized by this plan and must not be introduced as shortcuts or unrelated work:

- Replacing the approved modular monolith or introducing a second competing transactional API.
- Hard-coding one customer, company, factory, user, supplier, payroll identity or production environment into the product.
- Importing legacy ERP source, WordPress assumptions, customer identifiers, credentials or database design into the greenfield platform.
- Treating a reference document, spreadsheet, user-entered value, simulation or AI-generated output as verified business/legal evidence without the required source/approval.
- Combining material, trim, roll, bundle, WIP, finished-goods, carton or financial ledgers into one untraceable quantity/state.
- Weakening RLS, authorization, separation of duties, financial integrity, safety, statutory review, privacy or immutable audit to make a test pass.
- Executing live statutory activation, actual payroll/payment/bank transaction, destructive production-data action or production deployment without the specific accountable authorization.
- Using these exclusions as reasons to stop independent safe engineering, tests, staging preparation, installer construction or other in-scope work.

**Controlling decision:** execute every approved task in this plan and all mapped WP-00–WP-16 requirements. If a delivery route fails, replace the route—not the requirement or quality standard.
