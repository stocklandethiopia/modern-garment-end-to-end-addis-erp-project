# WP-03 — Enterprise Foundation Engineering LLD and Implementation Source Pack

**Document ID:** WP03-ENT-LLD-002  
**Prepared:** 27 November 2026  
**Package:** WP-03 — Tenant, legal entity, business model, organization, site, branch, DC, store, channel and identity foundation  
**Status:** Engineering design supplement; implementation work remains partial and acceptance is open  
**Repository baseline inspected:** delivery/autonomous-wp-01-16 at fc01eced62b97c4ec27f0075264fc8ba0fb11851  
**Companion design:** docs/architecture/tenant-organization-identity.md  
**Companion acceptance ledger:** docs/verification/WP03-implementation-acceptance-matrix.md

## 1. Purpose, authority and scope

This document turns the accepted WP-03 target architecture into a buildable engineering low-level design. It is a supplement to the existing tenant/organization/identity architecture, not a replacement or a scope change. It reconciles the target model with the current repository source, the WP-03 acceptance matrix, the organogram/DoA design work, the WP-03/WP-14 build plan and the Notion project-governance/product references.

WP-03 establishes the shared identity and enterprise context consumed by every later domain. It must not become an HR-only chart or a CRUD-only company register. It must provide legally accountable entities, typed operating locations, configurable company organization, identity and authorization boundaries, licensing applicability, scoped access, approval-policy foundations, durable audit evidence and versioned integration contracts.

**Included**
- Tenant and SaaS account lifecycle context.
- Legal entity and jurisdiction/country identity.
- Business unit, organization unit, position/job-slot and reporting model.
- Business site, operational node, branch registration, distribution centre, depot, store and inventory-location distinction.
- Legal activity/licence evidence linked to entity/site/activity/channel and effective dates.
- Global user identity, tenant membership, platform role, tenant domain role, scoped permission and session context.
- Person/employment/position-appointment ownership contracts with People & Payroll, without moving employee payroll into WP-03.
- Delegated Authority (DoA) and maker-checker policy foundation used by later workflows.
- Security, privacy, audit, outbox, API, UI, agent and test contracts.
- Ethiopia localization notes affecting entity, branch, site, company-governance, privacy and payroll integration.

**Not included in WP-03 implementation**
- Full employee/personnel lifecycle, payroll calculation, PAYE/pension remittance or HR case management; these remain WP-14.
- Procurement, accounting, inventory, manufacturing or retail transaction implementations owned by their numbered work packages.
- Legal opinions, licensing determinations, statutory rule activation, actual payments, tax filing or production deployment.
- Hard-coded company-specific job titles, approval amounts, organizational chart, licence permissions or statutory settings.

The controlling sequence remains WP-00 through WP-16. WP-03 is the active execution checkpoint. WP-14 consumes WP-03 contracts later; WP-15 consumes the governed APIs/events after the relevant domains exist; WP-16 validates the complete cross-domain release.

## 2. Governing source hierarchy and reference-use rule

The design hierarchy is:

1. Project Governance Agreement and root AGENTS.md.
2. Approved product/domain standards and WP-03 architecture.
3. WP-01 traceability requirements and WP-03 acceptance ledger.
4. Official Ethiopian law and issuing-authority evidence for legal controls.
5. Current repository source and tests for implementation state.
6. Notion product, technology and reference-study pages.
7. External ERP/reference repositories and the payroll localization ZIP as analytical evidence only.

Notion governing sources reviewed:
- Project Governance Agreement — One Goal, Express Approval and Numbered Handover.
- Garment Expert Product Goal — Initiative-to-Sell-Through Operating System.
- Garment ERP Reference Study — awesome-garment-erp and Scan ERP CMT Toolkit.
- Modern Garment ERP — Agent-Native Technology Integration Standard.
- End-to-End ERP Technology & Intelligent Experience Blueprint — Production to Customer.

Reference repositories and the supplied payroll ZIP are not the new product's schema, runtime or database specification. Use only validated capability patterns and design lessons. Do not copy the historical Stockland 16.x data model, version identifiers, PHP classes, tables or product-specific assumptions. The payroll sample is not a legal authority or production calculation source. It is a reference for evidence-backed rule versioning, exact arithmetic, fail-closed decisions and separated transaction stages.

## 3. Current repository baseline — what exists and what remains

### 3.1 Existing target architecture

The existing architecture document specifies:
- Angular 22 and TypeScript 6 client.
- Laravel 13 and PHP 8.5 modular monolith API.
- PostgreSQL 18 as system of record.
- Redis 8.2 as cache/queue/coordination only.
- REST/OpenAPI 3.1 contracts and transactional outbox.
- Shared PostgreSQL cluster/schema by default, tenant row isolation via composite tenant keys, forced RLS and server-side authorization.
- Domain-owned write models, application services and versioned events. No cross-module direct table writes.

The checked repository manifests confirm PHP ^8.5, Laravel ^13.0, Angular ^22, TypeScript ~6.0, PostgreSQL 18 and Redis 8.2. This is the repository's stated technical baseline, not a claim that production deployment has been qualified.

### 3.2 Existing WP-03 database and application source

