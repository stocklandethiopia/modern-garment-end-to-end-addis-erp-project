# Project Governance Agreement and Strict Change-Control Charter

**Project:** Modern Garment End-to-End Addis ERP  
**Owner:** The user as Product Owner  
**Working role:** ChatGPT as product/architecture/research/engineering collaborator  
**Recorded:** 22 November 2026  
**Status:** Owner-requested project working agreement  
**Repository:** https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project

> This is a project collaboration and change-control agreement, not a legal contract or a claim of control over the user's whole ChatGPT account, other conversations, external applications or independent plugins.

## 1. Agreement

For all work ChatGPT performs within this ERP project, the approved project goal, scope, principles, documents, plans and decisions listed below are the boundary. I agree to work within that boundary and not quietly replace, narrow, broaden, reinterpret or redirect it.

The single goal is to design and deliver the **Modern Garment End-to-End Addis ERP** as the garment-expert, customer-neutral apparel business operating system recorded in the approved project documents: beginning with business initiative, ideas, market insight, brand/collection strategy and in-house product development; continuing through sourcing, specialist department-by-department manufacturing, industrial engineering, skills, quality, compliance and traceability; and extending through central/regional distribution, owned branches and retail stores, wholesale, retailers, resellers, direct/digital channels, finance, procurement, HR/payroll, legal localization and continuous improvement.

It is not a generic ERP, not a cut-and-sew-only system, not merely a data-entry application, and not a continuation or redesign of the historical Stockland 16.x ERP. ERP 16.2.18 and sample repositories remain reference material only, subject to the approved reference policy and source/licence review.

## 2. Governing approved baseline

These documents collectively define the current baseline. Read them before proposing or implementing a change in their subject area.

1. [End-to-End Garment ERP Domain Map](../product/domain-map.md) — initiative-first, 12-domain scope and complete value stream.
2. [Apparel Business Operating System Charter](../product/apparel-business-operating-system-charter.md) — garment-expert culture, design/product development, specialist manufacturing processes, line planning, industrial engineering, skill matrix, quality and continuous improvement.
3. [Distribution Network, Order Management and Channel Commerce](../product/distribution-and-channel-commerce.md) — central/regional DCs, depots, branches, own retail, wholesale, retailer/reseller, D2C, POS, transport and reverse logistics.
4. [Platform Architecture Standard](../architecture/platform-standard.md) — bounded contexts, technical stack, data ownership, API/event and tenant boundaries.
5. [Engineering, Syntax and Code Quality Standard](../engineering/code-quality.md) — code and test quality gates.
6. [Development, Container and Deployment Standard](../engineering/development-deployment.md) — development and release discipline.
7. [Security and SaaS Tenancy Standard](../security/saas-security.md) — authorization, isolation, audit and security boundaries.
8. [Ethiopia Localization Standard](../localization/ethiopia.md) — country-pack and statutory activation rules.
9. [Ethiopia Legal and Regulatory Source Register](../localization/ethiopia-legal-regulatory-register.md) — authority-by-authority laws, directives, source status and open verification.
10. [Payroll and Variable Compensation](../hr/payroll-and-variable-compensation.md) — manufacturing, store, wholesale, reseller, creator and supplier classification and payment schemes.
11. [Approved Reference Repository Review](../references/reference-repositories.md) — reference-only use, source evidence and licence checks.
12. [Project README](../../README.md) — product identity, promise and document index.

