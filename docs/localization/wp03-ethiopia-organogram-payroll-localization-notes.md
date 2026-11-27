# WP-03 / WP-14 — Ethiopia Organogram, Entity, Branch and Payroll Localization Notes

**Document ID:** ETH-ORG-PAY-LOCAL-001  
**Prepared:** 27 November 2026  
**Project:** Modern Garment End-to-End Addis ERP  
**Package placement:** WP-03 enterprise/legal-entity/organization/site foundation; dependent payroll and employment rules remain WP-14; legal source governance originates in WP-02.  
**Status:** Engineering localization analysis / evidence register supplement — NOT a legal opinion, not a statutory-rule approval and not production activation authorization.  
**Source repository baseline reviewed:** \`delivery/autonomous-wp-01-16\` at \`fc01eced62b97c4ec27f0075264fc8ba0fb11851\`.

## 1. Purpose and evidence convention

This note connects the current WP-03 enterprise foundation and new organogram/Delegation of Authority (DoA) design to Ethiopia's legal and regulatory context, and records the payroll-localization findings that WP-14 must consume. It supplements, and does not replace, \`docs/localization/ethiopia-legal-regulatory-register.md\`, \`docs/localization/ethiopia.md\`, the organogram design and the WP-03/WP-14 implementation plan.

Evidence labels:
- **Source-confirmed:** the cited official legal-source record or supplied evidence pack states the item as described. It is time-bounded to the stated source/research date.
- **Engineering consequence:** recommended system behaviour inferred from the law or the governing project design.
- **Interpretation required:** clause interaction, facts, employer/entity applicability or legal effect needs a qualified Ethiopian professional.
- **Not verified / unresolved:** exact source, amendment, authority practice or applicable facts remain missing.
- **Not activated:** never treat a design note, test fixture, candidate rate or sample source as an executable statutory rule.

This work distinguishes legal text from implementation choices. A law may impose a requirement; the design below proposes how the ERP should evidence, enforce, version and test it. It does not determine the legal position for every legal form, employer, region, employee category, licence activity or transaction.

## 2. Supplied payroll evidence ZIP — provenance and limits

The supplied file \`Ethiopia_Payroll_Localization_Analysis_and_ERP_Evidence (2).zip\` was inspected. Its SHA-256 is:

\`\`\`text
f97a69b6b77c9ecade42c6e1c250b77467c1b58ae42b8d284447df350d0f6af4
\`\`\`

The ZIP README says its research cut-off is 27 November 2026, its focus is federal Ethiopian private-organization employee payroll, and it is an analysis/technical reference—not a legal opinion, executable payroll engine, installer, payroll filing, government approval, live payroll record or substitute for professional advice.

Contents include:
- Ethiopian payroll legal analysis;
- illustrative candidate payroll calculations;
- article-to-rule-to-ERP requirements matrix;
- ERP sample source/evidence map;
- CI/test evidence description;
- legal source register;
- selected PHP/SQL source excerpts and SHA-256 inventory.

The sample source evidence identifies a prior, separate Stockland ERP source/installer artifact (artifact #14104031868; the evidence pack also cites a source checkout SHA). Its CI record is from a separate historical product and test environment. It reports synthetic payroll calculation tests and specifically states that those tests do not prove Ethiopian legal rates or the PAYE base. It also records a MySQL 8 historical upgrade-path blocker on that different product. Neither result is a build, migration or CI result for the new Modern Garment ERP.

**Mandatory reuse rule:** use this ZIP to understand patterns such as source-backed/effective-dated rules, frozen calculation snapshots, exact money arithmetic, fail-closed approval, employee/nonemployee separation and separately reconciled payroll/payment/remittance states. Do not copy its database schema, PHP classes, product/version identifier, legacy HR tables, role assumptions, migration history, CI result, tax constants or sample rule payload into the new ERP. The new ERP's architecture remains the Laravel 13 modular monolith, PostgreSQL 18 and Angular 22 design governed by its own repo.

## 3. Ethiopian company form, governance and organogram localization

### 3.1 Commercial Code and legal-form-aware governance

**Source-confirmed starting point:** the Ministry of Justice trade/commercial sector catalogue lists the Federal Democratic Republic of Ethiopia Commercial Law Proclamation No. 1243/2021. The title and publication record are a primary legal starting point, not a conclusion that every private organization must use the same organogram.

Official source:
- [Ministry of Justice — Trade & Commercial Regulations (includes Commercial Code Proclamation No. 1243/2021)](https://justice.gov.et/en/sector/trade-commercial-regulations/)

**Engineering consequence:** the company setup flow must capture legal form, jurisdiction, registration evidence, effective constitutional documents and governance appointments. The organization model must support optional or legally constituted shareholders/general meeting, board, board committees and executive roles. It must not force a share-company board structure onto every private company, partnership, sole proprietorship, public enterprise or foreign branch.

**Interpretation required:** the exact board composition, director appointment/removal powers, quorum, reserved matters, management delegation and conflict/independence rules depend on legal form, the applicable Commercial Code clauses, constitutional documents and actual corporate resolutions. Ethiopian corporate counsel must confirm the legal entity's form and the source provisions before the chart is marked legally approved.

### 3.2 Organogram model and authority separation

The new organogram design is a configurable reference organization, not a compulsory list of posts. It covers, where applicable:
- shareholders/general meeting and board/committees;
- board chair/optional vice-chair/directors;
- CEO/Managing Director/Executive President and executive functions;
- head-office finance, procurement/supply chain, people, product/design/merchandising, manufacturing excellence/quality, distribution/commercial, digital/data, legal/compliance and independent Internal Audit;
- factory/plant, IE, PPC, production, quality, maintenance, material/stores, site HR/finance, HSE and site administration;
- DC/depot, branch/store and commercial channel operations.

A company may use different titles, combine functions or omit positions based on scale, legal form and business model. A title such as CEO, President, VP, CPO, Factory Manager, Business Owner, Process Owner or Procurement Owner does not itself create statutory authority, system access or transaction limits.

The design keeps these records distinct:
1. tenant/SaaS account;
2. legal entity and jurisdictional registrations;
3. business unit, org unit and cost centre;
4. physical site and typed operational node;
5. job position/slot, job family, grade and reporting line;
6. person and employee/employment contract;
7. effective-dated employee position appointment or acting appointment;
8. login identity and tenant membership;
9. tenant domain role and resource scope;
10. company-approved DoA policy and transaction-specific approval.

Tenant \`OWNER\` / \`ADMIN\` is a SaaS configuration permission, not the company's legal owner/director/CEO or a purchase-order/payroll approver. Business Owner/Process Owner/Procurement Owner/Platform Owner labels are accountability metadata unless expressly mapped to an approved company position and authority policy.

**Engineering consequence:** the org chart shows positions/relationships, not employee payroll details. A vacant position is valid. Human incumbent and acting appointment come from the People & Payroll bounded context in WP-14 through effective-dated appointments; WP-03 must not attach a job slot directly to a login user or membership.

### 3.3 Delegated Authority and segregation of duties

The DoA design is a company-specific, versioned policy model. It must resolve the effective position appointment and any approved delegate, then evaluate legal entity, site/unit/cost centre/channel, transaction type, amount/currency, budget, risk, current transaction version and policy date. A job title or tenant role is never enough.

The procure-to-pay reference flow is requester/budget owner → sourcing/commercial → technical evaluation → PO preparation → independent approval under signed DoA → independent receipt → independent quality inspection/acceptance → AP three-way match → treasury payment preparation → separate bank-signatory release. Maker/checker conflicts and self-approval fail closed. Material changes to amount, supplier, entity, budget or terms require a new decision under the applicable policy.

This is internal control design. It must not be described as statutory procurement law for every private manufacturer. Federal Public Procurement Proclamation No. 1333/2024 is listed In Force by the Ministry of Justice and its preamble expressly discusses expanding its scope to public enterprises. Apply it only where the entity is legally within scope (including relevant public enterprises); private-company internal sourcing/DoA controls are company policy and governance practice.

Official source:
- [Ministry of Justice — Federal Public Procurement and Property Administration Proclamation No. 1333/2024](https://justice.gov.et/en/law/the-federal-public-procurement-and-property-administration-proclamation/)

### 3.4 Legal entity, registration, licence, branch and retail boundaries

Official starting sources:
- [Commercial Registration and Business Licensing Proclamation No. 980/2016 — Gazette PDF hosted by MoTRI](https://www.motri.gov.et/sites/default/files/resource/proclamation-no-980_2016_0.pdf)
- [Commercial Registration and Business Licensing (Amendment) Proclamation No. 1150/2019 — Ministry of Justice](https://justice.gov.et/en/law/commercial-registration-and-business-licensing-amendment-proclamation/)

The supplied/repository legal register identifies the following Article 22 issues in Proclamation No. 980/2016:
- a valid licence holder need not obtain an additional licence for branches carrying on the same type of business activity, but commercial-register branch requirements/notifications remain a distinct issue;
- a manufacturing licensee may wholesale its products without an additional wholesale licence only at the same address of the manufacturing facility or registered address;
- manufacturing licensees are restricted from retailing their products except product types permitted by Council of Ministers regulation;
- importer wholesale/retail rules are separately stated.

**Engineering consequence:** model legal entity, licence class, activity, site/address, branch-register entry, issuing authority, evidence, status, validity and channel independently. Creating an operational \`BRANCH\` or \`STORE\` node does not prove commercial registration or retail permission. A manufacturing licence must not auto-enable retail sales. If the necessary activity/site/legal evidence is absent, expired, disputed or awaiting review, the relevant activity remains HOLD / REVIEW REQUIRED according to a counsel-approved applicability matrix.

**Interpretation required:** exact amendments, Council regulations, product exceptions, regional/city business licences, municipal permits, investment/industrial-park conditions and the facts of each establishment must be reviewed by local counsel/authority. The cited 980/2016 text alone is not a universal branch/retail permission engine.

## 4. Payroll statutory localization — employment income tax

### 4.1 Income Tax (Amendment) Proclamation No. 1395/2025

The official Federal Negarit Gazette copy is titled **Proclamation No. 1395/2025**, Federal Negarit Gazette 31st Year No. 64, Addis Ababa, 1 September 2025, starting at page 19136. The parallel Amharic identifier is 1395/2017 E.C.

Official source:
- [Ministry of Revenue — Federal Negarit Gazette, Proclamation No. 1395/2025 (Amharic identifier 1395/2017 E.C.)](https://www.mor.gov.et/DomesticProclamations/1786970364007-582645424-pdf-%C3%A1%C2%8A%C2%A0%C3%A1%C2%8B%C2%8B%C3%A1%C2%8C%C2%85-%C3%A1%C2%89%C2%81%C3%A1%C2%8C%C2%A5%C3%A1%C2%88%C2%AD-1395-2017.pdf)

The Gazette's amended Article 11 gives monthly employment-income brackets:

| Monthly taxable employment income (ETB) | Rate |
|---:|---:|
| 0–2,000 | 0% |
| 2,001–4,000 | 15% |
| 4,001–7,000 | 20% |
| 7,001–10,000 | 25% |
| 10,001–14,000 | 30% |
| Above 14,000 | 35% |

These are progressive monthly bands, not a flat marginal rate on the entire amount. The cumulative offsets sometimes used to express the same progressive arithmetic are derived mathematical equivalents, not separate statutory credits. The new ERP must implement the full legal base and calculation/rounding rules from the exact provisions and effective dates, not copy a compact spreadsheet shortcut.

### 4.2 Effective-date and catalogue conflict

The supplied ZIP's legal analysis records the amendment's transition clause as distinguishing:
- alternative minimum tax application to income tax collected from 8 July 2025;
- Schedule D and renumbered Article 97 specified withholding transactions from 7 August 2025;
- other provisions from 8 July 2025.

The repository legal-source register records a Ministry of Justice English catalogue metadata variant identifying 1395/2024 and a 30 December 2025 date, while the Gazette English title identifies 1395/2025 and the parallel Amharic title identifies 1395/2017 E.C. Gazette No. 64 is dated 1 September 2025. Treat these as a catalogue/source-metadata discrepancy, not as two separate income-tax amendments. Preserve the raw document and catalogue metadata, use the Gazette for citation/commencement, and have local counsel confirm the exact operative text and transition for the relevant period.

Official source register link:
- [Ministry of Justice — Taxation & Customs sector](https://justice.gov.et/en/sector/taxation-customs/)

### 4.3 PAYE base — unresolved legal interpretation

The supplied payroll evidence ZIP explicitly identifies the PAYE treatment of mandatory employee pension as unresolved. It preserves two alternative calculation methods, neither approved for production by the evidence pack:

| Candidate method | ETB 30,000 taxable gross | PAYE base | PAYE | Employee pension | Net pay before other deductions |
|---|---:|---:|---:|---:|---:|
| \`TAXABLE_GROSS\` | 30,000.00 | 30,000.00 | 8,450.00 | 2,100.00 | 19,450.00 |
| \`TAXABLE_GROSS_LESS_EMPLOYEE_PENSION\` | 30,000.00 | 27,900.00 | 7,715.00 | 2,100.00 | 20,185.00 |

The ETB 735 difference is material. The table is a synthetic/illustrative comparison, not an employee result or approved statutory rule. The cumulative band offsets used in the underlying CSV are arithmetic expressions of the progressive bands, not separate legal credits.

**Required decision before live payroll:** obtain a written opinion from a qualified Ethiopian tax practitioner/lawyer that:
- quotes the controlling Income Tax Proclamation and regulation clauses;
- considers the legal definition and computation of employment income;
- considers the Private Organization Employees' Pension Proclamation salary/contribution provisions and the interaction with employee deductions;
- selects one precise method and states effective dates, taxpayer/worker scope and rounding;
- identifies reviewer qualification, name, independence, opinion/reference number, review date and supporting evidence.

The system must support both test candidates, but must keep statutory activation false until the opinion and the full current legal-source bundle agree. Do not infer the answer from sample code, a historic product's test oracle or a label such as "pension-first".

### 4.4 Regulation No. 597/2026 — digital creators and individual service payees

The official Gazette copy is Federal Negarit Gazette 33rd Year No. 4, Addis Ababa, 28 October 2026 and is titled Regulation No. 597/2026. The Ministry of Justice catalogue record is dated 6 November 2026; that is a catalogue date, distinct from the Gazette issue/publication date. The regulation further amends Federal Income Tax Regulation No. 410/2017.

Official sources:
- [Ministry of Justice — Regulation No. 597/2026](https://justice.gov.et/en/law/%E1%8B%A8%E1%88%9A%E1%8A%92%E1%88%B5%E1%89%B5%E1%88%AE%E1%89%BD-%E1%88%9D%E1%8A%AD%E1%88%AD-%E1%89%A4%E1%89%B5-%E1%8B%A8%E1%8D%8C%E1%8B%B4%E1%88%AB%E1%88%8D-%E1%8B%A8%E1%8C%88%E1%89%A2-%E1%8C%8D/)
- [Federal Negarit Gazette PDF, 33rd Year No. 4, 28 October 2026](https://justice.gov.et/wp-content/uploads/2026/11/%E1%8C%98%E1%88%9B%E1%8C%AB%E1%88%AC-%E1%8C%A5%E1%88%AD-597-2019.pdf)

The repository legal register and supplied ZIP identify new/updated provisions covering specific digital-content creator and freelance/remote-service income, Schedule C/D distinctions, supporting records, withholding documentation and platform/payment-facilitator reporting. The source register identifies a resident Schedule D 15% rate, annual gross threshold over ETB 50,000, and quarterly declaration/payment within 30 days for the covered class. These are not general rates for every freelancer or every individual contractor. The precise taxpayer facts, residency, service/source, business classification, registration, withholding-agent scope, in-kind consideration and applicable transition must be assessed under the full Regulation and Proclamation.

The source register also notes an Article 22(3) creator business-classification directive was not located in that review. Ambiguous creator classification remains REVIEW REQUIRED until that directive or an authoritative legal determination is obtained.

**Engineering consequence:** do not make "influencer", "freelancer", "retainer", "individual supplier" or "creator" a blanket PAYE category. Keep employee payroll and nonemployee payee obligations in separate bounded contexts and ledgers. A nonemployee payee requires contract/deliverable/residency/TIN/licence/source classification, tax decision status and approval evidence before withholding/payment. The supplied reference sample's \`RETAINER_SERVICE\` exclusion is a useful negative test, not a product-specific enum to copy without mapping the new domain contract.

## 5. Private Organization Employees' Pension Proclamation No. 1268/2022

The supplied legal localization pack and current repository legal register identify Private Organization Employees' Pension Proclamation No. 1268/2022. The source material records:
- employee contribution: 7%;
- employer contribution: 11%;
- statutory coverage facts include an employee definition with at least 45 days of employment and managerial employees;
- Article 3 scope/exceptions and legacy/agreement rules require eligibility assessment;
- Article 2(7) defines pension salary by reference to the monthly salary for regular working-hour services, without deductions for income tax or other matters;
- Article 12 requires monthly payment within 30 days after the end of the month in which salary is paid, with consequences for late payment.

Official source starting points:
- [POESSA — Pension Administration](https://www.poessa.gov.et/)
- [Ministry of Justice — Labour & Employment sector-specific laws](https://justice.gov.et/en/sector/labor-employment-sector-specific-regulations/)

**Engineering consequence:**
- Employee 7% and employer 11% must be separate amounts and accounting treatments.
- Employee contribution affects employee net only under the applicable rule; employer 11% is employer cost, not a deduction from employee earnings.
- Coverage must be evaluated before a pension line is calculated. Capture employer type, worker/employer facts, nationality or agreement basis where relevant, service duration, prior provident-fund evidence, exception/consent basis, reviewer and effective dates.
- Keep taxable employment income and pensionable salary as separate component aggregates.
- Store month-of-payment, due-date calculation, pension register, amount due, payment reference, actual receipt and reconciled amount.
- Preserve late remittance interest/penalty only after current legal/directive interpretation has been confirmed.

The repository legal register lists POESSA Directives 1085/2025, 1086/2025 and 1087/2025 as relevant implementation sources. The supplied research pack says the 1086/1087 arrears/collection implementation and the current contribution salary base/coverage must be reviewed. Acquire the full official directive texts, hash them and obtain local pension/payroll review before configuring production parameters.

**Not activated:** no current evidence in this design pack authorizes a universal 7%/11% rule for every person paid by every organization or every contract category.

## 6. Labour Proclamation No. 1156/2019 — payroll inputs and worker safeguards

The Ministry of Justice lists Labour Proclamation No. 1156/2019 as In Force. The supplied legal requirements matrix identifies the following provisions as implementation-critical, subject to exact applicability, later directives, contract and collective agreement review.

Official source:
- [Ministry of Justice — Labour Proclamation No. 1156/2019](https://justice.gov.et/en/law/labour-proclamation/)

| Subject | Source provision cited in supplied pack | ERP design implication | Legal caution |
|---|---|---|---|
| Ordinary work limits | Arts. 61; supplied matrix states 8 hours/day and 48 hours/week | Effective-dated shift/attendance policy; distinguish roster from actual time | Verify averaging/arrangement and sector-specific exceptions |
| Overtime authorization and ceiling | Arts. 66–67; express instruction and permitted circumstances; supplied matrix gives max 4 hours/day and 12/week | Separate overtime request/instruction from recorded hours; validate limits; preserve evidence | Schedule alone does not establish authorization |
| Overtime categories/rates | Art. 68; supplied matrix identifies 1.5x daytime, 1.75x night, 2x weekly rest day, 2.5x public holiday overtime | Categorize actual overtime by time window/rest day/holiday with effective source | Public-holiday work under Art. 75 is separate; do not stack multipliers without written legal interpretation |
| Public-holiday work | Art. 75; supplied matrix identifies 2x hourly wage per covered hour | Separate holiday calendar, work category and earning code | Resolve overlap with Art. 68 before production formula |
| Annual leave | Arts. 76–77; supplied matrix gives 16 working days first year plus one day for every two additional years | Accrual policy/version, entitlement, usage, balance and termination settlement | More favourable contract/collective terms may apply |
| Sick leave | Arts. 85–86; supplied matrix describes up to 6 months under conditions and staged pay | Restricted medical evidence, effective leave and entitlement computation | Apply statutory prerequisites and medical evidence; privacy restricted |
| Maternity leave | Art. 88; supplied matrix states 30 consecutive prenatal +90 consecutive postnatal days paid | Separate leave period/benefit and return-to-work records | Supplied pack flags a wording/working-day ambiguity; counsel must resolve |
| Wage deductions | Art. 59; supplied matrix states authorized deductions and a general one-third cap absent written consent | Require deduction basis/document/consent where applicable and independent review | Confirm deduction class and any exceptions/collective terms |
| Wage register | Art. 60 | Preserve gross, calculation method, remuneration, deduction types/amounts, net and particulars | Protect private data; support employee access and auditor traceability |
| Severance | Arts. 39–41 | Conditional termination calculation based on proved statutory ground and pension status | Never calculate severance universally; eligibility and formula require case-level legal conditions |
| Final wages | Arts. 36–37; supplied matrix notes within seven working days subject to exception | Termination settlement with admitted/disputed amounts and deadlines | Confirm current clause application and facts |

This table is a localization design input, not a complete payroll-law interpretation. The exact Amharic text, subsequent binding amendments/directives, collective bargaining agreement, employment contract and special-sector/region facts must be reviewed. No automatic "pay all holiday overtime at 2.5x plus 2x holiday" calculation is permitted until the interaction is legally resolved.

## 7. Compensation, allowances, reimbursements and nonemployee payments

### 7.1 Classify components, not labels

Every earnings/deduction component must independently carry:
- component code and description;
- amount/currency and effective dates;
- source of the amount (contract, approved policy, attendance, accepted production output or sales transaction);
- taxable treatment and legal source;
- pensionable treatment and legal source;
- employer-cost treatment;
- approval and supporting evidence.

Do not assume housing, transport, meal, per diem, medical benefit, bonus, overtime, commission, piece rate, reimbursement or in-kind benefit is exempt or pensionable/non-pensionable just from its display name. The exact instrument, clause, scope, cap, evidence and period must exist. Otherwise use the legally determined default or hold the individual line for professional review; never manufacture an exemption.

### 7.2 Reimbursement is a separate process

Expense reimbursement should carry purpose, expense/travel order, date, location, receipt, approved claim and business linkage. It must not be silently converted into a salary allowance or treated as an exempt payroll line solely because the user selects "reimbursement".

### 7.3 Manufacturing incentives, store commissions and external compensation

The product goal and reference-study Notion page separate:
- manufacturing employee earnings/incentives;
- own-store sales commission/team bonus;
- wholesale/key-account sales commission;
- reseller rebate;
- supplier/subcontractor invoice;
- creator/campaign compensation;
- nonemployee individual service fees.

Manufacturing output-based incentives must use approved operations/SMV/SAM, recorded working time, skills, accepted output, quality, safety and transparent adjustments/appeal. Do not reward defect concealment, skipped inspection, unsafe speed or unapproved overtime. Reference repository piece-rate formulas, multipliers and automatic quality penalties are not accepted as Ethiopia policy. The Notion reference review explicitly marks piece-rate/quality-penalty formulas as reject-as-policy; use calculation architecture only, subject to lawful and fair policy review.

## 8. New ERP engineering implementation guardrails for payroll

WP-03 publishes shared entity/site/position/authority identifiers. WP-14 owns person, employee, employment contract, effective-dated position appointment, time/leave, skills, compensation and payroll. Do not create employee/appointment tables in WP-03 as a shortcut.

When WP-14 reaches implementation:
- Laravel is the authoritative calculation layer; Angular displays server-returned results and does not submit official PAYE/net values.
- Use exact decimal/minor-unit arithmetic; never binary floating point for money.
- Select effective-dated rules for the actual payroll period, not current wall-clock time.
- Freeze selected source records, instrument/article, official source bytes/hash, publication/effective dates, reviewer qualification/opinion and approved parameters into a deterministic calculation snapshot.
- Reject missing, stale, inconsistent, unapproved or non-covering rules; no default rate/rounding/base.
- Itemize taxable, exempt, pensionable, non-pensionable, reimbursement and deductions independently.
- Preserve a reason and approved legal evidence for each unusual exemption or non-standard pensionable salary.
- Split payroll calculation, submission, approval, posting, employee payment and statutory remittances into separate states/actors/evidence. A payment batch request or payroll status is not proof of a bank response or authority remittance.
- Keep historical posted payroll/journals/payments/remittances unchanged after law updates; corrections use an authorized adjustment/reversal run with source linkage.
- Keep individual supplier/creator/contractor payee review distinct from employee payroll.

The supplied ZIP's examples show a frozen rule/source snapshot, independent review and separate payment/remittance ledgers. These patterns may inform the new design; do not clone its tables or code.

## 9. Legal and source issues that remain open

| ID | Issue | Current evidence state | Required action before production use |
|---|---|---|---|
| ETH-PAY-001 | PAYE base: gross versus gross less employee pension | Interpretation unresolved in supplied ZIP and design analysis | Written qualified Ethiopian tax opinion with exact income-tax/pension clauses and chosen method |
| ETH-PAY-002 | Full amendment/transition chain for Proclamation 1395/2025 | Gazette copy confirms No. 1395/2025 / 1395/2017 E.C.; MoJ English catalogue metadata variant | Preserve metadata; local counsel verify operative text, commencement and historical periods |
| ETH-PAY-003 | Regulation 597/2026 creator/freelance/remote-service classification and withholding | Official Gazette No.4 dated 28 Oct 2026; MoJ catalogue date 6 Nov 2026 | Acquire full text and apply actual taxpayer facts; locate/reconcile Art.22(3) directive or obtain written ruling |
| ETH-PAY-004 | Pension coverage / salary base / exceptions / directives | Proclamation 1268/2022 and 7%/11% source evidence; 1085/1086/1087 directives require full review | POESSA/local pension opinion by employer and employee class; confirm variable pay and contribution due-date treatment |
| ETH-PAY-005 | Overtime and holiday interaction | Labour Arts. 68 and 75 separately cited | Written clause-level interpretation and payroll edge-case tests; no automatic stacking |
| ETH-PAY-006 | Allowance exemptions, reimbursements and deductions | Labels alone insufficient; source evidence per case | Current article/cap/scope plus contract/receipt facts and independent approval |
| ETH-PAY-007 | Commercial Code governance by company form | Proclamation 1243/2021 listed by MOJ; entity facts/constitution not supplied | Counsel confirms actual legal form, articles, Board/GM powers, appointments, reserved matters and delegation |
| ETH-PAY-008 | Branch/site activity licensing and retail | Proclamation 980/2016 Art.22 and amendment 1150/2019 listed; local product/activity facts unresolved | Entity/site/activity/address/branch registration/legal permit matrix; regional and city authority review |
| ETH-PAY-009 | Public procurement applicability | Proclamation 1333/2024 listed In Force and includes relevant public enterprises in scope | Determine whether a tenant/legal entity is within statutory scope; don't apply to ordinary private tenant by default |
| ETH-PAY-010 | Personal data processing and employment records | Proclamation 1321/2024 listed In Force | Counsel confirms legal basis, employee notice, sensitive/biometric handling, cross-border transfer, retention and data-subject processes |
| ETH-PAY-011 | Regional/city revenue/labour/licensing and industrial-zone/sector regimes | Not exhaustively covered by federal source register | Build location and activity-specific source set from relevant regional/city authorities |
| ETH-PAY-012 | Customs, FX, VAT, environment, chemical, textile standards and product compliance | Current source families recorded; several clause bundles remain incomplete | WP-02 source-register continuation, full directive retrieval and specialist/local authority confirmation |

The current repository legal-source register also records:
- Proclamation No. 1434/2026 Tax Administration catalog metadata conflicts requiring Gazette reconciliation.
- VAT invoice/registration/transitional applicability and the VAT refund directive need clause-level review.
- NBE FXD/01/2024, FXD/03/2025, FXD/04/2026, FXD/05/2026 and related modalities need amendment-precedence review.
- Customs Further Amendment No. 1425/2026 needs a current import/export rule pack.
- Environmental permits, wastewater/chemical/waste, facility HSE and textile/product standards are activity/product/site specific.
- Federal sources do not resolve all regional or city requirements.

## 10. Rule activation and acceptance holds

No live Ethiopia PAYE or pension rule is approved by this note. At WP-14 implementation:
- both \`ethiopia.employee_master_release_ready\` and \`ethiopia.statutory_activation_enabled\` remain false until the new ERP's own code and approval evidence justify a change;
- no legacy sample identifier, old ERP rule version, old code class or sample database schema may be used;
- no employee or payroll calculation becomes authoritative from front-end values;
- no actual wage payment, tax filing, pension remittance or bank release is executed by a design artifact;
- the final human/professional legal gate requires a qualified Ethiopian tax/payroll/pension review of the actual employer, worker class, legal clauses and effective date.

Tests can verify arithmetic under explicitly candidate/synthetic test fixtures, rejection paths and event/audit behavior; they cannot prove the legal method. Legal approval and exact-head code verification are separate acceptance dimensions.

## 11. Source register — official starting links

| Instrument / source | Topic | Official starting link | Status/qualification |
|---|---|---|---|
| Commercial Law Proclamation No. 1243/2021 | Company form, governance, board/management framework | [MoJ Trade & Commercial Regulations](https://justice.gov.et/en/sector/trade-commercial-regulations/) | Listed by MoJ; company-form and constitutional-document review required |
| Commercial Registration and Business Licensing Proclamation No. 980/2016 | Registration, branches, licensing, Article 22 business activity | [MoTRI Gazette PDF](https://www.motri.gov.et/sites/default/files/resource/proclamation-no-980_2016_0.pdf) | Official Gazette-hosted text; activity and location applicability review required |
| Amendment No. 1150/2019 | Commercial registration/licensing amendments | [MoJ law record](https://justice.gov.et/en/law/commercial-registration-and-business-licensing-amendment-proclamation/) | Listed In Force; reconcile with 980/2016 and subsequent provisions |
| Income Tax (Amendment) Proclamation No. 1395/2025 | Employment tax monthly rates and wider tax amendments | [MoR Gazette PDF](https://www.mor.gov.et/DomesticProclamations/1786970364007-582645424-pdf-%C3%A1%C2%8A%C2%A0%C3%A1%C2%8B%C2%8B%C3%A1%C2%8C%E1%8B%A8-%C3%A1%C2%89%C2%81%C3%A1%C2%8C%C2%A5-%C3%A1%C2%88%C2%AD-1395-2017.pdf) | Gazette no.64, 1 Sep 2025; preserve MoJ catalogue mismatch |
| Income Tax Regulation No. 597/2026 | Creator/freelance/remote service/MAT/withholding regulation amendments | [MoJ law record](https://justice.gov.et/en/law/%E1%8B%A8%E1%88%9A%E1%8A%92%E1%88%B5%E1%89%B5%E1%88%AE%E1%89%BD-%E1%88%9D%E1%8A%AD%E1%88%AD-%E1%89%A4%E1%89%B5-%E1%8B%A8%E1%8D%8C%E1%8B%B4%E1%88%AB%E1%88%8D-%E1%8B%A8%E1%8C%88%E1%89%A2-%E1%8C%8D/) | Gazette 28 Oct 2026; catalogue date 6 Nov 2026 |
| Private Organization Employees' Pension Proclamation No. 1268/2022 | Coverage, contributions, salary basis and remittance | [POESSA official portal](https://www.poessa.gov.et/) | Obtain exact Gazette text and current directives; specialist review required |
| Labour Proclamation No. 1156/2019 | Working hours, overtime, leave, deductions, termination | [MoJ law record](https://justice.gov.et/en/law/labour-proclamation/) | Listed In Force; exact clauses and later applicable instruments required |
| Public Procurement Proclamation No. 1333/2024 | Statutorily covered public procurement | [MoJ law record](https://justice.gov.et/en/law/the-federal-public-procurement-and-property-administration-proclamation/) | Listed In Force; scope includes relevant public enterprises; not universal private-company law |
| Personal Data Protection Proclamation No. 1321/2024 | Employee/customer/identity/sensitive data | [MoJ law record](https://justice.gov.et/en/law/personal-data-protection-proclamation/) | Listed In Force; detailed authority/guidance and implementation legal review required |
| Ministry of Justice sector source directory | Authority catalogues and legal status | [MoJ Taxation & Customs](https://justice.gov.et/en/sector/taxation-customs/) and [MoJ Employment](https://justice.gov.et/en/sector/labor-employment-sector-specific-regulations/) | Re-check exact source documents and issue dates before each rule release |

## 12. Final localization decision

The ERP must be Ethiopia-localizable by legal entity, site, activity, worker category and effective date—not by a single country-wide hard-coded switch. The new organogram provides the position and authority context; WP-14 provides the actual employee, employment and payroll records; WP-02 provides the controlling legal source register. Every statutory rule must be tied to the original official source, exact clause, effective period, applicability facts, source hash and qualified review decision.

**Current disposition: DESIGN / REVIEW REQUIRED.** No PAYE base method, exemption catalogue, pension coverage rule, company board structure, branch retail permission or public procurement profile is approved for blanket production activation by this note.
