# WP-14 — HR, Workforce Lifecycle, Time, Payroll, Pension, Benefits and Compensation

**Package:** WP-14  
**Status:** DESIGN COMPLETED — statutory parameter activation, employee policy and payments remain locally approved operations  
**Scope:** Approved Domain K, payroll and variable compensation memo, IE skill matrix, safety and legal-source register.

## 1. Purpose and people operating model

The people domain manages the full employment lifecycle and the capability/safety of the workforce while protecting privacy and employment rights. It must connect employee assignment, attendance, skill, factory operation, productivity evidence, time, payroll and statutory reporting without turning the factory performance module into an employee-pay calculator.

One person may be an employee, customer contact, supplier representative or creator under separate roles/relationships. A login is not an employee record. A reseller, creator, subcontract factory, agency, contractor or supplier must not be classified as payroll merely because money is paid to them. Classification follows the real relationship, contract, control, facts and current local law.

## 2. Workforce lifecycle

Recruitment/request → workforce planning and headcount approval → candidate/application → lawful screening and selection → offer → employment contract → identity/qualification verification → onboarding and orientation → entity/site/department assignment → time/attendance/roster → training/competency → performance/development and worker voice → leave/benefit → transfer/promotion/disciplinary process with due process → termination/retirement/exit → final pay, clearance, property return and record retention.

Every status change records authorized HR actor, legal entity, work site, effective date, supporting document, reason, approvals and employee notification. Past contracts/assignments remain historically queryable; corrections create an auditable amendment.

## 3. Person, employment and organizational assignment

Separate:
- Person identity/profile (minimal required personal data).
- User/login and tenant membership.
- Employment relationship and contract.
- Legal employer/entity and registered work site.
- Position, job family, department, supervisor and grade.
- Shift/line/team and operational assignment.
- Competence, training, medical/safety restriction where appropriately scoped.
- Statutory worker classification and evidence.
- Employee bank/payee details and payroll profile.

A person can have multiple sequential or explicitly permitted concurrent assignments. Payroll belongs to the legal employer and valid payroll period. A worker's assignment at a branch/store is not a stock or legal entity ownership record. A subcontractor's workers do not become employees of the hiring company by default.

### Employment and contract controls

Store contract type, start/end, probation where legally permitted, terms, pay basis, work location, working hours/roster, job duties, benefits, confidentiality/IP where valid, applicable policy and signature evidence. HR reviews contract changes, renewals, transfer and termination in line with current law. Do not invent a universal contract or assume every worker has the same status.

## 4. Workforce planning and shift/attendance

### Workforce demand
Plan direct and indirect headcount by facility, department, shift, operation, skill, production plan and season. Reconcile required labour with authorized headcount, roster, training, leave, attendance, legal working time and overtime authorization. This is integrated with WP-06/WP-10 capacity planning.

### Time and attendance
Support authorized sources: biometric/terminal, card, QR, supervisor correction, approved time sheet or integration. Every event identifies source/device, worker, site, event time, timezone, correction history, sync/confidence and reason. Sensitive biometric templates are not copied into ordinary ERP tables; store only necessary reference/processing evidence and apply privacy counsel controls.

Attendance event types include clock-in/out, break, shift transfer, leave, absence, travel, training, overtime start/end and approved correction. A raw time event is distinct from payable time; HR rules compute paid time only after shift/leave/overtime approval. Duplicate, out-of-order, missing and offline events enter exception queues, not silent payroll assumptions.

### Working-time controls
Tenant policies are configured from current Labour Proclamation and later directives, region, sector and approved contracts. System checks daily/weekly limits, protected breaks, rest periods, overtime request/approval, holiday work, night/shift and leave. Unknown legal calculation or exception remains review-required. Production target does not override lawful working-time limits.

## 5. Leave, benefits and worker services

Support annual leave, sick leave, maternity/paternity or other statutory/contractual leave, public holidays, emergency leave, unpaid leave, training leave and other tenant-configured types. Each type has:
- Source policy/law and applicability.
- Accrual/eligibility basis, balance, carryover and expiry.
- Request, supporting evidence (restricted as needed), approval/denial and appeal.
- Roster/shift coverage and payroll treatment.
- Effective date and historical balance.
- Leave return/fitness-to-work or accommodation process where applicable.

Benefits may include meal/transport, medical/insurance, provident/pension, housing, uniform/PPE, training, loan/advance and other lawful/contractual entitlements. Each benefit has employer/employee contribution, eligibility, provider, valuation, tax/pension treatment and source policy; statutory treatment is locally reviewed.

## 6. Payroll architecture

Payroll is a controlled accounting process, separate from time capture and production incentive calculation.

