# Enterprise Organogram and Delegation of Authority Framework

**Project:** Modern Garment End-to-End Addis ERP  
**Architecture area:** WP-03 enterprise organization and identity; cross-domain control used by WP-04–WP-16  
**Decision record:** ORG-DOA-001 · 27 November 2026  
**Status:** Approved design direction for implementation within the existing product scope; company-specific positions, limits and statutory settings remain configurable  
**Applies to:** Multi-entity garment groups, manufacturers, sourcing businesses, distribution networks, wholesale operations and company-owned retail branches

## 1. Decision and terminology

The ERP will model the **company's organization and delegated authority**, not use a generic "Owner" role as the approver for business transactions.

Three different concerns must stay separate:

1. **Corporate governance:** shareholders/general meeting, Board and Board committees, where applicable under the entity's legal form and constitutional documents.
2. **Management organization:** executive roles, head-office functions, business units, factories, distribution centres, branches, departments, positions and reporting lines.
3. **System identity and access:** user accounts, tenant membership, platform administration, business positions, domain roles, resource scopes and permissions.

A tenant membership role such as `OWNER`, `ADMIN` or `MEMBER` is an identity/tenant administration concept. It does not automatically make a person the CEO, CFO, Procurement Director, Factory Manager or purchase-order approver. Likewise, the project collaboration term **Product Owner** is not an ERP job title.

Where requirements or workflow records use labels such as **Business Owner**, **Process Owner**, **Procurement Owner** or **Platform Owner**, they describe domain accountability or stewardship unless the customer maps them to an approved company position. They do not grant line-management authority, system permissions or purchase-order approval. The ERP must resolve actual positions and active incumbents, then apply the separate DoA policy.

**Approval rule:** a transaction is approved by the authorized incumbent or valid delegate for the applicable position/authority under the effective Delegation of Authority (DoA) policy. There is no universal person or job title that approves every purchase order.

## 2. Configurable company governance and executive structure

The ERP must support the following reference structure. It is a scalable template, not a requirement that every customer employ every post or adopt every title.

```text
SHAREHOLDERS / GENERAL MEETING (where the legal form provides for it)
  └── BOARD OF DIRECTORS (where required or constituted)
        ├── Board Chairperson
        ├── Vice-Chairperson (optional)
        ├── Board Directors / Non-Executive Directors
        ├── Audit & Risk Committee (where appropriate)
        ├── Governance, Nomination & Remuneration Committee (optional)
        └── Strategy, Investment & Sustainability Committee (optional)
              │
              └── CEO / Managing Director / Executive President
                    ├── COO / Chief Manufacturing & Operations Officer
                    ├── CFO / Finance Director
                    ├── CHRO / People & Culture Director
                    ├── CPO / Chief Procurement & Supply Chain Officer
                    ├── CCO / Chief Commercial Officer
                    ├── Chief Product / Design / Merchandising Officer
                    ├── CIO / CTO / Digital & Data Director
                    ├── General Counsel / Legal & Compliance Director
                    └── Chief Quality, HSE & Sustainability Officer
```

Internal Audit has independent functional access and reporting to the Board/Audit & Risk Committee. Administrative reporting may be configured for practical support, but management must not control the scope, findings or escalation of an audit.

### 2.1 Board and shareholder governance

Where applicable, the general meeting exercises shareholder powers under the legal form and constitutional documents. The Board provides direction and oversight, appoints/evaluates executive leadership as authorized, approves strategy and reserved matters, oversees risk and controls, and monitors executive performance.

The Board Chairperson leads the Board; the CEO/Managing Director leads the company. They are separate positions by default. The system may record a legally permitted combined appointment only with the relevant conflict/independence safeguards and constitutional evidence.

Suggested Board committees, to be enabled according to company size, risk and legal requirements:

- **Audit & Risk:** financial reporting oversight, internal/external audit, internal control, enterprise risk, fraud and compliance escalation.
- **Governance, Nomination & Remuneration:** Board composition, executive succession, conduct and compensation governance.
- **Strategy, Investment & Sustainability:** strategic plans, major capital commitments, industrial investment, environmental and social risk.

The Board normally sets policy and delegates operating authority; it should not become the routine approver for ordinary purchasing, store refunds or factory consumption. Board-reserved approvals are for matters expressly reserved by law, the company's constitution, shareholder resolutions or the Board-approved DoA.

### 2.2 Executive leadership

