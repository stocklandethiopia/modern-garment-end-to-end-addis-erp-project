# WP-07 — Supplier Governance, Procurement, Materials, Import and Traceability

**Package:** WP-07  
**Status:** DESIGN COMPLETED — operational implementation follows in vertical slices  
**Scope:** Approved Domain E, procurement boundaries in legal register and product/material traceability requirements. Builds from WP-03 enterprise foundation and WP-05 released BOM; feeds WP-08/WP-09 manufacturing and WP-13 AP/landed cost.

## 1. Purpose and operating principle

Procurement and sourcing ensure that the right approved material, service or subcontract capacity is sourced from a qualified supplier, under authorized terms, received and inspected, and made available to a specific product/process with full custody and cost evidence.

Supplier selection, purchase authorization, goods receipt, quality acceptance, inventory posting, supplier invoice and payment are distinct linked events owned by separate domains. An approved purchase order is not a goods receipt. A goods receipt is not AP liability approval. An AP invoice is not stock movement. Payment is not proof that materials were received or accepted.

Support local, imported, customer-nominated, consigned, customer-owned, subcontracted and internally transferred materials without collapsing their ownership or accounting treatment.

## 2. End-to-end sourcing-to-pay lifecycle

Supplier discovery and onboarding → legal/sanctions/conflict/capability review → qualification and approved-supplier scope → requisition and demand aggregation → budget/OTB check → sourcing/RFQ/tender/quote → technical/commercial evaluation → award and independent approval → contract/PO and schedule → supplier acknowledgment → advance/LC/payment instrument (where lawfully permitted) → dispatch/import/customs/transport → receiving and actual count → incoming QC/quarantine → acceptance/rejection/variance → inventory lot/roll/trim/chemical record → issue to sample/production/subcontract → supplier invoice 3-way match → tax/withholding review → AP posting and payment proposal → settlement and supplier performance review.

## 3. Supplier master and governance

### 3.1 Supplier identity and relationship

Supplier records are legal/contracting counterparties, not just names in a dropdown. Record:
- Legal name, legal form, registration number, TIN, VAT status and licence/activity evidence as applicable.
- Country, registered address, operating sites, contact roles and authorized bank/payee verification reference.
- Supplier type: fabric mill, trader, trim/accessory vendor, chemical vendor, packaging, equipment, testing lab, logistics/carrier, design service, subcontract factory, agent or other.
- Ownership/related-party declaration, conflict of interest, beneficial ownership and risk assessment where lawful and appropriate.
- Quality, capacity, technical capability, material/process scope and certifications.
- Payment terms, currency, delivery/incoterm responsibility, minimum order, lead time and capacity reservation.
- Data privacy, confidentiality, IP, traceability and audit requirements.
- Environmental, chemical, social/labour and site-compliance evidence relevant to the supplied material/process.
- Qualification status, category scope, site scope, expiration and corrective-action history.

One legal supplier may have multiple delivery sites and bank accounts. Payment beneficiary changes require independent verification and maker-checker approval. A contact is not a supplier legal entity. A reseller/customer is not automatically a procurement supplier, even if the same group has separate legal relationships.

### 3.2 Qualification and review

Supplier lifecycle: PROSPECT → DUE_DILIGENCE → TECHNICAL_REVIEW → TRIAL/APPROVAL → QUALIFIED → CONDITIONAL → SUSPENDED → EXPIRED/REJECTED.

Qualification is scoped by product/material/process/site and date. A supplier qualified to supply woven cotton fabric is not automatically qualified for chemical dyeing, children’s wear accessories or wet-process subcontracting.

Review may require:
- Material specification, test reports and batch certificate.
- Quality system and inspection performance.
- Capacity, lead time and contingency.
- Factory/site audit, labour and HSE evidence.
- Environmental permit/effluent/waste evidence for regulated processes.
- Restricted substance and chemical evidence (e.g. applicable SDS, test or declaration).
- IP, artwork and confidentiality controls.
- Traceability and recall capability.
- Financial stability and country/payment risks.

