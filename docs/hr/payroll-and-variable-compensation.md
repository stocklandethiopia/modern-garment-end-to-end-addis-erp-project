# Payroll, Workforce Compensation and Incentive Schemes — Garment, Retail and Channel Business

**Purpose:** Define a flexible compensation model for an apparel enterprise with factories, specialist production departments, distribution centres, own retail stores, wholesale sales and external channel partners. This is an operating-system design specification—not a declaration that any one scheme is legally approved for Ethiopia.

## 1. First classify the relationship correctly

The ERP must decide which financial/HR domain owns the payee before calculating payment. A payee's role in the business or the label on an invoice does not by itself determine their legal status.

| Relationship | Typical examples | ERP treatment |
|---|---|---|
| Employee | Operator, cutter, store salesperson, merchandiser, HR officer, supervisor, manager | HR contract, time/attendance, payroll, statutory employee tax/pension/benefits |
| Employee with variable earnings | Sewing operator on approved team incentive; store employee with approved sales commission | Payroll earning code linked to an approved incentive/commission policy |
| Independent service provider | Freelance designer, consultant, photographer, advertising agency, outsourced marketing firm | Supplier/vendor master, contract, deliverable acceptance, AP invoice, tax/withholding/VAT classification |
| Digital content creator/influencer | Resident creator, brand ambassador, affiliate creator, sponsored reviewer | Creator/campaign contract and creator payment ledger; apply applicable income-tax/digital-content rule after classification |
| Reseller/dealer/wholesaler | Independent account that buys and resells products | Customer/account commercial terms, trade discount, volume rebate, co-op marketing allowance or separately contracted service fee—not payroll by default |
| Subcontract processor | External sewing, washing, dyeing, printing, embroidery or finishing factory | Subcontract procurement/work order, material custody, accepted output, AP settlement, quality and tax controls |
| Partner/owner/distribution of profits | Company shareholder or partnership member | Equity/partner distribution and applicable tax/accounting workflow—not employee payroll unless a separate genuine employment relationship exists |
| Trainee/apprentice/seasonal worker | Practical training, apprentice or seasonal workforce | Correct legal classification and contract; approved stipend/wage and safeguards; never assume unpaid status |

If the facts are mixed or uncertain, set the relationship to **classification review required** and block automatic payment/tax treatment until HR/legal/accounting review. Record the contract, actual control/independence, duration, tools/resources, deliverables, business risk, exclusivity and authority.

## Pension coverage and remittance control

Under Private Organization Employees’ Pension Proclamation No. 1268/2022, the statutory definition of a private-organization employee includes a salaried person employed for at least 45 days, whether for a definite or indefinite period or piece of work, and includes managerial employees. Do not automatically exclude temporary, seasonal, part-time, piece-work or managerial roles based only on their contract label; record service duration and apply the approved legal coverage test.

For covered employees, the current statutory source states employer contribution 11% and employee contribution 7% on the statutory salary base. The employer must remit contributions monthly within 30 days from the last day of the month in which salary is paid. If the employer fails to deduct the employee contribution, the employer remains liable for it; late payment can generate interest and a monthly penalty.

The payroll engine must store the effective-dated coverage decision, salary base, employee/employer amount, payment month, due date, actual remittance, payment receipt, arrears, correction and rule version. The 11%/7% rates and 45-day test are source checkpoints; qualified local payroll review must verify the current law, amendment and POESSA implementation directives before production activation.

## 2. Common pay architecture

A compensation scheme is assembled from effective-dated earning/deduction components. Do not use one global “salary + bonus” formula for all roles.

Common employee components may include:
- Base monthly salary, daily wage or hourly wage.
- Approved shift, location, skill or qualification allowance.
- Overtime and other legally payable time premiums, calculated only from approved time records and effective local law.
- Piece-rate or output-based earning where lawful and contractually approved.
- Team/line/department productivity incentive.
- Individual skill/performance bonus.
- Store/branch sales commission and management incentive.
- Benefits, allowances, advances and loans.
- Employee statutory deductions and employer statutory contributions.
- Retroactive adjustments, arrears, authorized reimbursements and final settlement.