The delivery baseline migration 2026_11_22_000001_create_enterprise_foundation_tables.php creates:
- tenants
- tenant_memberships
- legal_entities
- business_sites
- operational_nodes
- branch_registrations
- inventory_locations
- audit_events

Migration 2026_11_22_000003_create_licensing_evidence_tables.php adds licensing evidence foundations. Later migrations add secure membership lifecycle, invitations, scoped access foundations, domain-role assignment lifecycle, owner succession/exit and platform entitlements/tenant lifecycle.

Existing application paths include:
- backend/app/Services/TenantProvisioningService.php
- backend/app/Http/Middleware/ResolveTenantContext.php
- backend/app/Support/TenantContext.php
- backend/app/Http/Controllers/Enterprise/TenantController.php
- backend/app/Http/Controllers/Enterprise/LegalEntityController.php
- backend/app/Http/Controllers/Enterprise/BusinessSiteController.php
- backend/app/Http/Controllers/Enterprise/InventoryLocationController.php
- backend/app/Http/Controllers/Enterprise/LicensingEvidenceController.php
- backend/app/Services/Authorization/DomainCapabilityAuthorizer.php
- backend/app/Services/Authorization/ScopedAccessService.php
- backend/app/Services/TenantMembershipService.php
- backend/app/Services/TenantInvitationService.php
- backend/app/Services/TenantDomainRoleAssignmentService.php
- backend/routes/domain-api.php
- corresponding Angular enterprise services/components under frontend/src/app/features/enterprise/

Existing security implementation establishes transaction-local app.current_user_id and app.current_tenant_id, confirms active membership and active tenant, forces row-level security on tenant-owned tables and grants explicit app_runtime privileges. Tenant membership, platform operator roles and domain roles are distinct models.

### 3.3 Confirmed gaps from the acceptance ledger

The source acceptance ledger records the following as partial or blocked, not complete:
- GAR-ENT-001 tenant isolation and lifecycle acceptance across every tenant-owned path.
- GAR-ENT-002 multi-entity policy, fiscal/calendar settings and cross-entity separation.
- GAR-ENT-003 complete typed physical network semantics and no implicit stock-holding.
- GAR-ENT-004 brand/channel/account/partner separation and relationship tests.
- GAR-ENT-005 entity/site/department/warehouse/channel scoped permissions; current domain-role assignment is tenant-scoped and scoped domain access remains fail-closed.
- GAR-ENT-006 common DoA/conflict/delegation and approval-policy engine.
- GAR-ENT-007 shared effective-dated master-data and historical-resolution rules.
- GAR-ENT-008 audit integrity, retention, export and tamper-adversarial verification.
- GAR-ENT-009 licence/activity/site/channel applicability and transaction hold/release proof.
- GAR-ENT-010 privacy purpose, retention, legal hold and data-subject request workflows.
- Platform dependency-direction enforcement, complete OpenAPI contracts, Redis failure/replay evidence and deterministic cross-domain concurrency tests.

The architecture document contains a draft org_units field named owner_user_id. The new organogram/DoA design rejects that as an authority or manager shortcut. The target design must use a position-based manager relationship and separately resolve any active human incumbent through People & Payroll. This is a controlled design refinement within WP-03's approved identity separation; runtime migration and compatibility work must be reviewed before integration.

### 3.4 Separate work branches and unmerged candidates

As checked on 27 November 2026:
- PR #16 contains the organogram and DoA design and remains open/unmerged.
- PR #19 contains the combined WP-03/WP-14 organogram/payroll build plan and remains open/unmerged.
- PR #21 is a draft/unmerged WP-03 organization-runtime candidate.

The PR #21 candidate added org_units, job_positions, org_unit_manager_assignments, position_reporting_relations, Laravel APIs, a first Angular workbench and initial tests. It is candidate-only. Its test suites did not execute in the observed CI attempts because required workflow jobs failed before producing step logs. Do not describe that branch as integrated, tested or accepted. Reconcile it with this LLD and current base before any follow-on runtime implementation.

## 4. Target bounded-context architecture

### 4.1 Core enterprise objects

| Object | Authoritative meaning | Must not be confused with |
|---|---|---|
| Tenant | SaaS customer data/configuration/security boundary | Legal person, employer or business unit |
| Legal entity | Registered person/body with statutory, tax and accounting identity | Tenant, brand, site or branch node |
| Business unit | Management, business-line or profit/cost responsibility grouping | Legal entity |
| Org unit | Department/function/team in the company hierarchy | Person, employee, login or permission |
| Business site | Physical/registered place of activity | Warehouse or stock ledger |
| Operational node | Typed operational point: factory, DC, depot, branch/store, office or fulfilment node | Legal registration or stock balance |
| Branch registration | Legal/commercial-register registration and activity evidence tied to entity and address | A branch-type operational node |
| Inventory location | Explicit stock-holding location/bin under a capable operational node | Site or general organization unit |
| Position/job slot | Stable company role/seat with job family, grade, unit and effective dates | User, employee or incumbent |
| Person | Human identity anchor owned by People & Payroll | Login identity |
| Employee/employment | Employment relationship, contract and statutory profile owned by People & Payroll | Supplier, creator or login membership |
| Position appointment | Effective-dated link from employee/person to a position | Position itself or tenant role |
| User identity | Authentication principal | Employee or company director by default |
| Tenant membership | User's access relationship to a tenant | Employment or corporate authority |
| Domain role | Permission vocabulary for a business domain | Job title or legal delegation |
| Resource scope | Entity/site/unit/cost-centre/channel/record reach | Tenant membership alone |
| DoA authority | Versioned, approved transaction-decision authority | Tenant OWNER, generic ADMIN or job title alone |

