# Corrective Action Execution Ledger — WP-00–WP-16

**Project:** Modern Garment End-to-End Addis ERP  
**Record date:** 28 November 2026  
**Control item:** CAP-001 — repository, branch, PR and evidence-ledger reconciliation  
**Working branch:** `work/wp00-cap-001-evidence-ledger-2026-11-28`  
**Canonical delivery baseline:** `delivery/autonomous-wp-01-16` at `3438ab47240c10ce5ea5f1f772c1570cc2191134`  
**Default branch:** `main` at `adca71a053290eb768987a7644d4b13c267cf2a9`  
**Master audit:** [PR #30](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/30), audit head `12707a51a76c51110d07e9d4375eac1c2ec37d60`.

## 1. Control and evidence rules

This ledger operationalizes CAP-001 and is the current index for the subsequent CAP actions. The full action definitions, exit conditions, WP-level gap details and dependency waves remain in the [WP-00–WP-16 master reconciliation audit](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/blob/audit/wp00-wp16-master-reconciliation-2026-11-28/docs/audit/wp00-wp16-master-reconciliation-audit-2026-11-28.md).

- A branch, PR, source file, migration or artifact is not proof that its change is integrated or accepted.
- A CI result is bound to the exact SHA and workflow run. Never transfer a PASS to another commit.
- Distinguish `PASS`, `FAIL`, `NOT RUN`, `BLOCKED`, and `PARTIAL`. A failed run with no relevant executed steps is not evidence that application tests failed.
- Keep candidate branches separate from canonical delivery. Do not merge candidate PRs independently while their migration numbers/dependencies conflict.
- Do not write working commits to `delivery/autonomous-wp-01-16` or `main`. Use a dedicated `work/wpNN-...` branch and a PR targeting delivery.
- No scope, work-package order, architecture, statutory rule, or human approval boundary is amended by this ledger.
- A WP handover and its source map are updated on the same branch as the corresponding corrective action. Advance to dependent CAP work only after required exact-head checks and evidence are recorded.

## 2. CAP-001 execution record

| Field | Current evidence |
|---|---|
| Objective | Refresh the live canonical/head/PR/migration/verification inventory and identify stale source-map/handover pointers |
| Audited delivery SHA | `3438ab47240c10ce5ea5f1f772c1570cc2191134` |
| Main SHA | `adca71a053290eb768987a7644d4b13c267cf2a9` |
| Delivery baseline ERP CI | [Run #46086017090](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46086017090) — **SUCCESS**; Laravel/PostgreSQL, Angular, and lockfile jobs all passed |
| Audit PR checks | [ERP CI #46122655184](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46122655184) — **SUCCESS**; [WP verification #46122655071](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46122655071) — **SUCCESS**, both on audit head `12707a51a76c51110d07e9d4375eac1c2ec37d60` |
| Working correction branch | `work/wp00-cap-001-evidence-ledger-2026-11-28`, created directly from delivery SHA `3438ab47240c10ce5ea5f1f772c1570cc2191134` |
| Application-code change | None in CAP-001; documentation and evidence-index corrections only |
| Current disposition | **IN PROGRESS — final exact-head PR checks and handover verification required** |

### 2.1 Live open pull-request inventory

Inventory was refreshed from GitHub on 28 November 2026. All listed PRs were open at observation time; the state below is not a statement of merge approval.

| PR | Scope / title | Head branch @ SHA | Base branch @ SHA | Draft | Mergeable | CAP-001 disposition |
|---:|---|---|---|---:|---:|---|
| #1 | Canonical delivery → main promotion | `delivery/autonomous-wp-01-16` @ `3438ab47240c10ce5ea5f1f772c1570cc2191134` | `main` @ `adca71a053290eb768987a7644d4b13c267cf2a9` | Yes | Yes | Final WP-16 promotion path only; do not merge before CAP-028 and accountable release authorization |
| #2 | Prior audit evidence / corrective plan | `audit/corrective-action-plan-2026-11-24` @ `0d005ab32ab6b0e4c24ac87727f2d9ae3fb12024` | `audit/stockland-local-source-v0.3.0` @ `0730df72b382663c13022be6ed5e17bcab6a64a6` | Yes | Yes | Historical audit chain; review and reconcile against PR #30, not a delivery source branch |
| #3 | Agent-native ERP reference/feature branch | `feature/agent-native-erp-foundation-2026-11-25` @ `dfdf503b4aae4b0e787ca1cb3826c4fd97171b20` | `audit/corrective-action-plan-2026-11-24` @ `0d005ab32ab6b0e4c24ac87727f2d9ae3fb12024` | Yes | Yes | Child of historical audit chain; preserved as source/reference evidence, not delivery integration |
| #13 | WP-03 platform operator and scoped access | `work/wp03-completion-2026-11-27` @ `735d404fdf996e58bd07140cd900dc55c50c13ed` | delivery @ `f948bb85de4194ae18dc8ee8c974c3ad0986623c` | Yes | No | Stale/diverged; rebase only in consolidated CAP-030 migration train |
| #14 | Library apparel-sample reconciliation | `work/library-apparel-sample-reconciliation-2026-11-27` @ `b765460a608dd629920b9d1ef4b3512aa0db1eaa` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | Yes | Yes | Documentation/reference candidate on stale base; inspect before deciding incorporation |
| #16 | WP-03 organogram and delegated authority design | `work/enterprise-organogram-doa-2026-11-27` @ `ba3e0607f00bdd7e28ab5495a41150faf3f8a242` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | No | Yes | Documentation candidate; reconcile in WP-03/WP-14 requirements and decision records |
| #17 | WP-01 R3 reconciliation evidence | `work/wp01-r3-reconciliation-2026-11-27` @ `b3d7a918ef2ae41813c7786a23cb57944ddbf25f` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | No | Yes | Key CAP-002 candidate evidence; inspect and reconcile, do not duplicate its work blindly |
| #19 | WP-03/WP-14 organogram, authority and payroll plan | `work/wp03-wp14-organogram-payroll-plan-2026-11-27` @ `db297912814a722d5c0571004f9cedec82d2504f` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | No | No | Documentation candidate on stale base; source for CAP-003/007/012/024 review |
| #20 | WP-14 Ethiopia payroll candidate | `work/wp14-ethiopia-payroll-localization-2026-11-27` @ `4b89e9f7cbbc939d3080b70e3fc48b247e72abb5` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | Yes | No | Stale/diverged; statutory activation settings remain false; reconcile source/legal proof and migration train before integration |
| #22 | CI runner continuity route | `work/wp03-ci-runner-continuity-2026-11-27` @ `f95d1ad1baa42506c438d1c8353b2d50a3891a12` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | Yes | Yes | Infrastructure continuity candidate; inspect as an operational route, not a reason to lower product gates |
| #23 | WP-15 agent/device transition grants | `fix/wp15-agent-device-runtime-grants-2026-11-27` @ `8497cfc76200e1263f0f5ce47afdb668773758bc` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | Yes | Yes | Stale/diverged; candidate exact-head workflow results failed but job step payloads were unavailable; source must be reverified on fresh base |
| #24 | WP-03 engineering design and Ethiopia localization | `work/wp03-full-engineering-design-2026-11-27` @ `c7811d1cd79d352b215abe221d8427ef1ebcf49b` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | Yes | No | Documentation candidate; preserve and reconcile with #25 to avoid duplicate or misattributed branch references |
| #25 | WP-03 enterprise technical design | `work/wp03-enterprise-technical-design-2026-11-27` @ `e84ad6bc1e3991446b412d3c023c293cd4e35c2d` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | Yes | Yes | Documentation candidate; correct WP-03 map branch pointer |
| #27 | WP-11/WP-15 private storage and quality correction | `work/fix-private-document-storage-rls-inventory-2026-11-27` @ `b6e5486324e0eca18c065b253d1640ea7e6f2416` | delivery @ `fc01eced62b97c4ec27f0075264fc8ba0fb11851` | Yes | Yes | Stale/diverged candidate; exact-head ERP CI and component/WP-01 checks passed; still not integrated and migration 000038 must be train-ordered |
| #29 | WP-03 tenant-scoped organization structure | `work/wp03-org-structure-canonical-2026-11-28` @ `c716757e3b0fe091b2e6223b416237d32e455c8e` | delivery @ `3438ab47240c10ce5ea5f1f772c1570cc2191134` | Yes | Yes | Current-base candidate; exact-head ERP CI and component/WP-01 checks passed; migration 000035 remains a candidate; stress/accessibility/125+ acceptance remains NOT RUN |
| #30 | Master WP-00–WP-16 audit | `audit/wp00-wp16-master-reconciliation-2026-11-28` @ `12707a51a76c51110d07e9d4375eac1c2ec37d60` | delivery @ `3438ab47240c10ce5ea5f1f772c1570cc2191134` | Yes | Yes | Documentation-only audit branch; both exact-head workflows passed; not code integration |

Open PRs counted: **16**. Branch/base relationships are intentionally preserved as observed; a mergeable flag does not authorize merging and does not prove current-base correctness.

### 2.2 Candidate migration collision register

The current integrated migration history ends at `2026_11_26_000032_add_platform_entitlements_and_tenant_lifecycle.php`. Candidate PRs reuse subsequent numeric sequence identities. Treat this as a hard integration stop:

| Candidate PR | Candidate migration path(s) | Current disposition |
|---:|---|---|
| #13 | `2026_11_27_000033_create_platform_operator_grant_lifecycle.php`; `2026_11_27_000034_secure_tenant_access_scope_mutations.php`; `2026_11_27_000037_isolate_identity_control_functions.php` | Candidate only; order and dependencies require consolidation |
| #20 | `2026_11_27_000033_require_stored_statutory_source_document.php`; `2026_11_27_000034_require_payroll_line_evidence_hash.php`; `2026_11_27_000036_protect_payroll_run_line_evidence.php` | Candidate only; statutory source/activation holds remain |
| #23 | `2026_11_27_000033_grant_agent_and_device_runtime_transition_columns.php` | Candidate only; rebase and verify runtime permissions |
| #29 | `2026_11_27_000035_create_organization_structure_masters.php` | Candidate only; exact-head checks pass, broader acceptance still open |
| #27 | `2026_11_27_000038_repair_quality_inspection_review_state.php` | Candidate only; exact-head checks pass, migration/upgrade integration remains open |

**CAP-030 rule:** rebase candidates to a fresh delivery head, construct one dependency-ordered train with unique migration identities after 000032, test from an empty PostgreSQL 18 database and an upgrade path, verify rollback/forward recovery and least-privilege RLS/API behavior. Do not independently merge, casually rename, or rewrite migrations that may already have been applied.

### 2.3 Branch ancestry and verification matrix

Compare results against delivery SHA `3438ab47240c10ce5ea5f1f772c1570cc2191134`:

| Candidate | Compare status | Ahead | Behind | Merge base |
|---|---:|---:|---:|---|
| PR #13 | diverged | 122 | 78 | `f948bb85de4194ae18dc8ee8c974c3ad0986623c` |
| PR #20 | diverged | 69 | 63 | `fc01eced62b97c4ec27f0075264fc8ba0fb11851` |
| PR #23 | diverged | 2 | 63 | `fc01eced62b97c4ec27f0075264fc8ba0fb11851` |
| PR #27 | diverged | 33 | 63 | `fc01eced62b97c4ec27f0075264fc8ba0fb11851` |
| PR #29 | ahead | 28 | 0 | `3438ab47240c10ce5ea5f1f772c1570cc2191134` |

| SHA / PR | Workflow evidence observed | Interpretation |
|---|---|---|
| Delivery `3438ab47240c10ce5ea5f1f772c1570cc2191134` | ERP CI #46086017090: all 3 jobs SUCCESS | Exact canonical delivery ERP CI pass |
| PR #29 head `c716757e3b0fe091b2e6223b416237d32e455c8e` | ERP CI #46089028046 SUCCESS; component/WP-01 verification #46089027993 SUCCESS | Candidate checks only; not merged; 125+ stress/browser/accessibility acceptance remains NOT RUN |
| PR #27 head `b6e5486324e0eca18c065b253d1640ea7e6f2416` | ERP CI #46080661268 SUCCESS; component/WP-01 verification #46080661241 SUCCESS | Candidate checks only; not merged |
| PR #13 head `735d404fdf996e58bd07140cd900dc55c50c13ed` | No exact-head workflow run returned in the commit workflow lookup | Exact-head verification NOT OBSERVED; do not infer pass |
| PR #20 head `4b89e9f7cbbc939d3080b70e3fc48b247e72abb5` | ERP CI #46024115391 and combined #46024115411 concluded FAILURE; job detail returned `steps: null` | Failure status recorded; executed test steps/root cause not observable from returned job data; no claim of code tests failing or passing |
| PR #23 head `8497cfc76200e1263f0f5ce47afdb668773758bc` | ERP CI #46016837226 and combined #46016837218 concluded FAILURE; job detail returned `steps: null` | Failure status recorded; executed test steps/root cause not observable from returned job data; no claim of code tests failing or passing |
| PR #30 audit head `12707a51a76c51110d07e9d4375eac1c2ec37d60` | ERP CI #46122655184 SUCCESS; WP verification #46122655071 SUCCESS | Exact audit-document head checks passed; this is not CAP completion |
| PR #1 → main synthetic merge preview | ERP CI #46086022250 failed on synthetic merge checkout `4570039...`; backend reached Pint and reported one style issue in `tests/Feature/QualityInspectionReviewStateTest.php`; migrations and backend feature tests were skipped; Angular passed | **PR-preview-only failure**, not a failure on delivery SHA 3438. Track the style observation with WP-11/CAP-021, reproduce on a fresh relevant exact head before coding; do not call baseline delivery CI failed |

### 2.4 Stale references and documentation corrections in this CAP branch

- WP-00 source map is updated to reference the new master audit and this execution ledger.
- WP-03 source map is corrected: PR #21 is superseded by PR #29; PR #24 and PR #25 branch names are distinguished; PR #13 and PR #29 exact-head status and candidate-only boundaries are explicit.
- WP-11, WP-14 and WP-15 source maps now state exact candidate heads, migration names and exact verification interpretation.
- WP-16 source map now records delivery 3438 exact ERP CI, PR #30 exact docs checks, and the separate PR #1 synthetic preview observation.
- README and AGENTS pointers are synchronized to the active WP-00/CAP-001 work branch and the handover register, without changing product scope or the permanent WP order.

## 3. Finding ledger

| ID | Severity | WP / CAP | Finding / cause | Required action | Status |
|---|---|---|---|---|---|
| INV-001 | P0 | WP-00 / CAP-001 | Open PRs span current, stale and unrelated historical bases; a mergeable flag is not a valid ancestry/integration check | Maintain the exact-SHA inventory; rebase applicable candidates only after dependency/migration reconciliation | Recorded; candidate branches remain separate |
| INV-002 | P0 | WP-00 / CAP-030 | Candidate migrations collide on 000033–000038 after canonical 000032 | One consolidated dependency-aware migration train and PG18 fresh/upgrade/recovery evidence | OPEN — hard integration stop |
| INV-003 | P1 | WP-03 / CAP-001/012 | WP-03 source map pointed at closed/superseded PR #21 and misattributed the PR #24/#25 design branches | Correct branch/PR references and preserve superseded history without integrating duplicate work | Corrected in this branch; exact branch checks pending |
| INV-004 | P1 | WP-11 / CAP-021 | PR #1 synthetic merge preview reported one Pint style issue in `QualityInspectionReviewStateTest.php` | Reproduce against a current quality candidate or fresh merge simulation; format/test the applicable final source SHA; do not conflate with delivery baseline CI | Recorded for WP-11; not yet fixed or dispositioned |
| INV-005 | P1 | WP-14 / CAP-024 | Payroll candidate is stale/diverged and its associated workflows failed with no step details returned | Inspect exact candidate source, rebase safely, test evidence hashes/control totals on a fresh exact head; retain statutory activation false pending professional approval | OPEN — candidate only |
| INV-006 | P1 | WP-15 / CAP-025/026 | Agent/device grants candidate is stale/diverged and workflow job steps are unavailable | Rebase after migration plan, run PostgreSQL privilege and replay/concurrency tests on exact candidate SHA | OPEN — candidate only |
| INV-007 | P1 | WP-03 / CAP-012 | Organization structure PR #29 is current-base and CI-passing but broader 125+ stress/browser/accessibility acceptance is NOT RUN | Keep candidate-only; complete outstanding acceptance and migration-train sequencing | OPEN — candidate only |
| INV-008 | P1 | WP-16 / CAP-028 | PR #30’s workflow name says WP-01–WP-16 completion verification, but this run validates the workflow's configured component/package scope, not all 125 tasks or E2E-001–008 | Keep audit wording explicit; do not infer ERP/package completion from that check | Recorded; no acceptance claim |

## 4. CAP progress register

| CAP | WP | Priority | Status at this ledger revision | Dependency / next gate |
|---|---|---:|---|---|
| CAP-001 | WP-00 | P0 | IN PROGRESS — inventory and documentation reconciliation recorded; exact final-branch checks pending | Complete exact-head ERP CI and combined WP/source verification on this branch/PR, then record final SHA and handover |
| CAP-002 | WP-00/WP-01 | P0 | NOT STARTED | Start after CAP-001 is exact-head verified; reconcile R3 128 against GAR 120 |
| CAP-003 | WP-00 | P1 | NOT STARTED | Build decision/assumption/risk/exception register from accepted evidence |
| CAP-004 | WP-01 | P0 | NOT STARTED | Depends on the CAP-002 canonical identity/alias crosswalk |
| CAP-005 | WP-01 | P1 | NOT STARTED | Measurable NFR catalogue and verification methods |
| CAP-006 | WP-02 | P0 | NOT STARTED | Official primary source bytes, dates, hashes and authority inventory |
| CAP-007 | WP-02/WP-13/WP-14 | P0 | NOT STARTED | Effective-dated configurable statutory rules; qualified Ethiopian review before activation |
| CAP-008 | WP-03 | P0 | NOT STARTED | Tenant lifecycle, export, retention, legal hold and recovery |
| CAP-009 | WP-03 | P0 | NOT STARTED | Two-person platform operator grant lifecycle |
| CAP-010 | WP-03 | P0 | NOT STARTED | Same-tenant and cross-tenant authorization scopes across every endpoint/job/export |
| CAP-011 | WP-03 | P1 | NOT STARTED | MFA/OIDC/SSO and full session/device lifecycle |
| CAP-012 | WP-03/WP-14 | P1 | NOT STARTED | Organization/position/acting appointment/DoA and candidate #29 acceptance |
| CAP-013 | WP-03 | P1 | NOT STARTED | Role-specific platform/operator UX and accessible journeys |
| CAP-014 | WP-04 | P1 | NOT STARTED | Forecast/OTB/pricing/source lineage |
| CAP-015 | WP-05 | P1 | NOT STARTED | Immutable product passport and release dependency chain |
| CAP-016 | WP-06 | P1 | NOT STARTED | Approved SAM/method study, capacity and skill checks |
| CAP-017 | WP-07 | P1 | NOT STARTED | RFQ/PO/receipts/AP, UOM and material genealogy |
| CAP-018 | WP-08 | P1 | NOT STARTED | Executable route conditions and versioned SOP |
| CAP-019 | WP-09 | P1 | NOT STARTED | Departmental WIP and source-linked material movements |
| CAP-020 | WP-10 | P1 | NOT STARTED | SAM-based plan/actual and workforce productivity controls |
| CAP-021 | WP-11 | P0 | NOT STARTED | Independent quality review, current hold and atomic CAPA |
| CAP-022 | WP-12 | P1 | NOT STARTED | WMS/POS/channel commerce/POD/returns/refunds |
| CAP-023 | WP-13 | P0 | NOT STARTED | AP/AR/treasury/cost/valuation/FX/period close/reversals |
| CAP-024 | WP-14 | P0 | NOT STARTED | People/workforce lifecycle and source-backed payroll controls |
| CAP-025 | WP-15 | P1 | NOT STARTED | Private storage, extraction/import, outbox, device trust and replay |
| CAP-026 | WP-15 | P1 | NOT STARTED | Least-privilege agents, preconditions, approvals and analytics |
| CAP-027 | WP-16 | P1 | NOT STARTED | Reproducible installer, non-root images and SBOM/provenance |
| CAP-028 | WP-16 | P0 | NOT STARTED | Full E2E-001–008, security, accessibility, performance, DR, UAT and release record |
| CAP-029 | Cross-WP | P1 | NOT STARTED | OpenAPI, error/pagination/correlation contracts, UX/evidence consistency |
| CAP-030 | Cross-WP | P0 | OPEN — migration collision recorded | Consolidated unique migration train; gate candidate integrations |

## 5. CAP-001 exit criteria

CAP-001 is not complete merely because this file exists. Close it only when:
1. the inventory and stale-map corrections are present in the final PR head;
2. the exact final PR head has the required ERP CI and combined WP/source verification executed successfully;
3. the handover register and WP-00 source map point to the exact commit, PR and run evidence;
4. the branch inventory and candidate migration list are reconciled against the same final observation; and
5. CAP-002 starts only after that evidence is recorded.

## 6. Next dependency

After CAP-001 verification, execute **CAP-002**: reconcile the 128 R3 records against the 120 visible GAR rows using the existing R3 archive, requirement matrix, amendments and PR #17 evidence. Every one of the eight deltas must receive an explicit mapping, alias/supersession or other evidence-backed disposition. No requirement is removed or treated as implemented by this reconciliation.
