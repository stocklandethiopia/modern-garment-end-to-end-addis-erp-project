# WP-10 — Production Planning, Line Performance, Skills, Training, Incentives and Continuous Improvement

**Package:** WP-10  
**Status:** DESIGN COMPLETED — implementation and site-specific calibration follow  
**Scope:** Approved Domain H/K and apparel operating-system charter. Connects WP-06 IE standards, WP-09 actual operation events, WP-14 worker/payroll policy, WP-11 quality/HSE and WP-07 material readiness.

## 1. Purpose

Turn approved production orders and engineering standards into a feasible daily/shift/hour plan; monitor actual work at operation and line level; expose constraints; recommend safe recovery action; build multi-skilled capability; and improve the work system.

This is not an employee surveillance/ranking or automatic wage-deduction tool. Productivity measures must be contextual, explainable and used with accepted quality, safety, lawful working time, attendance and upstream constraints.

## 2. Planning hierarchy and control cadence

Planning horizons:
- Strategic capacity: monthly/seasonal factory, facility, product mix, subcontract capacity and headcount.
- Master production schedule: order, style, quantity, delivery window, site and route.
- Material/resource plan: BOM, stock, PO/in-transit, supplier lead time, machine and skill capacity.
- Line loading: style/route, line/cell, shift, start/finish and changeover.
- Daily plan: target by line/shift/style and critical route stage.
- Hourly control: planned and actual good output, earned minutes, WIP, constraints and next action.
- Shift handover: open WIP, shortage/downtime, quality holds, maintenance and next shift readiness.

The control cadence should support pre-shift readiness, first-hour check, hourly review, mid-shift recovery, end-shift reconciliation and daily cross-functional meeting. The exact meeting schedule is tenant/factory configurable, but action owner and due time are required.

## 3. Feasible production plan

A planned line load must account for:
- Released production order and route version.
- Approved style/operation SAM or SMV.
- Operation precedence and bottleneck cycle content.
- Available direct-labour minutes by skill and shift.
- Worker assignment, attendance, leave and training.
- Machine/attachment capacity and maintenance availability.
- Material/trim readiness and expected replenishment time.
- First-piece/sample approval and quality hold.
- Special-process capacity, subcontract lead time and hand-off.
- Planned changeover, style learning curve and line efficiency assumption.
- Delivery date, customer priority, shipment cutoff and buffer.

A plan scenario is not an instruction until it is approved and dispatched. The system should show feasible vs requested volume, unmet constraints and alternative resources/sequence with transparent assumptions.

## 4. Target, output and efficiency definitions

### 4.1 Daily / shift target

For a simple single-style line/shift:

**Target pieces = available direct-labour minutes × approved target efficiency ÷ approved style SAM per piece.**

Available labour minutes must use scheduled direct labour after lawful breaks and planned unavailable time; actual absence and reassignment are reflected with timestamp and reason. The SAM must be the effective approved revision for the style/method/route and unit scope.

The target efficiency is an approved planning assumption for the line/product/learning stage, not a legal entitlement or automatic individual quota. Display every input, version and adjustment. If line target is changed after shift starts, record the reason and preserve original plan.

### 4.2 Accepted output and earned minutes

- Report total pieces processed, good/accepted output, rejects, rework, scrap and WIP separately.
- For a completed-garment single-style line: earned standard minutes = accepted good quantity × approved style SAM.
- For multi-style or operation-level work: earned minutes are computed from accepted operation quantity × approved operation SAM. Do not add a completed-style total again when operation-level earned time is already counted.
- Direct-labour minutes use the explicitly stated worker/time scope. Exclude indirect labour only where policy defines and records it; never silently remove downtime or training.
- Efficiency = earned standard minutes ÷ eligible direct-labour minutes × 100, with numerator/denominator/scope shown.
- Line balancing efficiency, operator efficiency, utilization, attendance and throughput are separate metrics; never label them all “efficiency”.

### 4.3 Loss and constraints

Reason-coded loss includes material shortage, wrong shade/spec, absent skill, machine fault, setup/changeover, quality stop, first-piece delay, method/instruction issue, route dependency, utility interruption, absenteeism, planned maintenance, training, waiting for subcontract/inspection, and approved downtime category. Record source and affected time/operation/order. Do not penalize a worker for a verified upstream constraint.

