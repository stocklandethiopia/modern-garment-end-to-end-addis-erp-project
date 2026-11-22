# End-to-End Garment ERP Domain Map

This is the product capability map, not a claim that every capability has already been implemented.

**Governing design standard:** This map must be interpreted and extended in line with the [Apparel Business Operating System Charter](apparel-business-operating-system-charter.md). Every workflow must improve how apparel work is planned, executed, controlled, learned from and proven—not merely collect completed transaction data.

## A. Business initiative, brand strategy and product innovation

The business lifecycle begins before a buyer order and before a factory work order. The system must support companies that own brands and stores, create their own collections, buy or outsource manufacturing, operate factories, distribute other brands, or combine these models. Customer/buyer orders are one demand source—not the only reason a product is created.

### Strategy, market intelligence and initiative
- Strategic objectives, business model, brand portfolio, target customer segments, geography, price positioning, value proposition and channel strategy.
- Consumer/customer insight, store feedback, sell-through, returns reasons, fit complaints, competitor/market research, trend intelligence, seasonality and product-performance history.
- Product opportunity and initiative intake: idea, problem/opportunity, target wearer, usage occasion, product gap, concept brief, expected demand, target retail/wholesale price, target margin, sustainability intent, launch window and strategic fit.
- Initiative scoring and portfolio governance: expected sales/margin, brand fit, differentiation, development effort, supply risk, capacity, cash/working-capital impact and environmental/social impact.
- Stage-gated portfolio decisions: explore, approve for concept, prototype, range review, launch, hold, redesign or retire; preserve rationale and decision evidence.
- New product development is initiated by internal business strategy, design/innovation, store/customer feedback, seasonal collection planning, replenishment opportunities, licensing/brand partnerships or external buyer briefs.

### Brand, collection and assortment planning
- Brand architecture, product families, category hierarchy, season/drop, collection capsule, target customer, intended channel, launch dates and lifecycle.
- Range plan and line plan: style count, options, colorways, size curves, planned quantities, price architecture, target margin, open-to-buy/budget, capacity and material exposure.
- Assortment planning by channel, region, store cluster and customer segment; distinguish core/replenishment products, seasonal fashion, fashion-risk products and made-to-order/buyer-specific styles.
- Range review and assortment decisions with visual boards, financial roll-ups, demand scenarios and store/wholesale feedback.
- Product lifecycle from idea and development through launch, replenishment, markdown, end-of-life, archive and possible re-issue.

## B. Enterprise and platform foundation

- Tenant registration, trial, subscription, plan limits, feature entitlements and tenant lifecycle.
- Multiple legal entities, brands, business units, factories, branches, departments, cost centres and warehouses.
- Organization charts, approval delegation, fiscal periods, calendars, currencies, units, languages and numbering rules.
- Identity, MFA, SSO-ready architecture, user provisioning, roles, permissions, data scope and audit.
- Configurable fields, forms, document templates, workflows and notification preferences.
- Integration registry, API keys, webhooks, import/export jobs and monitoring.

## C. Merchandising and commercial execution

Merchandising connects the product strategy and development portfolio to commercial demand, buyers/accounts, channels, orders and profitability. It supports both own-brand/own-store products and buyer-specific development.

### Customer, buyer and account merchandising
- Buyer/customer onboarding, brand and retailer profiles, buying offices, agents, wholesalers, retail accounts, distributors and reseller partners.
- Customer hierarchy, bill-to/ship-to, buying contacts, territories, seasons, target markets, delivery windows, compliance requirements and communication history.
- Buyer enquiry/RFQ, product brief, quotation, target cost, price negotiation, sample request, approval status, order confirmation, amendments and cancellation.
- Order book, customer PO, style/colour/size breakdown, ratio, quantity, delivery schedule, Incoterms/ship mode, chargebacks/claims and buyer scorecards.
- Merchandising critical path with task owner, dependencies, planned/actual dates, approval evidence, late-risk forecast, escalation and next action.

### Range, style and assortment execution
- Connect the approved collection/line plan to style masters, colorways, size ranges, variants/SKUs, planned quantities, target retail/wholesale price, margin and intended sales channels.
- Coordinate design, technical development, sampling, costing, material sourcing, production, quality, distribution and launch readiness on a shared style/order timeline.
- Maintain order-independent development for own-brand collections, replenishment/core products, store-exclusive products and new initiatives; a buyer order can also initiate a buyer-specific style.
- Use store sell-through, returns, customer feedback, wholesale sell-in/sell-through (where available), markdowns and margin to inform next-season range decisions.
- Track product lifecycle, launch/drop date, channel assortment, replenishment eligibility, markdown/clearance and end-of-life.

### Commercial planning and profitability
- Target and actual sales, gross margin, contribution margin, channel mix, open-to-buy, inventory exposure, demand scenarios and commercial risk.
- Quote/order costing linked to approved BOM, route, SMV, supplier quotes, landed cost, process/subcontract costs, freight, duty, overhead and margin assumptions.
- Manage price changes, customer agreements, discounts, rebates, commissions and approvals with effective dates and full audit history.
- Show the commercial consequences of product design choices, material substitutions, process routes, collection mix, store allocations and order amendments.

## D. Design studio, product development, PLM and industrial engineering

Product development is an in-house creative and technical discipline. It must not be reduced to copying buyer specifications or entering a style/BOM.

