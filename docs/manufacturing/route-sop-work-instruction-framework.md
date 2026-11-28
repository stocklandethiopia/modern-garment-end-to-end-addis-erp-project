# WP-08 — Versioned Department SOP, Work Instructions and Product Route Framework

**Package:** WP-08  
**Status:** DESIGN COMPLETED — engine implementation and department configurations follow in WP-09  
**Scope:** Approved apparel product/process scope. Establishes the configurable process orchestration layer used by manufacturing, quality, material control, workforce, subcontract and packing. This package defines the framework, not one universal garment routing.

## 1. Purpose

A garment's manufacturing route is a controlled, versioned graph of operations and decision gates. Different fabrics, constructions, embellishments, buyer specifications, machine types, finishes, compliance requirements and sourcing models require different sequences.

The system must therefore support:
- Required, optional, conditional, parallel and alternative steps.
- Rework loops with bounded repeat and quality disposition.
- In-house, outsourced and hybrid process ownership.
- Department-specific SOPs and work instructions with skill, machine, material, method and safety requirements.
- Version-controlled handoffs and traceability from route step to execution events.
- Variant-/market-/customer-specific instructions and effective dates.
- Controlled emergency deviation with reason, risk assessment, approver and expiry.

A route is not just a list of departments or a mutable production status. It is a released, executable version whose step eligibility and output evidence are evaluated by the engine.

## 2. Core concepts

| Concept | Meaning | Ownership |
|---|---|---|
| Process family | Reusable operation definition, e.g. spread/cut, sew, print, wash, inspect, press, pack | Manufacturing engineering |
| Route template | Reusable graph of process steps and dependency conditions | Process engineering |
| Route version | Immutable approved template version with effective period | Process engineering |
| Product route assignment | Style/revision and product/order-specific route selection | Product engineering |
| Route instance | Instantiated graph for a production order/lot/batch | Manufacturing execution |
| Route step instance | One executable task/state within a route instance | Manufacturing execution |
| SOP | Approved standard operating procedure for process/department | Department owner + Quality/HSE review |
| Work instruction | Operation-level task instructions, visual aid, control point and revision | Process owner |
| Control plan | Quality characteristic, method, sample frequency, limits and reaction plan | Quality |
| Skill requirement | Required competence/certification for task | IE/HR/training |
| Equipment requirement | Machine, attachment, tooling, capacity and maintenance conditions | IE/Maintenance |
| Material requirement | Allowed component/lot/quantity and readiness | PLM/Materials |
| Deviation | Approved bounded difference from released route/SOP | Change control |
| Rework route | Controlled path for defect correction and re-inspection | Quality/Manufacturing |

## 3. Route graph model

Represent route steps as directed nodes and dependency edges. Node properties:
- Step code/name, process family, department and work centre capability.
- Step type: operation, quality gate, material gate, approval gate, subcontract dispatch/receipt, wait/hold or parallel join.
- Mandatory/optional/conditional flag and versioned condition expression.
- Required inputs and output schema (quantity, UOM, handling unit, evidence, status).
- Predecessor/dependency rule and split/join group.
- Responsible role, authorized skill level, equipment/machine, expected capacity and queue rules.
- Applicable SOP/work instruction/control plan revision.
- Quality, safety, environmental and compliance preconditions.
- Rework/exception path, max loop count and escalation.
- Completion evidence and output acceptance rule.

### Route execution state

DRAFT → VALIDATING → APPROVED → RELEASED → IN_PROGRESS → COMPLETED.
Alternative states: ON_HOLD, BLOCKED, CANCELLED, SUPERSEDED, REWORK_REQUIRED, CLOSED_WITH_DEVIATION.

A route version cannot be edited after approval. New route revisions are created, simulated, reviewed and released with effectivity. Existing production orders keep their pinned route revision unless an authorized change-impact/rework decision is approved.

## 4. Conditional process examples

The engine must evaluate product/order characteristics and selected approved options, not infer routing from department name.

| Product/process condition | Route behaviour |
|---|---|
| Standard woven shirt with no embellishment or wet process | Material readiness → spreading/cutting → bundle → sewing → inline/end-line QC → finishing/press → final QC → pack |
| Style requires embroidery | Approved artwork/strike-off gate → embroidery process before/after cutting/sewing as specified → inspection → downstream |
| Garment requires screen/digital print | Approved artwork, ink/recipe and sample gate → print operation → curing/inspection → next process |
| Denim/garment requires wash or dye | Approved recipe and chemical/HSE/effluent readiness → wash batch → shade/measurement/quality gate → finishing |
| Garment is fully outsourced | PO/custody dispatch → subcontract operation acknowledgement → operation evidence → receipt/inspection → accepted output |
| Only some components need special treatment | Route may split by panel/component group and rejoin through controlled reconciliation |
| Customer requires needle detection or metal control | Mandatory configured detection gate before packing/shipment |
| Product not routed for wet processing | Wash/dye steps are skipped with explicit condition evaluation evidence; no false waiting queue |
| Inspection fails | Controlled hold → defect classification → rework path → reinspection → rejoin at authorized route point |
| Material substitute approved by PLM/Quality | Route instance references approved BOM/spec revision and substitute authorization |

