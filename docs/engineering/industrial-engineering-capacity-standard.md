# WP-06 — Industrial Engineering, Method/Time Study, SAM/SMV, Capacity and Pre-Production

**Package:** WP-06  
**Status:** DESIGN COMPLETED — executable engineering follows in the implementation phase  
**Scope:** Approved Domain D / apparel operating-system charter. Consumes approved PLM release from WP-05 and returns method, capacity, routing inputs and readiness decisions to WP-07–WP-10.

## 1. Purpose and control principle

Industrial Engineering (IE) is the engineering system that turns product construction into a measurable, safe, feasible production method. It defines operation content, methods, equipment, skill needs, standard minutes and capacity. It does not set arbitrary worker quotas or measure speed without context.

The system must show the source and history of every SAM/SMV used in costing, line balancing, capacity planning and production target calculations. Standards are effective-dated, method-specific and approved. A target or efficiency score must not be computed against an obsolete or unapproved standard without an explicit controlled exception.

## 2. IE lifecycle

Approved style/tech pack → operation breakdown → method analysis → time study → allowance/standard calculation → method/standard review → approved operation bulletin → resource and skill requirements → line/cell balancing and capacity simulation → pre-production readiness → released standard for planning and costing.

Lifecycle states:
DRAFT → METHOD_REVIEW → TIME_STUDY → ENGINEERING_REVIEW → APPROVED → EFFECTIVE → SUPERSEDED.
Alternative states: REJECTED, WITHDRAWN, REWORK_REQUIRED. Revisions are immutable after approval; a new method or material change requires a new study or a documented engineering-change bridge.

## 3. Operation bulletin and method engineering

The operation bulletin decomposes the style into elemental operations that are observable, assignable and measurable. Each operation includes:
- Stable operation code and description.
- Product/style revision, route revision and predecessor/successor relationships.
- Method sheet and standard work sequence.
- Machine type, attachments, gauge, folder, needle/thread or other tool requirement.
- Skill/certification level and restricted-task authorization.
- Work centre/department, ergonomic/posture and safety risk.
- Quality characteristic, measurement/check point and defect risk.
- Standard work content and approved SAM/SMV with source study.
- Operation-specific material/trim or information requirement.
- Special process, external/subcontract flag, queue and expected transfer.
- Standard conditions, allowances, work environment and effective dates.

Method study identifies the safest repeatable way to achieve the specified quality. It records current method, proposed method, motion/handling, layout, machine/attachment, operator feedback, ergonomic and safety assessment, quality risk, expected cycle-time change and approval. A method change is not accepted solely because it reduces time; compare quality, worker risk, WIP, skill accessibility, machine availability and maintenance implications.

## 4. Time study and standard-minute governance

### 4.1 Observation record

For each study:
- Style/product revision, operation and method revision.
- Work centre, machine/attachment, trained operator group and shift context.
- Date, assessor, observed cycle identifiers and observation method.
- Element breakdown, observed times and exclusions with reasons.
- Abnormal interruptions (material shortage, machine fault, learning, instruction change) separately reason-coded.
- Performance-rating method and evidence/assessor.
- Personal/fatigue/delay or process allowances and source policy.
- Sample sufficiency, outlier handling, confidence and review.
- Standard calculation, precision, rounding, approver and effectivity.

Do not discard inconvenient observations without a traceable exclusion reason. The study should show the observed distribution and decision, not only a single total.

### 4.2 Calculation policy

Where a recognized time-study method is configured, retain its exact formula and definitions. A conventional direct-time method can be expressed as:

- Observed cycle time = valid element time observations per cycle.
- Normal time = observed time × approved performance rating factor.
- Standard time = normal time × (1 + allowance factor), where the policy defines allowance as a proportion of normal time.

Some industrial-engineering practices use standard-time divisor form when allowance is a fraction of standard time. The system must store the selected formula/version and never mix these definitions. The tenant IE authority chooses the adopted formula, allowance basis and rounding rule; the product must not silently hard-code one industry percentage.

SAM is the approved standard time in minutes for the specified operation/style/method and may be the sum of approved operation standards for a completed garment under a defined scope. SMV/SAM terminology and treatment must be configured consistently. A style-level total is not added again on top of its operation-level earned minutes.

### 4.3 Standard approval and effectivity

Approval requires:
- Complete observations and method evidence.
- Approved performance rating/allowance policy.
- Qualified IE review.
- Machine/skill and quality validation.
- Effective date, source study and approver.
- Impact assessment for costing, existing range commitments and open orders.

