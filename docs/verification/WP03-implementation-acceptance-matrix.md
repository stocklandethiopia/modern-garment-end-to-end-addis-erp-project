# WP-03 — Requirement-level implementation and acceptance ledger

**Active implementation branch:** `work/wp03-completion-2026-11-27`  
**Integrated base:** `delivery/autonomous-wp-01-16` at `f948bb85de4194ae18dc8ee8c974c3ad0986623c`  
**Prior implementation evidence:** `work/wp03-identity-platform-completion-2026-11-26`, `agent/chatgpt-wp03-continuation-2026-11-27`  
**Integration base:** `delivery/autonomous-wp-01-16`  
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
| AT-ENT-OWNER-001 ownership transfer | `POST /api/v1/enterprise/ownership/transfer`; only active OWNER may transfer to another active MEMBER/ADMIN. A SECURITY DEFINER function serializes tenant succession, locks both identities in stable order, demotes the current owner and promotes the successor in one transaction, and writes `TENANT_OWNERSHIP_TRANSFERRED`. | PASS — exact-head CI verified on source SHA `7d8d3cc8d94c7cb3e763a7b1174030315bd11382` | Prove no two active owners, former-owner permissions update immediately, only new owner can transfer next, and failure rolls back both role changes. |
| AT-ENT-OWNER-002 safe voluntary exit | `POST /api/v1/enterprise/membership/leave`; current member leaves their own tenant membership. OWNER is denied until ownership is transferred. Membership revocation trigger revokes all pending/active domain roles. | PASS — exact-head CI verified on source SHA `7d8d3cc8d94c7cb3e763a7b1174030315bd11382` | Prove all roles revoked, self-leave is audited, and ownerless tenant is impossible. |
| AT-ENT-OWNER-003 single-owner integrity | Migration 000031 creates a unique partial index for one active owner maximum plus a deferred constraint trigger for exactly one active OWNER per existing tenant, including tenant creation. | PASS — exact-head CI verified on source SHA `7d8d3cc8d94c7cb3e763a7b1174030315bd11382` | Prove migration preflight detects existing ownerless/multi-owner tenants; prove deferred constraint rejects owner removal and accepts owner transfer at transaction commit. |

## Exact verification evidence

- PR #6 exact source SHA `19e9084fbd5f4bd124bf8d2a1d50db3ce1b2569c` passed combined WP verification [#45893865867](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45893865867): 81 backend tests / 1,001 assertions, Angular 27 tests/7 files, migration/RLS and artifact assembly.
- PR #7 exact source SHA `7d8d3cc8d94c7cb3e763a7b1174030315bd11382` passed general ERP CI [#45896122644](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45896122644) and combined WP verification [#45896122613](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45896122613).
- Combined verification recorded PostgreSQL 18 migrations and forced RLS, PHP Pint/syntax across 132 files, **86 backend tests / 1,026 assertions**, Angular **27 tests / 7 files** plus production build, WP-01 R3 integrity and artifact assembly.
- Artifact `modern-garment-addis-erp-wp01-wp16-candidate`, ID `15154530114`, 726,099 bytes, expires 24 February 2027.
- PR #7 merged at `f7516de080c24ecd71ce599820e91f8f03ebab56`; destination CI [#45896477349](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45896477349) passed.  
- PR #9 platform lifecycle/entitlement foundation merged at `bd41c83e141835db3c0f03ac8d711096728a07b4`. Exact-source ERP CI [#45899259026](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259026) passed with **93 backend tests / 1,079 assertions** and **27 Angular tests / 7 files**. Combined WP-01–WP-16 verification [#45899259071](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259071) passed migrations/forced RLS, PHP formatting/syntax, WP-01 R3 checksum/strict audit and artifact assembly. Artifact ID `15154948146`, 739,862 bytes.
- Earlier failed attempts on intermediate heads were corrected (Pint style, atomic fixture setup and owner-invariant fixture alignment) and superseded; only the exact-head PASS above is acceptance evidence for the merged increment.

## Tenant lifecycle and platform entitlement foundation — merged integration increment

**Integrated state:** IMPLEMENTED FOUNDATION — EXACT-HEAD CI VERIFIED; PR #9 merged to delivery at `bd41c83e141835db3c0f03ac8d711096728a07b4`. The platform API/DB foundation is integrated; this is not full WP-03 acceptance.