A supplier can be conditionally approved for a bounded trial quantity/date with named mitigations. Expiry or serious nonconformance blocks new awards; open POs are reviewed, not deleted.

## 4. Requisition, sourcing and purchase authorization

### 4.1 Demand sources

Requisitions can originate from:
- Approved PLM BOM and sample requests.
- Material requirements planning against production schedule/route.
- Approved inventory reorder/min-max policy.
- Maintenance/spares and facilities demand.
- Authorized subcontract service requirement.
- Approved office/indirect procurement.
- Customer project/contract obligation.

Forecast and OTB are planning inputs. A requisition is still a request; a purchase order is a binding commitment only after delegated approval.

### 4.2 Sourcing workflow

1. Requester selects legal entity, delivery site, cost centre, project/product and required date.
2. Validate approved budget/OTB, material spec/revision, demand quantity, available stock, reservations, open POs and in-transit supply.
3. Determine sourcing route using tenant policy and legal applicability (private procurement, public procurement, donor/customer terms or emergency exception).
4. Issue RFQ/quote request to qualified suppliers; record scope, spec, quantity, delivery, validity, currency and response.
5. Evaluate technical compliance separately from commercial score. Weighting is approved policy, not hard-coded.
6. Capture comparative bid, total landed cost, MOQ, lead-time/capacity, quality risk, supplier concentration, payment terms and conflicts.
7. Record recommendation and approver. Requester/buyer/receiver/AP/payer roles are segregated according to risk/delegation.
8. Create award and contract/PO with version, line schedules, price, currency, tax, delivery and authorized exceptions.
9. Supplier acknowledgment and change order are recorded; amendment requires approval and retains old version.

No fabricated universal procurement thresholds or public tender process is imposed on private companies. Legal coverage is determined by entity status and transaction. Internal competition, conflict declaration, budget check, maker-checker and 3-way match remain configurable enterprise controls.

## 5. Purchase order and contract control

PO header records tenant/legal entity, supplier/site, currency, terms, delivery address/site, incoterm/responsibility where applicable, payment schedule, buyer, approval policy and version. PO lines identify item/service/material specification revision, UOM, quantity, unit price, delivery schedule, tax treatment status, quality plan, inspection requirements, tolerance and project/style/order link.

A PO supports split schedules, partial delivery, approved substitutions, cancellation balance, backorder and change orders. Quantity tolerances are policy-based and require reason/approval. Do not silently receive or invoice over quantity. A change in price, bank beneficiary, currency, delivery site, material spec or supplier changes the approved revision and may trigger a fresh approval.

Contracts preserve service terms, liability, IP/confidentiality, material custody, rejection/rework, audit and dispute clauses. The PO is a release against the contract, not a replacement for contractual obligation.

## 6. Receiving, inspection and stock creation

Receiving workflow:
1. Schedule expected inbound ASN/vehicle/container and PO lines.
2. Verify supplier, legal entity, delivery site and PO revision.
3. Count/weigh/measure actual received quantity and packaging/handling units.
4. Record lot/batch/roll/shade/color/width/GSM/expiry/serial/certificate and supplier references as applicable.
5. Capture damage, shortage, overage, wrong item, wrong shade/label or missing documents.
6. Route materials to incoming inspection or quarantine where required.
7. QC accepts, conditionally accepts, rejects, returns to supplier or holds pending lab test.
8. Accepted quantities create stock ledger receipts in the appropriate inventory location; rejected/quarantined quantities remain separately identifiable and unavailable for production.
9. Reconcile expected vs received vs accepted and remaining PO commitment.
10. Notify procurement, supplier quality, AP and production planning of material shortage or release.

Inventory balance is written only by the WMS/Inventory posting transaction, not directly by the procurement UI. The receiving process sends an approved receipt command with a unique idempotency key. Duplicate scans/retries cannot create duplicate stock.