## 5. Line balance and dispatch

Line plan selects route, operation bulletin, machine and qualified operators. For each station it shows available time, work content, cycle/load, predecessor, quality checkpoint, skill and machine constraint. Recommendations can move work/people, create buffer, split bundle or propose cross-training, but require authorized supervisor/IE approval and cannot violate restricted skill or safety controls.

Dispatch rules:
- Only eligible route steps enter the queue.
- WIP caps and FIFO/priority policy are configured by process.
- Critical orders may receive approved priority but cannot jump quality/permit gates.
- A bundle is not completed by scanning the same quantity twice.
- Work-in-progress is reconciled by operation, handling unit, quantity and location.
- Reassignment records original and new line/operation/worker and reason.

## 6. Skill matrix, certification and training

Skill matrix is a competency evidence system:
- Worker employment/assignment and site context (owned by HR).
- Skill/operation/machine competency taxonomy.
- Proficiency level and observable assessment criteria.
- Training/practice evidence, assessor, date and method.
- Authorization to perform restricted operation/equipment.
- Certification/license (if any), expiry, renewal and suspension.
- Cross-skill pathway, training plan and practical sign-off.
- Worker acknowledgement and appeal/correction path.

Skill status: NOT_ASSESSED, IN_TRAINING, PROVISIONAL, QUALIFIED, RESTRICTED, EXPIRED, SUSPENDED. Assignment is governed by skill authorization and site policy, not a single subjective score. No automatic inference of competence from production count. Workers must receive training before a new method/equipment is assigned where required.

### Skill-based decision support

- Detect critical operations with one qualified worker (single-point dependency).
- Recommend cross-training using demand/capacity gaps.
- Identify mismatch between planned operation and verified skill.
- Show skill coverage by shift/line and backup availability.
- Track training effectiveness using assessment and quality evidence, not attendance alone.
- Keep worker analytics explainable, role-restricted and non-punitive.

## 7. Incentives and compensation governance

Optional line/team/individual incentives are a policy controlled by HR/payroll with IE/quality/finance review. This planning package supplies accepted output and transparent performance evidence; it does not decide employment legal classification or execute payroll.

A policy can combine:
- Accepted output/productivity against approved method/time standard.
- First-pass quality, defect/rework and final acceptance.
- Safety/compliance and adherence to SOP.
- Attendance/time rules only where lawful and approved.
- Team contribution and skill/training/continuous improvement.
- Customer delivery and waste/yield where directly attributable and auditable.

Mandatory safeguards:
1. No reward for unsafe speed, missed inspection, concealed defect, unauthorized overtime or skipped protected break/leave.
2. Reject/scrap/rework units are never counted as accepted output unless policy explicitly defines a separate non-cash learning metric.
3. Quality, safety and compliance gates can cap or suspend incentive eligibility only through an approved transparent policy; no covert withholding.
4. Workers see calculation inputs, rules, result and appeal path.
5. Policy is versioned, effective-dated, reviewed by HR/legal/payroll and communicated.
6. No incentive is automatically posted/paid by production module; approved earning events go to WP-14 payroll after maker-checker.
7. Reseller rebates and influencer/creator fees are not employee incentives. They remain commercial AP/account transactions according to relationship and legal classification.

## 8. Downtime, Andon and recovery workflow

An Andon/downtime report records affected line/work centre/operation/order/bundle, start/end time, reason code, severity, reporter and evidence. It routes to the correct owner:
- Material/trim shortage → store/procurement planner.
- Machine fault → maintenance.
- Quality defect/hold → Quality engineer.
- Method/balance issue → IE.
- Skill/attendance gap → line supervisor/HR training.
- Utility/safety risk → facilities/HSE.
- Route/approval blockage → process/quality owner.

The system can recommend recovery options (rebalance, alternate qualified machine/line, material expedite, approved subcontract, sequence change, overtime request) with estimated effect, cost, quality/safety risk and owner. Consequential actions require their normal approval. No optimization may change legal working hours or bypass quality/permit checks.

## 9. Daily improvement and worker participation