### Payroll cycle
1. Open payroll period for legal entity and worker group.
2. Freeze effective employee, contract, salary, assignment and rule versions for the period.
3. Import/validate approved time, attendance, leave, overtime, shift/allowance, piece/output or incentive earning events.
4. Resolve missing/duplicate time and worker classification exceptions.
5. Calculate gross earnings by earning code and effective date.
6. Apply only approved deductions and statutory tax/pension/social contributions according to effective rule versions.
7. Reconcile employer contributions, advances/loans, benefits and other payable liabilities.
8. Run independent calculation and exception checks; compare with prior period and unusual movements.
9. Maker-checker approval by payroll reviewer, HR controller and finance as configured.
10. Publish payslip and employee explanation securely.
11. Create GL journal/liability and payment proposal to WP-13 only after approval.
12. Reconcile payment/bank proof, statutory remittance and filing evidence.
13. Close and lock payroll run; correction uses retro adjustment/next-run process or lawful amendment.

Statuses: DRAFT → INPUT_VALIDATED → CALCULATED → REVIEW_REQUIRED → APPROVED → POSTED → PAYMENT_PENDING → PAID/RECONCILED → CLOSED. A run cannot be approved with unresolved legal classification, unapproved rule version, unexplained variance or required missing input.

### Earnings and deductions
- Base salary/wage.
- Shift/night/holiday and lawful overtime (only approved).
- Piece/output/team/line incentive as approved policy.
- Store/wholesale commercial employee commission.
- Allowances and benefits in kind where tax treatment applies.
- Bonuses, leave encashment and termination/final settlement.
- Statutory employee/employer contributions.
- Authorized loan/advance/other contractual deductions with legal basis and caps.
- Tax withholding and pension/social security contributions.

Every line stores amount, currency, basis, period, earning/deduction code, formula version, source event and approval. Never hide calculations inside a spreadsheet import.

## 7. Ethiopia statutory rule handling

All legal parameters are effective-dated and source-linked by WP-02. No rate is hard-coded in service logic. Rule activation is gated by exact instrument/clauses, applicability, effective and transition dates, local professional approval and independent test vectors.

### Employment income tax
Proclamation 1395/2025 Article 11 publishes monthly marginal bands, but the table alone is not a complete PAYE algorithm. The Ministry of Revenue Technical Notes to Proclamation 979/2016 describe Article 10 as taxing total monthly employment income without deduction for expenditure incurred in deriving it; those Notes are expressly non-binding. Whether mandatory employee pension is deductible from the PAYE base remains a clause-specific question for qualified Ethiopian tax advice. Preserve the two candidate calculations but do not activate either method before a signed opinion approves the exact method.

### Private organization pension
Proclamation 1268/2022 provides the 45-day employee definition, but Article 3 limits coverage by worker/employer categories and exceptions. Article 2(7) defines pension salary by monthly salary for regular working hours before deductions; Article 10 provides employer 11% and employee 7%; Article 6 covers registration changes and Article 12 monthly remittance within 30 days. Do not infer coverage from payroll status, nationality, job title or contract label. Capture the eligibility facts and independently reviewed decision; obtain full Directives 1085/2025, 1086/2025 and 1087/2025 and POESSA/legal review before activation.

### Labour and employment
Labour Proclamation 1156/2019 and subsequent directives, labour authority guidance, regional/city rules, contracts and actual worker relationship define working time, overtime, leave, wages, termination, dispute, OSH and related controls. Legal rule catalogue must retain source and reviewer. The localized requirements matrix records the provisions identified for Article 59–61, 66–68, 75–77, 85–86 and 88, alongside final wage and severance controls; exact clause applicability, current amendments and worker-category exceptions remain a qualified review gate.

### Ethiopia payroll runtime and release status
The current code candidate uses Laravel server-side calculation, a frozen source snapshot, controlled private legal documents and SHA-256 re-verification. It retains both ETB 30,000 PAYE methods, exemption evidence, non-standard pensionable salary rationale and the `RETAINER_SERVICE` exclusion. It does not use a legacy product identifier, old sample database schema or browser-side tax calculation. Both production flags remain default-off pending employee master/contract/assignment resolution and professional legal activation.

See `docs/localization/ethiopia-payroll-localization-2026-11-27.md`, `docs/localization/ethiopia-payroll-engineering-release-gate.md`, and `docs/localization/ethiopia-payroll-legal-requirements-matrix.csv`.

