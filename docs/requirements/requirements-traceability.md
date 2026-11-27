# WP-01 — Requirements Inventory and Traceability Matrix

**Project:** Modern Garment End-to-End Addis ERP  
**Package:** WP-01 — Requirements and traceability  
**Status:** LEGACY GAR MATRIX — R3 CANONICAL REGISTER RECONCILIATION OPEN. This file contains 120 `GAR-*` records and is not yet reconciled to the verified WP-01 R3 register of 128 records across 24 bounded contexts. See [WP-01 R3 register reconciliation](wp01-r3-reconciliation-2026-11-27.md). The two ID namespaces must not be treated as interchangeable.  
**Authority:** Product Owner's express autonomous WP-01–WP-16 delegation, 22 November 2026  
**Baseline:** `docs/agent/project-governance-agreement.md`, `docs/product/domain-map.md`, apparel operating-system charter, distribution/channel charter, platform, security, localization, payroll and engineering standards.

## 1. Package outcome and boundary

WP-01 converts the approved product goal into uniquely identified, testable, cross-domain requirements. It does not add product scope. Every item below is traceable to one or more approved domain documents and is assigned to the work package that designs or delivers it.

This is a greenfield apparel business operating system, not a copy of the legacy Stockland ERP, not a cut-and-sew-only tracker, and not a collection of isolated CRUD screens. A requirement is accepted only when the workflow, accountable roles, rules, data ownership, evidence/audit trail, integration consequences, security and acceptance test are defined.

**Interpretation of completion:** the inventory and traceability framework are complete as a product-analysis deliverable. Application code, database migrations and executable acceptance tests are implementation outputs of later work packages; they are not falsely marked as implemented in WP-01.

## 2. Requirement record model

Each requirement has:
- A stable ID (`GAR-...`) and domain owner.
- Its authoritative scope source (approved repository document and section/domain).
- Primary actor and accountable control role.
- Trigger, workflow, decision/control and exception path.
- Core information/data ownership and required audit evidence.
- Acceptance scenario ID(s) used later in the test catalogue.
- Delivery package and dependency links.

### Traceability rules

1. A single source fact is captured once at its accountable domain; downstream domains consume a versioned reference or event.
2. A business object has one authoritative owner. Read models and analytics may project data but cannot become competing transaction owners.
3. Every transaction is tenant- and legal-entity-scoped. Site, factory, DC, branch, store, inventory location, channel and customer account are separate dimensions.
4. Product, process, supplier, material and route revisions are effective-dated and immutable after approval; corrections create revisions or reversing events.
5. Material movements are append-only source events with quantity, unit, source/destination, lot/roll/bundle/carton genealogy, actor, reason and timestamp.
6. Financial postings use balanced, auditable journals; operational events never directly mutate a posted ledger balance.
7. Statutory rules are effective-dated, source-linked and approval-gated. An unverified interpretation is `REVIEW REQUIRED`, never a guessed live calculation.
8. Human decisions are captured as approvals with actor, authority, timestamp, decision, reason and source revision.
9. Tests must prove positive, negative, partial, duplicate/retry, reversal, concurrency, tenant-crossing and audit cases as relevant.
10. A screen, endpoint, table, report or AI suggestion alone does not satisfy a requirement.

## 3. Traceability matrix

Legend: `A` = accountable business owner role; `Core data` = minimum authoritative records; `Control/evidence` = business control and persisted proof. Tests are named here and elaborated into executable suites in the implementation packages.

### A. Enterprise, tenant, identity and governance — Domain B (WP-03)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-ENT-001 | Separate customer tenant, legal entity, business unit, site and operating location | Tenant admin / Platform owner | Provision tenant; associate legal entities and sites; reject cross-tenant links | Tenant, legal entity, org unit, site; provisioning audit | AT-ENT-001: two tenants cannot read, link or mutate one another's objects | WP-03 |
| GAR-ENT-002 | Support multiple legal entities, currencies, calendars, languages and business models in one tenant | Group admin / Finance controller | Configure entity-specific policy, fiscal calendar and currency; block ambiguous ownership | Entity profile, policy version, calendar, currency | AT-ENT-002: same tenant runs two entities with different calendars and ledgers | WP-03 |
| GAR-ENT-003 | Model factory, warehouse, DC, branch, store and inventory location as distinct types | Operations admin / Supply-chain lead | Create typed network node; validate permitted parent-child relationships | Network node/type, location, relation, effective period | AT-ENT-003: branch does not implicitly create a warehouse or stock balance | WP-03 |
| GAR-ENT-004 | Separate brand, channel, customer/account and fulfilment partner from legal entity/site | Commercial admin / Commercial director | Associate brands/channels/accounts to allowed entities and fulfilment nodes | Brand, channel, account, partner, mapping | AT-ENT-004: own retail, wholesale and D2C have distinct account/channel semantics | WP-03 |
| GAR-ENT-005 | Role-based permissions are scoped by tenant, entity, site, function and record sensitivity | Security admin / Security owner | Grant least privilege; enforce scope server-side; deny stale/revoked grants | User, role, permission, scope grant, access audit | AT-ENT-005: user cannot access another entity/site without explicit scoped grant | WP-03 |
| GAR-ENT-006 | Sensitive/high-impact actions require maker-checker and separation of duties | Process owner / Control owner | Request, validate conflicts, independent approve/reject; escalate self-approval | Approval request, policy, approver, decision event | AT-ENT-006: same user cannot request and approve a restricted transaction | WP-03 |
| GAR-ENT-007 | Version all critical master data and policies with effective dates | Master-data steward / Domain owner | Draft, validate, approve, schedule, supersede; prevent retroactive destructive edits | Version, status, valid-from/to, approval, change reason | AT-ENT-007: historical transaction resolves original approved master version | WP-03 |
| GAR-ENT-008 | Maintain immutable audit evidence for sensitive reads/writes and business decisions | Auditor / Security owner | Capture actor, action, object, scope, correlation, before/after hash and reason | Audit event, actor, correlation ID, object reference | AT-ENT-008: privileged modification is traceable and tamper-evident | WP-03 |
| GAR-ENT-009 | Configure legal activity, licence and site applicability before commercial operation | Entity admin / Legal-compliance owner | Record permit/license/activity/site validity; hold transactions where required evidence is missing | Licence, activity, location, authority, evidence, validity | AT-ENT-009: manufacturer retail activity is held for licence review when permission is not evidenced | WP-03 |
| GAR-ENT-010 | Enforce retention, privacy purpose, data-subject and access restrictions | Privacy officer / Data protection owner | Define purpose and retention; restrict export/delete; preserve legal hold | Data purpose, consent/authority basis, retention rule, request log | AT-ENT-010: sensitive employee data is hidden from unauthorized roles and export is audited | WP-03 |