### Design studio and product creation
- Creative briefs, mood/inspiration boards, design concepts, sketches, technical flats, artwork, prints, graphics, embellishments, trims and design review.
- Design libraries, reusable blocks, silhouettes, construction standards, fit blocks, pattern libraries, material libraries and approved component libraries.
- Design-to-cost and design-for-manufacture feedback: material availability, construction complexity, operation count, SMV, machine capability, expected yield, wash/finish effects and target cost.
- 2D CAD pattern creation, digitization, pattern revision, grading rules, marker/CAD integration and controlled pattern-piece identity.
- 3D garment visualization, avatars/body measurements, digital drape/material properties, fit simulation, virtual samples, render review and 3D comment/approval rounds. Preserve source files, version, linked pattern/tech-pack revision and reviewer decisions.
- 3D is a development accelerator and decision aid; physical fit/sample approval remains required when risk, customer policy, material behavior or product category calls for it.

### Product engineering and PLM
- Product master with style, style revision, colorway, size range, fit block, variant/SKU and effective-dated product lifecycle.
- Technical specification, measurement points/tolerances, grading rules, seam/stitch/construction instructions, artwork placements, label/care instructions and packaging requirements.
- BOM at component level: fabric, lining, interlining, trims, labels, thread, buttons, zips, elastics, packaging and approved substitutes; quantity, wastage, unit, color/size applicability, source and revision.
- Tech pack revision control, approved sample references, pattern and artwork references, material approvals, cost sheet, operation bulletin, routing and change-impact analysis.
- Sample room workflow for concept/proto, fit, size set, sales/marketing, photo, pre-production and counter samples as appropriate; sample request, pattern/material reservation, sample-room capacity, comments, fit session, measurements, pass/fail, revision and approval.
- Fit team feedback includes designer, technical designer, merchandiser, fit model and quality/production perspectives; record movement/comfort/tactile and measurement observations, not only a fit status.
- Pre-production readiness gate: approved final tech pack, pattern/grade, BOM, color/material/trim standards, validated cost, routing/SMV, sample approvals, quality plan, packaging and launch/capacity plan.
- Production handover is a controlled snapshot. Bulk production must not silently consume a draft or superseded tech pack.

### Industrial and manufacturing engineering
- Method study, operation bulletin, time study, standard minute/standard allowed minute value (SMV/SAM), rating/allowance basis, effective date, study evidence and revalidation.
- Workstation method sheets, machine/attachment requirements, skill grade, ergonomic requirements, quality-critical points, cycle time and approved method revision.
- Line balancing, takt/cycle-time comparison, bottleneck detection, work-content distribution, operator/machine allocation, layout simulation and WIP-cap recommendations.
- Capacity planning by department, work centre, line, shift, machine family, process/recipe, skill pool, maintenance availability and product route.
- Style changeover and setup study, quick changeover (SMED), learning curves, efficiency ramp, alternative methods and controlled improvement proposals.
- IE changes require a measured before/after baseline, approved new method/SMV, training and effective date; preserve historical standards for old production.


## E. Procurement, sourcing, supplier governance and materials

Procurement is a controlled source-to-pay discipline. It must protect product quality, continuity of supply, cash, legal compliance and ethical sourcing—not simply create purchase orders.

### Demand, planning and sourcing
- Aggregate demand from initiatives, collections, buyer orders, production plans, stores, DC replenishment, maintenance, facilities and service operations.
- Purchase requisition, budget/commitment check, category strategy, make/buy decision, approved source list, RFQ/RFP, quotation comparison, negotiation and sourcing recommendation.
- Supplier onboarding and due diligence: legal identity, registration/business licence, TIN, tax/VAT status, bank ownership, beneficial ownership where required, sanctions/blacklist screening where applicable, capacity, quality systems, social/environmental risk and conflicts of interest.
- Supplier segmentation by fabric mill, trim/accessory provider, packaging supplier, chemical supplier, equipment/service provider, subcontract processor, carrier, marketing/content agency, consultant and other service providers.
- Supplier scorecards: quality, delivery reliability, lead time, cost, responsiveness, claims, traceability, compliance and improvement actions.
- Approved vendor list, supplier contracts, framework agreements, price validity, MOQ, lead-time, Incoterms, currency, warranty, delivery terms, service levels, retention, rebate and contract performance.

### Purchase-to-pay controls
- Purchase requisition → delegated approval → sourcing event → award → purchase order/contract → order acknowledgment → ASN → goods/service receipt → inspection/acceptance → invoice verification → withholding/VAT review → payment proposal → bank payment → reconciliation.
- Enforce delegated authority, role separation, budget availability, supplier bank-change verification, duplicate invoice detection and three-way match (PO, accepted receipt/service confirmation, supplier invoice) with controlled exceptions.
- Keep goods procurement, services, subcontract conversion, capital expenditure, freight, customs/duties and staff reimbursements distinct.
- Record contract deliverables, milestone certification, service acceptance evidence, retention/advance recovery and dispute status.
- Supplier invoice controls must validate TIN/licence, tax invoice requirements, tax/VAT category, applicable withholding rule, currency and bank/payment controls using the transaction-date rule version.
- Maintain audit evidence for sourcing decisions, quotation comparison, conflict declarations, approver, contract, goods receipt, acceptance, invoice and payment.

### Apparel material and subcontract sourcing
- Fabric: mill, construction, composition, GSM, width, finish, color, dye lot, shade, roll, shrinkage, testing, defects, country of origin, MOQ, lead time, price and approved standard.
- Trims/accessories: SKU, brand/customer approval, artwork/label text revision, size/color, lot, supplier, quantity, packaging and component compatibility.
- Chemicals/wash/print/embroidery inputs: approved chemistry/recipe, restricted-substance documentation, safety data, expiry, lot, storage and process eligibility.
- Subcontract processing: controlled job issue, material custody, process route, approved recipe/artwork, expected yield, delivery schedule, quality plan, loss/damage responsibility, receipt, rework and settlement.
- Fabric roll/lot and trim/accessory procurement, receipt, inspection, issue, return and consumption remain separate traceable flows.

