# WP-11 — Quality at Source, Product Compliance, CAPA and HSE

**Package:** WP-11  
**Status:** DESIGN COMPLETED — customer/site-specific limits and runtime implementation remain governed  
**Scope:** Approved Domain I, manufacturing charter and Ethiopia legal-source register. Covers quality from incoming material through shipment, product/process compliance, environmental and worker-safety controls.

## 1. Purpose

Quality is built into the product lifecycle and every manufacturing process. The system must prevent nonconforming material or work from silently progressing, contain defects at their source, route correction/rework and re-inspection, identify root cause, verify corrective action, and release only product lots that satisfy approved specifications.

Quality records are evidence, not arbitrary scores. Defect codes, criticality, AQL/sample plans, measurement tolerance, chemical limits, product safety requirements and certification are specific to customer, product, market, authority, process and effective date. The ERP must not invent universal legal limits or customer acceptance criteria.

HSE is a parallel control system: worker hazard, machine guarding, chemical handling, permits, incident investigation and environmental waste/effluent evidence must not be reduced to a single “compliance passed” flag.

## 2. Quality planning hierarchy

Quality requirements cascade from:
1. Applicable legal/market/product requirements, supported by exact official source and qualified interpretation.
2. Customer/brand contract, specification and approved technical pack.
3. Product risk assessment and control plan.
4. Material/component specifications and supplier qualification.
5. Approved SOP/work instruction and process parameters.
6. Inspection plan, sampling method and measuring equipment.
7. Actual inspection, deviation, defect, rework and release evidence.

Each control links to the applicable style revision, material specification, route step, customer/account, production lot, geography/market and time period. New customer specification creates a revision; historical inspections retain the exact spec and plan used.

## 3. Quality lifecycle and hold disposition

Inspection lifecycle: PLANNED → SAMPLE/INSPECT → PASS / FAIL / CONDITIONAL / HOLD → DISPOSITION → REWORK/REPAIR/SORT/RETURN/SCRAP/USE_AS_IS (authorized) → REINSPECT → RELEASED / REJECTED.

A quality hold identifies:
- Product/material/bundle/operation/batch/carton/shipment and lot genealogy.
- Quantity affected and physical location/status.
- Defect, measurement or requirement failed.
- Discovery point, inspector, date, plan/spec revision and evidence.
- Risk/criticality, affected scope and containment.
- Owner, response due, disposition authority and action.
- Rework route or supplier claim and required reinspection.
- Final accepted/rejected/scrapped/returned quantities and traceable stock/accounting commands.

A hold prevents allocation, production dispatch, packing or shipment depending on the affected object. Release is a controlled command from the authorized quality role after evidence is complete. Production supervisors cannot clear quality holds by ordinary state edit.

## 4. Incoming quality control

Separate incoming inspection profiles by fabric, trim/accessory, chemical, packaging, purchased component and subcontract return. Each plan defines:
- Source specification and revision.
- Supplier qualification and lot/batch requirement.
- Inspection characteristics (e.g. width, GSM, shade, construction, colour, strength, appearance, dimensional tolerance, expiry/SDS/certificate).
- Sampling plan/method, sample size, measuring equipment and calibration.
- Acceptance criteria, defects/criticality, test laboratory and required certification.
- Disposition, supplier claim and re-inspection.
- Scope/effectivity by supplier/site/product/market.

Incoming inspection can result in accepted quantity, conditional/restricted acceptance, quarantined quantity, rejected quantity or additional test. Only released accepted quantity becomes production-eligible. Partial acceptance must split lot balance and maintain identity/genealogy. Failed lots are not made available merely by changing warehouse status.

## 5. First-piece, in-process and end-line controls

### First-piece approval
Before bulk run where required, inspect the first operation/garment against approved tech pack, operation method, measurement/workmanship, machine setup, material and route. Record sample and critical characteristics. If failed, stop affected operation and create correction. An approved waiver must be narrow, reasoned and authorized; legal/customer mandatory control cannot be waived.

### Inline/process inspection
At each configured route control point, capture operation, inspector, sample/units, time, line, operator/work centre, defect, measurement, source lot/bundle and disposition. Control frequency can be time-, quantity-, risk- or event-based. Repeated failure triggers increased inspection or stop/review according to approved plan.

### End-line and process inspection
Check assembly, stitching, components, dimensions, function and routed special-process output. A product not meeting required end-line criteria does not become final-good output by passing a later cosmetic check unless the approved disposition allows it.

## 6. Special-process quality

Printing, embroidery, dye/wash, fusing, heat transfer, coating, pressing and other special processes may require:
- Approved sample/strike-off or pilot run.
- Artwork/recipe/spec version and checksum.
- Equipment identification/calibration and process parameter window.
- Chemical/ink/thread/material batch and expiry.
- Critical measured values and operator competence.
- Environmental/HSE controls and facility permit evidence.
- Post-process shade, placement, adhesion, durability, dimensional stability, colourfastness or other specified tests.

Do not invent a universal recipe or legal chemical threshold. Limits must be linked to exact customer spec, accredited standard, official law/authority source or qualified process approval. A deviation outside parameter window triggers a hold and documented disposition.

