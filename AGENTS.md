# Agent Instructions — Modern Garment End-to-End Addis ERP

Before doing any work in this repository, read:
1. `docs/agent/project-governance-agreement.md`
2. `README.md`
3. The relevant approved domain, architecture, localization, payroll, security and engineering specifications referenced by the agreement.

## Non-negotiable project rules

- Work only on the Modern Garment End-to-End Addis ERP goal described in the governance agreement.
- Treat the governance agreement and approved product documents as scope boundaries.
- Do not add, remove, narrow, broaden, substitute, reorder or reinterpret product requirements without explicit Product Owner approval.
- If a requirement conflicts, a law is unclear, or a proposed change is outside the baseline: do not alter the baseline; isolate the affected decision, document evidence/options/impact, continue independent work and flag any genuine human-only sign-off.
- Do not interpret silence, investigation requests or tool recommendations as approval.
- Do not resume from an old chat summary alone. Fetch current GitHub head and reconcile it with the Notion project goal and latest accepted handover.
- Work in numbered work packages in the fixed WP-00 to WP-16 order. The Product Owner accepted WP-00 and explicitly delegated autonomous execution and routine package approval for WP-01 through WP-16; complete, evidence, self-review and hand over each package, then continue without routine owner pauses.
- Every workflow must reflect actual apparel practice and contain SOPs, actors, controls, exceptions, quality, audit, decisions and measurable benefit—not just CRUD/data entry.
- Use official Ethiopian authority sources for legal matters. Do not guess rates, thresholds, legal interpretations or effective dates. Keep unverified items blocked pending qualified local approval.
- Keep manufacturing, store, wholesale, reseller, creator and supplier payment relationships correctly classified. Do not collapse all payments into payroll.
- Preserve separate fabric, trim, bundle, WIP, finished-goods and carton movement/audit chains.
- Never claim a build, test, legal review, release, Codespace transfer or deployment that was not actually performed and verified.
- No unrelated side projects, technologies, repositories, external services or workspaces. Research only to serve the approved ERP goal.
- Do not perform live statutory activation, actual payroll/financial execution, destructive production-data changes, secret disclosure or high-impact production deployment without the appropriate accountable human authorization. These gates do not block safe design, code, tests, documentation, local builds or non-production verification.

## Current checkpoint and advancement gate

- WP-00 is accepted by the Product Owner on 22 November 2026.
- The Product Owner expressly authorized ChatGPT to execute WP-01 through WP-16 autonomously, make in-scope engineering and delivery decisions, internally approve packages on evidence, and proceed without routine owner involvement.
- Preserve the fixed package order, complete each package's definition of done and record the numbered handover before advancing.
- Stop only the affected action for a genuine baseline conflict, unresolved law, missing external permission/credential, or human-only irreversible/high-impact operational approval. Continue independent safe work.

## Git workflow and clean-source rules

- Use a dedicated `work/wpNN-...` branch created from the current `delivery/autonomous-wp-01-16` head for the active work package. The active WP/CAP and current working branch are recorded in `docs/work-packages/handover-register.md`; never assume a branch from an older handover is still current.
- Commit and push working edits only to the dedicated work branch. Do not create working commits directly on `delivery/autonomous-wp-01-16` or `main`.
- Open the integration PR to `delivery/autonomous-wp-01-16` only after the change has a numbered scope, updated path map/task record, exact-head tests and security review appropriate to the change.
- The 26 November standing owner amendment authorizes verified integration merges without another routine owner acceptance form or notification response. This does not waive technical gates, complete WP definitions of done, legal/professional review or production deployment approval.
- Keep `delivery/autonomous-wp-01-16` as the canonical integration/release-candidate branch. Keep `main` unchanged until final WP-16 production promotion is justified by the exact release evidence and accountable approval.
- Keep lockfiles, migration history, source-package manifests/checksums and runtime `.gitignore` markers. Never commit `.env`, credentials, classic tokens, local data, `vendor/`, `node_modules/`, `dist/`, coverage, caches or temporary bundles.
- Remove only files/workflows proven obsolete by trigger/consumer inspection. Preserve accepted evidence, checksummed source inputs and historical branches until their disposition has been verified and recorded.
- Keep `docs/architecture/application-folder-code-map.md` current whenever application files are added, moved or removed. Exact paths, route registration and tests must be updated in the same branch.

## Mandatory PR, review and workflow-finding response

This rule applies to **every pull request, every review cycle, every inline thread (including resolved threads), every conversation comment, every requested change, every GitHub Actions workflow, every CI/check run, every security/dependency finding and every WP branch**.

- Inspect the exact current PR head: review submissions, inline threads, conversation comments, requested changes, check runs, workflow logs, artifacts and changed source. Trace each finding to the actual source, tests, configuration, requirement and downstream dependencies; do not rely only on a summary or bot comment.
- Maintain a finding register with a unique reference, severity, affected WP/requirement, source path/line, cause, consequence, required correction, owning branch and disposition.
- **Every actionable in-scope finding requires an engineering response, not report-only closure.** Reproduce or trace it; fix, improve or build the affected source/capability; add or strengthen regression, integration, security and acceptance tests; update dependent migrations, CI role/bootstrap, installer/deployment configuration, API contracts, source maps and operator documentation; then rerun applicable checks.
- Work on the owning PR's dedicated branch where possible. Keep unrelated work separate. Do not dismiss, suppress, downgrade, close or resolve a finding merely because a comment was posted or code changed. Resolve a thread only after the correction and its required verification evidence pass; otherwise explain the exact outstanding check/dependency in the thread.
- Documentation-only disposition is allowed only when evidence shows no source/build/test change is required or a genuine external, human-only or legal/professional decision is required. Explain why, preserve evidence, continue independent safe work and state the precise remaining action.
- For a failed workflow, inspect job and step execution. Distinguish runner/permission/quota/policy allocation failure (no code step ran) from actual build, test, lint, migration, security or artifact failure. Repair workflow/configuration/source defects where possible and retry exact-head checks. A workflow with no execution of the relevant job is NOT RUN, not PASS.
- Re-run applicable backend, database/migration/RLS, frontend, integration, source-integrity, security, artifact and combined WP verification gates against the **exact final commit SHA**. Record run IDs, job/step outcomes, test/assertion counts and artifact/checksum identifiers where available.
- Commit review corrections only to the appropriate dedicated work branch. Do not bypass WP-00–WP-16 order, canonical integration branch, technical gates or production controls. Merge only after required exact-SHA gates pass; a verified increment does not imply full WP completion or production approval.
- Post a factual GitHub response for each finding with the action, files, commits, checks and current disposition. Then report the same finding-by-finding outcome in the project conversation with exact SHAs, run IDs, PASS/FAIL/NOT RUN/BLOCKED status and remaining dependencies.
- Continue until no actionable in-scope finding remains unaddressed. Where an external permission, runner, legal/professional approval or human-only production action genuinely prevents completion, record the evidence, precise blocker and safe next action; do not turn a temporary constraint into an enduring ERP business rule or product-scope reduction.

## Required handover format

For each work package report:
- Work package ID and approved scope.
- Numbered requirements completed.
- Files/commits changed.
- Exact tests/commands and PASS/FAIL/NOT RUN/BLOCKED status.
- Official sources/legal approvals where relevant.
- Known gaps and risks.
- Acceptance evidence.
- Next package and whether autonomous progression is authorized or blocked by a specific human-only gate.
