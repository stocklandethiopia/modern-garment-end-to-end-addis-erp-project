# Miret — Four-Pillar Garment Operating Model

**Recorded:** 26 November 2026  
**Applies to:** The existing, fixed WP-00–WP-16 sequence and every garment business process, API, data model, event, dashboard, agent and acceptance journey.  
**Status:** Owner-directed product and acceptance clarification. This document does not create a new work package, reorder WP-00–WP-16, close implementation work or certify a factory.

“Miret” is the project name for four interconnected operating pillars. It is not an external certification or a claim that one standard fits every apparel product.

## 1. The four connected pillars

| Pillar | Governing question | Required outcome |
|---|---|---|
| **Product identity and integrity** | What exactly are we designing, sourcing, making, handling and selling? | One consistent, versioned identity and complete genealogy from initiative to customer/return |
| **Quality at source** | What is the approved requirement at this exact stage, for this product, buyer, market and lot? | A versioned control plan, evidence, containment, rework/reinspection and authorized release at every required stage |
| **Industrial and business efficiency** | Can the business make the right quantity safely, to specification, on time, with the available people, skills, machines and materials? | Explainable monthly capacity, MPS, daily line/shift and hourly plans; reconciled actuals and improvement actions |
| **Modern technology and optimization** | How does the ERP reduce effort, prevent errors and support each real role? | A maintainable modern platform, reliable integrations and operational agents that act only through governed capabilities |

No pillar stands alone. A production output counts as successful only when it is tied to the correct product revision and route, has the required quality evidence, and reconciles its resources and actuals. High output that bypasses quality, safety, product identity or authorization is not a successful result.

## 2. Product identity: one traceable product passport

The product record must be unique, versioned and connected to the transactions that create, move and sell it. Keep domain ownership: PLM owns product/style revisions; inventory owns material and stock identities; MES owns operation events; Quality owns inspection/hold/release evidence; WMS/OMS owns handling, allocation and fulfilment; Finance owns valued and posted records.

The canonical trace should link, where applicable:

1. Initiative/opportunity, brand, collection, season, target market/customer and approved range line.
2. Product/style identity and exact approved revision; technical pack, pattern/grading, measurement and tolerance revision, artwork, care/label and packaging specification.
3. Variant dimensions (such as size and colour), sellable SKU and applicable barcode/GTIN; tenant-scoped uniqueness and globally governed identifiers must not be conflated.
4. Approved BOM and material specifications, supplier qualification, PO/receipt, fabric lot/dye lot/shade/roll, trim/accessory lot, chemical batch, packaging lot and their certificates/tests.
5. Approved costing basis, method/time-study revision, operation bulletin, route/SOP/work-instruction revision, quality/control-plan revision and pre-production approval.
6. Production order and line/shift/work-centre context; bundle/panel/carton/pallet/handling-unit genealogy; operator, verified skill, equipment/machine, location and each operation event.
7. Inspection plan and exact revision, sample/measurement/defect evidence, hold/disposition/rework/reinspection and release decision.
8. Finished-goods lot, carton, warehouse/DC/branch/store custody, reservation, shipment, wholesale/retail/e-commerce/POS sale and customer.
9. Return/RMA, complaint, repair/refund/disposition and product-performance feedback linked back to the originating style/variant/lot/process.

Do not use one generic product code as a substitute for material-lot, roll, bundle, carton, location or transaction identities. Never overwrite an approved product snapshot or an original movement/inspection. Correct through versioned change, reversal or linked correction events. Product identity changes must preserve the old identity and show which orders, lots and customers were affected.

## 3. Quality at source: from incoming material to customer feedback

Quality requirements are selected by applicability, not by one universal number. Store the issuing body, standard/test method identifier, edition/revision, clauses or characteristic, applicability, effective dates, market/customer/product/process scope, sample plan, acceptance criteria, calibrated equipment/lab requirement, reviewer and approval. Distinguish law, buyer/brand contract, technical specification, voluntary standard and internal policy.

### Quality gates across the full flow

