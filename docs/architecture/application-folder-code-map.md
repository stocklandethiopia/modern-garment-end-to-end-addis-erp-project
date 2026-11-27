# Application folder and code-location map

**Repository:** `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`  
**Baseline inspected:** delivery commit `bd41c83e141835db3c0f03ac8d711096728a07b4`  
**Active WP-03 branch:** `work/wp03-completion-2026-11-27`, created from canonical delivery head `f948bb85de4194ae18dc8ee8c974c3ad0986623c`.  
**Prior WP-03 evidence branches:** `work/wp03-identity-platform-completion-2026-11-26` and `agent/chatgpt-wp03-continuation-2026-11-27`; these remain historical evidence and are not the active working branch.  
**Integrated WP-03 baseline:** PR #9 merged at `bd41c83e141835db3c0f03ac8d711096728a07b4`; exact-head ERP CI [#45899259026](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259026) and combined WP verification [#45899259071](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259071) passed. The current continuation added privileged tenant-register read auditing, verified in PR #11 on exact SHA `92d99588f10a281ef41ae87e27deeb1b6a31ff70` (ERP CI #45900565705; combined WP verification #45900565706; 93 backend tests / 1,081 assertions; artifact ID `15155567006`).  
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

**Delivery baseline inventory (`bd41c83`):** 286 tracked files across 385 tree entries.  
**Current PR #13 WP-03 candidate inventory at tree `b1626e8`:** 318 tracked files across 418 tree entries, including 74 files under `backend/app/`, 37 migrations, 14 backend test files and 81 files under `frontend/src/app/`. The separate WP-14 payroll and WP-03 organogram branches are not included in this PR #13 inventory. The primary application code is under `backend/app/` and `frontend/src/app/`; no additional product/runtime application exists at the repository root.

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

`backend/database/migrations/` contains the ordered PostgreSQL schema history. The PR #13 candidate tree contains the initial user migration and numbered enterprise/domain migrations through 000032, plus candidate platform/security migrations 000033, 000034 and 000037. Organogram and payroll migrations 000035 and 000036 remain separate in PR #21 and PR #20.

`2026_11_26_000027_create_tenant_domain_role_assignments.php` and WP-03 lifecycle/ownership guards `000028`–`000031`, platform lifecycle `000032`, operator grant lifecycle `000033`, tenant-scope mutation hardening `000034`, and isolated control-function privileges `000037`.

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

**Migration rules:** every tenant-owned table must have tenant-leading keys/FKs, forced PostgreSQL RLS and minimum explicit grants. Use `erp_migrator` for migrations, `app_runtime NOBYPASSRLS` for normal HTTP requests, and the separate non-superuser `app_control` login only for explicit, parameterized SECURITY DEFINER control API functions. `app_control` has no table DML, schema CREATE or RLS bypass. Never expose its credential to browser/client code. Append-only ledgers and audit events must not receive broad runtime UPDATE/DELETE privileges. Do not rename/reorder migrations already recorded in any environment without an explicit migration-history compatibility plan.

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
| Platform administration and entitlement foundation | `Enterprise/PlatformAdministrationController.php`; `Enterprise/PlatformOperatorGrantController.php`; `Enterprise/TenantEntitlementController.php`; `PlatformAdministrationService.php`; `ResolveAuthenticatedIdentityContext.php` | Candidate platform tenant register and operator-grant workbenches; APIs expose platform context, list/request/approve/revoke, tenant lifecycle and entitlement actions; tenant entitlement remains read-only | Migrations 000032/000033/000034/000037; identity-sensitive SECURITY DEFINER functions are EXECUTE-only for `app_control` and not executable by `app_runtime`; both roles have no direct platform/scope table DML. Authenticated actor/tenant context is set transaction-locally by parameterized Laravel services on `pgsql_control`. Tests cover function/table privileges and forged-GUC denial; `/api/v1/platform` uses global identity context, not tenant membership. Global register reads append `PLATFORM_TENANT_REGISTER_VIEWED`.
| Identity request context | `ResolveAuthenticatedIdentityContext.php`; `TenantContextResolver.php` | Auth session/interceptor | Context is request/transaction-local; verify reuse/concurrency and revocation paths |