A method, operation or material change may invalidate a previous standard. New production orders use the currently effective approved standard; existing orders remain pinned unless re-baselined by an authorized change. Historical efficiency and payroll calculations retain the standard version used at the time.

## 5. Capacity and resource model

Capacity is constrained by the actual resource combination, not just factory headcount:
- Facility/site and department/work centre.
- Line/cell and shift calendar.
- Direct labour availability, approved skills, leave, training and attendance.
- Machine/attachment capacity and maintenance status.
- Operation standard and precedence constraints.
- Material/trim readiness, quality holds, planned changeovers and product route.
- Outsourced/subcontract process capacity and transport lead time.
- Learning curve and ramp-up for new style/line.
- Planned breaks, legal working-time limits and overtime approvals.

Availability and performance are separate. A machine down for maintenance is not available capacity; a worker in training is not a fully qualified direct operator; a shortage at an upstream process can starve a downstream line even when nominal labour exists.

Capacity scenarios show assumptions, feasible output, bottleneck, utilization, risk range, unresolved constraints and required actions. A forecast is not a production authorization. The factory manager/production planner approves the selected plan within delegation.

## 6. Line balancing and operation assignment

For a candidate line:
1. Select the released style/route and operation bulletin revision.
2. Calculate total work content and operation precedence.
3. Select work centres, machine types, available operators and verified skill levels.
4. Assign operations subject to cycle time, machine capability, ergonomic/safety restrictions and quality checks.
5. Calculate station load and expected balance efficiency.
6. Simulate line output, bottleneck, WIP accumulation and buffer.
7. Compare alternative line sizes, multi-skilling and training plan.
8. Submit a recommended balance for IE and factory approval.
9. Record actual result during pilot/early production and improve through controlled method revision.

The optimizer may recommend an assignment; it cannot certify a worker, waive a restricted skill, assign unsafe work, or silently change the approved method.

## 7. Transparent production target and earned-minute definitions

For a simple single-style line/shift, initial target formula:

**Daily target pieces = available direct-labour minutes × approved target efficiency ÷ approved style SAM per piece.**

Every input is versioned: scheduled minutes, breaks, eligible direct headcount/time, attendance, target-efficiency assumption, released SAM, shift calendar, machine/skill capacity, material readiness, style learning curve, changeover and quality hold.

This is a planning recommendation, not an employee pay formula or mandatory quota. Target changes require reason: absence, material shortage, equipment downtime, quality stop, route/method change, approved training, changeover or capacity constraint.

For a single-style completed-garment line:
**Earned standard minutes = accepted good garments × approved style SAM.**

For multi-style or operation-level work, earned standard minutes are calculated from the relevant operation bulletin and accepted operation quantity. Never add the style total again to operation-level minutes. The KPI definition must state output scope, accepted quantity, labour denominator and any excluded time. Compare line/individual efficiency only within a comparable method, operation, skill and working context.

## 8. Pre-production readiness gate

Bulk release must consider:
- Approved style/tech pack, pattern, grade and BOM revision.
- Approved method, operation bulletin, SAM/SMV and process route.
- Fabric/trim/chemical specification, supplier status and material availability.
- Pattern marker/cutting feasibility and material yield.
- Machine/attachment/maintenance readiness and line balance.
- Qualified staffing/skill coverage and safety/ergonomic assessment.
- Sample/fit/size set, first-piece and critical quality plans.
- Special-process supplier/recipe/permit/capacity evidence.
- Packaging/label/care requirement and customer/channel specification.
- Production dates, capacity, delivery risk and open engineering change.

Status: READY, READY_WITH_CONDITIONS, NOT_READY, HOLD_FOR_REVIEW. Missing critical evidence blocks bulk start; conditional approval must list condition, owner, due date, compensating control and expiry. A missing statutory/environmental permission is a legal hold and cannot be waived by ordinary production approval.

## 9. Data model

