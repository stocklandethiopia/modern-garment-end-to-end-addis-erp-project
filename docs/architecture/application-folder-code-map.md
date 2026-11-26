# Application folder and code-location map

**Repository:** `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`  
**Baseline inspected:** delivery commit `f7516de080c24ecd71ce599820e91f8f03ebab56`  
**Working branch:** `work/wp03-identity-platform-completion-2026-11-26`  
**Purpose:** authoritative path map for the Laravel API, Angular application, migrations, tests, route registration and governed project documentation.

This map records the tracked code paths that existed at the inspected delivery head. It is a location index, not a claim that every listed domain is complete. A file being present only proves that source exists; the work-package handover register and exact-SHA test evidence determine implementation/acceptance status.

## 1. Repository layout

```text
repository-root/
├── .github/workflows/           # CI and package-verification workflows
├── AGENTS.md                    # contributor/agent rules
├── README.md                    # product entry point and setup links
├── Makefile                     # developer tasks
├── docker-compose.yml           # local PostgreSQL/Redis services
├── docker/postgres/init/        # restricted DB role bootstrap
├── backend/                     # Laravel 13 / PHP 8.5 API
├── frontend/                    # Angular 22 / TypeScript web client
├── docs/                        # approved product, architecture and WP evidence
└── work-packages/               # versioned source-package evidence and checksums
```

**Inspected inventory:** 286 tracked files across 385 tree entries. The primary application code is under `backend/app/` and `frontend/src/app/`. No additional product/runtime application exists at the repository root.

## 2. Backend — Laravel API

### 2.1 Application source

```text
backend/app/
├── Console/Commands/
│   └── ProvisionTenantOwner.php
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── Agents/AgentGovernanceController.php
│   │   ├── Auth/TokenController.php
│   │   ├── Distribution/FinishedGoodsController.php
│   │   ├── Enterprise/
│   │   │   ├── AccessScopeController.php
│   │   │   ├── BusinessSiteController.php
│   │   │   ├── InventoryLocationController.php
│   │   │   ├── LegalEntityController.php
│   │   │   ├── LicensingEvidenceController.php
│   │   │   ├── TenantController.php
│   │   │   ├── TenantDomainRoleAssignmentController.php
│   │   │   ├── TenantInvitationController.php
│   │   │   └── TenantMembershipController.php
│   │   ├── Finance/FinanceControlController.php
│   │   ├── Integration/DocumentIntegrationController.php
│   │   ├── Manufacturing/ManufacturingController.php
│   │   ├── Merchandising/
│   │   │   ├── AssortmentAccountTermsController.php
│   │   │   ├── CommercialPricingController.php
│   │   │   ├── DemandPlanningController.php
│   │   │   ├── EvidencePolicyController.php
│   │   │   ├── InitiativeController.php
│   │   │   ├── InsightSourceController.php
│   │   │   ├── MarketInsightController.php
│   │   │   ├── MerchandisingPlanningController.php
│   │   │   ├── MerchandisingReportingController.php
│   │   │   └── OtbPlanningController.php
│   │   ├── Planning/IndustrialEngineeringController.php
│   │   ├── ProductDevelopment/
│   │   │   ├── BomTechnicalPackController.php
│   │   │   ├── MeasurementPatternController.php
│   │   │   ├── ProductDevelopmentController.php
│   │   │   └── SampleFitController.php
│   │   ├── Quality/QualityController.php
│   │   └── Sourcing/SourcingController.php
│   └── Middleware/
│       ├── EnsureTokenAbility.php
│       ├── ResolveAuthenticatedIdentityContext.php
│       └── ResolveTenantContext.php
├── Models/
│   ├── LegalEntity.php
│   ├── Tenant.php
│   ├── TenantMembership.php
│   └── User.php
├── Notifications/TenantInvitationNotification.php
├── Providers/AppServiceProvider.php
├── Services/
│   ├── Agents/AgentGovernanceService.php
│   ├── Authorization/
│   │   ├── DomainCapabilityAuthorizer.php
│   │   └── TenantDomainRoleAssignmentService.php
│   ├── Distribution/FinishedGoodsService.php
│   ├── Finance/FinanceControlService.php
│   ├── Integration/
│   │   ├── DeviceScanResolutionService.php
│   │   ├── DocumentIntegrationService.php
│   │   └── IntegrationOutboxService.php
│   ├── Inventory/InventoryLedgerService.php
│   ├── Manufacturing/ManufacturingExecutionService.php
│   ├── Merchandising/
│   │   ├── AssortmentAccountTermsService.php
│   │   ├── EvidencePolicyService.php
│   │   ├── EvidenceSufficiencyService.php
│   │   └── MerchandisingDashboardService.php
│   ├── Planning/IndustrialEngineeringService.php
│   ├── ProductDevelopment/BomTechnicalPackService.php
│   ├── Quality/QualityControlService.php
│   ├── Sourcing/SourcingService.php
│   ├── Support/DecimalQuantity.php
│   ├── Tenant/
│   │   └── TenantContextResolver.php
│   ├── ScopedAccessService.php
│   ├── TenantInvitationService.php
│   ├── TenantMembershipService.php
│   └── TenantProvisioningService.php
└── Support/TenantContext.php
```