### Import, customs and foreign currency
- Import requisition, pro-forma invoice, supplier contract, bank FX application, permitted payment modality, letter of credit/cash-against-documents/advance where allowed, customs declaration, HS code, origin, tariff, customs valuation, duty/tax, freight, insurance, port/transport, release and landed cost.
- Capture exact NBE foreign-exchange directive and amendment set applicable on transaction date; do not rely on an old static import procedure.
- Reconcile foreign supplier invoice, bank debit, FX rate, customs value, duty/VAT, clearing-agent charge and inventory capitalization; preserve source documents and approved conversion rates.
- Export and re-export workflows must preserve customs regime, export proceeds, retention account rules, certificates, shipping evidence and buyer collection records.

### Procurement governance boundary
- Federal Public Procurement and Property Administration Proclamation No. 1333/2024 is a public-procurement regime. Do not impose it automatically on ordinary private apparel tenants. Apply it only where the customer/legal entity is within its statutory scope (including relevant public enterprises) and the responsible counsel has confirmed applicability.
- Private-sector tenants still need strong internal procurement governance, contract law, tax, licensing, competition, anti-fraud, delegated authority and documented sourcing controls. Internal best-practice controls must be labelled company policy, not misrepresented as a public-procurement statutory mandate.
- Never split orders, invoices or contracts to evade approval thresholds, tax withholding, customs or procurement controls; detect related transactions and route exceptions for review.

## F. Warehouse and inventory control

- A unified, multi-site inventory model for raw materials, fabric rolls, trims, packaging, WIP, finished goods, spare parts, consumables, samples, scrap and returns.
- Physical and logical locations: receiving docks, inspection/quarantine, bulk storage, racks, bins, production supermarkets, WIP zones, finished-goods staging, central distribution centres, regional depots, branch stockrooms, retail backrooms, shop floors and returns areas.
- Warehouse execution: ASN/pre-advice, receiving, count/quality checks, put-away, directed replenishment, picking, wave/batch picking, packing, staging, loading, dispatch, cycle count and stocktake.
- Inventory identity and traceability: SKU/style/variant, size, colour, barcode, serial where relevant, batch/lot, fabric roll, shade, supplier lot, country of origin, expiry and handling attributes.
- Inventory states and controls: expected, received, inspection, quarantine, available, reserved, allocated, picked, packed, in-transit, damaged, blocked, returned and disposed.
- Stock ledger, reservation ledger, valuation layers, FIFO/FEFO or configured costing policy, ageing, slow-moving stock, negative-stock controls, reconciliation and full source-document traceability.
- Stock ownership and physical custody are separate concepts. Support company-owned, consignment, customer-owned, supplier-owned and third-party logistics stock without misrepresenting ownership.
- Inter-warehouse and intercompany movements use controlled transfer orders, dispatch confirmation, in-transit visibility, receipt confirmation, discrepancy handling and finance posting where applicable.
- Roll-level fabric traceability, shade segregation, width/weight/shrinkage, lot genealogy and production consumption reconciliation.
- WMS capabilities must support central DCs and smaller branches/stores without requiring every stock point to operate a full-scale warehouse.

## G. Distribution network, order management and channel commerce

Distribution is a first-class business domain, not merely the last step after production. The ERP must manage the movement and sale of finished products from factories and central distribution centres to the business's own branches/stores, wholesale customers, retailers, resellers and end customers.

### Distribution network and fulfilment topology
- Configurable network of factories, factory warehouses, central distribution centres (DCs), regional DCs, wholesale depots, cross-dock points, branch warehouses, company-owned retail stores, outlet stores, returns hubs and third-party logistics locations.
- Each node has a type, operating calendar, service territory, capacity, inventory policy, fulfilment capability, allowed channels, cut-off times and responsible legal entity/branch.
- Model a branch as an organizational/commercial unit and its stockrooms or retail stores as separate inventory/fulfilment locations. Do not force a one-to-one relationship between branch and warehouse.
- Support central-DC-to-branch replenishment, DC-to-wholesaler, DC-to-retailer, DC-to-reseller, direct-to-customer, store-to-customer, store-to-store and supplier/manufacturer drop-ship flows.
- Network sourcing rules can prioritize a designated DC, regional proximity, available-to-promise quantity, customer territory, delivery promise, channel restrictions, fulfilment cost or operational capacity.
- Support single-node fulfilment, split shipments, backorders, partial delivery, cross-docking, ship-from-store and click-and-collect where enabled by tenant policy.

### Sales channels, accounts and commercial policies
- Channel types include business-to-business wholesale, retailer accounts, reseller/dealer/distributor networks, company-owned retail/POS, outlet/clearance, direct-to-consumer, e-commerce and marketplace integrations.
- A party can hold multiple roles (for example, a customer that is also a reseller); channel role, account hierarchy, ship-to/bill-to relationships and permissions must be explicit.
- Customer onboarding, territories, sales representatives, account groups, credit limits, payment terms, tax profiles, delivery terms, contracts, rebates, commissions and customer-specific agreements.
- Channel-specific assortment, price lists, discounts, promotions, minimum order quantities, case packs, order multiples, margin floors, credit rules and approval limits.
- Wholesale/retailer/reseller price tiers and negotiated agreements must coexist with retail shelf prices and POS promotions. Price calculation must be deterministic, explainable and auditable.
- B2B portal/API/EDI order intake, sales-representative orders, manual orders, retail POS transactions, e-commerce and marketplace orders feed one governed order-management lifecycle.