The CEO/Managing Director (which may be titled President or Executive President) is accountable for delivery of the approved strategy and enterprise performance. The role coordinates the executive team and exercises only the authority delegated by the Board or governing body. A company may appoint a Deputy CEO or Executive Vice Presidents for Manufacturing/Operations, Finance, People, Procurement/Supply Chain or Commercial. These are configurable posts beneath the CEO; the title President, Vice President or CEA does not itself create statutory status or transaction authority.

Suggested executive accountabilities:

| Position | Primary accountability |
|---|---|
| CEO / Managing Director / Executive President | Enterprise strategy execution, operating performance, executive coordination and delegated decisions |
| COO / Chief Manufacturing & Operations Officer | Factory operations, manufacturing performance, capacity, maintenance, industrial engineering and operational delivery |
| CFO / Finance Director | Financial control, accounting, treasury, budgeting, costing, tax governance, liquidity and financial reporting |
| CHRO / People & Culture Director | Workforce strategy, recruitment, employee relations, payroll governance, learning, competency and reward policy |
| CPO / Chief Procurement & Supply Chain Officer | Strategic sourcing, procurement policy, supplier governance, material planning, logistics and supply continuity |
| CCO / Chief Commercial Officer | Sales, wholesale, key accounts, retail operations, customer service, channel performance and revenue |
| Chief Product / Design / Merchandising Officer | Brand, range, merchandising, design studio, product development, PLM and product lifecycle |
| CIO / CTO / Digital & Data Director | Enterprise applications, infrastructure, cybersecurity, data governance and integration delivery |
| General Counsel / Legal & Compliance Director | Corporate legal affairs, contracts, licensing, regulatory coordination, claims and legal risk |
| Chief Quality, HSE & Sustainability Officer | Quality system, product compliance, worker safety, environment, chemicals, sustainability and independent quality escalation |

Titles such as CEA, Vice President, Deputy CEO, General Manager or HOO may be configured as local/company-specific titles and mapped to a defined position, job family, reporting line and authority profile. A title alone must never grant a permission or approval limit.

## 3. Head-office and central management organization

Head-office functions establish common policy, standards, consolidated planning, specialist expertise, enterprise reporting and assurance. They may have staff at each site, with a documented solid-line operational manager and dotted-line functional reporting to the central function.

### 3.1 Finance and corporate control — CFO

- Financial Controller / Group Controller
- Financial Planning & Analysis (FP&A), budgets and management reporting
- Chief Accountant, GL and financial close
- Cost Accounting and Manufacturing Costing
- Accounts Payable (AP), supplier invoice control and payment preparation
- Accounts Receivable (AR), credit control and collections
- Treasury, banking, cash management and authorized signatories
- Tax, VAT, withholding, customs and statutory reporting
- Fixed assets, capex control and inventory valuation
- Internal control and finance systems support

AP recording, payment preparation, bank release, journal posting and PO approval must be separated according to risk and practical staffing. Where a small site cannot fully separate posts, the DoA must impose independent compensating review.

### 3.2 Procurement, sourcing and supply chain — CPO

- Strategic Sourcing and Category Management
- Procurement Operations / Purchasing
- Supplier Qualification, Due Diligence and Performance
- RFQ/RFP, Commercial Evaluation and Contract Management
- Fabric, Trim, Accessories, Chemicals and Packaging Sourcing
- Subcontracting / Specialist Process Procurement
- Import, Customs and Foreign-Exchange Documentation
- Material Planning, Logistics, Transport and Supplier Delivery

Procurement owns sourcing integrity and commercial process control. It does not own independent goods receipt, quality acceptance, AP accounting or bank payment release.

### 3.3 People and administration — CHRO

- Workforce Planning and Organization Development
- Recruitment, Onboarding and Personnel Records
- HR Business Partners / Factory and Branch HR
- Employee Relations, Grievances and Disciplinary Process
- Time, Attendance, Leave and Shift Administration
- Payroll Operations and Benefits Administration
- Learning, Training, Skills Matrix and Competency Certification
- Performance Management and Reward Governance
- Staff Welfare, Administration and Facilities coordination

HSE may report to a dedicated central HSE/Quality executive, with a dotted-line operating relationship to HR and site management. Statutory and employee-sensitive data remains restricted by purpose and role.

### 3.4 Product, design and merchandising

- Brand Strategy, Market Research and Consumer Insight
- Merchandising, Range and Collection Planning
- Design Studio, Artwork and Product Concept
- Pattern, CAD, Grading and Technical Design
- Sample Room, Fit and Measurement
- Product Engineering, BOM, Tech Pack and Product Costing
- Product Lifecycle Management (PLM) and Revision Control

