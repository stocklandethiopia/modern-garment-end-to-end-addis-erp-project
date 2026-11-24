# Agent-Native Technology Integration Standard

**Product:** Modern Garment End-to-End Addis ERP  
**Decision date:** 25 November 2026  
**Status:** Product Owner-approved architecture direction; implementation proceeds through existing work packages.  
**Authority:** Project Governance Agreement Amendment A-01.

## 1. Purpose and doctrine

Build the most capable, relevant and supportable modern ERP we can deliver now, while preserving the ability to adopt technologies, devices, applications, agents, workflows and business practices that are not known today.

This is not a speculative catalogue of future modules. The durable investment is an architecture that can evaluate, integrate, govern, version, observe, update and retire new capabilities without unnecessary reconstruction of the ERP core.

The approved garment-expert, initiative-to-sell-through business goal and WP-00–WP-16 sequence remain unchanged. Technology must improve those workflows; it must not redirect product scope.

## 2. Two separate agent planes

### 2.1 Operational ERP Agent

This is the user-facing assistant inside the running ERP. It interprets intent, searches authorized records, explains results, prepares drafts, understands supplied files, discovers approved capabilities, coordinates tasks and executes permitted operations through Laravel domain application services.

Examples:
- Describe a new garment and have the agent find matching master data, detect duplicates, prepare STYLE/VARIANT/SKU drafts, validate attributes and submit the draft/publish workflow.
- Upload a branch POS workbook in chat; stage its rows, suggest column and variant-SKU mappings, identify exceptions, preview totals, obtain required approval and commit through the existing POS import service.
- Ask about stock, production WIP, purchase status, quality holds, order fulfilment or branch performance using the same authoritative business services as the conventional UI.
- Interact using text, voice, images, camera capture and supported business documents where the client and use case allow it.
- Start a business task conversationally and transfer the user to a structured ERP screen when review or complex data entry is required.

The Operational Agent does not edit application source code, run migrations, change permissions, deploy software, or bypass domain workflows.

### 2.2 ERP Evolution / Codex Agents

These are development-time agents operating in a trusted developer workspace. They inspect approved source, analyze a change, prepare architecture and implementation plans, edit code in isolated branches/worktrees, run tests, update documentation and prepare reviewable pull requests.

They are not production business identities, production database principals or deployment authorities. They must not receive live production credentials or silently change approved product rules.

### 2.3 Authority boundary

The model interprets intent and coordinates. Laravel validates, authorizes and executes. PostgreSQL enforces persisted truth and tenant boundaries. Human approval remains mandatory for established high-impact and irreversible operations.

## 3. Current-generation capabilities to build into the ERP now

| Capability | Where it applies | Required behavior |
|---|---|---|
| Conversational actions and typed tool calling | All business domains | Narrow, versioned capabilities instead of model-generated SQL or simulated UI clicks |
| Multimodal workspace | Product/design, warehouse, stores, inspection, support | Text, voice, images, camera and approved file intake with contextual handoff |
| Coordinated specialist agents | Complex research, imports, analytics and engineering | One supervisor delegates bounded tasks, reconciles outputs and presents one traceable result |
| Permission-aware enterprise retrieval | SOPs, product specs, policies, contracts and operations | Retrieval is filtered by tenant, role, document permission, version and provenance |
| Document intelligence | Excel/CSV, PDF, invoices, tech packs and quality evidence | Preserve originals; extract to typed staging; validate, score confidence and expose exceptions |
| Workflow automation | Approval, procurement, production, quality, transfers, POS and finance | Versioned workflow with states, actors, conditions, deadlines, escalation and compensation |
| Event-driven integration | Domain changes and external applications | Transactional outbox, versioned events/webhooks, retries, deduplication and reconciliation |
| Advanced analytics and decision support | Initiative, assortment, MRP, manufacturing, quality, stores, finance | Explain source metrics, assumptions, confidence, constraints and recommended action |
| Forecasting and optimization | Demand, materials, capacity, line balance and replenishment | Validated data, versioned models and explainable scenarios before operational changes |
| Device and edge integration | Scanning, weighing, printing, inspection and sensors | Vendor-specific adapters normalize data into validated ERP events |
| API and tool ecosystem | Suppliers, carriers, payment/POS partners, CAD/PLM and other systems | Versioned REST/OpenAPI, events and MCP where useful |
| Continuous software engineering | ERP source maintenance and new capabilities | Codex-assisted research, plans, implementation, tests, review and release evidence |
| AI evaluation and observability | Every agent workflow | Trace tool calls, evaluate quality/safety, monitor latency/cost and preserve user corrections |

