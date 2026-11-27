# Ethiopia Payroll Localization — Canonical ERP Source Basis

**Work package:** WP-14 — Workforce and payroll  
**Localization:** Federal Democratic Republic of Ethiopia  
**Research cut-off:** 27 November 2026  
**Status:** Legal source analysis and server-side calculation candidate implemented; statutory activation remains **HOLD**  
**Product boundary:** New customer-neutral Laravel/PostgreSQL ERP. This document does not import a sample system's database, schema, payee records, product identity, fixed rule identifier or historical payroll.

## 1. Purpose and legal boundary

This is the Ethiopian localization basis for a new garment ERP. The uploaded reference pack is used for legal issue identification, calculation regression and engineering control analysis only. Its legacy application architecture, SQL tables, customer data, code namespace and product-version assumptions are not adopted.

The ERP separates:
- employment income and employee payroll;
- pension coverage, pensionable salary and contributions;
- tax treatment and pension treatment for each earning component;
- approved reimbursements and employee earnings;
- nonemployee service, retainer, freelance, creator and supplier payments;
- calculation, payroll approval, accounting posting, employee payment, PAYE remittance and pension remittance.

A legal source record must be independently reviewed and applicable to the exact entity, worker, component and payroll period. This specification is not a legal opinion. No statutory calculation may be activated solely from a general summary, a rate table, an old payroll calculation, or a unit test.

## 2. Ethiopian official source register

| Instrument / authority | Relevant payroll subject | Current engineering treatment |
|---|---|---|
| Income Tax (Amendment) Proclamation No. 1395/2025, Federal Negarit Gazette No. 64, 1 September 2025 | Replaces Article 11 with monthly employment-income tax brackets; includes effective/transition rules | Bracket schedule is transcribed for candidate regression; approved effective-dated rule record and transition review required before use |
| Federal Income Tax Proclamation No. 979/2016, as amended | Employment-income definition, monthly PAYE computation, exemptions and withholding | Full authoritative text and amendment chain must be loaded and reviewed; PAYE base interaction with employee pension remains a legal hold |
| Federal Income Tax Regulation No. 597/2026, Federal Negarit Gazette No. 4, 28 October 2026 | Further amends Regulation 410/2017; added Article 20(2) covers relevant resident digital-content, freelance and remote-service income; amended Article 92 addresses documents required from payees by withholding agents | Separate nonemployee individual-payee/AP and tax-decision workflow; do not convert these payees to employee payroll or infer a withholding rate from a label |
| Private Organization Employees’ Pension Proclamation No. 1268/2022, Federal Negarit Gazette No. 16, 18 March 2022 | Articles 2–3 define the employee/scheme scope and exclusions; Article 6 covers registration changes; Article 10 sets contributions; Articles 11–12 cover collection, monthly remittance and late consequences | Coverage is not universal. Store nationality, employer/worker category, prior provident-fund and international-agreement facts, reviewed eligibility, pension salary basis, contributions and actual remittance evidence |
| Private Organization Employees’ Pension Implementation Directive No. 1085/2025 | Employer/worker registration, records and implementation procedure | Listed by the Ministry of Justice; the complete directive and exact clauses must be captured and professionally reviewed before activation |
| Pension Contribution Debt Collection Directive No. 1086/2025 | Recovery/enforcement of unpaid contributions | Listed by the Ministry of Justice; full text and enforcement workflow review remain required |
| Pension Contribution Revenue Planning, Collection, Transfer and Supervision Directive No. 1087/2025 | Collection, transfer, remittance administration and supervision | Listed by the Ministry of Justice; full text and implementation review remain required |
| Labour Proclamation No. 1156/2019 and current amendments/directives | Employment relationship, wage records, lawful deductions, working time, overtime, leave and termination | Ministry of Justice catalogue marks the proclamation In Force; detailed payroll formulas must use the complete current controlling text and professional review |

### 2.1 Official links

