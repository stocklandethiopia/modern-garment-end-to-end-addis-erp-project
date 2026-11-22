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

## Delivery rule

A feature is not complete because a screen exists. It must include its business workflow, permissions, validations, audit trail, API contract, persistence, tests, reporting impact, localization behavior where applicable, and operational documentation. Only a version that passes the repository's quality gates may be promoted to a release.

## Current status

This initial repository foundation records the architecture and quality standard. It is not yet a runnable ERP application, production installer or deployable SaaS release. Application modules will be delivered incrementally as tested vertical slices.