Each component must record taxable/pensionable/withholding treatment, calculation base, legal source, approver, effective date, payroll frequency, accounting mapping, rounding, eligibility, cap/floor and correction rules. “Taxable” and “pensionable” are separate legal characteristics; never assume they are identical.

## 3. Manufacturing production compensation

Manufacturing compensation needs its own model because work is organized by style, route, department, operation, line, shift, skill and accepted output. A garment's production path may include cutting, print/embroidery, sewing, washing/dyeing, finishing, pressing and packing. A single factory-wide unit-count bonus does not represent all work equally.

### A. Base pay and attendance
- Record employee contract, wage/salary basis, grade, department, job, work centre, line, shift, employment dates, roster and time/attendance.
- Track approved working time, rest, absence, paid leave and overtime separately.
- Preserve ordinary time and overtime calculation evidence; do not use efficiency incentives to replace legal wage/overtime obligations.

### B. Daily line planning and performance
For each line/shift/style/route revision, planning calculates a transparent recommended target:

**Daily target pieces = available direct-labour minutes × approved target efficiency ÷ style SMV/SAM per piece.**

The target engine stores:
- Approved style SMV/SAM and operation bulletin version.
- Net planned shift minutes and the approved break calendar.
- Planned/actual direct operators and eligible skill coverage.
- Machine/attachment capacity and planned maintenance.
- Material/trim readiness and quality holds.
- Style learning curve, changeover allowance and route/process constraints.
- Target-efficiency assumption, approver, reason and effective date.

The system must show planned target, hourly target curve, actual accepted output, earned standard minutes, available labour minutes, WIP and projected shift/day completion. A target revision requires a reason such as absence, material shortage, quality hold, machine downtime, method change or approved capacity constraint.

### C. Line efficiency and individual efficiency
A useful line KPI is:

**Line efficiency (%) = earned standard minutes for accepted output ÷ actual available direct-labour minutes × 100.**

For a simple single-style line where the approved style SAM applies to each accepted complete garment:

**Earned standard minutes = accepted good garments × approved style SAM.**

For multi-style/mixed operations, calculate earned minutes from the relevant approved operation bulletin and accepted quantity at each operation. Avoid double counting the same work content across operations.

Individual operation efficiency can be calculated from earned standard minutes for accepted work assigned to the employee divided by their relevant direct productive minutes. Compare people only when operation, method, style/complexity, training stage and working conditions are reasonably comparable. Never turn this into an opaque worker-ranking score.

### D. Optional daily line/team incentive
The user requirement is to support an approved daily/shift incentive per production line, not to hard-code a compulsory incentive for every tenant.

An approved policy may use:
- Achievement against the authorized line target.
- Earned standard minutes and line efficiency.
- First-pass yield and accepted quality.
- Rework/repair burden, defects and scrap/waste.
- Correct WIP and hand-off reconciliation.
- Teamwork, training/cross-skilling and reliable reporting.
- Safety/process gates and documented compliance.

The engine should allow formulas such as tiered target achievement, efficiency bands, team pool or a management-approved blended score. Do not prescribe a universal weight or rate. Quality and safety indicators can hold or defer an incentive pending investigation, but must not be used to impose unlawful wage deductions or fines on individual workers.

### E. Individual and skill-based recognition
Possible policies, subject to HR/legal approval:
- Qualified operation premium for scarce or certified skills.
- Multi-skilling/cross-training progression award.
- Approved individual contribution or improvement suggestion award.
- Training completion and practical sign-off recognition.
- Team-based learning or quality improvement recognition.

Do not reward only speed or raw pieces. Do not reward skipping quality checks, unauthorized overtime, unsafe work or concealing defects. Training and operator development must remain visible and resourced.

### F. Supporting process controls
- Separate cutting-room, trim-issue, sewing-line, wash/dye, finishing/pressing and packing performance; compare only appropriate process measures.
- Exclude or categorize non-attributable constraints with reason codes rather than silently penalizing workers for absent material, broken machines, wrong input or upstream quality holds.
- Record first-piece approval, inline QC, end-line QC, defect origin, rework, downtime, Andon response, hand-off quantities, shift handover and supervisor/IE/quality sign-off.
- Incentive result calculation must reference immutable production and time events, not editable summary totals.

