# WP-14 People & Payroll — Canonical Engineering Design and Build Release

**Project:** Modern Garment End-to-End Addis ERP  
**Work package:** WP-14 — People / HR / Payroll / Compensation  
**Artifact ID:** WP14-ENG-DESIGN-001  
**Date:** 27 November 2026  
**Target repository:** `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`  
**Source branch:** `work/wp14-ethiopia-payroll-localization-2026-11-27`  
**Integration sequence:** WP-03 organization/position/DoA → WP-04–WP-13 in approved order → WP-14 → WP-15 agent integration → WP-16 end-to-end release  
**Artifact purpose:** Canonical engineering design, implementation backlog, security controls and verifiable acceptance contract. This document is a committed design/build artifact; it does not claim that unimplemented runtime features or unrun tests have passed.

---

## 1. Binding delivery and acceptance policy

### 1.1 Evidence-based legal and compliance acceptance

No separate individual developer formality, personal attestation or duplicate acceptance ceremony is required for legal/compliance matters when the complete required supporting evidence is already present, accessible and traceable in the GitHub-controlled project evidence record.

A legal evidence record is sufficient for engineering traceability when it contains, as applicable:

- authoritative instrument or opinion and exact article/paragraph/schedule;
- issuing authority, jurisdiction, instrument identifier and source URL;
- original source bytes or controlled evidence object, SHA-256 digest and retrieval date;
- publication, effective, transition and supersession dates;
- applicability analysis for the legal employer, worker category, pay component and payroll period;
- qualified professional's identity/qualification, review date, opinion or approval reference and explicit conclusion;
- authorized translation and translation review where relevant;
- rule payload/version to which the evidence applies.

This removes redundant developer-side acceptance formalities. It does **not** remove the need for qualified legal/tax/pension evidence where the law or the existing legal gate requires it. Missing, conflicting, unverifiable or inapplicable evidence remains a system-enforced hold.

### 1.2 Engineering acceptance remains mandatory

Engineering acceptance is determined by objective, reproducible evidence on the exact Git commit SHA. It is not waived by a legal opinion, a developer statement, a green unit-test subset, an open PR or a design document.

Required gates include source reconciliation, architecture and data integrity, migration safety, API authorization, RLS, concurrency, deterministic calculation, auditability, privacy, accounting/remittance reconciliation, integration tests, security scans, operational recovery and WP-16 release evidence.

### 1.3 Delivery behavior

The delivery workflow is: inspect → reproduce → fix → improve → build → test → verify exact SHA → commit → push → report evidence and remaining blockers. A discovered issue must be paired with a correction or a concrete implementation increment wherever the repository and available execution environment permit. Never relabel an infrastructure failure as an application pass or fabricate a test result.

---

## 2. Existing source baseline and reconciliation boundary

The repository contains a canonical payroll-review foundation and a separate Ethiopia payroll candidate reported as 57 unmerged commits. The candidate includes a calculator, Finance integration work, evidence migrations and unit tests. These are source inputs, not proof that the feature is integrated or production-ready.

Known source documents to reconcile:

- `docs/localization/ethiopia-payroll-engineering-release-gate.md`
- `docs/localization/ethiopia-payroll-localization-2026-11-27.md`
- `docs/hr/workforce-payroll-pension-benefits-compensation.md`
- `docs/work-packages/wp03-wp14-organogram-payroll-implementation-plan.md`
- `docs/localization/ethiopia-legal-regulatory-register.md`
- `docs/architecture/application-folder-code-map.md`

Before changing application code, create a source inventory with exact refs and SHAs for the canonical branch, payroll candidate branch and any PRs. Capture the 57-commit range, changed paths, migrations, dependencies, tests, config flags and Finance touchpoints. Do not cherry-pick the whole candidate blindly, copy a second employee master, or overwrite canonical files.

### 2.1 Reconciliation procedure

