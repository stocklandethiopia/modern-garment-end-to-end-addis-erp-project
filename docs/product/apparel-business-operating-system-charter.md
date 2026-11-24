# Apparel Business Operating System — Product Goal and Garment-Expert Design Charter

**Status:** Governing product direction for the Modern Garment End-to-End Addis ERP  
**Decision principle:** Build an apparel business operating system, not a generic ERP with garment fields and not a cut-and-sew-only production tracker.

## 1. The product we are actually building

Build a connected operating environment for apparel and lifestyle businesses that can originate product ideas, create brands and collections, design and develop products in-house, plan and approve samples, source and control materials, manufacture through specialized internal or external departments, distribute finished goods, operate their own branches and stores, sell to wholesalers/retailers/resellers/direct customers, and learn from sell-through and returns.

The business may own factories, outsource some or all processing, purchase finished goods, operate retail stores, supply wholesale markets, or combine these models. The application must not assume the customer is merely a cut-and-sew contractor. It must also not assume manufacturing starts only after a buyer purchase order. An internal initiative, seasonal collection, brand strategy, store/customer insight or product opportunity can start the lifecycle.

Every module we design or implement must remain consistent with this charter.

## 2. Start at the beginning: strategy, initiative, design and idea

The first business questions are: who is the product for, which problem or opportunity does it address, which brand/category/collection does it belong to, where will it be sold, when should it launch, what is the target price and margin, what quantity/assortment is plausible, and can the business develop and supply it?

The ERP must support market/consumer insight, brand portfolio, product initiative intake, trend/research evidence, collection and line planning, assortment architecture, design briefs, moodboards, sketches, technical flats, artwork, concept reviews, price positioning and business-case decisions. Store sell-through, customer feedback, return reasons and product profitability should inform future initiatives and collections.

## 3. Product development is a first-class capability

Product development includes design studio, technical design, patternmaking, grading, material and color selection, trims, tech packs, costing, sample room, fit sessions, approvals, digital/3D visualization, revision management and pre-production handover. It must support proto, fit, size-set, sales/marketing, photo, pre-production, counter and other sample types according to the product and business need.

A 3D garment model must link to its style revision, pattern, fabric/material properties, avatar/fit context, comments and approval record. 3D can reduce iteration and clarify decisions, but it does not automatically replace physical fit evidence where the product risk or customer standard requires it.

No bulk run should start from an unapproved or superseded technical package. The system should prove that the right approved revision, sample, pattern, BOM, route, material standard, cost and quality plan were released to production.

## 4. Manufacturing is a network of specialist processes—not one generic stage

The product's route is configured by style, construction, material, finish, site and customer/brand requirement. A route may include only some of the available processes, and the order of certain operations may vary. Printing and embroidery may occur before cutting, after cutting or after sewing. Washing/dyeing/special treatment may be omitted, in-house or subcontracted. Garment type and recipe determine the real path.

Each department must have its own operating model, SOP, work instructions, resources, inputs, outputs, WIP controls, skills, process parameters, quality gates, records and exception loops. The system must connect these workflows without flattening their important differences.

### Department workflows that must be modeled separately

| Department / process | Distinct control and evidence the ERP must support |
|---|---|
| Product strategy/design studio | Initiative, market insight, range plan, concept brief, technical flats/artwork, design approvals and target commercial outcome |
| Technical design/pattern/sample room | Pattern/grade revisions, fit sessions, measurements/tolerances, sample rounds, comments, fit-model feedback and final sample approvals |
| Material sourcing and incoming control | Supplier/lot identity, fabric roll and shade, testing, inspection, quarantine/acceptance, claims and approved substitutes |
| Fabric issue and cutting room | Roll/lot/shade to order/lay traceability, relaxation, marker version, spreading, ply/lay, cutting, numbering, panel yield, remnants, recuts and bundle tickets |
| Trim/accessory issue | Separate BOM-based issue for thread, buttons, zips, labels, care labels, elastic, interlining, hangtags and packaging; brand/revision/color/size validation and returns |
| Print/embroidery/embellishment | Artwork or stitch-file revision, placement template, screen/thread/ink/recipe, mock-up approval, process parameters, batch result and defect/rework |
| Sewing lines | Operation routing, method bulletin, line layout, machines/attachments, skill-based operator assignment, hourly targets, output, WIP, inline QC, end-line QC and bottleneck actions |
| Washing/dyeing/special wet or dry process | Approved recipe revision, machine/load capacity, chemical issue, temperature/time/other parameters, batch traceability, intermediate tests, shade/hand-feel/shrinkage and environmental/safety controls |
| Finishing/pressing | Thread trimming, stain removal, repair, accessories, measurements, under/final/permanent pressing as applicable, equipment settings, appearance standard and reinspection |
| Packing/cartonization | Fold/pack standard, SKU-size-colour assortment, labels/tags, piece-to-carton-to-pallet/container genealogy, weight/dimension, metal check where required and shipment audit |
| DC/branch/store distribution | Quality-released stock, node allocation, replenishment, transfer, pick/pack/ship, POS, wholesale/retail orders, customer returns and stock/financial reconciliation |