The above are examples only; actual process sequence is configured by style revision, not fixed by product category or a global template.

## 5. SOP and work-instruction lifecycle

SOP lifecycle:
DRAFT → TECHNICAL_REVIEW → QUALITY_REVIEW → HSE/COMPLIANCE_REVIEW (when relevant) → APPROVED → PUBLISHED → SUPERSEDED/RETIRED.

Each SOP stores:
- Process/department, owner and approver roles.
- Purpose/scope, prerequisites, safety and PPE, materials/tools, setup and work steps.
- Visual aids, photos/diagrams/videos, warning/critical-control points.
- Quality characteristics, measurement method, sample frequency and accept/reject criteria.
- Expected output and reconciliation method.
- Defect reaction, stop-work escalation, rework and escalation owner.
- Skills/certification required and practical training acknowledgement.
- Controlled version, effective date, language/translation and read/acknowledgement.
- Applicable customer/product/market/route scope.
- Source standards/authority reference when applicable.

Work instruction is narrower than an SOP: specific operation, machine/setup, work sequence, approved method, safety, quality checkpoint and expected evidence. It must be readable at work centre and accessible in approved language/device mode. A user sees the revision applicable to that order/route/operation; not latest draft.

## 6. Route simulation, validation and approval

Before release, the route designer runs validation:
1. All nodes have a process owner and capability.
2. Graph is acyclic except explicitly declared bounded rework loops.
3. Conditional expressions are deterministic and use approved style/order attributes.
4. Every path from entry to exit is connected and has a valid completion rule.
5. Parallel branches have a join and quantity/genealogy reconciliation.
6. Optional/conditional nodes can be skipped only with an explicit evaluation result.
7. Inputs and outputs are typed and compatible across edges (quantity/UOM, material/handling unit, status).
8. Material requirements resolve to approved BOM/spec/substitute rules.
9. Required SOP, work instruction, control plan, skill, machine and safety evidence are valid for effectivity.
10. Outsourced steps have supplier qualification, custody terms, SLA and receipt/quality gate.
11. Rework path has a clear entry, correction operation, inspection and join/closure disposition.
12. Route capacity/lead-time is feasible or raises a visible risk.
13. Change impact identifies active orders, queues, reservations, training, quality plans and customer obligations.
14. Approvers are independent where segregation rules require.

The engine should produce path coverage, missing capability, circular path, unmapped output, expired instruction, unqualified supplier/worker, missing license/permit, capacity and incomplete gate diagnostics.

## 7. Production-order route instantiation and handoff contract

When production order is released:
- Pin style revision, tech pack, BOM, operation bulletin, route version, quality plans, SOP/instruction versions, skill/equipment rules and customer specs.
- Create route instance and applicable step instances, recording each evaluated condition and selected path.
- Allocate required materials and work centres only after authorized planning/release.
- Publish eligible work to the work queue only when all predecessors and preconditions pass.
- Each operation completion references input handling units, quantity/UOM, accepted/rejected output, operator/skill, equipment, timestamps, method revision, material consumption, quality result, defect/rework and next handoff.
- Parallel outputs are reconciled by explicit join, not by a single order-level status flag.
- Subcontract dispatch transfers custody; receipt records actual quantity and QC before route can advance.
- Skipped steps record “not applicable” and condition evidence, not “completed” with invented output.

## 8. Deviation, rework and route change control

Deviation request includes affected style/order/route step, current revision, proposed alternative, reason, duration/quantity, quality/safety/environment/legal risk, customer/brand effect, material and cost impact, approvers and expiry. Possible decisions: approve bounded temporary deviation, reject, request rework/new route revision or use-as-is with documented quality disposition. Legal requirements, HSE red lines and customer mandatory controls cannot be waived by a normal supervisor.

Route change on active production:
- Compare old/new graph and operation content.
- Identify completed/in-progress/queued steps and inventory/WIP.
- Decide grandfather, finish old route, rework, transfer or stop.
- Preserve historical operations against old route revision.
- Train/acknowledge revised SOP before affected operations resume.
- Recalculate capacity and material reservations using approved engineering versions.
- Notify Quality, IE, Materials, Planning and downstream warehouse/customer teams as required.

## 9. Data model