**WP-03 is not complete.** Tenant provisioning, membership/invitation services, domain-role lifecycle, owner succession/leave, single-active-owner invariant and platform administration are implemented foundations/candidates as recorded below. PR #9 integrated platform lifecycle and entitlement migration 000032 at delivery commit `bd41c83e141835db3c0f03ac8d711096728a07b4`; exact-head CI passed on source `f0688d04c9d9f28722a783086c7efd15500798a2` with **93 backend tests / 1,079 assertions**, **27 Angular tests / 7 files**, Postgres 18 migrations/forced RLS, and combined WP candidate assembly. `PlatformAdministrationTest.php` contains seven focused feature tests. The current continuation adds append-only `PLATFORM_TENANT_REGISTER_VIEWED` events for authorized PLATFORM_ADMIN and PLATFORM_SUPPORT register reads, with test assertions. PR #11 exact-head ERP/WP CI passed (93 backend tests / 1,081 assertions; 27 Angular tests; artifact ID `15155567006`). No platform operator is seeded, no commercial plan is activated, and no dedicated platform Angular administration UI is complete. Remaining acceptance includes export/retention/legal hold/controlled recovery; two-person operator-grant lifecycle; module-level feature-gate enforcement; effective-dated roles tied to actual organization/site/department/warehouse/channel data; downstream target-record scope checks; MFA/OIDC/SSO and session recovery/rotation; complete invitation/operator UIs; and installer/recovery/rollback/deployment handover.

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
| Platform bootstrap / role source | `docs/security/platform-administration-bootstrap.md`; `.env.example`; deployment secret manager | `platform_operator_grants`; `CONTROL_DB_USERNAME` / `CONTROL_DB_PASSWORD` | No application direct table DML; `app_runtime` cannot execute identity-sensitive platform/scope functions. `app_control` receives only explicit function EXECUTE grants. Bootstrap provisions two active PLATFORM_ADMIN identities and different grantor/approver actors for each grant. |
| Platform schema and lifecycle functions | Migrations `000032`, `000033`, `000034`, `000037` | Platform grants, tenant entitlements, audit, tenant scope modes/grants; platform role/lifecycle, grant lifecycle and scope mutation functions | Fixed-search-path SECURITY DEFINER functions; `app_runtime` has no EXECUTE for identity-sensitive control APIs and no direct DML; `app_control` has only specified function EXECUTE grants and no direct table rights. Tenant entitlement read remains available to app_runtime through its tenant-scoped function/RLS. |
| Platform acceptance tests | `backend/tests/Feature/PlatformAdministrationTest.php`; `ScopedAccessMutationSecurityTest.php` | Candidate cases include platform/tenant separation, support read-only, lifecycle, operator-grant lifecycle, no app_runtime privileged EXECUTE, app_control function-only access, forged actor-GUC denial and malformed route IDs. | Earlier PR #9/#11 evidence passed on their exact SHAs. PR #13 candidate tests remain NOT RUN until exact-head CI executes. |


### Platform administration and entitlements (WP-03 candidate)

- `backend/app/Http/Controllers/Enterprise/PlatformAdministrationController.php`: global platform tenant list, lifecycle change, entitlement update.
- `backend/app/Http/Controllers/Enterprise/TenantEntitlementController.php`: read-only current tenant entitlement summary.
- `backend/app/Services/PlatformAdministrationService.php`: calls narrowly granted platform functions and maps database authorization/validation errors.
- `backend/database/migrations/2026_11_26_000032_add_platform_entitlements_and_tenant_lifecycle.php`: global operator grants, tenant entitlements, platform audit, default entitlement trigger, forced RLS, platform role functions and lifecycle state machine.
- `backend/tests/Feature/PlatformAdministrationTest.php`: platform-admin/support separation, entitlement read/write, tenant lifecycle and runtime privilege assertions.
- `docs/security/platform-administration-bootstrap.md`: controlled two-person operator-grant provisioning and lifecycle/entitlement security boundary.
- Route registration: `backend/routes/domain-api.php`; `/api/v1/platform/*` uses global identity context, while `/api/v1/tenant/entitlements` uses tenant context.
- Integrated status: platform lifecycle/entitlement API and migration 000032 are exact-head CI verified and merged in PR #9. The current continuation branch adds immutable auditing for authorized platform tenant-register reads; its exact-head CI is pending. Controlled platform operator appointment, full feature-gate enforcement, retention/legal hold/export/recovery and platform Angular UI remain open.