### 3.5 Manufacturing excellence and quality

- Production Operations and Factory Performance
- Industrial Engineering (IE), Methods, SAM/SMV and Line Balancing
- Production Planning and Material Requirements Planning
- Manufacturing Engineering and Maintenance Standards
- Quality Assurance, Quality Control and Buyer Quality
- Department SOP / Work Instruction Governance
- Continuous Improvement, Lean / Kaizen and Operational Excellence
- HSE, Chemical Compliance, Environment and Sustainability

Quality must have stop/hold and escalation authority independent of production output pressure. The site Quality Manager has a functional reporting line to the central Quality Head/Director as well as a defined working relationship with the Factory General Manager.

### 3.6 Distribution and commercial channels

- Central and Regional Distribution Centre Management
- Warehouse, Inventory Control and Logistics
- Retail Operations and Branch Network
- Wholesale, Key Accounts and Reseller Management
- E-commerce, Digital Sales and Marketplace Operations
- Customer Service, Returns, Claims and After-Sales
- Pricing, Promotions, Credit and Channel Policy

### 3.7 Brand, communications and corporate services

Marketing and communications are separate from sales execution, while coordinating through the CCO/CEO structure as configured:

- Brand Management and Campaigns
- Social Media and Digital Content
- Public Relations, Corporate Communications and Media
- Photography, Creative Production and Influencer/Campaign Coordination
- Corporate Affairs, Events and Community Engagement

IT, cybersecurity, data/analytics, legal, company secretariat, risk, compliance and Internal Audit are distinct functions even when a smaller company combines some staff responsibilities.

## 4. Manufacturing unit / factory organogram

Each factory or manufacturing unit has a named site leader accountable for safety, delivery, cost, quality and people at that site. The title may be Factory General Manager, Plant Manager or Head of Operations (HOO).

```text
COO / GROUP OPERATIONS
  └── FACTORY GENERAL MANAGER / PLANT MANAGER / HOO
        ├── Production Manager
        │     ├── Cutting / Spreading / Marker / Bundling
        │     ├── Print / Embroidery (when applicable)
        │     ├── Sewing / Assembly
        │     ├── Washing / Dyeing / Special Process (when applicable)
        │     ├── Finishing / Pressing
        │     └── Packing / Final Preparation
        ├── Factory Planning & PPC Manager
        ├── Industrial Engineering Manager
        ├── Product / Process Engineering Manager
        ├── Factory Quality Manager (functional line to Head of Quality)
        ├── Maintenance & Utilities Manager
        ├── Warehouse & Material Control Manager
        ├── Factory HR / Training Manager (functional line to CHRO)
        ├── Site Finance / Cost Controller (functional line to CFO)
        ├── HSE / Environmental Officer (functional line to HSE)
        ├── Site Procurement / Purchasing (within CPO policy)
        └── Administration, Security and Facilities
```

Under Production Manager, the system supports the actual specialist route and the relevant supervisors/team leaders for each department. The approved style/route determines which optional processes exist; it must not assume every product passes through every department.

**Factory control rules:** production cannot approve its own quality release; the receiver cannot approve the supplier invoice for payment; a buyer cannot create and finally approve their own PO; inventory adjustment and physical count approval require independent review; the Factory Manager cannot override central legal, finance, quality or safety controls outside the delegated authority.

## 5. Distribution centre, depot and logistics structure

A central or regional DC may report to the Head of Supply Chain / CPO or Distribution Director:

- DC / Depot Manager
- Inbound Scheduling and Receiving
- Independent Receiving Verification / Quantity Control
- Incoming Quality Inspection and Quarantine
- Put-away, Bin and Location Control
- Inventory Control and Cycle Count
- Replenishment and Inter-site Transfers
- Picking, Packing, Dispatch and Proof of Delivery
- Transport / Fleet / 3PL Coordination
- Returns, Claims and Reverse Logistics

Stock custody, inventory ownership, physical location and legal entity are separate attributes. A branch is not automatically a warehouse; stock-holding capability must be explicitly enabled and authorized.

## 6. Branch / company-owned shop organogram

```text
CCO / RETAIL DIRECTOR
  └── REGIONAL / AREA MANAGER
        └── BRANCH / STORE MANAGER
              ├── Assistant Store Manager / Shift Supervisor
              ├── Sales Team Leader
              │     └── Sales Associates / Retail Sales Assistants
              ├── Cashier / POS Operators
              ├── Customer Service / Returns Desk
              ├── Receiving / Stock Controller / Storekeeper
              ├── Visual Merchandising
              └── Loss Prevention / Security
```