| Acceptance scenario | Implementation/evidence | Current status | Remaining acceptance |
|---|---|---|---|
| AT-ENT-PLATFORM-001 platform authority is separate from tenant roles | Global `platform_operator_grants` with PLATFORM_ADMIN and PLATFORM_SUPPORT roles. No grant is auto-seeded. Application has no DML grants on the table; initial grants require controlled two-person DBA bootstrap. | IMPLEMENTED — exact-head CI verified in PR #9 | Verify grant expiry/revocation, support read-only behavior, platform admin-only write access, and no tenant owner/member escalation. |
| AT-ENT-LIFECYCLE-001 suspend/reactivate tenant | `PATCH /api/v1/platform/tenants/{tenantId}/lifecycle` and `public.platform_change_tenant_status`; ACTIVE↔SUSPENDED, plus closure. Suspension disables normal tenant context and stores entitlement status for a reasoned reactivation. | IMPLEMENTED — exact-head CI verified in PR #9 | Verify every tenant-protected API denies suspended/closed tenants, concurrent lifecycle transitions serialize, and recovery has an approved operator route. |
| AT-ENT-LIFECYCLE-002 terminal closure | CLOSED is terminal in this initial policy. Tenant data is retained; no delete or automatic purge is performed. | IMPLEMENTED — exact-head CI verified in PR #9 | Define retention period, legal hold, export, closure approval, and separately approved recovery/exception process. |
| AT-ENT-ENTITLEMENT-001 tenant entitlement state | `platform_tenant_entitlements` defaults every tenant to `UNASSIGNED / REVIEW_REQUIRED`. Platform admin can set plans, boolean feature flags, non-negative integer limits and effective dates. Tenant can read only its own entitlement. | IMPLEMENTED — exact-head CI verified in PR #9 | Define signed-off commercial plan catalogue, feature gate enforcement for each module, tenant quota enforcement, billing/renewal process and expiry behavior. |
| AT-ENT-AUDIT-PLATFORM-001 global platform audit | `platform_audit_events` records operator, tenant, before/after, reason and correlation ID; UPDATE/DELETE blocked and direct app_runtime table access revoked. | IMPLEMENTED — exact-head CI verified in PR #9 and PR #11 | Add independent audit integrity review, export/retention policy and adversarial tamper attempts. |
| AT-ENT-AUDIT-PLATFORM-002 platform tenant-register read audit | `platform_list_tenants()` appends `PLATFORM_TENANT_REGISTER_VIEWED` for both PLATFORM_ADMIN and read-only PLATFORM_SUPPORT; the event stores the actor and aggregate tenant count, not directory personal/business data. `PlatformAdministrationTest.php` asserts both role paths. | IMPLEMENTED — EXACT-HEAD CI VERIFIED PR #11 | ERP CI #45900565705 passed 93 backend tests / 1,081 assertions; combined WP #45900565706 passed all four jobs including migration/RLS, WP-01 R3 and artifact assembly. The tests assert both admin and support reads are recorded. |

**New code paths:** migration `backend/database/migrations/2026_11_26_000032_add_platform_entitlements_and_tenant_lifecycle.php` (merged); service `backend/app/Services/PlatformAdministrationService.php`; controllers `backend/app/Http/Controllers/Enterprise/PlatformAdministrationController.php` and `TenantEntitlementController.php`; routes in `backend/routes/domain-api.php`; tests in `backend/tests/Feature/PlatformAdministrationTest.php`; operator bootstrap and boundary in `docs/security/platform-administration-bootstrap.md`.

**Bootstrap restriction:** the migration intentionally creates no PLATFORM_ADMIN or PLATFORM_SUPPORT identities. A named operator must be appointed and granted out of band by the controlled database authority process documented in `docs/security/platform-administration-bootstrap.md`. No tenant OWNER/ADMIN route can grant platform access.

## Next implementation sequence

1. Complete independent audit-integrity review, export/retention/legal-hold policy and adversarial tamper attempts for the now-verified platform audit stream. PR #11 confirms register reads are recorded for both PLATFORM_ADMIN and read-only PLATFORM_SUPPORT.
2. Finish tenant lifecycle acceptance beyond ACTIVE/SUSPENDED/CLOSED: export/archive, retention schedule, legal hold, deletion prohibition and independently approved recovery. Keep suspended/closed access fail-closed.
3. Complete two-person platform grant lifecycle, including appointment, approval, expiry/revocation and independent audit, plus platform administration/entitlement Angular workflows. Do not seed real PLATFORM_ADMIN or PLATFORM_SUPPORT identities without the approved appointment record.
4. Enforce entitlements at each module boundary; define the commercial plan catalogue and entitlement policy before activating real plans or quotas.
5. Implement effective-dated legal-entity/site/department/warehouse/channel role scopes against real organization records and prove target-record authorization. Keep SCOPED access denied until each scope is checked.
6. Implement MFA, enterprise OIDC/SSO, session recovery and refresh-token rotation/reuse detection; complete invitation/domain-role/ownership UI and tenant bootstrap/operator tooling.
7. Complete secret provisioning/rotation, recovery, migration rollback and non-production deployment drills. Execute full GAR-ENT-001..010 and GAR-PLT-001/003/004/005 positive, negative, retry, expiry, concurrency, audit and tenant-crossing acceptance. Reconcile WP-01 R3's 128 records to the 120 visible GAR rows and document the exact eight-record delta.

