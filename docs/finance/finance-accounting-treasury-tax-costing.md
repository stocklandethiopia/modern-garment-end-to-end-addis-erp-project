# WP-13 — Finance, Accounting, Treasury, Costing and Statutory Finance

**Package:** WP-13  
**Status:** DESIGN COMPLETED — statutory parameter activation and live banking remain gated  
**Scope:** Approved Domain J, architecture standard, Ethiopia localization and legal-source register. Establishes financial transaction ownership and interfaces with procurement, inventory, commerce, manufacturing and payroll.

## 1. Purpose and accounting architecture

Finance is the authoritative owner of ledgers, journals, AP, AR, cash/bank, financial close, tax records, costing and management financial statements. Operational modules own the source business event (receipt, sale, production output, time, payroll result); finance validates and posts the accounting effect once.

The system must preserve:
- Double-entry balanced journals and immutable posting evidence.
- Separate subledgers and reconciliation to GL.
- Legal entity, functional currency, transaction currency, rate source and effective date.
- Inventory valuation and production/WIP cost layers.
- Supplier liability, customer receivable, cash/bank and statutory tax liabilities separately.
- Effective-dated accounting policy and legal/tax rule version.
- Correction by reversal/adjustment, not silent edit of posted history.
- Period close controls, maker-checker and audit.
- Customer-specific reporting framework and Ethiopian statutory requirements after qualified review.

## 2. Finance context boundaries

| Context | Owns | Other context supplies |
|---|---|---|
| General Ledger | Chart of accounts, fiscal periods, journals, balances, consolidations, close | Approved source postings |
| Accounts Payable | Supplier invoices, credit/debit notes, due dates, payment proposal status | PO, receipt, contract and supplier tax evidence |
| Accounts Receivable | Customer invoices, credit notes, receipts, allocations, disputes and collections | Confirmed sale/delivery/billing trigger |
| Cash and Treasury | Cash/bank accounts, cash position, payment batches, bank statement, reconciliation, FX exposure | Authorized payment instructions and bank evidence |
| Inventory Costing | Inventory cost layers, landed cost, WIP and finished goods valuation | Receipt, issue, route output, stock movement |
| Product/Manufacturing Costing | Standard/target/actual cost, absorption, variances and margin | BOM, SAM, labour, overhead, quality and output |
| Tax | Tax profile/rule versions, taxable transactions, VAT/withholding/returns/authority receipts | Operational and financial transaction facts |
| Fixed Assets (integrated finance domain) | Asset register, acquisition, depreciation, disposal and impairment | Procurement/project/site and accounting evidence |
| Budget and Profitability | Budget versions, commitments, forecast, cost centre, product/channel/account profit | Commercial plans, procurement commitments, operational cost facts |

Finance may expose APIs and read models, but it cannot change the source of a warehouse receipt, accepted garment count, approved time or customer delivery.

## 3. Entity, ledger and reporting setup

Each legal entity has:
- Statutory/company identifier, tax registrations and jurisdiction.
- Functional/base currency and allowed transaction currencies.
- Fiscal year/calendar, accounting periods and local timezone.
- Chart of accounts, dimensions, accounting policies and retained earnings.
- Reporting framework (IFRS, IFRS for SMEs, or other framework as adopted/required by AABE for entity/reporting class).
- Consolidation group and intercompany relationships.
- Authorized signers, maker-checker, delegated posting thresholds and payment authority.
- Effective-dated tax, VAT, withholding, pension/payroll integration and customs profiles.

Do not assume every tenant uses one ledger, chart or reporting framework. Do not assume all Ethiopian private entities have identical statutory reporting obligations. AABE adoption and financial reporting class must be reviewed per entity.

## 4. Double-entry GL and journal lifecycle

Journal lifecycle: DRAFT → VALIDATED → PENDING_APPROVAL (if required) → POSTED → REVERSED/ADJUSTED.

A journal header records entity, ledger, accounting date, document date, source system/document, transaction and functional currencies, FX source/rate/date, period, description, origin, correlation/idempotency key and approval. Each line records account, debit/credit, currency and functional amount, dimensions (cost centre, site, department, product/channel/project where policy requires), tax, counterparty and source reference.