1. Identify the canonical delivery branch and its exact head SHA.
2. Identify the payroll candidate head SHA and all commits not present in the canonical base.
3. Compare migration files by semantic effect, not filename alone. Record table/column/index/policy ownership and dependency graph.
4. Build a migration DAG. Resolve duplicate timestamps, references to not-yet-created tables, non-repeatable operations, missing down/forward recovery strategy and cross-package dependencies.
5. Identify the authoritative document/evidence storage abstraction. Reuse it; do not create a parallel payroll file store.
6. Compare payroll models, API routes, policies, services, events and Finance adapters against canonical equivalents.
7. Reconcile tests and fixtures. Preserve useful candidate regression cases, but move them to canonical test conventions and add missing integration/security coverage.
8. Port changes in dependency-ordered, reviewable increments. Keep activation settings disabled.
9. Run migration from a clean database and from the supported prior schema. Verify rollback/recovery on disposable test data.
10. Record each adopted, rewritten, rejected or deferred candidate commit/path with rationale.

**Required reconciliation artifacts:** candidate commit/path inventory, migration dependency matrix, schema ownership matrix, API contract diff, storage/evidence mapping, test coverage matrix, conflict decisions and exact-SHA validation report.

---

## 3. Target architecture and bounded-context ownership

### 3.1 Authoritative system boundaries

| Capability | Authority / owner | WP-14 responsibility |
|---|---|---|
| Tenant, legal entity, site, org unit, position, appointment, reporting line and DoA | WP-03 | Consume validated references; do not duplicate masters |
| Person, employee, contract, assignment, HR lifecycle | WP-14 | Own employee and employment records |
| Raw attendance/device events | Approved time/device domain (WP-10 integration) | Consume source events; maintain approved payable-time records |
| Skills, operation standards and production output | WP-06 / WP-10 | Consume approved, attributable incentive inputs |
| Payroll rules and payroll runs | WP-14 | Server-authoritative calculation and immutable run snapshots |
| General ledger, AP, treasury, bank release and reconciliation | WP-13 | Submit balanced journal/payment proposals and consume settlement evidence |
| Legal evidence register and legal holds | WP-02 governance / controlled evidence store | Reference immutable evidence and approved rule snapshots |
| Agent policy, tools and action audit | WP-15 | Expose scoped read models and typed proposal interfaces |
| Cross-package release and installer | WP-16 | Supply reproducible migration/build/test/deployment evidence |

No WP-14 table becomes a competing tenant, organization, position, user, Finance ledger, bank or legal-document master.

### 3.2 Service architecture

- **Laravel application/domain services:** authoritative employee lifecycle, payroll rules, calculation, workflow, authorization checks and integration commands.
- **PostgreSQL:** transactional source of truth; constraints, foreign keys, tenant/legal-entity scope, RLS, immutable financial snapshots and transactional outbox.
- **Angular:** typed workbenches and employee self-service. It never calculates statutory payroll or grants authority.
- **Redis/queues:** disposable coordination, cache and asynchronous job execution only; no authoritative payroll state.
- **Object storage:** private, tenant-controlled legal/employee evidence using the existing document asset abstraction, immutable object version/reference, digest verification and access audit.
- **Integration APIs/events:** versioned contracts, idempotency keys, correlation IDs, transactional outbox and replay-safe consumers.
- **Agent services:** WP-15 governed tools; read-only by default, no direct database access or direct financial/legal state mutation.

### 3.3 Core domain model

Reuse existing canonical entities where present; add only missing concepts after schema inventory.

- `Person`: natural-person identity, separated from authentication.
- `Employee`: employment identity scoped to the legal employer, unique employee number within the employer.
- `EmploymentContract`: versioned, effective-dated agreement and terms.
- `EmployeeAssignment`: effective-dated legal entity, site, department, cost centre and WP-03 position appointment reference.
- `EmploymentEvent`: hire, transfer, promotion, secondment, suspension, leave, return and exit.
- `PayrollProfile`: protected statutory and payment references, with field-level access.
- `TimeEvent`: immutable source event and provenance.
- `PayableTimeRecord`: reviewed/approved time and payable quantity, distinct from raw events.
- `CompensationComponent`: earning/deduction/reimbursement classification and effective dates.
- `PayrollPeriod`, `PayrollRun`, `PayrollRunLine`: controlled lifecycle and immutable snapshots.
- `StatutoryRuleSet`, `StatutoryRule`, `LegalEvidenceReference`: versioned approved rule/evidence binding.
- `PayrollApproval`, `PayrollAdjustment`, `PayrollPostingReference`: workflow and correction provenance.
- `PayrollLiability`, `RemittanceProposal`, `SettlementReference`: liability and external settlement linkage, with WP-13 remaining financial authority.

