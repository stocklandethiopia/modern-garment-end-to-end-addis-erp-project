# Ethiopia Legal Localization Note — Organogram, Delegated Authority and Payroll

**Document ID:** ETH-LEGAL-ORG-PAY-001  
**Research cut-off:** 27 November 2026  
**Related packages:** WP-02 legal-source verification; WP-03 organization and DoA; WP-13 finance/tax; WP-14 HR/payroll/compensation  
**Status:** Engineering legal-localization analysis and evidence register. NOT a legal opinion. NOT authority to activate a statutory payroll rule or company governance chart.  
**Controlling-source rule:** use the applicable Federal Negarit Gazette / official issuing-authority text; retain controlling Amharic and authorized English translation with review. Secondary summaries are not controlling.

## 1. Purpose and boundaries

This note consolidates the Ethiopian legal questions that affect the new enterprise organogram, delegated authority and later payroll localization. It separates:

1. legal requirements that can be located in official instruments;
2. engineering consequences for the ERP;
3. applicability questions that depend on a customer's legal form, actual facts, site, activity or employee class;
4. unresolved questions requiring a qualified Ethiopian lawyer, tax practitioner, accountant, pension specialist or competent authority.

It does not convert a reference organogram, sample database, historic payroll vector, vendor rule or general company title into an Ethiopian legal requirement. It does not approve a customer's Board composition, bank mandate, Delegation of Authority, payroll calculation method, statutory exemption, overtime amount or deduction.

## 2. Evidence hierarchy and source handling

### 2.1 Evidence hierarchy

1. Federal Negarit Gazette publication of the applicable proclamation, regulation or amendment.
2. Current official directive issued by the competent authority.
3. Current official implementation circular, notice, tax calendar, form, ruling or e-service guidance.
4. Regional/state, city or municipal law/guidance where the entity, employer, site or activity is locally administered.
5. Written review/opinion by the customer's qualified Ethiopian lawyer, tax practitioner, accountant, customs specialist or payroll/pension professional.
6. Industry standards and good practices are recorded separately and do not override law.

Ministry of Justice is a legal-publication and discovery index; the issuing authority administers current operational procedures and directives. A catalogue record is not a replacement for the complete Gazette and amendment chain.

### 2.2 Mandatory legal-source record

For every executable rule or governance applicability determination, the ERP legal register should retain:

- jurisdiction and competent authority;
- instrument type, number, exact title and Gazette number;
- publication date and exact commencement/effective-from date;
- article, sub-article, schedule and transitional clause;
- original Amharic source bytes and authorized English translation where used;
- SHA-256 for each original evidence object (never invent a digest);
- amendments, replacements, repeals and precedence relationships;
- entity legal form, employer status, activity, region/site, worker/payee class and fact pattern to which the provision applies;
- interpretation question, alternatives, assumptions and uncertainty;
- reviewer identity, professional qualification, independence, review date and signed opinion/reference;
- approved configuration and date/version, separate from source registration;
- evidence of test vectors and customer activation authorization.

The software must retain the source snapshot used for a historical transaction. A later law change must not silently rewrite posted payroll, prior approvals or historic legal applicability decisions.

## 3. Organogram and corporate-governance localization

### 3.1 Commercial Code Proclamation No. 1243/2021

The Commercial Code of Ethiopia is the principal starting source for company governance, but its requirements vary by legal form and applicable provision. The system must not force one share-company organogram onto every private limited company, sole proprietorship, partnership, public enterprise, branch or other legal form.

The Federal Ministry of Justice lists the Commercial Code of Ethiopia Proclamation No. 1243/2021 in its Trade & Commercial Regulations sector. A separate English text reference and WIPO Lex record help locate provisions, but the controlling Amharic Gazette text and the customer's articles/memorandum must be checked before applying a governance rule.

**Share-company example requiring form-specific review:** Article 296 of the English Commercial Code text describes a Board of Directors with 3–13 directors and states that two-thirds may not participate in day-to-day management. This is a share-company provision, not a global requirement for every tenant or every legal entity. The English copy is a reference; resolve exact wording against the Amharic Gazette and qualified company-law advice before configuring it as a hard legal rule.

### 3.2 ERP design consequence

