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
| Federal Income Tax Regulation No. 597/2026, Federal Negarit Gazette No. 4, 28 October 2026 | Further amendments to Regulation 410/2017; addresses resident digital, freelance and remote-service income and withholding documentation | Must be considered for nonemployee payee classification; it does not convert those payees into employees |
| Private Organization Employees’ Pension Proclamation No. 1268/2022, Federal Negarit Gazette No. 16, 18 March 2022 | Employee coverage, salary definition, contribution rates, registration and remittance | Core legal source for pension coverage and contribution calculations; case-by-case coverage and salary classification still require review |
| Private Organization Employees’ Pension Implementation Directive No. 1085/2025 | Employer/worker registration, records and implementation procedure | Listed by the Ministry of Justice; the complete directive and exact clauses must be captured and professionally reviewed before activation |
| Pension Contribution Debt Collection Directive No. 1086/2025 | Recovery/enforcement of unpaid contributions | Listed by the Ministry of Justice; full text and enforcement workflow review remain required |
| Pension Contribution Revenue Planning, Collection, Transfer and Supervision Directive No. 1087/2025 | Collection, transfer, remittance administration and supervision | Listed by the Ministry of Justice; full text and implementation review remain required |
| Labour Proclamation No. 1156/2019 and current amendments/directives | Employment relationship, wage records, lawful deductions, working time, overtime, leave and termination | Ministry of Justice catalogue marks the proclamation In Force; detailed payroll formulas must use the complete current controlling text and professional review |

### 2.1 Official links

- Proclamation 1395/2025: https://www.ffic.gov.et/Portals/0/1395-2017.pdf
- Proclamation 979/2016 Technical Notes from the Ministry of Revenue (interpretive, explicitly non-binding): https://www.mor.gov.et/documents/49818/172549/Technical%2BNote-%2BIncome%2BTax%2BProclamation%2B%2BNo.%2B979-2016.pdf/c08d7d30-903c-70f2-044a-846a5413a2a9?download=true&t=1628061616844
- Regulation 597/2026: https://justice.gov.et/wp-content/uploads/2026/11/%E1%8C%98%E1%88%9B%E1%8C%AB%E1%88%AC-%E1%8C%A5%E1%88%AD-597-2019.pdf
- Pension Proclamation 1268/2022: https://www.ffic.gov.et/Portals/0/______1268.pdf
- Ministry of Justice POESSA directive catalogue: https://justice.gov.et/en/entity/private-organizatio-employees-social-security-administration/
- Directive 1085/2025: https://justice.gov.et/am/directives/private-organizations-employees-pension-implementation-directive-2/
- Directive 1087/2025: https://justice.gov.et/en/directives/%E1%8B%A8%E1%8C%8D%E1%88%8D-%E1%8B%B5%E1%88%AD%E1%8C%85%E1%89%B5-%E1%88%A0%E1%88%AB%E1%89%B0%E1%8A%9E%E1%89%BD-%E1%8C%A1%E1%88%A8%E1%89%B3-%E1%88%98%E1%8B%8B%E1%8C%AE-%E1%8C%88%E1%89%A2-%E1%8B%95-3/
- Labour Proclamation 1156/2019: https://justice.gov.et/en/law/labour-proclamation/

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

Proclamation 979/2016 Article 10 is described in the Ministry of Revenue's Technical Notes as applying the monthly rate to total employment income received and disallowing deduction of expenses incurred in deriving employment income. The Notes explain that this results in a gross employment-income basis. However, the Technical Notes expressly state they are not legally binding.

Separately, Pension Proclamation 1268/2022 defines pension salary as the monthly salary for regular working hours without deduction for income tax or any other matter. That provision defines the pension contribution salary basis; it does not, by itself, conclusively determine the PAYE base.

Therefore:
- the gross-taxable method has a stronger textual signal in the available income-tax explanatory material;
- the pension-first method is preserved as a candidate regression because the uploaded evidence and user instruction require it to remain testable;
- neither method is marked as approved by this engineering package;
- the production rule must contain a written, clause-specific Ethiopian tax opinion approving the exact method and citing the controlling income-tax and pension provisions;
- the reviewer must be qualified, independent from the payroll preparer and recorded in the exact frozen statutory rule snapshot.

The calculator has no default method. It rejects payroll-draft calculation when that opinion is missing, unclear, out of scope or disagrees with the selected method.

## 5. Pension coverage and salary basis

Pension Proclamation 1268/2022 provides, subject to coverage:
- Employee contribution: 7%.
- Employer contribution: 11%.
- Coverage definition includes a salaried person employed for at least 45 days and includes managerial employees.
- Article 3 contains coverage scope, legacy/agreements and exclusions. Do not assume every worker, nationality, employer or contract type is automatically covered.
- Article 2(7) defines salary for pension as the monthly salary for services during regular working hours without deductions for income tax or other matters.
- Article 12 requires monthly payment to the Pension Fund within 30 days from the last day of the month in which salary is paid; the proclamation also sets consequences for late payment.

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

## 7. Employee payroll versus nonemployee payment

`RETAINER_SERVICE` is explicitly excluded from employee payroll. It must be routed to supplier/AP or an individual-payee legal/tax review process based on the actual facts and contract, not inserted into PAYE or pension merely because the recipient is an individual.

Regulation 597/2026 further amends the income-tax regulation and addresses resident digital-content, freelance and remote-service income and withholding documentation. This is a separate tax-classification path, not a general rule that makes every freelancer or retainer recipient a payroll employee. No Schedule C/D rate, threshold or exemption is inferred from a UI category.

## 8. Server-side engineering implementation

The candidate implementation lives in the canonical Laravel application:

- `backend/app/Services/Payroll/EthiopiaPayrollCalculator.php`: exact-decimal server-side calculation, two candidate PAYE methods, explicit taxable/pensionable component treatment, pension coverage, deduction evidence, exemption evidence, non-standard pensionable salary rationale, progressive bands and a source snapshot hash.
- `backend/app/Services/Finance/FinanceControlService.php`: freezes all selected rule-source fields and payload for the entire payroll period; calls the calculator server-side for draft-line calculation; stores the calculation breakdown and evidence.
- `backend/app/Http/Controllers/Finance/FinanceControlController.php`: accepts the calculation inputs, not client-computed gross/tax/net totals; monetary values are decimal strings.
- `backend/config/payroll.php`: both Ethiopia activation flags are hard-coded default `false` until the remaining HR/legal release gates have been separately accepted.
- `backend/tests/Unit/EthiopiaPayrollCalculatorTest.php`: candidate regression, comparison, RETAINER_SERVICE exclusion, evidence gates and synthetic approved-source gate tests.

No browser-side PAYE calculator is authoritative. The UI must display server-returned line breakdowns only; client-submitted amounts are not accepted as the legal calculation result.

### 8.1 Rule provenance without a legacy version identifier

The ERP does not use the legacy fixed identifier `ET-PAYE-1395-2025-v3-PENSION-BASE` or the prior product version as a payroll ruleset. Instead, each run freezes the selected official legal source records, instrument/article references, publication/effective dates, source hashes, independent reviewer and approved parameters. The calculation creates a deterministic SHA-256 source snapshot reference.

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