**Backend responsibility rules**

| Location | Owns |
|---|---|
| `Http/Controllers/{Domain}/` | HTTP request validation, authorization invocation, response contract; no cross-domain business orchestration in controllers |
| `Http/Middleware/` | Authentication identity, tenant transaction context and token-ability enforcement |
| `Models/` | Laravel model identity/relationships; not a substitute for domain services or database controls |
| `Services/{Domain}/` | Domain use cases, transaction boundaries, business invariants and audit/outbox effects |
| `Services/Authorization/` | Tenant membership, role-to-capability and assignment controls |
| `Services/Integration/` | Device scan resolution, document ingestion and transactional outbox/retry |
| `Services/Support/` | Shared low-level helpers (for example exact decimal quantity), not business ownership |
| `Providers/AppServiceProvider.php` | Application boot and Gate/capability registration |
| `Console/Commands/` | Controlled administrative commands; owner provisioning is not an HTTP signup shortcut |

### 2.2 Laravel boot, routes and configuration

```text
backend/
├── artisan
├── bootstrap/
│   ├── app.php                  # Laravel application and route/middleware bootstrap
│   └── providers.php            # provider registration
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── hashing.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── sanctum.php
│   └── session.php
├── routes/
│   ├── api.php                  # /api/v1 routes; includes domain-api.php
│   ├── domain-api.php           # domain API routes under auth + tenant middleware
│   └── console.php
├── public/index.php
├── composer.json
├── composer.lock
└── phpunit.xml
```

Route registration is deliberately explicit: `bootstrap/app.php` loads `routes/api.php`; `api.php` includes `routes/domain-api.php` at its end. New route files must be registered deliberately and tested with `php artisan route:list`. Do not create a second business API or duplicate routes in a different stack.

### 2.3 Database ownership and migrations

`backend/database/migrations/` contains the ordered PostgreSQL schema history. The inspected delivery tree has the initial user migration and the 27 numbered enterprise/domain migrations through:

`2026_11_26_000027_create_tenant_domain_role_assignments.php` and WP-03 lifecycle/ownership guards `2026_11_26_000028_revoke_domain_roles_on_membership_change.php`, `2026_11_26_000029_enforce_non_overlapping_domain_role_periods.php`, `2026_11_26_000030_add_locked_tenant_membership_reader.php` and `2026_11_26_000031_add_tenant_owner_succession_and_exit.php`

Migration ownership by sequence:

| Migration range | Responsibility |
|---|---|
| `0001_01_01_000000` | Global user identity |
| `2026_11_22_000001–000003` | Tenant/entity/site/location foundation, access token and licensing evidence |
| `2026_11_22_000004–000008` | Initiative/market insight, brand/range planning and evidence policies |
| `2026_11_22_000009–000012` | Samples/fit, scoped access, member scope verification, assortment terms, BOM/tech-pack controlled releases |
| `2026_11_24_000013–000014` | Secure membership lifecycle and tenant invitations |
| `2026_11_26_000015–000021` | Sourcing/material traceability, routes/MES, quality/CAPA, distribution, IE/workforce, finance/payroll draft controls, documents/integrations/agents |
| `2026_11_26_000022–000026` | Receipt genealogy/immutability, finished-goods reservation, finance/agent uniqueness and customer-credit controls |
| `2026_11_26_000027–000031` | Tenant domain-role assignment foundation, automatic role revocation, database-enforced effective periods, locked membership lookup, single active owner invariant, ownership succession and safe voluntary tenant exit |

**Migration rules:** every tenant-owned table must have tenant-leading keys/FKs, forced PostgreSQL RLS and minimum explicit runtime grants. Use `erp_migrator` for migrations and `app_runtime NOBYPASSRLS` for application requests. Append-only ledgers and audit events must not receive broad runtime UPDATE/DELETE privileges. Do not rename/reorder migrations already recorded in any environment without an explicit migration-history compatibility plan.

### 2.4 Tests and local runtime

```text
backend/
├── tests/
│   ├── TestCase.php
│   ├── Feature/
│   │   ├── BrandRangeWorkflowTest.php
│   │   ├── DemandOtbPricingWorkflowTest.php
│   │   ├── DomainRlsSecurityTest.php
│   │   ├── HealthTest.php
│   │   ├── InitiativeWorkflowTest.php
│   │   ├── PersonalAccessTokenTest.php
│   │   ├── TenantInvitationLifecycleTest.php
│   │   ├── TenantIsolationTest.php
│   │   ├── TenantMembershipLifecycleTest.php
│   │   └── TenantProvisioningServiceTest.php
│   └── Unit/
│       └── DecimalQuantityTest.php
├── storage/                     # runtime writable paths; only .gitignore markers tracked
└── .env.example                 # documented environment variables; never real credentials
```

Run backend tests from `backend/` using the pinned Composer/PHP toolchain and PostgreSQL 18 test roles. Test fixtures must use the dedicated test-admin connection; the HTTP application must run as the restricted runtime role. Do not treat SQLite tests as equivalent to PostgreSQL RLS acceptance.

## 3. Frontend — Angular application

### 3.1 Application source