These capabilities are implemented when they materially support an approved business outcome and meet maturity, data-readiness, security, supportability and cost requirements. The table is a present-day technology baseline, not an exhaustive list of future features.

## 4. Applicability across the garment business

| Domain | Appropriate agent/intelligent functionality | Non-delegable controls |
|---|---|---|
| Initiative, brand and range | Research synthesis, sourced trend evidence, initiative drafts, assortment and price scenarios | Provenance, confidence and commercial approval |
| Design and PLM | Design brief extraction, tech-pack comparison, measurement/fit issue summaries, revision assistance | Human design/fit approval and controlled production handover |
| Product Master | Conversational product/variant drafts, duplicate detection, attribute completion and SKU suggestions | Authoritative master service, uniqueness checks and publish permission |
| Sourcing and procurement | Quote/document extraction, supplier comparison, lead-time and shortage alerts | Supplier, contract, PO and payment authorization |
| IE and manufacturing | Line-plan scenarios, capacity, bottleneck/WIP alerts, skill coverage and method-study support | Approved SMV/SAM, worker safeguards and supervisor/planner approval |
| Quality and compliance | Evidence capture, image-assisted defect triage, root-cause and CAPA suggestions | Qualified inspection, quarantine and quality release |
| WMS and inventory | Scan resolution, stock lookup, movement preparation, discrepancy explanation | Ledger-backed movement, lot/location control and approved adjustment |
| Distribution, wholesale and retail | POS file intake, order support, allocation/replenishment scenarios and returns | Duplicate/tender reconciliation, branch permissions and finance controls |
| Finance | Document intake, matching, reconciliation and variance explanation | Double-entry integrity, maker-checker, posting/payment approval |
| HR and payroll | Policy retrieval, workforce planning, training and skills insights | Privacy, fair-work, statutory review, payroll and employment decisions |
| Management and audit | Natural-language questions over governed KPIs, sourced reports and evidence packs | KPI definitions, data lineage and row/column security |

No agent is enabled for every domain merely because it can call a tool. Each domain owner defines its capability contracts, permissions, tests and acceptance.

## 5. Reference architecture

Keep the approved platform stack and modular-monolith decision. Do not introduce a second transactional API or immediate microservice fleet.

1. **Angular ERP workspace:** contextual assistant, text/voice/camera/file input where supported, structured results, approvals, task status and navigation to normal domain screens.
2. **Laravel Agent Gateway:** authenticated task/conversation APIs, server-derived user and tenant context, orchestration, tool discovery, validation, approvals, rate limits, correlation IDs and audit.
3. **Model/provider adapter:** isolate provider-specific SDK/API details. The ERP's workflow state and business rules must not be owned by a model provider.
4. **Capability registry:** publish enabled tools with version, schema, owner, scope, risk, approval and compatibility metadata.
5. **Domain services:** Product, Inventory, POS, PLM, Procurement, Manufacturing, Quality, Finance, HR and other modules remain owners of rules and transactions.
6. **Workflow and job execution:** queue long tasks, persist state outside model context, handle retries, deadlines, escalation and compensation.
7. **Data and integration foundation:** PostgreSQL is system of record; outbox/events provide reliable integration signals; Redis remains ephemeral cache/queue coordination; object storage holds source documents and generated files.
8. **Specialist compute:** isolated Python services may perform forecasting, optimization, document/vision processing or analysis. They return typed proposals; Laravel validates and authorizes changes.
9. **Observability:** capture agent/provider/model and prompt/tool versions, correlation/task IDs, approvals, outcomes, latency, costs where available, failures and evaluation results while minimizing sensitive data.

Normal ERP operations must continue when an AI provider, external service, queue worker or internet connection is unavailable.

## 6. Capability contract

