# End-to-End ERP Technology and Intelligent Experience Blueprint

**Product:** Modern Garment End-to-End Addis ERP  
**Decision date:** 25 November 2026  
**Status:** Product Owner-requested, comprehensive product/technology blueprint. Implementation is staged through the approved WP-00–WP-16 sequence.  
**Relationship:** Companion to [Agent-Native Technology Integration Standard](agent-native-technology-integration-standard.md) and [Platform Architecture Standard](platform-standard.md). This document describes the complete application experience and the technologies applied across the garment value stream; it is not only an agent specification.

## 1. Product outcome

Build one connected, customer-neutral apparel business operating system that starts before production and follows the product through design, sourcing, manufacturing, quality, finished-goods handling, distribution, retail/wholesale/digital sales and the final customer. It must also receive customer returns, fit feedback, product performance and sell-through data to improve the next initiative and collection.

The ERP is a coordinated set of business applications on one governed platform—not one giant screen and not disconnected department software. Each user sees role-appropriate workspaces while all domains share identity, product, organization, transaction, event, audit and permission standards.

The target experience combines:
- Complete structured business applications and dashboards.
- A persistent conversational assistant and contextual agent actions.
- Popup/context panels, guided cards, approvals and notifications.
- Voice, image, camera, barcode/QR and supported device interactions.
- Secure document upload, extraction, mapping, preview and controlled commit.
- Event-driven workflows, integrations, analytics and explainable decision support.
- Mobile-friendly and resilient operation for factory, warehouse, branch and field users.

**Important implementation status:** this is a target architecture and functional blueprint. It does not claim that every screen, workflow, agent, device adapter, external connector or AI capability is already implemented. Each feature must be implemented, tested and handed over in its numbered work package.

## 2. Design principles

1. **One business truth:** Laravel domain services and PostgreSQL are authoritative for business rules and persisted transactions. No parallel AI database becomes the source of truth.
2. **Workflow before chatbot:** every business process remains usable through structured screens/API. Conversation accelerates the workflow; it does not replace required fields, controls or approvals.
3. **Right interaction for the job:** a barcode scan should be immediate; a complex purchase review needs a structured table; voice is useful on the factory floor; a financial posting needs an explicit review/approval screen.
4. **One identity and permission model:** tenant, legal entity, site, factory, warehouse, branch, store, channel, user and role scopes are applied consistently.
5. **Complete traceability:** preserve the genealogy of style/revision, raw-material lot/roll, cut bundle, operation/WIP, finished garment, carton, shipment, store sale and customer return.
6. **Domain ownership:** inventory, POS, finance, procurement, HR/payroll, manufacturing, quality and customer processes own their own invariants and transactions.
7. **Human accountability:** AI may explain, search, prepare, recommend and perform explicitly permitted actions. It may not approve its own high-impact transactions or override legal, safety, quality or employment controls.
8. **Open extension, not speculation:** integrate mature capabilities now; pilot emerging ones; allow future device and protocol classes without creating placeholder modules for technologies not yet known.
9. **Useful under failure:** core ERP screens and deterministic workflows must remain available when model providers, external integrations or internet connectivity are unavailable.
10. **Measurable benefit:** every feature should help plan, execute, validate, prevent, optimize, learn or improve a real apparel process.

## 3. Complete application experience

### 3.1 Universal ERP workspace

The web application should provide a consistent shell across domains:

- **Main navigation:** role-aware modules, favorites and recent work; users only see authorized areas.
- **Global search:** products, variants/SKUs, orders, suppliers, lots/rolls, bundles, cartons, locations, documents, customers and workflows, always permission-filtered.
- **Command palette / quick actions:** keyboard-accessible command such as Ctrl/Cmd+K to find a record, create a permitted draft, scan an item or open a workflow.
- **Task inbox:** approvals, assigned work, exceptions, overdue actions, quality holds, agent tasks and integration failures.
- **Context header:** tenant/company, legal entity, site/branch, operating date/shift and active work context.
- **Workspace home:** role-specific KPIs, tasks, alerts and suggested next actions rather than one generic dashboard.
- **Responsive navigation:** desktop for planning/analysis; tablet for supervisors and warehouse stations; mobile for scans, quick actions, field visits and approvals.
- **Consistent detail pages:** summary, activity timeline, documents, relationships, audit/evidence, tasks and permitted actions.

Do not put every feature on one dashboard. Use progressive disclosure and role-specific views.

### 3.2 Persistent chatbot / assistant panel

The ERP includes a persistent assistant entry point—normally a floating chat launcher at the lower corner of the desktop workspace and a clearly accessible assistant control on mobile. It opens a side panel or full-height mobile sheet without losing the user's current record or form context.

The assistant must support:
- Text conversation with context-aware questions and follow-up.
- Record references such as the current style, order, PO, line, machine, lot, bundle, carton, branch or customer.
- Natural-language search, explanations, comparisons, summaries and next-step guidance.
- Structured response blocks: tables, charts, record cards, document previews, checklists, comparison panels and workflow/action cards.
- File attachment, paste, drag/drop, camera/photo capture and voice entry where available.
- Links back to the underlying ERP record and source evidence.
- Task progress, queued/running/awaiting-approval/failed/completed states.
- Conversation history subject to user, tenant, purpose and retention controls.
- Clear distinction between facts, calculations, assumptions, estimates, recommendations, drafts and confirmed transactions.

Example prompts:
- “Show which cutting bundles are waiting for line 4.”
- “Why is order SO-2026-019 late, and what can we do?”
- “Read this supplier quotation and compare it with our last approved price.”
- “Prepare today's branch POS import and show unknown SKUs before I submit.”
- “Find all cartons for this shipment and show the dispatch status.”

The assistant must not simply answer “Done” when it has only prepared a draft or issued a request. It confirms a committed action only after receiving a successful backend receipt and audit identifier.

### 3.3 Contextual popup, popover and message-box behavior

“Popup” is not one generic component. Use the right surface for the interaction:

