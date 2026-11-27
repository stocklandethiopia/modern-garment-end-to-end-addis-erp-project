# WP-03 Enterprise Foundation — Engineering and Architecture Low-Level Design

**Document ID:** WP03-LLD-ORG-IAM-001  
**Version:** 1.0 · **Date:** 27 November 2026  
**Status:** Engineering design baseline for implementation and verification; not package completion  
**Active package:** WP-03  
**Downstream dependencies:** WP-04–WP-16; payroll localization note is a WP-14 dependency and does not reorder the package sequence

## 1. Executive design statement

WP-03 establishes the shared enterprise foundation for the customer-neutral garment operating system. It makes every downstream operation resolve the correct tenant, legal entity, organizational unit, site, operational node, channel, identity, position, scope and authority without conflating those concepts.

The approved pattern is a PostgreSQL-backed, tenant-isolated Laravel modular monolith with Angular web client, versioned REST/OpenAPI contracts and transactional outbox. PostgreSQL is authoritative. Redis is non-authoritative cache/queue/coordination. External adapters and specialist calculation services use versioned contracts and cannot bypass domain services or authorization.

The organogram is part of WP-03 because a company position, actual human appointment, system identity and transaction authority must be resolved before procurement, finance, manufacturing, quality, retail, HR or payroll workflows can safely rely on them.

**This is a design artifact, not proof that all WP-03 has been implemented, tested or accepted.** The canonical delivery branch has tenant, legal entity, site, operational-node, inventory-location, membership, invitation, role, audit, platform lifecycle and entitlement foundations. PR #21 is an isolated, unmerged, not-yet-verified runtime candidate adding organizational units, job-position slots, unit manager-position mappings and reporting relationships. Human appointments, complete entity/site resource-scope enforcement and the versioned DoA engine remain open. The most recent required GitHub workflows for PR #21 failed before job steps/logs were available; tests were therefore NOT RUN, not passed.

### 1.1 Target outcomes

1. Every business record is tenant-owned; every legally significant transaction is explicitly legal-entity-scoped.
2. Site, operational node, branch registration, warehouse/location and commercial channel remain distinct.
3. Login identity is not the person/employment record, and neither is a job position.
4. Position appointments and reporting relationships are effective-dated and historically reproducible.
5. Approval authority is resolved server-side from a valid appointment, scope and effective policy—not a title or generic administrator role.
6. Tenant isolation is enforced in the API and PostgreSQL RLS, including jobs, exports and integrations.
7. Business changes write audit and outbox evidence atomically.
8. Agents may search, explain and propose; they cannot appoint, approve, post, pay, file or activate statutory rules.
9. Ethiopian legal facts are represented as source-backed applicability and review records; unresolved law defaults to REVIEW REQUIRED and blocks the affected operation.
10. The fixed WP-00–WP-16 sequence is preserved. WP-03 remains active; WP-14 remains later.

## 2. Evidence and source hierarchy

| Evidence label | Meaning |
|---|---|
| VERIFIED SOURCE | Inspected in repository or official source; exact file or URL recorded |
| NOTION BASELINE | Product, governance, sample-review or acceptance wording fetched from the project Notion workspace |
| IMPLEMENTED — NOT VERIFIED | Code exists on an isolated/unmerged branch, but exact-head tests have not executed and passed |
| ENGINEERING INFERENCE | Design consequence of approved architecture; not a newly discovered legal or customer fact |
| RECOMMENDATION | Technical control within the approved scope |
| TBD / REVIEW REQUIRED | Missing customer fact, source text, legal interpretation or verification evidence |
| CONFLICT | Inspected sources disagree; preserve the conflict until resolved |

### 2.1 Governing project sources

**Notion records inspected**
- Project Governance Agreement — One Goal, Express Approval and Numbered Handover.
- Garment Expert Product Goal — Initiative-to-Sell-Through Operating System.
- Stockland ERP — Functional Acceptance Runbook.
- Garment ERP Reference Study — awesome-garment-erp and Scan ERP CMT Toolkit.
- Modern Garment ERP — Agent-Native Technology Integration Standard.
- End-to-End ERP Technology & Intelligent Experience Blueprint — Production to Customer.

**Canonical repository sources**
- docs/architecture/tenant-organization-identity.md
- docs/architecture/platform-standard.md
- docs/engineering/code-quality.md
- docs/security/saas-security.md
- docs/localization/ethiopia.md
- docs/localization/ethiopia-legal-regulatory-register.md
- docs/requirements/requirements-traceability.md
- docs/verification/WP03-implementation-acceptance-matrix.md
- docs/work-packages/handover-register.md
- AGENTS.md and docs/agent/project-governance-agreement.md