Rules:
1. Journal must balance in each required currency according to entity policy.
2. Period must be OPEN; restricted period requires approved controlled reopening.
3. Account and dimension combinations must be valid/effective.
4. Source event must be authorized, unique and fully reconciled.
5. Journal posting is atomic; no partial line write.
6. Posted journals are immutable; corrections use linked reversal/replacement with reason and approval.
7. Duplicate source event/idempotency key cannot post twice.
8. Backdated posting requires policy and approval; it cannot alter a closed statutory return without controlled amended filing.
9. RLS/entity scoping and least privilege enforced at all layers.
10. Journal number sequence is entity/ledger/period controlled; no invented statutory invoice or receipt numbering.

## 5. AP, supplier liability and procure-to-pay

AP workflow:
1. Invoice received/captured with supplier, legal entity, invoice number, date, currency, gross/net/tax, due date and document checksum.
2. Match against approved PO/contract and actual receipt/accepted service.
3. Check duplicate invoice, supplier status, beneficiary, tax registration, VAT invoice compliance and withholding applicability.
4. Resolve quantity/price/tax/quality variance; hold if unresolved.
5. Separate advance/prepayment, expense invoice, inventory invoice, subcontract service and capital asset acquisition.
6. Approve invoice independently and post AP liability/appropriate asset/expense/inventory clearing.
7. Build payment proposal based on terms, cash, bank and authorized payment modality.
8. Dual approval and execution evidence through bank adapter/manual controlled process.
9. Reconcile bank settlement, supplier statement, withholding certificate and residual balance.

AP never creates a second warehouse receipt. An invoice for an advance does not simulate delivered inventory. Supplier bank beneficiary change requires separate verification.

## 6. AR, revenue and receivables

AR workflow:
- Billing trigger follows contract/account and delivery/acceptance/customer terms.
- Validate legal entity, account, tax profile, price/discount/promotion and source fulfilled quantity.
- Issue invoice with proper tax document sequence and required customer/source data.
- Apply receipt (cash/bank/card/mobile/marketplace settlement) to invoice, manage partial/unapplied amounts and advances.
- Manage credit notes, returns/refunds, disputes, overdue aging and collection.
- Reconcile channel settlements, payment processor/marketplace deductions and fees.
- Close customer balance only with documented settlement/credit/write-off under authority.

Revenue recognition policy is configured under the entity's reporting framework and reviewed by finance/auditor; ERP must not equate order placement or POS tender authorization to revenue recognition automatically. A customer order, dispatch, delivery, acceptance and payment are distinct facts.

## 7. Treasury, cash, bank and foreign currency

Maintain legal-entity-owned bank/cash accounts, currencies, authorized signers, bank connection, statement import, reconciliation, cash forecast, liquidity and payment approvals. Sensitive account data is encrypted/masked and access audited.

FX rate record includes source, rate type, from/to, precision, valid time, fetched/imported time, authority/source evidence, reviewer and approval policy. Preserve:
- Transaction rate used at recognition.
- Approved settlement rate and bank evidence.
- Period-end revaluation rate/source.
- Realized/unrealized FX gain/loss and rate variance.

NBE Foreign Exchange Management directives require amendment-aware legal mapping. The legal-source register lists FXD/01/2024, FXD/03/2025, FXD/04/2026, FXD/05/2026 and FVD/01/2026; the platform must consolidate exact clauses, modalities and effective dates before enabling a real transaction. Import purpose, advance payment, LC/CAD and FX retention are not inferred from a supplier invoice. If a legal modality or bank precondition is uncertain, hold actual payment/declaration; allow draft documentation and reviewer queue.

## 8. Inventory and product costing

Costing supports a customer-approved method (e.g. weighted average, FIFO, standard cost, specific identification where appropriate) by entity/item class and period. The system cannot choose one method silently for all companies.

Cost elements:
- Purchase/material and non-recoverable tax.
- Freight, insurance, customs duty and clearing/handling eligible for landed-cost capitalization.
- Fabric yield, marker/lay consumption, trims, chemical and packaging.
- Direct labour using approved SAM/SMV and lawful labour-cost basis.
- Variable/fixed factory overhead with documented allocation basis.
- Subcontract process, test/certification and rework where policy allows.
- WIP by production order/route step; finished goods by lot/style.
- Abnormal waste, idle capacity, scrap and variances separately identified.