## WP-03 global platform operator and isolated control-role security candidate — 27 November 2026

**Active candidate branch:** `work/wp03-completion-2026-11-27`  
**Integration base:** `delivery/autonomous-wp-01-16` at `f948bb85de4194ae18dc8ee8c974c3ad0986623c`  
**Status:** source candidate. The head includes isolated `app_control`, migration 000037, CI role provisioning, refreshed platform-role session and adversarial tests; exact-head PostgreSQL, Laravel, Angular and combined WP CI remain to be executed.

### Backend locations

```text
backend/
├── app/
│   ├── Http/Controllers/Enterprise/
│   │   ├── PlatformAdministrationController.php       # global platform role context and existing tenant controls
│   │   └── PlatformOperatorGrantController.php        # list/request/approve/revoke operator grants
│   └── Services/
│       └── PlatformAdministrationService.php          # platform and isolated control-connection function calls/error mapping
├── database/migrations/
│   ├── 2026_11_27_000033_create_platform_operator_grant_lifecycle.php
│   ├── 2026_11_27_000034_secure_tenant_access_scope_mutations.php
│   └── 2026_11_27_000037_isolate_identity_control_functions.php
├── routes/
│   └── domain-api.php                                 # authenticated global /api/v1/platform/* routes
└── tests/Feature/
    └── PlatformAdministrationTest.php                 # role context, two-person grant flow, revocation, expiry audit
```

Migration 000033 adds active-role resolution, grant-register reads, reasoned request/approval/revocation and on-demand expiry/audit functions. Migration 000034 routes scope mutations through database functions; migration 000037 revokes `app_runtime` EXECUTE on identity-sensitive platform/scope functions and grants only isolated `app_control`. This login has no table DML, schema CREATE, superuser or RLS bypass. Laravel services set actor/tenant GUCs transaction-locally from authenticated request context over `pgsql_control`; `app_runtime` cannot forge a GUC and invoke the privileged functions. PostgreSQL validates platform role, tenant authority, target membership, independent approval, reason and effective period. Effective access ends at the validity date; persisted EXPIRED status and its audit event are swept on a later authorized admin operation. A scheduled expiry worker is not yet present.

### Angular locations

```text
frontend/src/app/
├── app.component.html                              # tenant workspace shell vs global platform navigation
├── app.routes.ts                                   # /platform/tenants and /platform/operators
├── core/auth/
│   ├── auth-session.service.ts                     # tenant memberships and global roles loaded independently
│   └── auth-session.service.spec.ts                # platform-only login and fail-closed tests
└── features/platform/
    ├── platform-tenants.service.ts                 # read-only tenant register API
    ├── platform-tenants.service.spec.ts
    ├── platform-tenants.component.ts
    ├── platform-tenants.component.html
    ├── platform-tenants.component.css
    ├── platform-operators.service.ts                # grant API client
    ├── platform-operators.service.spec.ts
    ├── platform-operators.component.ts
    ├── platform-operators.component.html
    └── platform-operators.component.css
```

PLATFORM_SUPPORT sees only the global tenant register. PLATFORM_ADMIN additionally sees operator grant management. Tenant roles do not imply global platform authority. Active platform roles refresh every 30 seconds in the authenticated session so remote grant approval, revocation or expiry updates visible capabilities; PostgreSQL checks every protected API request independently. When a platform user also has tenant memberships, the shell retains tenant workspace selection; with no tenant membership, the user sees global navigation only.

**Acceptance boundary:** new source paths and tests are candidate implementation evidence, not a PASS until the exact branch head completes both ERP CI and combined WP verification. Recent GitHub retries still show runner allocation with zero executed steps, so application tests remain NOT RUN. A scheduled expiry worker, full target-resource scope enforcement, MFA/OIDC/SSO and remaining WP-03 tenant-retention/recovery workflows remain open.
