# WP-16 — End-to-End Acceptance, Security, Reliability, Build, Deployment and Rollback

**Package:** WP-16  
**Status:** RELEASE DESIGN COMPLETED — executable application verification is PENDING until the scaffold, CI and runtime are delivered and run  
**Scope:** Approved platform, code quality, security and deployment standards; acceptance journeys from WP-01; all domain packages WP-03–WP-15.

## 1. Purpose and release principle

This package defines how the full ERP is verified, packaged, deployed and recovered. A release is not accepted because documentation exists or a frontend page loads. It must prove the complete critical business journeys, data integrity, tenant isolation, legal-rule safety, performance, backup/restore and operational readiness.

**Truthful release status:** at the start of this execution the repository contained documentation only. WP-00–WP-15 have produced the approved delivery baseline/specifications; this does not prove an application was implemented. WP-16 therefore establishes two distinct outcomes:
1. Release engineering and acceptance criteria are defined.
2. Actual application build, test, deployment and production release remain unverified until executable code and workflow evidence exist.

No production deployment or release claim can be made without a successful immutable artifact, authorized operator, environment evidence and operational sign-off.

## 2. Environments and promotion

| Environment | Purpose | Data/credentials | Promotion gate |
|---|---|---|---|
| Local developer | Isolated code/design/test | Synthetic data and local-only secrets | Same code commands as CI |
| CI ephemeral | Lint, unit, integration, security and build | Disposable database/Redis; test credentials | All required jobs pass |
| Integration | Test connected module behavior | Synthetic/anonymized data; sandbox connector | Domain owners verify source/consumer contracts |
| Staging/UAT | End-to-end business acceptance and deployment rehearsal | Representative synthetic or lawfully anonymized data; sandbox integrations | Business/technical/legal reviewer sign-off |
| Production | Live customer transactions | Customer-authorized secrets/data, monitored and backed up | Formal release/operator and customer authority |

The same immutable image digest is promoted between environments. Production is never built from a mutable branch or a different set of dependencies.

## 3. CI and supply-chain quality gates

Required pipeline stages:
1. Repository policy and package/workspace validation.
2. Formatting and lint (Angular/TypeScript, PHP, Python, SQL/YAML).
3. Type/static analysis and framework validation.
4. Unit tests for domain rules, calculation logic, state transitions and policies.
5. Integration tests against PostgreSQL/Redis and object storage adapters.
6. API/OpenAPI schema compatibility and contract tests.
7. Tenant isolation, authorization, RLS and IDOR security tests.
8. Domain-specific concurrency, idempotency, reversal and ledger reconciliation tests.
9. Browser/UI accessibility, keyboard navigation and critical workflow E2E tests.
10. Dependency/license/vulnerability, secret, SAST and container scans.
11. Docker image build, non-root/runtime checks and SBOM.
12. Database migration forward path and recovery rehearsal.
13. Artifact provenance/signature where supported and immutable digest.
14. Deployment smoke test, health/readiness, queue and scheduled-job checks.

A test suite may not be marked passed if skipped, quarantined without approval or run against a different commit than the artifact. Required checks fail closed. Each report records source SHA, tool/runtime version, command, result, timestamp and artifact reference.

### Technology version gate
Use the approved platform standard: Angular 22 / TypeScript 6.0.x / Node 26.13.1 LTS; PHP 8.5 / Laravel 13; PostgreSQL 18; Redis 8.2; Python 3.15; Docker Compose v2; REST/OpenAPI 3.1. Patch versions are pinned in lockfiles or image references and reviewed for current security support. Major-version changes require a baseline-approved decision; minor/patch refreshes are recorded and tested.

## 4. Test pyramid and minimum evidence