Provide digital/physical board support for:
- Pre-shift plan and readiness.
- Hourly target/actual and reason-coded variance.
- Quality at source and repeated defects.
- 5S audit and workplace safety conditions.
- Worker suggestions and Kaizen events.
- Root-cause and countermeasure actions.
- Maintenance plan/actual and machine availability.
- Before/after benefit validation (minutes, quality, waste, cost, ergonomics, delivery).
- Daily management meeting, owner, due date, escalation and closure.

Improvement closure requires effectiveness evidence after a defined period; closing an action because the checkbox was clicked is not sufficient.

## 10. Data model

| Entity/table | Purpose / key fields |
|---|---|
| production_plans | entity/site, period, version, planner, capacity assumptions, approval |
| production_plan_orders | order, style/route revision, quantity, delivery window, priority |
| line_assignments | order, line/work centre, shift, start/end, balance revision |
| shift_rosters | site/line/shift, workers, approved role/skill, assignment validity |
| production_targets | line/order/style/shift, planned SAM, eligible minutes, efficiency target, target pieces, version |
| hourly_target_snapshots | hour bucket, target, actual, accepted, rejects, rework, WIP, source version |
| operation_output_events | operation, order, bundle, quantity/UOM, disposition, worker, machine, timestamp |
| earned_minute_entries | operation/style SAM ref, accepted quantity, earned minutes, calculation version |
| downtime_events | line/work centre/order, reason, start/end, affected minutes, evidence, owner |
| andon_events | reporter, severity, source event, assigned responder, SLA, resolution |
| skills | skill code, scope, proficiency rubric, restricted status |
| worker_skill_assessments | worker, skill, level, evidence, assessor, date, decision |
| skill_authorizations | worker, operation/machine, site, valid from/to, restrictions |
| training_plans / training_records | objective, competency, trainer, attendance, assessment and signoff |
| line_balance_scenarios | operation loads, machine/worker assignment, balance result, bottleneck, approval |
| incentive_policies | scheme, eligibility, formula, gates, cap, appeal, version and approvals |
| incentive_earning_events | worker/team, accepted production evidence, policy version, calculation and review status |
| kaizen_events / improvement_actions | problem, baseline, root cause, action, owner, due, result, effectiveness |
| maintenance_work_orders | asset, maintenance task, outage, repair, return-to-service, signoff |

## 11. APIs, events and decision support

Illustrative endpoints:
- POST /api/v1/production-plans; POST /api/v1/production-plans/{id}/simulate|approve.
- POST /api/v1/line-assignments; POST /api/v1/production-targets/recalculate.
- POST /api/v1/operation-output-events; POST /api/v1/downtime-events; POST /api/v1/andon-events.
- POST /api/v1/skills/assessments; POST /api/v1/training-plans.
- POST /api/v1/incentive-policies/{id}/versions; POST /api/v1/incentive-earning-events/review.
- POST /api/v1/kaizen-events; POST /api/v1/improvement-actions/{id}/verify.

Events: ProductionPlanApproved.v1, LineAssignmentChanged.v1, HourlyOutputRecorded.v1, ProductionConstraintDetected.v1, SkillAssessmentCompleted.v1, TrainingCompleted.v1, IncentiveEarningProposed.v1, KaizenActionVerified.v1.

## 12. KPI dictionary and safeguards

| KPI | Formula / declared scope | Interpretation / safeguard |
|---|---|---|
| Line efficiency | accepted earned standard minutes / eligible direct-labour minutes | SAM/version and labour scope visible; not individual ranking by default |
| Operation efficiency | accepted operation earned minutes / eligible operation labour minutes | Must use operation-specific SAM and method |
| First-pass yield | units accepted first pass / units inspected | Rework is separately shown; never conceal defects |
| Rework rate | units/operations entering rework / inspected or processed base stated | Denominator explicit |
| WIP | eligible physical quantity at each route step/location | Reconcile to handling-unit scans and inventory events |
| WIP age | elapsed time since accepted route-step entry | Segment planned wait vs overdue queue |
| Downtime | reason-coded unavailable minutes / scheduled resource minutes | Planned maintenance/training separated from unplanned |
| Schedule adherence | milestones completed by approved time / due milestones | Delivery constraints and approved changes visible |
| Skill coverage | critical operations with qualified coverage / critical operations planned | Highlight sole-operator risk |
| Training effectiveness | passed practical assessment and subsequent quality evidence | Attendance alone is not competence |
| Fabric yield | good cut output consumption / eligible fabric input on declared basis | Compare by fabric/marker/lot; no concealment of remnant/scrap |
| Improvement benefit | verified post-change delta against baseline with measurement period | Benefit owner validates, avoid double-counting |