Actual consumption and accepted output are captured from operational source events; cost engine values them using approved policy and rate versions. The product cost roll-up must avoid double-counting operation SAM, fabric waste or landed cost. Standard costs are frozen/effective-dated; actual-to-standard variance remains visible. Inventory valuation and production cost reconciliation is part of period close.

## 9. Budget, commitment and profitability

Budget is versioned by legal entity, cost centre, department, site, project, season, brand, channel and period. A commitment can be created by approved PO, contract, payroll obligation or capital project as policy defines. Available budget reconciles approved budget minus commitments and actual postings. Budget does not replace OTB; merchandising's OTB is a commercial range-planning control and reconciles to finance budget/commitments under documented rules.

Profitability views:
- Tenant/consolidated group and legal entity.
- Brand/season/collection/style/variant.
- Factory/site/department/line or production order.
- Channel/store/wholesale customer/reseller/account.
- Shipment, market and currency.
- Customer/creator campaign or contract.

Revenue, discounts, returns, tax, fees, freight, commissions, COGS, labour and overhead use declared basis. Management margin is distinct from statutory statement presentation and can be analyzed without writing GL.

## 10. Ethiopia tax/legal rule architecture

The statutory rules engine is an effective-dated, source-versioned configuration service:
- Authority, exact instrument/Gazette/directive, clause, original text and controlled interpretation.
- Publication, commencement, effective/transition/supersession and tax-period treatment.
- Jurisdiction, entity type/legal activity, taxpayer category, supplier/customer/worker classification, transaction type and threshold basis.
- Rate, amount, base, cap, exemption, credit, rounding, filing calendar, evidence and report format.
- Local professional reviewer, customer approver, test vector and activated version.
- Historical transaction reference to exact rule version.

The tax engine has DRAFT, REVIEW_REQUIRED, APPROVED_FOR_CONFIGURATION, TESTED, APPROVED_FOR_ACTIVATION, ACTIVE, SUPERSEDED states. Code or configuration can be built and tested in non-production; a customer-side statutory activation requires qualified local sign-off and accountable customer approval.

### 10.1 VAT and tax invoices
Support VAT registration status and applicable threshold/rules per legal entity/activity/period; input/output tax, exempt/zero-rated/out-of-scope distinction, creditability, tax invoice number/date, cancellation/reissue, adjustment/credit note, refund claim and return. Tax invoice process must maintain current MoR/MoF directive/version and electronic invoice integration where applicable. The 2024 VAT Proclamation No. 1341/2024, Regulation 570/2025 and separate VAT Registration Directive 1104/2017 E.C., Tax Invoice Usage Amendment Directive 1099/2017 E.C. and refund directive remain independently sourced. The full refund eligibility text and all entity/transaction applicability need professional review before automatic refund calculations. Do not reuse old VAT assumptions.

### 10.2 Income tax and withholding
Income Tax Amendment Proclamation 1395/2025 is identified in the Federal Negarit Gazette No.64, 1 September 2025 (parallel Amharic identifier 1395/2017 E.C.); the MoJ English catalogue metadata variant is preserved separately in WP-02. Employment monthly rates are stored effective-dated, but progressive taxable base, deductions and transition must be validated from full law. Article 97 withholding is restricted to the covered withholding agent, supplier and transaction conditions; 3% applies only to the defined covered goods/services thresholds; 30% applies only in the specified missing TIN/licence case. No blanket deduction is allowed.

### 10.3 Digital creator income
Proclamation 1395/2025 Article 22 distinguishes business income and Schedule D other income and requires the Tax Authority directive under Article 22(3) to determine when digital content creation constitutes business. Regulation 597/2026 provides creator Schedule C/Schedule D and platform reporting, 15% Schedule D and threshold/payment provisions. Cases not unambiguously classified by the complete law remain REVIEW_REQUIRED pending official directive/clarification and local tax review. Creator fee, sponsorship, affiliate, in-kind and non-cash value remain distinct source transaction types, not assumed payroll.

### 10.4 Minimum alternative tax
Proclamation 1395/2025 Article 23 and Regulation 597/2026 Articles 57–59 create a 2.5% framework with taxpayer/activity-specific bases, credits and exemptions. It must not be applied as a blanket 2.5% on every tenant/sale. The correct scope/base/credit/transition must be approved before activation.