- Proclamation 1395/2025: https://www.mofed.gov.et/media/filer_public/7f/72/7f720781-022a-4b90-a9e7-b4724c1f3002/income_tax_amendment_proc_no_1395-2017_compressed.pdf
- Proclamation 979/2016 Technical Notes from the Ministry of Revenue (interpretive, explicitly non-binding): https://www.mor.gov.et/documents/49818/172549/Technical%2BNote-%2BIncome%2BTax%2BProclamation%2B%2BNo.%2B979-2016.pdf/c08d7d30-903c-70f2-044a-846a5413a2a9?download=true&t=1628061616844 (Article 10 discussion, PDF pp. 51–52; non-binding introduction, PDF p. 4)
- Regulation 597/2026: https://justice.gov.et/wp-content/uploads/2026/11/%E1%8B%B0%E1%8A%95%E1%89%A5-%E1%89%81%E1%8C%A5%E1%88%AD-597-2019.pdf
- Pension Proclamation 1268/2022: https://www.ffic.gov.et/Portals/0/______1268.pdf
- Pension Proclamation 1268/2022 (Ministry of Justice sector listing): https://justice.gov.et/am/sector/social-security-pensions/
- Ministry of Justice POESSA directive catalogue: https://justice.gov.et/en/entity/private-organizatio-employees-social-security-administration/
- Directive 1085/2025: https://justice.gov.et/am/directives/private-organizations-employees-pension-implementation-directive-2/
- Directive 1087/2025: https://justice.gov.et/en/directives/%E1%8B%A8%E1%8C%8D%E1%88%8D-%E1%8B%B5%E1%88%AD%E1%8C%85%E1%89%B5-%E1%88%A0%E1%88%AB%E1%89%B0%E1%8A%9E%E1%89%BD-%E1%8C%A1%E1%88%A8%E1%89%B3-%E1%88%98%E1%8B%8B%E1%8C%AE-%E1%8C%88%E1%89%A2-%E1%8B%95-3/
- Labour Proclamation 1156/2019: https://justice.gov.et/en/law/labour-proclamation/
- Labour Proclamation 1156/2019 (official bilingual Gazette PDF): https://fjli.gov.et/Tedocs/E83/CP/LABOUR%20PROCLAMATION.pdf

The Amharic Federal Negarit Gazette text is controlling where translations differ. The payroll release gate requires the actual source document bytes to be retained in the ERP's controlled legal-document store and their SHA-256 calculated from those bytes. No source hashes are invented in this document.

## 3. Monthly employment-income tax bands

Article 11, as replaced by Proclamation 1395/2025, publishes these monthly bands:

| Monthly employment income (ETB) | Rate |
|---:|---:|
| 0–2,000 | 0% |
| 2,001–4,000 | 15% |
| 4,001–7,000 | 20% |
| 7,001–10,000 | 25% |
| 10,001–14,000 | 30% |
| Above 14,000 | 35% |

These are progressive marginal bands. The cumulative offsets used in an implementation are mathematical derivations, not separate statutory credits. The amendment states that, subject to the specified transition provisions, its other provisions apply to income derived from 8 July 2025; the specified Schedule D / renumbered Article 97 withholding provisions have a separate 7 August 2025 effective date.

**Important:** this table does not by itself settle all employment-income inclusion, exemption, fringe-benefit, allowance or pension-interaction questions. The complete law, schedules, amendment chain, regulation and actual employee facts must be reviewed.

## 4. PAYE base — retain both candidate methods, activate neither without written advice

The engineering regression retains the two methods in the reference analysis:

1. `TAXABLE_GROSS`: base salary plus components classified as taxable employment income.
2. `TAXABLE_GROSS_LESS_EMPLOYEE_PENSION`: taxable gross less the employee's mandatory pension contribution.

For the required ETB 30,000 illustration, assuming a covered employee with ETB 30,000 pensionable salary and no allowances or other deductions:

| Candidate basis | PAYE base | PAYE | Employee pension | Net pay | Employer pension |
|---|---:|---:|---:|---:|---:|
| `TAXABLE_GROSS` | 30,000.00 | 8,450.00 | 2,100.00 | 19,450.00 | 3,300.00 |
| `TAXABLE_GROSS_LESS_EMPLOYEE_PENSION` | 27,900.00 | 7,715.00 | 2,100.00 | 20,185.00 | 3,300.00 |

Difference in PAYE and net pay: ETB 735.00.

### 4.1 Legal signal and unresolved point

