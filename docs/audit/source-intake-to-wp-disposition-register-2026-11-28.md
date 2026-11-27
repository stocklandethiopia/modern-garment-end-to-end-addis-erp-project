# Source intake to WP-00–WP-16 disposition register

**Review date:** 28 November 2026  
**Working branch:** `work/source-intake-and-wp-mapping-2026-11-28`  
**Base canonical delivery SHA:** `dbdf112272f7f1e0d1ccf933e45061a79cc5e59b`  
**Source-intake origin:** `feature/agent-native-erp-foundation-2026-11-25` at `dfdf503b4aae4b0e787ca1cb3826c4fd97171b20`  
**Current intake-copy commit:** `fdf91a421069797124a7bfb32eb08cda2019db57`

## 1. What was collected and moved

The source branch contains 124 files under `source-intake/stockland-garment-erp-local-v0.3.0/`. Those 124 Git blobs were copied byte-for-byte into this work branch using their existing blob SHAs. No source file was rewritten during the copy. The canonical `delivery` and `main` refs were not changed by this intake operation.

The same origin branch also contains 4 `.agents/skills/` files and 9 `.codex/agents/` definitions. They are development-agent configuration, not application runtime source; they were not silently folded into the canonical application tree and remain separately identifiable on the origin branch for review.

## 2. Source classes and their permitted use

### A. Stockland Garment ERP v0.3.0 local application
Source root: `source-intake/stockland-garment-erp-local-v0.3.0/source/`.

- Runtime: Node.js native HTTP + browser JavaScript/PWA.
- Persistence: SQLite, schema version 2, 45 application tables.
- Manifest: 106 manifest-listed files, plus `BUILD_MANIFEST.json`.
- Original source archive SHA-256 recorded in the intake evidence: `5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1`.
- Build-manifest SHA-256 recorded: `0d7c03dac0be52df3a4699fcdb93b4da8b45cb853980dcc0a62a598568722c0`.
- Its requirement matrix has 30 rows and marks local tests as passed, but it is not the canonical SaaS stack.

**Disposition:** preserve intact as historical source and behavior evidence. Review the actual code/SQL/test/document artifacts, then port the business capability into the canonical Laravel/PostgreSQL services, Angular features, migrations, APIs, ledgers and tests. Do not replace the approved application with its Node/SQLite runtime or copy its tables wholesale without multi-tenant/security/domain redesign.

### B. X POS reference kit
Source root: `source-intake/.../source/reference-sources/OPEN_SOURCE_REFERENCE_KIT/`.

- Upstream provenance: `kodlyft/xpos`, `develop`, commit `4f65d681f766c0dcc73fcbb2debc7bd105c8223f`.
- License recorded: MIT; preserve `LICENSE`, `ORIGIN.md`, `SOURCE_MANIFEST.json` and `SOURCE_REGISTER.csv`.
- Selected application files: `invoiceSubmission.ts`, `offlinePolicy.ts`, `barcode.py`, `stock.py`; selected tests: `test_invoice_retry.py`, `test_stock_guards.py` (six source/test files).
- Stockland adaptation map and acceptance matrix are present.

**Disposition:** use as a bounded pattern reference for barcode lookup, inventory guard, payload-stable/idempotent sale submission and offline eligibility. Do not treat six selected files as a complete POS, or copy upstream Frappe/ERPNext code into the Laravel/Angular app without a reviewed adaptation and retained licence notices.

### C. Python/SQLite 19-stage reference runtime
Evidence: `LOCAL_REFERENCE_RUNTIME_VERIFICATION_2026-11-24.md`, Python source ZIPs under `source/reference-sources/`, workflow run `45397701554`.

- Seven declared source-file hashes matched and 16 Python tests passed in the separate reference workflow.
- A 19-stage example replay was tested in that reference environment.
- The report explicitly excludes canonical Laravel/PHP/Angular/PostgreSQL/RLS, real concurrency, full UI/API/auth, costing/GL, WMS/POS breadth, security/performance and production recovery.

**Disposition:** port the useful scenario as canonical WP-08/WP-09/WP-11/WP-15 acceptance fixtures after mapping units, state changes and ledger evidence. These reference results are not canonical WP-16 acceptance.

### D. Existing canonical ERP application
Canonical base: `delivery/autonomous-wp-01-16` at `dbdf112272f7f1e0d1ccf933e45061a79cc5e59b`.

- Angular 22 frontend.
- Laravel 13 / PHP 8.5 backend.
- PostgreSQL 18 with forced tenant RLS and restricted runtime/migration/control roles.
- Redis 8.2.
- Ordered Laravel migrations and domain services in `backend/`; feature routes/services/UI in the approved folder code map.
- Post-merge ERP CI run `46080457557` on exact delivery SHA passed.