### Creator / reseller / supplier / subcontractor classification
Differentiate:
- Employee: actual employment relationship and payroll.
- Independent creator/brand ambassador: campaign, sponsorship, usage rights/affiliate and in-kind consideration; tax classification as business/Schedule D/other reviewed per Proclamation 1395/2025 and Regulation 597/2026.
- Reseller/dealer/wholesaler: commercial discount/rebate/claim or service contract to customer/vendor ledger, not employee payroll by default.
- Supplier/agency/freelancer: accepted deliverable/service, procurement/AP and applicable tax evidence.
- Subcontract factory: supplier contract, material custody, accepted output and AP settlement.
- Intermediary/platform: reporting/payment facilitator obligations where law applies.

Where actual relationship or Article 22(3) digital creator business test is ambiguous, mark REVIEW REQUIRED and route to qualified legal/tax reviewer. A payment label cannot determine legal status.

## 8. Production incentives, sales commissions and fair compensation

Manufacturing incentive is separate from store/wholesale commission:
- Manufacturing line/team/individual earning event derives from approved style/operation SAM, available labour time and accepted quantity; quality, rework, safety, attendance, compliance and teamwork policies apply.
- Own-store employee commission may be based on eligible net sales, margin/service, stock accuracy and returns/void reversals.
- Wholesale/key-account employee commission may use policy trigger such as eligible delivery, collection or realized margin and must account for credit note, return, discount and bad debt.
- Manager bonus requires approved role-specific KPI and balanced controls.
- Reseller/dealer discount/rebate is a commercial account term.
- Creator/campaign affiliate, sponsorship, royalty/rights and barter are contractual/AP/creator payment types, not payroll by default.

Every scheme has formula, examples, eligibility, source events, caps, exceptions, policy revision, approval, employee visibility, payslip breakdown, dispute/appeal and legal review. No system may incentivize unsafe speed, unauthorized overtime, defect concealment, skipped inspection, denial of rest/leave or discriminatory ranking.

## 9. Skill, training and competency

WP-10 uses worker competencies; HR owns the official training/qualification record. Track:
- Competency code, operation/machine/site scope and rubric.
- Training plan, trainer, materials and attendance.
- Practical assessment, evidence, assessor and result.
- Provisional/restricted/qualified state, expiry and requalification.
- Required health/safety or external certification only as lawfully allowed.
- Cross-training and development plan.
- Training effectiveness and worker feedback.

Attendance in training is not proof of competence. Only passed practical assessment/authorization makes worker eligible for restricted operation. Skill data is not an opaque disciplinary ranking.

## 10. HSE, grievance, worker voice and privacy

- Worker safety role/competence, PPE training/issue and incident exposure integrate with WP-11.
- Confidential grievance, harassment, discrimination, wage complaint and whistleblowing cases use restricted case team and immutable audit.
- Worker suggestions, daily team meetings, consultation and improvement actions remain accessible without punitive ranking.
- Health data, body/biometric, family, identity, bank and grievance records are purpose-limited, encrypted/masked, access logged and retained only under lawful schedule/legal hold.
- Supervisor and production dashboard receives only minimum operational safety action; no unrelated medical/grievance data is exposed.
- Data subject request and breach response follow the Personal Data Protection Proclamation and current implementation rules after counsel review.

## 11. Data model

| Entity/table | Purpose / key fields |
|---|---|
| persons | minimal identity, classification, privacy and retention references |
| employees | tenant, person, legal employer, employee number, status and lifecycle |
| employment_contracts | employee, contract type, dates, terms, salary basis, signed evidence and revision |
| positions / job_families | job title, grade, role, skill and responsibility |
| assignments | employee, legal entity, site, org unit, manager, shift/team, effective dates |
| employment_events | hire, transfer, promotion, change, leave, exit, approvals and reasons |
| workforce_plans | site/department/shift/skill and headcount demand |
| rosters / shift_assignments | shift time, worker/line assignment, calendar and approval |
| time_devices / time_events | source/device, worker, timestamp, event type, sync/confidence and correction history |
| payable_time_entries | approved hours, earning type, period, rule version and source event |
| leave_policies / leave_balances / leave_requests | effective policy, accrual, balance, evidence, approval and status |
| benefit_plans / benefit_enrolments | benefit type, eligibility, provider, valuation, contribution and effective period |
| payroll_profiles | employee, legal entity, currency, tax/pension classification and bank/payee reference |
| payroll_periods / payroll_runs | entity, period, worker group, status, frozen input version and approvals |
| payroll_inputs | time/earning/deduction, source event, amount, basis and rule version |
| payroll_results / payslip_lines | gross/net, tax/pension/benefit components, calculation hash, period and reviewer |
| statutory_rule_applications | authority/instrument/clause, applicability, rule version, reviewer and activation |
| pension_contributions | employee/employer base/amount, period, rule version and remittance reference |
| compensation_policies | employee scheme, formula, gate, cap, appeal, version and approval |
| compensation_events | source earning (accepted output, sales/collection), eligible amount, reversal/return |
| worker_skills / skill_assessments | competency, evidence, assessor, proficiency and validity |
| training_programmes / training_records | objective, trainer, attendance, assessment, completion and certification |
| hse_training / safety_assignments | safety capability, PPE, restricted work authorization |
| grievance_cases / worker_suggestions | confidential case/suggestion, access policy, owner, status and resolution |
| payroll_approvals / payroll_payments | maker-checker, decision, payment batch, bank evidence and reconciliation |