- Support legal-form profiles with configurable governing bodies, shareholder/general meeting records, Board/committee records, executive positions, reserved matters and actual appointments.
- Do not automatically create a Board, Chair, Vice-Chair, committees, CEO or particular executive title for every company.
- Do not infer a Vice-Chair, Board Secretary, Managing Director, President, Chief Executive, Chief Executive Administrator or other acronym/title without customer evidence.
- Board Chair and CEO/MD should be separate by default; any combined appointment needs a verified legal/constitutional basis and recorded safeguards.
- Board committees are optional/configurable subject to company size, risk, law and constitutional documents.
- The ERP can provide a reference chart, but the customer's legal form, current registration, articles, shareholder resolutions and actual appointments determine the live governance chart.
- Record source, reviewer and effective dates for every legal-form-specific governance configuration.
- A job title, reporting edge or functional line does not create statutory status, bank signing power, procurement approval or any other transaction authority.

### 3.3 Delegation of Authority (DoA) is not a universal statutory matrix

The DoA framework in WP-03 is primarily a company-governance and internal-control mechanism. It must be aligned with the customer's constitution, Board/shareholder resolutions, employment/position appointments, signed customer authority policy and any mandatory law. It is not a universal Ethiopian legal table of PO approval limits.

The ERP must not invent or hard-code ETB thresholds. Authority rules are customer-specific and versioned by legal entity, site, department/cost centre, activity, transaction type, amount/currency, cumulative exposure, budget, risk, effective date and approval level.

A tenant OWNER/ADMIN is a SaaS administrator. It is not automatically the legal company's owner, director, CEO, finance approver or PO approver. Likewise Business Owner, Process Owner, Procurement Owner and Platform Owner remain accountability/stewardship metadata unless mapped by evidence to a real company position and separately authorized.

### 3.4 Public procurement law — scope gate

Federal Public Procurement and Property Administration Proclamation No. 1333/2024 is listed by the Ministry of Justice as in force. Its preamble expressly refers to expanding the scope to public enterprises. Determine applicability against the exact entity and statutory scope, including relevant public bodies/public enterprises.

Do not apply public tender law automatically to an ordinary privately owned garment manufacturer merely because it buys fabric, machinery or services. Private companies still need strong sourcing, conflict, delegated approval, receipt, invoice and payment controls as internal company policy/best practice; label them correctly as internal controls, not public procurement law.

**ERP rule:** a legal-entity profile and qualified applicability determination controls whether public-procurement legal workflows are enabled. No automatic inference from industry or purchasing activity.

### 3.5 Commercial registration, branches and activity licences

The Ethiopia Legal Regulatory Register identifies Business Licensing Proclamation No. 980/2016 and amendment No. 1150/2019 as source checkpoints. The existing project legal register quotes Article 22 of Proclamation 980/2016: same-activity branches, manufacturing-licensee wholesale at the manufacturing/registered address, and restrictions on manufacturer retail are addressed separately. Because licence activity and address facts matter, the exact official law, amendments, regulations and current licensing authority instructions must be captured and reviewed before a production rule is activated.

**ERP rule:**
- keep legal entity, commercial-register branch record, physical site, operational node, activity, licence evidence and sales channel separate;
- a branch node does not prove a legally registered branch;
- a manufacturing licence does not automatically enable retail, wholesale at another address, import, export or every other activity;
- a store/channel configuration must never grant legal authority to retail;
- register entity/site/activity/region/validity-specific evidence and require REVIEW REQUIRED to block the affected transaction;
- regional/city/municipal licensing, revenue, labor, fire/building, environmental and facility permits must be added for each real location.

### 3.6 Privacy and personnel information

Personal Data Protection Proclamation No. 1321/2024 is recorded in the project's legal register. The current implementing authority, detailed obligations and cross-border transfer/retention rules require current legal review. Regardless of unresolved clauses, apply least privilege, purpose limitation, data minimization and access audit.

The public organization chart must not expose employee payroll, tax identifiers, bank details, personal files, health information, grievances, disciplinary case evidence or other restricted HR data. A role authorized to see position titles does not automatically have access to person or payroll details.

## 4. Payroll localization — WP-14, not WP-03 runtime

### 4.1 Income Tax (Amendment) Proclamation No. 1395/2025