A transaction must independently resolve tenant, legal entity, operating site/node and commercial/channel context where applicable. Never use an implicit default entity, site, warehouse, branch or channel when the transaction has a legal or operational context.

### 4.2 Bounded-context ownership

| Bounded context | Owns | Consumes |
|---|---|---|
| Tenant & Enterprise | Tenant, legal entity, business unit, org unit, site, operational node, branch and licence evidence | Legal applicability decisions, master-data references |
| Identity & Access | User, credential reference, membership, platform role, tenant role, domain grant, scoped grant, authentication event | Tenant and approved company-position mappings where policy requires |
| People & Payroll (WP-14) | Person, employee, employment contract, employee attributes, time/leave, skills, appointments, payroll, pension and benefits | Tenant, legal entity, site, org unit, position, approved authority |
| Domain modules (WP-04–WP-13) | Their own transactions, state machines, operational decisions and domain data | Enterprise IDs and authorization/DoA decision services |
| Platform & Integration | API contracts, outbox/inbox, connectors, idempotency, secrets references, observability | Versioned domain events and approved integration scopes |
| Analytics & Intelligence | Derived read models, metrics and suggestions | Authorized minimized events/read models; never authoritative transactions |

Cross-domain reads for decisions use published application services or versioned read models. A domain must not write another domain's private tables directly.

## 5. Logical architecture and deployment topology

~~~mermaid
flowchart TB
  UI[Angular 22 Enterprise Workspace]
  EDGE[API Gateway / Laravel 13 Middleware]
  AUTH[Identity & Tenant Context]
  ENT[Enterprise Foundation]
  ORG[Organization & Position Service]
  DOA[DoA Policy / Approval Resolver]
  DOM[WP-04 to WP-14 Domain Modules]
  PG[(PostgreSQL 18)]
  OUT[Transactional Outbox / Inbox]
  REDIS[(Redis 8.2 Queue / Cache)]
  WORKER[Laravel Workers / Adapters]
  EXT[Authority / Bank / Partner Systems]
  AUD[Audit / Security / Observability]
  AGENT[Governed Operational Agents]
  UI --> EDGE
  EDGE --> AUTH
  EDGE --> ENT
  EDGE --> ORG
  EDGE --> DOA
  EDGE --> DOM
  ENT --> PG
  ORG --> PG
  DOA --> PG
  DOM --> PG
  PG --> OUT
  OUT --> REDIS
  REDIS --> WORKER
  WORKER --> EXT
  AGENT --> EDGE
  AUTH --> AUD
  ENT --> AUD
  ORG --> AUD
  DOA --> AUD
  DOM --> AUD
~~~

**Data authority:** PostgreSQL is authoritative. Redis is reconstructible transport/cache/coordination. Outbox events are committed in the same transaction as the aggregate state. External systems are adapters; their acknowledgements and reconciliation records are stored as evidence. No browser or agent can bypass the Laravel domain services and RLS controls.

## 6. Enterprise data model and relational constraints

### 6.1 Existing tables to retain and evolve additively

The current base is not to be replaced. Keep and extend the current entities, sites, nodes, branch registrations, inventory locations, tenant membership and audit model.

Existing keys/relationships:
- tenants.id is the SaaS root.
- legal_entities.tenant_id references tenants.
- business_sites.tenant_id and legal_entity_id bind a site to an entity.
- operational_nodes bind tenant + business_site + legal_entity and carry explicit node_type and capabilities.
- branch_registrations bind tenant + business_site + legal entity and carry commercial register reference, activity codes, issuing authority, dates and review status.
- inventory_locations bind to an operational node and parent location; allow_stock does not replace node capability checks.
- tenant_memberships bind tenant + user with unique membership and current lifecycle status.

All new tenant-owned tables must include tenant_id, a unique (tenant_id,id) key, tenant-composite foreign keys, RLS ENABLE + FORCE, an explicit tenant policy and a reviewed minimum privilege set for app_runtime. No application runtime DELETE for history-bearing enterprise/master records. Do not add nullable or implicit cross-tenant references.

### 6.2 Target organization tables