All tables use tenant/entity keys from WP-03. Health, bank, identity and grievance fields are classified separately and cannot be loaded into broad analytics without minimization and purpose approval.

## 12. APIs, events and segregation

Illustrative endpoints:
- POST /api/v1/employees; POST /api/v1/employees/{id}/contracts; POST /api/v1/assignments.
- POST /api/v1/time-events/import; POST /api/v1/time-corrections; POST /api/v1/leave-requests.
- POST /api/v1/payroll-runs; POST /api/v1/payroll-runs/{id}/calculate|review|approve.
- POST /api/v1/compensation-policies; POST /api/v1/compensation-events/review.
- POST /api/v1/skills/assessments; POST /api/v1/training-records.
- POST /api/v1/grievance-cases (restricted authorization).

Events: EmployeeHired.v1, EmployeeAssigned.v1, TimeEventCaptured.v1, PayableTimeApproved.v1, PayrollCalculated.v1, PayrollApproved.v1, PayrollPosted.v1, PensionRemittanceRecorded.v1, SkillAuthorized.v1, TrainingCompleted.v1 and CompensationAppealOpened.v1.

Payroll approval creates a finance posting/payment proposal, not bank settlement. Bank and statutory portal results are captured as separate evidence and reconciled.

## 13. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-HR-001 | New employee hired to legal entity and site | Valid contract/assignment is required before attendance/payroll |
| AT-HR-002 | Duplicate/out-of-order attendance events received | No double payable time; exception is visible |
| AT-HR-003 | Overtime submitted without authorization | Not silently paid; legal/policy review queue |
| AT-HR-004 | Employment contract changes mid-period | Pay split by effective date and version; prior terms retained |
| AT-HR-005 | Creator paid for campaign/affiliate income | Relationship and Schedule C/D legal review, not default employee payroll |
| AT-HR-006 | Reseller earns agreed volume rebate | Commercial account credit/rebate, not payroll |
| AT-HR-007 | Employee statutory tax rule is not approved | Payroll remains review-required; no automatic tax deduction |
| AT-HR-008 | Pension salary base or coverage is uncertain | Calculation held for local payroll review |
| AT-HR-009 | Approved line incentive includes rejected/rework units | Only eligible accepted output per policy; worker sees breakdown |
| AT-HR-010 | Store commission order is returned/refunded | Eligible net sales commission adjusted under approved policy |
| AT-HR-011 | Employee has expired skill authorization | Restricted operation assignment denied |
| AT-HR-012 | Training attendance recorded without practical assessment | Competency remains unqualified |
| AT-HR-013 | Confidential grievance is requested by production supervisor | Access denied; only authorized action/status shared |
| AT-HR-014 | Payroll run is recalculated after policy revision | Historical frozen rule/inputs preserved; adjustment workflow required |
| AT-HR-015 | Approved payroll run posts to finance | One balanced source-linked payroll journal; no automatic bank payment |
| AT-HR-016 | Exit settlement is calculated | Contract/leave/advance/asset clearance and statutory review evidence retained |

## 14. WP-14 handover

**Design baseline documented; runtime partial:** workforce lifecycle, identity/employment separation, workforce planning/time/attendance, leave/benefits, statutory and external/employee compensation boundaries, skill/training, HSE, grievance/privacy, data ownership and 16 acceptance scenarios. The current payroll calculation candidate is now server-side and source-backed, but complete employee master/contracts/assignments, operational profile resolution, payroll posting subledger, statutory remittance and live legal activation are not yet accepted.

**Not claimed:** no real employee personal data, salary, leave balance, PAYE or pension deduction, bank details, remittance, employment contract or statutory report has been processed. No Ethiopian customer payroll or legal interpretation is activated. The new candidate is a source-controlled, unposted calculation only; both activation flags remain false.

**Dependencies:** WP-02 law and legal holds; WP-03 tenant/identity/privacy; WP-06 capacity; WP-09 operation/output; WP-10 skills/incentive source; WP-11 HSE; WP-13 payroll posting and finance.

**Decision:** internally accepted under delegated execution. Proceed to WP-15 — Analytics, Integrations, AI/Optimization and Control Tower.