The Federal Negarit Gazette publication located in the Ministry of Revenue document library is Gazette No. 64, dated 1 September 2025, beginning at page 19136. The English Gazette title identifies Proclamation No. 1395/2025; its parallel Amharic title uses the Ethiopian-calendar identifier 1395/2017 E.C.

Article 11, as replaced by the amendment, sets these monthly employment-income rates:

| Monthly employment income (ETB) | Rate |
|---:|---:|
| 0–2,000 | 0% |
| 2,001–4,000 | 15% |
| 4,001–7,000 | 20% |
| 7,001–10,000 | 25% |
| 10,001–14,000 | 30% |
| Above 14,000 | 35% |

These are progressive marginal bands. The cumulative thresholds/offsets used in software are mathematical derivations, not separate statutory tax credits. The project's legal register records the amendment transition: most of its provisions apply to income derived from 8 July 2025, with specified Schedule D / renumbered Article 97 provisions having a separate effective date of 7 August 2025. Confirm the exact clause and employee/payee applicability against the complete Gazette before rule activation.

**Metadata conflict:** the official MoJ catalogue has been seen to label a record 1395/2024 with a catalogue date of 30 December 2025, while the Gazette publication and Ministry of Revenue file identify the amendment as 1395/2025 (Amharic 1395/2017 E.C.) and 1 September 2025. The Gazette is the source for citation and commencement; preserve the catalogue mismatch as an open metadata conflict rather than copying the catalogue label into the statutory rule ID.

The table does not alone determine the full taxable employment-income base, inclusion/exclusion of every benefit, exemptions, fringe benefits, deductions, rounding, withholding-agent duties or treatment of every worker class. Those require the complete Income Tax Proclamation No. 979/2016 as amended, regulations, directives and qualified tax interpretation.

### 4.2 PAYE base — two candidate methods; neither is approved here

The engineering regression retains two alternative methods:

1. TAXABLE_GROSS: salary plus components classified as taxable employment income.
2. TAXABLE_GROSS_LESS_EMPLOYEE_PENSION: taxable gross less the employee mandatory pension contribution.

Illustration required by the existing localization work, assuming ETB 30,000 taxable/pensionable salary, covered employee, no other allowances/deductions and the monthly progressive table above:

| Candidate | PAYE base | PAYE | Employee pension | Net pay | Employer pension |
|---|---:|---:|---:|---:|---:|
| Gross taxable | 30,000.00 | 8,450.00 | 2,100.00 | 19,450.00 | 3,300.00 |
| Gross taxable less employee pension | 27,900.00 | 7,715.00 | 2,100.00 | 20,185.00 | 3,300.00 |

The difference in PAYE/net is ETB 735. These are candidate regression values, not a ruling.

The project's payroll source analysis notes that Article 10 of Income Tax Proclamation 979/2016 is described in Ministry of Revenue Technical Notes as applying the monthly rate to total employment income received and not deducting expenses incurred to derive employment income. The Technical Notes are explanatory and expressly non-binding. Pension Proclamation 1268/2022 separately defines pension salary; that pension definition alone does not conclusively decide whether employee pension is deducted in determining the PAYE base.

**Mandatory hold:** a qualified Ethiopian tax practitioner or lawyer must issue a written, clause-specific opinion addressing employment income, statutory exemptions and the legal character of employee mandatory pension. The frozen rule payload must select exactly the method authorized by that opinion and retain reviewer qualification, identity, independence, review date, signed opinion reference, exact articles and official source evidence. No default or silent fallback is permitted. Until that is done, payroll activation stays false.

### 4.3 Private Organization Employees' Pension Proclamation No. 1268/2022

The official Federal Negarit Gazette copy is dated 18 March 2022, Year 28, No. 16. The source identifies 11% employer and 7% employee contributions under the private-organization pension scheme, subject to coverage and statutory salary rules.

The Gazette text distinguishes the worker definition from the scope-of-application clause. Article 2(1) defines a private-organization employee as a salaried person employed for not less than 45 days (definite or indefinite period or piece of work) and includes managerial employees. Article 3(1) states the Proclamation applies to employees of private organizations who are Ethiopian nationals, subject to the provision stated there for foreign nationals of Ethiopian origin and international agreements. Article 3(2) addresses employees already covered by provident funds or similarly named schemes; Article 3(3) permits certain religious/political-organization and informal-sector employees to be covered upon consent; Article 3(4) lists exclusions including domestic workers, employees of governmental international organizations and foreign diplomatic missions, and sole owners' managers/employees or sole owners. Exact facts, the complete Amharic Gazette and any applicable agreement/directive must be reviewed before coverage is determined.