| Proposed aggregate/table | Main fields | Required invariants |
|---|---|---|
| business_units | tenant_id, legal_entity_id nullable, parent_id, code, name, responsibility_type, valid_from/to | A grouping is not a new legal person; parent is in same tenant; no cycles |
| org_units | tenant_id, business_unit_id nullable, parent_id, legal_entity_id/site scope, code, name, org_type, valid_from/to | Unique tenant code; effective-dated hierarchy; parent period containment; parent and child remain tenant/entity valid |
| job_positions | tenant_id, org_unit_id, position_code, title, job_family, grade, position_class, FTE, status, valid_from/to | Stable position identity; one home org unit per version; not a person or login |
| position_reporting_relations | tenant_id, subject_position_id, manager_position_id, relation_type, valid_from/to, reason | No self-report; one solid-line manager at a point in time; graph cycle prevention; functional/dotted line is distinct |
| org_unit_manager_assignments | tenant_id, org_unit_id, manager_position_id, valid_from/to, reason | Manager position must belong to the managed unit unless approved cross-unit policy exists; no overlapping active manager seats |
| position_authority_profiles | tenant_id, position_id, profile_code, version, valid_from/to | Description/stewardship only until a DoA rule maps authority; no implicit permissions |
| position_appointments (WP-14 ownership) | tenant_id, person_id, employee_id, position_id, appointment_type, valid_from/to, acting_flag, reason, approved_by | People & Payroll owns the employee/person link; no user_id shortcut; effective dates and approval required |
| entity_licences | tenant_id, legal_entity_id, site_id, activity_code, licence_no, authority, valid_from/to, status, evidence_id | Licence is entity/site/activity scoped; missing/expired/revoked/review-required evidence fails closed where required |
| org_structure_versions | tenant_id, version, effective_from, status, submitted_by, approved_by, source_hash | Publish immutable effective-dated structure versions; historical transactions resolve the version used |

The position-appointment table belongs to WP-14's People & Payroll context even though WP-03 defines the integration contract. WP-03 must not create a second employee registry or attach incumbents to global users. A position may remain vacant; multiple appointments are allowed only under explicit FTE/job-share/acting rules and separate effective periods.

### 6.3 Identity and authorization tables

Retain current users, tenant_memberships, scoped-access and tenant_domain_role_assignments patterns. Evolve only through additive migrations:
- membership_role_assignments / scoped grants with resource type, resource ID, valid period, grantor and reason;
- permission definitions and role-permission mappings with versioning;
- position-to-role mapping policies only where an approved job appointment grants a domain role;
- active delegation records for temporary acting authority with source position, delegate position/person, authority scope, limits, valid period and approval;
- authorization decision records for high-risk decisions with actor, effective appointment, scope and policy version.

Never materialize a global company title as an unrestricted permission. The permission resolver must consider the active user, active tenant membership, target tenant, legal entity/site/unit/channel scope, domain role, effective dates and resource state on every server-side action.

## 7. Company governance and new organogram design

### 7.1 Legal-form-aware governance

The organogram is a configurable company model, not a hard-coded company chart. Start by recording:
- legal entity form and registration/jurisdiction;
- current memorandum/articles/constitutional document reference and effective date;
- shareholder/general meeting structure where that form provides for it;
- Board and committee structure where required or constituted;
- executive appointment/delegation evidence;
- branch registration and activity/licence evidence;
- reserved matters and board resolutions that create authority.

For Ethiopian share companies, the Commercial Code Proclamation No. 1243/2021 contains specific governance provisions, including Board provisions. These provisions must be applied to the actual legal form and company constitution; do not impose a share-company board template on every private company, partnership, sole proprietorship, public enterprise or foreign branch. Qualified Ethiopian corporate counsel must confirm the company's legal form, exact articles, director appointments and reserved matters before the chart is treated as legally operative.

### 7.2 Configurable reference hierarchy

The following is a reference template only:

~~~text
SHAREHOLDERS / GENERAL MEETING (where the form provides for it)
  -> BOARD / COMMITTEES (where applicable or constituted)
     -> BOARD CHAIR / OPTIONAL VICE-CHAIR / DIRECTORS
     -> CEO / MANAGING DIRECTOR / EXECUTIVE PRESIDENT
        -> COO / OPERATIONS
           -> FACTORY GENERAL MANAGER / PLANT MANAGER
              -> Production, PPC, IE, Engineering, Quality,
                 Maintenance, Stores/Materials, Site HR, Site Finance,
                 HSE and Site Administration
        -> CFO / FINANCE
        -> CHRO / PEOPLE
        -> CPO / PROCUREMENT & SUPPLY CHAIN
        -> CCO / COMMERCIAL
        -> PRODUCT / DESIGN / MERCHANDISING
        -> CIO / CTO / DIGITAL & DATA
        -> LEGAL / COMPLIANCE
        -> QUALITY / HSE / SUSTAINABILITY (independent functional escalation)
~~~

Head-office functions can have solid-line functional heads with dotted-line site relationships. Factory route structure is product/process dependent: cutting, trim/accessories, print/embroidery, sewing, optional wash/dye/special processes, finishing, pressing and packing should only appear for processes the customer/site operates. DC/depot/branch/store roles can be configured independently.

The Board Chair and CEO are separate positions by default. Board Vice-Chair is not an executive Vice President. Titles such as COO, CFO, CHRO, CPO, President, Vice-President, General Manager or HOO never imply statutory authority or a transaction limit by themselves.

### 7.3 Accountability labels versus authority

Business Owner, Process Owner, Procurement Owner, Platform Owner and Product Owner labels describe stewardship/accountability unless explicitly mapped to a signed company position and policy. Tenant OWNER/ADMIN is SaaS configuration access. Platform operators have separate global support/admin grants. None of these labels automatically approve purchasing, payroll, journal posting, bank payment, employee appointment or licence activation.