```text
frontend/src/app/
├── app.component.ts
├── app.component.html
├── app.component.css
├── app.routes.ts                # route registry / lazy feature entry points
├── core/
│   ├── api/
│   │   ├── api-health.service.ts
│   │   ├── connection-state.ts
│   │   └── connection-state.spec.ts
│   └── auth/
│       ├── auth-session.service.ts
│       └── auth.interceptor.ts
├── features/
│   ├── auth/
│   │   ├── login.component.ts
│   │   ├── login.component.html
│   │   ├── login.component.css
│   │   ├── tenant-picker.component.ts
│   │   ├── tenant-picker.component.html
│   │   └── tenant-picker.component.css
│   ├── enterprise/
│   │   ├── business-site.service.ts
│   │   ├── business-sites.component.ts
│   │   ├── business-sites.component.html
│   │   ├── business-sites.component.css
│   │   ├── legal-entity.service.ts
│   │   ├── legal-entities.component.ts
│   │   ├── legal-entities.component.html
│   │   ├── legal-entities.component.css
│   │   ├── tenant-member.service.ts
│   │   ├── tenant-members.component.ts
│   │   ├── tenant-members.component.html
│   │   └── tenant-members.component.css
│   ├── merchandising/
│   │   ├── evidence-policy.service.ts
│   │   ├── evidence-policy-workbench.component.ts
│   │   ├── evidence-policy-workbench.component.html
│   │   ├── evidence-policy-workbench.component.css
│   │   ├── evidence-policy-workbench.component.spec.ts
│   │   ├── initiatives.component.ts
│   │   ├── initiatives.component.html
│   │   ├── initiatives.component.css
│   │   ├── market-insights.component.ts
│   │   ├── market-insights.component.html
│   │   ├── market-insights.component.css
│   │   ├── merchandising-dashboard.component.ts
│   │   ├── merchandising-dashboard.component.html
│   │   ├── merchandising-dashboard.component.css
│   │   ├── merchandising-dashboard.component.spec.ts
│   │   ├── merchandising.service.ts
│   │   ├── merchandising.service.spec.ts
│   │   ├── planning-workbench.component.ts
│   │   ├── planning-workbench.component.html
│   │   ├── planning-workbench.component.css
│   │   ├── planning-workbench.service.ts
│   │   ├── planning-workbench.service.spec.ts
│   │   ├── range-planning.component.ts
│   │   ├── range-planning.component.html
│   │   ├── range-planning.component.css
│   │   ├── range-planning.service.ts
│   │   └── range-planning.service.spec.ts
│   └── product-development/
│       ├── product-development.component.ts
│       ├── product-development.component.html
│       ├── product-development.component.css
│       ├── product-development.service.ts
│       ├── product-development.service.spec.ts
│       ├── technical-product-development.component.ts
│       ├── technical-product-development.component.html
│       ├── technical-product-development.component.css
│       ├── sample-fit.component.ts
│       ├── sample-fit.component.html
│       └── sample-fit.component.css
└── pages/
    └── overview/
        ├── overview.component.ts
        ├── overview.component.html
        └── overview.component.css
```

### 3.2 Frontend entry/configuration files

```text
frontend/
├── angular.json                 # Angular CLI build/test targets
├── package.json                 # direct dependency and script contract
├── package-lock.json            # reproducible npm dependency graph
├── proxy.conf.json              # local development API proxy
├── tsconfig.json
├── tsconfig.app.json
├── tsconfig.spec.json
└── src/
    ├── main.ts                  # application bootstrap
    ├── index.html               # document shell
    └── styles.css               # global design tokens/reset/shell styles
```

**Frontend responsibility rules**

| Location | Owns |
|---|---|
| `core/auth/` | token/session state and HTTP authorization header behavior |
| `core/api/` | API health/connection state; do not duplicate domain data ownership |
| `features/{domain}/*.service.ts` | typed HTTP API access for that feature |
| `features/{domain}/*.component.ts` | view state, user interactions and feature orchestration |
| `*.component.html` | semantic template only; keep business rules out of templates |
| `*.component.css` | component-scoped presentation |
| `app.routes.ts` | route table; new feature pages must be registered here |
| `pages/overview/` | role-aware landing/overview experience, not a second data store |

Backend and frontend domain boundaries must match. A new Laravel controller/service is not a complete user workflow until its authorized Angular service, route, task UI, positive/negative UI tests and documented operating path are delivered.

## 4. WP-03 code locations — current identity and tenant foundation