Where staffing is lean, one person may cover multiple non-conflicting duties, but the ERP must preserve approval separation for cash, refunds, discounts, stock adjustments, voids, physical counts and cash deposits. The Store Manager has local operating authority within the configured store, product/channel and value limits; they do not receive unrestricted purchasing, finance, HR or tenant-administration powers.

## 7. Organization, positions and incumbent data model

The hierarchy must be data-driven and effective-dated. Do not encode the chart as a static text tree or attach it directly to login users.

| Record | Purpose and key design |
|---|---|
| Legal entity | Registered company/body and statutory accounting boundary |
| Business unit | Management/profit/cost responsibility grouping; not itself a legal entity |
| Organizational unit | Department, function, factory division, regional office, DC or branch team; parent-child hierarchy with no cycles |
| Business site / operational node | Physical or network location and its capabilities |
| Job position | An approved post in an org unit with position code, title, grade, job family, cost centre, legal entity/site context and status |
| Position reporting relation | Primary manager-position link plus optional time-bound functional/matrix reporting links |
| Person / employee | Human workforce record; separate from system identity |
| Position assignment | Effective-dated incumbent assignment; supports vacant positions, acting appointment, secondment, split allocation and succession |
| User identity | Authentication account, linked to a person where appropriate; never the source of the organization chart |
| Domain role | Capability role such as PROCUREMENT_MANAGER or QUALITY_INSPECTOR; separate from job title |
| Resource scope | Legal entity, site, department, warehouse, brand, channel or record scope; enforced by the API and PostgreSQL RLS where appropriate |

### 7.1 Core modeling constraints

- Every org unit belongs to a tenant and has a typed parent/child relationship; legal entity and site references are explicit where applicable.
- Replace the draft `org_units.owner_user_id` idea with a **manager position** relationship, such as `manager_position_id`. The manager is the incumbent of the designated position during the transaction's effective date, not a permanently hard-coded user.
- Add `job_positions`, `position_reporting_relations` and `position_assignments` as separate entities. Support vacancies and valid-time history.
- One position may have primary and acting incumbents only under explicit assignment type and non-overlapping effective-period rules.
- A person may hold more than one assignment, but authority is calculated from the specific active assignment and scope relevant to the transaction.
- Functional/matrix reporting does not automatically grant the functional manager line-management, payroll or disciplinary authority.
- Employee, contractor, supplier, reseller and creator records must keep their separate legal/commercial classifications.
- A user's system roles are provisioned from approved employment/position and domain-role administration workflows, but login access can be suspended without erasing employment history.
- A job title, department membership or reporting line alone is not sufficient to grant financial approval. A separate active DoA delegation is required.

## 8. Delegation of Authority (DoA) and approval engine

The ERP will maintain a versioned DoA policy for each tenant and, where needed, legal entity, business unit, activity, site, department and channel. The approved policy is the source of transaction approval routes. No amount threshold or currency limit is assumed by the product vendor.

Each authority rule should capture:

- Policy code, version, effective-from/effective-to and approval status.
- Legal entity and operating scope; department, site, cost centre, warehouse or channel where relevant.
- Business activity and transaction type (for example purchase requisition, purchase order, contract, capex, supplier award, credit note, discount, refund, stock adjustment, journal, payroll run).
- Budget state and budget holder; amount basis, currency, cumulative-period limit and aggregation rule.
- Risk/exception flags: related party, sole source, emergency, foreign currency/import, contract duration, restricted material, capex, budget overrun or conflict of interest.
- Required approver position/authority level, sequence or parallel group, quorum where relevant, minimum reason/evidence and separation-of-duties constraints.
- Delegated alternate, acting appointment, absence period, scope, expiry and revocation.
- Policy approver and immutable policy change history.

### 8.1 Illustrative authority levels

These are responsibility bands. Their transaction limits are set by each company's approved DoA and must not be hard-coded as universal values.