The system must never post a fabric roll issue, trim issue and cut-bundle issue as if they were the same transaction. Cutting normally transfers reconciled cut panels/bundles to the next route node, commonly sewing. Trim/accessory issue remains a separately controlled material movement to the line or operation where required. This preserves correct consumption, shortage responsibility, inventory accuracy and traceability.

## 5. Production planning must guide real daily work

Planning is not merely entering a production order. It must translate confirmed demand and approved product routing into feasible plans by site, department, production line and shift.

A line plan uses style-specific SMV/SAM, operation content, planned headcount and skill coverage, machine/attachment capacity, net shift time, planned breaks, absenteeism, material readiness, changeovers, maintenance, target efficiency and learning curve. A transparent starting target is:

**Daily line target (pieces) = available direct-labour minutes × approved target efficiency ÷ style SMV/SAM per piece.**

The result is a planning recommendation, not a blindly enforced quota. The system must disclose the assumptions and adjust the plan through controlled reasons when the line is constrained by materials, quality holds, bottlenecks, staffing, skill coverage, machine downtime or style learning.

Track plan versus actual by day, shift and hour: output, accepted first-pass quantity, earned standard minutes, attended direct-labour minutes, efficiency, WIP by operation, defects, rework, downtime, lost minutes, material shortage, attendance/skill coverage and projected completion. Give the line supervisor, IE, production manager and quality team different action-oriented views of the same facts.

An individual operator efficiency measure may use earned standard minutes compared with the operator's relevant productive minutes, but it must be contextualized by operation mix, training status, quality, machine conditions and team flow. Do not compare people performing materially different work as if the work were identical.

## 6. People, skill matrices and fair recognition

The people system must link an employee's validated skill/competency profile to actual production operations. A skill matrix should show which employee is qualified for which operation/machine/process, the demonstrated skill level, last assessment, trainer/assessor, certification expiry and training path.

The line planner can use the matrix to:
- recommend the best feasible operator-to-operation assignment while balancing standard work content;
- identify single-point skill dependencies and absenteeism coverage risk;
- propose cross-training and multi-skilling plans;
- track training and supervised sign-off for new styles/methods;
- identify where a bottleneck is caused by capability rather than machine or material constraints.

Daily line incentives and team recognition may be configured by the employer, but must never be an opaque score or race for raw unit count. Use transparent rules, versioned policies, worker visibility, audit, grievance/appeal and payroll approval. Any proposed incentive design should balance accepted output/productivity, first-pass quality, rework, safe work, compliance and teamwork. Block rewards where quality/safety gates fail or where evidence is incomplete. Protect lawful leave, breaks and safe working limits; never incentivize defect concealment, unsafe speed or unauthorized overtime. Incentives are optional policy, not a mandatory software assumption.

## 7. Quality is built into every process

Quality must be designed at source through approved specifications, incoming inspection, material and color/lot control, first-piece checks, in-process and inline checks, end-line inspection, specialized wash/print/embroidery/press checks, final measurement/appearance inspection and shipment audit as applicable.

Every finding records defect taxonomy, severity, location, quantity inspected/affected, containment, responsible process, repair/rework route, reinspected result, root-cause method, CAPA owner, due date and effectiveness check. Use Andon/escalation, stop/hold/release decisions and error-proofing so the next department does not inherit hidden defects.

## 8. Industrial engineering and continuous improvement are native to the ERP

Provide method study, time study, SMV/SAM version control, line balancing, bottleneck analysis, takt/cycle-time review, capacity simulation, WIP limits, Kanban signals, visual boards, style-changeover/SMED, 5S audits, daily team meetings, worker suggestions, Kaizen actions and before/after measurement.

The platform should recommend decisions such as: move an operation to rebalance a line; train additional qualified operators on a single-point skill; adjust the line target based on actual available labor minutes; replenish a missing trim kit; stop release of an unapproved shade lot; investigate repeated seam defects; or reschedule a machine with poor availability. A person remains accountable for high-impact approvals.

## 9. The audit chain is a product capability

The audit chain connects:
- initiative, design brief, style/variant and revision;
- pattern, measurements, 3D model, fit comments and approved sample;
- tech pack, BOM, material approvals, supplier lot and purchase/receipt;
- separate fabric, trim and packaging issues;
- lay/marker, cut panel, bundle, route operation and workstation;
- employee, skill qualification, shift, line and approved method/SMV;
- quality inspection, defect, rework, process recipe and release decision;
- garment/carton/pallet/container, warehouse node, transfer, shipment and customer order;
- POS/wholesale sale, invoice/payment, return, refund/credit and next-season feedback.

Record source reference, tenant/legal entity/site, product revision, quantity/unit, origin/destination, actor/role, event time, status, reason, evidence and linked corrective action. Approved technical revisions, posted stock/finance movements and closed production evidence must not be overwritten; corrections require controlled revisions/reversals with full history.

## 10. The software must deliver business benefits