## 7. Material traceability by family

### 7.1 Fabric
Track fiber/content, construction, weave/knit, finish, colour/dye lot, supplier lot, shade group, roll code, usable width, length, weight/GSM, defects, shrinkage, test result, certificate, ownership, storage condition and stock location. Roll split, re-roll, remnant and transfer events preserve parent roll lineage, length/weight balance and shade. Never merge rolls or shade lots without an authorized compatibility decision.

### 7.2 Trims and accessories
Separate trim/accessory master and lot: item, brand/customer approval, colour, size, finish, material/composition, supplier lot, batch, expiry where applicable, inspection status and approved substitute. Issue/return is an independent transaction linked to BOM, order, line, bundle or garment. Fabric consumption does not imply trim issue.

### 7.3 Chemicals and controlled process inputs
Maintain product identity, manufacturer, concentration/grade, lot, expiry, hazard class, SDS revision/language, storage compatibility, PPE/handling instructions, authorized user/process, issue quantity, return, spill, waste and disposal evidence. Chemicals are not treated as generic consumable stock. Wet-process operations require current site permits and competent HSE review; a software configuration does not grant legal authority to discharge effluent or handle controlled substances.

### 7.4 Packaging, labels and components
Trace carton, polybag, hangtag, barcode, care/size label, zipper, button, thread and packaging by specification revision, customer/brand applicability, supplier lot, colour, size, certificate and approved destination. Packing specifications are versioned and later validated against carton contents in WP-09/WP-12.

### 7.5 Ownership and consignment
Track owner-of-stock (tenant, legal entity, customer, supplier, consignment party), custody holder, valuation basis, location and liability. Customer-owned material in factory custody is not necessarily the factory's inventory asset. Consigned stock and goods-in-transit remain distinct statuses.

## 8. Import/export and landed-cost workflow

Import/export handling is a governed process, not a hard-coded tariff calculator. Record:
- Supplier/exporter, importer of record, legal entity, shipment and customs regime.
- Item/HS classification candidate and qualified approval/source.
- Origin, country of shipment, quantity, value, currency, valuation basis and transaction documents.
- Incoterm/responsibility, freight, insurance, broker, port/terminal, transit and clearance.
- Customs declaration, assessment, tariff/duty, exemptions/incentive evidence and authority receipt.
- FX payment purpose, approved modality, bank, required preconditions and NBE rule version.
- Taxes/charges, freight, insurance, brokerage, handling, demurrage and allocation method.
- Warehouse arrival, receipt, shortage/damage, quality and invoice reconciliation.

Landed-cost allocation policy identifies eligible cost pools, allocation bases (quantity, weight, volume, value or specific attribution), currency conversion source/date, non-recoverable tax treatment and adjustment/correction. Each receipt cost layer references original source documents and cost allocation version.

No import payment/advance/LC/CAD or export FX action may bypass the current consolidated NBE rule applicability and authorized bank approval. If FXD amendments or customs classification is uncertain, the transaction remains review-required; the module may prepare documentation without authorizing a real payment or customs declaration.

## 9. Supplier invoice, AP and payment segregation

3-way match compares:
- Approved PO/contract and amendment.
- Actual warehouse receipt and accepted quantity/quality.
- Supplier invoice price, quantity, tax/withholding, currency and terms.

Possible results: MATCHED, PARTIAL_MATCH, PRICE_VARIANCE, QUANTITY_VARIANCE, TAX_REVIEW, DUPLICATE_SUSPECTED, HOLD. Approved tolerances are policy-versioned and cannot bypass legal/tax review. AP owns invoice liability and GL posting; inventory owns receipt and valuation movement; treasury/payment service owns payment proposals and settlement evidence.

A supplier invoice may be received before complete delivery where contract permits, but must be represented as an advance/prepayment or invoice exception, not fake receipt. A payment is released only through authorized finance controls and proof is retained.

