# WP-03 / WP-14 Integrated Build Plan — Organogram, Delegated Authority and Ethiopia Payroll

**Project:** Modern Garment End-to-End Addis ERP  
**Plan ID:** WP03-WP14-ORG-PAY-001  
**Date:** 27 November 2026  
**Status:** Planned implementation baseline; no package completion is implied  
**Sequence:** WP-03 organogram/position authority foundation first; WP-14 employee/payroll integration follows; WP-15 agent integration consumes the governed APIs; WP-16 proves the end-to-end release gates.  
**Repository:** `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`

## 1. Purpose and strict-plan placement

This plan converts two current source/design workstreams into buildable, testable work-package increments:

1. **WP-03 — Enterprise organogram, positions, reporting lines and Delegation of Authority (DoA).** This is an enterprise identity and authorization foundation. It is not merely an HR chart. It establishes the legally configured company structure, positions, effective-dated assignments, reporting relationships and approval authority consumed by Procurement, Finance, HR, Payroll, Inventory, Quality, Manufacturing, Distribution and all other business domains.
2. **WP-14 — Ethiopia employee lifecycle, statutory payroll and compensation.** This consumes WP-03 legal-entity, position, employee-assignment and delegated-approval services. It adds the complete employee master and employment lifecycle, attendance-to-payable-time controls, server-side payroll, legal-source snapshots, statutory evidence, professional approval gates and accounting/remittance reconciliation.

The package order remains WP-00 through WP-16. This plan does not merge or reorder packages. WP-03 is already the active execution checkpoint in the handover register. WP-14 remains a later package; safe design and isolated source analysis may proceed, but its production activation cannot bypass WP-03 dependencies or WP-02 legal holds.

### Source artifacts to reconcile into the canonical branch

