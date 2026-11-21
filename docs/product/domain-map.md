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

## E. Warehouse and inventory

- Multi-site warehouse, zones, racks, bins, mobile scanning, barcode and label management.
- Receiving, put-away, replenishment, picking, kitting, issue, return, transfer, adjustment and cycle count.
- Roll-level fabric traceability, shade segregation, batch/lot genealogy, expiry and FIFO/FEFO where applicable.
- WIP stores, finished goods, packing material, scrap and rework inventory.
- Stock valuation, inventory reconciliation, ageing, slow-moving, negative-stock controls and audit trails.

## F. Production execution

- Production order, work order, line allocation, capacity and shift plan.
- Cutting ticket, lay, bundle, bundle ticket, bundle movement and cut-panel reconciliation.
- Sewing line, operator assignment, operation output, hourly production, WIP ageing and bottleneck alerts.
- Subcontract job issue, external operation receipt, reconciliation and supplier settlement.
- Finishing, washing, printing, embroidery, pressing, folding, tagging and cartonization.
- Machine downtime, maintenance request, spare parts, calibration and production loss.
- Real-time target vs actual, efficiency, utilization, absenteeism, rework and cost variance.

## G. Quality, compliance and delivery

- Inline, end-line, final and AQL inspections with configurable sampling plans.
- Defect taxonomy, severity, repair/reject, rework, CAPA, root cause and supplier corrective action.
- Fabric testing, shade/colour, measurement, workmanship, needle/metal and packaging checks as applicable.
- Pre-shipment inspection, buyer compliance, social/environmental evidence and document expiry.
- Packing list, carton details, assortment, shipping marks, dispatch, delivery, export documents and returns.

## H. Finance and administration

- Chart of accounts, journals, general ledger, AP, AR, customer/supplier balances and ageing.
- Cash, bank accounts, payment instruments, bank reconciliation and treasury.
- Cost centres, budgets, commitments, allocations, landed cost and profitability.
- Sales invoices, credit/debit notes, supplier invoices, tax, withholding, VAT and statutory reports.
- Fixed assets, depreciation, expense claims, advances, loans, accruals and provisions.
- Multi-currency, FX revaluation, intercompany transactions, consolidation and period close.
- Product costing, order margin, style profitability, factory cost, variance and management accounts.

## I. Human resources and people development

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

## J. Analytics and intelligent operations

- Role-based operational dashboards and governed KPI definitions.
- Order-to-ship visibility, OTIF, capacity, material risk, production efficiency, quality cost and working capital.
- HR headcount, turnover, absenteeism, training effectiveness and payroll cost.
- Forecasting, anomaly detection, marker/lay optimization and production scheduling.
- AI-assisted document extraction and recommendations must be explainable, permission-aware and human-approved for consequential actions.

## End-to-end value stream

Enquiry → Costing → Sample/Tech Pack → Order → Material Plan → Procurement → Receiving/QC → Warehouse Allocation → Production Plan → Cutting → Sewing → Finishing → Final QC → Packing → Finished Goods → Dispatch/Export → Invoice/Receivable → Cash/Bank → Profitability.

HR, Training, Finance, Maintenance, Compliance and BI support every stage and receive traceable events from it.

Each workflow must specify states, allowed transitions, responsible roles, evidence/documents, validations, notifications, audit events, exception paths and downstream financial/inventory effects.
