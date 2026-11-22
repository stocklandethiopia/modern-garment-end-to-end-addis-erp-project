# End-to-End Garment ERP Domain Map

This is the product capability map, not a claim that every capability has already been implemented.

## A. Enterprise and platform foundation

- Tenant registration, trial, subscription, plan limits, feature entitlements and tenant lifecycle.
- Multiple legal entities, brands, business units, factories, branches, departments, cost centres and warehouses.
- Organization charts, approval delegation, fiscal periods, calendars, currencies, units, languages and numbering rules.
- Identity, MFA, SSO-ready architecture, user provisioning, roles, permissions, data scope and audit.
- Configurable fields, forms, document templates, workflows and notification preferences.
- Integration registry, API keys, webhooks, import/export jobs and monitoring.

## B. Merchandising and commercial

- Buyer/customer onboarding, supplier and agent profiles, RFQ/enquiry, sample request and sample tracking.
- Style/collection development, season, product category, size range, colourway and customer specifications.
- Tech pack, measurement chart, artwork, construction details, trims, labels, packaging and revision control.
- Quotation, costing, price negotiation, order confirmation, purchase commitment and order amendments.
- Critical path calendar, milestone ownership, risk/late alerts and buyer communication history.

## C. Product engineering and industrial engineering

- Style master, product variants, BOM, material specification and approved substitutes.
- Operation bulletin, routing, machine class, standard minute value (SMV), skill requirements and operation sequence.
- Marker planning, fabric width, shrinkage, lay plan, spreading, cutting consumption and marker efficiency.
- Size/colour ratio, grading, pattern revisions, sample approval and pre-production readiness.
- Capacity planning, line balancing, takt/target calculation, labour standards and cost simulation.

## D. Procurement and materials

- Supplier qualification, sourcing, quotations, price history, contracts and vendor performance.
- Purchase requisition, approval, purchase order, delivery schedule, goods receipt and supplier invoice matching.
- Import purchase, landed cost, freight, insurance, customs, duties, foreign currency and bank document tracking.
- Fabric, trims, accessories, packaging, chemicals and consumables with lot, roll, shade, width, weight and expiry attributes.
- MRP, demand forecast, safety stock, reservations, shortage, substitute approval and material allocation.
- Incoming inspection, quarantine, supplier returns and claims.

## E. Warehouse and inventory control

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

## F. Distribution network, order management and channel commerce

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

## G. Production execution

- Production order, work order, line allocation, capacity and shift plan.
- Cutting ticket, lay, bundle, bundle ticket, bundle movement and cut-panel reconciliation.
- Sewing line, operator assignment, operation output, hourly production, WIP ageing and bottleneck alerts.
- Subcontract job issue, external operation receipt, reconciliation and supplier settlement.
- Finishing, washing, printing, embroidery, pressing, folding, tagging and cartonization.
- Machine downtime, maintenance request, spare parts, calibration and production loss.
- Real-time target vs actual, efficiency, utilization, absenteeism, rework and cost variance.

## H. Quality, compliance and delivery

- Inline, end-line, final and AQL inspections with configurable sampling plans.
- Defect taxonomy, severity, repair/reject, rework, CAPA, root cause and supplier corrective action.
- Fabric testing, shade/colour, measurement, workmanship, needle/metal and packaging checks as applicable.
- Pre-shipment inspection, buyer compliance, social/environmental evidence and document expiry.
- Packing list, carton details, assortment, shipping marks, dispatch, delivery, export documents and returns.

## I. Finance and administration

- Chart of accounts, journals, general ledger, AP, AR, customer/supplier balances and ageing.
- Cash, bank accounts, payment instruments, bank reconciliation and treasury.
- Cost centres, budgets, commitments, allocations, landed cost and profitability.
- Sales invoices, credit/debit notes, supplier invoices, tax, withholding, VAT and statutory reports.
- Fixed assets, depreciation, expense claims, advances, loans, accruals and provisions.
- Multi-currency, FX revaluation, intercompany transactions, consolidation and period close.
- Product costing, order margin, style profitability, factory cost, variance and management accounts.

## J. Human resources and people development

HR is a major enterprise domain, not a small employee register.

### Workforce lifecycle
- Workforce demand, headcount budget, organization design, job architecture and manpower requisition.
- Recruitment, applicant tracking, interview, selection, offer, contract and onboarding.
- Employee master, personal/emergency contacts, qualifications, experience, documents and consent.
- Position, grade, job family, department, supervisor, location, employment type and contract lifecycle.
- Probation, confirmation, transfer, promotion, secondment, disciplinary case, grievance and exit.
- Employee self-service, manager self-service, HR case management and employee relations.

### Time, pay and benefits
- Shift patterns, roster, biometric/device integration, attendance, lateness, absence and overtime approval.
- Leave types, entitlement, accrual, carry-forward, encashment and leave settlement.
- Payroll calendar, salary structure, earnings, deductions, allowances, benefits, loans and advances.
- Payroll calculation, validation, approval, payslip, bank payment file, journal posting and reconciliation.
- Pension, income tax, withholding, severance/final settlement and statutory returns under approved local rules.
- Payroll corrections, retroactive adjustments, off-cycle pay and audit history.

### Learning, capability and performance
- Training needs analysis linked to business strategy, skills gaps and compliance requirements.
- Annual training plan, training budget, learning catalogue, internal/external providers and enrolment.
- Competency framework, skills matrix, operator certification and expiry/renewal reminders.
- Attendance, assessment, practical sign-off, effectiveness evaluation and training ROI.
- Career paths, succession, mentoring, coaching, goals, appraisal, performance improvement and engagement.
- Factory induction, safety, quality, machine operation, supervisor and leadership development.

### Safety and wellbeing
- Occupational health and safety, incidents, near misses, risk assessments and corrective actions.
- PPE issue, medical/fitness evidence with restricted access, safety training and emergency drills.
- Welfare, accommodation/transport where applicable, worker feedback and grievance resolution.

## K. Analytics and intelligent operations

- Role-based operational dashboards and governed KPI definitions.
- Order-to-ship visibility, OTIF, capacity, material risk, production efficiency, quality cost and working capital.
- HR headcount, turnover, absenteeism, training effectiveness and payroll cost.
- Forecasting, anomaly detection, marker/lay optimization and production scheduling.
- AI-assisted document extraction and recommendations must be explainable, permission-aware and human-approved for consequential actions.

## End-to-end value stream

Enquiry/Forecast → Merchandising/Costing → Sample/Tech Pack → Customer Order/Wholesale Commitment → Material Plan → Procurement → Receiving/QC → Raw-Material Warehouse → Production Plan → Cutting → Sewing → Finishing → Final QC → Packing → Finished-Goods Warehouse → Central DC → Regional DC/Depot → Branch/Store or Wholesale/Retailer/Reseller Fulfilment → Delivery/POS/Customer Receipt → Invoice/Receivable/Settlement → Returns/Reverse Logistics → Channel and Product Profitability.

Finance, HR, Training, Maintenance, Compliance, CRM, Pricing, OMS, WMS, Transport, Customer Service and BI support every stage and receive traceable events from it.

Each workflow must specify states, allowed transitions, responsible roles, evidence/documents, validations, notifications, audit events, exception paths and downstream financial/inventory effects.
