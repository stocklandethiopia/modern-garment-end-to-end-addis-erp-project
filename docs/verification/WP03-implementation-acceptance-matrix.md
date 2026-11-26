# WP-03 — Requirement-level implementation and acceptance ledger

**Working branch:** work/wp03-identity-platform-completion-2026-11-26  
**Integration base:** delivery/autonomous-wp-01-16  
**Governed scope:** WP-03 only, within the fixed WP-00–WP-16 sequence  
**Status:** PARTIAL FOUNDATION — exact-SHA CI evidence exists; WP-03 handover is open.

This ledger reconciles the requirements assigned to WP-03 in docs/requirements/requirements-traceability.md with source and tests that exist today. It prevents a code file, migration, or shared CI pass from being mistaken for complete requirement acceptance.

## Evidence rules

- **PARTIAL** means some executable source or tests exist but the named acceptance scenario is not fully proved.
- **NOT RUN** means no test result was found for the required scenario.
- **BLOCKED** means the feature intentionally fails closed until an approved scope/identity/legal dependency exists.
- **PASS** is reserved for an executed test on an exact source SHA that proves the stated scenario; it does not automatically close all of a requirement's sub-scenarios.
- Passing generic unit/integration tests does not automatically mean an acceptance scenario has passed. The test must exercise the scenario's actual actors, data, authorization and boundary.

## Requirements assigned to WP-03

| Requirement | Acceptance scenario | Present implementation/evidence | Status | Remaining acceptance work |
|---|---|---|---|---|
| GAR-ENT-001 | AT-ENT-001 tenant isolation | TenantProvisioningService, ResolveTenantContext, composite tenant FKs and forced RLS; TenantProvisioningServiceTest, TenantIsolationTest and DomainRlsSecurityTest. | PARTIAL | Complete tenant and legal-entity lifecycle, entity/unit relationships, concurrent provision/disable, restore and data-subject tests. RLS pass is scoped to tested tables/paths only. |
| GAR-ENT-002 | AT-ENT-002 multi-entity configuration | legal_entities, tenant-scoped entity model and controller; legal entity ownership scoped by tenant. | PARTIAL | Add versioned entity policy, fiscal calendars, multi-currency settings, language/timezone/business-model profiles and prove two entities in one tenant can post/read only their respective books. |
| GAR-ENT-003 | AT-ENT-003 typed physical network | business_sites, operational_nodes, inventory_locations, composite FKs and parent/location validation; TenantIsolationTest covers invalid location relationships. | PARTIAL | Add/verify complete typed-node rules for factory, DC, branch, store and fulfilment node; validate stock-holding capability, legal-entity relation, effective dating and no implicit warehouse/stock. |
| GAR-ENT-004 | AT-ENT-004 brand/channel/account/partner separation | Merchandising has brand/range/channel-assortment and account terms; site/entity are distinct. | PARTIAL | Complete master-data and cross-entity/channel/partner mapping; no single entity/site fallback for brand, channel, account or fulfilment partner. Add explicit acceptance cases. |
| GAR-ENT-005 | AT-ENT-005 least-privilege tenant/entity/site/record scope | Token abilities, tenant context, access-scope modes and BRAND/SITE/MARKET grants; DomainCapabilityAuthorizer checks active tenant membership and role assignment. It deliberately denies members in SCOPED mode for domain abilities until target-scope proof is implemented. | BLOCKED for scoped domain access; PARTIAL overall | Add approved entity/site/department/warehouse/channel scope records, authorization context resolver and per-resource scope checks. Prove denial of stale/revoked/expired and cross-tenant grants in every domain. Keep current fail-closed behavior until then. |
| GAR-ENT-006 | AT-ENT-006 maker-checker separation | Invitation/member role changes, domain-role assignment request/owner approval/revoke, purchasing, finance/payroll and other approval foundations have maker-checker controls. | PARTIAL | Build a central conflict/delegation/approval-policy model; test self-approval, delegated approver, stale authority, simultaneous approvals and distinct requester/approver at every high-impact operation. |
| GAR-ENT-007 | AT-ENT-007 versioned critical master data | Effective-dated domain roles; versioned merchandising/product/technical master data and immutable decisions are present. | PARTIAL | Implement shared master-data effective-date, supersession and historical resolution rules for entity/site, roles, licence, channel and policy objects; prove historical transactions retain approved versions. |
| GAR-ENT-008 | AT-ENT-008 immutable audit evidence | Append-only tenant audit events, actor/action/object/scope/reason/correlation and database triggers/grants; membership and domain-role events recorded. | PARTIAL | Extend consistent before/after or event payload hashes, read/export audit policy, retention/integrity verification and privileged/tamper-adversarial tests. Do not label an event table as tamper-proof without a verified hash chain or equivalent. |
| GAR-ENT-009 | AT-ENT-009 licence/activity/site gate | Licensing evidence register and legal activity hold foundations; current own-retail control remains simulation/review gated. | PARTIAL | Resolve licence/permit by entity, site, activity, channel, jurisdiction and effective date; test transaction hold/release on verified current evidence and authority approval. No legal activation by code alone. |
| GAR-ENT-010 | AT-ENT-010 privacy/retention/data-subject handling | Tenant isolation and role scoping reduce unauthorized reads; sensitive identity and payroll fields exist. | NOT RUN / PARTIAL controls | Define purpose/legal basis, retention/hold rules, access/export/delete requests, data minimization and privacy audit. Prove unauthorized user/staff fields are masked and legal hold prevents deletion/export. |

