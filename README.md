# Modern Garment End-to-End Addis ERP

A greenfield, customer-neutral, multi-company SaaS ERP for garment manufacturing, apparel sourcing, wholesale, retail and distribution. Ethiopia-first localization is a first-class capability; it is not the product identity.

This repository is the canonical product source. ERP 16.2.18 and the five approved GitHub projects are reference inputs only. They do not constrain the product's scope, architecture or quality bar.

## Product promise

One connected operating system from customer enquiry and merchandising through product engineering, costing, procurement, material planning, cutting, sewing, finishing, quality, packing, warehouse, dispatch, sales, finance, HR, learning and workforce development, compliance, analytics and administration.

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

## Start here

1. Platform architecture and technology standard: docs/architecture/platform-standard.md
2. End-to-end business domain map: docs/product/domain-map.md
3. Engineering, syntax and code quality rules: docs/engineering/code-quality.md
4. Development, container and deployment standard: docs/engineering/development-deployment.md
5. Security and SaaS tenancy standard: docs/security/saas-security.md
6. Ethiopia localization and statutory controls: docs/localization/ethiopia.md
7. Approved reference repository review: docs/references/reference-repositories.md

## Delivery rule

A feature is not complete because a screen exists. It must include its business workflow, permissions, validations, audit trail, API contract, persistence, tests, reporting impact, localization behavior where applicable, and operational documentation. Only a version that passes the repository's quality gates may be promoted to a release.

## Current status

This initial repository foundation records the architecture and quality standard. It is not yet a runnable ERP application, production installer or deployable SaaS release. Application modules will be delivered incrementally as tested vertical slices.