All records carry tenant scope and legal-entity scope where applicable. Sensitive identity, salary, bank and statutory identifiers are encrypted or protected using the project's established secret/data protection standards; never expose them in broad event payloads or logs.

---

## 4. Migration, evidence storage and data-integrity design

### 4.1 Migration controls

- Forward-only production migrations; no destructive rewrite of payroll history.
- Explicit dependency ordering and deterministic migration identifiers.
- Expand → backfill → validate → switch-read/write → contract pattern for incompatible schema changes.
- Backfills are bounded, resumable, observable and idempotent.
- Constraints are introduced only after preflight detects and resolves invalid legacy rows.
- Index creation and lock impact are measured against production-like data volume.
- Migration runner uses a database advisory lock or the project's equivalent to prevent concurrent migration execution.
- Every migration has clean-install and upgrade-path tests. Destructive rollback is not used as a production recovery plan.
- A failed migration leaves a documented, recoverable state; operator runbook specifies forward repair or restore procedure.
- No migration silently enables payroll flags, creates live employee records or posts financial entries.

### 4.2 Evidence storage integrity

Use the canonical document asset service. Each evidence reference must bind tenant, legal entity where applicable, document UUID, document type, private object version, SHA-256, uploader/service actor, creation time, source metadata, review state and retention classification.

At the point of use, the server verifies that the referenced object exists, is tenant-owned, is the expected immutable version and hashes to the approved digest. A mismatch, replaced object, missing object or cross-tenant reference blocks the operation and emits a security/audit event. The digest is computed from bytes retrieved from controlled storage, not from a client-supplied hash.

Do not log document contents, employee bank details, identity numbers or full salary payloads. Audit records carry protected references and minimal metadata.

### 4.3 Data poisoning and malicious-input threat model

Treat imported CSVs, employee documents, legal PDFs, time-device payloads, agent outputs and integration messages as untrusted input. Defend against malicious or corrupted data without asserting that any existing candidate is malware.

Controls:
- strict schema validation, size/type limits, content-type and file-signature checks, malware scanning at ingestion where supported, quarantine and safe parser isolation;
- reject executable content and unexpected embedded objects in evidence workflows;
- canonicalize and validate identifiers, currencies, decimal strings, dates, time zones and enum values;
- parameterized database access; no dynamic SQL from uploaded content or agent output;
- tenant/legal-entity authorization on every lookup and write, not only UI filtering;
- source digest and provenance checks; detect duplicate/replay events;
- poison-resistant import preview, row-level errors and explicit approval before commit;
- prompt-injection resistant agent document handling: extracted text is data, never trusted instructions;
- audit every import, override, rejected digest, replay and privileged access attempt;
- alert on mass edits, unusual compensation changes, cross-scope access, repeated hash mismatch and abnormal payroll variance.

---

## 5. API and workflow contract

All routes must be inventoried against existing Laravel conventions before implementation. Names below are logical contract targets, not a claim that these routes already exist.

| Resource / command | Required behavior |
|---|---|
| Employee master | Create/update under HR policy; employer-scoped uniqueness; no direct status bypass |
| Contracts and revisions | Effective-dated, evidence-linked, overlap and retroactivity validation |
| Assignments | Resolve current WP-03 legal entity/site/position references; reject invalid or expired appointments |
| Time import | Idempotent ingestion; immutable raw event; source identity and provenance |
| Payable-time approval | Maker-checker, period scope, correction reason and audit |
| Compensation catalogue | Versioned earning/deduction/reimbursement classification and evidence |
| Statutory rule publication | Requires complete official source and qualified approval evidence; immutable version |
| Payroll period/run | Authorized period lifecycle, one active run policy, frozen inputs and rule snapshots |
| Calculate | Server-side deterministic calculation; decimal strings only; idempotent request |
| Review/approve | Independent actor and WP-03 DoA resolution; no self-approval |
| Post | Separate authorized command; one-time posting key; balanced WP-13 journal response |
| Payment/remittance proposal | Create proposal only; WP-13 controls bank release and actual settlement |
| Reconcile | Match external references/amounts/periods; exceptions require resolution and audit |
| Adjustment/reversal | New linked run or adjustment; never mutate posted history |