### Order management and inventory promise
- Sales quotations, order capture, customer PO matching, order validation, credit/payment checks, allocation, fulfilment sourcing, picking, packing, dispatch, proof of delivery, invoicing and settlement.
- Available-to-promise (ATP) and, where supported, capable-to-promise (CTP) must account for on-hand stock, reservations, safety stock, incoming supply, production dates, channel allocation, location and delivery lead time.
- Order orchestration supports approval holds, stock reservation, allocation release, substitutions with approval, partial fulfilment, split delivery, backorder, cancellation and exception resolution.
- Preserve customer PO, sales order, shipment, invoice, payment, return and credit-note links end to end. Prevent duplicate submission and duplicate posting through idempotency.
- Maintain channel, customer, campaign, sales-person, territory, branch, fulfilment-node and product dimensions for margin and service reporting.

### Replenishment and branch/store operations
- Forecast- or rule-based replenishment from central DC to regional DC, depot, branch and store using min/max, reorder point, days of cover, seasonality, sell-through, service level and allocation rules.
- Replenishment proposals, manual overrides, approvals, transfer orders, dispatch, in-transit tracking, receipt, shortage/overage and put-away.
- Allocation of scarce or new-season stock across wholesale commitments, key accounts, resellers, own retail stores and direct channels using approved priority policies.
- Branch/store stock visibility, stock counts, inter-store transfers, damaged/obsolete stock, markdown proposals, stock ageing and retail backroom-to-shelf movements.
- Retail POS must cover tills/registers, cashier shifts, opening floats, cash/card/mobile-payment tender, receipts, discounts, returns, exchanges, refunds, end-of-day reconciliation and offline transaction synchronization where enabled.
- Support store-specific assortment, price zones, promotions, tax/receipt rules and sales targets, with central governance and authorized local overrides.

### Delivery, returns and reverse logistics
- Carrier/3PL selection, dispatch planning, route/stop planning, shipment tracking, delivery appointments, proof of delivery, failed delivery and freight-cost capture.
- Customer returns authorization (RMA), reason codes, inspection/grading, restock, repair, quarantine, supplier claims, resale, markdown, scrap or disposal.
- Refunds, exchanges, credit notes, replacement orders, warranty/quality claims and financial reconciliation must link to the original sale and inventory movement.
- Track return-to-origin, inter-branch returns, wholesale claims, retailer claims and consumer returns with separate policy and approval controls.
- Reconcile physical dispatch, customer receipt, invoicing, cash collection and stock ownership; exceptions must be visible and auditable.

### Distribution performance and profitability
- DC/branch/store stock availability, fill rate, OTIF, order cycle time, picking accuracy, delivery success, stock cover, sell-through, shrinkage, returns rate and inventory turns.
- Contribution margin by product/style, customer, wholesale account, retailer, reseller, branch, store, channel, campaign and fulfilment node, including freight, discounts, rebates, commissions and returns.
- Consolidated operational and financial visibility across factories, central DCs, branches, stores and external channels while preserving legal-entity and tenant boundaries.

## H. Manufacturing operations and department execution

Manufacturing is a configurable network of specialist departments, work centres and external processors. Each section has its own process SOPs, material rules, inputs/outputs, skills, capacity, quality checks and accountability. They connect through controlled hand-offs, not through one generic "production status" field. A product's routing is selected and versioned by style, material, finish, customer/brand requirement and site capability.

### Production planning and daily control
- Demand and order portfolio review, make/buy/subcontract decision, master production schedule, material readiness, capacity/load plan, production calendar and due-date risk.
- Production order release only after the relevant readiness gates; line/department assignment based on skills, machine/attachment capacity, process capability, material/trim availability, changeovers, planned maintenance and priority.
- Style/route/SMV-specific line plan by shift/day: available direct-labour minutes, approved target efficiency, style SMV/SAM, planned line headcount, machine constraints and learning curve.
- Calculate a transparent starting target using productive labour minutes × approved target efficiency ÷ garment SMV. Adjust or annotate only through approved, reason-coded planning assumptions for absenteeism, bottlenecks, style learning, material shortage and planned downtime.
- Daily and hourly plan-versus-actual by factory, department, production order, style, line, shift and operation. Show units, earned standard minutes, direct labour minutes, line efficiency, first-pass yield, defects, rework, downtime, WIP, bottleneck, shortage and projected completion.
- Visual line boards and mobile dashboards for operators/supervisors/IE/quality/maintenance; alerts and Andon escalation when output, quality, material or machine conditions breach defined thresholds.
- Workload leveling, small-lot/one-piece or controlled bundle flow, Kanban replenishment and WIP caps where product and process are suitable; batch/lot processing remains available when chemistry, equipment, process economics or product risk requires it.
- Production meeting cadence: pre-shift readiness, daily start-up/stand-up, hourly review, end-of-shift reconciliation and cross-functional order-risk review.

### Fabric receiving, inspection and cutting-room preparation
- Receive rolls against PO/ASN; capture supplier lot, roll ID, shade/dye lot, width, weight, length, defects, test results and storage conditions.
- Fabric inspection with configurable point/system, defect map, score, shade banding, relaxation/shrinkage tests, usable width, quarantine, claim and acceptance.
- Fabric issue is its own controlled transaction: issue exact roll/lot/shade/length/weight to a production order, lay/marker/cutting table; capture planned versus actual consumption, remnants, returns and scrap with reason.
- Fabric allocation must respect shade/lot compatibility, roll length, usable width, shrinkage, directionality, face/nap, matching panels and customer-specific rules.
- Cutting plan: size/color ratio, marker version, marker efficiency, lay quantity/height, fabric utilization, spreading method, table/cutter capacity, lay ticket and expected panel yield.
- Spreading and relaxation controls, ply counting, tension/edge alignment, splice/defect avoidance, end loss, marker placement and pre-cut verification.
- Cutting execution by lay/layer/size/color, machine/operator, cut start/end, panel count, recuts, defects, remnants and actual consumption; link cut-piece identity back to fabric roll/lay.
- Numbering, panel matching, size/color segregation, bundle creation, bundle ticket/barcode, bundle quantity and component completeness checks.
- The cutting department issues completed, reconciled cut bundles/panels to the designated next route node—often a sewing line, but printing/embroidery or another process may occur before sewing. Cutting does not silently consume or post trim stock.