| Level | Typical accountable roles | Example decision scope |
|---|---|---|
| L1 — Operational staff | Requester, buyer, sales associate, cashier, receiver, storekeeper | Create or record assigned work; no self-approval |
| L2 — Supervisor | Line/shift supervisor, team leader, senior officer | Verify operational need, quantities, evidence and team activity within delegated scope |
| L3 — Department manager | Production, IE, HR, Finance, Sales, Store or Procurement Manager | Approve routine departmental activity within budget and documented limits |
| L4 — Site / business-unit leader | Factory GM, DC Manager, Regional Manager, Store Manager | Site-level operations and approved budget commitments within assigned scope |
| L5 — Functional / corporate director | CFO, CHRO, CPO, COO, CCO, Quality/HSE Director | Cross-site functional policy, significant functional expenditure and escalation |
| L6 — Chief executive | CEO / Managing Director / Executive President | Executive decisions within Board-delegated authority; material operating exceptions |
| L7 — Board / General Meeting | Board, required committee or shareholders' meeting | Reserved corporate matters only, according to law, constitution and Board-approved schedule |

### 8.2 Purchase requisition to supplier payment — required control flow

1. **Need and requisition:** the requesting department records purpose, specification, quantity, required date, destination site, budget/cost centre and any technical requirement. The requester's manager/budget holder validates business need and budget ownership.
2. **Sourcing:** Procurement checks approved supplier status, sourcing method, quotations/tender requirements, price reasonableness, supplier conflicts, contract terms and category-specific risk. Technical evaluation is performed by the competent user function (for example IE, Product Engineering, Quality, HSE or IT) and recorded separately.
3. **Commercial recommendation and award:** Procurement prepares a recommendation with comparison evidence and declared exceptions. An authorized award decision is recorded independently of the requester and supplier relationship lead.
4. **PO preparation:** a purchasing officer converts the approved requisition/award into the PO, verifies quantities, specification, price, currency, tax/withholding treatment, delivery terms, budget reservation and supplier identity. The PO is still a draft until all applicable approval steps complete.
5. **PO authorization:** the workflow resolves the current effective DoA policy and active position holders. It may require sequential or parallel approvals, such as:
   - budget holder / department head;
   - Procurement Manager or delegated procurement authority;
   - Finance Controller / CFO delegate for budget, cash, accounting and financial-risk checks;
   - COO / functional director for operational or cross-site commitments;
   - CEO for commitments above executive limits but within Board delegation;
   - Board / committee only where the matter is expressly reserved.
   
   The applicable route is selected by amount, currency, entity, budget, category, site, risk and policy. It is not assumed that CFO or CEO approves every PO. When a required approver is absent, only a valid, unexpired, scope-matched delegate may act.
6. **PO issue and change control:** only after required approval does the ERP issue/send the PO. Material price, quantity, currency, supplier, specification or delivery changes trigger re-evaluation and approval using the revised amount and policy; an approved PO cannot be silently edited.
7. **Receipt and inspection:** a warehouse receiver confirms actual quantity and condition against the PO/ASN. Quality independently inspects applicable materials. Short, excess, damaged, substituted, unapproved or quarantined materials follow a controlled exception/hold workflow; the requester/buyer cannot falsely confirm receipt.
8. **Invoice verification:** AP independently matches the PO, goods/service receipt and supplier invoice (three-way match; two-way matching only for explicitly permitted service/contract cases). Duplicate invoice, price/quantity/tax variance or missing receipt creates a blocked exception and a separate approval trail.
9. **Payment:** Treasury prepares the payment proposal. Release follows the legal entity's current bank mandate, authorized signatory combination and payment limits. The PO requester, buyer, receiver or AP recorder must not unilaterally release the same payment.
10. **Accounting and close:** finance posts linked source records and reconciles commitments, receipts, AP, payment, inventory and GL. Reversals/corrections retain the original transaction and audit history.

**Non-negotiable incompatible duties:** requester ≠ final approver for own request; buyer/PO preparer ≠ final approver of own PO; receiver ≠ sole quality acceptor; AP invoice recorder ≠ sole payment releaser; payment initiator ≠ sole bank release authority; physical counter ≠ sole stock adjustment approver. Where headcount forces combined roles, use an independent second review, system hold and recorded compensating control.

### 8.3 Other approval families

The same engine must support, with their own position matrix and control rules:

- Capex, asset disposal, contracts, borrowing, guarantees and related-party transactions.
- Supplier onboarding, supplier bank-detail change and supplier suspension.
- Product/style/tech-pack/fit/BOM release and approved substitutions.
- Production order release, material issue variance, scrap, rework and route exceptions.
- Quality hold/release, CAPA closure, restricted-substance and HSE exceptions.
- Inter-site transfer, stock adjustment, write-off, physical count variance and finished-goods release.
- Sales pricing, discount, promotion, customer credit, wholesale terms, refunds and write-offs.
- Payroll calculation review, statutory rules, employee changes, benefits and payment-file release.
- Journal posting, payment runs, tax returns, period close and bank reconciliation.
- User role grants, delegated access, privileged support and approval-policy changes.