## Platform requirements included in WP-03

| Requirement | Acceptance scenario | Present implementation/evidence | Status | Remaining acceptance work |
|---|---|---|---|---|
| GAR-PLT-001 | AT-PLT-001 architecture boundary | Laravel modular monolith structure, domain folders/services/controllers, explicit backend/frontend module locations and path map in docs/architecture/application-folder-code-map.md. | PARTIAL | Add machine-verifiable dependency-direction rules and tests that reject forbidden cross-module dependencies and cross-domain direct writes. |
| GAR-PLT-003 | AT-PLT-003 transaction integrity/concurrency | PostgreSQL 18 migrations, FKs/checks/unique constraints, forced RLS, transaction scopes, append-only ledgers and row/advisory locks exist. | PARTIAL | Add deterministic concurrent-posting/idempotency/deadlock tests for identity, tenant role, inventory, finance and production operations; prove no double mutation/consumption and no partial commit. |
| GAR-PLT-004 | AT-PLT-004 Redis is non-authoritative | Redis is configured for cache/queue/coordination; PostgreSQL is the system of record. | NOT RUN | Add failure-injection test: stop/flush Redis during a committed transaction and prove durable business state remains unchanged, outbox/queue can replay and idempotency prevents duplicate effects. |
| GAR-PLT-005 | AT-PLT-005 REST/OpenAPI validation | Versioned /api/v1 routes, request validation, tenant/token middleware and stable JSON error handling; one focused merchandising OpenAPI document exists. | PARTIAL | Publish complete OpenAPI 3.1 contracts for all enterprise/identity and domain APIs, response schemas/errors, auth/scopes, pagination, idempotency and deprecation compatibility tests. Prove invalid payload is atomic/no partial write. |

## Exact code locations

| Concern | Source locations |
|---|---|
| Tenant provisioning | backend/app/Console/Commands/ProvisionTenantOwner.php; backend/app/Services/TenantProvisioningService.php |
| Tenant selection and RLS context | backend/app/Http/Controllers/Enterprise/TenantController.php; backend/app/Http/Middleware/ResolveTenantContext.php; backend/app/Support/TenantContext.php |
| Tenant membership | backend/app/Http/Controllers/Enterprise/TenantMembershipController.php; backend/app/Services/TenantMembershipService.php; backend/database/migrations/2026_11_24_000013_create_secure_tenant_membership_lifecycle.php |
| Invitations and identity verification | backend/app/Http/Controllers/Enterprise/TenantInvitationController.php; backend/app/Services/TenantInvitationService.php; backend/app/Notifications/TenantInvitationNotification.php; backend/database/migrations/2026_11_24_000014_create_secure_tenant_invitations.php |
| Tenant/site/entity/location | Enterprise LegalEntityController, BusinessSiteController, InventoryLocationController and LicensingEvidenceController; migration 000001 |
| Scope grants | AccessScopeController; ScopedAccessService; migration 000009 |
| Domain roles | TenantDomainRoleAssignmentController; TenantDomainRoleAssignmentService; DomainCapabilityAuthorizer; migrations 000027–000031 |
| Request actor context | ResolveAuthenticatedIdentityContext; TenantContextResolver |
| Tests | TenantProvisioningServiceTest, TenantIsolationTest, TenantMembershipLifecycleTest, TenantInvitationLifecycleTest and DomainRlsSecurityTest |