### Trim, accessory and component issue
- Trims are issued separately from fabric and separately from finished cut bundles, against style BOM, order quantity, operation/line need and approved consumption.
- Control trim SKU, supplier/batch/lot, color/size, shade where relevant, label revision, care content, brand/customer approval, quantity, issue unit, line/department and return/unused balance.
- Kit issue for thread, buttons, zips, elastic, labels, interlining, tags and packaging components may be planned by order, line, bundle or operation according to the item and control risk.
- Prevent wrong-label, wrong-size, wrong-color and unapproved trim issue with barcode/scan validation and pre-production checklist.
- Record shortage, substitute request, approval, partial issue, return, scrap and consumption variance without mixing fabric and trim stock ledgers.

### Printing, embroidery and embellishment
- Configurable placement in the route: before cutting, after cutting and before sewing, or after sewing, depending on product/process.
- Artwork/embroidery file revision, placement template, color/thread/ink, screen/digital file, machine, recipe/parameters, operator, subcontractor and approval sample.
- First-piece/mock-up approval, test panel, registration/placement/dimension/color checks, process batch, output and defect/rework capture.
- Recipe/parameter controls for time, temperature, pressure, curing and other relevant process settings; capture actual values, not only a tick-box completion.
- Trace panels, bundles and affected quantities through rework or reprocessing.

### Sewing-line execution
- Line setup from approved operation bulletin/routing: layout, machine type, attachments/gauges, folders, needles, thread, work aids, skill coverage, target pitch, WIP limits and quality control points.
- Pre-production meeting and first-output/first-piece approval before normal bulk run; record unresolved risks and who accepted the start.
- Assign operators to operations using current competency/skill matrix, certification, work restrictions, attendance, machine availability and balanced work content.
- Bundle or piece tracking through operation sequence; each issue/receipt records source/destination station, quantity, good, defect, rework, lost/missing pieces and time.
- Hourly output and WIP visibility at line and operation level; identify bottleneck, starvation, blocking, excessive WIP, unbalanced work content and abnormal downtime.
- Inline quality at source, operator self-check and escalating quality gate; contain suspected defects before passing to the next operation.
- End-line inspection/reconciliation, repair queue, reinspection and controlled release to the next process. Preserve defect origin and responsible process without creating a blame-only culture.

### Washing, dyeing and wet/dry processing
- Optional, route-controlled in-house or outsourced operations. Not every style needs garment washing, dyeing, distressing, curing or chemical treatment.
- Approved recipe library and revision, sample/mock-up approval, machine/load capacity, garment/batch identity, chemical issue/return, water/energy use, time, temperature, pH/measurement and process parameters as applicable.
- Batch loading, recipe execution, extraction, drying, intermediate checks, additional dry/wet process, curing and post-process quality tests according to the approved route.
- Track recipe deviation, shade/color migration, damage, dimensional change, hand-feel, shrinkage, stains and reprocess disposition.
- Chemical safety, restricted-substance evidence, PPE, storage compatibility, effluent/environmental controls and local compliance approvals are mandatory configurable controls, not optional free-text notes.
- Outsourced processors receive a controlled subcontract job and material custody record; reconcile sent/received goods, process yield, loss, damage, invoices and quality results.

### Finishing, pressing and final presentation
- Separate process steps for thread trimming, stain removal, repair, attachment/accessory completion, measurement, appearance inspection, folding preparation and presentation.
- Pressing methods and equipment chosen by product construction and material: under-pressing, seam/shaping press, final pressing, steam/vacuum/heat/pressure where applicable, cooling/form retention and safe parameter control.
- Finishing work instructions contain sequence, machine/settings, temperature/pressure/time where relevant, pressing form, fabric restrictions, defect examples and acceptance criteria.
- Check appearance, workmanship, measurement/tolerance, shade, labels, care information, missing attachments, cleanliness and customer-specific finish.
- Nonconforming garments route to repair, re-press, rewash, re-inspect, downgrade, quarantine or scrap under authorization.

### Packing and shipment readiness
- Order/style/variant/size/color/carton assortment plan, folding method, tags, hangers, polybag, desiccant/accessory and carton specification.
- Scan-verified piece-to-carton-to-pallet/container genealogy; carton ID, contents, gross/net weight, dimensions, marks, destination and seal data.
- Packing quality checks, size/color reconciliation, carton audit, metal detection where required, shipment audit and final release.
- Finished goods become available to warehouse/distribution only after required process and quality gates pass; exceptions remain in hold/quarantine.

### Department-specific operating results
Each department reports its own planned/actual quantity, accepted quantity, yield, defects, rework, waste, cycle time, WIP, resource use, safety/maintenance events and hand-off status. A central control tower consolidates them without erasing departmental detail.

## I. Quality assurance, process control and compliance

Quality is designed into product definition, supplier/material approval, every manufacturing process and customer delivery. A final inspection alone is not a quality system.

### Quality planning and specifications
- Quality plan linked to style revision, buyer/brand specification, material, product risk, route, process and market.
- Approved measurement tables/tolerances, workmanship standards, stitch/seam requirements, appearance references, shade/colour standards, print/embroidery positions, wash/finish standards, label/care requirements and packaging criteria.
- Control plans define what is checked, sampling/frequency, method/tool, acceptance criteria, owner, evidence, reaction plan and release authority.
- Gauge/equipment calibration, inspector competency, sample reference control and quality-method revision.