## 4. Store and branch compensation

Own retail operations need a different model from a sewing line.

Possible employee arrangements:
- Fixed salary/wage for cashier, sales associate, stockroom, visual merchandising and store supervision.
- Optional individual commission based on eligible net sales assigned to the employee.
- Optional store-team target bonus to promote service and team contribution.
- Store-manager incentive based on an approved balanced scorecard.

The policy may consider net sales after discounts, cancelled/voided orders, refunds/returns, contribution margin, payment settlement, stock accuracy, customer service and compliance. It must define attribution across shared baskets, shifts, online pickup, returns and staff transfers.

Commission should not create perverse incentives to over-discount, sell unsuitable goods, hide returns, avoid service work or manipulate cash reconciliation. Refund/return reversals use a controlled commission correction and must not automatically deduct already-paid wages without legal review.

## 5. Wholesale and key-account sales employee schemes

Employee sales representatives and account managers can have:
- Base salary.
- Approved commission on eligible orders, invoices, collected cash or realized gross margin.
- Account portfolio/target bonus.
- New-account activation or strategic product-launch bonus.
- Approved travel/communications allowance.

The policy must state the earning event: confirmed order, dispatch, delivery, invoice, cash collection or another approved trigger. The best control is to avoid rewarding uncollectible or cancelled revenue. Credit notes, returns, rebates, bad debt, excessive discount and customer disputes require a governed true-up.

## 6. Reseller, dealer and distributor economics

An independent reseller/distributor is normally a commercial counterparty, not an employee. Model:
- Wholesale price list and account-specific trade discount.
- Quantity/volume tier, seasonal rebate or sell-through incentive.
- Target-based co-operative marketing allowance.
- Agreed territory/distribution rights and service levels.
- Credit, return, warranty and settlement terms.

Where a reseller also provides a separately contracted marketing or sales service, keep the service contract, approval, invoice, tax treatment and customer account adjustment separate. Do not use payroll solely because the payment is called “commission.”

## 7. Influencer, digital creator and brand ambassador schemes

The campaign module must handle:
- Flat campaign/appearance fee.
- Payment per approved deliverable (post, reel, video, live stream, photography, review or content package).
- Affiliate/referral commission by attributed and validated sales.
- Performance bonus by approved campaign KPIs.
- Product gifting, product-in-kind, barter and other non-cash consideration.
- Content usage rights, exclusivity, licence/royalty and renewal payments.
- Platform revenue or subscription/sponsorship payments where the enterprise operates or shares a creator programme.

Track campaign brief, deliverables, dates, approval, rights/usage period, platform, reach/attribution evidence, approved sales conversions, returned/cancelled orders, gross cash payment, in-kind value, invoice/receipt, creator TIN/legal form/residency/VAT registration and tax treatment.

### Ethiopia legal classification warning

Income Tax (Amendment) Proclamation No. 1395/2025 explicitly defines digital content creation income broadly, including advertisements/sponsorship, product endorsement/support, affiliate marketing, sponsor gifts/crowdfunding, subscriptions, sales of digital/physical goods and goods/services received instead of advertising.

The amendment treats the income as business income when its stated conditions are met (regular profit-oriented activity, creator is an organization and books/deductible business expenses); otherwise it refers to Schedule D “Other Income.” Council of Ministers Federal Income Tax Regulation No. 597/2026 adds a 15% tax rule for resident creators under the Schedule D category, an annual gross-income threshold over ETB 50,000, quarterly declaration/payment within 30 days after quarter end, and platform payment-facilitator reporting.

Therefore:
- Never default every influencer or ambassador payment to ordinary employee payroll.
- Never default every creator to Schedule D either; test the actual legal conditions and transaction.
- Separate resident individual creator, registered creator organization, employee creator, foreign/non-resident creator and platform/payment intermediary cases.
- Product-in-kind/barter, free product, affiliate and sponsorship income need treatment from the full current legal text and approved interpretation.
- Do not hard-code this note as a legal calculation engine; require a reviewed tax classification rule, current directive and accountant sign-off.

### Internal ERP comparison — what to reuse and what not to reuse