**New design inputs inspected**
- docs/organization/enterprise-organogram-and-delegated-authority.md
- docs/work-packages/wp03-wp14-organogram-payroll-implementation-plan.md
- docs/localization/ethiopia-payroll-localization-2026-11-27.md
- docs/localization/ethiopia-payroll-engineering-release-gate.md
- PR #21 organization runtime candidate and its exact-head evidence.

Sample repositories, code snippets, UI patterns, old product tables and payroll vectors are comparative inputs only. They do not authorize copying schemas, code, product version identifiers, tax methods or deductions into this greenfield ERP. Verify source licensing before any code reuse. Official legal instruments and competent Ethiopian authorities outrank sample software, vendor blogs and non-binding commentary.

## 3. Scope and boundaries

### 3.1 WP-03 owns

- Tenant lifecycle, tenant settings and product-level entitlement boundary.
- Legal entities and statutory identifiers.
- Business units, organizational units, positions, reporting lines and organizational manager-position relationships.
- Identity separation contracts and integration points to People & Payroll.
- Position appointment and acting/delegation foundations.
- Entity/site/department/warehouse/channel scopes and fail-closed authorization.
- Company-specific organogram and versioned Delegation of Authority foundations.
- Branch registration and site/activity/channel licensing evidence references.
- Audit, idempotency, transactional outbox/inbox and integration boundaries.
- Administrative and organization workbenches, operator runbooks and complete WP-03 acceptance.

### 3.2 Remains in later packages

- Employee, contract, time, leave, benefits and payroll implementation belongs to WP-14.
- Journals, AP/AR, tax, treasury and GL execution belongs to WP-13.
- Full sourcing and PO business workflow belongs to WP-07; WP-03 provides identity and authority foundations.
- Manufacturing, quality, warehouse, retail, product and production transactions belong to their respective later work packages.
- Live statutory activation, payroll payment, tax filing, pension remittance, bank release and production deployment remain human/professional gates.

### 3.3 Non-negotiable separations

| Object | Meaning | Not equivalent to |
|---|---|---|
| Tenant | SaaS customer data, configuration, users and policy boundary | Legal entity or company position |
| Legal entity | Registered body with legal/tax/accounting identity | Tenant, site, brand or channel |
| Business unit | Management/profit/cost responsibility | Separate legal person unless legally constituted |
| Org unit | Department/function/site-team hierarchy | User role or employee record |
| Business site | Physical or registered premises | Warehouse or stock ledger |
| Operational node | Factory, DC, depot, branch, store, office or fulfilment function | Automatically licensed activity |
| Branch registration | Evidence-backed legal registration | A branch-node label |
| Inventory location | Stock ledger place inside an authorized stock-holding node | A site address |
| Channel | Retail, wholesale, reseller, D2C, marketplace, B2B, export | Legal entity or warehouse |
| Person | Human identity | Login identity or payroll result |
| Employee/employment | Legally relevant employment relationship | Every user, contractor, creator or supplier |
| Job position | Approved job slot/post | Person, user, incumbent or permission |
| Position appointment | Effective-dated human assignment | Tenant membership |
| User identity | Authentication principal | Employee or job position |
| Tenant OWNER/ADMIN | SaaS tenant administration authority | CEO, Board Chair, CFO or PO approver |
| Domain role | Capability assignment | Proof of company appointment |
| DoA authority | Approved transaction power with scope, limit and effective date | Title, reporting line or administrator status |

A downstream transaction independently resolves tenant; legal entity when legal/accounting obligation exists; site/node when physical operation occurs; commercial account/channel when relevant; and valid actor authority. A single branch field cannot answer all these questions.

## 4. Target architecture

### 4.1 Stack and deployment pattern

| Layer | Target responsibility | Control |
|---|---|---|
| Angular 22 / TypeScript 6 | Role-aware workspaces, forms, approval inbox and human interaction | Client is untrusted; API owns validation and permissions |
| Laravel 13 / PHP 8.5 | Application services, domain policies, APIs and orchestration | Thin controllers; service layer owns invariants and transactions |
| PostgreSQL 18 | Tenant/business system of record, RLS, constraints, audit and outbox | Runtime role is non-owner, non-superuser, NOBYPASSRLS |
| Redis 8.2 | Cache, queue and short-lived coordination | Never authority for committed business facts |
| Transactional outbox/inbox | Versioned event publication and idempotent consumption | Aggregate + audit + outbox commit atomically |
| Private object storage | Legal evidence, contracts, identity and registration documents | Checksums, classification, retention and restricted access |
| Python specialist workers | Forecasting, optimization and document/analytics work through contracts | No direct writes to operational/financial transactions |
| Observability | Structured logs, metrics, traces and correlation | Minimize personal data; never log tokens/secrets |