### 5.1 API security requirements

- Authentication plus tenant membership plus resource scope plus capability/policy authorization on every request.
- Legal-entity, site, department and employee scope derived server-side from approved membership/appointment/DoA; never trusted from client-supplied tenant IDs alone.
- Field-level authorization for salary, bank, tax, pension and identity data.
- Employee self-service can access only the authenticated employee's own permitted data.
- HR, payroll preparer, payroll reviewer, payroll approver, Finance poster, treasury releaser and auditor are distinct capabilities; roles do not collapse segregation of duties.
- Request validation rejects floats for money, excess precision, unknown component codes, stale rule versions, unsupported periods and client-supplied calculated totals.
- Idempotency key is mandatory for commands that can create a run, line, approval, posting, payment proposal or remittance proposal.
- Rate limiting, pagination bounds, safe error responses, CSRF/session protections and API versioning follow the canonical application security baseline.
- Authorization failures do not disclose whether another tenant's employee or payroll record exists.

---

## 6. Calculation engine and Ethiopia localization

### 6.1 Deterministic calculation contract

Laravel owns all authoritative calculations. Use integer minor units or exact decimal arithmetic with explicit currency scale and approved rounding. Do not use binary floating point for monetary calculations.

For every run, freeze:
- employee and legal-employer identity references;
- contract and assignment versions covering the period;
- approved payable-time and incentive input versions;
- compensation component versions and classifications;
- tax and pension rule versions and effective dates;
- official source and legal opinion evidence references/digests;
- rounding policy and calculator version;
- calculation inputs, line-level formula breakdown and deterministic snapshot digest.

Re-running the same frozen input/rule/calculator version must produce the same result. A changed input creates a new draft calculation/version and invalidates any review/approval that depended on the prior snapshot.

### 6.2 Ethiopia legal and statutory controls

The existing WP-14 engineering release-gate remains the controlling legal evidence checklist. At minimum, reconcile the source and applicability of:

- Income Tax Proclamation 1395/2025 and relevant transition provisions;
- Income Tax Proclamation 979/2016 and amendments / controlling PAYE provisions;
- Regulation 597/2026 and applicable Regulation 410/2017 provisions for non-employee service classification;
- Pension Proclamation 1268/2022 and applicable private-organization directives 1085/2025–1087/2025;
- Labour Proclamation 1156/2019 and applicable amendments/directives.

Preserve the two ETB 30,000 candidate regressions exactly as regression fixtures:
- gross taxable basis: PAYE ETB 8,450; employee pension ETB 2,100; net ETB 19,450;
- gross taxable less employee pension basis: PAYE ETB 7,715; employee pension ETB 2,100; net ETB 20,185.

These are competing candidate outcomes, not independent legal proof. The production PAYE base must match the explicit written qualified Ethiopian tax opinion and approved frozen rule payload. Do not infer the method from test success, explanatory notes, a prior implementation or a developer's preference.

Employee and employer pension contributions are separate. Employer pension is employer cost and must never reduce employee net pay. Taxability and pensionability are independent component attributes. Exemptions require exact legal provision, applicability facts, caps, source digest and approval. Non-standard pensionable salary requires a documented legal/contract rationale and independent review.

`RETAINER_SERVICE`, supplier, creator/affiliate, reseller rebate and other non-employee payables are not employee payroll by label. Route them to the applicable AP/payee tax classification workflow; do not let them enter the employee PAYE/pension calculator without a legally supported employment classification.

### 6.3 Fail-closed activation

Keep both existing settings false until all applicable engineering and legal evidence gates pass:

- `ethiopia.employee_master_release_ready`
- `ethiopia.statutory_activation_enabled`

No environment override or client-side switch may bypass these controls. Activation is a reviewed, evidence-linked source change after exact-SHA engineering tests and required professional/legal evidence are present. This design artifact does not enable either flag.

---

## 7. Concurrency, idempotency and financial correctness

### 7.1 Payroll concurrency

