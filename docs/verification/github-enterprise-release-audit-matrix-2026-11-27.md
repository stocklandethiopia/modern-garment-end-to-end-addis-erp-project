# GitHub enterprise release audit matrix — 27 November 2026

**Repository:** stocklandethiopia/modern-garment-end-to-end-addis-erp-project  
**Canonical integration branch:** delivery/autonomous-wp-01-16  
**Current implementation branch:** work/wp03-completion-2026-11-27  
**Release status:** NOT READY — WP-03 remains open and WP-04–WP-16 acceptance is incomplete  
**Purpose:** map the eight practical release-audit domains to objective evidence, GitHub controls and remaining gates. This document is an internal readiness matrix, not a GitHub-issued certificate or independent assessor's attestation.

## How to interpret the eight audit domains

GitHub does not issue one universal set of eight certificates for an ERP repository. The following are eight practical audit domains assembled from repository controls, GitHub Actions, repository security features and independent operational evidence. A green Actions workflow is a check result, not a certificate that the entire application is secure or production-ready.

| # | Audit domain | Evidence already present in repository | Current status | Release gate still required |
|---:|---|---|---|---|
| 1 | Governance, architecture and requirements traceability | WP-00–WP-16 governance, project agreement, fixed work-package order, architecture/platform standards, code map, WP-01 R3 source package, WP-level acceptance ledger and handover register. | PARTIAL / DOCUMENTED | Reconcile all WP-01 requirement IDs to implemented workflows; close all WP-03–WP-16 requirements and evidence; approvals, data owners and operational ownership; full source provenance and release review. |
| 2 | CI, build, test and integration verification | Existing ERP CI and WP-01–WP-16 verification workflows run PHP/Laravel, PostgreSQL 18 migrations, forced-RLS assertions, PHP formatting/syntax, PHPUnit, Angular tests/build, npm/Composer audits, WP-01 source integrity and combined candidate artifact assembly. Earlier delivery checkpoints passed on their exact SHAs. | BASELINE PASS; CURRENT WP-03 CANDIDATE PENDING | New branch must pass both workflows on its exact head, then pass destination-branch checks after integration. Add full API/UI/E2E acceptance and production-like concurrency/recovery scenarios; do not infer a product-wide pass from selected tests. |
| 3 | Code quality, maintainability and static analysis | PHP Pint, PHP syntax checks, Angular compilation and unit tests run in CI. Repository includes architecture boundaries and typed frontend services. | PARTIAL | Add and enforce PHP static analysis (PHPStan/Larastan), TypeScript strict checks, template/accessibility checks, backend/frontend coverage thresholds, architecture/dependency-direction checks and quality trend reporting. |
| 4 | Dependency, license and software supply-chain security | Composer audit and npm audit are required by existing CI. Both Composer and npm lockfiles are committed. Source package has checksum and strict validation. | PARTIAL | Add PR dependency review where available, licence-policy and transitive dependency review, pinned/verified third-party Actions, SBOM generation and retained release SBOM. Artifact provenance/attestation requires plan-compatible support. |
| 5 | SAST, secret scanning and vulnerability response | Application source was previously checked for the reported Firebase/Cloudflare identifiers; none were found in the audited source. Existing CI runs dependency audits but the repository workflow inventory contains no CodeQL workflow. | NOT YET ENTERPRISE-VERIFIED | Enable GitHub Code Security/CodeQL or an approved SAST tool, secret scanning and push protection, publish alert triage policy/SLAs, and resolve or formally disposition all alerts. Do not describe unsupported alert APIs as zero alerts. |
| 6 | Identity, database, tenant isolation and audit integrity | Laravel Sanctum, identity/tenant context middleware, restricted database roles, PostgreSQL forced RLS on the listed tenant tables, authorization tests, append-only audit controls, and platform-admin/support separation. New WP-03 candidate adds database-enforced operator grant lifecycle. | PARTIAL; CURRENT MIGRATION/TESTS PENDING | Run exact-head PostgreSQL migrations/RLS and adversarial tests; verify all tenant-owned tables/routes/jobs/files/exports; test all privilege paths and direct SQL attempts; complete target-scope authorization, MFA/SSO, session rotation/recovery, audit retention/integrity, legal hold and data-subject requirements. |
| 7 | API, integrations, containers and operational security | Versioned same-origin Laravel REST API, controlled route registration, environment-bound infrastructure settings, provider-neutral core and local/private document storage. Redis/PostgreSQL service topology is defined for local/CI. | PARTIAL | Complete OpenAPI coverage, API compatibility/idempotency, webhook/outbox/device error and replay tests, secret provisioning, container build/hardening/scans, image SBOM, TLS/ingress/security-header configuration and deployment/rollback runbooks. External AI/storage/connectors stay optional. |
| 8 | Performance, resilience, backup/restore, UAT and release provenance | CI builds combined review candidate artifacts from tested commits and includes PostgreSQL/Redis test services. | NOT RUN / RELEASE BLOCKER | Load/concurrency/soak tests against production-like topology; backup and successful restoration drill; disaster-recovery objectives and failover; migration rollback/recovery rehearsal; factory/store UAT; independent penetration test; signed/tagged release; staging smoke test; authorized production deployment and post-release monitoring. |