The durable Notion memory is [Garment Expert Product Goal — Initiative-to-Sell-Through Operating System](https://app.notion.com/p/4205f6c73c68815d89bbd6f448726af1) under the [Chief Architect Agent Hub](https://app.notion.com/p/4185f6c73c6881cc922ad2cb5fccd264).

### Source precedence

If information conflicts:
1. A new, explicit Product Owner decision approved in the project conversation and recorded in the change log.
2. This governance agreement and the latest approved baseline documents.
3. Current applicable primary legal instruments and official authority directions for statutory matters. A law cannot be overridden by a product note.
4. Verified official or primary technical/industry sources used within the approved scope.
5. Secondary sources and reference repositories, which are evidence or inspiration only—not authority.

A newly published legal instrument does not silently amend the product specification or automatically change a calculation. It triggers a documented legal-change proposal, impact assessment, qualified local review and explicit approval before activation.

## 3. Scope lock — no silent changes

The following are fixed boundaries unless the Product Owner expressly approves an amendment:

- Initiative-first business model: own-brand, own-collection, own-store businesses are first-class; buyer orders are one demand source, not the universal starting point.
- Product development: design studio, ideas, collection/range, patterns, grading, samples, fit, 2D/3D, PLM, tech packs, costing and controlled production handover.
- Specialized manufacturing: separate department SOPs, work instructions, routes, inputs/outputs, material issues, skills, capacity, process parameters, quality gates and hand-off evidence.
- Distinct traceability for fabric rolls/lots, trims/accessories, cut panels/bundles, operation WIP, garments, cartons and finished goods.
- Industrial engineering: approved method/time studies, SMV/SAM, line balancing, capacity, daily/hourly target, earned minutes, line/operation efficiency, WIP and bottleneck management.
- Workforce capability: job/operation skill matrices, certification, skill-based deployment, cross-training, training and fair-work safeguards.
- Quality at source: incoming, first-piece, in-process/inline, end-line, special-process, final and shipment controls as applicable; defect containment, rework, root cause and CAPA.
- Continuous improvement: visual boards, Andon, 5S, worker suggestions, Kaizen, daily management and measurable before/after benefits.
- Full distribution and commerce: central/regional DC, depots, branches, company-owned stores, wholesale, retailers, resellers, direct customers, POS, e-commerce, delivery and reverse logistics.
- Full enterprise management: Finance, accounting, treasury, procurement, HR, payroll, training, safety, administration, reporting, analytics, audit and permissions.
- Ethiopia localization: sector-by-sector official legal sourcing; effective-dated legal rules; distinction between mandatory law and company/industry best practice; qualified local approval before statutory activation.
- Differentiated compensation: manufacturing line/team/skill arrangements, store commissions, wholesale sales commissions, reseller rebates, creator/influencer campaign income and supplier/subcontractor AP must remain correctly classified and separate.
- Customer-neutral SaaS tenancy and configurable business models. No hard-coded Stockland identity or legacy database/schema assumption.
- Approved architecture, code quality, security, audit, testing and release standards.

## 4. Express approval required for changes

No material change to any approved requirement, domain, workflow, business rule, legal interpretation, data ownership, architecture, technology, scope, sequence, acceptance criterion, security boundary, release criterion or baseline document may be implemented without the Product Owner's express approval.

For a proposed change, ChatGPT must:
1. Stop before implementing the disputed change.
2. Identify the exact existing requirement/document/decision.
3. Explain the conflict or limitation with evidence.
4. Present the proposed amendment, reason, alternatives and consequences.
5. Explain affected domains, workflows, data, security, legal compliance, cost/effort and tests.
6. Show the exact document sections that would change.
7. Mark it **PROPOSED — NOT APPROVED**.
8. Wait for a clear affirmative Product Owner decision.
9. Only then update the baseline, record the approval/date/rationale, update the cross-references and implement the approved change.

Silence, an unanswered message, a suggestion, a research finding or a tool's recommendation is not approval. Do not interpret a request to investigate as permission to change the product baseline. Do not overwrite or delete prior approved rationale; preserve a dated decision/change history.

Routine implementation details and defect fixes that are demonstrably inside the approved requirements may be handled autonomously, but must not alter the accepted behaviour, legal interpretation or scope. When in doubt, stop and ask.

## 5. Numbered work-package and handover discipline

Work is delivered in numbered work packages. Do not skip, merge, reorder or silently expand a package. A package is handed over when its scope is finalized and its acceptance evidence is complete. The Product Owner reviews that handover before the next package begins.

| ID | Work package | Required completion outcome |
|---|---|---|
| WP-00 | Governance and baseline control | This agreement, source-of-truth links, change log, scope lock and traceability method established |
| WP-01 | Requirements and traceability | Every approved requirement mapped to domain, user/role, workflow, data, evidence, acceptance test and owner |
| WP-02 | Ethiopia legal-source verification | Authority/source inventory by sector, current instruments and amendments, applicability, effective dates, unresolved matters and qualified-review gates |
| WP-03 | Enterprise and tenant foundations | Tenant/legal entity/business model/site/branch/factory/DC/store/channel/account model, identity, permission, audit and configuration boundaries |
| WP-04 | Initiative, brand, range and merchandising | Opportunity, research, brand, collection, range, assortment, buyer/customer and commercial planning workflows |
| WP-05 | Design studio and product development | Design, pattern/grade, 2D/3D assets, sample room, fit, tech pack, BOM, costing, revision and approval workflows |
| WP-06 | IE, product engineering and pre-production | Routing, operation bulletins, method/time study, SMV/SAM, machine/skill needs, capacity, design-for-manufacture and readiness gates |
| WP-07 | Procurement, suppliers and material traceability | Supplier governance, purchase-to-pay, fabric/trim/chemical sourcing, lot/roll/shade, receipt, inspection, separate issue/return and import controls |
| WP-08 | Department SOP and route framework | Versioned product-specific process routes, SOP/work instruction engine, roles, inputs/outputs, quality gates, exceptions and controlled hand-offs |
| WP-09 | Manufacturing execution by department | Cutting/spreading/marker/bundling; trim issue; optional print/embroidery; sewing; optional wash/dye/special processing; finishing/pressing; packing; subcontract processing and WIP |
| WP-10 | Production planning and workforce capability | Line/shift/day/hour target and actual, SMV-based efficiency, skill matrices, deployment, training, incentives governance, downtime, WIP, bottleneck and continuous improvement |
| WP-11 | Quality, compliance and HSE | Incoming, first-piece, inline, end-line, special-process, final and shipment quality; CAPA; chemical/environmental and worker-safety evidence |
| WP-12 | Warehouse, distribution and channel commerce | Raw/WIP/finished stock, central/regional DCs, branches/stores, ATP, transfer, replenishment, wholesale, retail/reseller, POS, e-commerce, transport and returns |
| WP-13 | Finance, accounting, treasury and tax | GL, AP, AR, cost accounting, inventory valuation, budgets, banking, tax, VAT, withholding, FX, customs, intercompany and statutory reports |
| WP-14 | HR, payroll, benefits and external compensation | Workforce lifecycle, time/attendance, payroll, pension, tax, training, skill, safety, manufacturing incentives, store/wholesale commissions, creator and supplier classification |
| WP-15 | Analytics, integrations and intelligent operations | Role-based BI, KPI definitions, product/channel profitability, event integrations, AI/optimization with explanation and human approval |
| WP-16 | End-to-end verification and release | Cross-domain scenarios, security/tenant isolation, legal-rule tests, performance, migration/backup/restore, container builds, deployment and rollback evidence |

The work-package table is a controlled sequence. Detailed task breakdowns are created within each package without changing the overall approved goal. If dependencies prove a change of sequence is necessary, present it as a proposal and obtain approval first.

## 6. Definition of done for every work package

A package is not complete merely because code, a page, table, form or document exists. Its handover must include:

1. **Scope:** approved package ID, exact requirements addressed and explicit exclusions.
2. **Workflow:** real business triggers, actors, permissions, states, transitions, SOP/work instructions and exception/rework paths.
3. **Decision support:** planning, validation, alert, optimization or next-action benefit; not data capture alone.
4. **Data and traceability:** entities, relationships, identifiers, source documents, audit events, ownership and retention.
5. **Legal/compliance:** applicable official source and rule version, or a clear blocked/not-applicable status; no guessed legal parameter.
6. **Integration:** contracts, events, idempotency, failure/retry and reconciliation with dependent domains.
7. **Quality:** automated tests and evidence for normal, edge, partial, rejected, reversal, concurrency and failure cases.
8. **Security:** role, tenant, legal-entity, site/branch and data-scope verification.
9. **Documentation:** updated approved specification, operator/admin guidance and support/recovery notes.
10. **Evidence:** exact commit, files changed, commands, test results, build/artifact identifiers and known limitations.
11. **Handover:** completed numbered report, open risks, decisions required and the next package—without starting it before Product Owner review.

Use evidence labels precisely: **PASS**, **FAIL**, **NOT RUN**, **BLOCKED**, **NOT APPLICABLE**, or **PENDING OWNER APPROVAL**. Never claim a test, source review, legal validation, release or deployment that did not occur.

## 7. Legal, financial and people-related safety gates

- Official law/regulation/directive and responsible authority must be identified by subject and jurisdiction. Secondary ERP examples are not legal authority.
- Legal rules are versioned and effective-dated with instrument, clause, source file/hash, publication/effective date, applicability, calculation base, thresholds, exceptions, reviewer, local approval and test vectors.
- Historical payroll, tax filings, invoices, customs records, inventory valuation and posted journals must not be silently recalculated or overwritten after a legal change.
- Unknown legal scope or conflicting authority metadata means **BLOCKED / REVIEW REQUIRED**, not a guessed rate.
- Employment, freelancer, creator, reseller and supplier classifications must be based on the actual relationship and applicable law—not the label used in a transaction.
- Production efficiency and incentive systems must protect quality, worker safety, lawful time/leave, fair treatment, transparency and appeal. Never reward unsafe speed, skipped inspection or concealed defects.
- Public procurement law is applied only where the entity is legally in scope; private-company internal procurement controls must be labelled as company policy/best practice.
- Irreversible or high-impact production operations, real statutory activation, production data migration, payroll payments, finance posting changes, live deployment and destructive operations require the appropriate explicit human approval.

## 8. Research and reference-source discipline

- Research only to improve or verify the approved product scope.
- Prefer current official primary sources for law and regulation, and peer-reviewed/recognized industry sources for apparel practices.
- Reference GitHub repositories may inform functional patterns; they are not copied wholesale. Inspect exact source and licence before reuse.
- Separate verified fact, interpretation, benchmark, recommendation and unresolved question.
- New ideas outside the approved baseline are recorded as proposals only, not silently added.
- Do not introduce competing technology stacks, new applications, unrelated products or experimental services without a documented need and express approval.

## 9. Project continuity and memory

The canonical continuity sources are this agreement in GitHub, the linked Notion Product Goal page and the Notion Chief Architect Agent Hub. At the beginning of any available project session, ChatGPT should:
1. Identify this project and open the agreement/Notion goal when the connected tools permit.
2. Fetch the current repository branch/head and reconcile it with the last accepted handover.
3. Review the latest approved documents and pending owner decisions.
4. State the current work-package ID and last accepted checkpoint.
5. Continue only within the approved scope.

Important limitation: ChatGPT cannot guarantee that every unrelated new chat, every platform surface, or every external plugin automatically receives or obeys this context. A connected Notion page and repository file are durable references, not a technical lock on the entire account. When this project context is not available in a new session, the agreement must be reloaded or the user must direct the session to these canonical links. ChatGPT must never claim universal cross-platform memory or permissions it cannot verify.

## 10. Change and handover record

Maintain a dated record in this document or a linked decision log for:
- change ID and date;
- requirement/document affected;
- proposal and evidence;
- explicit Product Owner approval reference;
- files/sections changed;
- commit and review evidence;
- tests/release/impact;
- handover acceptance and next work package.

### Initial agreement record — 22 November 2026

- Product Owner requested one-goal-only project discipline, strict adherence to all approved documents/plans, numbered stepwise work, complete handover after each finalized step, and express approval before any change to the agreed baseline.
- This document records that operating instruction. The agreement itself and its links are being added to the canonical repository and Notion project memory.
- Current work-package position: **WP-00 — Governance and baseline control**. The agreement, root agent instructions and README entry have been committed; the **WP-00 handover is submitted and awaiting Product Owner acceptance**.
- Next package: **WP-01 — Requirements and traceability**, held until the Product Owner expressly accepts the WP-00 handover.


## Amendment A-01 — Modern technology integration and agent-native evolution

**Recorded:** 25 November 2026  
**Authority:** Product Owner request to incorporate modern-generation ERP capabilities and open-ended future adaptability into repository rules, Codex agents and Notion.  
**Status:** Approved architecture/governance amendment. Implementation proceeds through existing WP-00–WP-16 sequencing and evidence gates.

### Decision

The ERP must meet both objectives simultaneously:

1. **Modern-generation capability now:** implement the most relevant, production-ready technology capabilities that materially support the approved apparel business scope. Do not deliberately defer mature capabilities just to claim future readiness.
2. **Open-ended adaptability:** provide stable extension mechanisms for technologies, devices, applications, agents, workflows and business practices that are not known today, without unnecessary reconstruction of the ERP core.

This amendment does not add unrelated product domains, change the initiative-to-sell-through goal, or reorder the approved work packages.

### Scope and implementation boundary

- The approved garment business operating-system scope, current stack and work-package sequence remain authoritative.
- Current capabilities may include conversational and multimodal interaction, authorized agent tool execution, permission-aware knowledge retrieval, document understanding, workflow orchestration, event-driven integration, analytics, forecasting/optimization where data supports it, device adapters, API/MCP integrations, evaluation, observability and AI-assisted software engineering.
- Adopt mature and useful capabilities now; pilot emerging capabilities with bounded evidence; keep unknown future categories possible through generic extension contracts rather than speculative placeholder modules.
- Operational ERP Agents act on business records through authorized Laravel application services. Codex Evolution Agents operate in developer workspaces on source code. They are separate planes and never share unrestricted permissions.
- The capability registry describes installed/approved tools; it is not a list of guessed future products or technologies.

### Mandatory integration record

For every new agent, tool, model, application, device, SDK, data source, automation or workflow, document:
1. Business outcome, domain owner, approved workflow and measurable acceptance result.
2. Vendor/source/version, official documentation, licence, support lifecycle and security posture.
3. Integration pattern and compatibility with the approved platform stack.
4. Versioned API/event/tool schema, error semantics and compatibility.
5. Tenant, legal entity, site/branch, user/role scope, data classification and retention.
6. Transaction boundary, idempotency, duplicates, retries, timeout, rate limit and reconciliation.
7. Risk tier, human approval, reversibility/compensation, audit and evidence.
8. Tests, threat model, monitoring, cost/latency, outage fallback and disable/rollback.
9. Enablement/feature flag, migrations, support owner and documentation.
10. Explicit decision status and approval reference; proposals must not be described as approved.

### Autonomy and safety

Laravel domain services and PostgreSQL constraints remain authoritative for inventory, sales, orders, finance, payroll, HR and other business records. Agent-generated recommendations/drafts are not committed transactions. Model tools cannot approve themselves. High-impact or irreversible operations remain subject to existing human/professional controls. AI-generated source changes are developed in isolated branches, tested and reviewed; deployment follows the protected release process.

### Codex Free-plan compatibility

The development workflow must work locally with Codex CLI or a supported IDE workflow available to the user's account and current usage limits. Codex Cloud is optional, not a prerequisite. If multi-agent concurrency is unavailable or usage is limited, the coordinator executes the same roles sequentially. Agent TOML does not create an independent security boundary; parent-session sandbox/approval settings and repository permissions control access.

### Change record

- A-01 — 25 November 2026 — Product Owner requested the current-generation and future-adaptive technology direction be incorporated in strict project rules, repository documentation, Notion and Codex agents. The amendment preserves fixed product scope and WP sequence.
- Canonical standards: `docs/architecture/agent-native-technology-integration-standard.md` and `docs/agent/codex-agent-operating-model.md`.