### B. Initiative, market insight, brand, collection and merchandising — Domains A/C (WP-04)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-MER-001 | Start product/business lifecycle with initiative and opportunity, not only a buyer order | Strategy/brand lead / Business owner | Capture idea, rationale, market, segment and expected value; review/approve/reject | Initiative, idea, hypothesis, approval, evidence | AT-MER-001: own-brand concept progresses without customer order | WP-04 |
| GAR-MER-002 | Capture consumer, market, competitor, store and channel insights with source/provenance | Market analyst / Merchandising lead | Record observation, source, geography, confidence and date; resolve conflicting evidence | Insight, source, segment, geography, confidence | AT-MER-002: forecast/assortment decision traces back to source insight | WP-04 |
| GAR-MER-003 | Manage brand identity, positioning, target consumer, price architecture and product pillars | Brand manager / Commercial director | Draft brand strategy; approve revision; associate target categories and price bands | Brand, brand strategy version, category, price architecture | AT-MER-003: products require a valid brand strategy reference | WP-04 |
| GAR-MER-004 | Plan seasons, collections, range plans and assortment by market/channel | Merchandiser / Merchandising director | Build range, assess breadth/depth, target price, margin and capacity; approve gates | Season, collection, range, assortment, channel allocation | AT-MER-004: one collection supports differing channel/region assortments | WP-04 |
| GAR-MER-005 | Govern open-to-buy, demand forecast, buy plan and budget envelope | Planner / Finance controller | Forecast demand and commitments; reconcile ordered/on-hand/in-transit; flag excess/shortfall | OTB plan, forecast version, budget, commitment | AT-MER-005: OTB calculation reconciles planned demand and existing commitments | WP-04 |
| GAR-MER-006 | Manage style lifecycle, variant, size/color, SKU and commercial attributes | Product merchandiser / Product-data owner | Create style/variant, validate size/color matrix and SKU uniqueness; retire safely | Style, variant, SKU, size range, color, lifecycle | AT-MER-006: variant identity remains stable across channels and revisions | WP-04 |
| GAR-MER-007 | Govern target retail/wholesale price, margin, markdown and promotion approval | Pricing manager / Commercial controller | Draft price by market/channel/currency; simulate margin; approve promotion and expiry | Price list/version, cost basis, promotion, approval | AT-MER-007: unauthorized price or expired promotion is rejected | WP-04 |
| GAR-MER-008 | Feed sales, sell-through, returns and fit/quality feedback back into next range decision | Merchandiser / Product director | Analyze cohort and channel feedback; raise initiative/collection action | Sales/return facts, feedback, insight/action link | AT-MER-008: return reason affects style review without overwriting sale history | WP-04 |

### C. Design studio, product development and PLM — Domain D (WP-05)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-PLM-001 | Manage design brief, concepts, sketches and design reviews | Designer / Design director | Brief, concept, review, comment, approve/rework; preserve versions | Design brief, concept, asset, review decision | AT-PLM-001: approved concept is immutable; revision creates new version | WP-05 |
| GAR-PLM-002 | Manage patterns, pattern pieces, grading rules and size specifications | Pattern maker / Technical design lead | Create pattern revision, grade, validate dimensions and release status | Pattern, piece, grade rule, measurement spec, revision | AT-PLM-002: size grading validates all required points and tolerance | WP-05 |
| GAR-PLM-003 | Store/manage CAD, 2D, 3D, artwork and technical assets with revision and access control | CAD/3D designer / PLM owner | Upload/associate assets; compare versions; restrict source files; approve usage | Asset, version, checksum, storage metadata, rights | AT-PLM-003: superseded 3D/graphic asset cannot silently enter production tech pack | WP-05 |
| GAR-PLM-004 | Govern sample-room request, sample stages, allocation and status | Sample coordinator / Development manager | Create sample request; allocate capacity/material; record construction and sample result | Sample request, sample, stage, material issue, result | AT-PLM-004: sample stage cannot pass without required review evidence | WP-05 |
| GAR-PLM-005 | Capture fit sessions, fit model feedback, measurements, comments and approvals | Fit technician / Technical design lead | Record fit round; assign corrections; accept/reject/conditional approval | Fit session, measurement, comment, action, approval | AT-PLM-005: unresolved critical fit action blocks production release | WP-05 |
| GAR-PLM-006 | Maintain technical pack, construction details, operation sequence and workmanship standards | Technical designer / Product engineering owner | Assemble specs and images; review completeness; release a version | Tech pack, operation spec, tolerance, instruction, revision | AT-PLM-006: factory receives only released tech-pack version | WP-05 |
| GAR-PLM-007 | Govern BOM components, usage, wastage, substitutions and material compatibility | Product engineer / Materials owner | Calculate consumption; approve substitute and effective date; retain original BOM | BOM, component, consumption, UOM, wastage, substitute rule | AT-PLM-007: unauthorized fabric/trim substitution fails pre-production check | WP-05 |
| GAR-PLM-008 | Calculate style cost and scenario profitability using versioned assumptions | Cost engineer / Finance controller | Build material/labour/overhead/landed/channel cost; compare margin; approve basis | Cost sheet/version, rates, assumptions, scenario | AT-PLM-008: cost replay identifies exact BOM/SMV/rates used | WP-05 |
| GAR-PLM-009 | Control design, BOM, pattern, tech-pack and production-handover approvals | Product developer / Product director | Multi-step review; route changes to affected domains; hold production release | Approval matrix, release package, dependency status | AT-PLM-009: incomplete approval or superseded revision cannot be released | WP-05 |
| GAR-PLM-010 | Provide physical-fit evidence alongside 3D simulation where required | Fit technician / Quality lead | Configure style-specific evidence gates; attach physical sample/fit result | Fit protocol, sample result, 3D review, waiver | AT-PLM-010: 3D review does not auto-waive mandatory physical sample approval | WP-05 |