## 7. Final inspection, shipment release and product safety

Final inspection is defined by approved customer/product/market sampling plan, inspection level and acceptance criteria. Store:
- Sampling standard/source/version and lot size.
- Sample quantity, selection method and inspector.
- Defect classification and accepted/rejected count.
- Measurement and workmanship result.
- Customer-specific label, care, content, packaging and carton checks.
- Needle/metal detection or product-safety test where required by product/customer/market.
- Required certificate/test report/market documentation and validity.
- Final shipment authorization, decision, approver and attached evidence.

AQL is not a single mandatory number for every garment. The selected plan is customer/product policy or a legally applicable requirement with source and approved version. Failed inspection blocks shipment pending authorized disposition and new reinspection.

## 8. Defect taxonomy and root-cause analysis

Defect catalogue is versioned by product/process/category and may classify:
- Critical/major/minor severity and safety/regulatory/customer implications.
- Source process and detection stage.
- Visual, measurement, construction, material, functional, contamination, shade, print/embroidery, wash/finish, label/packaging or transport defect.
- Reworkability and approved method.
- Containment scope and escalation threshold.

Root-cause methods (5 Why, fishbone, Pareto, process mapping, fault tree or tenant-approved method) are tools, not predetermined conclusions. A root cause must be supported by evidence, reviewed and related to affected operation/material/supplier/method/training/machine. “Operator error” cannot be a default root cause without evidence that method, training, equipment, materials and working conditions were adequate.

## 9. CAPA and effectiveness

CAPA state: OPEN → CONTAINMENT → ROOT_CAUSE → CORRECTIVE_ACTION → PREVENTIVE_ACTION → VERIFICATION → CLOSED. Reopen when recurrence occurs or effectiveness fails.

CAPA records:
- Problem statement, source and scope.
- Affected product/order/lot/route/site/supplier/customer and quantity.
- Immediate containment with due time/owner.
- Root cause, evidence and causal chain.
- Corrective/preventive actions, risk ranking, resource and accountable owner.
- Training/SOP/route/maintenance/supplier or design revision impact.
- Effectiveness measure, observation period, sample/denominator and verifier.
- Residual risk, lessons and recurrence monitoring.

Closing a task checkbox is not CAPA effectiveness. Verification must compare against baseline and ensure no displacement of defects into another process or product.

## 10. HSE, worker safety and environmental controls

### 10.1 Workplace hazard and safety

Provide risk assessment, hazard register, machine guarding/lockout-tagout evidence where applicable, PPE assessment/issue, fire/electrical/ergonomic/chemical controls, emergency preparedness, training/certification, near miss/incident, investigation, corrective action and safe return-to-work/workplace review.

High-risk conditions can trigger stop-work/stop-equipment/route hold with clear release authority. A supervisor or production planner cannot override legally required safety controls to protect a target.

### 10.2 Chemical management

Maintain chemical inventory, hazard classification, SDS revision/language, supplier, batch/expiry, storage compatibility, authorized process/use, training/PPE, issue and return, spill, exposure/incident and waste treatment/disposal. Wet-process and print operations only use chemical lots authorized for that location/process and supported by current safety/environment approvals.

### 10.3 Environment, effluent and waste

For washing, dyeing, printing, finishing, boiler, generator and other applicable processes, capture site permit, environmental management plan, water/energy/chemical consumption where required, effluent/waste monitoring, waste classification, manifests, authorized transporter/disposal site, incident, authority inspection and corrective action. Applicable permit/threshold/limit is location- and process-specific and must be reviewed from official authority source. ERP cannot issue legal permission or certify facility discharge.

### 10.4 Worker incident, health and grievance privacy

Sensitive health, accident, disability and grievance information has restricted access, purpose, retention and export controls. Operational supervisors receive only necessary safety actions; they do not see unrelated medical or confidential grievance details. Worker reporting and appeal paths are protected and audited.

## 11. Compliance obligations and rule evidence

A compliance requirement stores jurisdiction, authority, legal instrument/clause, official source, publication/effective date, affected entity/activity/site/product/worker, interpretation status, reviewer, evidence needed and applicability result. Status can be DISCOVERED, REVIEW_REQUIRED, APPROVED_FOR_CONFIGURATION, ACTIVE, SUPERSEDED or NOT_APPLICABLE.

Legal or product safety rule activation is not automatic when a source document is imported. It requires the WP-02 activation checklist and a qualified professional/authority review. Regional/city permits, textile/product standards, chemical limits, export/customer certification and privacy implementing rules remain scoped to the relevant entity/site/market. Unknown means REVIEW REQUIRED and affected action is blocked where the missing permission/control is essential.

## 12. Data model