The selected application style is a modular monolith. Domain modules own write models and expose application services, API contracts and versioned events. There is no unrestricted cross-module table write. Database-per-tenant may be separately evaluated for exceptional regulatory isolation or scale, but is not the default or current implementation.

### 4.2 Logical topology

~~~mermaid
flowchart LR
  UI[Angular workspace] --> API[Laravel API]
  API --> IAM[Identity and tenant context]
  API --> ENT[Enterprise and organization]
  API --> AUTH[Position and DoA resolver]
  API --> DOM[Domain application services]
  IAM --> PG[(PostgreSQL 18)]
  ENT --> PG
  AUTH --> PG
  DOM --> PG
  DOM --> AUD[Audit events]
  DOM --> OUT[Transactional outbox]
  OUT --> Q[Redis queue]
  Q --> WRK[Laravel workers]
  WRK --> ADP[External adapters]
  WRK --> ANA[Python specialist services]
  PG --> READ[Reports and read models]
~~~

### 4.3 Trust boundaries

- Browser, imports, AI outputs, integrations and devices are untrusted inputs.
- API requests resolve into transaction-local tenant and actor context after authentication.
- Background jobs carry explicit tenant, actor/system identity, correlation and authorization evidence; queue payload is not permission.
- Runtime DB role has no superuser, table ownership or RLS bypass.
- Analytics receives minimized, permission-filtered snapshots, not unrestricted operational credentials.
- Credentials are held in managed secret storage and referenced by identifier; secret values never enter source, logs, events or client bundles.
- External providers and AI agents cannot connect to PostgreSQL directly.

## 5. Bounded contexts and ownership

| Context | Authoritative ownership | Package relationship |
|---|---|---|
| Tenant & Enterprise | Tenant, legal entity, business unit, org unit, site, operational node, branch/licence evidence | WP-03 |
| Identity & Access | Global user, credentials, tenant membership, MFA/SSO, capability roles, scope, sessions | WP-03 |
| People & Payroll | Person, employee, employment contract, appointment evidence, time, leave, skills, payroll, benefits | WP-14; consumes WP-03 IDs |
| Finance & Treasury | Legal books, journal, AP/AR, costing, tax, budget, bank mandate, payment | WP-13; consumes scope/DoA |
| Sourcing & Procurement | Supplier, RFQ, sourcing, requisition, PO, contract and receipt orchestration | WP-07; consumes DoA |
| Inventory & WMS | Stock ledger, location, reservation, movement, handling units | WP-12 |
| Manufacturing & IE | Work centres, routes, operations, WIP and labor/production events | WP-06, WP-08–WP-10 |
| Quality & Compliance | Inspection, hold, release, CAPA, HSE | WP-11 |
| Commerce & Distribution | Customer/account/channel, orders, allocation, fulfilment, POS, delivery, returns | WP-12 |
| Legal source registry | Instrument bytes, authority, article, amendments, effective dates and professional review | WP-02; consumed downstream |
| Platform & Integration | Outbox, inbox, adapters, event registry, idempotency, agent boundary | WP-03/WP-15 |

A downstream context consumes stable identifiers and published APIs/events. It may not write another context's tables or reimplement its approval resolver.

## 6. Canonical information model

This is the logical model, not a command to create every table immediately. Before each migration, reconcile names, constraints, existing tables, data ownership and approved requirement traceability.

### 6.1 Enterprise and location master

| Entity | Core fields / relationships | Invariant |
|---|---|---|
| Tenant | id, code, status, locale, timezone, lifecycle, entitlement reference | One root boundary; unique code; controlled lifecycle |
| Tenant settings | tenant_id, key, JSON value, version, validity | Tenant-scoped; secrets are references, not values |
| Legal entity | registration number, legal name/form, country, identifiers, base currency, status | Separate statutory/accounting body |
| Business unit | parent, code, responsibility type, cost/profit responsibility | Does not create legal personality |
| Org unit | parent, unit type/code/name, legal entity/site context, validity | Tenant-safe; no hierarchy cycle |
| Business site | legal entity, code/type, address revision, timezone, geography, status | Premises; not inventory by default |
| Operational node | legal entity, site, node type, capabilities, status | Type/capability explicit |
| Branch registration | legal entity, site, registration reference, activity codes, authority, validity, evidence | Legal evidence separate from branch node |
| Business activity | activity code, industry classification, source version | Controlled master with provenance |
| Entity licence | legal entity, site, activity, licence, issuer, validity, evidence, review state | Applicability scoped and time-bounded |
| Node capability | operational node, capability, evidence, validity | Stock, manufacturing, retail and fulfilment explicitly granted |
| Inventory location | node, parent, code/type, stock flag, status | Only authorized stock-holding node |
| Brand/channel | owner or licence, channel type, seller entity, policy version | Does not replace entity/site/customer |
| Customer/partner | account class, party identity, channel/entity relationship, contract | Customer, reseller, supplier, creator and fulfilment partner stay distinct |

