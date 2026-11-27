# WP-03 — enterprise SaaS, identity, organization and authorization source map

**Disposition:** substantial canonical foundation exists; platform operator, scoped-access hardening and organization-structure candidates remain unmerged and must be reconciled before promotion.

## Canonical source paths on delivery
- Controllers: `backend/app/Http/Controllers/Enterprise/{TenantController,TenantMembershipController,TenantInvitationController,TenantOwnershipController,LegalEntityController,BusinessSiteController,InventoryLocationController,TenantDomainRoleAssignmentController,AccessScopeController,PlatformAdministrationController}.php`
- Services: `backend/app/Services/{Tenant/TenantContextResolver,TenantMembershipService,TenantInvitationService,TenantOwnershipService,TenantProvisioningService,Authorization/DomainCapabilityAuthorizer,Authorization/TenantDomainRoleAssignmentService,ScopedAccessService,PlatformAdministrationService}.php`
- Middleware/models/support: `backend/app/Http/Middleware/{ResolveTenantContext,ResolveAuthenticatedIdentityContext}.php`, `backend/app/Models/{Tenant,TenantMembership,LegalEntity,User}.php`, `backend/app/Support/TenantContext.php`.
- Migrations: `2026_11_22_000001` tenant/entity/site foundation; `2026_11_24_000013` membership lifecycle; `000014` invitations; `2026_11_26_000027` domain roles; `000028` revoke roles; `000029` non-overlapping periods; `000030` locked membership reader; `000031` owner succession/exit; `000032` platform entitlements/lifecycle.
- Frontend: `frontend/src/app/features/auth/` and `frontend/src/app/features/enterprise/`.
- Tests: `backend/tests/Feature/{TenantIsolationTest,TenantMembershipLifecycleTest,TenantInvitationLifecycleTest,TenantProvisioningServiceTest,PlatformAdministrationTest}.php`; WP-03 candidate tests remain on their source branch.

## Collected/candidate source
- Source-intake local app includes fixed Stockland roles/locations and a SQLite identity model; it is reference input only.
- PR #13 / `work/wp03-completion-2026-11-27`: platform operator/scoped access candidate.
- PR #21 / `work/wp03-org-master-runtime-2026-11-27`: `org_units`, `job_positions`, manager assignments and reporting relations candidate.
- PR #19 / `work/wp03-wp14-organogram-payroll-plan-2026-11-27` and PR #25 / `work/wp03-full-engineering-design-2026-11-27`: organogram/DOA design evidence.

## Remaining build / verification
Finish WP-03 definition of done: delegated organization structure, job/position reporting, platform operator grant lifecycle, tenant/member scope design, API/RLS/identity isolation, concurrency and recovery tests, UI, operations bootstrap, and exact-SHA handover. Re-sequence migration collisions before integration. Scope must never weaken owner succession, RLS, or app_runtime least privilege.