Proclamation 979/2016 Article 10 is described in the Ministry of Revenue's Technical Notes (PDF pp. 51–52) as applying the Article 11 rates to total employment income received for the calendar month and disallowing deduction of expenditure incurred in deriving employment income. The Notes say the tax is imposed on the gross amount of employment income received. Their introduction expressly says the explanatory notes are not legally binding and directs users to the Proclamation and related laws and regulations for binding information.

Separately, Pension Proclamation 1268/2022 defines pension salary as the monthly salary for regular working hours without deduction for income tax or any other matter. That provision defines the pension contribution salary basis; it does not, by itself, conclusively determine the PAYE base.

Therefore:
- the gross-taxable method has a stronger textual signal in the available income-tax explanatory material;
- the pension-first method is preserved as a candidate regression because the uploaded evidence and user instruction require it to remain testable;
- neither method is marked as approved by this engineering package;
- the production rule must contain a written, clause-specific Ethiopian tax opinion approving the exact method and citing the controlling income-tax and pension provisions;
- the reviewer must be qualified, independent from the payroll preparer and recorded in the exact frozen statutory rule snapshot.

The calculator has no default method. It rejects payroll-draft calculation when that opinion is missing, unclear, out of scope or disagrees with the selected method. The approval workflow must retain the written opinion itself as a tenant-owned private document, its SHA-256, reviewer qualification/registration and approval record.

## 5. Pension coverage and salary basis

Pension Proclamation 1268/2022 provides, subject to the exact worker and employer scope:
- Employee contribution: 7%.
- Employer contribution: 11%.
- Article 2(1) defines a private-organization employee as a salaried person employed for not less than 45 days for a definite or indefinite period or piece of work, including managerial employees.
- Article 3(1) generally applies the scheme to Ethiopian-national employees of private organizations, subject to the stated legacy coverage and international-agreement provisions.
- Article 3(3) includes consent-based coverage for employees of religious and political organizations and persons in the informal sector.
- Article 3(4) excludes domestic workers, employees of international organizations and foreign diplomatic missions, and the specified owners/directors or employees of private organizations established or operated in their own names.
- For non-Ethiopian nationals outside an explicit covered category, do not guess. Require a specific Ethiopian legal/social-security determination.
- Article 2(7) defines pension salary by the monthly salary for services during regular working hours without deductions for income tax or other matters.
- Article 6 requires specified registration information changes to be notified within 60 days.
- Article 12 requires monthly payment to the Pension Fund within 30 days from the last day of the salary-payment month. The source analysis records interest at the bank deposit interest rate plus a monthly 5% penalty for late payment; validate the current controlling text and implementation with POESSA before coding assessment/collection.

The employee's 7% contribution is deducted from the employee's net; the employer's 11% contribution is an employer cost and is never deducted from employee earnings.

The rule bundle must not use an employee's job title or a free-text checkbox to decide pension coverage. It must preserve the applicable employer/worker class, nationality or agreement basis where relevant, 45-day service facts, prior-scheme evidence, reviewed coverage decision and effective dates.

### 5.1 Pensionable salary classification

Taxable and pensionable are separate legal characteristics. The default component classifications must be conservative and explicit. A component label such as allowance, per diem, bonus, housing, transport, meal, overtime or commission does not automatically determine either treatment.

When the pensionable salary differs from the normal regular-hours salary basis, the line must preserve:
- the reason for the difference;
- applicable contract clause and effective period;
- exact legal/official directive basis;
- source URL and document SHA-256;
- independent reviewer and approval reference;
- the amount and components affected.

No unexplained override or silent salary-base reduction is accepted.

## 6. Tax exemptions, allowances and reimbursements

An exemption exists only where the current applicable law or binding directive provides it and the employee facts meet the conditions. Each exempt component requires an evidence record with:
- legal instrument, exact article/clause and jurisdiction;
- applicable taxpayer/employee and component facts;
- any statutory cap or limit;
- effective dates;
- official source URL and source SHA-256;
- contract, receipt, travel order or other supporting evidence as applicable;
- independent professional review and approval.

If any of these are missing, the component cannot be marked exempt for a production calculation. It remains taxable or the line is held for review, depending on the facts.