### 6.2 Identity and organization

| Entity | Core fields / relationships | Invariant |
|---|---|---|
| User | global identity, normalized login, status, provider subject | Not tenant-specific employee row |
| Tenant membership | tenant_id, user_id, role, status, validity, audit | Membership and tenant context checked every request |
| Identity credential | provider, subject, verification, status, MFA capability | No plaintext secret storage |
| Role/permission definitions | capability codes, version, resource/action | Capability is not job title |
| Membership role assignment | membership, role, tenant/entity/site scope, validity | Role and resource scope independently resolved |
| Person | person identity and restricted PII | Owned by People & Payroll |
| Employment record | employer legal entity, worker class, contract type, start/end, legal evidence | Legal employer explicit |
| Job position | stable code, org unit, title, grade, family, cost centre, status, validity | May be vacant; not an incumbent |
| Reporting relation | subject, manager, line type, validity, reason | Solid-line and functional distinct |
| Position assignment | position, person/employee, assignment kind, FTE, validity, approval/evidence | Primary, acting, secondment, temporary; no ambiguous overlap |
| Position scope assignment | appointment and entity/site/department/cost-centre/warehouse/channel scope | No implicit tenant-wide authority |
| Authority delegation | delegator, delegate, action, scope, limit, validity, approval, revocation | Narrow, time-limited and revocable |

**Data-model correction:** an earlier draft data dictionary included an org-unit owner-user shortcut. The organogram design rejects static user ownership as management authority. Use a manager-position relationship and resolve the human incumbent as of the transaction date. Do not backfill user IDs into position assignments without source evidence and approval.

### 6.3 Delegated authority and approvals

| Entity | Core fields | Invariant |
|---|---|---|
| DoA policy | tenant, legal entity/scope, policy code/version, effective period, status, approver/evidence | Versioned, customer-approved |
| DoA rule | policy, transaction type, scope, amount basis, currency, cumulative limit, budget/risk, approval path | No guessed global thresholds |
| Approval template | sequence/parallel groups, position requirements, escalation, deadline | Immutable version; explicit SoD |
| Approval request | aggregate type/id/version/hash, entity/scope, amount/currency, budget/risk, requester, policy snapshot | No client-provided authority |
| Approval step | request, sequence, required authority, resolved appointment/delegate snapshot, status | All required steps resolved |
| Approval decision | step, actor, decision, reason, timestamp, authority proof | Append-only; conflict checks |
| Approval event | request state, actor/system, event ID, correlation, policy/version | Audit and replay evidence |

## 7. Identity and authority resolution

Every protected command uses a server resolver. Inputs: authenticated actor, target tenant, legal entity, site, resource scope, action code, transaction version, amount/currency, budget and action date.

Resolution sequence:

1. Authenticate actor, token and session; reject disabled, expired, revoked or insufficient-strength sessions.
2. Resolve active tenant membership and active tenant lifecycle.
3. Resolve target aggregate through tenant RLS and tenant-composite foreign keys.
4. Determine target legal entity, site/node, department/org unit, cost centre, channel and record classification from server-side records.
5. Resolve person only when the action requires a human appointment; a login user may be a system operator or non-employee.
6. Resolve effective-dated position assignment(s) for the action date; distinguish primary, acting, temporary and secondment.
7. Resolve domain capability and target resource scope independently.
8. Resolve active policy version and matching rules for tenant/entity/site/activity/transaction amount/currency/budget/risk/date.
9. Calculate approval steps, conflict/SoD and delegation validity. Requester/preparer cannot be prohibited approver even if they have other roles.
10. Return deterministic allow/deny/pending reason codes with matched rule, policy and evidence IDs. Missing evidence denies or holds. Never fall back to OWNER, ADMIN, generic CEO or a client-supplied approver.

The browser may simulate a candidate approval path for review. The final command is re-evaluated inside the service transaction immediately before the decision is stored.

## 8. Configurable organogram and governance

### 8.1 Governance pattern

The ERP provides a configurable template, not a mandatory chart:

- Shareholders / General Meeting where the legal form provides for it.
- Board, Chairperson, optional Vice-Chairperson, directors and optional committees where legally constituted or appropriate.
- CEO / Managing Director / President or customer-specific equivalent.
- Executive management (COO, CFO, CHRO, CPO, CCO, Product/Design/Merchandising, CIO/CTO, Legal/Compliance, Quality/HSE/Sustainability).
- Head-office specialist functions.
- Factory/plant, DC/depot, wholesale, branch/store and digital channel nodes as the actual business needs.

