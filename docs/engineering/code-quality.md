# Engineering, Syntax and Code Quality Standard

## 1. General rules

- Prefer readable, explicit, typed and testable code over cleverness.
- Use domain language consistently across UI, API, database, events and reports.
- Keep functions and classes focused; separate orchestration from business rules.
- Validate untrusted input at every boundary. Encode output appropriately.
- Use UTC for persisted timestamps; render dates in the tenant's configured timezone and calendar.
- Use decimal/numeric types for money and quantities requiring exact precision.
- Make error handling explicit. Never swallow exceptions or return false success.
- Do not commit generated build output, local databases, credentials, private files or customer data.
- No TODO may silently represent a security, financial, payroll or data-integrity requirement.

## 2. Angular and TypeScript

- Angular 22 with Angular CLI 22 and TypeScript 6.0.x is the frontend baseline; Node.js 26.13.1 LTS is the build/tooling runtime.
- Use standalone components (standalone is the default); do not add redundant standalone: true.
- Use strict TypeScript and strict template checking. Avoid any; use unknown at untrusted boundaries and narrow safely.
- Organize by business feature/domain. Shared UI must not become a dumping ground for business logic.
- Angular v22 uses OnPush-style change detection by default. Do not repeat an explicit OnPush setting unless a compatibility reason is documented.
- Use signals for local reactive state, computed() for derived state and linkedSignal() where a selection must remain valid as upstream options change. Do not use effect() as a substitute for derived state or as a hidden transaction runner.
- Prefer stable Signal Forms (@angular/forms/signals) for new signal-native forms where schema validation and typed field access are useful. Existing Reactive Forms remain valid; do not mass-migrate working screens without a bounded benefit and tests.
- Use Resource/httpResource for reactive asynchronous reads. All business mutations (receive, issue, release, approve, reserve, dispatch, post, pay) go through typed API services and Laravel commands with server authorization and idempotency.
- Use lazy feature routes and @defer for non-critical heavy views, with accessible placeholder/loading/error states. Use SSR/hydration only when justified by a measured requirement; protect personalized tenant, payroll, customer and finance data from shared caching.
- For zoneless-compatible code, use signals and supported notification mechanisms. Test Reactive Forms and external observable bridges before removing ZoneJS from an existing feature.
- Use the Angular CLI builders and the project's locked Vitest toolchain; do not use an unreleased Angular main/canary package or invent a custom builder without evidence.
- Use Angular Aria, CDK or Material patterns appropriately. Meet WCAG 2.2 AA, visible focus, keyboard access, screen-reader labels/announcements and automated axe checks.
- Do not trust frontend route guards as authorization; enforce every permission in the API.
- The maturity/source links and feature-by-feature ERP application rules are in [Angular Current Capabilities and Adoption Standard](../architecture/angular-current-capabilities-and-adoption-standard.md). The official repository main branch is a development feed, not a production dependency.

## 3. PHP and Laravel

- PHP 8.5 and Laravel 13 are the transactional backend baseline.
- Use strict types, PSR-12 formatting and Composer lockfile.
- Organize code by domain module with explicit application, domain, infrastructure and interface boundaries.
- Controllers validate/dispatch and shape responses; they do not contain complex business rules.
- Use Form Requests or equivalent boundary validators, Policies/Gates for authorization, Resources for response contracts and queued jobs for asynchronous work.
- Use database transactions for multi-record business operations. Lock critical stock, payment and payroll records where concurrency requires it.
- Use migrations for every schema change. Never edit a production database manually as a deployment step.
- Use Eloquent carefully; prevent N+1 queries, unbounded reads and implicit cross-tenant relations.
- Use events/outbox for integration; do not dispatch an irreversible external side effect before the transaction commits.
- Money is stored as integer minor units or explicit DECIMAL with documented precision; never PHP float.
- Keep secrets in environment/secret managers, never in committed .env files.

## 4. Python

- Python 3.15 is reserved for separately deployable specialist compute services.
- Use type hints, pyproject.toml, locked dependencies, Ruff lint/format, pytest and static type checking.
- Expose versioned, authenticated service contracts. Validate input and output schemas.
- Do not connect Python workers directly to business tables to bypass Laravel domain rules.
- Python may propose schedules, forecasts or optimization results; consequential changes are validated and approved through the ERP API.
- Pin dependencies and scan for known vulnerabilities.

## 5. Database and data access

- PostgreSQL is the transactional source of truth.
- Use migrations, foreign keys, unique constraints, check constraints and appropriate indexes.
- Every tenant-owned table has an explicit tenant boundary and tests.
- Prefer normalized transactional models; add reporting projections/materialized views only with measured need.
- Avoid hard deletes for posted finance, payroll, stock and compliance records; use controlled void/reversal/retention workflows.
- Use optimistic concurrency/version columns where users may edit the same record.
- Define retention, archival, export and deletion policies per data category.

## 6. API contract rules

- REST endpoints are versioned and documented in OpenAPI 3.1.
- Use consistent pagination, filtering, sorting, error envelopes and correlation IDs.
- Validate types, ranges, enums, tenant scope and business invariants on the server.
- Use idempotency for commands that may be retried.
- Do not expose internal table names, stack traces, secrets or private employee information.
- Contract changes require tests and documented compatibility.

## 7. Naming and formatting

- PHP: PSR-12, PascalCase classes, camelCase methods/properties.
- TypeScript: Angular style guide, PascalCase types/classes, camelCase members, kebab-case feature files.
- Python: PEP 8, snake_case functions/modules, PascalCase classes.
- SQL: snake_case identifiers, explicit constraints and meaningful index names.
- JSON/YAML: two-space indentation, stable key naming and schema validation.
- Commit messages: Conventional Commits (feat, fix, docs, test, refactor, build, ci).

## 8. Minimum quality gates

A change cannot be promoted if any required gate fails:

1. Formatting and lint checks.
2. Static analysis/type checks.
3. Unit tests for business rules.
4. Integration tests for database, queue and API boundaries.
5. Tenant-isolation and authorization tests.
6. End-to-end tests for critical user journeys.
7. Database migration forward and rollback/recovery review.
8. Dependency, secret, container and static security scans.
9. Production build and container build.
10. Deployment smoke tests and health checks.

Financial posting, inventory valuation, payroll calculation, tax and tenant isolation require independent test cases and reviewer attention. A green frontend build alone is not an ERP acceptance result.