## 13. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-OPS-001 | Shift plan has 10 operators, net minutes, approved SAM and efficiency | Target is reproducible from visible inputs |
| AT-OPS-002 | Mid-shift absence or downtime changes availability | Revised forecast records reason; original plan remains history |
| AT-OPS-003 | Mixed style and operation reporting | Earned minutes use right standard and do not double count |
| AT-OPS-004 | WIP bottleneck develops at operation | Queue/age and owner alert identify actual constraint |
| AT-OPS-005 | Line assignment includes expired skill authorization | Worker cannot be assigned to restricted operation |
| AT-OPS-006 | Critical operation has one qualified worker | Single-point risk and cross-training proposal raised |
| AT-OPS-007 | Andon machine fault persists | Maintenance owner/SLA escalation; line capacity recalculated |
| AT-OPS-008 | Operator reports unsafe method | Stop/escalation recorded; no incentive penalty |
| AT-OPS-009 | Incentive policy rewards raw total output only | Policy validation rejects missing quality/safety/fairness safeguards |
| AT-OPS-010 | Rework/reject quantity is scanned as output | It does not count as accepted earning unless a separate authorized measure exists |
| AT-OPS-011 | Approved incentive earning is calculated | Transparent formula/version and worker appeal shown; payroll not auto-paid |
| AT-OPS-012 | Kaizen action is closed without effectiveness measure | Closure rejected or remains pending verification |

## 14. WP-10 handover

**Completed:** horizon planning, line/shift/hour target semantics, accepted output/earned minutes, loss taxonomy, line balance/dispatch, skill/certification/training, fair incentive boundary, Andon/recovery, daily improvement, KPI dictionary, data model and 12 acceptance scenarios.

**Not claimed:** no worker performance benchmark, factory efficiency target, SAM calibration, incentive rate, wage policy or factory deployment has been approved. No schedule engine, skill assessment tool, payroll integration or automated test is implemented by this design artifact.

**Dependencies:** WP-06 standards/capacity, WP-07 materials, WP-08 route engine, WP-09 actual work events, WP-11 quality/HSE, WP-14 HR/payroll and WP-15 analytics.

**Decision:** internally accepted under delegated execution. Proceed to WP-11 — Quality, Compliance, CAPA and HSE.

## 15. Miret integration — monthly capacity to hourly accepted output

WP-10 is the efficiency pillar of the Miret model; it must consume approved product identity, effective SAM/SMV and route from WP-05/06/08, resource/material readiness from WP-07, actual operation events from WP-09, and quality disposition from WP-11. The planning hierarchy remains monthly/seasonal capacity → MPS → material/resource plan → line loading → daily line/shift plan → hourly control → shift handover. Do not create a second planning sequence or a second definition of efficiency.

The line plan must identify its exact style/revision, order, route and operation standards, line/work-centre, machines/attachments, shift calendar, planned headcount and verified skill coverage, net productive minutes, material/trim availability, maintenance/changeover assumptions, quality gates and delivery cutoff. A planner can compare scenarios; only the authorized owner publishes and dispatches the plan.

The dashboard and agent must preserve requested vs feasible quantity; planned vs actual total and accepted-good output, rejects, rework, scrap and operation WIP; the approved SAM/SMV and target-efficiency version; earned standard minutes and eligible direct-labour minutes with scope; machine downtime and reason-coded material, skill, quality, maintenance and route loss; FPY/defects/rework and any stop/hold; forecast completion, named recovery owner and due time.

The Miret formula, earned-minute definitions and fair-work safeguards are specified in [Miret — Four-Pillar Garment Operating Model](../product/miret-system-operating-model.md). Acceptance must include a monthly-to-daily-to-hourly replay, style changeover, mixed-style load, absent skill, machine failure, material shortage, quality hold and approved re-plan. Do not claim site-calibrated capacity until real factory time-study and resource data are approved.