Consequential HR decisions, statutory activations and financial actions have their own professional/human safeguards as defined by the existing security and legal standards.

## 8.4 Baseline runtime audit — existing PO authorization is not yet full DoA

A source inspection of the canonical `delivery/autonomous-wp-01-16` baseline on 27 November 2026 confirms that purchase-order authorization is a **partial control foundation**, not the full position-based DoA design specified above.

Observed implementation:

- `backend/app/Services/Authorization/DomainCapabilityAuthorizer.php` maps `sourcing.approve-purchase-order` to the tenant domain-role codes `PROCUREMENT_APPROVER` and `PROCUREMENT_MANAGER`. The present gate confirms an active tenant membership and active role grant; its current release is tenant-wide and explicitly denies members in `SCOPED` access mode.
- `backend/app/Http/Controllers/Sourcing/SourcingController.php` invokes that Gate for the decision endpoint.
- `backend/app/Services/Sourcing/SourcingService.php` serializes the PO decision with a row lock, blocks the `created_by` and `submitted_by` actors from approving their own PO, requires a reason and records the final `approved_by`/time. This is useful baseline maker-checker protection.
- The current decision writes the PO's single status/approver fields. The inspected migrations do not yet provide the versioned DoA policy/rule, active position-assignment resolver, approval-step/decision chain, authority amount/cumulative limit, approved budget, department/site/cost-centre scope or acting-delegation evidence described in this document.

**Therefore:** a user with a granted `PROCUREMENT_APPROVER` or `PROCUREMENT_MANAGER` role may satisfy the current capability gate, but that alone is not proof that the person is the proper company approver for that exact entity, site, department, budget, transaction amount or risk. The current code does not make a generic `OWNER` the PO approver; the `OWNER`-only ability in the authorizer is confined to tenant domain-role approval. Neither that tenant capability nor the present Procurement role matrix may be represented as the final corporate DoA.

Required corrective delivery:
1. WP-03: deliver company org units, job positions, reporting relations, person/position assignments, functional-line scopes and effective-dated identity-to-position mapping; retain tenant OWNER strictly as SaaS tenancy administration.
2. WP-03: deliver the effective-dated DoA policy/rule and delegation foundation with policy-version approval, legal-entity/site/department/cost-centre scope and immutable decision evidence.
3. WP-07: integrate PR, sourcing award and PO approval to server-resolved position-based DoA, total/currency basis, budget, cumulative limits, risk exceptions, material PO revision reapproval and controlled supplier award.
4. WP-13: integrate financial budget checks, AP three-way match, accounting and treasury/bank-mandate separation.
5. WP-16: run end-to-end positive and negative tests for wrong position/scope, limit exceedance, budget, conflict, delegation expiry, concurrency/replay, policy revision and prohibited incompatible duties.

Until those packages pass their applicable tests, describe PO approval as **tenant-domain-role gated with creator/submitter self-approval protection — PARTIAL**, not as fully delegated corporate approval. Do not route live commitments using this partial gate as a substitute for the customer's approved DoA.

## 9. Board-reserved matters and executive delegation

The Board/General Meeting reserved-matters schedule is configurable and should cover, where applicable:

- Strategy, annual plan and consolidated budget approval.
- Material capital projects, new factory/site establishment and closure.
- Borrowing, guarantees, security, material litigation and exceptional commitments.
- Acquisition, merger, disposal of material assets/businesses and corporate restructuring.
- Related-party transactions and conflicts that require independent approval.
- Appointment/removal and succession of CEO or other roles reserved by law/constitution.
- Dividend, share-capital, constitutional and other statutory shareholder matters.
- Risk appetite, audit charter, financial statements and other governance matters reserved by law or the Board.

An approved annual budget can delegate execution of ordinary purchase orders to management within defined limits. The ERP must not route each ordinary PO to the Board merely because the Board approved the budget.

## 10. Ethiopia localization and legal configuration