Board Chair and CEO are separate by default. Board-reserved matters, combined roles and legal restrictions depend on the customer's legal form, constitutional documents, shareholder resolutions and applicable law. Do not pre-create every office, committee or title for every tenant.

### 8.2 Operating-area reference positions

| Area | Configurable example roles/functions | Key control |
|---|---|---|
| Corporate governance | Shareholders, General Meeting, Board, Chair, directors, committees | Legal form/articles drive applicability |
| Executive | CEO/MD, COO, CFO, CHRO, CPO, CCO, CIO/CTO, General Counsel, Quality/HSE | Titles never grant authority by themselves |
| Finance | Controller, Chief Accountant, FP&A, AP, AR, cost accountant, Treasury | AP prep, journal, bank release and PO approval separated |
| Procurement | Sourcing/category manager, buyer, technical evaluator, PO preparer | Buyer cannot approve own PO |
| Factory | Factory GM/Plant Manager, Production, IE, PPC, QA, Maintenance, Stores, site HR, Finance, HSE | Quality hold independent from production pressure |
| Distribution | Regional/area manager, DC manager, inbound, stock control, dispatch, transport, returns | Custody, location and accounting distinct |
| Retail/branch | Store manager, supervisors, sales, cashier/POS, service, stock, visual merchandising | Cash, refund, discount, void, count and adjustment separated |
| Corporate/shared | Legal, Internal Audit, Compliance, IT/security, data/analytics, administration | Independent oversight and least privilege |

### 8.3 Reporting semantics

- SOLID_LINE: accountable line-management relationship.
- FUNCTIONAL: dotted-line/specialist relationship; does not imply disciplinary or transaction authority.
- PROJECT: time-bound project reporting/assignment.
- APPOINTMENT/ACTING: human incumbency/temporary responsibility; separate assignment record.
- Approval route: distinct and never inferred from reporting edge alone.

A graph check evaluates overlapping effective intervals. Future-effective appointments must not create a current cycle; a cycle over intersecting valid periods is prohibited. One primary manager per subject position per effective period unless approved exception policy allows otherwise. Functional links may be multiple with explicit scope.

## 9. DoA and segregation of duties

### 9.1 DoA policy attributes

A DoA policy/rule may constrain tenant/legal entity, site, department, cost centre, project, brand, channel, transaction type, single amount, cumulative exposure, currency, budget availability, risk/materiality, requestor/preparer/reviewer/approver/release actor, number/order of steps, escalation, delegation, expiry and emergency path.

Policy states: DRAFT, UNDER_REVIEW, APPROVED, ACTIVE, SUPERSEDED, EXPIRED, SUSPENDED. Policy changes are versioned. Company limits are customer-configured and signed; no threshold is hardcoded. Tenant OWNER/ADMIN cannot alter a live company approval policy simply because they administer tenant accounts.

### 9.2 Purchase-to-pay example

1. Requester raises requisition with need, specification, entity, site, cost centre and budget.
2. Budget service validates available and committed funds.
3. Procurement conducts sourcing/quotation and commercial evaluation; technical evaluation is separate where relevant.
4. Buyer prepares PO draft; maker/checker validates supplier, price, quantity, currency, tax, delivery and evidence.
5. DoA resolver selects ordered/parallel approvers from signed customer policy, amount, cumulative exposure, budget, supplier/risk category, legal entity and scope.
6. PO is issued only after all required approvals. Material supplier/price/quantity/currency/specification/delivery change creates a new version and reopens affected approvals.
7. Independent receiver records actual receipt; Quality inspects and holds rejected/quarantined items.
8. AP matches PO, receipt and invoice (3-way; 2-way only for approved service/contract cases). Duplicate and variance exceptions are held.
9. Treasury prepares payment; bank release follows current legal-entity bank mandate/signatory rules, separate from PO approval/AP entry.
10. Finance reconciles commitment, receipt, AP, payment, inventory and GL.

### 9.3 Incompatible duties

Requester/preparer vs final approver; buyer/PO preparer vs final PO approver; receiver vs quality acceptor; AP recorder vs bank payment releaser; payment initiator vs sole bank releaser; physical counter vs sole stock-adjustment approver; payroll preparer vs payroll approver; payroll approver vs bank release; agent/proposal creator vs human approver. Headcount combinations require an approved compensating control and recorded independent review.

## 10. API and event contracts

### 10.1 API principles

