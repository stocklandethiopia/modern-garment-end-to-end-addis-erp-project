# Approved Reference Repository Review

The repositories below are learning references. They are not upstream dependencies and must not be copied wholesale. Their business workflows are evaluated independently from their framework choices, code quality, security posture, test coverage and license.

## Approved garment ERP references

| Repository | Primary learning value | Reuse boundary |
|---|---|---|
| BengalTex ERP | Broad ERP coverage: accounting, banking, HR, payroll, procurement, BOM, MRP, production, QC, stock, approvals and reporting. Angular frontend and .NET API are useful functional references. | Do not copy its .NET architecture into the selected Laravel backend. Inspect each workflow and its tests before adopting behavior. |
| Euro Suits ERP | Garment-specific enquiry, sample, material, costing, capacity, order, production and export workflows; Go backend with SQL migrations and tests. | Learn domain rules and workflow transitions; do not combine its SvelteKit/Go stack with the product stack. |
| Production Quality ERP | Hourly production, SMV, efficiency, capacity, WIP and defect monitoring. | Test coverage and secrets/configuration need review. Do not treat a package script or README claim as proof of test readiness. |
| Tverse | Warehouse, inventory, listing, picking, packing and fulfilment concepts. | Learn the operational flow; do not import its React/Spring stack into the core application. |
| Garment ERP | Sales order, stock reservation, picker assignment, shortage and verification concepts. | Use as a focused workflow reference, not as a full enterprise architecture. |

## Engineering references

- Angular CLI Builder documentation: https://angular.dev/tools/cli/cli-builder
- Angular deployment documentation: https://angular.dev/tools/cli/deployment
- Laravel 13 release and support policy: https://laravel.com/framework/docs/13.x/releases
- Laravel deployment documentation: https://laravel.com/framework/docs/deployment
- Docker build best practices: https://docs.docker.com/build/building/best-practices/
- PostgreSQL versioning policy: https://www.postgresql.org/support/versioning/
- Node.js release schedule: https://github.com/nodejs/release
- Python version status: https://devguide.python.org/versions/
- Redis version management: https://redis.io/docs/latest/operate/oss_and_stack/install/version-mgmt/

## Review method

For every candidate workflow, record:

1. Business purpose, actors, preconditions and outputs.
2. State machine, transition permissions and exception paths.
3. Data model, constraints, history and audit events.
4. API and integration contracts.
5. Accounting, inventory, HR or compliance impact.
6. Tests that demonstrate the behavior.
7. Security, tenancy, privacy and operational risks.
8. License and attribution requirements before any direct code reuse.

A feature is adopted only after it passes this review and is implemented in the product's selected architecture. Public GitHub visibility is not a license to copy code.


## AI-assisted source and code reuse

AI research/coding agents may inspect reference repositories and summarize patterns, licence and security evidence. They do not change the reference-only status.

Before reuse, inspect exact upstream files, current licence, attribution/notice requirements, dependency licences, security history and compatibility. Never copy secrets, customer/personal data or unverified source. Record provenance and adaptations. A model-generated explanation is not proof that source is safe, licensed or production-ready.