| Entity/table | Purpose / minimum fields |
|---|---|
| process_families | tenant, code, department, process domain, capability and owner |
| process_definitions | family, process code, input/output schema, standard fields, quality and risk profile |
| route_templates | tenant, code, product scope, owner, template state |
| route_versions | template, version, effective period, approval, checksum, released_by |
| route_steps | route version, step code/type, process ref, mandatory flag, condition expression, owner, capability |
| route_edges | route version, predecessor/successor, edge condition, split/join group |
| route_step_requirements | step, material/equipment/skill/SOP/control-plan/license/permit requirement |
| sop_documents | process, owner, scope, revision, language, status, source/attachments |
| work_instructions | SOP/operation ref, revision, machine setup, method, visual aid, warnings |
| control_plans | process/style revision, characteristic, sample method/frequency, limits and reaction |
| sop_acknowledgements | user/worker, SOP revision, training/acknowledgement date, assessment result |
| route_assignments | style/order scope, selected route version, effective period, approval |
| route_instances | production order/lot, pinned product and route revisions, state |
| route_step_instances | route instance, step definition, evaluated condition, state, assigned work centre/owner |
| route_step_events | start/finish/hold/skip/rework, actor, evidence, input/output quantities |
| route_deviations | request, risk, proposed route delta, quantity/time scope, decision, expiry |
| rework_routes | defect class, allowed corrective path, max iterations, reinspection and disposition |
| process_capabilities | site/department/work centre, process family, active period, resource evidence |
| route_change_impacts | affected orders/WIP/material/SOP/training/quality plans and action owners |

## 10. API, events and observability

Illustrative endpoints:
- POST /api/v1/process-families and /api/v1/process-definitions.
- POST /api/v1/route-templates and /api/v1/route-templates/{id}/versions.
- POST /api/v1/route-versions/{id}/validate; POST /api/v1/route-versions/{id}/simulate; POST /api/v1/route-versions/{id}/release.
- POST /api/v1/production-orders/{id}/instantiate-route.
- POST /api/v1/route-step-instances/{id}/start|complete|hold|skip|request-rework.
- POST /api/v1/route-deviations; POST /api/v1/route-change-impacts.

Events: SOPRevisionPublished.v1, RouteVersionApproved.v1, RouteValidationFailed.v1, RouteInstantiated.v1, RouteStepEligible.v1, RouteStepCompleted.v1, RouteStepSkipped.v1, RouteDeviationApproved.v1 and RouteReworkRequested.v1.

Execution dashboards show queue eligibility, blocked preconditions, aging WIP, missing skills/materials, subcontract custody, overdue SOP acknowledgement, quality hold, and route version. Every blocked step must state which requirement failed and who owns the next action.

## 11. Security, safety and authorization

- Only authorized process engineers can author routes; department/quality/HSE/IE owners approve their respective content.
- Only released/effective revisions are available for production execution.
- Worker access is limited to instructions/operations assigned or authorized for their competence.
- A worker may pause/report unsafe or defective conditions without unauthorized penalty.
- Deviation and release approvals apply maker-checker and customer/legal hold rules.
- Safety/permit holds cannot be skipped by ordinary route overrides.
- Route definition, condition evaluation, SOP acknowledgement and step execution have immutable audit evidence.
- External subcontractor portal access is tenant-, supplier-, contract- and work-order-scoped with expiring credentials and restricted records.

## 12. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-ROUTE-001 | Standard route omits wet processing | Conditional wet-process steps skip with evidence and no false WIP |
| AT-ROUTE-002 | Route requires embroidery and strike-off | Bulk route cannot start until artwork/strike-off is approved |
| AT-ROUTE-003 | Parallel component processing is configured | Both branches reconcile quantity and genealogy at join |
| AT-ROUTE-004 | Route graph has an unbounded cycle | Validation rejects route |
| AT-ROUTE-005 | Rework loop is within max attempts | Rework executes and re-inspection required before rejoin |
| AT-ROUTE-006 | Rework attempts exceed max | Escalates to quality disposition; no infinite loop |
| AT-ROUTE-007 | SOP has expired or unacknowledged critical revision | Step remains blocked until valid instruction/training state |
| AT-ROUTE-008 | Worker lacks required skill or machine unavailable | Work is not dispatched; reason and action owner shown |
| AT-ROUTE-009 | Subcontract route lacks qualified supplier/custody terms | Route validation/release fails |
| AT-ROUTE-010 | Required facility/chemical permit is unresolved | Wet-process action held, no ordinary override |
| AT-ROUTE-011 | New route version released while order is in progress | Existing route remains pinned; controlled impact plan required |
| AT-ROUTE-012 | User tries to skip mandatory QC step | Server rejects and audits |
| AT-ROUTE-013 | Conditional node evaluates false | Recorded NOT_APPLICABLE result, not invented output |
| AT-ROUTE-014 | Route step completion command is retried | Idempotent result with one execution effect |

## 13. WP-08 handover

**Completed:** route graph/state model, SOP and work-instruction governance, product-specific optional/conditional/parallel/outsource routing, simulation and validation rules, route instantiation, deviation/rework/change control, data model, API/events, security/safety and 14 acceptance scenarios.

**Not claimed:** no customer SOP was approved, no physical route was time-studied, no execution engine/API/migration/UI/test has been implemented by this design package.

**Dependencies:** WP-03 entity/access/audit; WP-05 released tech pack/BOM; WP-06 approved method/SAM/capability; WP-07 suppliers/material; WP-09 execution; WP-10 planning; WP-11 quality and HSE.

**Decision:** internally accepted under delegated execution. Proceed to WP-09 — Departmental Manufacturing Execution.