- Versioned REST resources under /api/v1.
- Tenant context comes from authenticated session and verified selection; client tenant ID is never trusted alone.
- Controllers validate DTOs and invoke domain services; service owns locks, invariants, transaction, audit and outbox.
- Lists are paginated and tenant-filtered in SQL.
- Mutations use Idempotency-Key for retriable external commands.
- Client cannot supply actor, approver, appointment, policy version, scope or legal classification as trusted values.
- Stable errors, correlation, schema version and OpenAPI 3.1.
- No success response until transaction outcome is known.

### 10.2 Existing candidate API family

PR #21 currently registers (unmerged, not test-verified):
- GET/POST /api/v1/enterprise/organization-units
- GET/POST /api/v1/enterprise/job-positions
- GET/POST /api/v1/enterprise/organization-units/{unitId}/manager-assignments
- GET/POST /api/v1/enterprise/position-reporting-relations

Target extensions, subject to source reconciliation and OpenAPI acceptance:
- Position appointments (People & Payroll owns employee/person data).
- As-of-date organization tree.
- DoA policy lifecycle.
- Approval request/decision workflow.
- Authority delegation and revocation.
These are target API concepts, not verified live routes. Final schema/error/auth/pagination/idempotency/deprecation contract must precede implementation.

### 10.3 Event contract

| Event | Owner | Consumer / purpose |
|---|---|---|
| OrganizationUnitCreated/Changed/Deactivated.v1 | Enterprise | Reporting, cost centre and domain read models |
| JobPositionCreated/Changed/Deactivated.v1 | Enterprise | People, approval resolver, workforce planning |
| PositionAppointmentStarted/Changed/Ended.v1 | People & Payroll | IAM role provisioning, DoA resolver, HR history |
| PositionReportingRelationCreated/Ended.v1 | Enterprise | Org view and manager inbox |
| DoAPolicySubmitted/Approved/Activated/Superseded.v1 | Governance/Enterprise | Domain authorization consumers |
| AuthorityDelegationGranted/Revoked/Expired.v1 | Enterprise/People | DoA resolver and audit |
| ApprovalRequested/StepDecided/Approved/Rejected/Expired.v1 | Approval service | Procurement, Finance, Quality, HR domain workflows |
| LegalSourceVersionRegistered/ReviewChanged.v1 | Legal register | Policy owners and legal holds |

Events are committed to outbox with aggregate/audit. Consumers are idempotent by event ID, preserve tenant/scope/version and never treat event arrival as approval.

## 11. Security, privacy and database controls

1. Every tenant-owned table has tenant_id, unique (tenant_id,id), composite tenant FKs, forced RLS and explicit runtime grants.
2. app_runtime is non-owner, NOBYPASSRLS; no arbitrary tenant/user context mutation.
3. Middleware sets transaction-local tenant and actor after authentication/membership checks; jobs establish equally explicit context.
4. Missing/invalid context returns zero rows/denial.
5. API enforces every read/write/export/search/attachment/download/job. UI hiding is not security.
6. Person, employee, payroll, tax ID, bank, health/safety and restricted employee relations use purpose limitation, masking and least privilege.
7. Audit retains actor, action, object, tenant/scope, reason, correlation, timestamp and safe before/after evidence. Do not claim tamper-proof without tested hash chain or equivalent.
8. History is immutable where required; correct by versioned change or compensating event, not rewrite.
9. Evidence documents are private, checksummed and linked to the exact source/appointment/authorization.
10. Tests cover cross-tenant, cross-entity, stale role, expired appointment, invalid scope, missing RLS context, queue replay and user/agent privilege escalation.

## 12. Angular workbenches

- Organization tree with entity/site context, as-of selector and history.
- Unit catalogue/detail with parent, children, positions and manager-position history.
- Position catalogue with vacancy, title, grade, family, cost centre, site and status.
- Appointment workflow (People & Payroll-owned), with restricted person data.
- Reporting-line editor distinguishes solid/functional/project links and previews cycles.
- DoA policy simulator with explainable matched rules/steps; simulation cannot commit approval.
- Approval inbox shows request version, matched policy, authority scope, decision history and action.
- Exports honour authorization and suppress private payroll/HR data.
- Accessibility: keyboard, focus order, captions, semantic labels, error summaries, screen-reader, mobile/tablet.
- Client does not decide incumbent, legal effectiveness, authority, tax/pension classification or payment.

## 13. Agent integration boundary

Operational ERP agents use tenant-scoped, capability-specific application tools, not SQL or raw table CRUD. Codex/developer agents operate only in developer branches and never act as production business users.

| Tier | Permitted posture |
|---|---|
| A0 | Public/non-sensitive explanations |
| A1 | Permission-filtered read/search/summarize |
| A2 | Draft/proposal with owner and expiry |
| A3 | Human-approved action through domain service after independent decision |
| A4 | Prohibited agent execution: employee appointment/termination, payroll approval/posting, journals, bank payment release, statutory filing/activation and destructive production data changes |