Expense reimbursements are not silently converted to salary allowances. A reimbursement workflow must preserve business purpose, expense report, date, location, receipt and policy approval; the payroll calculator in this slice does not calculate or classify reimbursements.

## 6. Labour Proclamation payroll and paid-absence requirements

The supplied legal matrix identifies the following federal Labour Proclamation 1156/2019 rules. The complete current text, controlling Amharic wording, subsequent directives, sector-specific exceptions, regional administration, collective agreements and employee facts must be reviewed before these are activated as calculations.

| Area | Source provision | Localized requirement | Open legal control |
|---|---|---|---|
| Ordinary time | Article 61 | No more than 8 hours/day or 48 hours/week, subject to valid statutory arrangements, averaging and applicable sector reductions | Effective-dated schedule by worker and site; validate special directives and category |
| Overtime authorization | Articles 66–67 | Permitted grounds, express employer instruction, actual time evidence; maximum 4 hours/day and 12 hours/week | A roster or planned schedule does not itself authorize overtime |
| Overtime premium | Article 68 | 1.5× day (06:00–22:00); 1.75× night (22:00–06:00); 2× weekly rest day; 2.5× public-holiday overtime | Record the category and ordinary hourly basis |
| Public holiday | Article 75 | 2× hourly wage for each hour worked on a public holiday | Do not automatically stack with Article 68's 2.5× public-holiday overtime category; obtain legal interpretation |
| Young workers | Article 91 | Young workers are not to be employed at night, for overtime, on weekly rest days or public holidays | Age is a restricted HR attribute; enforce age-aware shift and assignment controls without exposing age in broad production dashboards |
| Annual leave | Articles 76–77 | 16 working days in the first year, then +1 working day for each additional 2 years; pay as if the worker continued working; qualifying unused leave is paid on termination | Carry forward statutory/contractual entitlement as dated movements, not an overwritten balance |
| Sick leave | Articles 85–86 | Up to 6 months within a rolling 12-month period: first month 100%, next 2 months 50%, following 3 months unpaid, subject to conditions and medical evidence | Track first sickness date and consumed leave across the rolling period |
| Maternity | Article 88(3) | 30 consecutive days paid prenatal and 90 consecutive days paid postnatal | A later clause's “working days” wording requires controlling Amharic/legal reconciliation for unusual delivery timing |
| Wage deductions | Article 59 | Only lawful/contractually authorized deductions; generally no more than one-third of monthly wage per deduction event without written consent | Store legal basis, agreement/court/work-rule evidence, written consent where required, amount and reviewer |
| Wage register / payslip | Article 60 | Record gross, method of calculation, other remuneration, deduction categories/amounts, net and relevant particulars; explain on request | Employee access with privacy-preserving role security |
| Severance | Articles 39–41 | Fact-specific eligibility; not a universal formula; Article 40 calculation/cap and any Article 41 additions apply only when the statutory conditions are met | Verify exact termination ground, probation, pension eligibility, service and related facts |
| Final wages | Articles 36–37 | Termination-connected wages/payment due generally within 7 working days, subject to the specified exception; an admitted amount remains payable while a disputed remainder is resolved | Separate admitted and disputed amounts and retain payment timing evidence |

Do not implement an automatic universal overtime, leave cash-out or severance multiplier from a sample. The complete statute and case facts must drive the effective rule. These requirements are transcribed in the [Ethiopia Payroll Legal Requirements Matrix](ethiopia-payroll-legal-requirements-matrix.csv).

## 7. Employee payroll versus nonemployee payment

`RETAINER_SERVICE` is explicitly excluded from employee payroll. It must be routed to supplier/AP or an individual-payee legal/tax review process based on the actual facts and contract, not inserted into PAYE or pension merely because the recipient is an individual.

Regulation 597/2026 further amends Regulation 410/2017. Its added Article 20(2) addresses income derived by a resident from digital or other freelance services or businesses carried out through remote services. Its amended Article 92 sets documents generally required from a withholding payee (TIN and valid business licence) and lists exceptions including TIN alone for building lessors and digital content creators, and TIN plus an advocacy licence for legal professionals. This is a separate individual-payee/AP tax decision, not employee PAYE. No withholding rate is inferred from the payee label. Verify the complete current clause and the actual taxpayer category before approval.