### D. Industrial engineering, method, time study and capacity — Domain D (WP-06)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-IE-001 | Govern operation breakdown, method sheets and approved work content | IE analyst / IE manager | Define operations, method, equipment, skill and sequence; review/approve | Operation bulletin/version, work method, equipment need | AT-IE-001: production route links to released operation bulletin | WP-06 |
| GAR-IE-002 | Manage time study, observed cycles, allowances and standard time evidence | Time-study engineer / IE manager | Sample cycles, normalize method, calculate standard, review outliers and approve | Time study, observation, allowance, standard revision | AT-IE-002: approved standard can be reproduced from observation evidence | WP-06 |
| GAR-IE-003 | Govern style/operation SAM or SMV with effective dates and approvals | IE manager / Operations director | Approve standard per style/operation/method; reject incomplete studies | Standard minute value, revision, source method | AT-IE-003: obsolete SAM cannot be used for new production order without exception | WP-06 |
| GAR-IE-004 | Calculate capacity by facility, department, line, shift, machine and skill | Production planner / Factory manager | Model available minutes and constraint calendars; explain capacity assumptions | Capacity calendar, resource, shift, skill coverage, downtime | AT-IE-004: capacity forecast excludes unavailable resources and highlights bottleneck | WP-06 |
| GAR-IE-005 | Balance lines and suggest operation allocation using work content and skill coverage | Line planner / IE manager | Propose line balance; simulate cycle/load; approve manual or recommended assignment | Balance scenario, operation load, operator skill assignment | AT-IE-005: no unqualified worker is assigned to restricted operation | WP-06 |
| GAR-IE-006 | Establish pre-production readiness gate before bulk start | Pre-production coordinator / Factory manager | Verify approved tech pack/BOM, materials, samples, route, capacity, quality and compliance | Readiness checklist, missing-item, approval | AT-IE-006: bulk order cannot release with critical missing readiness items | WP-06 |

### E. Procurement, supplier governance and materials — Domain E (WP-07)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-SRC-001 | Govern supplier onboarding, capability, risk, qualification and periodic review | Supplier quality/procurement / Procurement director | Due diligence, scope qualification, approve/suspend, reassess on expiry | Supplier, qualification, risk, certification, audit | AT-SRC-001: unqualified supplier cannot receive controlled award | WP-07 |
| GAR-SRC-002 | Manage requisition, sourcing event, quotes, bid evaluation and award | Requester/buyer / Procurement controller | Budget check, competitive evaluation, conflict disclosure, approval | Requisition, RFQ, quote, evaluation, award | AT-SRC-002: buyer cannot approve own restricted sourcing award | WP-07 |
| GAR-SRC-003 | Manage PO, contract, delivery schedule, change order and commitment | Buyer / Procurement manager | Approve PO within delegation; version amendment; track due dates | Purchase order/version, contract, commitment, schedule | AT-SRC-003: receipt cannot exceed authorization without tolerance/approval | WP-07 |
| GAR-SRC-004 | Record goods receipt, actual quantity, quality status and variance | Receiver / Warehouse controller | Count/weigh, inspect, quarantine, accept/reject; reconcile PO remainder | Receipt, lot, quantity, UOM, variance, QC disposition | AT-SRC-004: partial receipt and short/over variance do not duplicate stock | WP-07 |
| GAR-SRC-005 | Trace fabric by supplier lot, roll, shade, width, weight and inspection defects | Fabric storekeeper / Material-control lead | Receive, inspect, classify, locate, issue by roll/length; retain roll balance | Fabric roll, lot, shade, test, stock movement | AT-SRC-005: roll trace survives split issue, return, remnant and relocation | WP-07 |
| GAR-SRC-006 | Trace trim/accessory separately by item, lot, colour, size and approved specification | Trim storekeeper / Material-control lead | Receive, inspect, kit, issue by approved BOM demand, return leftovers | Trim lot, accessory, kit, movement, BOM line | AT-SRC-006: trim issue is not inferred from fabric-roll consumption | WP-07 |
| GAR-SRC-007 | Manage chemicals and controlled inputs with SDS, compatibility and handling rules | Chemical storekeeper / HSE owner | Verify supplier docs, segregation, expiry, authorized use and disposal | Chemical lot, SDS, hazard, storage class, use log | AT-SRC-007: expired/incompatible chemical is blocked from issue | WP-07 |
| GAR-SRC-008 | Manage import/export, customs classification, valuation, documents and landed cost | Import specialist / Finance-trade owner | Validate HS/origin/value/docs/FX, clear shipment, allocate landed cost | Shipment, declaration, tariff source, invoice, cost allocation | AT-SRC-008: landed cost reconciles invoices, duty, freight, exchange and receipt | WP-07 |
| GAR-SRC-009 | Distinguish supplier invoice/AP liability from receipt/stock movement | AP accountant / Financial controller | 3-way match PO, receipt, invoice; hold mismatch; post approved AP | AP invoice, match, tax attributes, journal reference | AT-SRC-009: invoice does not create duplicate inventory movement | WP-07 |
| GAR-SRC-010 | Track subcontract work, material custody, accepted output and loss/variance | Subcontract coordinator / Supply-chain manager | Issue materials to processor, reconcile output/scrap, inspect return, settle AP | Subcontract order, custody, issue/return, accepted output | AT-SRC-010: subcontract material balance and accepted output reconcile by lot | WP-07 |

