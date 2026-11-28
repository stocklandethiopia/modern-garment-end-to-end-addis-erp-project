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
- **CAP-002 structural identifier reconciliation integrated; semantic review OPEN:** PR #32 merged at `bc442495df842f8fb06ae8b8d540145c69155023`; its exact tested head and runs remain recorded in the WP-01 handover. PR #33 refreshed that handover and merged at `0a12c85cbfdc6a18c1bf1b67bf0e543e513b4374`; tested head `37d88f175fffb805d4de38d108045ab8e8859825`, same merge tree `7a687a8954be0d9bf198ecee742f300f4e994439`. ERP CI #46162557764, combined WP verification #46162557766 and WP-01 source verification #46162557765 all passed on that exact head. The 231 candidate associations remain `DRAFT_REVIEW_REQUIRED`; no semantic equivalence, supersession or retirement is approved; all 128 R3 implementation statuses remain `not_started` and all 120 legacy GAR IDs remain preserved.
- **Master audit:** PR #30 remains the controlling audit document; exact head `12707a51a76c51110d07e9d4375eac1c2ec37d60` passed ERP CI #46122655184 and WP verification #46122655071. It remains a separate open audit PR.

## CAP-003 execution checkpoint
CAP-003 is active on work/wp00-cap-003-governance-register-current-delivery-2026-11-28, created from delivery SHA 0a12c85cbfdc6a18c1bf1b67bf0e543e513b4374. Register, strict structural/source-pin validator, validator tests and required combined-WP workflow gate are added on that branch. Exact-head CI and handover closeout remain pending. CAP-002 semantic review remains open and CAP-004 is dependency-held. Preserve migration sequencing, source/task evidence and exact-SHA handovers; do not interpret register publication or a passing validator as production approval.

## Gate
WP-00 is accepted. Every later package still needs its own implementation evidence and handover. `main` remains unchanged until WP-16 promotion criteria and accountable release approval are satisfied.
