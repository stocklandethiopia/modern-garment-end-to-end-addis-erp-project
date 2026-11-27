# Ethiopia Payroll — Engineering Release Gate

**Work package:** WP-14  
**Decision:** <badge> HOLD — NOT APPROVED FOR LIVE PAYROLL </badge>  
**Scope:** Ethiopia private-organization employee payroll localization  
**Applies to:** New Laravel server-side payroll implementation only

## 1. Release decision

The calculation component and candidate regression are in source control. This does **not** mean Ethiopia payroll is ready for live use.

The separate WP-14 implementation candidate in PR #20 contains both activation settings in `backend/config/payroll.php`, defaulted to `false`:

- `ethiopia.employee_master_release_ready`
- `ethiopia.statutory_activation_enabled`

In that PR #20 candidate, the Laravel service methods for adding a payroll calculation line, submitting for professional review and recording the professional decision call the server-side release-gate assertion before doing those actions. There is no environment-variable shortcut. Enabling either setting requires a separately reviewed source change after this gate and the WP-14 / WP-16 criteria below pass.

**Branch and runtime evidence boundary:** PR #19 is a documentation/plan branch. It does not contain `backend/config/payroll.php` or the payroll service implementation, and therefore does not itself enforce the settings. The separate WP-14 source candidate is PR #20, currently at a different branch and SHA; its exact code must be reviewed and tested independently. The canonical delivery branch does not receive the PR #20 runtime protection until that source candidate is integrated. Creating a metadata-only DRAFT header is distinct from calculating or submitting payroll. This document is the required release contract; it is not proof that the payroll gate has been deployed, accepted or enabled.

No real payroll, posted transaction, payment, tax filing or pension remittance is performed by this package.

## 2. Official legal-source access gate

For every instrument used by a production rule:

- Obtain the complete official Federal Negarit Gazette or issuing-authority document.
- Prefer the controlling Amharic text; preserve any authorized English translation and record translation review.
- Store the original document bytes in controlled legal evidence storage and calculate SHA-256 from those bytes.
- Record instrument number/title, authority, exact article/sub-article/schedule, publication date, effective-from/to, transitional provisions, jurisdiction and worker/employer applicability.
- Record the official URL, source digest, reviewer, review date, professional qualification and approval reference.
- Reconcile amendments, regulations and directives in precedence order. A catalogue listing or explanatory note alone is not the complete law.
- Reject missing/invalid hashes, non-official URLs, missing articles, stale applicability, or records not independently marked `PROFESSIONAL_APPROVED`.

### Required source-set review

| Source | Legal area | Current evidence condition |
|---|---|---|
| Proclamation 1395/2025, Article 11 and transition clauses | Monthly employment-income rates and effective dates | Official Gazette text accessed; local professional review and exact production rule approval remain required |
| Proclamation 979/2016, especially employment-income and PAYE provisions, plus subsequent amendments | PAYE base, employment income, exemptions and withholding | Ministry of Revenue Technical Notes are helpful but expressly non-binding; authoritative law text and amendment chain must be captured and reviewed |
| Regulation 597/2026 and applicable provisions of Regulation 410/2017 | Freelance/digital/remote-service classification and withholding documents | Official MoJ Gazette PDF identified; complete application to actual payee facts and professional review remain required |
| Pension Proclamation 1268/2022, Articles 2, 3, 6, 10–12 | Coverage, pension salary, 7%/11%, registration and remittance | Official Federal Negarit Gazette text accessed; employee-specific coverage and salary classification remain required |
| Directives 1085/2025, 1086/2025 and 1087/2025 | Private-organization pension implementation, debt collection, contribution collection/transfer/supervision | Ministry of Justice catalogue entries located; complete texts, clauses and current application must be downloaded, hashed and reviewed |
| Labour Proclamation 1156/2019 and applicable amendments/directives | Working time, overtime, lawful deductions, wage register, leave, termination | MoJ status page identifies the proclamation as In Force; exact clauses, later amendments, Amharic wording and professional payroll review are required |