### F. Department SOP, route and manufacturing execution — Domains H (WP-08/WP-09)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-MFG-001 | Model versioned SOPs/work instructions with owner, competence, revision and acknowledgement | Process engineer / Department head | Draft, technical review, quality/HSE review, release, supersede, acknowledge | SOP, instruction, revision, authorization, training record | AT-MFG-001: operator sees only effective instruction for active route | WP-08 |
| GAR-MFG-002 | Configure product-specific, conditional, parallel and outsourced process routes | Production engineer / Manufacturing owner | Build route revision with conditions, optional/parallel/return paths; approve | Route, step, dependency, condition, route release | AT-MFG-002: style with no wash step does not enter wash queue | WP-08 |
| GAR-MFG-003 | Execute spreading, marker, lay planning, cutting and cut-part reconciliation | Cutting planner/operator / Cutting manager | Plan lay; verify marker/fabric shade/width; cut; inspect; record yield/scrap | Cutting order, lay, marker, roll, panel, scrap, cut bundle | AT-MFG-003: cut quantity and residual roll balance reconcile | WP-09 |
| GAR-MFG-004 | Create and trace panel/bundle IDs through movement, split/merge and operation hand-offs | Bundle handler / Production controller | Label, move, split/merge under reason and genealogy; prevent duplicate scans | Bundle, panel range, operation WIP, movement event | AT-MFG-004: bundle split/merge preserves source panel genealogy | WP-09 |
| GAR-MFG-005 | Issue and return trims/accessories as a distinct controlled material transaction | Trim issuer / Materials controller | Reserve, pick, issue to line/order, record shortage/return, reconcile usage | Trim issue/return, BOM demand, lot, recipient, movement | AT-MFG-005: trim cannot be silently deducted from fabric stock | WP-09 |
| GAR-MFG-006 | Execute print/embroidery/embellishment with artwork/recipe/process approval | Special-process supervisor / Quality owner | Verify artwork, placement, colour, parameters, sample approval; record output | Artwork revision, process batch, recipe, machine, QC result | AT-MFG-006: rejected artwork/process parameter blocks production batch | WP-09 |
| GAR-MFG-007 | Execute sewing line bundles by operation, work centre, operator and shift | Line supervisor / Sewing manager | Dispatch eligible WIP, record operation output/defect/rework and hand-off | Bundle, operation event, operator, line, machine, shift | AT-MFG-007: operation WIP and accepted output reconcile at every hand-off | WP-09 |
| GAR-MFG-008 | Support optional wash/dye/wet processing and subcontract variants | Process supervisor / Wet-process manager | Validate recipe, load, capacity, chemical/HSE controls, batch result and rework | Wet-process batch, recipe version, chemical lot, wastewater record | AT-MFG-008: only routed styles can enter an authorized wet-process batch | WP-09 |
| GAR-MFG-009 | Manage finishing, pressing, measurement, presentation and final preparation | Finishing supervisor / Production manager | Finish, inspect measurements/appearance, repair, release or hold | Finishing lot, measurement, defect, rework, QC gate | AT-MFG-009: failed finish check returns to controlled repair and reinspect | WP-09 |
| GAR-MFG-010 | Pack by customer/channel pack specification, carton assortment and labels | Packing supervisor / Warehouse owner | Validate SKU/size/carton plan; pack, seal, weigh, label and reconcile | Packing order, pack ratio, carton, serial/seal, shipment link | AT-MFG-010: carton cannot contain unapproved SKU/size/quantity mix | WP-09 |
| GAR-MFG-011 | Execute configurable outsourced operations with custody, SLA, quality and settlement | Subcontract coordinator / Manufacturing owner | Dispatch, acknowledge, monitor, receive, inspect, settle; escalate loss/late | Outsource operation, custody transfer, return receipt, SLA case | AT-MFG-011: missing quantity at subcontract return raises controlled exception | WP-09 |
| GAR-MFG-012 | Capture real-time WIP, downtime, Andon, shift handover and reason-coded loss | Operator/supervisor / Factory manager | Scan/record events; classify constraint; route alert to responsible support | WIP snapshot/event, downtime, Andon, shift handover | AT-MFG-012: shortage/downtime signal reaches assigned owner and is auditable | WP-09 |

### G. Production planning, performance, workforce capability and improvement — Domain H/K (WP-10/WP-14)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-OPS-001 | Calculate daily line target from available labour minutes, approved target efficiency and SAM/SMV | Production planner / IE manager | Generate transparent target; show headcount, net time, SMV and assumptions | Shift calendar, staffing, SAM version, target version | AT-OPS-001: target recomputes only with recorded reason/version | WP-10 |
| GAR-OPS-002 | Show hourly plan/actual, earned minutes, line efficiency, WIP and completion forecast | Line supervisor / Factory manager | Compare accepted output and earned standard minutes; highlight variance | Hourly output, earned minutes, capacity, forecast | AT-OPS-002: mixed-style efficiency uses operation bulletin without double count | WP-10 |
| GAR-OPS-003 | Attribute constraints to material, quality, machine, method, skill, attendance or plan | Supervisor / IE manager | Record reason and affected time/order; route next action | Constraint, downtime, shortage, hold, reason code | AT-OPS-003: workers not penalized for verified upstream constraints | WP-10 |
| GAR-OPS-004 | Maintain assessed skill matrix, expiry, evidence and operation authorization | Trainer/assessor / HR training owner | Assess, certify, expire, retrain and restrict deployment | Skill, level, evidence, assessor, validity, authorization | AT-OPS-004: expired/unqualified skill blocks restricted assignment | WP-10 |
| GAR-OPS-005 | Recommend line balance and cross-training from skills, work content and capacity | IE planner / IE manager | Simulate workload and skill gaps; human approves deployment | Skill gap, balance scenario, recommended assignment | AT-OPS-005: recommendations explain constraint and do not auto-rank workers | WP-10 |
| GAR-OPS-006 | Govern optional transparent incentives with quality, safety, fairness and appeal | HR/IE / HR director | Version policy, simulate, approve, calculate, publish breakdown, handle appeal | Incentive policy, earning event, quality/safety gate, appeal | AT-OPS-006: no incentive rewards skipped QC or unsafe output | WP-10/WP-14 |
| GAR-OPS-007 | Support daily meetings, 5S, Kaizen, suggestions and measured countermeasures | Team leader / Continuous-improvement owner | Capture problem, cause, action, owner, due date, before/after result | Improvement, suggestion, action, benefit evidence | AT-OPS-007: action closure requires effectiveness check | WP-10 |
| GAR-OPS-008 | Track planned maintenance and machine availability impacts on production | Maintenance planner / Maintenance manager | Schedule preventive maintenance; log failure/repair/return-to-service | Asset, PM plan, work order, downtime, release | AT-OPS-008: out-of-service machine excluded from available capacity | WP-10 |

