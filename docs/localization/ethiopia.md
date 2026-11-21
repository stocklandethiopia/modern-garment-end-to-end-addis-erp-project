# Ethiopia Localization Standard

Ethiopia is the first localization pack, not a hard-coded product boundary. The platform must also support other countries through country packs and effective-dated rules.

## 1. Locale and calendars

- Default tenant timezone for Ethiopia: Africa/Addis_Ababa.
- Default currency: ETB, with multi-currency and effective-dated exchange rates.
- Support Gregorian and Ethiopian calendar display/input where the workflow requires it; persist canonical dates/timestamps and record the calendar used for user entry.
- Support English and Amharic resource bundles, Unicode text, local address/contact formats and configurable document numbering.
- Store timestamps in UTC and convert using the tenant/site timezone. Do not hard-code a fixed UTC offset.

## 2. Statutory rules

Payroll, HR, tax, pension, VAT, withholding, invoicing, customs, foreign currency and reporting rules must be implemented as configurable, effective-dated country rules.

Every statutory rule must record:

- Country, region/authority and rule identifier.
- Official legal source and publication/effective date.
- Effective-from and effective-to dates.
- Calculation base, thresholds, rates, rounding and exceptions.
- Applicability by worker/entity/transaction type.
- Version, reviewer, approval and test evidence.
- Historical calculation reproducibility.

No tax rate, pension percentage, salary threshold, overtime multiplier, leave entitlement or severance formula may be guessed or copied from a sample repository. Before activation, a qualified Ethiopian payroll/accounting/legal reviewer must validate the rule against current official sources and approve the test cases.

## 3. Payroll controls

- Keep gross pay, taxable earnings, non-taxable earnings, deductions, employer contributions and net pay separately traceable.
- Preserve the exact rule version used for each payroll run.
- Validate employee eligibility, effective-dated contracts, attendance, overtime, leave, loans and benefits.
- Require maker-checker approval before posting or payment.
- Reconcile payroll totals to bank payment files and the general ledger.
- Corrections use adjustment or reversal runs with a reason and audit trail; never silently overwrite a finalized payslip.
- Restrict access to bank, tax, health, family and personal employee data.

## 4. Accounting and trade

- Configure chart of accounts, fiscal periods, tax codes, invoice formats and statutory reports per approved Ethiopian requirements.
- Support ETB functional currency and foreign currency procurement, import costs, landed cost, bank instruments and reconciliation.
- Keep exchange-rate source, date, rate and approval traceable.
- Customs, import/export and foreign currency processes require current local review before production activation.

## 5. Country-pack design

Country-specific rules live in a localization package and configuration registry, not in shared domain code. The same payroll and finance workflows must remain usable by another country with different effective-dated rules.

## 6. Acceptance gate

No Ethiopia payroll or statutory finance module is production-ready until current legal sources, approved rule tables, independent calculation examples, edge cases, historical recalculation tests, reconciliation and authorized local sign-off are recorded.