Article 2(7) defines pension salary as monthly salary for services rendered during regular working hours without deductions for income tax or any other matter. The official text's contribution collection/remittance rules include monthly payment and a 30-day period from the last day of the salary-payment month under Article 12(1)–(2); other subsections address the authorized collection bodies and further compliance. Confirm the current administrative route and implementing directives before integrating remittance.

The project payroll source analysis identifies employer 11% and employee 7% contribution rates. The exact rate, applicable worker class, salary basis and any transitional or implementing rules must be retained in the frozen legal rule with the clause and source hash. The employee contribution affects employee net pay; employer contribution is an employer cost and must never be deducted from employee earnings.

Directives 1085/2025, 1086/2025 and 1087/2025 have been identified in the project source register as implementation, arrears-enforcement and contribution collection/transfer/supervision items. Their complete texts, exact clauses, current status and applicability must be downloaded, hashed and independently reviewed. The pension salary base is not automatically the same thing as the PAYE base.

### 4.4 Labour Proclamation No. 1156/2019 and worker/payee classification

The Ministry of Justice lists Labour Proclamation No. 1156/2019 as in force. The complete current law, subsequent amendments/directives, any applicable collective agreement and regional requirements must be reviewed for actual working time, overtime, night/public-holiday work, leave, wage records, deductions, termination/final pay and employee protections.

Do not implement sample-system overtime multiples, absence penalties, damage deductions, quality penalties or piece-rate deductions by copying them from a sample repository. Each compensation element requires a current lawful basis, contractual basis and approved policy. A software label alone does not authorize a deduction.

### 4.5 Regulation No. 597/2026 — separate creator/freelance classification

The Federal Negarit Gazette copy located is Gazette Year 33, No. 4, dated 28 October 2026. The Ministry of Justice catalogue record is dated 6 November 2026. Preserve both as distinct Gazette publication and catalogue-ingestion dates, not as competing effective dates. The regulation further amends Federal Income Tax Regulation No. 410/2017.

The official text introduces/updates definitions and procedures for digital content creators/platforms and differentiates business-income creators from resident Schedule D creators. The existing project analysis records a 15% Schedule D rate for qualifying resident creators not meeting business-income conditions, a gross-income threshold above ETB 50,000, quarterly declaration/payment and platform reporting. These rules depend on the actual resident/payee/business facts and the full regulation; no user-interface category can decide the classification.

**Nonemployee boundary:** creator compensation, freelance/retainer service, supplier invoices, subcontractor invoices, reseller rebates, wholesale commissions and employee wages are different legal/commercial relationships. A person paid for services is not automatically an employee. RETAINER_SERVICE must not be inserted into employee PAYE/pension calculation just because the recipient is an individual. Route uncertain cases to AP or individual-payee legal/tax review.

### 4.6 Compensation data design

For each earning or deduction component, store separately:
- amount, currency, unit and effective dates;
- tax treatment (taxable, exempt, pending review);
- pension treatment (pensionable, non-pensionable, pending review);
- official source instrument/article and full source hash;
- contract/benefit policy and supporting evidence;
- independent reviewer and approval reference;
- originating event (approved time, accepted output, shift, skill, leave, expense or benefit);
- correction/reversal linkage and historic snapshot.

Housing, transport, meal, per diem, bonus, overtime, commission, medical benefit and reimbursement labels do not automatically create tax exemption or pension exclusion. Reimbursement remains a separately evidenced expense workflow unless a specific applicable rule says otherwise.

For garment manufacturing, piece/output or team incentives should derive from approved operation standard/SAM/SMV and accepted traceable output, with skills, materials, machine readiness, learning curve, quality and safety safeguards. Never reward unsafe speed, skipped checks or defect concealment. Store commissions and wholesale/key-account sales commissions use separate triggers and must not be mixed with sewing-line earned minutes. Creator, reseller and supplier payments stay outside employee payroll unless a qualified facts-based legal assessment changes their classification.

## 5. Legal issues and configuration holds