Do not treat the MoR Technical Notes as binding law. Their explanation of Article 10 is a legal signal that must be considered in the tax-base opinion, not a replacement for the authoritative proclamation.

## 3. Mandatory written PAYE-base decision

The payroll rule bundle must explicitly select one of these methods:

- `TAXABLE_GROSS`
- `TAXABLE_GROSS_LESS_EMPLOYEE_PENSION`

The following are mandatory before either method can be used for a production payroll line:

1. A written opinion from a qualified Ethiopian tax practitioner or lawyer.
2. Exact citations to the controlling Income Tax Proclamation / Regulations and the Private Organization Employees’ Pension Proclamation / directives.
3. Analysis of “employment income”, statutory exemptions and the legal character of mandatory employee pension.
4. An explicit approved method, not just a general “reviewed” stamp.
5. The reviewer’s name/qualification/identity, independence from the payroll preparer, review date, opinion reference and supporting article references.
6. Matching approval in the frozen Income Tax rule payload.

The method in the rule payload must match the opinion's approved method. If the opinion is absent, qualified status is unclear, provisions conflict, or the opinion does not address the pension interaction, production payroll remains blocked.

### ETB 30,000 candidate regression

The server-side unit suite preserves both candidate results:

| Candidate basis | PAYE base | PAYE | Employee pension | Net pay |
|---|---:|---:|---:|---:|
| Gross taxable | 30,000.00 | 8,450.00 | 2,100.00 | 19,450.00 |
| Gross taxable less employee pension | 27,900.00 | 7,715.00 | 2,100.00 | 20,185.00 |

The pension-first result is a **candidate-only regression** until the written opinion approves it. The 735 ETB difference is material and must be explained in the customer activation record. The unit test passing does not approve the legal method.

## 4. Pension gate

A production rule bundle must explicitly state:
- employee and employer contribution basis points from the approved source;
- statutory salary basis, including the regular-working-hours basis;
- coverage criteria, 45-day service fact and any applicable nationality, prior-scheme, agreement or exclusion decision;
- effective dates and applicability to the legal employer and employee;
- approved treatment of variable remuneration, allowances and non-standard contracts;
- remittance period, due date, filing/payment reference, actual receipt and reconciliation process;
- late contribution interest/penalty treatment as confirmed by the current law/directives.

Employee pension reduces employee net pay. Employer pension is an employer cost and must never be deducted from employee earnings.

The current application source does not yet contain a complete authoritative employee master, contract, assignment and employee statutory-classification resolver. The `employee_master_release_ready` gate must stay false until those sources are implemented and tested.

## 5. Compensation classification gate

Each earning component must independently record:
- tax treatment: taxable or exempt;
- pension treatment: pensionable or non-pensionable;
- amount, currency, code, effective dates and source event;
- supporting legal/contract evidence and approval.

An exemption requires the exact current statutory provision, article, applicability facts, caps/limits and documentary proof. A display name such as housing, transport, meal, per diem, medical, bonus or reimbursement never grants tax exemption.

A reimbursement remains a separate expense-reimbursement workflow with purpose, receipt, business-travel/expense authorization and approval. It is not silently added to taxable or pensionable wages.

A pensionable salary that differs from the standard regular-hours salary basis requires a reason, contract reference, legal basis, supporting source, evidence hash, independent reviewer and approval date. The system must hold unexplained overrides.

## 6. Nonemployee-payee exclusion gate

`RETAINER_SERVICE` is not an employee payroll category. It must not enter the employee PAYE or pension calculator. Route it to the actual vendor/AP or individual-payee/legal-tax review workflow, retaining contract, deliverables, residency, TIN/licence, tax category and withholding decision. Regulation 597/2026 must be considered where digital, freelance or remote-service income is involved. No tax rate is inferred from a dropdown or payee label.

## 7. Server-side and data-integrity gate