### 10.5 Pension, payroll and public procurement
Pension contributions and payroll calculations are prepared in WP-14, with effective-dated legal source and professional verification; finance posts only approved payroll liabilities. Public Procurement Proclamation 1333/2024 is applicable only where entity is legally covered, including relevant public enterprises. Private sourcing best practices do not become statutory tender rules. No historic synthetic PAYE or legacy 49-table calculation is reused.

### 10.6 Customs and investment incentives
Store shipment/HS/origin/value/regime, customs declaration and assessment, tariff source, current amendments (including Proclamation 1425/2026), permit/incentive decision and evidence. FX and customs are linked to landed cost but not assumed from one static duty table. Incentive eligibility is entity/permit/activity/location/time-specific and needs official evidence and local customs/tax professional review.

## 11. Close, reconciliation and audit

Period close checklist:
- AP supplier statements, invoice holds and GRNI.
- AR customer balance, cash receipts, credits and ageing.
- Bank/cash reconciliation and payment outstanding.
- Inventory ledger/balance, physical count, in-transit, consignment and valuation.
- Production/WIP/finished goods and standard/actual variance.
- Payroll and pension liabilities, statutory tax withholdings and remittance.
- Fixed assets/capital work, depreciation and disposal.
- VAT/output-input, withholding, returns and tax control accounts.
- Intercompany due-to/due-from and elimination.
- Suspense, accruals, FX revaluation, allocations and journal review.
- Management and statutory statement disclosures and audit evidence.

Close stages: OPEN → PRE_CLOSE → RECONCILIATION → REVIEW → CLOSED → LOCKED. Reopening requires independent approval and reason, preserves previous close and records adjustments. All reconciliation exceptions have owner, amount, age, due date and resolution evidence.

## 12. Data model

| Entity/table | Purpose / minimum fields |
|---|---|
| accounting_frameworks | framework, entity/reporting class, authority/adoption source, effective period |
| charts_of_accounts / accounts | entity, code, account type, normal balance, control account and effective period |
| fiscal_calendars / fiscal_periods | entity calendar, period dates, status and close lock |
| ledgers | entity, ledger type, currency, chart and accounting policy |
| journal_headers / journal_lines | source event, accounting date, currency, rate, account, debit/credit, dimensions and approvals |
| posting_batches | validated source set, idempotency, posting status and reconciliation |
| ap_invoices / ap_invoice_lines | supplier, invoice, receipt/PO refs, tax, due, match status, liability |
| ap_credit_notes / prepayments | supplier adjustment or advance, source, allocation and balance |
| payment_proposals / payment_batches | supplier, bank, currency, amount, approvals, bank reference and settlement |
| ar_invoices / ar_invoice_lines | customer/order/delivery, tax, currency, due and source |
| ar_receipts / receipt_allocations | payment source, amount, currency, invoice allocation and unapplied balance |
| credit_notes / refunds / disputes | original invoice/payment, reason, approval, amount and settlement |
| bank_accounts / bank_statements | legal entity, masked bank ref, currency, statement evidence and import |
| bank_reconciliations | ledger item, statement line, match status, reviewer and variance |
| exchange_rate_sources / exchange_rates | authority/provider, rate type, currency pair, value, valid time and approval |
| inventory_cost_layers | item/location/lot, method, quantity, cost, currency and source receipt |
| landed_cost_allocations | shipment/receipt, cost pool, source invoice/duty/freight, base and allocation |
| work_order_costs / wip_cost_layers | order, operation, materials, accepted output, labour and overhead |
| cost_variances | standard/actual, cause, scope, amount and disposition |
| budget_versions / budget_lines | entity, cost centre/site/channel/project, period, currency and approval |
| commitments | approved PO/contract/payroll/capex source and remaining balance |
| tax_profiles / legal_rule_versions | authority, law, clause, version, scope, effective dates and approval |
| tax_transactions / tax_calculations | source transaction, tax type, base, rate/version, exception and draft/approved state |
| tax_returns / tax_return_lines | period, entity, rule versions, source transactions, approval and submission receipt |
| withholding_certificates | supplier/payee, source payment, type, amount, rule version and document |
| customs_declarations / duty_assessments | shipment, HS, origin, valuation, regime, rule, authority outcome |
| fx_exposures / fx_settlements | currency, source, purpose, bank, directive version, transaction and realized result |
| close_checklists / reconciliations | period, owner, subledger source, balances, differences and approval |
| consolidation_runs / intercompany_matches | group, entity balances, elimination, currency and approval |