- Enforce a database uniqueness policy for one active payroll run per tenant/legal employer/period/run type, with explicit supersession semantics.
- Serialize period state transitions using row-level locks or equivalent transactional locking.
- Use unique idempotency records with request digest and result reference; same key/same payload returns the original result, same key/different payload is rejected.
- Protect line uniqueness by run and employee/employment identity; handle multiple legal employments only through explicit supported policy.
- Use optimistic version checks for draft edits and approval snapshots.
- Prevent concurrent calculate/review/approve/post races with state-transition compare-and-set and transaction boundaries.
- Outbox events are written in the same transaction as state change; consumers deduplicate by event ID/idempotency key.
- Queue retries are safe and cannot create duplicate lines, journals, payments or remittances.
- Lock ordering is consistent; deadlock retries are bounded and observable.

### 7.2 Posting invariants

A payroll run cannot post unless:
- run is frozen and approved under current DoA;
- all lines are valid and evidence digests verify;
- totals reconcile to the frozen payroll register;
- all required employee, employer, tax and pension liabilities balance;
- account mapping is complete and effective for the legal entity/period;
- period is open in WP-13;
- posting idempotency key is unused or resolves to the same prior posting;
- segregation-of-duties and authorization checks pass.

WP-14 sends a typed posting request with source run ID, immutable snapshot digest, period, legal entity, currency totals, account mapping references and idempotency key. WP-13 validates and owns journal creation. The response must include journal ID, status, posted timestamp and correlation reference. A timeout is reconciled by idempotency lookup, never by blindly resubmitting a new journal.

### 7.3 Payment and statutory remittance

Payroll approval/posting is not payment. Payment proposal is not bank release. Remittance calculation is not statutory filing or settlement.

Maintain separate states and references for:
1. payroll approved;
2. payroll journal posted;
3. payment proposal created;
4. authorized treasury/bank release;
5. bank settlement confirmed;
6. PAYE/pension remittance proposal;
7. authorized remittance submission/payment;
8. authority/bank receipt captured;
9. reconciliation completed or exception raised.

Match legal entity, period, payee/authority, currency, amount, external transaction reference and receipt evidence. Partial settlement, rejection, bank fees, duplicate bank messages, reversed payments and late receipts must be representable without rewriting payroll history.

---

## 8. Audit, privacy and retention

Every material command records actor/service identity, tenant, legal entity, capability, correlation ID, idempotency key, timestamp, before/after version references, reason, source evidence references and outcome. Audit history is append-only to application roles; privileged database access is separately controlled and monitored.

Mandatory audit events include employee/contract/assignment changes, statutory evidence upload/review/publication, component classification, payable-time approval, payroll input freeze, calculation, review, approval, posting request/result, payment/remittance proposals, settlement imports, reconciliation, adjustment/reversal, activation changes, access denials and evidence digest mismatch.

Apply data minimization and least privilege:
- employees see only their own payslips and permitted HR records;
- managers see only assigned scope and fields;
- payroll staff see required payroll fields;
- Finance sees journal/liability fields, not unrestricted HR documents;
- auditors receive read-only scoped evidence;
- agents receive purpose-limited redacted read models.

Define retention/deletion by applicable law and approved records policy. Legal holds and posted financial records cannot be silently purged. Sensitive values are masked in UI, logs, exports and agent contexts. All access to bank and statutory identity data is auditable.

---

## 9. Angular workbenches and user journeys

Build accessible, typed Angular workbenches with server-side authority and clear loading, empty, error, conflict and held states.

1. Employee directory/profile and controlled onboarding.
2. Contract and revision workflow with evidence.
3. Assignment, position and reporting reference view from WP-03.
4. Time-event import, duplicate/outlier review and payable-time approval.
5. Compensation catalogue, classification and evidence review.
6. Payroll period readiness and exception dashboard.
7. Draft calculation, line-level explanation and variance review.
8. Maker-checker approval inbox and immutable decision history.
9. Employee payslip portal with strict self-access.
10. Finance posting status, liability view and GL reconciliation.
11. Treasury payment proposal status and settlement matching.
12. PAYE/pension remittance evidence and reconciliation.
13. Audit/evidence viewer with scoped access and digest status.

The UI must not expose an action the API would reject as a substitute for authorization. UI hiding is usability only; API and database controls enforce security.

---

## 10. WP-15 agent integration boundary

