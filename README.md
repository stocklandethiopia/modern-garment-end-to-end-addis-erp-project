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

1. Platform architecture and technology standard: docs/architecture/platform-standard.md
2. End-to-end business domain map: docs/product/domain-map.md
3. Apparel business operating system charter and manufacturing excellence: docs/product/apparel-business-operating-system-charter.md
4. Distribution network, order management and channel commerce: docs/product/distribution-and-channel-commerce.md
5. Engineering, syntax and code quality rules: docs/engineering/code-quality.md
6. Development, container and deployment standard: docs/engineering/development-deployment.md
7. Security and SaaS tenancy standard: docs/security/saas-security.md
8. Ethiopia localization and statutory controls: docs/localization/ethiopia.md
9. Ethiopia legal and regulatory source register: docs/localization/ethiopia-legal-regulatory-register.md
10. Payroll and differentiated variable-compensation schemes: docs/hr/payroll-and-variable-compensation.md
11. Approved reference repository review: docs/references/reference-repositories.md

## Application foundation — CI verified

The executable **WP-03 enterprise/platform foundation** and the current **WP-04 implemented backend slice** are verified by two successful GitHub Actions runs: [#288](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45025072785) and [#289](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45025076840), both on commit `6ea74813d79ebdf0a4cfd2e5912373c3daad395c`.

- Laravel 13 API: health, Sanctum token issue/revoke, authenticated tenant selection, tenant context, legal-entity/site/node/licence/branch/inventory-location onboarding, initiatives, insight sources and market insights, brand strategies, seasons, collections and version approvals, range plans/lines/freeze, assortment plans/lines.
- PostgreSQL 18: tenant-composite foreign keys, forced RLS, restricted runtime grants, append-only audit and evidence, immutable strategy/collection version histories, approved-decision records, 24 named PostgreSQL CHECK constraints and merchandising schema.
- Angular 22: login, tenant selection, overview/API health, enterprise setup, initiative pipeline/detail and market-insight source/evidence screens.
- Local PostgreSQL/Redis Compose, interactive first-owner provisioning, PHP/Node lockfiles and setup guide.

**Latest CI evidence:** Composer validation and audit, Pint, PHP syntax, PostgreSQL migrations executed as the restricted `erp_migrator`, **35 PHPUnit tests / 213 assertions — passed**, npm dependency audit, Angular unit tests and production build all passed on PHP 8.5.13 / PostgreSQL 18.7 / Node 26.13.1.

**Scope still pending:** brand/season/collection/range/assortment Angular screens; WP-04 forecast/OTB/pricing/promotion services and complete commercial acceptance; WP-05–WP-15 runtime domain modules; full E2E-001–E2E-008, security/performance hardening, backup/restore rehearsal, staging/UAT and production release. Pull request [#1](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/1) remains draft and unmerged; `main` is unchanged.

- [Local development guide](docs/development/local-development.md)
- [Laravel backend](backend/)
- [Angular frontend](frontend/)
- [Local infrastructure](docker-compose.yml)
- [CI workflow](.github/workflows/ci.yml)
- [WP-00 to WP-16 handover register](docs/work-packages/handover-register.md)

## Work-package delivery status

The numbered design packages WP-00 through WP-16 have been prepared and internally reviewed under the Product Owner's autonomous delegation. See the [handover register](docs/work-packages/handover-register.md) for exact commits, limitations and implementation status.

- [WP-01 Requirements traceability](docs/requirements/requirements-traceability.md)
- [WP-02 Ethiopia legal-source register and holds](docs/localization/ethiopia-legal-regulatory-register.md)
- [WP-03 Tenant, organisation and identity architecture](docs/architecture/tenant-organization-identity.md)
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

The repository now contains a runnable **development foundation** and CI-verified executable slices for enterprise onboarding and early merchandising workflows. It is **not** a finished end-to-end ERP, production installer, UAT-approved system or deployable SaaS release. Continue delivering the approved WP-04 and WP-05–WP-16 workflows as tested vertical slices; never describe a design document or a passing foundation build as proof of full business-process completion.
