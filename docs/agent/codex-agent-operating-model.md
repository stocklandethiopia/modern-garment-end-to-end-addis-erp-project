# Codex Agent Operating Model

**Product:** Modern Garment End-to-End Addis ERP  
**Decision date:** 25 November 2026  
**Status:** Project-scoped development-agent baseline; Codex Cloud is not required.

## 1. Purpose and boundaries

Codex agents help evolve the ERP source and engineering documentation. They are separate from the Operational ERP Agent running inside the live application.

- Codex agents inspect, plan, develop, test, review and document source in a trusted developer workspace.
- Operational ERP agents act on business data only through Laravel capabilities and authenticated-user permissions.
- Codex is not a runtime business identity. Operational ERP agents do not write source code or deploy releases.
- No Codex agent receives production credentials, production database access or live deployment authority by default.

Read root `AGENTS.md`, Project Governance Agreement, Platform Architecture Standard and Agent-Native Technology Integration Standard before work.

## 2. Free-plan-compatible operation

OpenAI's current plan guide states Codex is included on Free and Go with plan-dependent usage limits; Codex Cloud is not included on Free or Go. Local CLI and IDE use is therefore the project baseline. Cloud environments, paid API credits, paid plugins and external hosted execution are optional, not prerequisites.

- Use Codex CLI or supported IDE/Desktop client signed in with the user's ChatGPT account.
- Work in a trusted local checkout and non-production feature branch/worktree.
- Root project instructions are in `AGENTS.md`.
- Project-scoped custom agents are standalone TOML files in `.codex/agents/`.
- Reusable skill instructions are in `.agents/skills/<skill-name>/SKILL.md`.
- Use only models/tools actually available to the signed-in account. Avoid pinning agent definitions to a model or feature that may not be included in Free.
- Usage limits vary; inspect allowance with `/status` in Codex CLI or the current account usage view.
- If multi-agent delegation is available, use one coordinator and only the smallest useful set of specialists. If it is unavailable, concurrency is capped, or usage is exhausted, execute the same roles sequentially.
- Do not bypass sandbox/approval controls to conserve usage or make work faster.

Plan details can change. Re-check the linked official plan guide before making account-entitlement claims.

## 3. Permissions and inheritance

Custom Codex agents inherit the parent session's permissions, provider and configured MCP servers. A TOML role description does not create an independent security boundary or provide separately restricted credentials.

Therefore:
- Configure sandbox and approval behavior in the parent Codex session/settings.
- Never use dangerous approval/sandbox bypass options.
- Use trusted clones, inspected branches and scoped integrations only.
- Keep credentials out of tracked files. MCP access is inherited; connect only minimum required services.
- Use separate worktrees/branches for parallel file changes; assign exclusive ownership of overlapping paths.
- Security/QA reviewer roles are instructed not to edit, but enforce read-only through the parent sandbox/repository permissions if required.
- All production release controls remain outside agent authority.

## 4. Agent catalog

| Agent | Main responsibility | Output |
|---|---|---|
| ERP Chief Architect | Protect product scope, architecture, sequence, task boundaries and handovers | Impact assessment, plan, decisions and final reconciliation |
| Garment Domain Analyst | Apparel initiative, PLM, sourcing, factory, IE, quality, WMS and channel workflows | Actors, SOP, controls, exceptions and acceptance scenarios |
| Laravel Platform Engineer | Laravel modules, APIs, permissions, transactions, PostgreSQL, queues and outbox | Services, migrations, contracts, tests and evidence |
| Angular Experience Engineer | Angular workspace, conversational UI, files, mobile/device UX and accessibility | Components, flows, client contracts and UI tests |
| Agent Integration Engineer | Agent Gateway, provider adapter, typed tools, orchestration, MCP, retrieval and tracing | Capability contract, policies, tools and evaluation |
| Data and Optimization Engineer | BI, retrieval, forecasting, optimization and specialist Python | Reproducible data analysis, assumptions, evaluation and scenarios |
| Security and RLS Reviewer | Tenant/branch isolation, permissions, privacy, prompt injection and supply chain | Independent findings with severity, evidence and remediation |
| QA and Concurrency Reviewer | Integration, regression, idempotency, locking, queues, performance and recovery | Test matrix, evidence, defects and release risks |
| Documentation and Governance Reviewer | Specs, decision records, links, Codex guidance and handovers | Consistency review, updated docs and evidence index |