Agents may detect, summarize, compare and propose. They do not autonomously:
- hire, terminate, transfer or change employee status;
- create/approve position appointments or DoA rules;
- determine binding legal classifications or activate statutory rules;
- approve payroll or release payroll;
- post journals or release bank payments;
- submit statutory filings or certify remittances;
- alter posted history or bypass maker-checker controls.

Expose typed, scoped, auditable tools for data-quality findings, payroll pre-audit, legal-source change proposals and reconciliation exceptions. Treat documents and extracted text as untrusted data. Agent output is a proposal requiring the owning workflow's authorized actor. Enforce tool allowlists, tenant/legal-entity scope, redaction, time-bound access, prompt-injection tests, replay protection and deterministic non-AI fallback.

---

## 11. Engineering implementation backlog

The following sequence is the required build order within WP-14, subject to the overall WP-03 → WP-04–WP-13 → WP-14 package sequence.

| ID | Build increment | Required implementation and proof |
|---|---|---|
| WP14-ENG-01 | Source and migration reconciliation | 57-commit inventory, canonical path ownership, migration DAG, conflict decisions, storage mapping |
| WP14-ENG-02 | Canonical HR schema | Person/employee/contract/assignment lifecycle, constraints, effective dates, safe migration and backfill |
| WP14-ENG-03 | Evidence service integration | Canonical document asset references, immutable object version, byte-level digest verification, access audit |
| WP14-ENG-04 | HR APIs and policies | Typed contracts, scoped authorization, field privacy, negative tenant/legal-entity tests |
| WP14-ENG-05 | Time and compensation inputs | Immutable raw events, approved payable time, deduplication, component classification/evidence |
| WP14-ENG-06 | Statutory rule engine | Effective-dated source snapshots, qualified evidence binding, exact arithmetic, fail-closed rule resolver |
| WP14-ENG-07 | Payroll calculation lifecycle | Frozen inputs, deterministic snapshots, idempotent calculation, immutable posted history |
| WP14-ENG-08 | Concurrency and security hardening | Unique constraints, locking, race/replay tests, RLS, API/RLS bypass attempts, audit and privacy |
| WP14-ENG-09 | WP-13 Finance integration | Balanced journal contract, liability mapping, outbox/retry/idempotency, duplicate-posting prevention |
| WP14-ENG-10 | Payment/remittance reconciliation | Separate proposals, authorized release boundary, external settlement/receipt matching and exceptions |
| WP14-ENG-11 | Angular HR/payroll workbenches | Accessible typed UI, employee portal, exception/review/approval and reconciliation flows |
| WP14-ENG-12 | WP-15 agent tools | Read-only scoped views, proposal contracts, prompt-injection and action-boundary tests |
| WP14-ENG-13 | WP-16 release evidence | Exact-SHA CI, security/performance/recovery, UAT, installer and operational runbooks |

Each increment must update requirement traceability, source map, test evidence and handover register. A row is complete only when implementation and its acceptance evidence are committed and verified.

---

## 12. Mandatory engineering test matrix

| Test group | Minimum cases and expected invariants |
|---|---|
| Migration | Empty install, canonical upgrade, candidate reconciliation, ordering, duplicate migration, interrupted migration, resumable backfill, recovery |
| HR lifecycle | Duplicate employee number, overlapping contracts/assignments, expired appointment, invalid legal entity, retroactive change, termination/rehire history |
| API authorization | Anonymous, wrong tenant, wrong legal entity, wrong site, wrong employee, role escalation, field overexposure, self-approval |
| RLS | Cross-tenant SELECT/INSERT/UPDATE/DELETE, missing tenant context, forged tenant context, service role scope, connection-pool context leakage |
| Evidence | Missing object, wrong tenant, changed bytes, wrong digest, stale version, unsupported file, malformed PDF, oversized upload, malicious content quarantine |
| Calculation | Both ETB 30,000 candidate regressions, band boundaries, rounding ties, precision, effective-date boundaries, exemptions, pension coverage, employer contribution separation |
| Input validation | Float rejection, excess decimals, invalid currency, duplicate time event, unsupported component, client-supplied total, stale rule version |
| Concurrency | Two simultaneous period opens, duplicate line creation, simultaneous calculation, edit-vs-approve, approve-vs-post, retry after timeout |
| Idempotency | Same key/same payload, same key/different payload, duplicate event delivery, queue retry, replayed posting response |
| Accounting | Debit/credit balance, liability mapping, closed period, missing account, duplicate journal, posting timeout and lookup recovery |
| Remittance | Partial/duplicate/reversed settlement, wrong period, wrong payee, wrong currency, missing receipt, amount mismatch, late receipt |
| Audit/privacy | Complete event chain, immutable history, masked logs, employee self-access, manager scope, auditor read-only, agent redaction |
| Agent safety | Prompt injection in contract/PDF, poisoned source text, hallucinated citation, cross-tenant query, forbidden write tool, replay |
| Recovery | Backup/restore, outbox replay, queue recovery, adjustment/reversal, historical snapshot stability after rule update |
| Frontend | Accessibility, keyboard, permission boundaries, stale form conflict, server validation, no client statutory calculation |

