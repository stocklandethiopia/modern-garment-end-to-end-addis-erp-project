---
name: safe-code-evolution
description: Safely implement or change ERP code, schema, APIs, integrations, agent capabilities and workflows with Codex.
---

# Safe ERP Code Evolution

1. Read AGENTS.md, governance, current work-package record and relevant domain/architecture standards.
2. Fetch current branch/head and confirm the requested change is approved and in scope.
3. Record assumptions, modules, contract/migration impact, dependencies and non-goals.
4. Work in isolated feature branch/worktree; assign file ownership before parallel work.
5. Preserve backward compatibility and version public APIs/events. Never destructively alter finance, inventory or payroll history.
6. Add migrations and forward/rollback compatibility tests; never migrate production as part of agent work.
7. Test authorization, tenant/branch RLS, idempotency, concurrency, queue retry, failure and compensation.
8. Run relevant formatting, static analysis, unit, integration, security and build checks; record exact outcomes.
9. Obtain independent Security/QA review of exact commit.
10. Update docs, capability registry, skills, release notes and handover.
11. Push only to authorized feature branch and open PR; never bypass merge/release approval.