| Hold ID | Subject | Evidence position | Required ERP behavior |
|---|---|---|---|
| ETH-ORG-01 | Customer legal form and Board structure | Not known per customer | Configurable template; no automatic Board/Chair/CEO |
| ETH-ORG-02 | Customer articles and reserved matters | Not supplied | Hold corporate governance activation until source/review recorded |
| ETH-ORG-03 | Public procurement applicability | Entity-specific | Enable statutory procurement pack only for entities within law |
| ETH-ORG-04 | Branch/manufacturing/retail licence scope | Site/activity-specific | Hold activity when licence/activity evidence is missing or expired |
| ETH-ORG-05 | Regional and municipal requirements | Location-specific | Capture regional/city authority evidence per site |
| ETH-PAY-01 | PAYE base and pension interaction | Two candidate methods; no written opinion | No statutory payroll activation |
| ETH-PAY-02 | Pension coverage and salary classification | Complete directive review outstanding | Hold unverified worker/component lines |
| ETH-PAY-03 | Directives 1085/1086/1087 | Catalogue records identified; full text/hash review outstanding | No final pension workflow activation |
| ETH-PAY-04 | Labour/overtime/deduction rules | Clause-level review outstanding | Do not copy sample multipliers/deductions |
| ETH-PAY-05 | Creator/freelance classification | Regulation 597/2026 identified; facts required | Separate Schedule D/business/AP review from employee payroll |
| ETH-PRIV-01 | Personal data, retention and cross-border transfer | Proclamation 1321/2024 identified; implementation review outstanding | Privacy-by-design; hold high-risk data handling until reviewed |

## 6. Engineering activation gate for WP-14

The current source documents state that both flags remain false:
- ethiopia.employee_master_release_ready
- ethiopia.statutory_activation_enabled

The payroll draft-line, submission and professional-decision methods are expected to fail closed while either gate is false. There is no environment-variable shortcut. No live payroll, posted transaction, payment, tax filing or pension remittance has been performed by the design work.

Before changing either flag, all of the following must be evidenced:
1. Official Income Tax Proclamation, regulations and amendment chain downloaded, preserved, hashed and reviewed.
2. Written Ethiopian tax opinion selects the PAYE base and rounding.
3. Official Pension Proclamation and relevant directives downloaded, preserved, hashed and reviewed.
4. POESSA/local pension specialist confirms worker coverage, salary basis and implementation.
5. Complete employee master, contracts, appointments and statutory classification resolver implemented and tested.
6. Component taxable/exempt/pensionable/non-pensionable/reimbursement catalog approved with exact legal evidence.
7. Labour, working time, overtime, leave, termination and deduction rules are verified before their respective use.
8. Server-side exact-decimal calculation, frozen source snapshot, tenant RLS, maker-checker, privacy, concurrency and history protections pass exact-head tests.
9. Accounting posting, payment-file, bank release and statutory remittance remain separate approvals/reconciliations.
10. WP-14 and WP-16 acceptance, qualified review and accountable customer activation are complete.

## 7. Current repository status and no-overclaim notes

- WP-03 target architecture is documented in docs/architecture/tenant-organization-identity.md.
- Organogram/DoA design exists as source in docs/organization/enterprise-organogram-and-delegated-authority.md on its own design branch/PR lineage.
- The integrated WP-03/WP-14 build plan exists in docs/work-packages/wp03-wp14-organogram-payroll-implementation-plan.md on a separate design branch.
- Payroll source localization and release gate exist in docs/localization/ethiopia-payroll-localization-2026-11-27.md and docs/localization/ethiopia-payroll-engineering-release-gate.md on the separate payroll work branch.
- PR #21 adds an unmerged WP-03 runtime candidate. Its current scope is organizational units, job positions, manager-position assignments and reporting relations. It does not implement human appointments or the complete DoA resolver.
- PR #21 exact-head CI attempts have failed before job steps/logs were available. The candidate backend and Angular tests are NOT RUN on that head.
- The candidate work is not merged into delivery or main and does not authorize live use.

## 8. Official source register and further verification

