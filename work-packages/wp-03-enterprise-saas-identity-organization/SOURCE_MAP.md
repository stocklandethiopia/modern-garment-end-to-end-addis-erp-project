# WP-03 — enterprise SaaS, identity, organization and authorization source map

**Disposition:** substantial canonical foundation exists; platform operator, scoped-access hardening and organization-structure candidates remain unmerged and must be reconciled before promotion.

## Canonical source paths on delivery
- Controllers: `backend/app/Http/Controllers/Enterprise/{TenantController,TenantMembershipController,TenantInvitationController,TenantOwnershipController,LegalEntityController,BusinessSiteController,InventoryLocationController,TenantDomainRoleAssignmentController,AccessScopeController,PlatformAdministrationController}.php`
- Services: `backend/app/Services/{Tenant/TenantContextResolver,TenantMembershipService,TenantInvitationService,TenantOwnershipService,TenantProvisioningService,Authorization/DomainCapabilityAuthorizer,Authorization/TenantDomainRoleAssignmentService,ScopedAccessService,PlatformAdministrationService}.php`
- Middleware/models/support: `backend/app/Http/Middleware/{ResolveTenantContext,ResolveAuthenticatedIdentityContext}.php`, `backend/app/Models/{Tenant,TenantMembership,LegalEntity,User}.php`, `backend/app/Support/TenantContext.php`.
- Migrations: `2026_11_22_000001` tenant/entity/site foundation; `2026_11_24_000013` membership lifecycle; `000014` invitations; `2026_11_26_000027` domain roles; `000028` revoke roles; `000029` non-overlapping periods; `000030` locked membership reader; `000031` owner succession/exit; `000032` platform entitlements/lifecycle.
- Frontend: `frontend/src/app/features/auth/` and `frontend/src/app/features/enterprise/`.
- Tests: `backend/tests/Feature/{TenantIsolationTest,TenantMembershipLifecycleTest,TenantInvitationLifecycleTest,TenantProvisioningServiceTest,PlatformAdministrationTest}.php`; WP-03 candidate tests remain on their source branch.

## Collected/candidate source — refreshed 28 November 2026
- Source-intake local app includes fixed Stockland roles/locations and a SQLite identity model; it is reference input only.
- PR #13 / `work/wp03-completion-2026-11-27`, head `735d404fdf996e58bd07140cd900dc55c50c13ed`: platform operator/scoped-access candidate. It is stale/diverged against delivery `3438ab47240c10ce5ea5f1f772c1570cc2191134` (122 ahead / 78 behind; merge base `f948bb85de4194ae18dc8ee8c974c3ad0986623c`); PR mergeable=false; exact-head workflow run was not observed in the current commit lookup. Candidate migrations 000033, 000034 and 000037 must enter the CAP-030 train.
- PR #29 / `work/wp03-org-structure-canonical-2026-11-28`, head `c716757e3b0fe091b2e6223b416237d32e455c8e`: current-base organization-master candidate; migration 000035. ERP CI #46089028046 and component/WP-01 verification #46089027993 passed on this candidate head. It remains unmerged; 125+ stress/browser/accessibility/organization acceptance remains NOT RUN.
- PR #21 / `work/wp03-org-master-runtime-2026-11-27` was superseded/closed and is not the active organization candidate. Preserve its review history; do not duplicate its implementation.
- PR #16 / `work/enterprise-organogram-doa-2026-11-27` and PR #19 / `work/wp03-wp14-organogram-payroll-plan-2026-11-27`: organogram/DoA design evidence, documentation candidates on older delivery base.
- PR #24 / `work/wp03-full-engineering-design-2026-11-27`: WP-03 engineering design; PR #25 / `work/wp03-enterprise-technical-design-2026-11-27`: separate enterprise technical design. Do not misattribute the branch names.
- The full live inventory and migration collision register is in `docs/audit/corrective-action-execution-ledger-2026-11-28.md`.

## Remaining build / verification
Finish WP-03 definition of done: delegated organization structure, job/position reporting, platform operator grant lifecycle, tenant/member scope design, API/RLS/identity isolation, concurrency and recovery tests, UI, operations bootstrap, and exact-SHA handover. Re-sequence migration collisions before integration. Scope must never weaken owner succession, RLS, or app_runtime least privilege.