- Unit: domain value objects, policies, formula boundaries, state machines, validators, approval rules and calculation precision.
- Integration: transactional services, database constraints, RLS, queues, outbox, file adapter, external API adapters with sandbox/stubs.
- Contract: request/response schema, event version, compatibility and consumer behavior.
- System: complete modules, cross-domain handoff, reconciliation and role permissions.
- E2E: named critical user journeys from initiative to sale, buyer order to shipment, subcontract custody, factory hourly recovery, retail/POS, return/refund and payroll/statutory-review workflow.
- Non-functional: load, soak, concurrency, backup/restore, failover, security, accessibility and operational recovery.

Minimum global scenarios:
- Tenant A and B cannot infer/read/write/export/access assets/jobs/analytics for one another.
- Composite tenant/entity/site FKs reject cross-scope data linking.
- Missing tenant context is default-deny at RLS and application layers.
- Idempotent retry prevents duplicate order, receipt, stock movement, AP/AR, payment, payroll and journal effect.
- Concurrent allocation/stock issue/payment posting cannot overconsume or double settle.
- Outbox and consumer failures are recoverable/reconcilable without losing a committed business event.
- Posted finance, inventory and payroll facts are immutable and corrected by controlled reversal/adjustment.
- Stale or unapproved legal rules are blocked from statutory activation.
- User cannot bypass maker-checker, skill, quality, license or safety controls through API manipulation.
- Historical business facts reproduce their original rule/spec/version.
- Sensitive records/exports/AI prompts obey scope and purpose.

## 5. End-to-end business acceptance journeys

The E2E catalogue is defined in WP-01. Before release each is executed in a clean tenant with traceable evidence:
- E2E-001 own-brand initiative → market insight → collection/range → design/sample/fit → BOM/cost → source → manufacture/QC → DC/store → sale/return/feedback.
- E2E-002 buyer order → feasibility → product specification → materials → pre-production → manufacturing → final QC/packing → dispatch/invoice/collection.
- E2E-003 outsourced special process → approved supplier/contract → material custody → operation → return receipt/QC → accepted output/AP settlement.
- E2E-004 wholesale/reseller → account/price/credit → allocation/dispatch → AR/collection → rebate/return.
- E2E-005 own store and D2C → assortment/price → POS or digital order/payment → fulfilment/delivery → return/refund → stock/cash/finance reconciliation.
- E2E-006 shift/hourly recovery → SAM/route/capacity/skills → target/actual/WIP → Andon → corrective action/rebalanced line → quality-accepted output.
- E2E-007 worker/creator/reseller/vendor classification → approved legal rule or REVIEW_REQUIRED → time/earning/AP event → review → posting/remittance only where authorized.
- E2E-008 cross-tenant/entity/site isolation and audit attack scenarios.

Each execution captures test tenant setup, role, data seed version, scenario, source IDs, expected vs actual, logs/correlation IDs, screenshots or test report, defect and approver. Synthetic data only; no real worker/customer data required for baseline tests.

## 6. Security verification

Use the existing SaaS security standard and OWASP ASVS/Top 10 as references. Verify:
- Authentication, MFA/session rotation/revocation, recovery and privileged access.
- Tenant/entity/site/department/record-level authorization at every API, export, file, worker and background job.
- Forced RLS under non-owner application DB role with no BYPASSRLS.
- IDOR, injection, CSRF/CORS/CSP, SSRF, XSS, path traversal, unsafe file parsing and mass assignment.
- Webhook signature and replay protection, idempotency, rate/size limits and abuse monitoring.
- Secrets, key rotation, dependency/SBOM, container hardening and supply-chain checks.
- Audit integrity, time synchronization, log redaction and access to security evidence.
- Privacy purpose, retention, data subject handling, export/deletion, breach response and cross-border data flow.
- AI prompt/data access scope, prompt injection and model output as untrusted.
- Backup encryption, access, restore, retention and deletion.

High-risk findings block release until fixed or have explicit accountable risk acceptance, expiry and compensating controls. Tenant isolation, secret exposure, financial double posting and legal/safety bypass are release-blocking with no routine waiver.

## 7. Performance, capacity and reliability

