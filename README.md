# Modern Garment End-to-End Addis ERP

A greenfield, customer-neutral, multi-company SaaS ERP for garment manufacturing, apparel sourcing, wholesale, retail and distribution. Ethiopia-first localization is a first-class capability; it is not the product identity.

This repository is the canonical product source. ERP 16.2.18 and the five approved GitHub projects are reference inputs only. They do not constrain the product's scope, architecture or quality bar.

## Product promise

One connected apparel business operating system beginning with business initiatives, market insight, brand strategy, product ideas, in-house design, collection/range planning, 2D/3D product development, patterns and samples; continuing through specialized, configurable manufacturing departments and their own SOPs, industrial engineering, skill-based planning, process quality and traceability; then through central/regional DCs, own branches and retail stores, wholesale, retailer/reseller, direct and digital channels, POS, delivery and reverse logistics—with finance, HR, learning, improvement, compliance and analytics integrated throughout.

The platform must support multiple tenants, companies, legal entities, factories, branches, warehouses, brands, currencies, languages and operating calendars without hard-coded Stockland identity or sample customer data.

## Architecture decision

- Web: Angular 22 + Angular CLI, TypeScript strict mode.
- API: PHP 8.5 + Laravel 13, organized as a modular monolith with explicit business-domain boundaries.
- Node.js: Node 26.13.1 LTS for Angular CLI, frontend tooling and build pipelines; not a second competing business API.
- Data: PostgreSQL 18.x as the transactional source of truth; Redis 8.2 extended-support line for cache, queues and short-lived coordination.
- Specialist compute: Python 3.15 for isolated optimization, forecasting, document intelligence and AI workloads; it does not own ERP transactions.
- Containers: Docker multi-stage builds and Docker Compose v2 for reproducible development; production deployment is an immutable-image pipeline.
- Contracts: versioned REST APIs with OpenAPI 3.1, schema validation, idempotency for retryable commands and transactional outbox events.

Exact supported patch versions are recorded in the platform standard and must be refreshed during dependency updates. Prefer supported LTS/bug-fix releases over release candidates or unmaintained versions.

## Project governance

- Strict project scope, express change approval, numbered work packages and handover rules: [Project Governance Agreement](docs/agent/project-governance-agreement.md).
- Agent instructions: [AGENTS.md](AGENTS.md). All contributors and coding agents must read these before work.

## Start here

1. Project governance: docs/agent/project-governance-agreement.md
2. Platform architecture and technology standard: docs/architecture/platform-standard.md
3. Exact application folders and file responsibilities: docs/architecture/application-folder-code-map.md
4. Miret four-pillar operating model: docs/product/miret-system-operating-model.md
5. Angular current capabilities and adoption standard: docs/architecture/angular-current-capabilities-and-adoption-standard.md
6. End-to-end business domain map: docs/product/domain-map.md
7. Apparel business operating system charter and manufacturing excellence: docs/product/apparel-business-operating-system-charter.md
8. Distribution network, order management and channel commerce: docs/product/distribution-and-channel-commerce.md
9. Engineering, syntax and code quality rules: docs/engineering/code-quality.md
10. Development, container and deployment standard: docs/engineering/development-deployment.md
11. Security and SaaS tenancy standard: docs/security/saas-security.md
12. Ethiopia localization and statutory controls: docs/localization/ethiopia.md
13. Ethiopia legal and regulatory source register: docs/localization/ethiopia-legal-regulatory-register.md
14. Payroll and differentiated variable-compensation schemes: docs/hr/payroll-and-variable-compensation.md
15. Approved reference repository review: docs/references/reference-repositories.md

## Application foundation and verified integration delivery