### Required named Ethiopia regression preservation

Keep the existing release-gate cases ETH-PAY-001 through ETH-PAY-017 and add:
- concurrent run creation and period transition;
- RLS isolation under pooled connections;
- evidence-object byte mutation after approval;
- duplicate and out-of-order time events;
- calculation snapshot reproducibility;
- approval invalidation after any material input/rule change;
- payroll-to-GL balance and duplicate posting;
- payment and statutory remittance settlement reconciliation;
- cross-tenant employee self-service denial;
- agent prompt injection and prohibited action rejection.

A test must assert observable persisted state and authorization result, not merely that a method returned without throwing.

---

## 13. Engineering acceptance and evidence contract

A WP-14 increment is engineering-accepted only when its exact commit SHA has:

1. requirement-to-source-to-test traceability;
2. reviewed source/migration ownership and no duplicate canonical master;
3. successful clean-install and supported upgrade migrations;
4. backend unit, feature and integration tests;
5. PostgreSQL constraints and RLS positive/negative tests;
6. API authorization, privacy and field-level access tests;
7. concurrency, idempotency and retry/replay tests;
8. payroll calculation and immutable snapshot tests;
9. WP-13 journal and liability integration tests;
10. payment/remittance reconciliation tests;
11. Angular tests/build and accessibility checks;
12. dependency, secret and security scans;
13. operational metrics, alerts, backup/restore and runbooks;
14. exact-SHA CI evidence and reproducible build record;
15. package dependency and WP-16 traceability updated.

The GitHub evidence record must identify the commit SHA, workflow run/check IDs, test commands, environment/database version, result summaries, artifact references and any infrastructure-only interruption. A workflow that never reaches the test steps is **not** a test pass or an application failure; it is an infrastructure-blocked result requiring a reliable retry. No gate is waived to compensate for runner limitations.

No extra developer acceptance letter is required. The committed evidence record and mandatory engineering checks constitute the developer-side engineering proof. Legal conclusions remain evidenced by the qualified legal/professional records required above.

---

## 14. Release boundaries and operational invariants

- No live payroll activation from this design commit.
- No employee, salary, bank or legal evidence data copied from sample systems or fixtures into production.
- No payment, bank release, tax filing or pension remittance triggered by calculation or approval.
- No agent direct database, posting, payment, statutory filing or rule activation authority.
- No rewrite of posted payroll or historical journals when rules change.
- No default approval thresholds, assumed job titles or inferred legal authority.
- No RLS bypass for ordinary application or agent identities.
- No evidence acceptance based only on a filename, URL, client hash, test fixture or explanatory note.
- No claim of test pass without exact-SHA execution evidence.

## 15. Definition of done

WP-14 is complete when the canonical HR/employee lifecycle, contract and assignment integration, time and compensation inputs, Ethiopia statutory rule engine, server-side payroll, approval workflow, Finance journal/liability integration, payment/remittance reconciliation, Angular workbenches, audit/privacy controls and WP-15 interfaces are implemented and satisfy the complete engineering acceptance matrix on the exact release candidate SHA.

Production activation additionally requires the evidence-backed legal/professional decisions and operational controls defined by the existing WP-14 legal engineering gate and WP-16 release process. These are evidence requirements, not a request for redundant personal developer acceptance.

**Current artifact status:** committed design/build specification. Runtime implementation, CI, migration reconciliation, tests and production activation are not certified by this document.