| Entity/table | Purpose / minimum fields |
|---|---|
| quality_requirements | source/type, clause/spec, jurisdiction/customer, applicability, effective period, owner/reviewer |
| quality_requirement_versions | immutable rule/spec revision, source checksum, approval and supersession |
| inspection_plans | product/material/process/route scope, sampling plan, characteristics, limits and version |
| inspection_plan_lines | characteristic, method, unit, sample size/frequency, tolerance and reaction |
| inspection_events | inspected object/lot/order/operation, plan revision, inspector, date and result |
| inspection_samples | sample selection, unit/handling unit, measurement, instrument and evidence |
| defect_catalogue | code, severity, type, source process, criticality and version |
| defect_events | object, source stage, defect code, quantity, evidence, detection time |
| quality_holds | scope, quantity, affected status/location, reason, owner, authority and release |
| dispositions | rework/repair/sort/return/scrap/use-as-is, approval, quantity, destination |
| rework_orders | source defect/hold, route, material, owner, quantity and completion |
| reinspection_events | rework link, plan revision, sample/result, inspector and final disposition |
| supplier_quality_claims | supplier/PO/lot, defect, quantity/cost, evidence, response and settlement |
| root_cause_analyses | problem scope, method, causal evidence, verified cause and review |
| capa_cases / capa_actions | containment, corrective/preventive action, owner, due, effectiveness check and closure |
| compliance_sources / compliance_rules | authority, instrument, clause, version, applicability, approval status |
| compliance_applicability | entity/site/activity/product/market, source rule, decision, rationale and reviewer |
| certificates / test_reports | issuer/lab, scope, product/material/site, result, validity, attachment checksum |
| hse_hazards / risk_assessments | hazard, site/process, likelihood, severity, controls and risk acceptor |
| hse_incidents | incident/near miss, affected area, restricted records, investigation and actions |
| chemical_sds / chemical_lots | product/lot, SDS revision, hazard, expiry, storage/use and custody |
| environment_permits / waste_manifests | site/process permit, scope/validity, waste type, quantity, transporter and disposal receipt |
| safety_training_records | worker/role, training/assessment, competence, validity and acknowledgement |

## 13. Integration and events

Quality consumes material receipt, production operation, route, product spec, supplier, worker skill and shipment events. It emits:
- QualityHoldPlaced.v1 / QualityHoldReleased.v1
- InspectionCompleted.v1 / InspectionFailed.v1
- ReworkAuthorized.v1 / ReinspectionPassed.v1
- CAPAOpened.v1 / CAPAEffectivenessFailed.v1 / CAPAClosed.v1
- ComplianceEvidenceExpired.v1 / LegalApplicabilityChanged.v1
- HSEStopWorkRaised.v1 / HSEIncidentRecorded.v1

A quality decision triggers an authorized WMS/manufacturing command; the quality module does not directly change stock quantity or post finance journal. Event consumers are idempotent.

## 14. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-QLT-001 | Incoming fabric lot fails shade/width check | Quarantine prevents production allocation and supplier claim is created |
| AT-QLT-002 | Required first-piece approval is missing | Bulk operation remains held |
| AT-QLT-003 | Inline inspection records repeated seam defect | Defect source is traceable; configured containment/escalation is triggered |
| AT-QLT-004 | Rework is completed but not reinspected | Product remains on quality hold |
| AT-QLT-005 | Final inspection fails customer plan | Shipment release blocked and approved disposition required |
| AT-QLT-006 | Defect recurs after CAPA closure | CAPA reopens or recurrence case is created |
| AT-QLT-007 | Certificate expires before planned shipment | Required compliance release is blocked |
| AT-QLT-008 | Chemical lot lacks valid SDS/permit scope | Chemical issue or process batch is blocked |
| AT-QLT-009 | Worker reports unsafe machine guard | Stop-work and maintenance/HSE escalation routed; production cannot override |
| AT-QLT-010 | Site lacks evidence of wet-process environmental permit | Affected process held; no generic compliance pass |
| AT-QLT-011 | Customer inspection tolerance changes | New plan revision effective-dated; previous inspections remain tied to old spec |
| AT-QLT-012 | Authorized use-as-is deviation is requested | Multi-role approval and exact quantity/scope/evidence required |
| AT-QLT-013 | Privacy-sensitive incident record is requested by unrelated supervisor | Access denied; minimal operational action disclosed |
| AT-QLT-014 | Duplicate inspection event is received | No duplicate release/stock effect; idempotent event recorded |

## 15. WP-11 handover

**Completed:** quality planning hierarchy, incoming/first-piece/inline/process/final inspection, hold/disposition/rework/reinspection, defect/RCA/CAPA, compliance source registry, HSE/chemical/environmental control, data model, events and 14 acceptance tests.

**Not claimed:** no factory inspection plan, customer AQL, product certification, chemical limit, environmental permit, OSH assessment or site legal opinion has been approved. No actual inspection, CAPA, compliance audit, HSE deployment or automated test has been executed by this design package.

**Dependencies:** WP-02 legal-source and applicability holds; WP-03 tenant/security/audit; WP-05 released product specs; WP-07 incoming materials/SDS; WP-08 route; WP-09 production actuals; WP-12 stock disposition/shipment hold; WP-13 tax/financial evidence; WP-14 worker/health/privacy.

**Decision:** internally accepted under delegated execution. Proceed to WP-12 — Warehouse, Distribution and Channel Commerce.