## Owner succession and voluntary leave — new implementation slice

| Acceptance scenario | Implementation/evidence | Status | Remaining verification |
|---|---|---|---|
| AT-ENT-OWNER-001 ownership transfer | `POST /api/v1/enterprise/ownership/transfer`; only active OWNER may transfer to another active MEMBER/ADMIN. A SECURITY DEFINER function serializes tenant succession, locks both identities in stable order, demotes the current owner and promotes the successor in one transaction, and writes `TENANT_OWNERSHIP_TRANSFERRED`. | IMPLEMENTED — exact-head CI pending | Prove no two active owners, former-owner permissions update immediately, only new owner can transfer next, and failure rolls back both role changes. |
| AT-ENT-OWNER-002 safe voluntary exit | `POST /api/v1/enterprise/membership/leave`; current member leaves their own tenant membership. OWNER is denied until ownership is transferred. Membership revocation trigger revokes all pending/active domain roles. | IMPLEMENTED — exact-head CI pending | Prove all roles revoked, self-leave is audited, and ownerless tenant is impossible. |
| AT-ENT-OWNER-003 single-owner integrity | Migration 000031 creates a unique partial index for one active owner maximum plus a deferred constraint trigger for exactly one active OWNER per existing tenant, including tenant creation. | IMPLEMENTED — exact-head CI pending | Prove migration preflight detects existing ownerless/multi-owner tenants; prove deferred constraint rejects owner removal and accepts owner transfer at transaction commit. |

## Exact verification evidence

Previous integrated candidate SHA dbd01b56510e87beebfac454ae49723bfbf5c633 passed:
- Combined WP-01–WP-16 workflow #45891155076: all four jobs passed; backend 78 tests / 967 assertions; Angular 27 tests / 7 files and production build; RLS/migrations; WP-01 source validation; artifact assembly.
- Destination delivery CI on merge commit 6bd8990d434d3cbc9f51e58489cc76fb240a83b6: #45891439094 and #45891445136.

These runs predate the new WP-03 code in this branch and therefore do **not** verify migrations 000028–000031 or the new role/ownership lifecycle tests. PR #6 exact-head CI is the required verification for this increment.

## Next implementation sequence

1. Verify tenant membership ↔ domain-role lifecycle (revoke, role promotion, reactivation, overlap/effective-date, race/concurrency and audit). Migration 000029 enforces database-level overlap; 000030 provides the narrowly granted locked-membership reader.
2. Complete and verify owner succession/voluntary leave (migration 000031), exactly-one-active-owner constraint, transfer concurrency/rollback and post-transfer authority.
3. Complete tenant activation/suspension/closure/export/retention/recovery and platform entitlement separation.
2. Complete a domain-role administration API/UI and a full list of role codes, status/effective dates, conflict/approval history and revoke reasons.
3. Design and implement owner transfer/succession/leave plus last-owner protection, using an explicit owner-only state machine and concurrency locks.
4. Complete tenant activation/suspension/closure/export/retention/recovery and platform entitlement separation.
5. Implement the entity/site/department/warehouse/channel scope model and target-specific policy checks; do not enable scoped access until every target resource is tenant- and scope-verified.
6. Add MFA/OIDC/SSO/session refresh/reuse detection and recovery.
7. Complete enterprise/operator Angular journeys and provisioning/recovery runbooks.
8. Run full GAR-ENT-001..010 and GAR-PLT-001/003/004/005 acceptance suite, plus independent review, then update the handover register.

**WP-03 remains open.** No later work package can be treated as accepted to bypass it. Routine owner acceptance is not required between safe engineering steps; exact technical evidence is.
