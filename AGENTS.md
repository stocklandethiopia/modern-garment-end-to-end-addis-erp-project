# Agent Instructions — Modern Garment End-to-End Addis ERP

Before doing any work in this repository, read:
1. `docs/agent/project-governance-agreement.md`
2. `README.md`
3. The relevant approved domain, architecture, localization, payroll, security and engineering specifications referenced by the agreement.

## Non-negotiable project rules

- Work only on the Modern Garment End-to-End Addis ERP goal described in the governance agreement.
- Treat the governance agreement and approved product documents as scope boundaries.
- Do not add, remove, narrow, broaden, substitute, reorder or reinterpret product requirements without explicit Product Owner approval.
- If a requirement conflicts, a law is unclear, or a proposed change is outside the baseline: stop, document the conflict/options/impact and wait for explicit approval.
- Do not interpret silence, investigation requests or tool recommendations as approval.
- Do not resume from an old chat summary alone. Fetch current GitHub head and reconcile it with the Notion project goal and latest accepted handover.
- Work in numbered work packages. Complete and hand over one package with evidence before starting the next package.
- Every workflow must reflect actual apparel practice and contain SOPs, actors, controls, exceptions, quality, audit, decisions and measurable benefit—not just CRUD/data entry.
- Use official Ethiopian authority sources for legal matters. Do not guess rates, thresholds, legal interpretations or effective dates. Keep unverified items blocked pending qualified local approval.
- Keep manufacturing, store, wholesale, reseller, creator and supplier payment relationships correctly classified. Do not collapse all payments into payroll.
- Preserve separate fabric, trim, bundle, WIP, finished-goods and carton movement/audit chains.
- Never claim a build, test, legal review, release, Codespace transfer or deployment that was not actually performed and verified.
- No unrelated side projects, technologies, repositories, external services or workspaces. Research only to serve the approved ERP goal.
- Do not change production data, activate legal rules, deploy or perform irreversible actions without the required explicit human approval.

## Current checkpoint and advancement gate

- WP-00 governance artifacts are committed and the handover is awaiting Product Owner acceptance.
- Do not begin WP-01 or any later work package until the Product Owner expressly accepts WP-00.
- After each package, stop at handover and wait for the Product Owner's review/acceptance before proceeding.

## Required handover format

For each work package report:
- Work package ID and approved scope.
- Numbered requirements completed.
- Files/commits changed.
- Exact tests/commands and PASS/FAIL/NOT RUN/BLOCKED status.
- Official sources/legal approvals where relevant.
- Known gaps and risks.
- Acceptance evidence.
- Next package, which must not start before Product Owner review.