## 10. Procurement fraud, risk and exception controls

- No requester or buyer self-approves restricted award/PO.
- Supplier bank detail change receives out-of-band/independent verification and maker-checker.
- Duplicate invoice detection uses supplier, invoice number, date, amount, currency and document fingerprint.
- Split requisition/PO pattern and repeated emergency exemptions are flagged for audit.
- Supplier quote scoring preserves original inputs and evaluator conflicts.
- Related-party supplier and conflict declarations are surfaced to independent approver.
- Receipt quantity/quality variance has named owner and claim/return process.
- A supplier cannot approve its own quality disposition.
- The system never silently swaps a material specification for an available alternative.
- Repeated quality/delivery failures affect qualification and sourcing risk but do not rewrite past supplier performance.
- Payment release is separated from receiving and AP invoice capture.

## 11. Data model

| Entity/table | Purpose / minimum fields |
|---|---|
| suppliers | tenant, legal name/form, registration/TIN/VAT/licence reference, supplier type, status |
| supplier_sites | supplier, address, site type, delivery capability, country, validity |
| supplier_contacts | site/supplier contact, role, verification and restricted data flags |
| supplier_bank_beneficiaries | supplier/entity, masked account ref, bank, currency, verified by, effective date |
| supplier_qualifications | supplier/site/category, scope, requirement, evidence, reviewer, validity, status |
| supplier_risk_assessments | risk dimension, score/rationale, reviewer, corrective action |
| sourcing_events | entity, requester, category, budget, method, scope, deadline, status |
| sourcing_invitees | event, supplier qualification, invitation, response |
| supplier_quotes | event/PO line, quote revision, quantity, price, currency, lead time, terms, attachment |
| bid_evaluations | technical result, commercial score, evaluator, conflicts, recommendation, approval |
| purchase_requisitions | entity, site, requester, source demand, budget ref, needed date, status |
| requisition_lines | item/spec revision, quantity/UOM, project/style/order, destination |
| purchase_orders | entity, supplier/site, version, currency, terms, status, contract, approver |
| purchase_order_lines | item/service, spec revision, UOM, qty, unit price, tax profile, tolerance |
| po_schedules | PO line, due date, quantity, destination site and ASN status |
| purchase_contracts | parties, scope, version, terms, IP/custody/quality/liability and signature evidence |
| goods_receipts | PO ref/version, supplier/site, delivery event, received_by, status |
| receipt_lines | PO line, actual quantity, accepted/rejected/quarantine quantity, variance, UOM |
| material_items | material class, spec, composition, UOM, storage/quality handling profile |
| material_lots | item, supplier, supplier lot, batch, origin, ownership, expiry/certificates |
| fabric_rolls | material lot, roll code, shade group, width, GSM, length/weight, parent roll, location |
| trim_lots | material lot, trim spec, colour/size, approval and balance |
| chemical_lots | material lot, SDS revision, hazard class, expiry, storage group, permission profile |
| material_inspections | lot/roll, inspection plan, defect, sample, result, disposition, inspector |
| inventory_receipt_commands | approved receipt command, idempotency key, posting result reference |
| material_issue_requests | source demand, order/line/bundle, requested lot/quantity, approval |
| material_custody_events | material/lot/handling unit, from/to custodian, quantity, reason, event time |
| import_shipments | importer, exporter, shipment/container, regime, origin, incoterm, status |
| customs_declarations | shipment, authority ref, HS, value, tariff source, assessment, clearance |
| landed_cost_batches | shipment/receipt, cost pool, currency/rate source, allocation method, posting status |
| supplier_invoices | supplier, invoice number, date, currency, gross/tax/withholding, match status |
| invoice_match_lines | invoice line, PO line, receipt line, quantity/price/tax variance |
| supplier_claims | shortage/quality/damage, evidence, claim amount, disposition, recovery |
| procurement_approvals | request/award/PO version, approver, policy, decision and reason |