**WP-03 remains open.** No later work package can be treated as accepted to bypass it. Routine owner acceptance is not required between safe engineering steps; exact technical evidence is.


## Global platform operator grant workflow — 27 November 2026 candidate increment

**Status:** IMPLEMENTED CANDIDATE — exact-head PostgreSQL/Angular CI and acceptance run pending. This increment is not yet merged into the canonical delivery branch and does not close WP-03.

| Acceptance scenario | Candidate implementation | Current status | Exact remaining proof |
|---|---|---|---|
| AT-ENT-PLATFORM-003 global platform-only sign-in | Global token authentication already accepts an active user without checking tenant membership. GET /api/v1/platform/context resolves only that identity's active, effective global roles through an authenticated identity context. Angular sign-in loads tenant memberships and platform roles separately; no tenant membership is required for platform operators. | IMPLEMENTED CANDIDATE | Run new AuthSessionService tests and prove platform-only sign-in, ordinary-tenant no-platform role, context endpoint failure fail-closed behavior and session reset. |
| AT-ENT-PLATFORM-004 platform support is read-only | The existing platform tenant register is available to PLATFORM_ADMIN/PLATFORM_SUPPORT and is read-audited. New Angular PlatformTenantsComponent exposes only the global read-only register. PLATFORM_SUPPORT cannot list/manage operator grants and sees no tenant workspace actions while no tenant is selected. | IMPLEMENTED CANDIDATE | Run backend and Angular tests; manually verify support route/role with a no-tenant identity; verify no lifecycle, entitlement or grant mutation control is present and API denies mutation. |
| AT-ENT-PLATFORM-005 two-person platform operator appointment | Migration 2026_11_27_000033_create_platform_operator_grant_lifecycle.php adds database functions for request, independent approval, revoke, active-role resolution and expiry sweep. A different active PLATFORM_ADMIN must approve; requester and target cannot approve; reasons are mandatory; every application grant has an explicit expiry no more than 365 days after its start; no direct app_runtime table DML is added. | IMPLEMENTED CANDIDATE | Execute the new PostgreSQL feature tests, test forbidden direct DML/EXECUTE, test stale/expired identity, duplicate grant, requester/recipient conflict, concurrent request/approval/revoke and immutable audit events. |
| AT-ENT-PLATFORM-006 global operator administration UI | Angular /platform/operators workbench provides admin-only request/list/approve/revoke actions; support receives the separate read-only tenant register. Existing tenant roles do not imply platform roles. | IMPLEMENTED CANDIDATE | Pass Angular production build and service/session tests; perform authenticated two-admin workflow in a test environment and verify accessible keyboard/validation/error states. |
| AT-ENT-PLATFORM-007 grant expiry and revocation audit | platform_current_operator_roles() enforces valid_from/valid_to at authorization time. On next authorized grant-register or grant-mutation operation, platform_expire_operator_grants() records EXPIRED state and an immutable platform audit event. | PARTIAL CANDIDATE | Prove the immediate access cutoff at the date boundary and sweep/audit semantics. A scheduled expiry worker with a non-forgeable system audit actor is not implemented; effective authorization is date-checked immediately, while EXPIRED status/audit is swept on authorized admin activity. |
| AT-ENT-PLATFORM-008 tenant/global role separation | Tenant picker/workspace routes are rendered only with an active tenant; platform roles are fetched independently; grant functions and list functions re-check platform authority in PostgreSQL. | IMPLEMENTED CANDIDATE | Exact-head CI; cross-tenant/tenant-owner escalation tests; direct database privilege assertions and route registration check. |

### New source locations