### 7.4 Position and reporting semantics

- Position/job slot is a stable structural post, whether filled or vacant.
- An org-unit manager relationship references a manager position, not a login user.
- Solid-line reporting is the primary management line; functional/dotted-line reporting is separate and can coexist.
- Reporting relationships are effective-dated and versioned.
- No position can be its own manager; the solid-line graph must be acyclic across overlapping time periods.
- Human incumbency and acting appointments are resolved by People & Payroll's approved position-appointment API.
- Reorganization must not rewrite historic position, employment, transaction or approval records.

## 8. Delegation of Authority (DoA) — cross-domain control

### 8.1 Policy model

DoA is a versioned, scope-aware policy service, separate from role-based access control. A policy decision must bind to:
- tenant and legal entity;
- transaction/operation type and domain;
- business unit, org unit, site, branch/DC/store, cost centre and channel where relevant;
- currency, amount, budget status and risk/materiality band;
- supplier/customer/contract/project class where relevant;
- effective period and policy version;
- required approval sequence/quorum and position authority;
- delegation/acting rules and expiry;
- segregation-of-duty conflicts and exception/escalation route.

Do not hard-code generic monetary limits or assume the same DoA applies to every customer, entity, site, currency or business model. Store company-approved rules and evidence of approval by the person/body authorized under the legal form and constitutional documents.

### 8.2 Runtime decision sequence

1. Authenticate actor; resolve active tenant membership and user context.
2. Resolve target record, tenant, legal entity, site/unit/cost centre/channel and current transaction version.
3. Resolve the record's applicable company structure version and effective DoA policy.
4. Resolve actor's active employment/person appointment to a position; resolve approved acting delegation where present.
5. Check permission and resource scope independently from authority.
6. Evaluate amount/budget/risk/currency/transaction category and ordered approval steps.
7. Apply separation-of-duties and conflict checks. Self-approval and incompatible roles fail closed.
8. Create an immutable approval instance tied to object version and policy version.
9. Capture each decision, approver, position/appointment/delegation snapshot, reason, timestamp, evidence and correlation ID.
10. Re-evaluate if the transaction materially changes; material amount, supplier, legal entity, budget, scope or terms changes invalidate approvals and require re-submission/re-approval.
11. On rejection, expiry, revocation, role/appointment change or policy supersession, close/hold according to the state machine; never auto-convert to approved.

### 8.3 Segregation of duties baseline

For procure-to-pay:
- requester / budget owner raises need;
- sourcing performs RFQ and commercial comparison;
- technical evaluator records technical acceptance;
- PO preparer creates the draft and submits;
- independent authorized position approves under the DoA;
- independent receiver records actual receipt;
- Quality independently accepts/quarantines where inspection is required;
- AP performs supplier invoice validation and three-way match;
- treasury prepares the payment batch;
- separate bank signatories release payment.

A buyer cannot finally approve their own PO. A receiver cannot independently approve their supplier invoice. A person preparing a bank batch cannot unilaterally release it. Small-site staffing constraints require documented compensating review, not silent removal of segregation. Board-reserved matters remain separate from ordinary procurement.

### 8.4 Existing purchase-order boundary

The current Sourcing implementation has an interim tenant-domain-role gate with requester/submitter self-approval protection and a row lock. It is not the full DoA service. The current authorizer does not yet resolve position, incumbency, entity/site scope, amount/cumulative thresholds, budgets, delegates, multiple approval steps, reapproval after material amendments or AP/bank-release separation. Do not describe the current PO status field or a single procurement approver role as a complete DoA engine.

## 9. Site, branch, DC, store and licence controls

A physical/business site, operational node, branch registration and inventory location are different records:
- A legal entity owns statutory registration/tax identity.
- A business site describes physical/registered premises and time zone/address.
- An operational node describes what function is performed there.
- Branch registration proves the commercial-register relationship and activity record.
- Entity/site/activity licence proves permission for an activity.
- Inventory locations exist only under an explicitly stock-capable operational node.

For Ethiopian entities, legal review must map Commercial Registration and Business Licensing Proclamation No. 980/2016 and amendment No. 1150/2019 to the exact activity, entity, branch and address. The existing WP-02 legal register identifies Article 22 branch/manufacturing/wholesale/retail issues. In particular, a factory or manufacturing licence must not automatically enable company-owned retail sales. The system should place an activity/site/channel transaction on REVIEW REQUIRED or HOLD if the necessary legal evidence is absent, expired, contradicted or awaiting counsel/authority confirmation.

## 10. API and service contract design

All write operations execute through authenticated Laravel application services. Public routes are versioned under /api/v1 and require tenant context where tenant-scoped.

