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

- **CAP-001 CLOSED:** PR #31 final tested head `c0175bded5113c4ec8b98b4589e641e310b57a0c`; ERP CI #46131518062 PASS; configured WP verification #46131518069 PASS; merged to delivery at `217aa9848e1499617aeab76d77349f4bb1805066`. Candidate and merge trees were identical. Documentation/evidence changes only; separate delivery push-side run is not asserted.
- **CAP-002 CLOSED FOR STRUCTURAL IDENTIFIER RECONCILIATION:** PR #32 final tested head `6e8788be6849a78115ee138eb68a607cbaea1c03`; ERP CI #46154426395 PASS; combined WP verification #46154426366 PASS; dedicated WP-01 source package #46154426369 PASS; merged delivery at `bc442495df842f8fb06ae8b8d540145c69155023`. The seven-column artifact preserves 128 R3 IDs and 120 legacy GAR IDs with 231 candidate edges. All edges remain `DRAFT_REVIEW_REQUIRED`; graph labels do not authorize semantic equivalence or GAR retirement; all R3 implementation statuses remain `not_started`.
- **CAP-003 ACTIVE:** governance register `docs/governance/decision-assumption-risk-exception-register-2026-11-28.md` (blob SHA `22bb5d0a16bc7aa28ba9536ec06fe8f9e7542052`) committed on `work/wp00-cap-003-governance-register-2026-11-28`; current branch is based on delivery `bc442495df842f8fb06ae8b8d540145c69155023`. It contains 13 decisions, 8 assumptions, 12 risks and 6 bounded exceptions, with authority, owner role, source evidence and review triggers. Executable validator: `scripts/verify-wp00-governance-register.mjs`; exact CI gate is added to the combined WP workflow. PR-head tests and handover merge are pending.
- **CAP-004 NEXT:** build requirement-level traceability from each R3/GAR relationship to accountable owner, WP task, executable workflow, source path, schema/migration, API, UI, event, test, exact-SHA evidence and runbook. No requirement status moves to implemented without code-level proof.
- **Master audit:** PR #30 remains the controlling audit plan, exact audit head `12707a51a76c51110d07e9d4375eac1c2ec37d60`; its ERP CI #46122655184 and WP verification #46122655071 passed. PR #30 remains a separate open audit PR.

## Remaining build / verification
Complete CAP-003 exact-head ERP CI and full WP workflow, inspect governance-register validator logs, update this handover with the merge SHA and run evidence, then proceed to CAP-004. Maintain branch/commit/migration discipline. The governance register does not replace legal, finance, security or production approvals; those boundaries remain explicit in its section 2 and the project governance agreement.

## Gate
WP-00 is accepted. Every later package still needs its own implementation evidence and handover. `main` remains unchanged until WP-16 promotion criteria and accountable release approval are satisfied.