### Quality at source and stage gates
- Supplier qualification, fabric/trim incoming inspection, fabric defect mapping, shade/lot segregation, material testing and quarantine/release.
- First-piece/first-off approval at cutting, printing, embroidery, sewing, washing/dyeing, pressing, finishing and packing where relevant.
- In-process/inline and end-line quality checks, self-inspection, independent verification, AQL/final inspection and shipment audit using configurable plans.
- Defect containment and stop/hold/andon action before nonconforming work moves downstream; authorized rework/reinspection or disposition.
- Separate acceptance gates for fit/measurement, shade/colour, workmanship, process recipe/parameters, appearance, safety, packaging and shipment readiness.

### Defect, rework and corrective action
- Standard defect taxonomy, severity, defect location, detection process, source lot/bundle/operator/line/machine/recipe, quantity inspected and quantity affected.
- Repair, rework, rewash, re-embroidery, recut, reject, downgrade, scrap, concession and supplier/carrier claim routes with approvals.
- Root cause analysis (why-why, Pareto, fishbone/5-why), corrective/preventive action (CAPA), responsible owner, due date, evidence and effectiveness verification.
- First-pass yield, defects per unit, repair/rework minutes, reject rate, cost of poor quality, recurrence and process escape analytics; avoid blame-only reporting.

### Compliance and release
- Product safety, restricted-substance/chemical evidence, social/environmental compliance, audit findings, corrective action and expiry alerts.
- Process-specific chemical handling, PPE, ventilation, water/energy/effluent and waste controls where relevant.
- Shipment inspection, packing/carton audit, customer/buyer compliance evidence, export/shipping documents, certificate and release traceability.

## J. Finance, accounting, treasury, tax and financial governance

Finance must operate across manufacturing, procurement, wholesale, retail, stores, distribution, payroll, commissions, returns and intercompany entities. It is not an invoice-printing module.

### Accounting and financial control
- Multi-entity chart of accounts, legal-entity and branch dimensions, cost/profit centres, journals, GL, AP, AR, fixed assets, depreciation, bank/cash, budgets, commitments, accruals, provisions, close and consolidation.
- Double-entry accounting, immutable posted journals, reversal/adjustment workflows, reconciliations, maker-checker, segregation of duties, approval limits and audit trails.
- Accounting framework by reporting entity, effective date and AABE adoption status (IFRS or IFRS for SMEs where applicable); support financial statements, notes, audit trail and controlled reporting packs.
- Cost accounting: garment/style/order cost, material consumption, labour, SMV/earned minutes, overhead absorption, subcontract, energy, freight, duty, wastage, rework, scrap, variance and actual contribution margin.
- Manufacturing costs and commercial channel margins must remain reconcilable without merging their distinct cost drivers.

### Ethiopian tax and statutory finance
- Configurable tax registrations and profiles for each legal entity and taxable activity; distinguish federal, regional and city revenue authority jurisdiction.
- Output/input VAT, exempt/zero-rated/out-of-scope classification, tax invoice, credit/debit note, VAT return, refund, tax withholding, employment income tax, business income tax, turnover/gross sales tax where applicable, minimum alternative tax, advance tax, customs duty, excise where applicable, interest, penalty and tax audit/dispute evidence.
- Supplier/customer tax checks: TIN, licence, VAT registration, invoice authenticity/status, withholding category, threshold, applicable rule version and tax certificate.
- Tax calculations use the law, regulation, directive, authority, effective date, tax year, transaction category, taxpayer category and approved interpretation applicable to the transaction; never assume all group entities have identical rates or filing obligations.
- Electronic tax-invoice and tax-invoice administration support must follow the current Ministry of Revenues directive and authority integrations; keep invoice numbering, signature/QR/validation, cancelled/reissued invoice and audit evidence as required by the active rule.
- Preserve tax return workpapers, source invoices, declarations, withholding certificates, payment receipts, tax assessment, objections, appeal and resolution timeline.

### Banking, cash and foreign exchange
- Bank accounts, mandates/signatories, payment batches, bank reconciliation, cash counts, card/mobile payment settlement, POS acquirer fees, refunds, merchant settlement and cash-in-transit.
- Multi-currency AP/AR, import/export FX application, approved bank rate, NBE daily indicative rate for reference, transaction-date conversion, realized/unrealized FX gains/losses and foreign currency retention.
- FX rule registry must include the base NBE FXD/01/2024 and the current amendments FXD/03/2025, FXD/04/2026 and FXD/05/2026, plus relevant modality-specific directives such as FVD/01/2026. Do not hard-code an advance-payment limit or import modality without checking the current amendment.
- Intercompany loans, balances, charges, stock transfers, transfer pricing and consolidation require entity-level evidence and locally approved tax/accounting treatment.

### Commercial and channel finance
- Customer credit, account statements, wholesale pricing, rebates, sales commissions, reseller/dealer incentives, retail POS tender, gift cards/vouchers, promotions, refunds, chargebacks, returns and customer balances.
- Revenue recognition, inventory cost of goods sold, discounts, sales returns, freight, channel fees, influencer campaign cost and partner settlements must link to original orders/campaigns and legal entities.
- Separate customer trade discounts/rebates and reseller incentives from employee payroll compensation and from independent service-provider invoices.
- Store/DC/branch profitability with inventory turns, stock losses, write-downs, markdowns, returns, freight and working capital.