## Current evidence boundaries

### Existing exact-head CI evidence

Past successful runs are scoped to the commit they tested. For example, the delivery branch has prior successful runs covering migrations/RLS, backend tests, Angular tests/build, dependency audits and WP-01 integrity. Those runs do not validate source added later on `work/wp03-completion-2026-11-27`.

For the active WP-03 candidate, the exact-head ERP CI and combined WP verification have not yet been observed completing. Its new SQL migration, Laravel grant workflow and Angular global operator pages must pass before this increment can be merged to delivery.

### GitHub Enterprise feature/license boundary

Repository-level workflow files can be committed, but enabling GitHub Security features and Enterprise audit-log access may require organization/enterprise owner permissions and the relevant plan or add-on. GitHub's current documentation states that Code Security and Secret Protection features for private/internal repositories require the relevant paid product; artifact attestations for private/internal repositories require GitHub Enterprise Cloud. The connected repository API session has not established the organisation's plan, purchased entitlements, rulesets, enterprise audit-log access or alert inventory.

Useful official references:
- GitHub ruleset controls: https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-rulesets/available-rules-for-rulesets
- GitHub Code Security / Secret Protection availability: https://docs.github.com/en/enterprise-cloud@latest/get-started/learning-about-github/about-github-advanced-security
- Code-scanning merge protection: https://docs.github.com/en/enterprise-cloud@latest/code-security/how-tos/find-and-fix-code-vulnerabilities/manage-your-configuration/set-merge-protection
- Artifact attestations and private repository availability: https://docs.github.com/en/enterprise-cloud@latest/actions/how-tos/secure-your-work/use-artifact-attestations/use-artifact-attestations
- Enterprise security-alert auditing: https://docs.github.com/en/enterprise-cloud@latest/code-security/concepts/security-at-scale/audit-security-alerts

A GitHub administrator must confirm the organisation's Enterprise Cloud/Code Security/Secret Protection entitlements and apply rulesets. A security manager/enterprise owner must review the alert inventory and enterprise audit log. This repository session cannot grant itself those administrative permissions.

## Required release sequence

1. Finish WP-03, including all exact-head CI, acceptance, identity/scope and operational handover gates.
2. Complete WP-04 through WP-15 strictly in the approved work-package sequence; each package needs backend, Angular workflows, database/security, integration tests and evidence.
3. Complete WP-16 E2E-001–E2E-008, performance/soak, restore/DR, independent security review, staging and factory/store UAT.
4. Enable available repository/org security features and rulesets; verify results on the exact release candidate SHA. Resolve all alerts and retain SBOM/provenance artifacts.
5. Open final PR #1 promotion only after acceptance evidence and human/qualified-professional approvals are complete. Merge to main only after required checks/reviews; publish a versioned GitHub Release with source SHA and immutable artifact digests.
6. Production deployment remains a separate approved operation, followed by smoke tests, monitoring and rollback readiness.

**No release certificate or production deployment is claimed by this matrix.**


## WP-03 scope mutation hardening evidence — 27 November 2026

The candidate adds a database-owned mutation boundary for tenant scoped access. Migration `2026_11_27_000034_secure_tenant_access_scope_mutations.php` revokes direct runtime DML on `tenant_access_scope_modes` and `tenant_access_scopes`; `app_runtime` retains SELECT and EXECUTE only on three SECURITY DEFINER functions with fixed search paths. These functions revalidate actor and target membership and persist audit events atomically. Feature tests assert table privilege boundaries, permitted function execution, normal owner/admin mutations, duplicate-grant rejection and non-admin denial.

**Evidence status: source implemented; PostgreSQL execution and exact-head CI NOT VERIFIED.** The last inspected PR run on prior head `0e38ff2496597cb9dd559bcde7d4ae7022e588b8` showed backend and frontend jobs with `runner_id: 0`, blank runner names and zero executed steps; this head predates the final migration/service/test edits. The current branch head must still pass ERP CI and combined WP verification before this candidate increment is accepted.

This hardening does not make scoped authorization complete. The scope vocabulary remains BRAND/SITE/MARKET, and domain capabilities remain denied for SCOPED members until each controller action checks the actual target entity/site/department/warehouse/channel record. Do not count this as a pass for full identity/tenant-isolation acceptance or as closure of WP-03.
