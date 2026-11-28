# WP-03 — enterprise SaaS, identity, organization and authorization source map

**Disposition:** PARTIAL — the canonical tenant/identity foundation is integrated; platform/operator and organization-structure candidate increments are now exact-head verified but remain separate review candidates pending one migration-train reconciliation.

## Canonical source paths on delivery
- Controllers: `backend/app/Http/Controllers/Enterprise/{TenantController,TenantMembershipController,TenantInvitationController,TenantOwnershipController,LegalEntityController,BusinessSiteController,InventoryLocationController,TenantDomainRoleAssignmentController,AccessScopeController,PlatformAdministrationController}.php`
- Services: `backend/app/Services/{Tenant/TenantContextResolver,TenantMembershipService,TenantInvitationService,TenantOwnershipService,TenantProvisioningService,Authorization/DomainCapabilityAuthorizer,Authorization/TenantDomainRoleAssignmentService,ScopedAccessService,PlatformAdministrationService}.php`
- Middleware/models/support: `backend/app/Http/Middleware/{ResolveTenantContext,ResolveAuthenticatedIdentityContext}.php`, `backend/app/Models/{Tenant,TenantMembership,LegalEntity,User}.php`, `backend/app/Support/TenantContext.php`.
- Integrated migrations through 000032: tenant/entity/site foundation; membership and invitations; effective-dated domain roles and revocation; owner succession/exit; platform lifecycle and entitlements.
- Frontend: `frontend/src/app/features/auth/` and `frontend/src/app/features/enterprise/`.
- Integrated tests: `backend/tests/Feature/{TenantIsolationTest,TenantMembershipLifecycleTest,TenantInvitationLifecycleTest,TenantProvisioningServiceTest,PlatformAdministrationTest}.php`.

## Verified WP-03 candidate source — platform/operator and access control (PR #13)
- Branch: `work/wp03-completion-2026-11-27`; exact tested head `735d404fdf996e58bd07140cd900dc55c50c13ed`.
- Migrations: `000033_create_platform_operator_grant_lifecycle`, `000034_secure_tenant_access_scope_mutations`, `000037_isolate_identity_control_functions`.
- Source includes isolated `app_control` execution for selected SECURITY DEFINER functions, non-forgeable privilege boundary relative to `app_runtime`, two-person operator bootstrap contract, effective-dated operator grants, scoped-access API error mapping, malformed UUID validation, and 30-second active-session platform-context refresh.
- Seven review findings are fixed and their GitHub threads resolved. Combined WP verification [#46085083642](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46085083642) passed all three jobs. This PR remains separate pending migration-train reconciliation.

## Verified WP-03 candidate source — organization structure runtime (PR #29)
- Branch: `work/wp03-org-structure-canonical-2026-11-28`; exact tested head `2396aae8af329750bb9e6c7e1ef206652588521f`.
- Backend: `backend/app/Http/Controllers/Enterprise/EnterpriseOrganizationController.php`; `backend/app/Services/Enterprise/EnterpriseOrganizationService.php`.
- Migration `2026_11_27_000035_create_organization_structure_masters.php` creates `org_units`, `job_positions`, `org_unit_manager_assignments` and `position_reporting_relations`, with tenant-leading relationships, forced RLS and SELECT/INSERT-only runtime grants for create-only structural masters.
- API routes in `backend/routes/domain-api.php`: organization-units, unit manager assignments, job-positions and position-reporting-relations; Angular route `/enterprise/organization-structure` is registered in `frontend/src/app/app.routes.ts`.
- Angular source: `frontend/src/app/features/enterprise/enterprise-organization.service.ts`; `organization-structure.component.ts`, `.html`, `.css` and `.spec.ts`; navigation is added through `frontend/src/app/app.component.ts`.
- PostgreSQL feature coverage: `backend/tests/Feature/EnterpriseOrganizationStructureTest.php` tests tenant isolation, role boundaries, effective dates, position/person separation, manager assignments, reporting cycles, audit/outbox, basic pagination, forced RLS and restricted grants. Detailed remaining scenarios are in `docs/verification/WP03-implementation-acceptance-matrix.md`.
- Four graph-mutating operations share a tenant-scoped transaction advisory lock. Five incompatible `SELECT ... FOR UPDATE` reads were removed; no broader `app_runtime` UPDATE grant was added.
- ERP CI [#46088130006](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46088130006) and combined WP verification [#46088130010](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46088130010) both passed; the combined backend suite recorded 106 tests / 1,232 assertions, with migration/RLS, Angular build/tests, WP-01 R3 integrity and artifact assembly successful.
- Candidate artifact ID `15239349964`, 1,145,924 bytes, SHA-256 `83f1c4d60a98903115f6d4d4654099f500cbf3395a6a4c264047dffd3b34e7c3`.

## Migration integration stop
- PR #13 uses candidate migrations 000033/000034/000037; PR #29 uses 000035; WP-14 PR #20 uses 000033/000034/000036; WP-15 PR #23 uses 000033; WP-11/WP-15 PR #27 uses 000038.
- Reconcile these candidates against fresh delivery and package dependency order, assign unique migration identities before any candidate integration, and validate the entire ordered history from empty PostgreSQL 18 under the restricted migrator. Do not rewrite migrations already applied in a real environment without an explicit compatibility plan.

## Remaining WP-03 build / verification
- Complete employee/person-to-position incumbency and position-based delegated authority in the fixed package sequence.
- Implement and prove target-record entity/site/department/warehouse/channel scope enforcement; keep `SCOPED` authorization fail-closed until each target path is complete.
- Complete tenant lifecycle export/archive, retention/legal hold, controlled recovery, MFA/OIDC/SSO, session rotation, invitation/domain-role/operator interfaces, scheduled grant expiry/revocation and operator/bootstrap recovery.
- Complete the WP-03 requirement/acceptance matrix, API/UI/negative/concurrency/rollback tests, installer/operator documentation and numbered handover. Passing component CI does not complete WP-03.