**Disposition:** this remains the application source of truth. CI pass verifies that revision and its exercised gates; it does not mean all WP-00–WP-16 tasks, production data tables, business workflows or release criteria are complete.

## 3. WP folder mapping created

The per-WP `SOURCE_MAP.md` files in `work-packages/wp-00...` through `wp-16...` point to:
1. exact canonical runtime source paths already present;
2. relevant collected source/artifact paths;
3. unmerged candidate branches/PRs where relevant;
4. missing code, migration, UI, test and acceptance evidence.

These are source maps, not generic implementation placeholders and not duplicate application modules. No runtime code has been represented as complete simply because a map or folder exists.

## 4. Candidate code branches and current evidence

| Candidate | Scope | Evidence/disposition at review |
|---|---|---|
| PR #13, `work/wp03-completion-2026-11-27` | WP-03 platform admin and scoped access | Latest failure run #445 exposed a valid ADMIN approver being rejected by a MEMBER-only scope target guard; correction is now committed and requires new exact-head CI |
| PR #20, `work/wp14-ethiopia-payroll-localization-2026-11-27` | WP-14 Ethiopia payroll candidate | Source/legal vectors and candidate code exist; no legal approval/activation claim; requires migration-sequence reconciliation and exact CI |
| PR #21, `work/wp03-org-master-runtime-2026-11-27` | WP-03 org structure master candidate | Candidate migration/source exists; requires dependency, API/RLS/UI/tests and CI review |
| PR #23, `fix/wp15-agent-device-runtime-grants-2026-11-27` | WP-15 agent/device grants | Candidate migration/source exists; requires exact CI and integration review |
| PR #27, `work/fix-private-document-storage-rls-inventory-2026-11-27` | WP-11/WP-15 private document and quality integrity | ERP CI #977 and component/WP-01 #442 passed on exact candidate SHA `b6e5486324e0eca18c065b253d1640ea7e6f2416`; PR remains unmerged and migration integration still needs reconciliation |

Candidate run evidence is branch-specific; never carry a PASS from one SHA to another.

## 5. Migration-number collision — integration stop

Several independent candidate branches reuse migration sequence numbers. They cannot be integrated independently in the current state.

| Candidate branch | Existing candidate migration names |
|---|---|
| WP-03 PR #13 | `000033_create_platform_operator_grant_lifecycle`, `000034_secure_tenant_access_scope_mutations`, `000037_isolate_identity_control_functions` |
| WP-14 PR #20 | `000033_require_stored_statutory_source_document`, `000034_require_payroll_line_evidence_hash`, `000036_protect_payroll_run_line_evidence` |
| WP-15 PR #23 | `000033_grant_agent_and_device_runtime_transition_columns` |
| WP-03 PR #21 | `000035_create_organization_structure_masters` |
| WP-11/WP-15 PR #27 | `000038_repair_quality_inspection_review_state` |

**Required action:** rebase candidate code against a fresh delivery head; create one ordered migration train with unique timestamps after the canonical 000032 baseline; inspect foreign-key/function/table dependencies; run the entire migration chain from an empty PostgreSQL 18 database using the restricted migrator; run rollback/forward compatibility checks; prove RLS and API behavior on least-privilege roles; only then prepare a consolidated integration PR. Do not rename migrations already deployed in any real environment without a recorded migration-history compatibility plan.

## 6. Database and Memory boundary

The canonical repository owns its schema in `backend/database/migrations/` and tests it against PostgreSQL 18 in GitHub Actions. The connected Supabase project list returned no projects in this session; no connected production database or business-data instance was verified. Do not create parallel Supabase tables or change the approved PostgreSQL architecture without a formal architecture decision.

ChatGPT Memory is conversational personalization/context, not the ERP database. A disabled Memory setting does not prevent migrations, schema design, local/CI PostgreSQL tests or application development. Account Memory must be changed by the user in ChatGPT Settings → Personalization → Enable memory; it cannot be enabled by a repository commit or a GitHub/Supabase tool.

## 7. Current disposition and next execution order

1. Complete exact WP-03 repair and verification on PR #13; keep it unmerged while any gate fails.
2. Reconcile WP-03 organization, WP-11 quality, WP-14 payroll and WP-15 agent/device candidates into one dependency-aware migration train.
3. Continue building the next eligible canonical WP tasks from actual source artifacts; use the WP maps as the intake ledger and update them when source is ported.
4. Run the complete per-WP unit/API/RLS/integration/concurrency/E2E acceptance suite at exact SHA; update application path map, requirements trace and handover.
5. WP-16 remains open for full end-to-end, security, performance, backup/restore, staging/UAT, rollback and approved release.

**No new external source upload was requested or performed.** The next work should be analysis, implementation, reconciliation and verification of the already committed material.