### H. Quality, compliance, product safety and HSE — Domain I (WP-11)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-QLT-001 | Control incoming fabric, trim, accessory and chemical inspection by specification | Incoming QC / Quality manager | Sample/inspect, classify, quarantine, accept/reject, supplier claim | Inspection plan, sample, result, lot disposition | AT-QLT-001: rejected lot cannot be allocated to production | WP-11 |
| GAR-QLT-002 | Require first-piece approval when defined by style/process risk | Line QC / Quality manager | Inspect first output against approved specification; release or stop | First-piece inspection, measurement, defect, approval | AT-QLT-002: bulk execution stays held until required first-piece approval | WP-11 |
| GAR-QLT-003 | Capture inline and end-line quality by style, operation, bundle, line and defect | Inline/end-line inspector / Quality lead | Inspect sample/units; contain, repair, recheck and escalate | QC event, defect code, sample, disposition, origin | AT-QLT-003: defect rate and source stage derive from traceable inspection facts | WP-11 |
| GAR-QLT-004 | Apply process-specific quality gates for print, embroidery, wash, finish and pack | Special-process QC / Quality manager | Validate parameter and output; quarantine deviation; rework/waiver approval | Process QC plan, batch result, deviation, waiver | AT-QLT-004: omitted optional step does not create false failed inspection | WP-11 |
| GAR-QLT-005 | Govern final inspection, AQL/customer requirement and shipment release | Final inspector / Quality manager | Build sample plan, record result, disposition shipment; block failure | Final inspection, sampling plan, customer spec, release | AT-QLT-005: failed shipment audit blocks dispatch unless authorized disposition | WP-11 |
| GAR-QLT-006 | Manage defect containment, rework, reinspection, root cause and CAPA | Quality engineer / Quality director | Contain, assign cause/action, verify effectiveness; reopen ineffective CAPA | Defect, rework order, RCA, CAPA, verification | AT-QLT-006: reworked unit must pass reinspection before release | WP-11 |
| GAR-QLT-007 | Maintain compliance evidence, certificates, audits, corrective actions and validity | Compliance officer / Compliance owner | Schedule, inspect, attach evidence, expiry and corrective action | Requirement, certificate, audit, finding, action | AT-QLT-007: expired required certification blocks configured activity/release | WP-11 |
| GAR-QLT-008 | Capture workplace safety, incident, hazard, PPE and controlled chemical processes | HSE officer / HSE manager | Assess risk, authorize controls, record incidents, investigate and close | Hazard, incident, risk assessment, PPE, training | AT-QLT-008: high-risk unsafe condition triggers stop-work escalation | WP-11 |
| GAR-QLT-009 | Preserve environmental and hazardous-waste controls for wet/chemical processing | Environment/HSE officer / Compliance owner | Track permit obligations, chemical inventory, effluent/waste manifests and inspections | Permit, chemical use, waste batch, disposal evidence | AT-QLT-009: chemical/waste activity requires configured permit and evidence status | WP-11 |

### I. Warehouse, distribution, order management and channel commerce — Domains F/G (WP-12)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-DST-001 | Maintain separate raw, WIP, finished, consignment, quarantine and in-transit stock states | Warehouse operator / Inventory controller | Receipt, hold, transfer, reserve, release; reconcile state changes | Stock ledger, inventory status, movement, location | AT-DST-001: each movement changes stock once and records source/destination | WP-12 |
| GAR-DST-002 | Model central/regional DC, depot, branch, store, warehouse and location separately | Network planner / Distribution director | Define node, ownership, service territory and capacity; validate stock permission | Network node, location, node capability, relation | AT-DST-002: retail branch can exist without inventory location | WP-12 |
| GAR-DST-003 | Track lot/roll/bundle/carton/pallet/serial genealogy through storage and transfer | Warehouse operator / Inventory controller | Scan move, split, consolidate, repack; retain complete source genealogy | Handling unit, lot, bundle, carton, movement chain | AT-DST-003: carton contents trace back to fabric lot and cut bundle | WP-12 |
| GAR-DST-004 | Support inventory reservations and available-to-promise by policy | Order planner / Fulfilment manager | Reserve eligible available stock, account for holds/lead time and oversell policy | Inventory balance/projection, reservation, promise | AT-DST-004: held, damaged or unlocated goods are excluded from ATP | WP-12 |
| GAR-DST-005 | Execute replenishment and inter-node transfer with approval and in-transit reconciliation | Replenishment planner / Network manager | Plan min/max/reorder, approve transfer, ship/receive/variance | Replenishment plan, transfer, shipment, receipt | AT-DST-005: in-transit remains distinct until destination receipt | WP-12 |
| GAR-DST-006 | Manage wholesale order, credit, allocation, dispatch, invoice and collection | Wholesale sales / Commercial controller | Validate account/credit/price/stock; fulfil; invoice/collect; manage shortage | Sales order, credit limit, allocation, dispatch, AR | AT-DST-006: credit hold blocks unauthorized order release | WP-12 |
| GAR-DST-007 | Manage independent retailer/reseller terms, rebates, sell-in and sell-through | Channel manager / Commercial director | Contract terms, tier rebate, claimed evidence, validate eligibility | Channel account, agreement, rebate, claim, sell-through | AT-DST-007: reseller rebate is not posted to employee payroll | WP-12 |
| GAR-DST-008 | Manage owned store operations, POS, cash, returns, stock and daily close | Store manager / Retail controller | Sale, tenders, void/refund, cash-up, close and discrepancy review | POS receipt, tender, cash session, store movement | AT-DST-008: sale and cash/stock/GL effects reconcile idempotently | WP-12 |
| GAR-DST-009 | Support D2C/e-commerce/marketplace orders, payment and channel fulfilment | Digital commerce / E-commerce owner | Import order, reserve, authorize payment, pick/pack/ship, refund | Digital order, payment intent, fulfilment, refund | AT-DST-009: duplicate webhook does not duplicate order/payment/stock | WP-12 |
| GAR-DST-010 | Manage carrier, delivery, proof of delivery, failed delivery and reverse logistics | Dispatcher / Logistics manager | Tender shipment, track milestone, capture delivery/exception/return | Consignment, carrier event, POD, return authorization | AT-DST-010: undelivered return reconciles back into inspection/stock disposition | WP-12 |
| GAR-DST-011 | Govern customer returns, exchanges, refund, repair and quality feedback | Customer service / Returns controller | Authorize, inspect, classify resale/repair/scrap; credit/refund and feedback | RMA, disposition, credit note, feedback | AT-DST-011: return condition determines correct stock and financial treatment | WP-12 |

