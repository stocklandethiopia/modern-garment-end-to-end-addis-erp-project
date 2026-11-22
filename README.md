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

## Application foundation

The first executable foundation is now present on `delivery/autonomous-wp-01-16`:
- Laravel API scaffold with token authentication, health endpoint, tenant context and legal-entity onboarding.
- PostgreSQL 18 foundation migrations for tenants, memberships, legal entities, sites, operational nodes, branch-registration evidence, inventory locations and append-only audit events.
- Forced row-level security (RLS) policies on tenant-owned foundation tables.
- Local PostgreSQL/Redis Compose infrastructure and a one-time interactive first-owner provisioning command.
- Angular 22 workspace with a live API-health connection check and implementation-status overview.
- CI workflow for PHP 8.5/PostgreSQL 18 and Node 26.13.1.

**This is not the full ERP yet.** The first scaffold implements only platform foundation and a small API surface; operational modules WP-04–WP-15 remain to be implemented as executable domain slices. Build/test/deploy status must be read from GitHub Actions and the handover register, never inferred from file presence.

- [Local development guide](docs/development/local-development.md)
- [Laravel backend](backend/)
- [Angular frontend](frontend/)
- [Local infrastructure](docker-compose.yml)
- [CI workflow](.github/workflows/ci.yml)

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

This initial repository foundation records the architecture and quality standard. It is not yet a runnable ERP application, production installer or deployable SaaS release. Application modules will be delivered incrementally as tested vertical slices.