| Concern | Backend source | Frontend source | Schema / evidence |
|---|---|---|---|
| Global authentication/tokens | `backend/app/Http/Controllers/Auth/TokenController.php`; `backend/app/Http/Middleware/EnsureTokenAbility.php` | `features/auth/login.component.*`; `core/auth/auth-session.service.ts`; `auth.interceptor.ts` | User and personal-access-token migrations; `PersonalAccessTokenTest.php` |
| Tenant listing/selection | `Enterprise/TenantController.php`; `ResolveTenantContext.php`; `TenantContext.php` | `features/auth/tenant-picker.component.*` | Tenant/membership RLS; `TenantIsolationTest.php` |
| Tenant provisioning | `Console/Commands/ProvisionTenantOwner.php`; `TenantProvisioningService.php` | Operator-driven bootstrap; no public owner-signup screen | Enterprise migration; `TenantProvisioningServiceTest.php` |
| Tenant members/roles | `Enterprise/TenantMembershipController.php`; `TenantMembershipService.php`; `TenantMembership.php` | `features/enterprise/tenant-member.service.ts`; `tenant-members.component.*` | Membership lifecycle migration; `TenantMembershipLifecycleTest.php` |
| Invitations | `Enterprise/TenantInvitationController.php`; `TenantInvitationService.php`; `Notifications/TenantInvitationNotification.php` | Invitation registration is API-backed; dedicated admin invitation UI remains open | Invitation migration; `TenantInvitationLifecycleTest.php`, `TenantMembershipLifecycleTest.php` (membership-to-domain-role revocation, role promotion and effective-period overlap cases) |
| Legal entities/sites/locations | `Enterprise/LegalEntityController.php`; `BusinessSiteController.php`; `InventoryLocationController.php` | `legal-entity.service.ts`, `legal-entities.component.*`, `business-site.service.ts`, `business-sites.component.*` | Enterprise foundation migration and related tests |
| Scoped access/domain roles | `Enterprise/AccessScopeController.php`; `TenantDomainRoleAssignmentController.php`; `ScopedAccessService.php`; `Authorization/DomainCapabilityAuthorizer.php`; `TenantDomainRoleAssignmentService.php` | Tenant-level role register API: `GET/POST /api/v1/enterprise/domain-role-assignments`, plus approve/revoke actions; admin UI remains open | Migrations 000010 and 000027–000030; domain-role periods prevent overlapping active/pending grants; membership locks are executed by a narrowly granted SECURITY DEFINER function so app_runtime does not need direct UPDATE privilege on tenant_memberships. membership revocation or promotion to tenant ADMIN automatically ends active/pending domain roles and writes audit evidence. Complete entity/site/department/warehouse/channel enforcement remains open |
| Owner succession and voluntary leave | `Enterprise/TenantOwnershipController.php`; `TenantOwnershipService.php`; `public.transfer_tenant_ownership(uuid,text)`; `public.leave_tenant(text)` | `POST /api/v1/enterprise/ownership/transfer`; `POST /api/v1/enterprise/membership/leave` | Migration 000031 enforces one active OWNER per tenant, serialized handover, deferred owner-count guard, reasoned audit, and blocks an OWNER from leaving before transfer |
| Identity request context | `ResolveAuthenticatedIdentityContext.php`; `TenantContextResolver.php` | Auth session/interceptor | Context is request/transaction-local; verify reuse/concurrency and revocation paths |

**WP-03 is not complete.** The existing code is a foundation. The accepted architecture still requires owner succession/leave and last-owner controls, complete tenant lifecycle/platform entitlements, effective-dated roles tied to actual organization/site/department/warehouse/channel data, downstream target-record scope checks, MFA/OIDC/SSO and session recovery/rotation, complete invitation/operator UIs, and installer/recovery/rollback/deployment handover.

## 5. Continuous change and clean repository rules

1. Work on one dedicated `work/wpNN-...` branch created from the current `delivery/autonomous-wp-01-16` head.
2. Push commits only to that work branch during implementation. Do not make working edits directly on `main` or the delivery branch.
3. Keep the delivery branch as the verified integration/release-candidate line. Open a PR from the work branch to delivery after scope, tests and exact-SHA CI are ready; the standing owner authorization removes routine acceptance waits, not technical gates.
4. Keep `main` reserved for final WP-16 production-release promotion.
5. Keep dependency lockfiles, migration history, source-package checksums and required runtime directory markers. Do not commit `.env`, credentials, tokens, `vendor/`, `node_modules/`, Angular `dist/`, coverage, local DB files, temporary archives or editor caches.
6. Remove a workflow/script only when it has no required trigger, no active consumer and no accepted evidence responsibility. Preserve forensic/audit evidence branches and source provenance until their disposition is recorded.
7. Every new endpoint requires an authorization policy, tenant/RLS tests, a documented API route, a caller/UI or an explicit reason it is system-only, and an entry in the task/traceability register.
8. When code moves folders or responsibilities change, update this map in the same PR.