| Stage | Required quality evidence and control |
|---|---|
| Supplier and pre-purchase | Supplier/site qualification, capability, approved specification, certificates/test reports, restricted-substance/chemical evidence where relevant and change/expiry status |
| Incoming fabric | Construction/composition, width, GSM/weight, shade/dye-lot, roll length/quantity, defects, shrinkage/colourfastness or other specified tests, sampling/equipment and quarantine/partial acceptance |
| Incoming trim/accessory | Exact BOM/spec revision, artwork/colour/size/compatibility, dimension/function/strength where specified, lot/batch and label/care/brand correctness |
| Chemical and packaging | Approved chemical/recipe, SDS and restricted-substance scope, batch/expiry/storage; packaging/label/pack revision, artwork, dimensions and market requirements |
| Product/sample/fit | Approved sample round, measurements/tolerances, construction, fit/movement/comfort comments, design/technical/quality review and exact approved revision |
| Pre-production/first piece | Released tech pack/BOM/route/SAM, material and machine readiness, line setup, first-piece or strike-off approval before bulk work where required |
| In-process/inline | Route-step control plan, operation, line/work-centre, operator/machine, sample/units, measurement/defect, time, evidence and containment; control frequency comes from the approved plan |
| Special process | Print/embroidery/wash/dye/fusing/pressing/other routed process: artwork/recipe revision, machine/settings, chemical/thread/ink batch, operator competence, parameters, test and HSE evidence |
| End-line/finishing/final | Assembly/workmanship, dimensions, components, function, appearance, repair/rework, final inspection and required safety/product checks |
| Packing/carton | Size/colour assortment, quantity, SKU/label/care/pack conformity, carton identity, weight/dimensions, seal and required metal/needle checks |
| Shipment and distribution | Shipment/handling-unit match, documents, seals, transport damage, DC/branch/store receipt, discrepancy and custody records |
| Sale, retailer and customer return | Complaint/return reason, photographs and lot/style links, return inspection/disposition, customer remedy and feedback to CAPA/product/collection decisions |

A required failed or missing check creates a hold at the affected scope. A hold must prevent the relevant next step (material issue, route dispatch, pack, reserve or ship) until an authorized decision with evidence. Rework, repair, sort, supplier return, concession/use-as-is or scrap must have a permitted disposition, quantity reconciliation and reason. Reinspection is a new event; it never erases the original fail. AI may help collect/compare evidence and identify a likely defect, but quality authority owns disposition and release.

### Standards register and selection rules

As at 26 November 2026, the standards register should account for these current source snapshots:

- **ISO 9001:2026, edition 6** — quality-management-system requirements and continual-improvement framework. It is not a garment measurement specification and does not certify an individual product by itself.
- **ISO 2859-1:2026, edition 3** — lot-by-lot acceptance sampling by attributes, with AQL-indexed schemes. The selected inspection level, scheme, lot size, switching rules and acceptance/rejection numbers must be approved for the applicable contract/risk; do not hard-code one AQL across all buyers or processes.
- **ISO/IEC 17025:2017** — competence, impartiality and consistent operation of testing/calibration laboratories; track laboratory/accreditation scope and validity where required.
- **AATCC Manual of International Test Methods and Procedures, 2026 Volume 101** — textile test-method reference. Store the exact method and edition used; do not assume every AATCC test applies to every fabric or garment.

Other buyer, brand, market and product-specific requirements (including other ISO, ASTM, EN, retailer manuals or national legal requirements) are added only with an authoritative source, exact edition, scope, interpretation and approval. Legal/technical specialists and the relevant buyer/customer own the selection and acceptance limits. The ERP is a control and evidence system, not an issuer of certification.