Proposed APIs, subject to actual route reconciliation:
- GET/POST /api/v1/enterprise/legal-entities
- GET/PATCH /api/v1/enterprise/legal-entities/{entityId}
- GET/POST /api/v1/enterprise/sites
- GET/PATCH /api/v1/enterprise/sites/{siteId}
- GET/POST /api/v1/enterprise/operational-nodes
- GET/POST /api/v1/enterprise/branch-registrations
- GET/POST /api/v1/enterprise/licensing-evidence
- GET/POST /api/v1/enterprise/organization-units
- GET/POST /api/v1/enterprise/job-positions
- GET/POST /api/v1/enterprise/position-reporting-relations
- GET/POST /api/v1/enterprise/organization-units/{unitId}/manager-assignments
- GET/POST /api/v1/enterprise/position-appointments (People & Payroll-owned contract; not an Enterprise table write)
- POST /api/v1/authorization/decisions
- GET/POST /api/v1/approval-policies
- POST /api/v1/approval-instances/{approvalId}/decisions

Do not add or expose the above routes solely because they appear in this design. Each endpoint requires an approved API schema, controller/service owner, policy, response and negative test. Writes require a stable idempotency key when a retried request could create duplicate business records. Validate tenant and legal-entity relationships server-side; never trust a client-supplied scope as authorization.

Return paginated responses with stable ordering and cursor/page metadata; validation errors must identify field and reason without leaking cross-tenant existence. All errors are structured, correlation-linked and safe for logs.

## 11. Event and integration contracts

Use versioned transactional outbox messages. The following are target events, not evidence that all are implemented:
- TenantProvisioned.v1
- LegalEntityRegistered.v1 / LegalEntityStatusChanged.v1
- BusinessSiteCreated.v1 / BusinessSiteChanged.v1
- OperationalNodeCreated.v1 / OperationalNodeCapabilityChanged.v1
- BranchRegistrationRecorded.v1
- LicenceEvidenceSubmitted.v1 / LicenceEvidenceApproved.v1 / LicenceEvidenceExpired.v1
- OrganizationUnitCreated.v1 / OrganizationStructurePublished.v1
- JobPositionCreated.v1 / JobPositionChanged.v1
- PositionReportingRelationCreated.v1
- PositionAppointmentStarted.v1 / PositionAppointmentEnded.v1 (emitted by People & Payroll)
- DoAPolicyPublished.v1 / DoAApprovalRequested.v1 / DoADecisionRecorded.v1
- TenantMembershipGranted.v1 / TenantMembershipRevoked.v1
- DomainRoleRequested.v1 / DomainRoleApproved.v1 / DomainRoleRevoked.v1

Each event includes event ID, schema version, tenant ID, aggregate type/ID/version, legal entity/site scope where relevant, actor/device/service identity, correlation/causation, occurred-at timestamp, effective date, source-document reference, payload hash and no unnecessary PII. Consumers must be idempotent and preserve replay/duplicate handling.

## 12. Angular enterprise workspace

The user interface should provide:
- Tenant/company and active entity/site context header.
- Legal entity register and evidence status.
- Business-site and operational-node workspace.
- Branch registration/licence checklist with effective dates and evidence attachments.
- Organization tree and unit/department editor.
- Job-position and vacancy register.
- Solid-line and functional reporting editor.
- Position-incumbency/appointment view using People & Payroll API.
- DoA policy matrix and approval-chain preview.
- User/membership/domain role/resource-scope administration.
- Audit activity timeline and source evidence navigation.

UI is role-aware and responsive. Hiding a button is not security; every server action must repeat authorization. A chart may visually display positions and lines but must show effective dates, vacant/acting state and source version. Do not expose confidential payroll attributes on the organization chart. Provide accessible keyboard navigation, table alternatives, explicit empty/error/loading states, no duplicate submits and tenant-switch race protection.

## 13. Security, privacy and operational controls

### 13.1 Tenant isolation

- PostgreSQL runtime role is non-owner/non-superuser.
- Force RLS on all tenant tables; use transaction-local tenant/user context.
- Use composite foreign keys and unique (tenant_id,id) references for tenant-owned relations.
- Revoke default PUBLIC/app_runtime grants, then grant minimum SELECT/INSERT/UPDATE by table and operation.
- No direct app_runtime delete for legal, organization, membership, audit or approval history.
- Test missing tenant context, forged tenant header, stale membership, suspended tenant, cross-tenant FK, list/read/export, background worker and integration consumer paths.

### 13.2 Identity and HR/privacy boundary

Identity credentials are not employee master data. Employee national ID, TIN, bank data, contracts, medical/leave documents, biometric data, family information and payroll are sensitive. Keep purpose and legal basis, minimize collection, encrypt restricted fields/files, enforce object-level access, audit read/download/export, avoid PII in ordinary logs and apply legally approved retention/hold. Do not expose private employee/payroll fields through org chart or analytics by default.

Ethiopia Personal Data Protection Proclamation No. 1321/2024 is listed In Force by the Ministry of Justice. Detailed implementation of lawful basis, sensitive data, cross-border transfer, data-subject requests and authority instructions requires current qualified review; this LLD is not a compliance certification.

### 13.3 Agents and automation

Operational agents can:
- explain organization records and legal evidence status;
- summarize DoA routing and missing evidence;
- draft organization changes or policy proposals;
- prepare approvals for a human position incumbent;
- flag stale appointments, missing licences or SoD conflicts.