- Organogram / DoA design: `docs/organization/enterprise-organogram-and-delegated-authority.md` (currently on the separate WP-03 design branch / PR #16).
- Ethiopia payroll legal localization: `docs/localization/ethiopia-payroll-localization-2026-11-27.md` (currently on the separate WP-14 source branch).
- Payroll engineering gate: `docs/localization/ethiopia-payroll-engineering-release-gate.md` (currently on the separate WP-14 source branch).
- WP-14 workforce/payroll design: `docs/hr/workforce-payroll-pension-benefits-compensation.md`.
- Legal register and WP-02 controls: `docs/localization/ethiopia-legal-regulatory-register.md`.
- Current executable baseline and path ownership: `docs/architecture/application-folder-code-map.md`.

The existing organogram and payroll design branches are source evidence, not merged application releases. Reconcile their commits, CI and open legal/technical gaps before integration; do not silently assume a PR has merged.

## 2. Cross-package architecture decisions

### 2.1 One company organization model; separate identities and authorities

Model these separately and link them by effective-dated records:

- Tenant / SaaS account.
- Legal entity and registration/tax identity.
- Business site (factory, head office, DC, depot, branch/store).
- Organizational unit (division, department, section, team, cost centre).
- Position / job slot (stable position identity, title, grade, job family, authority profile).
- Person.
- Employee and employment contract.
- Position appointment / employee assignment.
- Login identity and tenant membership.
- Tenant domain role, platform role and scoped permission.
- Business accountability metadata (Business Owner, Process Owner, Procurement Owner, Platform Owner).
- Delegated transaction authority.

A tenant `OWNER` role is a SaaS tenant governance permission. It is not a company director, executive, procurement approver or payroll approver. A job title or accountability label alone grants no permission. Company transaction authority is derived from a current, approved DoA rule plus an authenticated actor's valid position appointment, legal-entity and target scope, amount/limit, budget and separation-of-duties checks.

### 2.2 Configurable company governance, not a fixed sample chart

Support, where applicable to the customer's legal form and articles:

Shareholders / General Meeting → Board (Chair, optional Vice-Chair, directors and committees) → CEO / President / Managing Director → executive management → head-office functions → factories, DCs, depots, branches and stores.

A factory may include a Factory GM / Plant Manager, Production, IE, PPC, QA, Maintenance, site HR, site Finance/Cost Control, Stores, Purchasing under CPO policy, HSE and Administration. Distribution may include regional/area management, DC/depot management and store management with supervisors, sales/POS, stock control, customer service, visual merchandising and security.

The model must permit different structures and legal forms; never hard-code a single company chart, title, reporting line, monetary threshold, legal body or board approval rule. Distinguish Board Vice-Chair from an executive Vice-President. Unverified acronyms/titles remain unconfigured until the customer's official title is confirmed.

### 2.3 DoA and approval engine

DoA is an independently versioned policy, not a list of user roles. Each rule can constrain:
- legal entity, site, department, cost centre, project, brand, channel and transaction type;
- amount, currency, cumulative period exposure, budget availability and risk/materiality;
- requester, preparer, reviewer, approver and release actors;
- number/order of approvals, escalation, delegation, expiry and emergency path;
- effective dates, policy version, evidence and supersession.

Every decision stores policy/rule snapshot, position appointment snapshot, actor, scope, amount/currency, budget reference, decision, reason, timestamp and immutable audit event. Incompatible duties and self-approval fail closed. A material change after approval invalidates affected approvals and restarts the required approval steps.

Purchase-to-pay separation: request/budget validation → procurement sourcing → technical evaluation → PO preparation → delegated PO approval → independent receipt/quality → AP three-way match → treasury payment preparation → separate bank-signatory release. A tenant owner or a generic process owner must not become an automatic PO approver. Board approval is invoked only where the signed customer DoA requires it.

### 2.4 Payroll is server-authoritative

Laravel services are the source of truth for payroll. Angular only gathers authorized inputs and displays server-calculated results. Monetary inputs are exact decimal strings; calculations use integer minor units or exact decimal arithmetic. No browser-calculated PAYE, pension, gross or net value is authoritative.

PostgreSQL is the transactional source of truth. Every table must have tenant and legal-entity scope as applicable, RLS, least-privilege grants, foreign-key and uniqueness constraints, effective-dated history, idempotency for retryable commands and append-only audit for approval/posting transitions. External integrations use versioned REST/OpenAPI contracts and transactional outbox events. Redis is for queues/cache/short-lived coordination only; Python specialist services may provide forecasting or document extraction but never own payroll/DoA transactions.

## 3. WP-03 build plan — organogram, positions and delegated authority

### WP03-ORG-01 — Source reconciliation and domain contract

- Reconcile the organogram design, tenant-organization identity architecture, WP-03 requirements and current schema before writing migrations.
- Inventory all existing `org_units`, legal entity, site, warehouse, department, employee, membership and role fields; do not create duplicate organization or employee masters.
- Replace any draft use of `org_units.owner_user_id` as a company-manager shortcut with a position/assignment relationship such as `manager_position_id`, after dependency and migration review.
- Publish canonical OpenAPI schemas and domain ownership for OrgUnit, Position, PositionAppointment, ReportingLine, DelegationRule, ApprovalInstance and ApprovalDecision.
- Add WP-01 requirement IDs and test traceability; preserve legacy aliases until the semantic crosswalk is accepted.

**Acceptance:** approved entity relationship diagram, field ownership matrix, API contract, migration impact report and source-to-requirement crosswalk.

### WP03-ORG-02 — Organization and position master

Build backend/schema:
- legal-entity-linked organizational units with unit type, parent, cost centre, site and effective dates;
- stable positions with configurable title, job family, grade, function, site/department scope, status and authority-profile reference;
- position appointments with employee/person reference, start/end, primary/acting/temporary status, appointment evidence, approver and audit;
- effective-dated solid-line manager position and optional functional/dotted-line reporting relationships;
- vacancy, succession, acting appointment, transfer, suspension and termination handling;
- validation for cycles, overlapping active appointments, invalid manager scopes, orphaned units and impossible reporting chains.

**Acceptance:** temporal and graph-integrity tests; no user account is treated as a position or employee; historic organization views reconstruct as-of a selected date.

### WP03-ORG-03 — Delegation of Authority policy and workflow engine

Build:
- versioned DoA policy, rules, thresholds, scopes, currencies, cumulative limits and effective dates;
- approval-step templates, quorum/parallel/sequential approvals, delegation and expiry;
- resolution service from actor → active membership → person → active appointment → position → applicable DoA rule;
- budget validation and risk/materiality attributes supplied by the owning domain;
- approval instance snapshots and immutable decisions;
- reapproval on material transaction mutation;
- explicit maker-checker and incompatible-role conflict detection;
- fail-closed fallback when no applicable rule or scope can be resolved.

Do not copy monetary limits from a sample ERP or infer legal authority from job title. Company-specific limits require the customer's signed DoA policy and legal governance evidence.

**Acceptance:** deterministic resolver tests; boundary/cumulative/currency/date/scope tests; no self-approval; revoked/expired appointment and delegation rejected; approval replay idempotent; material change invalidates prior approval.

### WP03-ORG-04 — Publish the reusable DoA integration contract (WP-03 scope)

- Publish the versioned API and event contracts for approval requests, rule resolution, approval steps, decisions, delegation and authority snapshots.
- Provide a domain-neutral resolver that accepts a transaction type, authenticated actor, legal entity, scope, amount/currency, budget facts, risk attributes and transaction version from the owning domain.
- Define consistent response states, denial reasons, idempotency keys, reapproval rules, audit events and outbox contracts.
- Keep the WP-03 implementation independent of procurement, quality, distribution, finance and payroll transaction ownership. Each numbered package integrates its own transactions when reached in the approved sequence.
- Preserve tenant OWNER authority only for tenant/platform governance; it is never transaction authority.

**Acceptance:** OpenAPI/event schemas, resolver unit and security tests, policy-version replay, scope/amount/date/currency boundary tests, maker-checker conflict tests and integration contract tests using synthetic domain adapters. Domain production integrations are delivered by their numbered packages below.

### WP03-ORG-05 — Angular organogram and authority workbench

Build Angular features:
- organization tree and effective-date history;
- legal entity/site/department/cost-centre navigator;
- position catalogue and vacancy board;
- appointment/acting/delegation request and approval;
- solid-line and functional reporting-line editor;
- DoA policy/rule configuration with simulation/impact preview;
- approval inbox and decision history;
- access-aware export/print view and immutable change log.

All screens use typed API services and server-side authorization. Do not implement business authority in client-side route guards or hide controls as a substitute for API authorization.

**Acceptance:** accessible UI, keyboard navigation, API permission negative tests, e2e flows for create/restructure/appoint/act/expire/approve/revoke and effective-date historical view.

### WP03-ORG-06 — Migration, audit and operational rollout

- Add forward-only migrations with safe backfill and dual-read/compatibility period where needed.
- Resolve existing user-role grants to approved positions only with explicit mapping evidence; do not auto-convert role names into job titles.
- Provide tenant admin import/export templates, validation preview, duplicate detection and rollback plan.
- Add audit events and outbox events for organization, appointment and DoA changes.
- Add metrics for unassigned approver, expiring appointment, unresolved authority, approval queue age and policy coverage.
- Test tenant isolation, RLS, concurrency, temporal consistency, data retention and restore.

**Acceptance:** zero unauthorized implicit approver mappings; successful migration dry-run/rollback rehearsal; exact-SHA CI; tenant and scope tests.

### WP03-ORG-07 — WP-03 package acceptance and handover

- Trace every WP-03 requirement to API, schema, UI, test and evidence.
- Complete invitation/identity/MFA/SSO, tenant lifecycle, platform entitlements, scoped resource authorization and installer/operator work already open in WP-03.
- Run full WP-03 acceptance matrix and update path map/handover register.
- Only after the complete WP-03 DoD is met does the fixed package sequence advance.

**Hard boundary:** a Board, CEO, procurement or finance title in the chart is not authority until a current appointment and approved DoA policy make it so. The organogram is configuration, not legal advice.

## 4. WP-14 build plan — employee lifecycle, Ethiopian payroll and compensation

### WP14-PAY-01 — HR employee master and organization binding

Build the authoritative employee master and connect it to WP-03:
- person identity separated from login and tenant membership;
- employee number scoped to legal employer;
- employment relationship, contract revisions, probation where lawful, salary basis, grade and effective dates;
- legal employer, work site, department, cost centre, position appointment and manager position;
- employee status, hire, transfer, promotion, secondment, suspension, leave and exit;
- worker classification and evidence for employee, agency, contractor, independent service, supplier, creator and subcontractor;
- payroll profile, statutory identity/reference and protected payment destination.

Payroll eligibility requires an active employment relationship, valid contract and legal-employer assignment for the entire applicable period. No generic payee record is sufficient.

### WP14-PAY-02 — Time, payable events and compensation components

- Ingest time events from approved device/card/QR/biometric references, manual timesheets and controlled integrations.
- Store raw event separately from approved payable time; deduplicate and handle offline/out-of-order corrections.
- Integrate WP-06/WP-10 skills, SAM/SMV, operation output, attendance, approved overtime and accepted/rejected/rework quantities.
- Separate manufacturing incentive, store commission, wholesale commission, reseller rebate, creator/affiliate, supplier/AP and `RETAINER_SERVICE`.
- Build earning/deduction component catalogue with independent taxable, pensionable, reimbursable and accounting classifications.
- Exemptions require exact legal provision, employee/component facts, caps, source URL/hash, effective dates, evidence and independent approval.
- Non-standard pensionable salary requires rationale, contract/legal basis, source hash and reviewer approval.

### WP14-PAY-03 — Ethiopia legal source and statutory rule engine

- Load complete official Amharic Gazette texts and authorized translations for income-tax, pension, labour and applicable regulations/directives.
- Store legal instrument metadata, article/subarticle, issuing authority, publication/effective/transition dates, official URL, original document SHA-256, applicability and reviewer.
- Reconcile Proclamation 1395/2025, Proclamation 979/2016 and amendments, Regulation 597/2026, Pension Proclamation 1268/2022, Directives 1085/2025–1087/2025, Labour Proclamation 1156/2019 and later applicable instruments.
- Keep taxability and pensionability as independent classifications.
- Keep both ETB 30,000 candidate regressions: gross taxable PAYE ETB 8,450 / net ETB 19,450; pension-first PAYE ETB 7,715 / net ETB 20,185. Neither is production authority until a qualified Ethiopian tax opinion explicitly approves the PAYE base and exact clauses.
- Preserve exemption evidence, non-standard pensionable salary rationale, rule-source snapshot hash, explicit rounding method, progressive band boundary tests and `RETAINER_SERVICE` exclusion.
- Use source documents and evidence rather than a fixed legacy rule identifier, sample database schema, sample customer or product version.

**Legal hold:** MoR explanatory Technical Notes may be considered as interpretive context but are expressly non-binding. No statutory PAYE base, pension classification or rate is activated from notes or tests alone. Qualified tax/pension professionals must approve exact legal interpretations and source set.

### WP14-PAY-04 — Server-side calculation and immutable run snapshots

- Implement Laravel calculator using exact decimal/integer-minor-unit arithmetic.
- Accept monetary input as decimal strings; reject floats, malformed precision and client-computed totals.
- Freeze employee, contract, assignment, component, time and statutory-source snapshots for the payroll period.
- Resolve the approved PAYE base only from an approved tax opinion matching the selected rule payload.
- Calculate employee pension and employer pension separately; employer pension is employer cost and never an employee deduction.
- Apply only approved statutory and contractual deductions; preserve line-level reason and source.
- Produce line-level calculation breakdown, deterministic source snapshot hash and explainable payslip.
- Idempotently calculate draft payroll only; posted history is immutable and any correction is a separately authorized adjustment/reversal.

### WP14-PAY-05 — Payroll workflow, accounting and remittance

- Payroll period open/close and input freeze.
- Exception queues for missing employee contract, assignment, time, component evidence, legal classification, invalid rule dates and unexplained variance.
- Independent payroll preparer/reviewer/approver and finance controller roles resolved through WP-03 DoA.
- Separate calculate, review, approve, post, payment proposal, bank release, PAYE remittance, pension remittance and reconciliation transitions.
- Create balanced WP-13 journals only after approval; maintain liability ledgers and source linkage.
- Integrate bank/payment provider only through authorized payment proposal/release; capture external references and reconcile actual settlement separately.
- No payroll calculation automatically executes payment or statutory filing.

### WP14-PAY-06 — Angular HR/payroll workbenches

Build:
- employee master and contract/assignment lifecycle;
- statutory classification and legal evidence review;
- attendance exceptions and payable-time approval;
- compensation component/evidence catalogue;
- payroll period/input readiness dashboard;
- draft calculation, line explanation and variance review;
- maker-checker approval inbox;
- payslip portal with employee-specific access;
- finance posting and remittance reconciliation workbench;
- protected access, privacy masking and audit history.

The client never computes PAYE/pension or makes legal classifications. It displays authoritative server responses and clearly labels HOLD/REVIEW/APPROVED status.

### WP-15 — Agent and intelligent operations integration (downstream package)

This is a WP-15 delivery scope, not a WP-14 task. Execute it only after WP-14 HR/payroll has completed its package work and WP-15 is reached in the fixed sequence. Use governed WP-15 agent capabilities only through scoped tools, typed APIs and explicit policy:
- **HR data-quality agent:** detect missing/expired contracts, assignment gaps, duplicate employee records and inconsistent position links; propose corrections, never write/approve them without authorized workflow.
- **Organogram/DoA policy assistant:** explain the applicable position and approval path; draft a proposed chart/DoA change with impact analysis; never appoint a director or grant transaction authority.
- **Payroll legal-source monitor:** monitor official source catalogues, identify possible amendments, compare exact articles and prepare a proposed change record; never auto-activate a rule.
- **Payroll pre-audit agent:** detect outliers, unexplained salary changes, missing exemption evidence, non-standard pensionable salary, duplicate lines, time/attendance mismatch and PAYE/pension variance; propose a review task, not approve/pay.
- **Reconciliation agent:** compare payroll subledger, WP-13 journal, bank settlement and official remittance receipts; prepare exceptions and draft reconciliation.
- **Document extraction agent:** extract employee contract/legal-source metadata with confidence and source coordinates; human reviewer confirms critical fields.

Agent runtime requirements:
- use WP-15 AgentGovernanceService / proposal-and-approval pattern and current agent run/action audit model;
- least-privilege service identity, tenant/legal-entity/site/employee scope, tool allowlist, time-bound access and redaction;
- read-only by default; write actions become typed proposals with idempotency key and human/authorized service approval;
- prohibit direct SQL, direct payroll posting, bank release, statutory filing, employee status changes, position appointment, DoA edits and rule activation by an agent;
- log model/provider, prompt/policy version, source references, tool inputs/outputs, confidence, decision, approver and outcome;
- test prompt injection, malicious document content, cross-tenant leakage, replay, stale source, hallucinated legal citations and overbroad tool use;
- provide deterministic non-AI fallback for every legally or financially critical workflow.

### WP14-PAY-08 — Acceptance, privacy and release gate

- Complete AT-HR-001..016 and add Ethiopia payroll statutory/regression cases from the release-gate document.
- Verify RLS, employee self-service access, HR/payroll/finance segregation, field-level privacy, audit and retention.
- Run exact-head migrations, backend tests, Angular tests/build, dependency/security scans and integration tests.
- Run concurrency/idempotency tests for payroll period creation, line creation, calculation, approval and posting.
- Reconcile payroll register → GL → payment file/bank result → PAYE/pension remittance evidence.
- Verify historical posted payroll remains unchanged after legal-source update.
- Run backup/restore and correction/reversal rehearsal.
- Complete factory, head-office and branch UAT with synthetic or approved non-production data.
- Keep both Ethiopia payroll activation settings false until employee master and professional statutory approvals are complete; changing them requires a separate reviewed release commit and authorized operational sign-off.

## 5. Shared data and event contracts

### Core ownership

| Data | System of record | Consumers |
|---|---|---|
| Legal entity, site, org unit, position, reporting line | WP-03 enterprise organization | All business domains |
| DoA policy and approval decisions | WP-03 authorization/approval domain | Procurement, Finance, HR, Payroll, Inventory, Quality, Manufacturing, Distribution |
| Person and employment contract | WP-14 HR | WP-03 appointment, WP-06/WP-10 skills/time, WP-13 finance, WP-15 agents |
| Position appointment | WP-03, linked to WP-14 employee | HR, authorization, payroll, workflow routing |
| Attendance raw events | WP-10/device integration owner | WP-14 payable-time approval |
| Payroll inputs/results | WP-14 payroll | WP-13 GL/treasury, employee payslip, WP-15 read-only analytics |
| Official legal-source documents and approvals | WP-02 legal source register / controlled evidence store | WP-14 rule engine, WP-15 source monitor |
| Agent run/proposal/approval audit | WP-15 governance | All domains through approved scoped tools |

### Events (versioned, tenant-scoped, idempotent)

- `OrganizationUnitChanged.v1`
- `PositionCreated.v1`
- `PositionAppointmentApproved.v1`
- `ReportingLineChanged.v1`
- `DelegationPolicyPublished.v1`
- `ApprovalRequested.v1`, `ApprovalDecided.v1`, `ApprovalInvalidated.v1`
- `EmployeeHired.v1`, `EmploymentContractChanged.v1`, `EmployeeAssigned.v1`
- `PayableTimeApproved.v1`
- `PayrollCalculated.v1`, `PayrollReviewed.v1`, `PayrollApproved.v1`, `PayrollPosted.v1`
- `PayrollPaymentProposed.v1`, `PayrollPaymentReconciled.v1`
- `PayeeStatutoryClassificationReviewed.v1`
- `StatutorySourceChangeDetected.v1`, `StatutoryRuleApprovalRequested.v1`

Events carry schema version, tenant, legal entity, aggregate ID, occurred-at, effective-at, actor/service identity, correlation ID, idempotency key and source snapshot reference. Sensitive salary, bank, identity and health fields are not copied into broad event payloads; use protected references and purpose-limited access.

## 6. Cross-package dependencies and strict sequence

| Dependency | Required before dependent feature can pass |
|---|---|
| WP-01 requirements reconciliation | Stable requirement IDs and source-to-acceptance mapping for org/DoA and payroll |
| WP-02 legal source register | Complete official sources and qualified legal approvals for statutory activation |
| WP-03 org/position/DoA | Authoritative legal entity/site/position/appointment and transaction approval resolution |
| WP-06/WP-10 | Approved skills, operation output and time evidence for incentive/payable-time integration |
| WP-13 finance | Payroll journal, liability, payment proposal, bank reconciliation and statutory liability posting |
| WP-14 HR/payroll | Authoritative employee/contract/profile and statutory payroll |
| WP-15 agent governance | Scoped read tools, proposals, approval boundary, audit and evaluation |
| WP-16 release | E2E, security, performance, recovery, UAT, installer, operational and legal acceptance |

The implementation sequence is: finish WP-03 active identity/tenant requirements → deliver organogram/position/DoA as the WP-03 organization authority increment → complete WP-03 acceptance/handover → continue WP-04 through WP-13 in fixed order → execute WP-14 HR/payroll build → add WP-15 agent integration in its numbered package → prove cross-domain release at WP-16. The WP-14 design and isolated payroll calculation may be prepared earlier, but no production payroll dependency may skip WP-03 or WP-02.

## 7. Global acceptance and quality gates

A source artifact is accepted only when all applicable gates pass on the exact commit SHA:

1. Requirements traceability: each WP requirement mapped to migration/model, service/API, Angular workflow, audit/event, test and evidence.
2. Architecture: bounded context ownership, no duplicate masters, OpenAPI compatibility, idempotency and transactional outbox.
3. Data: PostgreSQL migrations, tenant/legal-entity RLS, least-privilege grants, constraints, effective-date integrity and safe rollback.
4. Backend: policy/authorization tests, positive and negative workflow tests, domain invariants, concurrency and idempotency.
5. Frontend: Angular typed services, accessibility, permission-aware UX, loading/error/empty states, no client authority logic, component/e2e tests.
6. Security/privacy: cross-tenant/scope attack tests, field masking, audit completeness, sensitive data retention and agent tool isolation.
7. Integration: event contract, retries/dead-letter/replay, downstream reconciliation and no duplicate financial posting.
8. Agent safety: prompt injection, poisoned source document, overbroad access, hallucinated legal citation, approval bypass and tool-call audit tests.
9. Operations: metrics, alerts, queue recovery, backup/restore, migration rehearsal, deployment/rollback and operator runbooks.
10. Business acceptance: factory/head-office/DC/branch role-based UAT, realistic exceptions and signed customer-specific policy where required.

CI runner allocation failure is not a test pass or an application test failure. Record it as infrastructure BLOCKED and retry only through an authorized/available runner; do not merge around required technical gates.

## 8. Explicit non-goals and safeguards

- Do not copy the sample ERP's customer data, legacy 16.x product version, fixed payroll rule identifier, SQL tables, schema, engineering architecture or organization chart.
- Do not infer the customer's exact company legal form, Board composition, position incumbents, DoA thresholds, payroll contract terms or employee data.
- Do not hard-code universal PO or payroll approval amounts.
- Do not let tenant OWNER, Business Owner, Process Owner, Procurement Owner, Platform Owner, AI agent or system administrator automatically approve a company transaction.
- Do not allow an AI agent to give binding legal advice, approve payroll, post journals, release payments, file tax/pension returns or appoint company officers.
- Do not recalculate/rewrite posted payroll or historical transactions when new legal sources or rules are added.
- Do not enable live payroll, statutory rates, real bank/payment or production deployment without the accountable professional/human approval required by governance.

## 9. Current evidence and status

- WP-03 remains PARTIAL and is the active package; the organogram/DoA source is a design increment, not a delivered runtime feature.
- WP-14 remains PARTIAL; the payroll localization and server-side calculator are isolated candidate work, not yet an integrated, fully accepted HR/payroll feature.
- The two payroll activation flags remain default false in the candidate source.
- No actual payroll, journal posting, bank release, PAYE/pension remittance, employee appointment or company DoA policy has been executed by this plan.
- This plan does not claim CI, UAT, legal sign-off or production readiness. Exact-head CI, source reconciliation and package acceptance remain mandatory.