Official references:
- [ISO 9001:2026](https://www.iso.org/standard/9001)
- [ISO 2859-1:2026](https://www.iso.org/standard/2859-1)
- [ISO/IEC 17025:2017](https://www.iso.org/standard/17025)
- [AATCC 2026 Manual, Volume 101](https://www.aatcc.org/news-read/insights/announcing-the-101st-edition-of-the-aatcc-manual-of-international-test-methods-and-procedures)

## 4. Efficiency: monthly capacity to hourly line control

Planning is one connected hierarchy, not four unlinked spreadsheets:

1. **Monthly/seasonal strategic capacity:** factory, department, line/work-centre, machine family, subcontract, headcount/skills, product mix, calendar, demand and delivery window.
2. **Master production schedule (MPS):** customer or own-brand orders, style/revision, quantity, size/colour mix, site, route, start/finish and shipment priority.
3. **Material/resource plan:** approved BOM and allowances, usable stock, reservations, open POs/in-transit, supplier lead time, fabric/trim readiness, maintenance and qualified machine/worker availability.
4. **Line loading and daily plan:** feasible style/order and route-step load per line/cell/shift; target, sequence, changeover, staffing/skill, quality checks, buffer/WIP limits and named supervisor.
5. **Hourly control:** planned vs actual accepted output, operation WIP, earned minutes, direct-labour minutes, defects/rework, machine downtime, shortages, quality holds, constraint reason and forecast finish.
6. **Shift handover and daily review:** open WIP, shortages, breakdown, quality holds, maintenance, attendance/skill changes, recovery owner and due time; preserve the original plan and every approved revision.

For a simple single-style line/shift, preserve the approved project formula:

**Daily line target (pieces) = available direct-labour minutes × approved target efficiency ÷ approved style SAM per piece.**

The planner must show each input and revision: net productive minutes after lawful breaks/planned unavailability, actual planned headcount, approved effective SAM/SMV, target-efficiency assumption, learning curve, changeover, skill coverage, machine/attachment capacity, material readiness, maintenance and quality holds. It must show requested vs feasible quantity and the bottleneck; a plan remains a scenario until the authorized planner/supervisor approves and dispatches it.

For measurement:
- Completed-style earned minutes = accepted good quantity × approved style SAM.
- Operation-level earned minutes = accepted operation quantity × approved operation SAM; do not count both completed-style and operation-level earned time for the same work.
- Efficiency = earned standard minutes ÷ explicitly scoped eligible direct-labour minutes × 100.
- Separate efficiency, utilization, attendance, throughput, line-balance efficiency, FPY and OEE. Define the numerator, denominator, unit, time window and data source for each KPI before publishing it.
- Track output, accepted good, reject, rework, scrap and WIP separately. Rejected, uninspected or quality-held work is not accepted good output.
- Reason-code losses for shortage, wrong shade/spec, skill gap, absenteeism, machine/utility fault, setup/changeover, quality stop, training, planned maintenance, route dependency and subcontract wait. Do not penalize a worker for a verified upstream constraint.

Compare by company/tenant, legal entity, factory, department, line, machine, shift, style/order, operation and (where lawful and policy-approved) worker. Worker-facing metrics must be understandable, contextual, access-restricted and non-punitive. Never convert an unapproved SAM or a planning assumption into wages, deductions or a compulsory individual quota. Any incentive is an independently approved HR/payroll policy with quality, safety, lawful-time, worker visibility and appeal controls.

## 5. Technology and operational agents

The platform baseline remains Angular 22 / TypeScript 6 / Node 26 for the web client, Laravel 13 / PHP 8.5 for transactional APIs, PostgreSQL 18 for source-of-truth and tenant isolation, Redis 8.2 for ephemeral cache/queue coordination, Python 3.15 for specialist computation, and Docker/Compose for reproducible environments. Preserve the modular-monolith boundary; do not create a second business API or allow AI/Python to write around Laravel domain rules.

Current Angular capabilities and safe adoption are governed by [Angular Current Capabilities and Adoption Standard](../architecture/angular-current-capabilities-and-adoption-standard.md). The official Angular main branch is a changing development feed, not the package version to deploy. Use stable features that are actually present in the pinned Angular release. Experimental capabilities are feature-flagged pilots only.

Operational agents must be attached to a real user task, record context and evidence, invoke named domain capabilities, and return a measurable result:

| Work role/activity | Agent responsibility | Hard boundary |
|---|---|---|
| Merchandiser/product manager | Summarize approved market/return/sell-through signals; prepare initiative, range and scenario drafts | No silent launch, price or assortment approval |
| Designer/technical developer | Compare approved style/tech-pack revisions; find measurement/BOM/label conflicts; stage revision checklist | Cannot approve its own or replace an approved revision |
| Buyer/material controller | Extract supplier/GRN/certificate data; compare spec/quantity/UOM/lead time; detect lot/spec mismatch and shortage | No supplier award, PO approval, bank change or stock posting without domain controls |
| Incoming QC inspector | Show applicable inspection plan, previous supplier/lot evidence and required characteristics; capture structured result | Human inspector and quality reviewer own inspection decision and release |
| IE/planner | Calculate monthly capacity, line load, SAM-based targets, bottleneck, changeover and skill/machine/material scenarios | Recommendations disclose assumptions; authorized planner approves plan/target |
| Operator/line supervisor | Scan bundle/operation, show next eligible task and SOP; raise Andon; explain WIP, downtime and hourly variance | No silent route skip, skill bypass, quality-hold bypass or unsafe machine control |
| Inline/end-line QC | Surface due checks, repeat defect patterns and evidence gaps; open NCR/CAPA drafts | Cannot self-release held product or close CAPA effectiveness |
| Finishing/packing | Check approved finishing/pack instructions, SKU/size mix, carton quantity and label mapping | Cannot ship unreconciled or held goods |
| Warehouse/DC/branch | Resolve barcode/QR to approved item/lot/location; stage receipt, move, count, pick and discrepancy actions | Server validates custody, reservation, idempotency and stock balances |
| Wholesale/retail/e-commerce | Stage order/POS/return files, map SKU, detect duplicates, reconcile tender and order status | User approval and owning POS/OMS/Finance command required for posting/refund |
| Management/quality/IE control tower | Explain KPI movements with drill-down to original events; prioritize overdue constraints/CAPA | No fabricated causes, inferred facts or cross-tenant disclosure |
| HR/Finance | Retrieve approved policy, explain a calculation draft and identify missing evidence | No autonomous adverse employment decision, payroll execution, tax activation, payment or journal posting |

Agent execution must follow the Agent-Native Standard: server-derived actor/tenant/scope, permission-checked capability registry, typed inputs/outputs, source references, risk tier, human approval where required, idempotency, transactional outbox, audit trail, evaluation and an emergency disable path. The model never supplies its own identity, approval or permission. It never executes arbitrary SQL/shell, changes application code, deploys production or approves its own action. Normal ERP functions must continue when an AI provider is unavailable.

## 6. Miret acceptance across WP-00–WP-16

The Miret model is cross-cutting and must be traced into the existing package sequence:

| Work package | Miret contribution and required evidence |
|---|---|
| WP-00 Governance | Owners, change control, approval limits, evidence vocabulary and release boundary |
| WP-01 Requirements | Stable requirements link product, quality, efficiency, role, event, KPI, test and owner |
| WP-02 Ethiopia legal | Effective-dated official legal/market rules; legal requirements kept distinct from voluntary quality standards |
| WP-03 Enterprise | Tenant, legal entity, site, factory, line, user/scope, RLS and audit foundations |
| WP-04 Merchandising | Initiative/brand/collection/range identity, demand, channel and product success measures |
| WP-05 PLM | Unique style/revision/variant, product passport source, tech-pack, BOM, pattern, sample/fit and production snapshot |
| WP-06 IE | Approved time studies, SAM/SMV, operation bulletin, monthly capacity, line balance and readiness |
| WP-07 Sourcing | Qualified supplier, fabric/trim/chemical/packaging lots, incoming quality and material readiness |
| WP-08 Route/SOP | Versioned operation route, SOP, quality point, required skill/machine/parameters and exception paths |
| WP-09 MES | Actual operation/bundle/WIP/scan/consumption/downtime, in-process QC and complete genealogy |
| WP-10 Planning | Monthly/MPS/daily per-line and hourly plans, earned minutes, skill/machine/material constraints and fair improvement |
| WP-11 Quality | Incoming/first-piece/inline/end-line/special-process/final/shipment/return quality, holds, CAPA and HSE |
| WP-12 Distribution | Finished-goods identity, carton/lot custody, DC/branch/store, wholesale/retail/digital fulfilment and returns |
| WP-13 Finance | Product/material/labour/quality-loss cost and reconciled financial consequences; controlled posting |
| WP-14 Workforce | Verified competence, attendance/time, training, lawful payroll and transparent approved incentives |
| WP-15 Integration/AI | Domain agents, outbox, event lineage, governed KPIs, optimization and model/tool evaluation |
| WP-16 Acceptance/release | Adversarial, concurrent and E2E tests across all four pillars; exact artifact/commit evidence and approved rollout |

### Mandatory end-to-end acceptance journeys

1. **Identity:** one new own-brand style moves initiative → approved style revision → variant/SKU → BOM/material lots → order/route → bundle/carton → DC/branch/store → sale/return, with no ambiguous or broken link.
2. **Quality at source:** a defective incoming fabric/trim lot is quarantined; affected work is prevented from consumption; a failed inline check places the right bundle/order/operation on hold; rework and independent reinspection retain original events; only approved good quantity is packed/shipped.
3. **Capacity and efficiency:** monthly capacity and MPS produce a feasible line/shift plan; daily target can be reproduced from approved SAM, labour minutes and efficiency assumption; hourly accepted output, earned minutes, WIP, machine/skill/material loss and revised forecast reconcile to source events.
4. **Agent utility:** each selected role receives a relevant task-specific explanation or staged action from the correct context; unauthorized user/tenant cannot access it; no agent bypasses a hold or approval; accepted commands are idempotent and auditable.
5. **Closed-loop learning:** shipment/store/customer complaint and return reason are linked to style/variant/lot/process and feed quality/CAPA, costing and the next collection decision.

A design paragraph or screen is not proof of implementation. Every journey must have automated coverage where practical, role/tenant denial cases, concurrency/replay cases, human-owner acceptance and exact-head CI evidence. Factory calibration, buyer/customer acceptance, local legal sign-off and production rollout remain separate gates.

## 7. Change control and ownership

The four pillars refine how the approved product goal and work packages are integrated; they do not authorize package reordering, scope expansion into unrelated industries, universal AQL values, invented factory targets or unapproved statutory rules. Any material architecture, legal, commercial, privacy or release-boundary change remains subject to the existing governance agreement and required owner/professional approvals.