| Instrument / subject | Official source / status | Localization consequence |
|---|---|---|
| Commercial Code Proclamation No. 1243/2021 | MoJ Trade & Commercial Regulations sector; confirm exact Amharic article and customer legal form | Governance structure configurable by legal form/articles |
| Public Procurement Proclamation No. 1333/2024 | MoJ official law page; in force; public-enterprise scope highlighted | Entity-specific applicability; private internal control remains distinct |
| Business Licensing Proclamation No. 980/2016 and amendment No. 1150/2019 | Project legal register identifies Gazette and Article 22 controls; source/legal review per activity/site | Branch, manufacturing, wholesale and retail permissions are separate |
| Income Tax Amendment Proclamation No. 1395/2025 | MoR Gazette PDF; Gazette No.64, 1 Sep 2025 | Effective-dated progressive monthly bands; source metadata conflict preserved |
| Income Tax Proclamation No. 979/2016 and Regulation No. 410/2017 as amended | MoR/MoJ | PAYE base, income inclusion, exemptions and withholding chain |
| Regulation No. 597/2026 | MoJ Gazette PDF; Gazette No.4, 28 Oct 2026; MoJ catalogue dated 6 Nov 2026 | Creator/freelance/remote-service classification; do not mix with employee payroll |
| Private Organization Employees' Pension Proclamation No. 1268/2022 | HOPR Federal Gazette, Year 28 No.16, 18 Mar 2022 | Employer/employee rates and salary/coverage evidence |
| Directives 1085/2025, 1086/2025, 1087/2025 | MoJ catalogue entries found; complete text and hashes outstanding | No live pension workflow until reviewed |
| Labour Proclamation No. 1156/2019 | MoJ official law page lists In Force | Worker class, time, leave, overtime, deductions and termination require clause review |
| Personal Data Protection Proclamation No. 1321/2024 | MoJ official legal register | Personnel, payroll and identity privacy; current implementation details require review |

### Official links

- Ministry of Justice — Trade & Commercial Regulations: https://justice.gov.et/en/sector/trade-commercial-regulations/
- Commercial Code Proclamation No. 1243/2021, WIPO Lex record (secondary index, check against Amharic Gazette): https://www.wipo.int/wipolex/en/legislation/details/21658
- MoJ — Public Procurement Proclamation No. 1333/2024: https://justice.gov.et/en/law/the-federal-public-procurement-and-property-administration-proclamation/
- Ministry of Revenues — Proclamation No. 1395/2025 Gazette PDF: https://www.mor.gov.et/DomesticProclamations/1786970364007-582645424-pdf-%C3%A1%C2%8A%C2%A0%C3%A1%C2%8B%C2%8B%C3%A1%C2%8C%C2%85-%C3%A1%C2%89%C2%81%C3%A1%C2%8C%C2%A5-%C3%A1%C2%88%C2%AD-1395-2017.pdf
- MoJ — Regulation No. 597/2026 Gazette PDF: https://justice.gov.et/wp-content/uploads/2026/11/%E1%8C%98%E1%88%9B%E1%8C%AB%E1%88%AC-%E1%8C%A5%E1%88%AD-597-2019.pdf
- HOPR — Pension Proclamation No. 1268/2022 Gazette PDF: https://www.hopr.gov.et/admin/Files/2026/2/25/fc53f560-b228-4314-b372-da2b26ef4b53_Upploads_04012023090127_%E1%8B%A8%E1%8C%8D%E1%88%8D%20%E1%8B%B5%E1%88%AD%E1%8C%85%E1%89%B5%20%E1%88%A0%E1%88%AB%E1%89%B0%E1%8A%9E%E1%89%BD%20%E1%8C%A1%E1%88%A8%E1%89%B3%20%E1%8A%A0%E1%8B%8B%E1%8C%85%20%E1%89%81%E1%8C%A5%E1%12268.pdf
- MoJ — Labour Proclamation No. 1156/2019: https://justice.gov.et/en/law/labour-proclamation/
- MoJ — Personal Data Protection Proclamation No. 1321/2024: https://justice.gov.et/en/law/personal-data-protection-proclamation/
- Ministry of Trade and Regional Integration — Business Licensing Proclamation source in project legal register: https://www.motri.gov.et/sites/default/files/resource/proclamation-no-980_2016_0.pdf

**No source hash, professional opinion or customer legal decision is invented in this note.** The current controlling Amharic text and subsequent amendment chain must be captured and independently reviewed before activation.