Every agent-accessible operation must define:
- Unique capability name and semantic version.
- Description, business domain and accountable owner.
- Typed input/output schema, validation and error semantics.
- Required permissions and tenant/legal-entity/site/branch scope.
- Data classification and allowed fields.
- Risk tier and approval requirement.
- Read/draft/reversible/material/destructive classification.
- Idempotency key, duplicate behavior and transaction boundary.
- Retry, timeout, rate-limit and compensation rules.
- Audit events, correlation IDs and evidence retention.
- Tests for valid, invalid, unauthorized, cross-tenant, duplicate, concurrent, partial-failure and rollback cases.
- Compatibility, deprecation and migration policy.

Tool descriptions are not authorization. Laravel re-checks the authenticated principal, tenant, role, row scope, workflow state and business limits on every invocation. Model-supplied user, tenant, branch or approval identity is never trusted.

## 7. Extension manifest and dynamic discovery

New functionality is packaged as a versioned extension/capability, not as an ad hoc prompt. A manifest identifies:
- Extension ID, name, publisher/maintainer and version.
- Supported ERP/API versions and dependencies.
- Business domain and UI entry points.
- Capability names, schemas, permissions and risk tiers.
- Event subscriptions and external integration endpoints.
- Data ownership, migrations and retention.
- Configuration and secret references (never secret values).
- Tests, security review, observability and support owner.
- Install, feature-flag, upgrade, disable and rollback procedures.

The registry describes what is actually installed and enabled. Agents discover tools from the authenticated registry and may not fabricate tool names or infer availability from documentation.

The registry must be open-ended: new capability categories, device classes and protocols can be introduced. Do not create speculative placeholder modules for every imaginable future technology.

## 8. Technology and capability lifecycle

1. **Intake:** capture the desired business outcome and official documentation, SDK/API, sample data, vendor/version and licence.
2. **Inspect:** map affected domains, existing capability gaps, data flows, security boundaries, connectivity and dependencies.
3. **Classify:** decide whether the change is configuration, workflow, adapter, extension, bounded context, specialist service or unsuitable.
4. **Assess:** value, maturity, technical fit, supportability, cost, privacy, security, vendor lock-in and failure modes.
5. **Design:** contract, permissions, data model, events, migrations, UI, operational owner and rollback.
6. **Prototype:** use synthetic data and isolated infrastructure.
7. **Verify:** automated tests, RLS/tenant checks, concurrency/idempotency, security, performance, evaluation and user acceptance.
8. **Approve and release:** obtain required owner/domain/security/legal approvals and use feature flags and controlled rollout.
9. **Operate:** register the approved version, train users and monitor cost, quality, failure and value.
10. **Upgrade or retire:** preserve history and compatibility; disable first; delete data only through a separately approved retention process.

For an unknown future capability, apply the same lifecycle; the architecture must not require the project to have predicted the category.

## 9. Autonomy and approvals

| Tier | Examples | Default |
|---|---|---|
| A0 — Read/explain | Search, lookup, summarize and explain KPIs | Automatic within caller authorization |
| A1 — Draft/recommend | Product draft, SKU mapping, plan or reconciliation proposal | Stage and show source/confidence |
| A2 — Reversible low-impact | Update drafts, approved mapping profile, workflow task | Scoped permission and audit |
| A3 — Material business action | POS batch commit, stock movement/adjustment, PO approval, order release, price activation | Domain permission and configured human approval |
| A4 — High-impact/irreversible | Payment, payroll execution, posted journal, statutory activation, destructive data change, production deployment | Explicit human/professional approval; never autonomous by default |

An agent can prepare an A3/A4 action and evidence, but cannot approve itself. Maker-checker, legal holds and existing human-only boundaries remain authoritative.

## 10. File, device and external-data boundaries

- Preserve the original upload and record hash, uploader, tenant, branch, time, type, size and retention.
- Validate real file type, size, encoding and parser safety. Never execute macros or embedded code. Parse in bounded workers.
- Treat spreadsheet cells, PDFs, QR payloads, source repositories, web pages, SDK samples, device messages and tool results as untrusted data. Prompt injection cannot change policy or tool permissions.
- Extract to staging with source-row mapping; validate confidence and exceptions; reconcile totals; require approval; commit through the owning domain service.
- Normalize scanner/device protocols through adapters into typed events. Record trustworthy device identity, adapter version, location, operator and event time.
- Offline capture must reconcile idempotently on reconnect. A scan is not proof that stock moved until the authorized ledger transaction commits.
- External webhooks/APIs require authentication, signature validation where available, replay protection, rate limits, retries and reconciliation.
- MCP can connect compatible external tools; internal ERP operations should use first-party Laravel capability contracts. MCP does not replace business authorization.