### Finance compliance and closure
- Effective-dated tax calendars, filing obligations, evidence checklist, source-document retention, reconciliation tasks, preparer/reviewer, due dates and escalation.
- Tax audit readiness, supporting schedules, general-ledger to tax-return reconciliation, risk register, assessments, objections and appeal workflow.
- Finance close cannot be certified while material unresolved VAT, withholding, customs, payroll, stock valuation, bank or intercompany reconciliations remain without an approved exception.

## K. Human resources, workforce, payroll and people development

HR is a major enterprise domain. Payroll must model different workforce populations and compensation arrangements without confusing employee wages, sales commissions, supplier invoices, reseller rebates and digital-creator income.

### Workforce lifecycle
- Workforce demand, headcount budget, organization design, job architecture and manpower requisition.
- Recruitment, applicant tracking, interview, selection, offer, contract and onboarding.
- Employee master, personal/emergency contacts, qualifications, experience, documents and consent.
- Position, grade, job family, department, supervisor, location, employment type and contract lifecycle.
- Permanent, fixed-term, temporary, seasonal, part-time, probationary, apprentice/trainee and other legally recognized employment arrangements, with classification and eligibility rules approved against current labour law.
- Probation, confirmation, transfer, promotion, secondment, disciplinary case, grievance and exit.
- Employee self-service, manager self-service, HR case management, social dialogue, employee relations and worker voice.

### Time, pay and benefits
- Shift patterns, roster, attendance, device integration, lateness, absence, rest periods and overtime approval.
- Leave types, entitlement, accrual, carry-forward, encashment and leave settlement.
- Payroll calendar, salary structure, earnings, deductions, allowances, benefits, loans and advances.
- Monthly salary, hourly/daily wage, shift allowance, approved overtime, piece-rate/quantity-based earning, skill/qualification premium, team/line incentive, individual incentive, store sales commission, sales bonus and management bonus as separate configurable earning types.
- Payroll calculation, validation, approval, payslip, bank payment file, journal posting, statutory submission and reconciliation.
- Pension, employment income tax, withholding, benefits, employment injury, severance/final settlement and statutory returns under approved local rules.
- Payroll corrections, retroactive adjustments, off-cycle pay and full audit history.

### Role- and business-specific compensation schemes
- Manufacturing operators and production support: fixed wage/salary plus any approved operation/piece/line/team incentive, skilled-operation premium, certification allowance or approved shift/overtime payment. Productivity measures must be linked to approved SMV/SAM, accepted output, time evidence, quality, actual staffing and fair-work safeguards.
- Sewing/cutting/wash/finishing/packing lines: daily/shift line target and actual, earned standard minutes, direct-labour minutes, line efficiency, first-pass quality, rework, WIP, downtime and safety/process compliance. Any incentive scheme is optional and requires approved policy and legal/HR validation.
- Store/retail staff: fixed wage/salary and optional individual/store sales commission based on approved eligible net sales, returns/voids, margin and store service measures. Do not pay commission on cancelled or refunded transactions without a controlled adjustment.
- Branch managers: role-based salary and optional balanced branch-performance bonus using commercial results, stock accuracy, customer experience, compliance and team outcomes.
- Wholesale/key-account sales employees: salary plus approved commission/bonus based on defined eligible sales, realized margin, collection and customer-quality criteria, with credit-note/return adjustments.
- Employees in merchandising, product development, finance, HR, warehouse and support: job-grade salary, skill/certification premium and approved performance bonus; do not force factory line metrics onto non-line roles.
- Commission-only, piece-rate, contractor or casual arrangements are not automatically treated as lawful merely because the system can calculate them. The actual working relationship, contract, labour law, tax and pension status must be reviewed before activation.

### External commercial payables—not employee payroll
- Wholesalers, retailers, dealers and resellers are normally handled through customer/supplier account agreements, price tiers, trade discounts, rebates, promotional allowances or approved settlement commissions—not payroll—unless the legal facts establish an employment relationship.
- Influencer, content creator, brand ambassador, affiliate, photographer and advertising-agency payments must be classified from contract, activity, recipient legal form, residency, TIN/licence, VAT status, payment nature and current income-tax rules. Support fixed campaign fees, deliverable fees, affiliate/performance commissions, royalties/licence usage, product-in-kind/barter and platform revenue as separate payable types.
- A creator's digital content income may fall under business income or Schedule D other income depending on the conditions in the Income Tax Amendment and implementing regulation; do not automatically label every influencer fee as payroll or apply one universal withholding rule.
- Supplier and subcontractor service payments are controlled in AP/procurement; employee compensation and external supplier payments must not be mixed.

### Learning, capability and performance
- Training needs analysis linked to business strategy, skills gaps and compliance requirements.
- Annual training plan, training budget, learning catalogue, internal/external providers and enrolment.
- Competency framework, skills matrix, operator certification and expiry/renewal reminders.
- Attendance, assessment, practical sign-off, effectiveness evaluation and training ROI.
- Career paths, succession, mentoring, coaching, goals, appraisal, performance improvement and engagement.
- Factory induction, safety, quality, machine operation, supervisor and leadership development.
- Operational skill matrix by employee, operation, machine, process, style family and skill grade; evidence-backed assessment, assessor, date, recertification/expiry and authorization limits.
- Link skills to line balancing, shift assignment, absence coverage, bottleneck risk, cross-training, succession and training investment. Supervisors/IE may receive explainable deployment suggestions, never an unreviewable automated worker score.