- The enterprise chart must be configurable by legal form, constitutional documents, shareholder resolutions, registrations, licenses and actual management practice. Do not force a Board, Chairperson, Vice-Chairperson, committees or a specific President/CEO title onto every business.
- Ethiopia Commercial Code Proclamation No. 1243/2021 contains legal-form-specific company governance provisions. For example, Article 296 addresses the Board of Directors of a share company, including a board of 3–13 directors and a restriction that two-thirds of its directors may not take part in day-to-day management; implementation must be confirmed against the applicable entity form and the authoritative Amharic Federal Negarit Gazette text where wording differs. Store legal evidence and qualified review rather than treating this template as legal advice. See the [WIPO Lex record](https://www.wipo.int/wipolex/en/legislation/details/21658) and [English text reference](https://ethiodata.et/wp-content/uploads/2023/01/Commercial-Code-of-Ethiopia-English-Proclamation-No.-1243_2021.pdf).
- The Federal Public Procurement and Property Administration Proclamation No. 1333/2024 must be activated only for a customer/entity within its legal scope (including relevant federal public bodies and public enterprises as applicable). Do not label the public-procurement law as mandatory for an ordinary private manufacturer solely because it buys fabric. Private companies still use the ERP's internal procurement controls as company policy/best practice. See the [Ministry of Justice source](https://justice.gov.et/en/law/the-federal-public-procurement-and-property-administration-proclamation/).
- Ethiopian company thresholds, tax, VAT, withholding, pension, labour, customs, FX, licensing, environmental and data-protection rules must come from the existing versioned Ethiopia legal register and qualified professional review. No statutory threshold or formula is to be invented in the DoA.
- Monetary authority settings are stored per legal entity and currency. Exchange rates, budget conversions, transaction-date treatment and rounding use the approved finance/localization policy.
- Regional and municipal business licensing, factory/store registration, and activity-specific permissions are recorded against the legal entity/site and verified before enabling affected operations.
- All statutory approvals preserve the exact law/directive version, clause/source, reviewer, approver, effective date and transaction evidence.

## 11. Authorization, security and audit requirements

- Enforce authorization on the Laravel API and PostgreSQL, never only through Angular visibility or route guards.
- Combine RBAC (capability/role) with scoped policy checks (tenant, legal entity, site, department, warehouse, brand, channel, cost centre, record, transaction and effective date).
- The authenticated identity must be linked to an active person/position assignment and valid domain role where the action requires a job position. A tenant administrator role does not confer business transaction authority.
- Approval uses a server-resolved approver from a valid position assignment and current DoA policy. Client-submitted approver IDs, titles or threshold claims are untrusted.
- Maker-checker applies to restricted transactions; the same human cannot bypass separation by holding multiple accounts or temporary roles.
- Approval records snapshot the exact transaction version/hash, policy version, authority rule, scope, amount/currency, requested-by, each decision, reason, timestamp, delegation proof and final disposition.
- Policy, position, assignment and delegation changes are effective-dated, versioned and audited. Historical decisions remain reproducible against the rules and incumbents that applied at the time.
- Revocation, transfer, leave, termination or position vacancy immediately invalidates authority according to effective date. Reassignment never silently inherits a former incumbent's approvals.
- Emergency overrides are limited to defined emergency policies, require reason and evidence, expire automatically and receive independent post-event review.
- Denied, expired, out-of-scope, over-limit, over-budget or incomplete routes fail closed. Do not fall back to a generic administrator or default approver.
- Apply privacy classification and purpose limitation to personnel data; org chart visibility must not expose payroll, personal records or restricted HR notes.

## 12. Target technical entities and relationships

Detailed column types and migrations are to be implemented in WP-03 and the respective domain packages. Target entities include:

- `organizational_units` (or consistent `org_units` naming): tenant, legal entity/business unit, parent org unit, unit type, code, name, site/node context, cost centre, status, effective dates and optional manager-position reference.
- `job_positions`: tenant, legal entity, business unit, org unit, position code, localized display title, canonical title/job family, grade, cost centre, employment type, position status and effective dates.
- `position_reporting_relations`: tenant, subordinate position, manager position, relation type (PRIMARY / FUNCTIONAL / PROJECT), valid period; primary hierarchy must be acyclic.
- `position_assignments`: tenant, position, person/employee, assignment type (PRIMARY / ACTING / SECONDMENT / TEMPORARY), valid period, FTE allocation, status, appointment authority and evidence.
- `delegated_authority_policies`: tenant, legal entity/scope, policy code/version, effective period, status, policy approver and source.
- `delegated_authority_rules`: policy, transaction/activity, amount basis, currency, budget and risk conditions, required authority/position, sequence/parallel group, limits and segregation rules.
- `approval_requests`: transaction type/id/version, legal entity, site/org/cost centre scope, amount/currency, budget reference, risk flags, requestor, resolved policy version and state.
- `approval_steps` / `approval_decisions`: required authority, resolved approver and assignment, delegated authority proof, decision, reason, timestamps and immutable evidence.
- `authority_delegations`: delegator/delegate position or incumbent, delegated action/scope/limit, start/end, reason, approval and revocation audit.
- `organization_change_events`: org-unit, position, assignment and reporting-line change history.
- `people` / `employment_records`: authoritative HR identities and employment relationship; separate from user login and supplier/customer contact.

All tables are tenant-scoped, use composite foreign keys where applicable, effective-dated uniqueness/exclusion constraints, RLS and explicit least-privilege grants. Approval and decision records are append-only or corrected by compensating events, not destructive edits.

## 13. Delivery sequence and acceptance checkpoints

This framework supports the existing WP-00–WP-16 order; it does not replace or reorder it.

| Package | Required implementation impact |
|---|---|
| WP-01 | Trace every organization/authority requirement to its role, workflow, data, rule, API, test and evidence |
| WP-02 | Verify legal-form, company-law, procurement-law and Ethiopia statutory sources; maintain qualified-review gates |
| WP-03 | Implement tenant-safe org units, positions, person/identity links, assignments, reporting, scope-aware roles and DoA foundations |
| WP-04–WP-06 | Use position/authority rules for merchandising, product, technical and pre-production gates |
| WP-07 | Implement requisition, sourcing, supplier award, PO approval and change control under DoA |
| WP-08–WP-12 | Apply scoped operational roles and separate authority for routes, MES, quality, stores, DC and channel decisions |
| WP-13–WP-14 | Apply finance, treasury, HR, payroll, statutory and payment-approval separation |
| WP-15 | Govern integrations and AI recommendations using the same human authority and audit boundaries |
| WP-16 | Test positive/negative, expired delegation, conflict, scope, limit, budget, concurrency, replay, RLS, audit and recovery scenarios end to end |

### Minimum end-to-end approval tests

1. A valid low-risk, in-budget PO follows the configured approval chain and is issued only after all required decisions.
2. A requester cannot approve their own PO, even with a second session or another role assignment.
3. A buyer cannot approve the PO they prepared; a receiver cannot approve payment; AP entry does not release cash.
4. A role title without an active position assignment and matching DoA rule receives no authority.
5. A current employee at the wrong site/legal entity, or outside assigned category/department/cost centre, is denied.
6. A transaction exceeding its individual or cumulative authority limit routes to the next required level.
7. Over-budget, capex, related-party, sole-source, emergency, foreign-currency or contract exceptions add the required independent checks.
8. Expired acting assignments and delegates cannot approve; pending steps are safely re-resolved or returned for reapproval under policy.
9. A material PO change invalidates prior approval for the changed version and re-runs the route.
10. Duplicate submission, concurrent approval, replayed API call or changed transaction hash cannot create a second approval or release.
11. A Board-reserved transaction cannot be approved by the CEO alone, and an ordinary delegated PO does not unnecessarily route to the Board.
12. Tenant A cannot read, approve or infer Tenant B's organization, personnel, policy or transaction data.

## 14. References and design basis

- IFC, *Implementing Corporate Governance Practices* — separation of Board and CEO responsibilities, Board oversight, delegation of authority and authority-matrix practice: https://www.ifc.org/content/dam/ifc/doc/mgrt/cg-handbook-english-26-nov.pdf
- Ethiopia Ministry of Justice — Federal Public Procurement and Property Administration Proclamation No. 1333/2024: https://justice.gov.et/en/law/the-federal-public-procurement-and-property-administration-proclamation/
- WIPO Lex — Commercial Code of Ethiopia Proclamation No. 1243/2021, entry into force 12 April 2021: https://www.wipo.int/wipolex/en/legislation/details/21658
- Ethiopia Commercial Code English text reference (verify against the Amharic Gazette for legal use): https://ethiodata.et/wp-content/uploads/2023/01/Commercial-Code-of-Ethiopia-English-Proclamation-No.-1243_2021.pdf
- GAO, *Financial Audit Manual* — authorization, supporting evidence, matching and segregation-of-duties control reference: https://www.gao.gov/assets/gao-24-107026.pdf

This is an ERP organization and control design, not a legal opinion or the customer's final Board charter. Each customer configures its legally valid organization, actual job appointments, constitution, bank mandates and signed DoA before activating approvals.