Before production, define and measure workload profile:
- Tenant count, active users, concurrent sessions and peak requests.
- Orders/lines, SKU/style variants, inventory movements and scan volume.
- Shop-floor events/hour, production lines and WIP size.
- Finance journal/AP/AR, payroll headcount and period-close batch.
- Document/asset volume and import/export sizes.
- Analytics refresh, queue and connector throughput.
- Geographic latency and expected offline constraints.

Set per-service p95/p99 latency, throughput, availability, error budget, queue lag, DB connection/lock, batch duration and recovery SLOs with product owners. Baseline numbers must come from measured load tests; no unmeasured SLA is advertised.

Perform load, soak, burst, concurrency and recovery tests. Tune SQL indexes with real plans, connection pools, queues, caching and partitioning only when measured. Redis loss must not lose transactional data. Queue backlog and external connector outage must be observable and replayable.

## 8. Backup, restore and disaster recovery

- Define RPO/RTO by customer tier and service criticality.
- PostgreSQL backup/PITR is encrypted, access-limited and isolated from primary account/region where available.
- Object storage is versioned/replicated as policy requires; database asset metadata and object checksum reconciliation is tested.
- Redis is ephemeral; rebuildable queue/cache semantics and retry handling are documented. Do not rely on Redis persistence as transaction truth.
- Backup includes configuration/schema, required encryption key recovery material and object references, not exposed application secrets.
- Test point-in-time restore and cross-environment recovery in isolated network; never restore over live production during ordinary rehearsal.
- Record restore duration, data loss window, database consistency, attachment integrity, outbox backlog recovery and application health.
- Retention follows privacy/legal hold; backup deletion and key lifecycle are controlled.

## 9. Database migrations and change safety

- Migrations are versioned, reviewed, repeatable in disposable environments and forward-compatible with rolling deployment.
- Prefer expand → deploy compatible code → backfill → verify → contract.
- Large backfills are resumable, rate-limited and observable.
- No direct production SQL as a manual deployment procedure.
- Destructive migration is prohibited without backup, tested recovery, explicit human approval and impact analysis.
- Rollback is not assumed safe after data transformation; use forward repair or compensating migration.
- Migration job runs once under a narrowly privileged deployment role and records source/artifact/schema version.
- Seeders in production are idempotent reference-data operations only; no demo customers/employees/orders are introduced.

## 10. Observability and incident response

Telemetry:
- Structured logs with correlation/trace, tenant-safe dimensions, severity and redaction.
- Metrics for request/queue/database/worker/integration/financial reconciliation and tenant health.
- Distributed traces across API, outbox, jobs and external adapters.
- Alerts have severity, owner, runbook, escalation and acknowledgement.
- Security, payroll, inventory and finance audits are separately searchable with restricted access.
- Incident process includes detection, triage, containment, rollback/recovery, customer communication, evidence preservation and post-incident corrective actions.

## 11. Release and rollback procedure

1. Freeze candidate commit and verify all checks for exact source SHA.
2. Review open defects, legal holds, security findings, data migration, SLO and dependency status.
3. Back up production and validate restore readiness.
4. Build immutable images/SBOM/signature and publish with source SHA.
5. Deploy to staging; migrate and run smoke, E2E, queue, scheduled task and integration tests.
6. Obtain business/customer and qualified statutory approvals for tenant activation.
7. Accountable operator authorizes production release, monitoring window and communication.
8. Deploy compatible app, run migration job once and verify health/metrics.
9. If thresholds breached, pause rollout, rollback app if schema-compatible or run approved forward recovery; preserve audit and customer transaction consistency.
10. Verify queues/reconciliation, report status, and close only after operator and business acceptance.

A GitHub merge, tag or successful CI job is not itself permission for live production deployment.

## 12. Data model for release engineering