### J. Finance, accounting, treasury and statutory finance — Domain J (WP-13)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-FIN-001 | Maintain entity-specific chart of accounts, fiscal calendars, periods and accounting policies | GL accountant / Finance controller | Configure, review, lock period; manage controlled reopening | Ledger, account, fiscal period, policy version | AT-FIN-001: journal cannot post to closed period without controlled reopen | WP-13 |
| GAR-FIN-002 | Post balanced, immutable journals from approved source transactions | GL accountant / Financial controller | Validate account, dimension, currency, balance, approval and idempotency | Journal, lines, source reference, posting event | AT-FIN-002: journal is balanced; duplicate source event cannot post twice | WP-13 |
| GAR-FIN-003 | Manage AP invoice, matching, tax, payment proposal and settlement | AP accountant / AP manager | Match PO/receipt/contract; approve; pay through authorized bank workflow | Supplier invoice, match, liability, payment, remittance | AT-FIN-003: AP does not create duplicate receipt or stock posting | WP-13 |
| GAR-FIN-004 | Manage AR billing, receipt, credit, collections, aging and dispute | AR accountant / AR manager | Invoice from fulfilled sale; apply receipt/credit; resolve dispute | Customer invoice, receipt allocation, credit, aging | AT-FIN-004: partial collection leaves correct open balance | WP-13 |
| GAR-FIN-005 | Manage cash, bank accounts, payment runs, reconciliation and treasury forecasts | Treasurer / CFO | Propose, approve, execute through bank integration/manual evidence, reconcile | Bank account, payment batch, statement, cash forecast | AT-FIN-005: payment is not treated as settled without bank evidence | WP-13 |
| GAR-FIN-006 | Cost product, inventory, WIP, finished goods, landed cost and production variance | Cost accountant / Cost controller | Apply approved costing method; reconcile actual vs standard; close variance | Cost layer, valuation event, work order cost, variance | AT-FIN-006: cost valuation reconciles material, labour, overhead and landed cost | WP-13 |
| GAR-FIN-007 | Manage budgets, commitments, cash forecast, cost centre and profitability | Budget owner / CFO | Plan, revise, control commitments, forecast and explain variance | Budget version, commitment, forecast, cost centre | AT-FIN-007: committed purchase value reduces available budget according to policy | WP-13 |
| GAR-FIN-008 | Manage VAT, withholding, tax invoices, filings and tax evidence via effective-dated rules | Tax accountant / Tax owner | Determine scope, calculate draft, reconcile, approve filing and retain return version | Tax rule, tax transaction, return, invoice, authority receipt | AT-FIN-008: unapproved/uncertain tax rule remains blocked from posting/filing | WP-13 |
| GAR-FIN-009 | Support FX exposure, exchange-rate source, import payment, export proceeds and revaluation | Treasury/import specialist / CFO | Record source/date/rate, approval and statutory modality; reconcile bank evidence | FX rate, exposure, payment purpose, bank advice, revaluation | AT-FIN-009: foreign-currency payment holds when current NBE modality evidence missing | WP-13 |
| GAR-FIN-010 | Close month/year with subledger reconciliation, consolidation and audit evidence | Chief accountant / CFO | Reconcile AP/AR/stock/payroll/assets; review exceptions; close and lock | Close checklist, reconciliation, consolidation, audit schedule | AT-FIN-010: period close lists unresolved differences and prevents silent changes | WP-13 |

### K. Workforce, payroll, benefits and differentiated compensation — Domain K (WP-14)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-HR-001 | Manage workforce lifecycle, contract, establishment, position, assignment and exit | HR officer / HR director | Hire, verify, contract, assign, update, transfer and exit with approvals | Person, employment, contract, position, assignment, event | AT-HR-001: worker has valid assignment and entity/site before time entry | WP-14 |
| GAR-HR-002 | Manage time, attendance, roster, shift, leave and overtime under approved policy | Timekeeper / HR payroll owner | Capture, correct with reason, approve, reconcile, calculate payable time | Time event, shift, leave, OT request, approval | AT-HR-002: unapproved overtime is flagged, not silently paid | WP-14 |
| GAR-HR-003 | Calculate payroll through effective-dated and locally approved statutory rules | Payroll officer / Payroll controller | Validate classification, period, earning/deduction, calculate, review and approve | Pay run, earning, deduction, rule version, payslip | AT-HR-003: historic payslip reproduces the original rule version | WP-14 |
| GAR-HR-004 | Distinguish employee, creator, reseller, agent, freelancer, supplier and subcontractor status | HR/procurement/tax / Legal owner | Evaluate actual relationship and documents; flag review-required classification | Counterparty, relationship, contract, classification assessment | AT-HR-004: independent reseller rebate is not routed to payroll by label | WP-14 |
| GAR-HR-005 | Manage pension coverage, contribution base, remittance deadline and evidence | Payroll officer / Pension compliance owner | Apply approved coverage test, calculate, reconcile, remit and track exceptions | Pension rule, coverage result, contribution, remittance | AT-HR-005: uncertain salary base or coverage triggers review, not guessed deduction | WP-14 |
| GAR-HR-006 | Support role-specific manufacturing/store/wholesale incentive and commission plans | HR/payroll/commercial / HR director | Approve policy, attribute event, compute, publish breakdown, appeal | Compensation plan, earning event, eligibility, appeal | AT-HR-006: accepted-output line bonus differs from store/wholesale commission trigger | WP-14 |
| GAR-HR-007 | Manage learning, assessment, training plan and certification | Trainer / Learning owner | Assign, deliver, assess, approve competency and expiry | Course, attendance, assessment, certification, validity | AT-HR-007: incomplete training prevents restricted competency authorization | WP-14 |
| GAR-HR-008 | Protect grievance, worker voice, health/safety and fair treatment workflows | HR/HSE / HR director | Record confidential case, restrict access, investigate, resolve and appeal | Case, incident, grievance, confidentiality, outcome | AT-HR-008: confidential grievance visible only to authorized case roles | WP-14 |

### L. Analytics, integration and intelligent operations — Domain L / platform (WP-15)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-ANA-001 | Provide role-based KPI definitions and drill-down to source facts | Executive/functional lead / Analytics owner | Define formula, grain, scope, refresh, owner and lineage | KPI definition/version, semantic model, lineage | AT-ANA-001: dashboard metric reconciles to source transaction detail | WP-15 |
| GAR-ANA-002 | Provide initiative-to-sell-through profitability across style, season, channel and entity | CFO/merchandiser / CFO | Reconcile cost, sell-through, returns, margin and markdown by dimension | Fact model, cost version, sale/return, dimension | AT-ANA-002: profitability includes channel deductions and returns exactly once | WP-15 |
| GAR-ANA-003 | Maintain event contracts, outbox, idempotency, retry and reconciliation | Integration engineer / Platform owner | Publish transactionally; retry safely; quarantine poison events; reconcile backlog | Outbox, event schema/version, consumer receipt, retry | AT-ANA-003: repeated event delivery yields one business effect | WP-15 |
| GAR-ANA-004 | Integrate payment, bank, carrier, marketplace and authority services through adapters | Integration owner / Domain owner | Authenticate, validate, map, handle timeout/rate limit and reconcile | Connector config, request ID, response, external reference | AT-ANA-004: failure/retry does not duplicate payment, shipment or tax submission | WP-15 |
| GAR-ANA-005 | Use AI/optimization only with traceable input, version, confidence and human decision boundary | Planner/analyst / AI governance owner | Generate recommendation, explain factors, show uncertainty, request approval for action | Model version, features, output, explanation, reviewer decision | AT-ANA-005: AI suggestion never posts journal or issues stock autonomously | WP-15 |
| GAR-ANA-006 | Provide operational control-tower alerts and actionable exception queues | Executive/functional manager / Operations owner | Detect threshold/breach, route owner, track SLA and close reason | Alert rule/version, event, owner, resolution, SLA | AT-ANA-006: overdue material/quality/production risk escalates to accountable role | WP-15 |