| Entity/table | Purpose / key fields |
|---|---|
| operation_bulletins | tenant, style_revision, route_ref, version, status, approved_by, effective period |
| operation_bulletin_lines | operation code, sequence, description, predecessor, work centre, machine, skill, quality point, standard ref |
| method_sheets | operation, method revision, work sequence, tools/attachments, safety/ergonomic assessment, approver |
| time_studies | operation/method revision, assessor, date, context, formula version, allowance policy, sample assessment |
| time_observations | study, cycle, element, time, rating, validity, exclusion reason, evidence ref |
| time_study_summaries | study, observed distribution, normal time, standard time, confidence, calculation hash |
| sam_smv_standards | style/operation/method ref, minutes, unit scope, version, approval, effective period |
| ie_policies | tenant, time formula, allowance basis, rounding, terminology, policy version |
| work_centres | site, department, capability, calendar, capacity units, status |
| machines | work centre, machine type, attachments, rate/capacity, maintenance state, certification |
| shifts | site, calendar, planned minutes, breaks, workdays and time zone |
| resource_calendars | worker/machine/work centre, availability, leave/training/maintenance, valid period |
| capacity_scenarios | style/route, volume, dates, resources, assumptions, risk and result |
| line_balance_scenarios | operation loads, precedence, assigned resources, proposed stations, output and bottleneck |
| preproduction_checklists | style/order/revision, requirement, evidence, status, owner and due date |
| readiness_decisions | checklist version, disposition, approver, condition, reason and expiry |

## 10. APIs, events and decision support

Illustrative endpoints:
- POST /api/v1/operation-bulletins; POST /api/v1/method-sheets; POST /api/v1/time-studies.
- POST /api/v1/time-studies/{id}/review; POST /api/v1/sam-standards/{id}/approve.
- POST /api/v1/capacity-scenarios; POST /api/v1/line-balance-scenarios.
- POST /api/v1/preproduction-checklists/{id}/evaluate; POST /api/v1/readiness-decisions.

Events: OperationBulletinApproved.v1, MethodRevisionApproved.v1, SAMStandardApproved.v1, CapacityRiskDetected.v1, LineBalanceRecommended.v1, PreproductionReadinessChanged.v1.

Decision support includes outlier detection, method comparison, station load imbalance, capacity bottleneck, skill single-point dependency, machine downtime exposure, material readiness and delivery risk. Any recommendation must list inputs, assumptions, calculation version and next action.

## 11. Safety, quality and fairness safeguards

- No standard is adopted from an untrained or unsafe work method.
- No productivity target overrides law, protected break, lawful hours, workplace safety or required quality inspection.
- No worker ranking without context or evidence; analytics is for work-system improvement, staffing and training.
- Actual time, attendance, accepted output and downtime are separate source events.
- An operator can raise method/ergonomic/safety concern without incentive penalty.
- Incentive eligibility and wage calculation are governed by HR/payroll rules and qualified local review, not by the IE module alone.
- Standard changes are visible to affected roles and training acknowledgement is required before restricted task use.

## 12. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-IE-001 | Operation bulletin assembled from released tech pack | Every operation and quality point has method/resource/skill and route linkage |
| AT-IE-002 | Time study includes invalid interruption | Exclusion requires reason; raw observation remains auditable |
| AT-IE-003 | Allowance formula basis changes | New policy version; prior standard and calculations remain reproducible |
| AT-IE-004 | Standard is approved for a future date | Earlier production orders retain previous effective standard |
| AT-IE-005 | Candidate line includes worker without required skill | Recommendation excludes them and explains skill gap |
| AT-IE-006 | Machine capacity is reduced by maintenance | Capacity forecast excludes unavailable minutes |
| AT-IE-007 | Two alternative line balances are simulated | Workload, bottleneck, skill and machine assumptions compare transparently |
| AT-IE-008 | Daily target uses actual eligible labour minutes and style SAM | Formula and every input can be reproduced |
| AT-IE-009 | Line output includes mixed styles/operations | Earned minutes use operation standards without double counting |
| AT-IE-010 | Material or first-piece approval is missing | Pre-production state NOT_READY and bulk release blocked |
| AT-IE-011 | Conditional readiness is granted | Condition has owner, due date, expiry and compensating control |
| AT-IE-012 | Historical shift efficiency is viewed after standard revision | Original SAM version is used for historical result |

## 13. WP-06 handover

**Completed:** method/operation bulletin, time-study governance, SAM/SMV versioning, capacity/resource model, line balancing, production target and earned-minute semantics, readiness gates, APIs/events, data model, safeguard rules and 12 acceptance scenarios.

**Not claimed:** no timed study was performed at a real factory, no actual SAM was established, no line capacity or target has been validated for a customer site, and no engine/test has been implemented by this specification.

**Dependencies:** WP-05 released product/technical pack; WP-03 site/workforce/identity foundation; WP-07 material availability; WP-08 route engine; WP-09 execution; WP-10 planning; WP-14 skills/time/pay policy.

**Decision:** internally accepted under delegated execution. Proceed to WP-07 — Procurement, Supplier Governance and Material Traceability.