- Laravel is the authoritative calculation layer. Angular may display the server result but cannot determine official PAYE, pension, exemptions, taxable base or net pay.
- The payroll API accepts decimal strings with at most two fractional digits; it does not accept client-computed gross, PAYE, employee pension, total deductions or net pay.
- The server calculates from a frozen rule set and verified inputs.
- A payroll run must contain exactly one selected Income Tax and one Pension rule record for this calculator, each covering the complete payroll period.
- Both records must have official source URL/hash, instrument/authority/title, publication/effective dates, independent reviewer and professional approval reference.
- Each rule payload must include supporting official sources with exact articles, dates, source hashes and independent review evidence.
- The tax rule payload must include monthly bands, explicit rounding method, approved tax-base method and separate tax-base opinion.
- The pension payload must include employee/employer rates, pension salary basis and approved rounding method.
- The result stores line inputs, breakdown, rule-source snapshot and deterministic SHA-256 snapshot hash.
- The line is a draft calculation only. No calculation result is posted to the GL or transmitted to a bank or authority here.

## 8. Historical transaction protection

This change does not recalculate or rewrite posted payroll, old payslips, posted journals, bank payments, tax filings or pension remittances. Historical periods remain tied to their original approved snapshots. Any lawful correction must use an authorized adjustment/reversal run with source linkage and an independent approval trail.

## 9. Minimum test/acceptance gate

| ID | Scenario | Required outcome |
|---|---|---|
| ETH-PAY-001 | ETB 30,000 gross-base candidate | Taxable base 30,000; PAYE 8,450; net 19,450 |
| ETH-PAY-002 | ETB 30,000 pension-first candidate | Taxable base 27,900; PAYE 7,715; net 20,185; test-only until opinion approves |
| ETH-PAY-003 | Employer contribution | 11% is not deducted from employee net |
| ETH-PAY-004 | Non-covered worker | No private-scheme contributions after approved coverage decision |
| ETH-PAY-005 | `RETAINER_SERVICE` | Rejected from employee payroll |
| ETH-PAY-006 | Exempt component without exact legal evidence | Rejected/held |
| ETH-PAY-007 | Non-standard pensionable salary without rationale | Rejected/held |
| ETH-PAY-008 | No approved income-tax/pension rules | Payroll calculation held |
| ETH-PAY-009 | PAYE base opinion absent or inconsistent | Payroll calculation held |
| ETH-PAY-010 | Official source URL/hash missing or invalid | Payroll calculation held |
| ETH-PAY-011 | Rule effective dates do not cover the complete payroll period | Payroll calculation held |
| ETH-PAY-012 | Missing rounding method | Calculation rejected; no default |
| ETH-PAY-013 | Decimal precision / band boundaries / ties | Exact-cent calculations under the approved rounding rule |
| ETH-PAY-014 | Duplicate worker line or replay | Unique/run idempotency constraint prevents duplicate payroll line |
| ETH-PAY-015 | Posted historical payroll opened after a rule update | Original historical record remains unchanged |
| ETH-PAY-016 | No employee/contract/assignment/statutory profile source | Default-off activation gates prevent submission/approval |

Further WP-16 tests must cover tenant RLS, authorization, concurrent run/line creation, legal-source hash tampering, role segregation, privacy, payroll-to-GL reconciliation, remittance reconciliation, restore/rollback and full factory/branch UAT.

## 10. Activation approval checklist

- [ ] Official Income Tax Proclamation / regulation / amendment chain acquired and hashed.
- [ ] Written Ethiopian tax opinion approves the PAYE-base method and rounding.
- [ ] Official Pension Proclamation and all relevant directives acquired and hashed.
- [ ] POESSA/local specialist approves coverage and pensionable-salary treatment.
- [ ] Employee master, contracts, assignments and statutory profile resolution exist and are server-verified.
- [ ] Component tax/pension classification register is approved.
- [ ] Exemptions and non-standard salary cases have individual evidence.
- [ ] Candidate and negative tests pass on the exact source SHA.
- [ ] PostgreSQL/RLS, concurrency, privacy, adjustment and accounting tests pass.
- [ ] WP-16 security, recovery, performance and UAT gates pass.
- [ ] Both activation settings are changed only in a reviewed, evidence-linked release commit.

**Current disposition: HOLD.** The source package intentionally leaves all activation boxes unchecked.