All tenant/entity/site IDs use WP-03 composite references and RLS. Financial accounts and posted journals stay in WP-13; stock movements and location balances stay in Inventory/WMS.

## 12. APIs and events

Illustrative endpoints:
- POST /api/v1/suppliers; POST /api/v1/suppliers/{id}/qualifications.
- POST /api/v1/purchase-requisitions; POST /api/v1/sourcing-events; POST /api/v1/sourcing-events/{id}/awards.
- POST /api/v1/purchase-orders; POST /api/v1/purchase-orders/{id}/change-orders.
- POST /api/v1/asn; POST /api/v1/goods-receipts; POST /api/v1/material-inspections.
- POST /api/v1/material-issue-requests; POST /api/v1/import-shipments; POST /api/v1/landed-cost-batches.
- POST /api/v1/supplier-invoices; POST /api/v1/supplier-invoices/{id}/match.

Events: SupplierQualified.v1, PurchaseRequisitionApproved.v1, SourcingAwardApproved.v1, PurchaseOrderReleased.v1, GoodsReceiptAccepted.v1, MaterialLotReleased.v1, MaterialShortageDetected.v1, SupplierInvoiceMatched.v1, LandedCostAllocated.v1.

## 13. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-SRC-001 | Supplier applies for one material/process category | Qualification scoped; no blanket vendor approval |
| AT-SRC-002 | Buyer is also requester on a restricted award | Maker-checker rejects self-approval |
| AT-SRC-003 | Requisition covers approved BOM demand and available stock | Net shortage calculated; open PO/in-transit not double counted |
| AT-SRC-004 | PO arrives in two receipts | Partial quantities reconcile against authorized balance |
| AT-SRC-005 | Fabric lot has multiple rolls and roll split/remnant | Shade and parent-roll lineage/balance retained |
| AT-SRC-006 | Trim is issued to one line and returned | Separate trim ledger reconciles by lot/BOM |
| AT-SRC-007 | Chemical lot lacks valid SDS or is expired | Quarantined; not available for wet process |
| AT-SRC-008 | Supplier delivers wrong spec/shade | Material remains quarantined/rejected; production not allocated |
| AT-SRC-009 | Duplicate receipt API retry occurs | One inventory receipt effect only |
| AT-SRC-010 | Invoice exceeds receipt or PO price tolerance | AP match held; no duplicate stock posting |
| AT-SRC-011 | Supplier bank account is changed | Independent verification and approval required |
| AT-SRC-012 | FX modality or HS classification is uncertain | Review hold; no real payment/declaration authorization |
| AT-SRC-013 | Landed cost has mixed freight/insurance/duty | Allocation basis and source rates are reproducible |
| AT-SRC-014 | Subcontract processor returns a short bundle/lot quantity | Custody variance and quality/claim case created |
| AT-SRC-015 | Public entity and private entity use procurement | Legal procurement profile is enabled only where statute applies |

## 14. WP-07 handover

**Completed:** supplier identity/qualification, sourcing, requisition-to-PO, contract, receiving, material family traceability, custody, import/FX/customs evidence, landed cost, invoice 3-way match, fraud controls, data dictionary, API/events and 15 acceptance scenarios.

**Not claimed:** no supplier due diligence was executed, no customer supplier/price list exists, no customs classification or FX payment was approved, no stock transaction/AP invoice/payment or supplier integration is implemented by this specification.

**Dependencies:** WP-03 entity/site/security; WP-04 demand/range and OTB; WP-05 BOM/spec revision; WP-06 method/capacity; WP-08 route requirements; WP-09 separate material issue; WP-11 incoming QC/HSE; WP-13 AP/FX/tax/landed cost.

**Decision:** internally accepted under delegated execution. Proceed to WP-08 — Department SOP and Process Route Framework.