Do not spawn all roles for every task. The architect selects only the specialists needed. It remains accountable for reconciling their outputs.

## 5. Task routing

- New or unclear business need → Chief Architect + Garment Domain Analyst.
- New device, API, agent tool, model or workflow → Chief Architect + Agent Integration Engineer + Security Reviewer.
- Laravel/database behavior → Laravel Engineer + QA Reviewer.
- Angular/chat/mobile/camera → Angular Engineer + QA Reviewer.
- Forecasting/analytics/optimization → Data Engineer + relevant domain owner.
- RLS, sensitive employee data, finance or HR → Security Reviewer + domain engineer + required human reviewer.
- Documentation/governance → Documentation Reviewer + Chief Architect.
- Material merge/release → independent Security and QA review of exact commit.

## 6. Standard execution workflow

1. Fetch current branch/head; review governance agreement, README, relevant domain standard and last accepted handover.
2. Classify request as defect, in-scope implementation, approved architecture extension or material change proposal.
3. If scope, legal meaning, architecture baseline, work-package order or acceptance criteria would change, follow the governance approval process before implementation.
4. Write a bounded brief: affected paths, contracts, non-goals, dependencies, permissions, owners and acceptance tests.
5. Delegate independent research/read/test tasks; avoid simultaneous edits to shared files.
6. Implement on an isolated branch/worktree using current project conventions and lockfiles.
7. Run relevant format, static analysis, unit, integration, RLS, security, concurrency, build and regression checks.
8. Have Security and QA review the exact commit/diff.
9. Update docs, requirement traceability, skills, runbooks and handover.
10. Push only to authorized feature branch and open a reviewable PR. Do not merge/deploy around approval boundaries.

Report PASS, FAIL, NOT RUN, BLOCKED, NOT APPLICABLE and PENDING OWNER APPROVAL accurately.

## 7. Repository agent definitions

- `.codex/agents/erp-chief-architect.toml`
- `.codex/agents/garment-domain-analyst.toml`
- `.codex/agents/laravel-platform-engineer.toml`
- `.codex/agents/angular-experience-engineer.toml`
- `.codex/agents/agent-integration-engineer.toml`
- `.codex/agents/data-optimization-engineer.toml`
- `.codex/agents/security-rls-reviewer.toml`
- `.codex/agents/qa-concurrency-reviewer.toml`
- `.codex/agents/documentation-governance-reviewer.toml`

Project skills:
- `.agents/skills/erp-capability-intake/SKILL.md`
- `.agents/skills/agent-tool-implementation/SKILL.md`
- `.agents/skills/garment-conversational-import/SKILL.md`
- `.agents/skills/safe-code-evolution/SKILL.md`

These files define role instructions and repeatable workflows. They do not install an independent runtime, guarantee subagent availability, grant extra permissions or confer production authority.

## 8. Local startup checklist

1. Open a trusted clone in Codex CLI or supported IDE.
2. Check repository origin and branch before trusting workspace instructions.
3. Start from repository root so `AGENTS.md` is loaded.
4. Ask the Chief Architect to inspect the task and select the smallest specialist set.
5. Run specialists sequentially where delegation or usage limits require it.
6. Keep work on feature branch/worktree; test and review final diff.
7. Push a reviewable branch/PR; keep production release outside agent authority.

## 9. References

- [Using Codex with your ChatGPT plan](https://help.openai.com/en/articles/11369540-using-codex-with-your-chatgpt-plan)
- [Codex custom agents and subagents](https://learn.chatgpt.com/docs/agent-configuration/subagents)
- [Codex Skills](https://developers.openai.com/plugins/concepts/skills)
- [Codex CLI](https://developers.openai.com/codex/cli)