### M. Platform, security, delivery and reliability — cross-cutting (WP-03/WP-15/WP-16)

| ID | Approved requirement | Primary actor / A | Workflow, decision and exception | Core data / event evidence | Acceptance scenario | Package |
|---|---|---|---|---|---|---|
| GAR-PLT-001 | Implement Laravel modular monolith with explicit bounded contexts and domain ownership | Architect / Chief architect | Enforce module contracts and dependency rules; prevent cross-module table ownership | Module map, dependency manifest, API/event contracts | AT-PLT-001: architecture test rejects forbidden dependency direction | WP-03 |
| GAR-PLT-002 | Implement Angular web experience with accessible role-specific journeys | End user / Product-design owner | Present task-centric screens, validation, loading/error/empty states and keyboard access | Route, feature permission, UI state, accessibility evidence | AT-PLT-002: critical journeys work via keyboard and screen reader semantics | WP-03/WP-16 |
| GAR-PLT-003 | Use PostgreSQL as transaction source of truth with database integrity constraints | Backend engineer / Data owner | Enforce FK, unique, check, transaction and row-level scope; reject invalid state | Relational schema, migration, constraint, transaction | AT-PLT-003: conflicting concurrent postings serialize without double consumption | WP-03 |
| GAR-PLT-004 | Use Redis only for cache, queues, rate limits and expiring coordination—not ownership | Platform engineer / Platform owner | Expire/rebuild cache, retry queue, monitor backlog; recover without data loss | Cache key policy, job receipt, lock, metrics | AT-PLT-004: cache loss does not change transactional truth | WP-03 |
| GAR-PLT-005 | Publish REST APIs with OpenAPI contract, validation, versioning and stable errors | API consumer / API owner | Validate request/response; handle compatibility/deprecation and authorization | OpenAPI document, schema version, API audit | AT-PLT-005: invalid payload receives stable error and causes no partial write | WP-03 |
| GAR-PLT-006 | Provide secure authentication, MFA-ready policy, session revocation and secrets management | User/security admin / Security owner | Authenticate, challenge, expire, revoke, rotate secrets; deny insecure setup | Identity, session, MFA event, secret reference (never secret value) | AT-PLT-006: revoked session/token cannot access API | WP-03/WP-16 |
| GAR-PLT-007 | Enforce tenant isolation through authorization plus database defense-in-depth/RLS | Security tester / Security owner | Apply tenant context transactionally; reject cross-tenant selectors and exports | Tenant context, policy, RLS audit, security test | AT-PLT-007: forged tenant ID cannot cross tenant boundary | WP-03/WP-16 |
| GAR-PLT-008 | Provide observability, correlation IDs, actionable logs, metrics and traces | Operator / SRE owner | Trace request/job/event end-to-end; redact personal/secrets; alert on failures | Log event, metric, trace, correlation | AT-PLT-008: one business transaction is traceable across modules without leaking secrets | WP-16 |
| GAR-PLT-009 | Provide reproducible containers, deterministic dependency lock and CI quality gates | Developer / Release engineer | Install, lint, static analysis, test, build and scan; block release on failure | Lockfiles, image digest, pipeline run, SBOM/scan | AT-PLT-009: clean pipeline builds from approved versioned toolchain | WP-16 |
| GAR-PLT-010 | Provide backup, restore, recovery objectives and disaster-recovery rehearsal evidence | DBA/SRE / Platform owner | Backup, encrypt, verify restore, test recovery and report RPO/RTO | Backup manifest, checksum, restore test, RPO/RTO | AT-PLT-010: restore from backup produces consistent database and object links | WP-16 |
| GAR-PLT-011 | Support controlled migration, rollback/forward repair and release evidence | Release engineer / Technical owner | Version schema, rehearse migration, verify rollback/forward plan, release signed artifact | Migration, release, manifest, rollback evidence | AT-PLT-011: failed migration recovery preserves data and audit consistency | WP-16 |
| GAR-PLT-012 | Meet documented performance, concurrency, availability and retention objectives | Product/operations / Platform owner | Define workload profile, baseline, test, tune and monitor | SLO, load profile, test run, latency/error metrics | AT-PLT-012: representative peak workload passes agreed SLO before production release | WP-16 |

## 4. Cross-domain process acceptance catalogue

These scenarios test the actual end-to-end apparel operating system; they are not isolated module smoke tests.

| Scenario | End-to-end journey and mandatory control points | Principal requirements | Final evidence |
|---|---|---|---|
| E2E-001 Own-brand initiative to retail sale | Initiative → market evidence → brand/collection/range → design → pattern/fit/sample → BOM/tech pack → approved cost → sourced materials → manufacturing route → QC/packing → DC/store allocation → POS sale → return/sell-through feedback | GAR-MER-001..008, GAR-PLM-001..010, GAR-SRC-001..007, GAR-MFG-001..010, GAR-QLT-001..009, GAR-DST-001..011, GAR-FIN-001..010 | Traceable style and product genealogy, margin reconciliation and feedback-to-range link |
| E2E-002 Buyer order / contract manufacturing | Customer RFQ/order → capacity/route feasibility → customer specification and tech-pack revision → material procurement/receipt → production readiness → line plan → routed execution → quality and shipment inspection → packing → dispatch → invoice/collection | GAR-ENT-001..010, GAR-PLM-006..009, GAR-IE-001..006, GAR-SRC-001..010, GAR-MFG-001..012, GAR-OPS-001..008, GAR-QLT-001..009, GAR-FIN-001..010 | Customer order, approved revision, accepted output, packing list, shipment and AR reconciled |
| E2E-003 Outsourced special-process route | Style route includes print/embroidery or wash/dye → subcontract PO/work order → separate material custody → dispatch → processor acknowledgment → output/lot/recipe/certificate → return QC → accepted output and AP settlement | GAR-SRC-007..010, GAR-MFG-002, GAR-MFG-006, GAR-MFG-008, GAR-MFG-011, GAR-QLT-004, GAR-FIN-003 | No material loss hidden; full lot custody, quality release and payable evidence |
| E2E-004 Wholesale and reseller distribution | Customer/retailer qualification → price/credit/contract → allocation/ATP → DC pick/pack/dispatch → invoice → collection → eligible rebate/sell-through claim → returns/credit note | GAR-ENT-004, GAR-MER-007, GAR-DST-004..007, GAR-DST-010..011, GAR-FIN-003..005, GAR-HR-004 | Customer balance, stock, margin, rebate and return reconciled; no payroll misclassification |
| E2E-005 Own-store POS and e-commerce | Store/channel setup → assortment and price → ATP → POS or digital order → payment → pick/ship → delivery → return/refund → stock and cash reconciliation → store sales commissions if applicable | GAR-ENT-003..004, GAR-DST-001..011, GAR-FIN-003..005, GAR-HR-006 | Duplicate payment/order avoided; cash/stock/AR and commission trigger reconciled |
| E2E-006 Hourly production recovery | Approved style SMV/route → capacity/skills/material readiness → hourly plan/actual → line efficiency/WIP/downtime → Andon constraint → maintenance/material/quality response → rebalanced plan → accepted output | GAR-IE-001..006, GAR-OPS-001..008, GAR-MFG-012, GAR-QLT-002..006 | Target assumptions, loss reasons, intervention and accepted output explain the result |
| E2E-007 Statutory payroll and creator/vendor classification | Employee/creator/reseller/subcontractor relationship assessment → rule version and applicability → time/earning or AP/campaign event → draft calculation → qualified review/approval → controlled posting/payment evidence → historical replay | GAR-HR-001..008, GAR-FIN-003, GAR-FIN-008, GAR-ENT-005..008 | Correct payee classification, reviewed rule evidence and no unauthorized statutory activation |
| E2E-008 Tenant and entity isolation | Tenant A and B with multiple entities/sites/channels → execute production, stock, payroll and finance transactions → attempt cross-tenant and cross-site forged access → verify audit, RLS and exports | GAR-ENT-001..010, GAR-PLT-006..008 | All unauthorized access attempts rejected and logged; no cross-tenant disclosure |