## 6. Inspected repository hygiene

- `.gitignore` already excludes secrets, `.env`, Composer `vendor/`, npm `node_modules/`, Angular `dist/`, coverage, Python caches, local Docker state and IDE files. Keep it.
- Laravel `bootstrap/cache` and `storage` contain only tracked `.gitignore` markers; these are required writable runtime directory placeholders, not disposable build outputs.
- `composer.lock` and `package-lock.json` are required reproducibility inputs.
- `work-packages/wp-01-requirements-traceability/source/WP01_R3_SOURCE_2026-11-26.tar.xz.b64.part-00..17` are required source-package payload segments. They are not temporary build output; the WP-01 workflow reconstructs and verifies their archive checksum.
- The temporary `.github/workflows/workspace-snapshot.yml` was removed on this working branch because it only ran when that workflow file itself changed and added no recurring verification or delivery gate.
- Historical source-intake/audit branches are not app folders. Preserve them until the source audit's evidence disposition is explicitly closed; do not merge them into the runtime tree wholesale.
- No unneeded compiled application bundle or binary installer was found in the inspected delivery tree.

**Maintenance rule:** this document must be kept synchronized with the real tree. The directory listing above reflects the inspected baseline; later WP-specific additions must be appended in the same branch/PR that adds the code.


## WP-03 platform administration and entitlement extension

| Concern | Exact source file | Route / database contract | Security and status |
|---|---|---|---|
| Platform tenant register/lifecycle | `backend/app/Http/Controllers/Enterprise/PlatformAdministrationController.php`; `backend/app/Services/PlatformAdministrationService.php` | `GET /api/v1/platform/tenants`; `PATCH /api/v1/platform/tenants/{tenantId}/lifecycle` | Runs with authenticated global identity context, not tenant OWNER/ADMIN authority. Database function requires a valid PLATFORM_ADMIN or PLATFORM_SUPPORT grant (read-only for support). |
| Platform entitlements | `PlatformAdministrationController.php`; `PlatformAdministrationService.php`; `TenantEntitlementController.php` | `PUT /api/v1/platform/tenants/{tenantId}/entitlements`; `GET /api/v1/tenant/entitlements` | Platform change requires PLATFORM_ADMIN; tenant user can read only the current tenant's entitlement through tenant-context RLS/function. |
| Platform bootstrap / role source | `docs/security/platform-administration-bootstrap.md` | `platform_operator_grants` | No application INSERT/UPDATE/DELETE privilege; no operator is seeded. Initial operator grants require controlled DBA provisioning, distinct grantor/approver identities and a documented approval. |
| Platform schema and lifecycle functions | `backend/database/migrations/2026_11_26_000032_add_platform_entitlements_and_tenant_lifecycle.php` | `platform_operator_grants`, `platform_tenant_entitlements`, `platform_audit_events`; `platform_list_tenants()`, `platform_change_tenant_status()`, `platform_set_tenant_entitlements()`, `platform_get_current_entitlement()` | Fixed-search-path SECURITY DEFINER functions; platform audit is append-only; app_runtime has read-only RLS-scoped entitlement and self-grant reads, and no direct writes to platform tables. |
| Platform acceptance tests | `backend/tests/Feature/PlatformAdministrationTest.php` | Test list, support read-only, entitlement change/read, lifecycle suspend/reactivate/close, and runtime privilege assertions. | New exact-head PostgreSQL CI required; this change is not yet accepted until all tests pass. |