| Entity/table | Purpose |
|---|---|
| build_runs | source SHA, branch, toolchain, workflow, status, artifact refs and time |
| test_runs / test_results | suite/scenario, commit, environment, result, skipped reason, evidence |
| security_findings | source, severity, affected component, owner, remediation/expiry |
| sbom_manifests | artifact digest, package/licence/vulnerability metadata |
| release_candidates | source SHA, image digest, migration, approval and readiness |
| deployment_records | environment, artifact, operator, approval, status and health |
| migration_runs | migration version, schema before/after, runner, result and recovery |
| backup_manifests | backup type, encrypted location ref, checksum, retention and source DB |
| restore_rehearsals | backup ref, target environment, duration, recovered point and integrity result |
| slo_definitions / slo_observations | service, metric, window, target, measured result and breach |
| incidents / postmortems | impact, detection, timeline, mitigation, RCA and actions |
| release_approvals | business, legal, security, data and operator decision evidence |

## 13. Acceptance tests and go/no-go

| Test | Required evidence | Go/no-go |
|---|---|---|
| AT-REL-001 | All required CI checks pass on exact candidate SHA | Go only if all required checks are green |
| AT-REL-002 | E2E-001 to E2E-008 pass with traceable seeded data | Any critical business-path failure is no-go |
| AT-REL-003 | Adversarial tenant isolation/RLS tests pass | Any cross-tenant exposure is no-go |
| AT-REL-004 | Finance/stock/payroll concurrency and idempotency tests pass | Double posting/consumption is no-go |
| AT-REL-005 | Legal/statutory holds are visible and no unapproved rule active | Unsafe activation is no-go |
| AT-REL-006 | Security scan has no unresolved critical/high release blocker | Critical/high findings require remediation/risk gate |
| AT-REL-007 | Load and soak profile meets approved SLO | Failure is no-go or capacity-limited rollout only by approval |
| AT-REL-008 | Backup restore and asset-link integrity test pass | No verified restore is no-go |
| AT-REL-009 | Migration rehearsal and recovery/forward-fix plan pass | Unsafe migration is no-go |
| AT-REL-010 | Staging deployment, smoke tests and monitoring runbook pass | No production release without evidence |
| AT-REL-011 | Customer/legal/privacy configuration and operator release evidence exists | No live activation without accountable sign-off |
| AT-REL-012 | Rollback/forward recovery and incident procedures are rehearsed | No production release without recovery plan |

## 14. WP-16 handover

**Completed:** environment separation, CI gates, test pyramid, E2E acceptance, security verification, performance/SLO method, backup/restore, migration safety, observability/incident process, immutable release and rollback procedures, release evidence model and 12 go/no-go criteria.

**Current actual status:** The package is a release engineering standard. No application source scaffold, executable migrations, test suite, CI pipeline, image artifact or deployment has yet been proven on this branch at the point this specification is authored. Therefore the product is NOT RELEASED and NOT RUNNABLE based solely on documentation. The implementation programme must now create the application and obtain actual build/test evidence.

**Human-only gates:** production deployment, real customer data load, statutory rule activation, real payroll/banking/payment/customs execution, destructive production migrations and acceptance of legal interpretation require accountable customer/operator/professional actions. Continue all safe source, code, CI and staging work without those live actions.

**Decision:** WP-16 release design internally accepted. Specification work packages WP-00–WP-16 are complete as design artifacts. Runtime implementation and verification are the next engineering execution layer and must never be represented as complete until evidenced.

## 15. Miret integrated acceptance (additional candidate requirement)

The Miret contract in docs/product/miret-system-operating-model.md adds five exact-head acceptance journeys to the existing E2E suite: (1) initiative-to-product passport-to-sale/return identity and genealogy; (2) incoming and inline quality holds, rework and independent reinspection; (3) monthly capacity/MPS-to-daily-line-to-hourly-output reconciliation against effective SAM; (4) role/task-specific agent capability with permission, idempotency and human-approval tests; and (5) customer/branch/store feedback linked to product/CAPA/next initiative.

These are required acceptance coverage, not a PASS assertion. WP-16 must link each journey to an automated test, owner/UAT evidence, source commit and exact workflow result. Factory calibration, customer-specific standards, local professional sign-off and production deployment remain separate gates.
