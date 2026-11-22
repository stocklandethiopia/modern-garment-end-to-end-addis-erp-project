# Ethiopia Localization Standard

Ethiopia is the first localization pack, not a hard-coded product boundary. The platform must also support other countries through country packs and effective-dated rules.

## 1. Locale and calendars

- Default tenant timezone for Ethiopia: Africa/Addis_Ababa.
- Default currency: ETB, with multi-currency and effective-dated exchange rates.
- Support Gregorian and Ethiopian calendar display/input where the workflow requires it; persist canonical dates/timestamps and record the calendar used for user entry.
- Support English and Amharic resource bundles, Unicode text, local address/contact formats and configurable document numbering.
- Store timestamps in UTC and convert using the tenant/site timezone. Do not hard-code a fixed UTC offset.

## 2. Statutory rules and official-source discipline

Payroll, HR, tax, pension, VAT, withholding, invoicing, customs, foreign currency and reporting rules must be implemented as configurable, effective-dated country rules.

All Ethiopian fiscal, procurement, finance, workforce, customs, foreign-exchange, licensing, privacy, environmental and product-compliance rules must be sourced subject-by-subject from the relevant official authority. Use the Ministry of Justice/Federal Negarit Gazette for the legal instrument and the issuing authority for current directives, implementation notes, forms and notices. Secondary ERP/vendor commentary is not an authority.

The current primary-source inventory is maintained in [Ethiopia Legal and Regulatory Source Register](ethiopia-legal-regulatory-register.md). The register distinguishes law, regulation, directive, official implementation guidance and industry best practice.

Every statutory rule must record:

- Country, region/authority and rule identifier.
- Official legal source and publication/effective date.
- Effective-from and effective-to dates.
- Calculation base, thresholds, rates, rounding and exceptions.
- Applicability by worker/entity/transaction type.
- Version, reviewer, approval and test evidence.
- Historical calculation reproducibility.

No tax rate, pension percentage, salary threshold, overtime multiplier, leave entitlement or severance formula may be guessed or copied from a sample repository. Before activation, a qualified Ethiopian payroll/accounting/legal reviewer must validate the rule against current official sources and approve the test cases.

## 3. Tax, invoicing, finance and procurement controls

- Tax logic must distinguish taxpayer category, legal entity, business activity, VAT registration, goods/services, domestic/import/export, withholding-agent status, TIN/licence validity, transaction amount and the exact law version effective on the transaction date.
- AP must separately classify goods, services, subcontracting, freight, imported supply, employee reimbursements, creator/influencer payments and reseller incentives; do not apply one generic withholding rule.
- Support current tax invoice usage/administration and VAT registration/refund directives, and retain invoice validation, credit/debit-note, cancellation, refund and withholding-certificate evidence.
- Import and FX controls must use the consolidated NBE FXD/01/2024 amendment set and relevant Customs Commission rules, not one historical base directive.
- Procurement must distinguish public-procurement statutory scope from private-sector internal procurement controls. Proclamation 1333/2024 must not automatically be applied to every private apparel customer.
- Legal-entity commercial activities and licences must be checked before enabling a factory, wholesale depot or retail store. The product must not assume a manufacturer licence automatically authorizes retail activity.
- The ledger and tax return workpapers must preserve source documents, tax rule IDs, rates/thresholds, exchange-rate source, approval, and reconciliation back to invoices, inventory, payroll and bank.

## 4. Payroll controls


- Keep gross pay, taxable earnings, non-taxable earnings, deductions, employer contributions and net pay separately traceable.
- Preserve the exact rule version used for each payroll run.
- Validate employee eligibility, effective-dated contracts, attendance, overtime, leave, loans and benefits.
- Require maker-checker approval before posting or payment.
- Reconcile payroll totals to bank payment files and the general ledger.
- Corrections use adjustment or reversal runs with a reason and audit trail; never silently overwrite a finalized payslip.
- Restrict access to bank, tax, health, family and personal employee data.

## 5. Accounting and trade

- Configure chart of accounts, fiscal periods, tax codes, invoice formats and statutory reports per approved Ethiopian requirements.
- Support ETB functional currency and foreign currency procurement, import costs, landed cost, bank instruments and reconciliation.
- Keep exchange-rate source, date, rate and approval traceable.
- Customs, import/export and foreign currency processes require current local review before production activation.

## 6. Country-pack design

Country-specific rules live in a localization package and configuration registry, not in shared domain code. The same payroll and finance workflows must remain usable by another country with different effective-dated rules.

## 7. Acceptance gate

No Ethiopia payroll or statutory finance module is production-ready until current official legal sources and implementing directives, approved rule tables, independent calculation examples, edge cases, historical recalculation tests, reconciliation and authorized local sign-off are recorded.

## 8. Effective-dated 2026 source checkpoints

The register currently records the following discovered instruments as source checkpoints only; it does not authorize automatic calculation:

- Income Tax (Amendment) Proclamation No. 1395/2025 — employment income tax table and digital-content income definition.
- Federal Income Tax Regulation No. 597/2026 — further digital-content creator tax treatment and reporting.
- VAT Proclamation No. 1341/2024; VAT Regulation No. 570/2025; VAT Registration Directive No. 1104/2026; Tax Invoice Usage and Administration Amendment Directive No. 1099/2026; VAT Refund Determination Directive (18 Apr 2026).
- Pension Proclamation No. 1268/2022 and directives 1086/2025 and 1087/2025.
- Customs Proclamation Further Amendment No. 1425/2026.
- NBE FXD/01/2024 as amended by FXD/03/2025, FXD/04/2026 and FXD/05/2026; check FVD/01/2026 for applicable import modality.
- Investment incentive Regulation No. 586/2026 and implementing Directive No. 1064/2025.
- Tax Administration Amendment Proclamation No. 1434/2026; resolve official catalogue metadata discrepancies against its Gazette text.
- Business Licensing Proclamation No. 980/2016 as amended by 1150/2019, Labour Proclamation 1156/2019, and Financial Reporting Proclamation 847/2014.

The register must be reviewed and updated for each release and whenever an official authority publishes a change.