**Canonical integration branch:** `delivery/autonomous-wp-01-16`  
**Latest integrated commit:** `bd41c83e141835db3c0f03ac8d711096728a07b4`  
**Latest WP-03 integration:** [PR #9 — platform lifecycle and entitlement foundation](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/9), merged into delivery at `bd41c83e141835db3c0f03ac8d711096728a07b4`.  
**Default branch:** `main` remains unchanged.

Exact-head verification for the latest integrated WP-03 platform lifecycle/entitlement foundation passed before merge on source SHA `f0688d04c9d9f28722a783086c7efd15500798a2`:
- [ERP Continuous Integration #45899259026](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259026): PostgreSQL 18 migration/RLS, Composer audit, PHP Pint/syntax and **93 backend tests / 1,079 assertions** passed; Angular **27 tests across 7 files**, dependency audit and production build passed.
- [Combined WP-01–WP-16 verification #45899259071](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259071): backend, frontend, WP-01 R3 integrity/strict audit and final artifact assembly all passed.
- Delivery post-merge CI on exact merge SHA `bd41c83e141835db3c0f03ac8d711096728a07b4`: [push run #45899593787](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899593787) and [PR run #45899601895](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899601895) both passed.

Combined candidate artifact: `modern-garment-addis-erp-wp01-wp16-candidate`, ID `15154948146`, 739,862 bytes, expiry 24 February 2027. It is a verified source/review artifact, not a signed production installer or deployment image.

The repository now contains Laravel backend foundations and candidate services/migrations for enterprise identity, platform tenant lifecycle/entitlements, merchandising, PLM, IE, sourcing/material genealogy, route/MES execution, quality/CAPA, distribution, finance/payroll review, documents/integrations and governed agents. Angular executable user journeys are currently concentrated in authentication, enterprise setup, merchandising and initial product development. See the [application folder/code map](docs/architecture/application-folder-code-map.md) for exact file paths.

**Current execution checkpoint:** WP-03 remains the active package. The tenant lifecycle/platform entitlement foundation is integrated on delivery at `bd41c83e141835db3c0f03ac8d711096728a07b4` after exact-head verification (93 backend tests / 1,079 assertions, 27 Angular tests, PostgreSQL 18 migrations/forced RLS and combined candidate artifact). The separate platform tenant-register read audit is the next WP-03 increment on `agent/chatgpt-wp03-continuation-2026-11-27` and still requires exact-head CI. Remaining work includes tenant export/retention/legal hold/recovery, platform grant expiry/revocation, feature-gate enforcement, scoped roles/target checks, MFA/OIDC/SSO, invitation/domain-role/ownership UI, operator bootstrap/recovery and full acceptance evidence. Owner succession, one-active-owner protection and safe voluntary leave are merged and CI-verified.

**Release wording:** successful CI is technical evidence for the exact checks and SHA only. It does not mean WP-01–WP-16 are all complete. Full factory/store/customer E2E, independent security review, production concurrency/load/soak, backup/restore/DR/rollback, external integration UAT, professional/statutory sign-offs, staging/UAT and accountable production deployment remain WP-16 gates. No production release is claimed.

- [Local development guide](docs/development/local-development.md)
- [Laravel backend](backend/)
- [Angular frontend](frontend/)
- [Application file/code map](docs/architecture/application-folder-code-map.md)
- [WP-03 requirement-level acceptance matrix](docs/verification/WP03-implementation-acceptance-matrix.md)
- [Platform administration and tenant entitlement bootstrap](docs/security/platform-administration-bootstrap.md)
- [Local infrastructure](docker-compose.yml)
- [CI workflow](.github/workflows/ci.yml)
- [WP-00 to WP-16 handover register](docs/work-packages/handover-register.md)

## Work-package delivery status

The numbered design packages WP-00 through WP-16 have been prepared and internally reviewed under the Product Owner's autonomous delegation. See the [handover register](docs/work-packages/handover-register.md) for exact commits, limitations and implementation status.

- [WP-01 Requirements traceability](docs/requirements/requirements-traceability.md)
- [WP-02 Ethiopia legal-source register and holds](docs/localization/ethiopia-legal-regulatory-register.md)
- [WP-03 Tenant, organisation and identity architecture](docs/architecture/tenant-organization-identity.md)
- [WP-03 Enterprise Foundation Engineering LLD](docs/architecture/wp03-enterprise-foundation-engineering-lld.md)
- [WP-03 / WP-14 Ethiopia organogram and payroll localization notes](docs/localization/wp03-ethiopia-organogram-payroll-localization-notes.md)
- [WP-04 Initiative, market insight and merchandising](docs/product/initiative-merchandising-range-planning.md)
- [WP-05 PLM, design studio and product development](docs/product/plm-design-studio-product-development.md)
- [WP-06 Industrial engineering and capacity](docs/engineering/industrial-engineering-capacity-standard.md)
- [WP-07 Supplier sourcing and material traceability](docs/procurement/supplier-sourcing-material-traceability.md)
- [WP-08 Route, SOP and work-instruction framework](docs/manufacturing/route-sop-work-instruction-framework.md)
- [WP-09 Departmental manufacturing execution](docs/manufacturing/departmental-manufacturing-execution.md)
- [WP-10 Production planning, skills and improvement](docs/manufacturing/production-planning-skills-incentives-improvement.md)
- [WP-11 Quality, compliance, CAPA and HSE](docs/quality/quality-compliance-capa-hse.md)
- [WP-12 WMS, distribution and channel commerce](docs/distribution/wms-order-management-channel-commerce.md)
- [WP-13 Finance, accounting, treasury and tax](docs/finance/finance-accounting-treasury-tax-costing.md)
- [WP-14 Workforce, payroll and compensation](docs/hr/workforce-payroll-pension-benefits-compensation.md)
- [WP-15 Analytics, integration and AI control tower](docs/analytics/integration-ai-control-tower.md)
- [WP-16 E2E acceptance, security and release controls](docs/release/end-to-end-acceptance-security-deployment.md)

**Important:** these are accepted design/requirements deliverables, not proof that the ERP application exists or has passed a build/test/release. Runtime implementation and executable verification are tracked separately.

## Delivery rule

A feature is not complete because a screen exists. It must include its business workflow, permissions, validations, audit trail, API contract, persistence, tests, reporting impact, localization behavior where applicable, and operational documentation. Only a version that passes the repository's quality gates may be promoted to a release.

## Current status

The current source is integrated on `delivery/autonomous-wp-01-16` at `bd41c83e141835db3c0f03ac8d711096728a07b4`. The active WP-03 continuation branch is `agent/chatgpt-wp03-continuation-2026-11-27`, maintained independently from the parallel WP-03 branch. Its privileged tenant-register read-audit increment passed exact-head ERP CI #45900565705 (**93 backend tests / 1,081 assertions**) and combined WP verification #45900565706 (all four jobs passed; artifact ID `15155567006`). The verified increment is ready for delivery integration. Continue the fixed WP-00–WP-16 sequence without routine owner-acceptance pauses, while retaining exact-SHA quality gates and the human/professional boundaries in the governance agreement. WP-03 is not yet complete; all domain candidate code and all remaining production/release gates must be reported accurately. This repository is **not** a finished end-to-end ERP, signed production installer, UAT-approved system or production deployment.