## 13. API and accounting event contract

Illustrative endpoints:
- POST /api/v1/finance/journals/validate; POST /api/v1/finance/journals/{id}/approve|post|reverse.
- POST /api/v1/ap/invoices; POST /api/v1/ap/invoices/{id}/match; POST /api/v1/payments/proposals.
- POST /api/v1/ar/invoices; POST /api/v1/ar/receipts; POST /api/v1/ar/credit-notes.
- POST /api/v1/bank-statements/import; POST /api/v1/bank-reconciliations.
- POST /api/v1/costing/landed-cost; POST /api/v1/costing/work-orders/close.
- POST /api/v1/tax/calculations/draft; POST /api/v1/tax/returns/submit (submit action requires configured approved gateway/authority process).
- POST /api/v1/finance/periods/{id}/close.

Source events: SupplierInvoiceMatched.v1, GoodsReceiptValued.v1, CustomerInvoiceReady.v1, PaymentSettled.v1, InventoryMovementPosted.v1, ProductionOrderCosted.v1, PayrollApproved.v1, TaxRuleActivated.v1. Finance posts one journal per authorized accounting source event according to mapping/version. All consumers deduplicate by event ID and source document.

## 14. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-FIN-001 | Journal has unequal debit and credit | Posting rejected before any line is persisted |
| AT-FIN-002 | Same source event is retried | One journal posting only |
| AT-FIN-003 | Supplier invoice has no accepted receipt where 3-way match required | AP held; no stock receipt inferred |
| AT-FIN-004 | Advance supplier invoice is approved | Prepayment recorded, not fictitious inventory |
| AT-FIN-005 | Customer sends partial payment | Receipt allocated; residual AR remains open |
| AT-FIN-006 | POS payment authorization succeeds but settlement not confirmed | Not reported as bank cash settlement |
| AT-FIN-007 | Landed cost is allocated to multiple receipt lots | Same source cost is not double capitalized |
| AT-FIN-008 | Actual production material/labour differs from standard | Variance is visible with source evidence |
| AT-FIN-009 | FX rate is from unapproved source/date | Payment/valuation workflow holds or requires review |
| AT-FIN-010 | Creator income is ambiguous under Article 22(3) | Tax classification REVIEW_REQUIRED; no automatic tax formula |
| AT-FIN-011 | Proclamation 1434/2026 metadata is unresolved | Tax administration parameter is not activated |
| AT-FIN-012 | MAT 2.5% rule is requested for an ineligible/unknown taxpayer | No universal calculation; hold for applicability review |
| AT-FIN-013 | VAT refund source text/applicability is incomplete | Claim evidence can be collected; automatic entitlement blocked |
| AT-FIN-014 | Closed period journal must be corrected | Controlled reopen/reversal/amended filing, history preserved |
| AT-FIN-015 | Financial statement framework differs by legal entity | Entity reporting uses effective AABE-adopted framework |

## 15. WP-13 handover

**Completed:** finance boundaries, GL, AP/AR, treasury/cash/FX, costing/landed cost/WIP, budget/commitment, Ethiopia tax-source architecture, VAT/withholding/creator/MAT/pension/customs safeguards, close/reconciliation, schema, APIs/events and 15 acceptance scenarios.

**Not claimed:** no financial statements were prepared, no customer chart of accounts or accounting policy was approved, no tax return/invoice/payment was submitted, no statutory rate was activated and no bank connection was made. This is a design and rule-governance specification, not an accountant's legal opinion.

**Dependencies:** WP-02 legal rule register; WP-03 entity/ledger foundations; WP-04 OTB/budget plan; WP-05 cost BOM; WP-07 PO/receipts/customs; WP-09 production actuals; WP-12 sales/inventory/returns; WP-14 payroll liabilities; WP-15 analytics.

**Decision:** internally accepted under delegated execution. Proceed to WP-14 — HR, Workforce, Payroll, Pension, Benefits and External Compensation.