The historical Stockland ERP acceptance runbook provides useful finance/workflow patterns: separate PO, AP supplier invoice, AR customer balance, stock movement, balanced GL journal and audit evidence; independent approval; actual warehouse receipt quantity and documented variance; idempotent inventory posting; and location-scoped finance views. These are process controls to consider, not a reason to copy its legacy database schema or customer-specific workflow unchanged.

Do not copy its historic/synthetic PAYE example or pension-before-tax arithmetic as an authoritative current formula. Its example predates the 2025 Income Tax Amendment and 2026 implementing regulation. Recompute the legal tax/pension basis from the current official sources and obtain local payroll review before activation.

**Unresolved official clarification:** Proclamation No. 1395/2025 Article 22(3) requires a Tax Authority directive defining when digital-content creation is a business activity. The directive was not located in the official-source search completed on 22 November 2026. Regulation No. 597/2026 references the business-income test in Article 55 and provides Schedule C/Schedule D treatment. Until the Article 22(3) directive or an official Tax Authority confirmation is obtained, the creator classification workflow must remain **review required** for cases that are not unambiguously covered by the published text.

## 8. Payroll and commission integrity controls

- Classification review before onboarding or first payment.
- Effective-dated contracts, compensation components and scheme versions.
- Maker-checker for salary changes, incentive policy, commission plan and off-cycle payment.
- No retroactive edit to approved pay runs; use correction/adjustment runs.
- Distinguish gross entitlement, taxable base, pensionable base, withholding base, employer expense and net payment.
- Reconcile time, production, POS/OMS, returns, collections, AP, bank and GL source data.
- Reconcile employee pension and tax with authority filings and certificates.
- Prevent duplicate commission and payroll payment by idempotent earning event keys.
- Access controls for employee, campaign, bank, personal and health data.
- Dispute, grievance, appeal, recalculation, audit and supporting evidence.
- Retain the legal-rule version, approved formula, source event IDs and calculation breakdown on each earning line.

## 9. Minimum acceptance scenarios

1. A sewing line target is calculated from the approved style SMV/SAM, available labour minutes and authorized target efficiency.
2. Absence, machine downtime, missing fabric/trim, quality hold or method revision updates the plan with a reason and audit trail.
3. A line incentive uses approved accepted-output and quality evidence, not editable totals or raw speed alone.
4. A multi-style line earns standard minutes without double counting the same operation.
5. Employees on different processes or skill stages are not compared using a misleading identical productivity metric.
6. Store commission is calculated on the approved sales base and reversed/adjusted for eligible returns and cancellations.
7. Wholesale sales commission is tied to its defined earning trigger and reconciles to discounts, collections, credit notes and returns.
8. A reseller rebate is processed through the account/settlement ledger, not payroll.
9. An influencer campaign fee and affiliate commission create a creator/AP record with deliverables and tax-classification evidence.
10. A resident digital creator's Schedule D/business classification is determined from the approved current legal rule, not a hard-coded vendor type.
11. Employee tax/pension and creator/vendor tax calculations use different approved rule profiles where required.
12. Historical payslips, commissions and returns can be reproduced exactly from the scheme and rule versions in force at the time.

## 10. Research references and legal gate

Industry design references:
- ILO Ethiopia garment SCORE results: https://www.ilo.org/resource/article/advancing-productivity-quality-improvement-and-decent-work-ethiopias
- ILO Ethiopia sector skills strategy: https://www.ilo.org/publications/sector-skills-strategy-garment-sector-ethiopia
- Ethiopia Ministry of Labour and Skills, Garment Production Level I: https://www.hptc.edu.et/sites/default/files/GARMENT%20L-I.pdf
- Bangladesh SEIP, Production Planning and Control competency standard: https://seip-fd.gov.bd/wp-content/uploads/2019/07/Competency-Standard-%E2%80%93-Production-Planning-and-Control.pdf
- Bangladesh/IFC/ILO Better Work GEAR assessment 2025: https://betterwork.org/reports-and-publications/gear-bangladesh-report-2025/

Legal source register: ../localization/ethiopia-legal-regulatory-register.md

All Ethiopia statutory rates, payroll classification, wage/overtime, pension base, creator taxation, withholding and VAT treatment require the complete current law/directive and qualified local sign-off before activation.