Agents cannot:
- appoint or terminate an employee or position incumbent;
- change legal entity registration, legal form or licence activation;
- issue/approve DoA policies or delegate their own authority;
- approve their own or another agent's high-impact transaction;
- create/approve a PO, post a journal, approve payroll, release a bank payment, file tax, remit pension or override statutory/legal holds;
- bypass RLS, tenant/domain roles, policy checks, audit or human review.

Agent integrations use least privilege, read-only by default, proposal/approval before write, bounded tools, signed actor attribution, audit events and prompt-injection/data-exfiltration controls.

## 14. Migration, data conversion and backward compatibility

- Additive, numbered migrations only; do not replace the current enterprise foundation or rewrite previous migrations already deployed.
- Migration names/sequence must be reconciled with all open WP-03 branches and current delivery head before allocation.
- Preflight tenant/registration/site duplicates, missing legal entity/site relationships, invalid activity evidence, orphan memberships and owner-count invariants.
- Preserve every existing tenant, legal entity, site, operational-node, branch-registration, inventory-location, membership, domain-role and audit identifier.
- Convert any legacy owner_user_id concept only through an explicit reviewed mapping to position/accountability metadata. Do not automatically turn user membership roles into a job-position incumbency or DoA authority.
- Effective-date backfilled organization data with source evidence; unknown dates remain unknown/REVIEW REQUIRED, not invented.
- Run migration in a disposable CI database and migration-upgrade path; verify rollback/forward-fix handling. Never execute a destructive reset on production data.
- Keep a migration ledger with checksum, source SHA, database version, owner, prerequisites, verification and recovery plan.

## 15. Verification and acceptance plan

| ID | Scenario | Minimum evidence required |
|---|---|---|
| WP03-AT-001 | Tenant provisioning and isolation | Two tenants, users, composite FKs, forced RLS, negative forged context and cross-tenant reads/writes |
| WP03-AT-002 | Multi-entity | Two legal entities in one tenant, separate statutory IDs/currency/fiscal policies, no cross-entity leakage |
| WP03-AT-003 | Site/node/branch | Factory, DC, depot, branch/store and office typed nodes; no implied warehouse/stock; legal entity and site consistency |
| WP03-AT-004 | Licensing applicability | Activity/site/channel/date evidence, branch registration, expiry/revocation and fail-closed transaction hold |
| WP03-AT-005 | Organization hierarchy | Parent integrity, period containment, no cycles, effective-dated reorganization |
| WP03-AT-006 | Position and appointment separation | Vacant position, incumbent via People & Payroll contract, acting appointment, expiry, no user-as-employee shortcut |
| WP03-AT-007 | Reporting | Solid-line uniqueness/cycle prevention; separate functional line; historical structure resolution |
| WP03-AT-008 | Access scope | Tenant, entity, site, unit, cost-centre, warehouse/channel scope; revoked/expired grants denied; default deny |
| WP03-AT-009 | DoA/SoD | Correct chain, amount/budget/scope/policy version, self-approval rejection, delegation expiry and material amendment reapproval |
| WP03-AT-010 | Audit/outbox | Atomic aggregate+audit+outbox commit/rollback, payload hash, idempotent consumption and tamper controls |
| WP03-AT-011 | Concurrency | Concurrent org updates, manager assignment, approvals, idempotency and no partial commit |
| WP03-AT-012 | Privacy/retention | Role/object masking, access/download logs, retention/legal-hold control and data-subject request workflow |
| WP03-AT-013 | UI | Angular build/test, role/tenant switching, keyboard/a11y, pagination and complete loading/error/empty states |
| WP03-AT-014 | API/OpenAPI | Complete OpenAPI 3.1 contracts, validation, pagination, auth scope, idempotency, error schema and compatibility |
| WP03-AT-015 | Recovery/integration | Redis unavailable/replay, outbox retry, backup/restore and external adapter reconciliation |

**Status discipline:** current requirements remain PARTIAL/BLOCKED until each scenario executes on the exact final SHA. A workflow that fails before runner/job steps is infrastructure-blocked; it is neither a passing test nor evidence of code failure. No production acceptance is inferred from design completion.

## 16. Ordered implementation work plan within WP-03

| Work ID | Deliverable | Dependencies | Acceptance output |
|---|---|---|---|
| WP03-ENG-01 | Requirements/source/route/schema reconciliation and approved contracts | Existing architecture + WP-01 traceability | Field/API/event crosswalk; no duplicate table owners |
| WP03-ENG-02 | Tenant, legal entity, fiscal/profile and identity hardening | Existing migrations 000001, 000013–000014, 000027–000032 | Exact RLS, membership, entity and lifecycle tests |
| WP03-ENG-03 | Site, operational-node, branch and licensing applicability | Existing 000001/000003 | Legal entity/site/activity matrix, holds and negative tests |
| WP03-ENG-04 | Organization-unit and job-position master | Entity/site contract | Additive migration, stable IDs, effective dates and no person/user conflation |
| WP03-ENG-05 | Solid-line/functional reporting and unit manager positions | Position master | Graph/period constraints and concurrent tests |
| WP03-ENG-06 | People & Payroll appointment contract | WP-14-owned person/employment master | Effective-dated appointment API/events and vacancy/acting scenarios |
| WP03-ENG-07 | Scoped authorization service | Membership/domain roles + approved resources | Entity/site/unit/record authorization; keep fail-closed until proved |
| WP03-ENG-08 | Versioned DoA and approval/SoD engine | Position appointment + signed company authority | P2P/Finance/Payroll policy tests and no self-approval |
| WP03-ENG-09 | Event/API/OpenAPI and Angular enterprise workbench | Stable contracts | API conformance, responsive/a11y UI tests |
| WP03-ENG-10 | Migration, audit, concurrency, security, UAT and package handover | All prior WP03 steps | Full GAR-ENT/PLT acceptance, exact SHA test evidence, operator docs |