Uploaded material, prompt content and external connector text are untrusted data. Tool allowlists, schema validation, provenance, proposal hashes, independent approver identity, expiry, replay protection and audit are required. No model is an approver.

## 14. Downstream work-package dependencies

| Package | WP-03 contract consumed |
|---|---|
| WP-04 | Brand/assortment/channel ownership, entity/site seller, position/scoped approval |
| WP-05 | Product release authority and reviewer scope |
| WP-06 | Site/work-centre and authorized IE roles |
| WP-07 | DoA resolver for requisition/sourcing/PO; requester/buyer/receiver separation |
| WP-08/09 | Site/unit/position context; manufacturing transactions remain manufacturing-owned |
| WP-10 | Position/appointment/skills contract; people facts remain HR-owned |
| WP-11 | Independent quality authority and site/activity legal controls |
| WP-12 | Operational node/location/capability/channel/entity scope; retail licensing gate |
| WP-13 | Legal entity, currency, budget, bank mandate, AP/GL approval |
| WP-14 | Person/employment/appointment, payroll authorization, privacy and Ethiopia law gates |
| WP-15 | Tenant-safe APIs/events, restricted tools and agent action boundaries |
| WP-16 | End-to-end scope, DoA, RLS, audit, reliability, UAT, legal activation and recovery evidence |

## 15. Acceptance and verification

| ID | Scenario | Required result |
|---|---|---|
| WP03-AT-001 | Tenant isolation | No cross-tenant read/write/search/export/attachment |
| WP03-AT-002 | Entity/site integrity | Cross-tenant or mismatched legal-entity/site links rejected |
| WP03-AT-003 | Temporal integrity | No self-parent, invalid date range, manager overlap or solid-line cycle |
| WP03-AT-004 | Identity separation | Position cannot be attached directly to user/membership; appointment owned by People & Payroll |
| WP03-AT-005 | Appointment resolution | Expired/future/revoked/invalid acting assignment denied for action date |
| WP03-AT-006 | DoA determinism | Same transaction/policy resolves reproducible approval chain |
| WP03-AT-007 | SoD | Requester/preparer cannot self-approve or circumvent via second role |
| WP03-AT-008 | Delegation | Delegate acts only within time, scope, amount, currency and action |
| WP03-AT-009 | Reapproval | Material change invalidates affected prior approvals |
| WP03-AT-010 | Concurrency/idempotency | No duplicate/partial approval or org change under replay/race |
| WP03-AT-011 | Audit/outbox atomicity | Failure rolls back aggregate/audit/outbox; success publishes idempotently |
| WP03-AT-012 | Legal applicability | Missing/expired/unreviewed licence/source holds affected activity |
| WP03-AT-013 | Agent boundary | Agent can read/draft but cannot appoint, approve, post, pay, file or activate law |
| WP03-AT-014 | Privacy | Unauthorized identity/payroll/bank/restricted HR evidence denied or masked |
| WP03-AT-015 | Recovery | Restore retains appointment, approval, source snapshots and audit consistency |
| WP03-AT-016 | Usability | Keyboard/screen-reader/desktop/tablet workflows tested |

**Exact-head verification required:** Composer validation/audit, PHP syntax/Pint, PostgreSQL migrations and RLS under restricted runtime, Laravel feature/API tests, Angular strict compile/build/Vitest, route/OpenAPI tests, concurrency/idempotency/outbox failure injection, dependency/security scans, exact-head artifact assembly. A workflow failing before job steps/logs is infra-blocked; the test is NOT RUN. Do not call that a pass or infer a code defect.

## 16. Risks and unresolved decisions

| Ref | Open issue | Safe control |
|---|---|---|
| R01 | Customer legal form/articles/Board structure unknown | Configurable template; legal review before activation |
| R02 | Actual customer titles/reporting unknown | Approved customer mapping; no invented title conversion |
| R03 | Position appointment/person master dependency | People & Payroll integration; no user-to-position shortcut |
| R04 | DoA limits/currencies/cumulative amounts unknown | Signed customer policy; no hardcoded thresholds |
| R05 | Entity/site/warehouse/channel scope enforcement incomplete | Fail closed until target-resource resolver exists |
| R06 | Public procurement scope varies | Legal entity applicability assessment; distinguish private internal policy |
| R07 | Ethiopian law metadata/translation differences | Preserve Gazette bytes/hash, Amharic, translation review and conflict log |
| R08 | PAYE base/pension interaction unresolved | WP-14 qualified Ethiopian tax opinion; no activation |
| R09 | Pension coverage/salary/directive interpretation incomplete | POESSA/local specialist review |
| R10 | Privacy and regional rules unfinished | Data minimization, purpose, retention and local review |
| R11 | CI runner previously failed before steps/logs | NOT RUN/infra-blocked; do not claim test pass |
| R12 | Existing schema candidates can collide or duplicate future tables | Reconcile exact delivery head/migration inventory before migration |