## 5. Minimum role and accountability catalogue

The role catalogue is a starting enterprise role taxonomy; tenants configure job titles and scoped permissions without changing the permission semantics.

| Role family | Typical actors | Decision/accountability boundary |
|---|---|---|
| Tenant/platform governance | Tenant owner, tenant admin, identity admin, security admin | Tenant settings, user access, integration setup; cannot bypass domain financial or legal approvals |
| Executive and strategy | CEO/GM, strategy lead, brand director, board delegate | Business initiatives, strategic budgets, policy approvals within delegation |
| Product and merchandising | Product director, merchandiser, planner, designer, pattern maker, technical designer, sample coordinator | Collection/range, design, product specification, fit and release decisions |
| Procurement and suppliers | Requester, buyer, procurement manager, supplier quality, import specialist | Supplier qualification, sourcing, PO/contract and import evidence; maker-checker preserved |
| Factory and IE | Factory manager, department head, line supervisor, IE engineer, planner, operator, maintenance | Method, route, capacity, execution, downtime and line support within competence |
| Quality and compliance | Incoming/inline/final inspector, quality engineer, compliance officer, HSE officer | Inspection, hold/release, CAPA, safety/environment evidence; cannot self-approve prohibited deviations |
| Warehouse and distribution | Storekeeper, inventory controller, DC manager, replenishment planner, dispatcher | Physical custody, movement, stock status and shipment evidence |
| Commercial and channel | Wholesale sales, key-account manager, store manager, cashier, e-commerce manager, reseller manager, customer service | Price/account, sales, tender/POS, return and customer experience within commercial authority |
| Finance and tax | AP, AR, GL, cost accountant, treasury, tax accountant, financial controller, CFO | Journal, tax, payment, reconciliation and period close; distinct duties enforced |
| People and learning | HR officer, recruiter, timekeeper, payroll officer, trainer, assessor, HR director | Employment, time, payroll, capability, benefits, grievance and safety support |
| Audit and assurance | Internal auditor, external auditor, legal reviewer, data protection officer | Read/control evidence and independent verification; no routine transaction write privilege |
| Integration and analytics | Integration admin, analyst, data steward, model approver, SRE | Contract, data quality, KPI, monitoring and AI recommendation governance |

## 6. Source-to-requirement mapping

| Approved source | Requirements |
|---|---|
| `docs/product/domain-map.md` Domains A–L | GAR-MER, GAR-ENT, GAR-PLM, GAR-IE, GAR-SRC, GAR-DST, GAR-MFG, GAR-QLT, GAR-FIN, GAR-HR, GAR-ANA |
| `docs/product/apparel-business-operating-system-charter.md` | GAR-PLM, GAR-IE, GAR-MFG, GAR-OPS, GAR-QLT, E2E-001/002/003/006 |
| `docs/product/distribution-and-channel-commerce.md` | GAR-DST-001..011, E2E-001/004/005 |
| `docs/architecture/platform-standard.md` | GAR-PLT-001..005, GAR-ENT-001..009, GAR-ANA-003..004 |
| `docs/security/saas-security.md` | GAR-ENT-005..008, GAR-PLT-006..008, GAR-PLT-010 |
| `docs/localization/ethiopia.md` and legal source register | GAR-ENT-009, GAR-SRC-008, GAR-FIN-008..009, GAR-HR-003..005, GAR-QLT-007..009 |
| `docs/hr/payroll-and-variable-compensation.md` | GAR-OPS-004..006, GAR-HR-001..008 |
| `docs/engineering/code-quality.md` and `development-deployment.md` | GAR-PLT-001..012 |
| `docs/agent/project-governance-agreement.md` | All requirements; scope, approvals, evidence, sequencing and human-only gates |

## 7. Global acceptance rules

A delivery package may be called complete only when:
- Every assigned requirement has implementation/specification evidence and a passing acceptance result or an explicitly documented legal/operational hold.
- No requirement is silently removed, narrowed, substituted or left unowned.
- Every negative and exception path is tested, not just the happy path.
- Security scope is enforced server-side and verified with cross-tenant negative tests.
- Source-to-target transactions reconcile and are idempotent.
- Historical evidence remains reproducible.
- Legal requirements are tied to official instruments and effective dates and held for professional approval where necessary.
- Any AI/optimization result is explainable, bounded and human-approved before it can create a consequential transaction.
- Gaps are listed with exact IDs and a safe continuation plan.

## 8. WP-01 handover

**Outcome:** 120 stable requirement IDs, eight end-to-end acceptance journeys, role accountability taxonomy, traceability rules and source mapping have been established from the current approved baseline.

**Not claimed:** no application module, database migration, executable automated test, production security assessment or legal opinion has been completed by this requirements-inventory document alone. Those are assigned to WP-02 through WP-16.

**Open dependencies:** WP-02 must resolve official-law source/version and applicability status; WP-03 establishes tenant, identity and audit foundations; downstream packages deliver each mapped workflow in order.

**Decision:** WP-01 is internally accepted under the Product Owner's autonomous delegation. Proceed to WP-02 without a routine owner pause.