| Surface | Purpose | Behavior |
|---|---|---|
| Contextual popover | Quick help or suggested action beside a field, scan result or record | Non-blocking; dismissible; anchored to the item |
| Toast/snackbar | Short status such as saved, queued or failed | Non-blocking; accessible; never the only place for a critical alert |
| Persistent notification | Approval request, shortage, quality hold, late task or integration exception | Stays in notification/task inbox until handled or acknowledged |
| Confirmation dialog | Confirm a consequential action, show affected records and consequences | Blocking; clear Cancel and Confirm; permission and server validation still required |
| Agent action card | A draft, proposed change, comparison, import preview or recommended plan | Shows sources, assumptions, changes, warnings, confidence and available actions |
| Approval panel | Maker-checker or named authorization | Shows requester, approver, scope, policy, evidence and decision trail |
| Guided wizard/drawer | Multi-step business process | Saves draft/progress and supports back, pause, validation and recovery |

Do not use repeated popups for every event. Avoid interrupting production operators with low-priority notifications. Critical safety, quality or authorization holds must remain visible until resolved through the correct workflow.

### 3.4 Voice interface

Provide voice as an optional input/output mode, not as the only way to use the ERP.

- Push-to-talk for noisy factory, warehouse and retail environments; hands-free continuous voice only where the user explicitly enables it and privacy permits.
- Natural speech-to-speech conversation where supported; transcription-first mode as a fallback.
- Visible microphone state, recording indicator, transcript, language selection, mute/stop control and clear permission-denied state.
- Support interruption/barge-in, confirmation questions and read-back of important quantities, dates, SKUs, locations and monetary amounts.
- Voice can search, explain, prepare a draft, dictate notes, open a screen, start a scan or stage a workflow.
- Before a material transaction, show or speak back the proposed action and require the configured confirmation/approval. Voice recognition alone is not authorization.
- In shared production areas, avoid speaking private employee, customer, finance or payroll data aloud.
- Keep the text and structured UI equivalent available for accessibility and noisy environments.

**Current technology reference:** OpenAI's current audio guidance recommends GPT-Live for new conversational voice applications; the Realtime API supports stateful speech-to-speech sessions, tool use and low-latency turn-taking patterns. The ERP should isolate this behind a voice-provider adapter, use secure short-lived client credentials and keep business tools server-authorized. Evaluate the current GA transport/session guidance at implementation time. See [Audio and voice](https://developers.openai.com/api/docs/guides/audio), [Realtime API](https://developers.openai.com/api/docs/guides/realtime) and [Realtime conversations](https://developers.openai.com/api/docs/guides/realtime-conversations).

### 3.5 Document upload and intelligent intake

The assistant and relevant domain screens must support secure attachment from desktop/mobile:
- Drag and drop, file picker, camera capture, mobile photo, paste and supported cloud/document integration.
- Upload progress, pause/retry when supported, file size/type validation, duplicate detection, hash and original-file preview.
- Supported business formats should include approved PDF, XLS/XLSX, CSV/TSV, DOC/DOCX, image and other explicitly configured formats. Actual accepted types depend on the parser and selected AI provider/API; never assume a provider accepts every file.
- Preserve the original file in controlled object storage. Keep uploader, tenant, branch, source, time, checksum, classification, retention and access metadata.
- Parse in a bounded worker. Do not run spreadsheet macros, scripts, embedded code or active content.
- Extract to typed staging records with source page/sheet/row/cell references, confidence, warnings and validation errors.
- Show a preview and comparison against ERP master/transaction data.
- The user corrects mappings or missing values. Reusable mappings are only learned after an authorized confirmation.
- Commit through the owning domain service after required permissions and approval. Preserve an immutable import receipt and reconciliation report.

Common file workflows:
- Tech pack, measurement chart, BOM and artwork into PLM.
- Supplier quotation, proforma invoice, purchase order, delivery note and certificate into sourcing/procurement.
- Goods received note, roll list, fabric inspection and shade report into receiving/quality.
- Production plan, operation bulletin, machine/maintenance record and time report into manufacturing/IE.
- Defect photographs, inspection sheets, CAPA evidence and compliance documents into Quality.
- Branch POS sales workbook, stock count and tender summary into Retail/WMS.
- Supplier invoice, expense evidence, bank statement and tax document into Finance, subject to accounting controls.
- Payroll input/attendance evidence into HR/Payroll, with strict privacy and approval.