## 11. Data, memory and model governance

- Business facts live in PostgreSQL or controlled object storage, not model memory.
- Conversation context is scoped to authenticated user, tenant, purpose and retention policy. Cross-tenant memory/retrieval is prohibited.
- Retrieval must be permission-filtered before content reaches the model; keep source/version/provenance.
- Minimize personal, employee, financial and customer data sent to providers; redact or tokenize when possible.
- Keep keys, passwords, tokens, private certificates and live data out of prompts, code, skills and commits.
- Version prompts, provider settings, tool schemas, retrieval configuration and evaluation cases.
- Provide a disable switch, timeouts, quotas/cost budgets, circuit breaker, safe fallback and provider-outage behavior.
- Do not disable the ERP's normal business workflows when AI is unavailable.

## 12. Evaluation and operations

Each production agent workflow needs a versioned task specification, success criteria, representative synthetic/adversarial cases, tool-selection and argument evaluation, permission-denial/cross-tenant tests, human correction capture, latency/cost/failure monitoring, regression evaluation and an operator/rollback runbook.

Confident model wording is not proof. Inventory valuation, money, tax, payroll, production standards and statutory calculations use deterministic, versioned application rules. AI may explain or prepare inputs but cannot invent or silently adjust authoritative values.

## 13. Technology baseline

Use versions and compatibility policy from [Platform Architecture Standard](platform-standard.md). Laravel remains transaction and authorization authority; PostgreSQL remains persisted truth/RLS; Angular remains the enterprise UI; Redis, object storage, queues and Python retain approved roles.

Do not add Kafka, a second graph/vector database, another API framework, new frontend or another workflow engine merely because it is fashionable. Introduce infrastructure only after measured need, compatibility, operating cost and an approved architecture decision.

## 14. Codex team and Free-plan compatibility

The project-scoped Codex roles and run instructions are in [Codex Agent Operating Model](../agent/codex-agent-operating-model.md). Custom agents live in `.codex/agents/`; skills live in `.agents/skills/`; root guidance lives in `AGENTS.md`.

The baseline must work with local Codex CLI or supported IDE workflows available on the user's plan and within its current usage limits. Codex Cloud is optional, never required. When subagent concurrency or usage is unavailable, run the same roles sequentially. Custom agents inherit parent-session permissions; TOML files are not security boundaries.

## 15. Acceptance criteria

A new capability is accepted only when its business purpose/domain owner is clear; its contract and manifest are documented; positive and negative access tests pass; duplicate/retry/concurrency/failure behavior is tested; audit/trace evidence is verified; approvals and rollback are proven; cost/dependencies/support are documented; conventional UI/API remains usable; documentation/skills/runbooks are updated; and exact release evidence/limitations are recorded.

## 16. Technology intake record

Record: requested outcome; user/role; domain and workflow; current capability gap; technology/vendor/version; official docs/API/SDK; licence/support lifecycle; data categories; tenant/branch scope; hosting/connectivity; integration pattern; capability contract; permissions; approval tier; cost; risks; tests/evaluations; rollout/rollback; owner decision.

## 17. References

- [OpenAI — Using Codex with your ChatGPT plan](https://help.openai.com/en/articles/11369540-using-codex-with-your-chatgpt-plan) — plan availability and usage limits; re-check at use time.
- [Codex custom agents and subagents](https://learn.chatgpt.com/docs/agent-configuration/subagents) — project-scoped agent configuration and inherited permissions.
- [Codex skills](https://developers.openai.com/plugins/concepts/skills) — reusable skill folders.
- [Codex CLI](https://developers.openai.com/codex/cli) — local command-line workflows.
- [OpenAI Agents API](https://developers.openai.com/api/docs/guides/agents-api/overview) — optional managed orchestration building blocks.
- [Model Context Protocol specification](https://modelcontextprotocol.io/specification/2025-11-25) — external tool protocol reference.