| Concern | File |
|---|---|
| PostgreSQL grant lifecycle and role functions | backend/database/migrations/2026_11_27_000033_create_platform_operator_grant_lifecycle.php |
| Global platform services | backend/app/Services/PlatformAdministrationService.php |
| Grant controller | backend/app/Http/Controllers/Enterprise/PlatformOperatorGrantController.php |
| Global platform context controller | backend/app/Http/Controllers/Enterprise/PlatformAdministrationController.php |
| Global routes | backend/routes/domain-api.php |
| Database/HTTP acceptance | backend/tests/Feature/PlatformAdministrationTest.php |
| Global role session resolution | frontend/src/app/core/auth/auth-session.service.ts and auth-session.service.spec.ts |
| Platform operator workbench | frontend/src/app/features/platform/platform-operators.service.ts, platform-operators.service.spec.ts, platform-operators.component.ts, platform-operators.component.html, platform-operators.component.css |
| Read-only tenant register | frontend/src/app/features/platform/platform-tenants.service.ts, platform-tenants.service.spec.ts, platform-tenants.component.ts, platform-tenants.component.html, platform-tenants.component.css |
| Application shell/routes | frontend/src/app/app.component.html; frontend/src/app/app.routes.ts |
| Security boundary/runbook | docs/security/platform-administration-bootstrap.md |

**Security boundary:** the first real PLATFORM_ADMIN appointment still requires the controlled two-person DBA bootstrap and an actual named identity. The new UI does not self-provision identities, does not seed operator roles, and does not make a tenant OWNER/ADMIN a global administrator. The expiry-date check prevents use after the effective end date; the persisted EXPIRED status/audit sweep runs on a subsequent authorized admin operation, not through a scheduled worker yet.

The fixed WP-00–WP-16 sequence remains in force. All other WP-03 rows above remain PARTIAL/BLOCKED/NOT RUN as previously recorded; this candidate increment does not close tenant retention/legal hold/export/recovery, target-resource scope enforcement, MFA/OIDC/SSO, full installer/operator handover or full WP-03 acceptance.


## Tenant scoped-access database mutation boundary — 27 November 2026

**Status: IMPLEMENTED CANDIDATE — exact-head execution pending.** This is a security hardening increment only; it does not complete target-resource scope enforcement.

| Acceptance scenario | Candidate evidence | Current status | Exact remaining proof |
|---|---|---|---|
| Runtime cannot directly alter scope modes or scope grants | Migration `2026_11_27_000034_secure_tenant_access_scope_mutations.php` revokes all direct table privileges on both tables from PUBLIC and app_runtime, then grants SELECT only. | IMPLEMENTED CANDIDATE | Execute migration on PostgreSQL 18; assert has_table_privilege returns false for INSERT/UPDATE/DELETE and true for SELECT on both tables. |
| Only narrow scope mutation APIs are executable | Three fixed-search-path SECURITY DEFINER functions re-check active tenant, actor OWNER/ADMIN and target MEMBER before changing records. | IMPLEMENTED CANDIDATE | Verify function ownership, grants, RLS bypass under the migration role, PUBLIC execute revocation and forbidden direct calls on PostgreSQL. |
| Mode/grant/revoke mutation and audit are atomic | Each function writes the immutable audit event in the same PostgreSQL transaction; application service no longer writes duplicate audit rows. | IMPLEMENTED CANDIDATE | Run feature tests and induce audit insert failure to prove the associated mutation rolls back. |
| Concurrent duplicate effective grants are blocked | Transaction advisory lock plus overlap check rejects an active grant for the same member/scope/permission and overlapping validity interval. | IMPLEMENTED CANDIDATE | Execute duplicate and concurrent PostgreSQL regression cases under multiple connections. |
| Rollback does not silently restore direct runtime writes | Migration 000034 is forward-only; down() explicitly refuses the downgrade. | DOCUMENTED / CANDIDATE | Validate deploy/recovery runbook uses a forward corrective migration and retains the secure privilege boundary. |
| Target-resource SCOPED authorization | Existing domain Gate remains fail-closed; only BRAND/SITE/MARKET scope grant types exist, and controllers do not yet pass concrete target records into the Gate. | OPEN / FAIL-CLOSED | Implement legal-entity/site/department/warehouse/channel scope identities, pass real target IDs from each action, and prove positive and negative cross-target tests before allowing SCOPED users through any capability. |

**Verification boundary:** PR #13 is still a candidate and is not merged. The latest known runner failure observed on prior head `0e38ff2496597cb9dd559bcde7d4ae7022e588b8` occurred before any Laravel or Angular step executed. It predates migration 000034's final candidate edits. No successful exact-head CI or PostgreSQL run is claimed for this increment.
