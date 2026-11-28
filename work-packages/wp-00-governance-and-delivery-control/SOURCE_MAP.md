# WP-00 — governance and delivery control source map

**Disposition:** owner acceptance and autonomous WP-01–WP-16 execution authorization are recorded; current work is source reconciliation and controlled integration, not a new product-scope change.

## Source-of-authority and evidence
- `AGENTS.md` — mandatory workflow, work-package order, branch, PR, review and exact-SHA rules.
- `docs/agent/project-governance-agreement.md` — approved scope and operating boundary.
- `docs/product/apparel-business-operating-system-charter.md` and `docs/product/domain-map.md` — product objective and bounded contexts.
- `docs/work-packages/production-execution-plan-wp00-wp16.md` — 125 numbered corrective/build tasks.
- `docs/work-packages/handover-register.md` — package status and next actions.
- `docs/audit/wp00-wp16-source-level-gap-audit-2026-11-27.md` — historical detailed source-level gap evidence.
- `docs/audit/wp00-wp16-master-reconciliation-audit-2026-11-28.md` on audit PR #30 — controlling 125-task reconciliation and CAP-001–030 master action plan.
- `docs/audit/corrective-action-execution-ledger-2026-11-28.md` — live PR/branch/migration inventory, evidence register, finding dispositions and CAP execution status.
- Intake evidence under `source-intake/stockland-garment-erp-local-v0.3.0/` — historical source reports and owner-supplied evidence, not overriding canonical policy.

## Canonical target paths
- `AGENTS.md`
- `docs/agent/project-governance-agreement.md`
- `docs/product/`
- `docs/architecture/`
- `docs/work-packages/`
- `docs/audit/`
- `work-packages/` — per-WP source maps, manifests, and exact evidence pointers.

## Corrective-action execution checkpoint — 28 November 2026

- **CAP-001 closed:** PR #31 was merged to canonical delivery at `217aa9848e1499617aeab76d77349f4bb1805066`. Final PR head `c0175bded5113c4ec8b98b4589e641e310b57a0c` passed ERP CI #46131518062 and configured WP verification #46131518069. Candidate and merge commit trees are identical. The changes were documentation/evidence-ledger and handover only; no application code or migrations changed. The workflow lookup in this execution exposed PR-triggered runs; a separate direct push-side run is not asserted.
- **CAP-002 integrated / automated checks PASS; semantic review OPEN:** PR #32 merged at delivery SHA `bc442495df842f8fb06ae8b8d540145c69155023`. Tested candidate SHA `6e8788be6849a78115ee138eb68a607cbaea1c03` and merge commit share tree `404363a4c89638019a6379500d95da7a89481a95`. PR #32 (CAP-002) merged to `delivery/autonomous-wp-01-16` at `bc442495df842f8fb06ae8b8d540145c69155023`. Tested candidate SHA: `6e8788be6849a78115ee138eb68a607cbaea1c03`; candidate and merge commits have the identical Git tree `404363a4c89638019a6379500d95da7a89481a95`. Exact-head ERP CI [#46154426395](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426395) passed (93 Laravel tests / 1,081 assertions; 27 Angular tests / 7 spec files; PostgreSQL migration/RLS, PHP quality and dependency checks passed). Exact-head combined WP verification [#46154426366](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426366) passed all four jobs, including WP-01 R3 source integrity, R3↔GAR validator and candidate artifact assembly. Dedicated WP-01 source package [#46154426369](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/46154426369) passed source archive/checksum validation, 20/20 package tests and strict audits A01–A10/F01–F07. The automated identity-preservation and structural-integrity implementation is integrated and verified. The 231 candidate associations remain `DRAFT_REVIEW_REQUIRED`: topology labels do not establish semantic equivalence, supersession or retirement. Review each association against both complete requirement statements, actors, triggers, workflows, controls, evidence and acceptance boundaries. All 128 R3 implementation statuses remain `not_started`; all 120 legacy GAR IDs remain preserved. CAP-002 remains open for this semantic review; CAP-004 must not treat draft associations as approved mappings.
- **Master audit:** PR #30 remains the controlling audit document; exact head `12707a51a76c51110d07e9d4375eac1c2ec37d60` passed ERP CI #46122655184 and WP verification #46122655071. It remains a separate open audit PR.

## Remaining build / verification
CAP-002 exact-head CI and integration are complete; the WP-01 semantic association review remains open. Proceed next with independent CAP-003 in WP-00, while preserving CAP-004 as dependent on approved semantic mapping. Reconcile every candidate branch against the exact current `delivery` SHA; preserve migration sequencing, task evidence and WP handovers. Planning records do not substitute for implementation, tests, professional approval or release evidence.

## Gate
WP-00 is accepted. Every later package still needs its own implementation evidence and handover. `main` remains unchanged until WP-16 promotion criteria and accountable release approval are satisfied.