### Safe and fair incentive governance
- Incentive schemes are optional, separately versioned policies with eligibility, formula, measurement window, quality gate, cap/floor if approved, exception handling, review, employee visibility, grievance/appeal and payroll authorization.
- Manufacturing line or team schemes can reward accepted/earned output and productivity together with first-pass yield, rework, safe work, training, teamwork and accurate reporting. The system must not reward unsafe speed, defect concealment, unauthorized overtime, skipped inspection or work outside approved competencies.
- Store and sales schemes can balance eligible net sales, gross margin, customer service, returns, stock accuracy, payment collection and compliance; avoid rewarding over-discounting, mis-selling or pushing unsuitable inventory.
- Reversal of an incentive after returns/chargebacks must be handled through a lawful, approved adjustment; never impose an unapproved wage deduction or penalty.

### Safety and wellbeing
- Occupational health and safety, incidents, near misses, risk assessments and corrective actions.
- PPE issue, medical/fitness evidence with restricted access, safety training and emergency drills.
- Welfare, accommodation/transport where applicable, worker feedback and grievance resolution.

## L. Analytics and intelligent operations

- Role-based operational dashboards and governed KPI definitions.
- Order-to-ship visibility, OTIF, capacity, material risk, production efficiency, quality cost and working capital.
- HR headcount, turnover, absenteeism, training effectiveness and payroll cost.
- Forecasting, anomaly detection, marker/lay optimization and production scheduling.
- AI-assisted document extraction and recommendations must be explainable, permission-aware and human-approved for consequential actions.

## End-to-end value stream

Strategy/Market Insight → Product Initiative → Brand/Range/Assortment Plan → Design Studio/Concept → Pattern/3D/Prototype → Sample/Fit Rounds → Costing/PLM Approval → Collection Launch or Buyer Order → Sourcing/Procurement → Fabric and Trim Receiving/Separate Issue → Pre-Production Readiness → Planned Department Routes (Cutting → Optional Print/Embroidery → Sewing → Optional Wash/Dye/Special Process → Finishing/Pressing → Packing) → Quality Release → Finished-Goods Warehouse → Central/Regional DC → Own Branch/Store, Wholesaler, Retailer, Reseller, E-commerce or Direct Customer → Delivery/POS/Sell-through/Returns → Product and Channel Performance → Next Initiative/Collection Improvement.

Finance, HR, Training, Maintenance, Compliance, CRM, Pricing, OMS, WMS, Transport, Customer Service and BI support every stage and receive traceable events from it.

The ERP must be decision-supporting and improvement-oriented, not merely a database for entering completed work. Each key capability must plan, guide, validate, alert, recommend or optimize the work it records. Every workflow must specify states, allowed transitions, responsible roles, evidence/documents, validations, notifications, audit events, exception paths, downstream financial/inventory effects and measurable business outcomes.

## Product-wide operating principles

1. **Start with the business opportunity and product idea.** Do not assume a buyer order or factory order is the beginning of work.
2. **Design for own-brand and own-store retail.** Store sell-through, returns, fit feedback and customer insight must flow back into product/collection planning. Wholesale and buyer-order business remain supported.
3. **Support multiple business models.** A tenant can be brand owner, designer, manufacturer, CMT/FOB supplier, outsourced-manufacturing brand, wholesaler, retailer, distributor or a combination. Manufacturing is optional by business profile; product development and commerce are not.
4. **Route by product and process, not by a rigid global sequence.** Each style/revision selects a controlled route. Washing, dyeing, printing, embroidery, special finishing and subcontract steps are conditional and can occur at different route points.
5. **Give each department its own SOP and accountability.** The platform provides a reusable SOP/work-instruction framework, but cutting, trim issue, sewing, washing, finishing, pressing and packing must have distinct rules, checklists, inputs, outputs, measurements, approvals and exceptions.
6. **Capture material genealogy separately.** Fabric rolls/lots, trims/accessories, cut panels/bundles, WIP, garments, cartons and finished stock have linked but distinct issue/return/consumption ledgers.
7. **Make quality a process control.** Use first-piece approval, incoming/in-process/inline/end-line/final checks, error-proofing, defect containment, rework/reinspection, root-cause and CAPA. Do not rely on final inspection to discover preventable upstream faults.
8. **Make industrial engineering actionable.** Govern SMV/SAM, time-study evidence, method revisions, line balance, capacity and skill-based deployment. Show why a target is recommended and preserve the approved assumptions.
9. **Make people capability visible and developable.** Skill matrices and certifications help match people to work, identify coverage risks, plan cross-training and support fair recognition—not to create opaque or punitive worker ranking.
10. **Connect daily production and continuous improvement.** Provide visual targets, hourly actuals, WIP, shortages, bottlenecks, Andon, downtime reasons, daily team reviews, worker suggestions, 5S audits, Kaizen actions and measured follow-up.
11. **Keep incentives fair and quality/safety protected.** Any per-line or individual/team incentive is optional, transparent, approved and auditable. Its scorecard must balance accepted output/productivity with first-pass quality, rework, safety, compliance and team contribution; never reward unsafe speed, unauthorized overtime, defect concealment or denial of lawful leave.
12. **Preserve a complete audit chain.** Every hand-off records what moved, from/to where, quantity, unit, product/style revision, lot/bundle/carton identity, time, responsible person/role, status, quality result, variance and reason. A status change without evidence is not an adequate audit trail.
13. **Build the 3D/in-process experience as a connected workspace.** Design/3D views, line boards, cutting plans, operation queues, QC stations, washing recipe execution, finishing and packing should surface the right product context and next action—not become disconnected dashboards.
14. **Measure outcomes, not screen count.** A module is complete only when it demonstrably reduces delay, waste, defects, re-entry, stock mismatch, uncertainty or decision time, or improves fit, margin, delivery, safety, capability or customer experience.
15. **Use configuration and versioning instead of hard-coded assumptions.** Department SOPs, routes, thresholds, allowances, sampling plans, incentives, recipes, work instructions, capacity calendars and approval rules are versioned and effective-dated by tenant/site/product/process.