Each screen and workflow should answer at least one operational question: What is the best next action? What is at risk? What should be planned? What should be stopped? Why is output falling? Which process causes repeat defects? Which material lot is affected? Which employees are qualified? Will we finish on time? Is the range commercially viable? What should be replenished? Which store or channel is performing? What improvement changed the result?

Avoid duplicate data entry. Capture facts once at the source and reuse controlled master data and events. Integrate scanners, machine/device signals, CAD/PLM, POS and external partners when value and reliability justify it. Every automation must retain permissions, traceability, failure handling and human override/approval as appropriate.

## 11. Required product quality gates

A capability is not complete when a screen or table exists. It must demonstrate:
1. A defined operator/manager workflow with the real SOP and exception paths.
2. Correct identity and genealogy across products, revisions, materials, bundles, WIP and finished goods.
3. Role- and location-based permissions and auditable approvals.
4. Validations, quality holds, stock/financial integrity and idempotent integrations.
5. Planning/optimization/alerts that reduce manual judgment where safe and explain recommendations.
6. KPIs with explicit formulas, owners and drill-down to source events.
7. Tests for normal, partial, rejected, reworked, reversed, concurrent and failure scenarios.
8. Training/work instructions, configuration/versioning and operational evidence.
9. Measured user/business benefit, not merely a data-entry completion statistic.

## 12. Research foundation and adoption caveats

This charter synthesizes public research and training references, including the International Labour Organization's Ethiopia garment-sector SCORE results, a 2026 peer-reviewed critical review of lean applications in textile/garment operations, an open-access 2024 study of fit-model participation and 3D virtual fitting, and Ethiopian/Bangladeshi government competency-based garment-production materials.

These references inform the process model; they do not establish one universal route or one guaranteed productivity target. Published case-study improvements are context-specific, not implementation promises. Each tenant must configure and approve its own SOPs, legal requirements, capacity assumptions, quality thresholds, recipes and incentive rules with competent apparel/IE/quality/HR specialists.

### Reference sources
- ILO, *Advancing productivity, quality improvement and decent work in Ethiopia's garment industry* (1 June 2022): https://www.ilo.org/resource/article/advancing-productivity-quality-improvement-and-decent-work-ethiopias
- ILO, *Industrial engineering as a catalyst for factory productivity improvements* (26 May 2025): https://www.ilo.org/resource/article/ilo-promotes-industrial-engineering-catalyst-productivity-improvements
- Arafat et al., *Applications of lean manufacturing in the textile garments industry*, International Journal of Advanced Manufacturing Technology (15 March 2026): https://link.springer.com/article/10.1007/s00170-026-17713-6
- Yang & Baytar, *Fit models' roles in identifying fit issues in the apparel technical design process and implications for improving 3D virtual fitting*, Fashion and Textiles (19 September 2024): https://link.springer.com/article/10.1186/s40691-024-00398-4
- Ethiopia Ministry of Labour and Skills, *Produce Simple Garment in Garment Production* (August 2022): https://www.hptc.edu.et/sites/default/files/2023-02/Download_49.pdf
- Ethiopia Federal TVET Agency, *Performing Garment Product Finishing* learning guide: https://dl.ojocv.gov.et/admin_/ttlm_/perform%20product%20finishing.pdf
- Bangladesh SEIP / Finance Division, *Competency-Based Learning Material for Garments Washing Technology*: https://seip-fd.gov.bd/wp-content/uploads/2022/11/CBLM-for-Garments-Washing-Technology.pdf


## 13. Embedded intelligence and agent-native garment operations

The garment ERP is a modern operating platform, not a collection of forms with an external chatbot. Users should be able to initiate appropriate approved workflows through conversation, structured UI, voice, images, camera capture and file uploads where useful.

- Product and design teams can ask the agent to prepare initiative, collection, product/variant, BOM, tech-pack, sample and fit-review drafts using approved master data and revision history.
- Sourcing and production teams can use document understanding, shortage explanations, capacity scenarios, line-balance and schedule recommendations with assumptions and source data.
- Quality teams can use image-assisted evidence capture and defect-triage suggestions; inspection criteria, quarantine and quality release remain controlled.
- Supervisors can receive WIP, bottleneck, downtime, material-readiness and skill-coverage alerts with explainable recommended actions.
- Management can ask natural-language questions over governed KPIs and permission-filtered business knowledge.
- Every action uses domain services, approved SOPs, versioned workflows and traceable audit. AI does not replace approved SMV/SAM, method standards, human design/fit acceptance, quality gates or worker safeguards.
- Future technology categories remain open through the extension standard; do not constrain the garment operating system to a predefined device/vendor list.


## 14. Full application and user-experience blueprint

The product-wide application shell and detailed technology applications from initiative through product design, materials, specialized production, quality, warehouse and customer endpoint are specified in [End-to-End ERP Technology and Intelligent Experience Blueprint](../architecture/end-to-end-erp-technology-and-experience-blueprint.md). This includes conversational, popover/popup, notification, voice, camera/scan and document-intake experiences, plus agent/human responsibilities at each stage.