OpenAI's Responses API file-input documentation describes supported spreadsheet, PDF, document and text/code inputs, with different extraction behavior by type. Provider file processing is an optional extraction aid; the ERP's own validation, staging, permission and commit pipeline remains mandatory. See [File inputs](https://developers.openai.com/api/docs/guides/file-inputs).

### 3.6 Camera, scanning and device interaction

The ERP should offer a universal scan action in relevant screens. A scan may resolve:
- Product/style/variant/SKU.
- Fabric roll, lot, shade or material container.
- Cut lay, bundle/panel, sewing batch or WIP container.
- Carton, pallet, shipment or return.
- Warehouse/bin/location, machine, tool or asset.
- Supplier document, customer order, store or delivery identifier.

Provide a hardware-independent device adapter. Support keyboard-wedge scanners and approved handheld/RFID devices where available; camera-based scanning is an additional path, not the only path. The browser Barcode Detection API is not supported consistently across all major browsers, so feature-detect and keep a tested scanner-library/device/manual-entry fallback. See [MDN Barcode Detection API](https://developer.mozilla.org/en-US/docs/Web/API/Barcode_Detection_API).

Use a scan to identify and prepare a context-specific action, not silently post a movement. A warehouse scan may open a receive/move/pick task; a factory scan may register bundle arrival/operation start; a retail scan may add the correct variant to a sale cart. The owning workflow validates status, quantity, location and permission before any ledger transaction.

### 3.7 Notifications and collaboration

- In-app notification center with unread/priority filters, links to source records and acknowledgement history.
- Real-time updates for approvals, inventory shortages, machine downtime, quality holds, late orders, task completion and integration failures.
- Email, SMS, push, WhatsApp or partner messaging only through approved channel adapters, consent, delivery policy and retention controls.
- Role-targeted notifications: operator receives the next task; supervisor receives line exceptions; quality receives holds; planner receives material/capacity risk; management receives governed KPI exceptions.
- Escalations use policy-defined deadlines, responsible role and fallback. A message delivery receipt is not proof that a business action was completed.

### 3.8 Mobile and intermittent connectivity

Provide responsive, installable PWA-style experiences where useful for factory, warehouse, store and field roles. Cache only approved static assets and explicitly permitted non-sensitive reference data. Service workers, background sync and push notifications may support intermittent connectivity, but browser support and device policy must be tested.

Offline business actions are queued locally with identity/device/time/source and an idempotency key. Show **Pending sync** until the server validates and acknowledges the event. Never claim a central inventory, payment, POS or production transaction succeeded while it exists only on a device. Do not cache payroll/finance/private data by default. See [MDN offline and background operation](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Offline_and_background_operation).

### 3.9 Accessibility and language

- Keyboard navigation, semantic controls, screen-reader labels, visible focus, contrast, reduced motion and accessible validation.
- Touch targets suitable for glove-friendly tablet/handheld operation without sacrificing precision for financial data entry.
- Language-aware labels, formats, calendars, currency and number/date parsing; locale-specific content is explicit and verified.
- Voice transcripts and translated content are labeled as machine-generated until accepted.
- Do not make color the only status signal; pair it with text and icon.
- Angular 22's release notes identify Signal Forms, Asynchronous Signals and Angular Aria as stable APIs; use where appropriate after validating project compatibility and accessibility behavior. See [Angular v22 release](https://angular.dev/events/v22).

## 4. Complete application lifecycle: initiative to end customer and back

The following is the business application map. Each stage uses dedicated screens/workspaces, records, events and approval paths. Domain-specific routes remain configurable; not every tenant owns a factory or every optional process.

### Stage 1 — Business initiative, market insight and collection strategy

**Applications:** market/customer insight, initiative pipeline, brand strategy, season calendar, collection/range planning, assortment, open-to-buy, pricing and profitability.

**Technology in use:** Angular planning boards and dashboards; Laravel workflows and approval services; sourced knowledge/document retrieval; analytics and forecast scenarios; import of market, sales, return and sell-through data.

**Agent involvement:** research assistant summarizes source-linked market evidence; initiative agent drafts business cases; range agent compares category/price/size/colour options and forecasts; analyst agent highlights prior collection performance and uncertainty.

**Human control:** merchandiser/brand/product owner approves strategy, range freeze, budget and assortment. An agent cannot publish a collection or invent market evidence.

**Interaction examples:** chatbot asks “what moved last season?”; a contextual card shows opportunity gaps; upload market reports; voice-dictated initiative brief; approval dialog for a range freeze.

### Stage 2 — Design studio, Product Master and PLM

**Applications:** idea and moodboard workspace, product/style master, colour/material library, variant and SKU, pattern/grading, measurement/tolerance, technical pack, BOM, 2D/3D design assets, sample rounds, fit session, costing and revision management.

**Technology in use:** document and image storage; controlled CAD/2D/3D file integration; versioned product data; secure preview/annotation; structured approvals; Product Master API and variant uniqueness rules.

**Agent involvement:** design brief assistant; tech-pack and BOM extraction; image/document summarizer; variant/SKU draft assistant; measurement/fit issue summarizer; change-impact agent that identifies affected BOM, costing, procurement and production orders.

**Human control:** technical designer, pattern/fit team, merchandiser and quality/production representatives approve design, measurement, fit and pre-production sample. Agent suggestions never overwrite approved revisions.

**Interaction examples:** upload PDF/XLSX tech pack; camera photo of sample/defect with annotation; voice note from fit meeting; contextual comparison popup between revision A and B; approval card for a controlled tech-pack release.

### Stage 3 — Sourcing, supplier collaboration and procurement

**Applications:** supplier onboarding/qualification, mill and factory records, quotation/tender comparison, sourcing calendar, purchase requisition, RFQ, purchase order, contracts, supplier portal, import and inbound logistics.

**Technology in use:** supplier portal/API, document extraction, approval workflow, versioned supplier/material master, event/webhook integration, procurement and finance application services.

**Agent involvement:** quotation/document extraction; compare approved specification, price, lead time, MOQs and supplier performance; identify missing certificates and delivery risks; prepare requisition/PO drafts; explain variances.

**Human control:** supplier approval, contract award, PO approval, bank/payment and legal/tax classification remain under configured procurement/finance and human authority.

**Interaction examples:** supplier uploads a quotation; assistant extracts line prices and flags spec mismatches; approver sees a side-by-side comparison popup; chat explains late supplier risk.

### Stage 4 — Inbound receiving, raw-material WMS and material quality

**Applications:** ASN/expected receipt, gate/vehicle receiving, GRN, supplier lot, fabric roll, shade/dye lot, width/weight/length, trim/accessory lot, inspection, quarantine, acceptance, bin/location put-away and supplier claims.

**Technology in use:** barcode/QR/RFID where appropriate, handheld/mobile scanning, scales/measurement devices through adapters, photo evidence, lot/roll event history, warehouse location model and quality service.

**Agent involvement:** document-to-expected-PO matching; roll list extraction; shortage/damage variance explanation; inspection-photo triage; suggested put-away based on allowed location, shade/lot and storage constraints.

**Human control:** receiver confirms actual quantities; qualified QC disposition and quarantine release are controlled by authorized roles. AI cannot accept rejected material.

**Interaction examples:** scan PO, roll and bin; upload delivery note and fabric inspection; contextual popup suggests correct PO/line; voice capture of a receiving exception; exception task is assigned to procurement/quality.

### Stage 5 — Pre-production, industrial engineering and production planning

**Applications:** style readiness, approved tech pack/BOM, route and operation bulletin, method/time study, SMV/SAM governance, capacity calendar, skill matrix, machine/attachment capability, line balancing, material/trim readiness, production order, plan and shift schedule.

**Technology in use:** deterministic planning rules; scenario optimizer in Python where beneficial; material planning/MRP; skill and capacity data; machine calendars; versioned routing and production readiness checklist.

**Agent involvement:** readiness agent identifies missing approvals/material/skills; IE assistant compares method standards and suggests balance; planning agent produces explainable capacity/line-plan scenarios, risks and expected completion; scheduler summarizes impact of absenteeism, maintenance or shortage.

**Human control:** IE approves methods/SMV; planner and factory manager approve feasible line/shift plans; worker safeguards and quality requirements cannot be traded away for speed.

**Interaction examples:** supervisor asks by voice “what should line 3 make next?”; plan card shows assumptions and constraint; popup warns trims are not ready; upload operation bulletin; approval workflow releases the plan.

### Stage 6 — Manufacturing execution by department

**Applications:** cutting/spreading/marker, lay and bundle identity; separate trim issue; print/embroidery/embellishment; sewing line and operation queue; optional wash/dye/special process; finishing/pressing; subcontract processing; maintenance and WIP.

**Technology in use:** station tablets/terminals; scan actions; barcode/RFID where suitable; digital work instructions/SOP display; machine/edge event adapters; Andon; operator/line/shift/operation data; offline capture; event-driven WIP updates.

**Agent involvement:**
- **Shop-floor supervisor assistant:** explains shortages, aging WIP, line status, bottleneck and next action.
- **Cutting assistant:** checks marker/revision/material readiness, reconciles cut quantity, creates/validates bundle genealogy and highlights shortage/scrap variance.
- **Trim issue assistant:** independently verifies BOM revision, lot/colour/size and issued/returned quantities; never merges trim consumption with fabric issue.
- **Sewing/line assistant:** surfaces the next operation, skill/machine constraints, hourly target/actual, WIP and first-pass quality; recommends rebalancing, not unilateral assignment.
- **Special-process assistant:** prepares recipe/batch documentation and sampling/checklists for wash/dye/print/embroidery; process parameters and release require authorized staff.
- **Maintenance assistant:** correlates fault/downtime reports and suggests maintenance tickets; safety interlocks remain independent of AI.

**Human control:** operator confirms physical work; supervisor authorizes production transitions; IE owns standard methods; maintenance owns machine safety; production and quality retain release authority. Never use AI to run safety-critical machine controls or bypass machine guards.

**Interaction examples:** scan bundle at each handoff; tap a station card to open SOP/video/photo work instruction; voice-dictate a stoppage; machine event creates a downtime popup for supervisor; agent proposes a line rebalance card with affected operations and requires approval.

### Stage 7 — Quality assurance, CAPA and product compliance

**Applications:** incoming, first-piece, inline, end-line, special-process, finishing, final and shipment inspection; AQL/inspection plans where approved; defect taxonomy, evidence, quarantine, rework, reinspection, root cause and CAPA.

**Technology in use:** mobile QC forms; image/photo/video evidence; structured defect code/measurement capture; lot/bundle/operation genealogy; rules-based quality holds; analytics for defect Pareto and supplier/line trends.

**Agent involvement:** classify possible defect for reviewer triage; summarize trends; group repeat defects by style/material/operation/line; draft root-cause hypotheses and CAPA; check evidence completeness; notify responsible team.

**Human control:** qualified inspectors confirm defect and disposition; authorized QC releases held goods; CAPA owner approves corrective/preventive action. Image classification is advisory, not final inspection.

**Interaction examples:** camera captures seam/measurement defect; assistant suggests candidate defect codes with confidence; QC chooses final code; a persistent alert blocks shipment until formal quality release.

### Stage 8 — Packing, finished-goods warehouse and shipment preparation

**Applications:** finished-goods receipt, inspection release, folding/packing, size-colour assortment, cartonization, carton/pallet identity, labels, weight/dimensions, carton audit, staging, pick/pack/ship and export/shipment documents.

**Technology in use:** handheld scan, barcode/QR/GS1 identifiers where adopted, label printer adapters, scales and dimension devices, shipment integration, inventory ledger, carton genealogy and document generation.

**Agent involvement:** packing instruction retrieval; carton content/reconciliation; carton/PO/order mismatch warnings; shipment document extraction; dispatch completeness checklist; missing-document and shipment-risk summaries.

**Human control:** packer scans and confirms contents; warehouse supervisor resolves variance; authorized shipping/export reviewer signs controlled shipment documents. Agent does not certify export/legal compliance.

**Interaction examples:** scan pieces into carton; popup shows expected remaining size ratio; upload packing list; assistant builds draft carton discrepancy report; approval card releases dispatch after all gates.

### Stage 9 — Distribution network, DCs, depots, branches and stores

**Applications:** central DC, regional DC, depot, branch backroom, company-owned stores, replenishment, inter-node transfer, stock allocation, available-to-promise, delivery, proof of delivery and reverse logistics.

**Technology in use:** inventory ledger, location-aware ATP/CTP, mobile scanning, route/transport integration, EDI/API, event updates, dashboards and offline-capable receiving/dispatch workflows.

**Agent involvement:** recommend replenishment or transfer based on sales, available stock, lead time and constraints; detect slow/fast movers; explain stockout; prepare pick/transfer tasks; reconcile receiving variance; summarize carrier delay.

**Human control:** authorized staff approve transfer/adjustment/dispatch exceptions; stock movements commit through inventory ledger and transfer workflow. AI never creates stock just because a forecast expects it.

**Interaction examples:** scan transfer QR; recipient uploads delivery evidence; agent compares dispatch versus receipt; supervisor sees a popup for shortage/damage and chooses a reason-coded correction.

### Stage 10 — Sales, POS, wholesale, retailer/reseller and digital commerce

**Applications:** customer/account pricing, quotations, wholesale orders, retailer/reseller portal, sales order management, allocation, POS checkout, e-commerce/D2C, marketplace/channel connector, promotions/discounts, customer credit and returns.

**Technology in use:** unified order API, channel adapters, POS terminals, product catalogue, pricing/promotion engine, payment/tender adapter, idempotent offline sales sync, stock reservation, order events and finance reconciliation.

**Agent involvement:** sales assistant searches product/variant availability; drafts quotations and follow-up; product recommendation uses allowed customer context; POS import assistant parses branch workbook and maps variant SKUs; order agent explains shortage/fulfilment; service agent prepares return/exchange request.

**Human control:** sales/credit/pricing policies govern discounts and terms; user confirms POS batches and exceptions; payments and finance postings follow existing approval and reconciliation controls. A chatbot cannot invent a price or mark a payment as settled.

**Interaction examples:** scan EAN/2D code to add the exact size/colour to cart; chat asks for stock in other branches; checkout uses a visible tender/payment result; upload POS Excel for batch preview; a price override opens a reason-and-approval dialog.

### Stage 11 — Final customer and product experience

**Applications:** customer account/profile, product catalogue, order tracking, delivery status, product care, size/fit guide, warranty/repair or service where offered, product feedback, returns/RMA and customer support.

**Technology in use:** secure customer-facing web/mobile experience; order/tracking APIs; QR product information where adopted; digital content/knowledge; customer notification preferences; service and return workflows.

**Agent involvement:** customer assistant answers order/product/care questions using approved product knowledge; retrieves order status only after customer authentication; helps start a return/exchange; captures fit/quality feedback and routes complaints to service/quality teams.

**Human control:** identity and account authorization apply; refund, replacement, warranty exception and complaint closure follow business policy. The customer assistant must not expose internal supplier costs, employee data or other customers' orders.

**Interaction examples:** customer scans a product QR to open authorized product/care/traceability information; voice or chat requests delivery status; customer uploads receipt/product photo for a return case; agent creates a draft RMA, then customer confirms.

### Stage 12 — Returns, recovery and closed-loop improvement

**Applications:** return authorization, store/customer receipt, inspection, resale/refurbish/repair/dispose disposition, refund/credit, supplier claim, defect/CAPA feedback, product/fit analytics and next collection planning.

**Technology in use:** return scan, photo/document intake, inventory disposition ledger, order/payment references, returns workflow, analytics and traceable feedback back to PLM/range planning.

**Agent involvement:** classify return reason for review; compare feedback by style/size/material/market; identify recurring quality and fit signals; draft CAPA/engineering changes and product-learning summary.

**Human control:** QC confirms product condition; authorized staff decides disposition/refund; technical/product teams approve specification changes. The feedback loop must not silently revise an approved tech pack.

## 5. Supporting enterprise applications across the value stream

These are connected applications, not afterthought modules:

| Application | Required technology and capability |
|---|---|
| Finance, accounting and costing | Double-entry GL, AP/AR, bank/cash, inventory valuation, product/operation costing, budget, tax and close; deterministic calculations and controlled posting |
| Procurement and supplier management | Supplier lifecycle, contracts, RFQ, purchase-to-pay, quality and delivery performance; portal and document intake |
| HR/workforce/payroll | Employee lifecycle, attendance, leave, shifts, payroll, training, competency, safety and differentiated compensation; restricted personal data and statutory approvals |
| Maintenance and assets | Equipment, preventive/corrective maintenance, parts, downtime, calibration and energy; device telemetry is evidence, not automatic safety control |
| Document and knowledge management | Versioned technical documents, SOPs, policies, contracts, certificates, evidence, retention and tenant-aware search |
| Workflow and approvals | Versioned state machine, maker-checker, delegation, SLA, escalation, exception/rework and audit trail |
| Business intelligence | Governed KPIs, operational dashboards, data lineage, drill-down, scheduled reports and role-based export |
| Compliance and localization | Effective-dated legal sources/rules, evidence, obligations, local review and fail-closed activation |
| Platform administration | Tenant/company/site config, identity, subscription, roles, integrations, feature flags, access review and health monitoring |

## 6. Agent architecture and detailed involvement

### 6.1 Agent layers

1. **Front-door ERP Assistant / Router:** receives text, voice, image or file intent; identifies current module and context; asks for missing information; routes to a domain capability.
2. **Task Supervisor / Orchestrator:** decomposes multi-step requests, selects specialists, maintains task state, reconciles results and owns the user-facing response.
3. **Domain agents:** bounded specialists for initiative/merchandising, design/PLM, sourcing/procurement, inventory/WMS, IE/planning, shop-floor operations, quality/CAPA, distribution, sales/POS/customer service, Finance and HR (subject to data restrictions).
4. **Document and perception services:** parsers and optional image/audio extraction; return structured observations and source references, not direct transactions.
5. **Knowledge retrieval:** permission-filtered search over approved SOPs, work instructions, product specs, policies, supplier contracts and evidence. Search results retain provenance and version.
6. **Capability/tool registry:** only lists installed and enabled operations; records schema, role/tenant scope, risk, owner and approval.
7. **Deterministic workflow engine/domain services:** enforce business rules, state transitions, database transactions, calculations, audit and notifications.
8. **Human review/approval:** confirms material actions, uncertain classifications and high-impact operations.

### 6.2 Recommended specialist agents

| Specialist | Typical responsibilities | Must not do |
|---|---|---|
| Initiative and Merchandising Agent | Source market insights, summarize sell-through, prepare initiative/range/assortment scenarios | Approve its own business case or invent research |
| Product and PLM Agent | Extract tech pack, compare revisions, prepare product/variant/BOM drafts, summarize fit notes | Publish unapproved spec or silently revise BOM |
| Sourcing and Supplier Agent | Extract quotations, compare suppliers, flag missing certificates, prepare RFQ/PO draft | Approve supplier/contract/payment without policy |
| Material and WMS Agent | Scan/lookup roll, lot, bin and quantity; prepare receipt/put-away/pick/transfer; explain discrepancies | Post unauthorized inventory movement |
| IE and Planning Agent | Analyze SMV/SAM, skill coverage, capacity, balance, MRP and completion scenarios | Change approved method/SMV or impose unsafe targets |
| Shop-floor Agent | Guide SOP, identify next operation, explain WIP/shortage/downtime, draft shift handover | Control safety interlocks or falsely confirm physical production |
| Quality and CAPA Agent | Triage evidence, summarize defects, draft CAPA and trace root-cause candidates | Make final inspection or quality-release decision |
| Distribution and Logistics Agent | Recommend allocation/replenishment, trace dispatch and receipt, explain carrier delay | Create stock or mark proof of delivery without evidence |
| Retail, POS and Customer Agent | Map POS files, find exact variants, prepare sales/returns and answer customer queries | Override prices, payment state or customer access |
| Finance Assistant | Extract invoice/bank data, propose match/reconciliation, explain cost and variance | Post journals, release funds or determine unverified tax |
| Workforce and Learning Assistant | Retrieve policy, suggest training and summarize skill coverage | Make adverse employment decisions or autonomously execute payroll |
| Management Intelligence Agent | Answer governed KPI questions, compare plans/actuals and prepare evidence packs | Invent KPI definitions or expose restricted data |

The Chief Architect/Codex development team described in the separate Codex Agent Operating Model is a development-time team and is not this runtime agent list.

### 6.3 Standard agent execution lifecycle

1. Receive user intent and identify conversation, tenant, role, module, current record and requested outcome.
2. Resolve authenticated user and scope on the server; never trust model-supplied identity.
3. Retrieve only authorized, current, versioned records and documents.
4. Determine whether the request is read, explain, draft, reversible action, material transaction or prohibited/high-impact action.
5. Ask a focused clarification if a required field or business decision is genuinely missing.
6. Plan a bounded set of steps and select tools from the capability registry.
7. Present assumptions, evidence, confidence, unresolved issues and proposed changes before a material commit.
8. For a draft, save to a staging/draft record. For a transaction, require the configured role and approval.
9. Invoke the owning Laravel application service, which re-validates identity, permission, workflow state, constraints, idempotency and transaction.
10. Verify the backend response, transaction receipt and audit event. Do not infer completion from a model response.
11. Show the user a structured result, source references, exceptions and next action.
12. Persist task/audit state outside the model conversation; apply retention and redaction rules.

### 6.4 User action tiers

| Tier | Agent action | Example |
|---|---|---|
| A0 Read/explain | Search and summarize within access | “Where is bundle B-102?” |
| A1 Draft/recommend | Prepare but do not post | Draft PO, product SKU map, line plan, RMA |
| A2 Reversible low impact | Execute scoped, reversible action | Update an approved draft or mapping |
| A3 Material transaction | Domain permission plus configured human confirmation | POS batch commit, stock transfer, order release |
| A4 High impact/irreversible | Human/professional approval; no autonomous execution | Payroll payment, finance journal posting, statutory activation, destructive production operation, production deployment |

A model or agent cannot act as the approver of its own request. Maker-checker and segregation-of-duties controls remain in force.

## 7. Technology architecture and how each layer is applied

The current approved platform stack remains unchanged. This blueprint assigns responsibilities to that stack; it does not approve a parallel framework or unnecessary infrastructure.

| Layer | Approved baseline | Application across the ERP |
|---|---|---|
| Web experience | Angular 22 + TypeScript | Responsive application shell, domain workspaces, chat, popups, approvals, document intake, camera/voice UI and accessible forms |
| Frontend runtime/tooling | Node.js 26 LTS + npm lockfile | Angular build, tests, assets and local development; not a second transactional API |
| Business/API layer | PHP 8.5 + Laravel 13 | Domain services, authorization, workflow, API, validation, jobs, notifications, tool execution and audit |
| Transactional data | PostgreSQL 18 | Product, order, inventory, WIP, quality, finance, people, RLS, constraints, event/outbox state |
| Cache and queue coordination | Redis 8.2 support line | Cache, rate limits, transient session/queue coordination; never sole business-record storage |
| Specialist computation | Python 3.15 line in approved standard | Forecasting, optimization, analytics, document/image processing adapters where justified; no direct transaction-table writes |
| Files and media | S3-compatible object storage interface | Tech packs, CAD assets, photos, videos, invoices, certificates, evidence, exports and generated documents |
| Packaging | Docker + Compose v2; immutable images for release | Reproducible developer/test runtime, staging and controlled release |
| API/event contracts | REST + OpenAPI 3.1 + transactional outbox | ERP UI, mobile/partner integrations, event consumers, versioning and reconciliation |
| Device/connectivity | Adapter interfaces + browser/mobile capabilities | Scanners, cameras, printers, scales, RFID/IoT gateways, POS and future edge devices |
| Identity/security | Existing SaaS security standard | Authentication, MFA, roles, tenant/site/branch scoping, RLS and audit |
| Agent/provider adapters | Versioned provider-neutral interface | OpenAI or other approved model/voice/document provider can be evaluated without moving business rules out of ERP |
| Observability | Application logs, metrics, trace/correlation IDs; OpenTelemetry-compatible instrumentation | End-to-end request, workflow, queue, device, provider and agent execution trace |

**Do not add a new database, vector engine, message broker, microservice framework, frontend, workflow engine or AI vendor as a default.** Add infrastructure only after measured scale/functional need, security/licence/support review, total operating cost and the required architecture decision.

## 8. Data, identity, events and traceability

### 8.1 Core business identifiers

Use stable tenant-scoped identifiers for company, legal entity, site, factory, line, warehouse, bin, branch, store, sales channel, customer, supplier, product/style, revision, colour, size, variant/SKU, material, supplier lot, roll, bundle, WIP container, garment, carton, pallet, shipment, order, return and financial document.

### 8.2 Business events

Represent significant changes as traceable events with: event ID, tenant and scope, source document, object IDs, product/revision, from/to location or state, process step, event time, actor/device, quantity/UOM, reason, evidence, correlation/causation and schema version.

Typical events:
- MaterialOrdered, MaterialReceived, RollInspected, RollAccepted/Quarantined, MaterialPutAway, MaterialIssued, MaterialReturned.
- LayReleased, CutCompleted, BundleCreated, BundleMoved, TrimIssued, OperationStarted/Completed, WIPTransferred.
- QualityInspected, DefectRaised, ReworkStarted/Completed, CAPAOpened/Closed, QualityReleased.
- CartonClosed, ShipmentPacked, ShipmentDispatched, DCReceived, BranchTransferred, StoreReceived.
- POSBatchStaged/Approved/Committed, SaleCompleted, PaymentReconciled, CustomerReturnReceived, RefundApproved.
- AgentTaskCreated, ToolInvoked, ApprovalRequested/Granted/Rejected, ActionCommitted/Failed.

Where partner interoperability and physical traceability justify it, evaluate a mapping to GS1 EPCIS 2.0 visibility events and GS1 Digital Link identifiers/QR experiences. EPCIS defines a model and interfaces for visibility event data across enterprises; it is an interoperability option, not a demand to redesign every internal table. See [GS1 EPCIS 2.0](https://www.gs1.org/standards/epcis) and [GS1 Digital Link](https://support.gs1.org/support/solutions/articles/43000729075-what-is-gs1-digital-link-).

### 8.3 Search, analytics and retrieval

- Transactional queries use PostgreSQL and domain APIs.
- KPI metrics use governed definitions, ownership, lineage and effective time.
- Knowledge retrieval indexes approved documents with tenant, role, version, source and retention metadata. Permission filters apply before content reaches an agent.
- Keep originals and hashes; never use an embedding or cache as the authoritative business record.
- Data corrections use the owning domain's audited correction process.

## 9. Integration and device landscape

| Integration area | Examples | Required pattern |
|---|---|---|
| PLM/CAD/3D | Pattern, grading, 2D/3D garment design, tech-pack exchange | Versioned file/API adapter; revision and approval provenance |
| Suppliers/mills/factories | Quotations, ASN, purchase, quality certificate, subcontract WIP | Supplier portal/API/EDI, authenticated events and reconciliation |
| Factory/IIoT | Machine runtime, downtime, counters, energy and maintenance signals | Read-only telemetry first; gateway, device identity, timestamp and safe command isolation |
| Scanning/identification | 1D/2D barcode, QR, RFID, roll/bundle/carton labels | Hardware-independent scan adapter; duplicate/replay protection |
| Weighing/measurement | Scales, width/length, quality measurement devices | Device calibration, UOM mapping, confidence and operator confirmation |
| Printing | Barcode/GS1 labels, carton and shipment documents | Template/version and printer adapter; print receipt and reprint audit |
| POS/Payments | Store POS, tender provider, payment terminal | Idempotent transaction adapter; separate payment authorization/settlement/reconciliation |
| E-commerce/marketplaces | Web store, marketplace, catalogue and stock/order sync | Channel adapter, order identity, stock reservation, retry and reconciliation |
| Logistics | Carrier booking, tracking, proof of delivery | Versioned shipment API/events and evidence confirmation |
| Communication | Email/SMS/push/approved messaging | Consent, template governance, channel preference, delivery status and audit |
| External agent tools | MCP servers and partner tools | Versioned allowlisted connector, scoped authentication and permission review |

Do not assume a browser-only camera/BarcodeDetector API is available everywhere; MDN marks it limited/experimental. Use tested scanner hardware or a compatible library/adapter as well as manual fallback. Do not allow an external integration to write directly to another domain's tables.

## 10. Agent technology patterns

### 10.1 Model and orchestration patterns

- Use structured outputs/JSON Schema for extraction and action proposals; the application still validates business rules not represented in the schema.
- Use function/tool calling to invoke specific Laravel-owned capabilities, never unrestricted SQL or generic production HTTP.
- Use a supervisor with bounded specialists. Use a handoff when a specialist should own the next user response; use an agent-as-tool pattern when the supervisor should retain ownership and merge results.
- Persist long-running task state in the ERP workflow layer, not only in chat context.
- Keep retrieval, file parsing, business execution and human approvals as distinct steps.
- Add provider-neutral adapters and test model changes against representative synthetic cases before rollout.

Current OpenAI references: [Responses API](https://developers.openai.com/api/reference/responses/overview), [Structured Outputs](https://developers.openai.com/api/docs/guides/structured-outputs), [Agents SDK](https://developers.openai.com/api/docs/guides/agents/sdk), [Orchestration and handoffs](https://developers.openai.com/api/docs/guides/agents/orchestration).

### 10.2 Voice and real-time tools

The voice client must use short-lived session credentials from the trusted backend; never expose permanent API keys in Angular. If the live voice provider supports client-side tool events, the actual ERP action still calls a server-controlled endpoint which re-checks authorization and approval. The browser is a media client, not a trusted business transaction service.

### 10.3 Document/image processing

Separate file storage, virus/malware inspection, parsing, extraction, master-data matching, business validation, preview, approval and commit. AI extraction can identify candidate fields or defects; deterministic validation and a human reviewer handle ambiguous/high-risk results.

### 10.4 Agent observability

Capture correlation/task IDs, agent/provider/model version, prompt/tool schema version, retrieval references, handoff, tool invocation, approval, outcome, latency, failure and cost where available. Redact sensitive data. Use OpenTelemetry-compatible traces; GenAI semantic conventions are evolving, so pin the adopted version and do not assume a development-status convention is a stable compliance standard.

## 11. Security, privacy and human controls

- Authenticate users and derive tenant/site/branch context server-side.
- Apply Laravel policy and PostgreSQL RLS at every data boundary, including exports, retrieval, files, queues, devices and agent tools.
- Restrict file type/size, scan, parse safely and prevent macro/script execution.
- Treat document text, supplier instructions, QR payloads, web content and model outputs as untrusted data. Prompt injection must not alter system rules or permissions.
- No model-generated SQL, arbitrary shell, broad filesystem or universal admin tools for runtime agents.
- Minimize PII, employee, customer and financial data sent to outside model providers; enforce retention and contractual/legal controls.
- Money, tax, inventory valuation, payroll, SMV/SAM and statutory logic are deterministic and versioned.
- High-risk stock, payment, payroll, journal, legal, safety, quality and production actions keep domain-specific human approval.
- Audit requester, actor, approver, input source, data scope, capability/version, result and correction.
- Human users can stop a voice session, cancel a pending task, reject an action and report an incorrect recommendation.
- Provide provider kill switch, queue pause, degraded mode and safe rollback.

## 12. Reliability, performance and offline behavior

- Normal business screens are not blocked by model inference.
- Long jobs return a task ID and status; do not hold an HTTP request open indefinitely.
- Set explicit timeouts, retry caps, exponential backoff with jitter, idempotency keys, dead-letter queues and reconciliation.
- Protect against concurrent stock, order, POS, payroll and approval updates with database constraints/transactions/locking appropriate to the domain.
- Define per-role response and availability targets during WP-16 performance design; do not invent a universal SLA without workload evidence.
- Offline mode is explicit and bounded. Show pending, synced, rejected and conflict states. Reconciliation is server-led.
- Test provider outage, queue outage, slow network, device disconnect, duplicate scan, replay, expired session, stale revision and interrupted upload.
- Keep backups, restore drills, observability, alerting and incident runbooks within the existing deployment/release standard.

## 13. Implementation map against approved work packages

This blueprint does not change order or authorize skipping a work package. Capabilities enter the existing sequence:

| Package | Application/technology outcomes |
|---|---|
| WP-00 Governance | This blueprint, technology intake, scope and change controls |
| WP-01 Requirements | Map each experience, process, role, API/event, device and agent capability to requirements and acceptance tests |
| WP-02 Ethiopia legal sources | Official-source workflows, legal evidence intake and approval gates; no AI statutory activation |
| WP-03 Platform foundation | Identity, tenant/legal entity/site/branch, audit, RLS, application shell foundations, upload/security boundaries |
| WP-04 Initiative and merchandising | Market insights, initiative/collection/range, assistant search/draft and governed planning analytics |
| WP-05 PLM/product development | Product master, design/tech-pack/fit workflow, document/image intake, revision comparison and approval cards |
| WP-06 IE and pre-production | SMV/SAM, methods, skill/capacity, line plan, scenario optimizer and readiness agent |
| WP-07 Procurement/materials | Supplier portal/adapters, quotation/invoice intake, lots/rolls/shades and receiving integration |
| WP-08 Route/SOP framework | Versioned route/work instructions, workflow engine, station/device contract and agent knowledge |
| WP-09 Manufacturing execution | Cutting/bundle/trim/sewing/special process/finishing/packing station workflows, scans and WIP events |
| WP-10 Planning/workforce | Line/shift boards, skills, training, safe alerts, downtime and improvement suggestions |
| WP-11 Quality/CAPA/HSE | Mobile inspection, image evidence, defect triage, holds, rework and human release |
| WP-12 WMS/distribution/channels | Carton/pallet, DC/branch/store, scan, replenishment, POS/wholesale/retail/e-commerce, delivery and returns |
| WP-13 Finance/accounting/tax | Costing, invoices, reconciliations, controlled posting and statutory gates |
| WP-14 HR/payroll/compensation | Employee self-service, policy/learning assistant, attendance and restricted payroll review |
| WP-15 Analytics/integration/AI | Agent Gateway, capability registry, approved model/voice adapters, retrieval, event integrations, evaluation and observability |
| WP-16 E2E/security/release | Full cross-domain journeys, RLS, concurrent actions, offline recovery, security, performance, backups, rollout and rollback |

## 14. End-to-end acceptance journeys

Do not accept an experience because the chat panel renders or a file successfully uploads. Prove the complete workflow:

1. **Tech pack to production-ready style:** upload file → preserve/hash → extract → map → human correction → product/BOM draft → revision approval → downstream readiness impact. No unapproved revision is published.
2. **Roll receipt to cut bundle:** receive/scan roll → inspect/quarantine → put away → reserve/issue material → cut lay → reconcile consumed/remnant/scrap → create traceable bundle. Fabric and trim remain separate.
3. **Bundle to finished carton:** scan bundle → validate route/SOP/skills → register operation output/WIP → inline quality → rework/clear → finish/pack → carton genealogy. No scan alone posts unverified work.
4. **POS workbook to reconciled sale:** upload → parse/stage → exact variant mapping → duplicate/tender/total checks → preview → authorized approval → idempotent commit → inventory/finance reconciliation and receipt.
5. **DC to customer:** allocate available stock → pick/pack/dispatch → carrier tracking → proof of delivery → customer notification → authenticated customer status query.
6. **Return to product learning:** create RMA → inspect/photograph → disposition → refund/credit/stock posting through domains → aggregate fit/quality reason → CAPA/product feedback.
7. **Voice-to-workflow:** user speaks a request → transcript/intent shown → assistant resolves context → proposes action card → user confirms → server checks permission → workflow commits → receipt is read/displayed.
8. **Disconnected station recovery:** user/device captures permitted offline event → visible pending-sync state → reconnect → duplicate-safe reconciliation → accepted/rejected receipt → audit.
9. **Permission and injection test:** restricted user asks for unauthorized finance/HR record or uploads a document containing malicious instructions → retrieval/tool call denied → no data disclosure or action → incident evidence is recorded.

## 15. Definition of done for a technology feature

A capability is ready only when:
- The business process and intended user are identified.
- User interface and accessibility behavior are defined.
- Application/domain owner, data source and system-of-record are explicit.
- Agent involvement and action tier are defined—or agent use is explicitly excluded.
- API, event, device and document contracts are versioned.
- Permissions, tenant/branch isolation, approval, audit and retention are enforced.
- Positive, negative, duplicate, concurrency, failure, outage and recovery tests are run.
- Human confirmation and rollback/compensation paths are proven.
- Monitoring, support, cost, licensing, provider and device dependencies are documented.
- The ordinary ERP path remains usable without AI.
- Exact commit, test commands, evidence and open limitations are included in the handover.

## 16. Technology references and maturity notes

Current references checked on 25 November 2026. These sources inform technology patterns; the approved project stack and security/governance standards remain authoritative.

- [OpenAI Audio and voice](https://developers.openai.com/api/docs/guides/audio) — current conversational voice guidance and workflow options.
- [OpenAI Realtime API](https://developers.openai.com/api/docs/guides/realtime) — browser/server voice session patterns and tool interaction.
- [OpenAI Responses API](https://developers.openai.com/api/reference/responses/overview) — multimodal inputs, built-in tools and function calling.
- [OpenAI File inputs](https://developers.openai.com/api/docs/guides/file-inputs) — supported file-input methods and extraction behavior.
- [OpenAI Structured Outputs](https://developers.openai.com/api/docs/guides/structured-outputs) — JSON Schema-constrained output; business validation is still required.
- [OpenAI Agents SDK](https://developers.openai.com/api/docs/guides/agents/sdk) and [orchestration/handoffs](https://developers.openai.com/api/docs/guides/agents/orchestration) — application-owned agent runtime, specialist routing and human review patterns.
- [Angular v22 release](https://angular.dev/events/v22) — Signal Forms, Asynchronous Signals and Angular Aria stable API announcements.
- [GS1 EPCIS 2.0](https://www.gs1.org/standards/epcis) — supply-chain visibility event model and capture/query interoperability.
- [GS1 Digital Link](https://support.gs1.org/support/solutions/articles/43000729075-what-is-gs1-digital-link-) — link GS1 identifiers to web product/traceability information.
- [MDN Barcode Detection API](https://developer.mozilla.org/en-US/docs/Web/API/Barcode_Detection_API) — browser barcode support is limited; use fallback paths.
- [MDN Offline and background operation](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Guides/Offline_and_background_operation) — service workers, background sync and notification patterns.
- [MCP 2026-07-28 specification release](https://github.com/modelcontextprotocol/modelcontextprotocol/blob/main/blog/content/posts/2026-07-28-spec-ga/index.md) — optional external-tool interoperability and authorization changes; use only behind reviewed connectors.
- [OpenTelemetry GenAI agent semantic conventions](https://github.com/open-telemetry/semantic-conventions-genai/blob/main/docs/gen-ai/gen-ai-agent-spans.md) — emerging agent/tool trace model; its status is development, so pin and review the convention version before adoption.

## 17. Relationship to canonical documents

This blueprint must be read with:
- [Project Governance Agreement](../agent/project-governance-agreement.md)
- [Platform Architecture Standard](platform-standard.md)
- [Agent-Native Technology Integration Standard](agent-native-technology-integration-standard.md)
- [Codex Agent Operating Model](../agent/codex-agent-operating-model.md)
- [End-to-End Garment ERP Domain Map](../product/domain-map.md)
- [Apparel Business Operating System Charter](../product/apparel-business-operating-system-charter.md)
- [Distribution Network, Order Management and Channel Commerce](../product/distribution-and-channel-commerce.md)
- [Engineering, Security, Localization and Release Standards](../engineering/code-quality.md)

GitHub is the canonical source for implementation/version history. The matching Notion page is the durable product/architecture explanation and navigation entry.