## 8. Server-side engineering implementation

The candidate implementation lives in the canonical Laravel application:

- `backend/app/Services/Payroll/EthiopiaPayrollCalculator.php`: exact-decimal server-side calculation, two candidate PAYE methods, explicit taxable/pensionable component treatment, pension coverage, deduction evidence, exemption evidence, non-standard pensionable salary rationale, progressive bands and a source snapshot hash.
- `backend/app/Services/Integration/DocumentIntegrationService.php`: re-reads a tenant-owned private document from configured storage and verifies its SHA-256 before it is accepted as statutory evidence.
- `backend/database/migrations/2026_11_27_000033_require_stored_statutory_source_document.php`: adds the source-document reference to the statutory rule record and enforces a tenant-scoped reference to the existing document-assets registry.
- `backend/app/Services/Finance/FinanceControlService.php`: freezes all selected rule-source fields and payload for the entire payroll period; calls the calculator server-side for draft-line calculation; stores the calculation breakdown and evidence.
- `backend/app/Http/Controllers/Finance/FinanceControlController.php`: accepts the calculation inputs, not client-computed gross/tax/net totals; monetary values are decimal strings.
- `backend/config/payroll.php`: both Ethiopia activation flags are hard-coded default `false` until the remaining HR/legal release gates have been separately accepted.
- `backend/tests/Unit/EthiopiaPayrollCalculatorTest.php`: candidate regression, comparison, RETAINER_SERVICE exclusion, evidence gates and synthetic approved-source gate tests.

No browser-side PAYE calculator is authoritative. The UI must display server-returned line breakdowns only; client-submitted amounts are not accepted as the legal calculation result.

### 8.1 Rule provenance without a legacy version identifier

The ERP does not carry forward a fixed sample-system rule label or product-version identifier. Each rule record is an effective-dated, source-backed record. Each run freezes its selected legal source documents, instrument/article references, publication/effective dates, document UUIDs, SHA-256 hashes, independent reviewer and approved parameters. The statutory source documents must be stored in tenant-owned `document_assets` records; the current bytes are re-read and their hashes reverified before a payroll draft accepts the rule bundle. The calculator creates a deterministic SHA-256 source snapshot reference.

This retains historical reproducibility without reusing an arbitrary legacy version label or a sample-system database structure.

## 9. Historical and accounting boundary

- This change does not update, rewrite, recalculate or reverse any posted payroll or historical transaction.
- The calculator only creates a new payroll-line calculation inside a draft run, after all gates permit that path.
- Corrections to a posted payroll must use a separate, authorized adjustment or reversal run under the accounting control process.
- Payroll approval, finance posting, employee payment, PAYE remittance and pension remittance remain separate transitions with separate authorized actors and reconciliation evidence.
- No bank/payment or statutory remittance has been performed by this source change.

## 10. Current unresolved items — do not activate

1. Obtain and store complete official Amharic and authorized translation copies of the relevant income-tax provisions and all applicable amendment/regulation instruments.
2. Obtain a written, clause-specific Ethiopian tax opinion on whether employee mandatory pension reduces the PAYE base; approve exactly one method.
3. Capture complete Directives 1085/2025, 1086/2025 and 1087/2025; review coverage, pension salary treatment, registration, records, remittance and enforcement clauses with POESSA/local pension counsel.
4. Complete the employee master, contracts, assignments and effective-dated employee statutory classification source. The current WP-14 candidate still lacks the complete HR employee lifecycle and profile resolver.
5. Build a reviewed component-classification catalogue for taxable, exempt, pensionable, non-pensionable and reimbursable items.
6. Confirm current PAYE return/withholding procedure and due dates from the competent Tax Authority's official instruments.
7. Complete legal reviews for overtime, public holiday work, leave, termination/final pay, regional rules, collective agreements and any sector-specific worker class before activating those formulas.
8. Verify current creator/freelance/remote-service treatment under Regulation 597/2026 and any later Tax Authority directive.

**Current decision: HOLD.** The server-side calculation component and candidate tests exist, but both production activation flags remain false. No actual payroll is enabled or processed by this work.