Do not start WP-04 until the complete WP-03 definition of done is met. Payroll rule research/design may proceed as WP-14 preparation, but payroll implementation and statutory activation stay in WP-14 order.

## 17. Cross-package notes: organogram and payroll localization

### 17.1 Organogram is WP-03; employee incumbency is WP-14

The new organogram/DoA design is a WP-03 enterprise control foundation. It supplies legal-form-aware governance, organization units, positions/job slots, reporting lines, position authority metadata and the DoA resolver consumed by Procurement, Finance, Inventory, Manufacturing, Quality, Distribution and HR/Payroll.

The chart is configurable: board/general meeting structures apply only where required or constituted under the company's legal form and constitutional documents. Executive, head-office, factory, DC, depot and store roles are templates, not mandatory posts. Tenant OWNER/ADMIN, Business Owner/Process Owner/Procurement Owner and generic titles do not grant company transaction authority. The authoritative human incumbent and acting appointment must be resolved by People & Payroll (WP-14) through effective-dated person/employee/position assignment.

### 17.2 Payroll integration contract

WP-03 exposes legal entity, site, org unit, cost centre, position, approved manager position, user identity/membership and DoA policy version. WP-14 owns person, employee, employment contract, assignment/appointment, time/leave, skills, payroll, benefits and pension. The payroll run must snapshot the employee's employer legal entity, applicable site/cost centre, effective position appointment, approved compensation terms and versioned DoA approval chain. A login account is not an employee record and a manager title is not sufficient payroll approval.

Payroll rules remain source-backed and effective-dated, calculated server-side with exact decimal arithmetic, frozen input/rule snapshots and deterministic hashes. Payroll posting, employee bank payment, PAYE remittance and pension remittance are distinct states and ledgers. Historical posted payroll is never silently recalculated after a law change.

### 17.3 Ethiopia legal evidence and production gate

The companion file docs/localization/wp03-ethiopia-organogram-payroll-localization-notes.md records current legal-source evidence, exact instrument references, source metadata conflicts, sample calculation limits, interpretation questions and required professional review. It is an engineering localization note, not a legal opinion. No statutory payroll rule, employee pension coverage, allowance exemption, corporate governance arrangement or branch retail permission is enabled solely by this design.

## 18. Risks and unresolved decisions

| ID | Issue | Current disposition |
|---|---|---|
| R-01 | Domain role assignments currently have tenant-only scope | Keep scoped domain decisions fail-closed; implement full resource-scope resolver |
| R-02 | org_units.owner_user_id draft conflicts with position-based organogram | Use manager_position_id/position-based reporting; treat incumbency as WP-14 |
| R-03 | Position appointments depend on person/employee model | WP-03 publishes stable position contract; WP-14 owns incumbent resolution |
| R-04 | DoA not a complete engine in current source | Build policy/approval service before relying on transaction authority |
| R-05 | Site/node/branch not equal to licensing permission | Use legal-entity/site/activity/effective-date applicability and review state |
| R-06 | Ethiopian company/legal-form differences | Store constitutional/legal-form evidence; qualified local counsel confirms mapping |
| R-07 | Payroll PAYE base and pension scope unresolved | WP-14 legal gate; leave both candidate methods inactive |
| R-08 | Tax-law source/catalogue date and identifier conflicts | Preserve original Gazette metadata and source register reconciliation |
| R-09 | Privacy rules and sensitive employee data | Data minimization, role access, purpose, retention and legal review |
| R-10 | Workflow runners may fail before execution | Do not call tests passing; repeat only when runner can start and provide logs |

## 19. Final design decision

The WP-03 design remains the approved target architecture. The new organogram/DoA design and payroll localization analysis are linked as cross-package specifications; they do not reorder WP-14 or activate legal settings. Runtime completion remains PARTIAL until the entire acceptance matrix is proved on the exact final commit, including scoped access, appointment resolution, DoA/SoD, licensing, privacy, UI and recovery.

### Source and evidence references

- docs/architecture/tenant-organization-identity.md
- docs/verification/WP03-implementation-acceptance-matrix.md
- docs/architecture/application-folder-code-map.md
- docs/work-packages/handover-register.md
- docs/organization/enterprise-organogram-and-delegated-authority.md (open design PR #16)
- docs/work-packages/wp03-wp14-organogram-payroll-implementation-plan.md (open plan PR #19)
- docs/localization/ethiopia-legal-regulatory-register.md
- docs/localization/wp03-ethiopia-organogram-payroll-localization-notes.md
- Notion Project Governance Agreement and Chief Architect Agent Hub.