## 17. WP-03 delivery sequence and handover

1. ORG-01 source reconciliation and domain contract.
2. ORG-02 organization and position master.
3. ORG-03 appointment integration and DoA policy/workflow.
4. ORG-04 Procurement and Finance integration.
5. ORG-05 Angular organization/authority workbench.
6. ORG-06 migration, audit, imports and operational rollout.
7. ORG-07 full WP-03 acceptance and handover.

WP-14 remains after WP-04–WP-13. Payroll legal analysis is a localization dependency register, not authorization to advance or activate payroll during WP-03.

Handover requires requirement traceability, entity/relationship model, migration/rollback evidence, APIs/events, user workbench, security/RLS/SoD, audit/outbox, exact-head tests, operator runbook and open legal/customer decisions. A design memo or partial feature is not enough to advance the package.

## 18. Source references

**Notion**
- Governance: https://app.notion.com/p/4205f6c73c6881398043d974ccc4fdc2
- Garment Expert Product Goal: https://app.notion.com/p/4205f6c73c68815d89bbd6f448726af1
- Functional Acceptance Runbook: https://app.notion.com/p/3f75f6c73c688157bf77ecee31c6d60e
- Reference Study: https://app.notion.com/p/4215f6c73c688137a817c0ef018ec212
- Agent-Native Standard: https://app.notion.com/p/4225f6c73c68818b9b61ca6e03859c9b
- Technology Blueprint: https://app.notion.com/p/4225f6c73c688156a021dade5d10a5b6

**Repository**
- WP-03 target: docs/architecture/tenant-organization-identity.md
- Organogram/DoA: docs/organization/enterprise-organogram-and-delegated-authority.md
- Combined work plan: docs/work-packages/wp03-wp14-organogram-payroll-implementation-plan.md
- Ethiopia legal register: docs/localization/ethiopia-legal-regulatory-register.md
- WP-03 acceptance: docs/verification/WP03-implementation-acceptance-matrix.md
- Code map: docs/architecture/application-folder-code-map.md

**Legal**
- MoJ Commercial Code / Trade & Commercial Regulations: https://justice.gov.et/en/sector/trade-commercial-regulations/
- MoJ Public Procurement Proclamation 1333/2024: https://justice.gov.et/en/law/the-federal-public-procurement-and-property-administration-proclamation/
- MoR Income Tax Amendment 1395/2025 Gazette PDF: https://www.mor.gov.et/DomesticProclamations/1786970364007-582645424-pdf-%C3%A1%C2%8A%C2%A0%C3%A1%C2%8B%C2%8B%C3%A1%C2%8C%C2%85-%C3%A1%C2%89%C2%81%C3%A1%C2%8C%C2%A5-%C3%A1%C2%88%C2%AD-1395-2017.pdf
- MoJ Regulation 597/2026 Gazette PDF: https://justice.gov.et/wp-content/uploads/2026/11/%E1%8C%98%E1%88%9B%E1%8C%AB%E1%88%AC-%E1%8C%A5%E1%88%AD-597-2019.pdf
- HOPR Pension Proclamation 1268/2022 Gazette PDF: https://www.hopr.gov.et/admin/Files/2026/2/25/fc53f560-b228-4314-b372-da2b26ef4b53_Upploads_04012023090127_%E1%8B%A8%E1%8C%8D%E1%88%8D%20%E1%8B%B5%E1%88%AD%E1%8C%85%E1%89%B5%20%E1%88%A0%E1%82%AB%E1%89%B0%E1%8A%9E%E1%89%BD%20%E1%8C%A1%E1%88%AB%E1%89%B3%20%E1%8A%A0%E1%8B%8B%E1%8C%85%20%E1%89%81%E1%8C%A5%E1%88%AD%201268%20%281%29.pdf
- MoJ Labour Proclamation 1156/2019: https://justice.gov.et/en/law/labour-proclamation/

## 19. Document control

Status is design source for WP-03 implementation and integration. Companion payroll note is engineering localization analysis, not legal opinion or statutory rule activation. No customer chart, board charter, approval threshold, PAYE base or pension exception is invented. Product scope, technology, domain ownership or WP order changes use the formal project change process. All source/test claims use exact branch/SHA and PASS, FAIL, NOT RUN, BLOCKED or REVIEW REQUIRED labels.
