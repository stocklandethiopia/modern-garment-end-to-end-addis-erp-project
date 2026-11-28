# WP-12 — Warehouse, Distribution, Order Management and Channel Commerce

**Package:** WP-12  
**Status:** DESIGN COMPLETED — transactional implementation and channel adapters follow  
**Scope:** Approved distribution/channel charter and Domains F/G. Connects inventory custody, manufacturing output, order fulfilment, wholesale, reseller, own retail, POS, D2C, marketplace, delivery and returns.

## 1. Purpose and key model distinctions

A garment enterprise may distribute through its own stores, central/regional distribution centres, branches, wholesale customers, independent retailers, resellers, marketplaces, D2C, corporate buyers and export customers. The ERP models each commercial and physical role separately.

Never assume:
- Branch = warehouse.
- Store address = inventory location.
- Customer account = consumer identity.
- Sales channel = legal entity.
- Warehouse transfer = sale.
- POS sale = cash settlement.
- Reseller rebate = employee commission.
- Shipment = delivery.
- Return = automatically saleable stock.
- Available inventory = physically unrestricted inventory.

Inventory is owned by the Inventory/WMS context; order pricing and fulfilment are owned by Commerce; AR/GL/cash posting are owned by Finance; physical execution generates source events and approved commands. One fact is not posted twice because it appears in two modules.

## 2. Network topology

Network node types include:
- Factory / production site.
- Raw-material store and WIP supermarket.
- Central DC, regional DC, local depot and wholesale depot.
- Retail branch, company-owned store, outlet or concession.
- E-commerce fulfilment centre.
- Third-party 3PL/warehouse/fulfilment partner.
- Customer delivery location, cross-dock or transit node.

Each node has legal owner/entity, address/site, capability, service area, capacity/calendar, operating hours, stock-holding permission and active period. Inventory locations are explicit zones/bins within stock-holding nodes. A branch can be a registered commercial branch with no stock; a retail store can have an inventory location only when explicitly configured.

## 3. Inventory ledger and status model

All stock movement is append-only and traceable:
- Receipt / supplier receipt.
- Production output receipt.
- Issue to production/department.
- Return from production/subcontract.
- Transfer out / in-transit / receipt.
- Reservation / unreserve.
- Pick / pack / ship / delivered.
- Customer return / inspection / disposition.
- Adjustment / count variance / scrap / write-off / consignment.
- Ownership or custody change.

Every movement includes tenant, legal entity, item/SKU/material, UOM, quantity, from/to location, owner, stock status, handling unit/lot/roll/bundle/carton, source document, event time, actor, reason, idempotency key and correlation ID.

Stock statuses include AVAILABLE, RESERVED, ALLOCATED, PICKED, PACKED, IN_TRANSIT, QUARANTINE, QUALITY_HOLD, DAMAGED, EXPIRED, CUSTOMER_OWNED, SUPPLIER_CONSIGNED, SCRAP and BLOCKED. The exact set is tenant configurable only as additive policy-safe dimensions; status transitions are controlled.

Inventory balance is a projection of ledger movements. Do not update balance directly. A receipt or issue command must be authorized and transactional; duplicate event retry creates no second movement. Negative inventory is blocked by default; controlled backflush/negative policy requires tenant configuration and reconciliation, never hidden negative stock.

## 4. Handling unit and product genealogy

Handling units support:
- Fabric rolls and remnants.
- Trim/accessory/chemical lots.
- Panels and cut bundles.
- WIP containers/trolleys.
- Individual serialized item where required.
- Cartons, cases, pallets, containers and shipments.

Split, merge, repack, relabel and consolidation events preserve parent/child genealogy and quantity/UOM. Every finished carton can be traced back to garment/bundle, production order, style revision, fabric roll/shade, trim/chemical lots, process route and QC inspection. Reverse trace from defect/supplier lot to affected orders/cartons/customers must be possible.

## 5. Available-to-promise and order promise

Available-to-promise is a decision projection, not the inventory ledger. It considers:
- Physical on-hand in allowed status/location.
- Existing reservations/allocations.
- Confirmed incoming receipts and production output.
- Quality/quarantine/expiry/ownership restrictions.
- Pick/pack and transfer lead time.
- Store/DC/channel fulfilment capability.
- Customer/account credit and order terms.
- Channel oversell/backorder policy.
- Cutoff dates, delivery service and allocation priority.

A promise identifies stock source, expected fulfilment node, expected date, assumptions and confidence. A change in availability generates an exception/re-promise rather than silently changing a confirmed delivery date. Forecasted production is not ATP unless policy explicitly supports a future promise with a clearly marked risk.

## 6. Order management and fulfilment

Unified order header with channel-specific detail:
1. Capture order from customer, wholesale portal, POS, D2C, marketplace, B2B contract or store replenishment.
2. Validate account, selling legal entity, activity/licence, channel, customer terms, price/tax profile and authorization.
3. Validate SKU/style revision, quantity, unit, assortment restrictions and order date.
4. Credit/anti-fraud/payment control as appropriate.
5. ATP/source selection and inventory reservation or make-to-order demand.
6. Confirm order and promise date; notify exceptions.
7. Release warehouse waves/pick tasks or production demand based on fulfilment model.
8. Pick, scan, pack, cartonize and verify quality/label/quantity.
9. Dispatch, carrier handoff, tracking and proof of delivery.
10. Finance posts invoice/AR/receipt/refund/credit according to approved trigger.
11. Return, exchange, repair or complaint creates a linked reverse-flow case.

An order can contain partial fulfilment, split shipment, backorder, substitution only under approved product/customer policy, cancellation and return. Each line tracks requested/confirmed/allocated/picked/shipped/delivered/invoiced/returned quantity distinctly.

## 7. Wholesale, retail, reseller and digital channel rules

### Wholesale
- Account onboarding, legal/customer identity, contract, territory, price list, credit terms, minimum order, delivery terms and return policy.
- Sales order, allocation, pick/pack/dispatch, invoice, receivable and collection.
- Credit hold and overdue/dispute review.
- Bulk assortment, pack ratio, size curve, price tier, rebate and sell-through support.
- Customer-specific labelling/packing and shipment documentation.

### Independent retailer/reseller network
- Reseller/dealer agreement, approved territory, brand protection and pricing policy.
- Tier pricing, discount and volume rebate.
- Eligibility/claims, proof of sell-in/sell-through or campaign.
- Separate customer receivable/rebate/credit note or AP service payment where appropriate.
- No automatic employee payroll classification.
- Monitor channel conflict, returns, counterfeit/diversion complaints and market coverage.

### Company-owned retail and outlets
- Store assortment, stock transfer/replenishment, POS, cash drawer, payment tenders, refunds/voids, discounts, daily close and shrinkage.
- Store staff sales commission (if approved) is a separate WP-14 compensation event.
- Retail legal activity/site permission is validated; a manufacturing licence does not automatically authorize retail.
- Store can hold stock only when explicit inventory location exists and is licensed/authorized.

### D2C/e-commerce and marketplace
- Web/catalog integration, variant availability, promotions, customer consent/privacy, checkout/payment, order acceptance, fraud checks, fulfilment, delivery, returns/refunds and support.
- Payment status and inventory reservation are separate; an authorization is not settlement.
- Marketplace order/fees/commission/settlement are reconciled by external transaction ID.
- Webhook signature verification, replay protection, idempotency and dead-letter reconciliation required.
- Price/assortment/stock feed is channel-scoped and respects legal entity/channel permission.

### Corporate, uniform and export accounts
- Contract, specification, named delivery locations, size breakdown, milestone shipment and acceptance.
- Export documentation, customs, origin and trade/FX controls are handled by WP-07/WP-13, not hard-coded in order module.
- Customer-owned goods and consignment are separate ownership states.

## 8. Replenishment and network planning

Planning policies can include min/max, reorder point, days of supply, service level, sales velocity, season/end-of-life, demand forecast, transfer lead time and store/DC capacity. Suggest:
- Store-to-DC and DC-to-DC transfer.
- Factory-to-DC finished goods transfer.
- Inter-branch balancing.
- Wholesale allocation vs own-store priority under declared policy.
- Seasonal assortment redistribution/markdown risk.

Recommendation explains units, demand horizon, stockout/overstock, transfer cost, margin and delivery risk. Planner authorizes transfer. Goods remain IN_TRANSIT until receiving node confirms quantity; dispatch does not silently increase destination stock.

## 9. POS, cash and settlement

POS process:
1. Authenticate cashier and open till/cash session with opening float.
2. Scan/lookup valid SKU and price list/promotion.
3. Validate store/site/legal entity, tax invoice requirements and inventory status.
4. Record sale lines, discounts, taxes and payment tender split.
5. Reserve/decrement stock through a single inventory movement command.
6. Capture receipt/invoice number and payment authorization/transaction references.
7. Process void/refund/exchange with independent authorization and reason.
8. Cashier closes session and reconciles cash/card/mobile/digital tenders.
9. Supervisor reviews variance and submits close.
10. Finance reconciles settlement and GL/AR.

Offline POS is a controlled future capability: local store cache can accept only preauthorized limited actions, use unique device/sequence IDs and bounded price/stock/credit rules; sync conflict is reconciled centrally. No offline action may bypass statutory invoice numbering or create unreconciled stock/cash. Offline capability remains disabled until legal, device security and reconciliation design is tested.

## 10. Delivery, proof, return and reverse logistics

Shipment links cartons/handling units, carrier/service, route, promised and actual milestones, export/customs docs, insurance and proof of delivery. Carrier events are idempotent and normalized with original source payload/hash.

Return lifecycle: REQUESTED → AUTHORIZED → IN_TRANSIT → RECEIVED → INSPECTED → DISPOSITIONED → CREDIT/REFUND/EXCHANGE/REPAIR/CLOSED.

Return inspection determines:
- Restock as available (only if quality/label/trace conditions pass).
- Quarantine for QC/repair.
- Return to supplier.
- Repair/rework.
- Scrap/write-off under authorized approval.
- Customer-owned/consignment return to owner.
- Fraud/dispute investigation.

A refund/credit is a Finance action linked to original invoice/payment. Physical return inspection and stock disposition are separate from financial credit. Return reasons (size/fit, quality, wrong item, delivery, customer preference, counterfeit concern) feed WP-04 product/range improvement.

## 11. Data model

| Entity/table | Purpose / key fields |
|---|---|
| network_nodes | tenant/entity/site, node type, capabilities, address, service area, stock permission |
| inventory_locations | node, code, zone/bin, storage rules, capacity, status |
| node_relationships | source/destination, relation, effective dates and permitted movements |
| item_masters / sku_masters | material/product item, UOM, tracking policy, variant, lifecycle |
| stock_ledger_entries | source transaction, item, owner, from/to, status, lot/handling unit, qty/UOM, actor, timestamp |
| stock_balances | derived projection by tenant/entity/location/item/status/lot, version/last event |
| handling_units | roll/bundle/pallet/carton ID, parent, type, seal, content summary, current location |
| handling_unit_contents | parent handling unit, contained child/item, quantity/UOM, genealogy |
| inventory_reservations | order/line, item, node/location, qty, status, promise and expiry |
| inventory_transfers | source/destination nodes, approval, shipment, in-transit receipt and variance |
| replenishment_policies | node/item, min/max, reorder, horizon, channel priority and version |
| replenishment_suggestions | demand forecast, current stock, recommended source/qty and approval |
| customer_accounts | contracting party, legal identity, account class, credit and terms |
| channel_accounts | account, channel, territory, agreement, tier pricing and status |
| sales_orders / sales_order_lines | channel, legal entity, account, price/tax, SKU, requested/confirmed quantities, state |
| allocations | order line, source node, qty, rule, reservation and date |
| pick_waves / pick_tasks | node, priority, route, picker, handling unit, scan and result |
| pack_orders / pack_lines | order, carton plan, scanned contents, labels, seal and dimensions |
| shipments / shipment_lines | carrier, service, source/destination, cartons, tracking and status |
| carrier_events | external event ID, milestone, source payload hash, time, normalized outcome |
| delivery_proofs | shipment, recipient/authorized evidence, time, location, signature/photo ref |
| pos_stores / pos_devices | site, entity, terminal/device, cashier and permitted offline profile |
| pos_sessions | till, opening float, cashier, open/close, cash variance and review |
| pos_transactions / tenders | receipt, SKU/quantity/price/tax/discount and tender/settlement refs |
| digital_orders / marketplace_orders | provider/order ID, webhook receipt, fees, payment and fulfilment refs |
| payment_intents | provider, external reference, amount/currency, authorization and settlement state |
| return_authorizations / return_lines | original sale/order, reason, item/qty, expected resolution |
| return_inspections / dispositions | condition, lot, evidence, restock/repair/scrap/return decision |
| channel_price_books / rebates | account/channel price tier, rebate rule, eligibility, claim and credit reference |

## 12. APIs, events and controls

Illustrative endpoints:
- POST /api/v1/network-nodes; POST /api/v1/inventory-locations.
- POST /api/v1/inventory/receipts; POST /api/v1/inventory/issues; POST /api/v1/inventory/transfers.
- GET /api/v1/inventory/availability; POST /api/v1/orders; POST /api/v1/orders/{id}/confirm.
- POST /api/v1/orders/{id}/allocate; POST /api/v1/fulfilment/pick-waves; POST /api/v1/shipments.
- POST /api/v1/pos/sessions; POST /api/v1/pos/transactions; POST /api/v1/returns.
- POST /api/v1/carrier/webhooks; POST /api/v1/marketplace/webhooks.

Events: StockMovementPosted.v1, InventoryReserved.v1, ATPPromiseCreated.v1, SalesOrderConfirmed.v1, PickCompleted.v1, CartonSealed.v1, ShipmentDispatched.v1, DeliveryConfirmed.v1, POSTransactionRecorded.v1, ReturnReceived.v1 and ReturnDispositioned.v1.

Controls:
- Stock ledger is immutable; balance is rebuilt/reconciled projection.
- Order and payment are distinct states; provider webhooks are verified and idempotent.
- Shipment is not proof of delivery; proof has source/actor/time.
- Financial posting uses source transaction ID and outbox idempotency.
- Any channel activation validates legal entity/activity/site permissions.
- Customer and consumer personal data use purpose/retention/access limits.
- External sellers/carriers receive scoped access only.

## 13. Decision support and KPIs

- ATP accuracy and promise-date reliability.
- Fill rate, order cycle time and on-time/in-full delivery.
- Stock accuracy and count variance by location/status.
- Inventory turns, aging, stockout, overstock and days of supply.
- DC pick accuracy, carton mismatch, dock-to-stock and dispatch SLA.
- Store sell-through, conversion, returns and shrinkage.
- Wholesale credit exposure, overdue AR and customer profitability.
- Reseller sell-through and rebate validity.
- D2C payment success, marketplace fee/settlement variance and delivery failure.
- Return reason, resale yield, repair cost and product/fit feedback.
- Transfer cost/time, in-transit aging and allocation fairness.

Every KPI declares denominator, valuation, time window, currency, channel and stock-status semantics. A dashboard must drill down to source facts.

## 14. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-DST-001 | Create branch without warehouse capability | Branch exists; no inventory location or stock balance inferred |
| AT-DST-002 | Receive stock into DC and transfer to regional depot | In-transit remains distinct until destination receipt |
| AT-DST-003 | Reserve item while quality-held or customer-owned | ATP excludes ineligible stock |
| AT-DST-004 | Two channels compete for last unit | Reservation follows declared allocation policy and is serializable |
| AT-DST-005 | Duplicate marketplace order webhook arrives | One sales order and one stock reservation |
| AT-DST-006 | POS refund is processed without returned goods inspection | Financial credit and physical stock disposition remain separate |
| AT-DST-007 | Reseller rebate is paid | Posts commercial account/credit/AP event, not employee payroll |
| AT-DST-008 | Factory licence is used to activate retail store without evidence | Retail selling held pending licensing determination |
| AT-DST-009 | Carton content changes after seal | Seal invalidated; re-open/repack event and approval recorded |
| AT-DST-010 | Shipment dispatched but delivery not confirmed | No false delivered status |
| AT-DST-011 | Customer return is received damaged | Quarantine/repair/scrap disposition; not available to sell |
| AT-DST-012 | Offline POS sync repeats a transaction ID | Duplicate tender/inventory effects rejected |
| AT-DST-013 | Customer account is over credit limit | Order held/approval workflow; no silent override |
| AT-DST-014 | Return is linked to original sale and then refunded | Original sale preserved and separate credit/payment evidence linked |
| AT-DST-015 | Multi-entity group replenishes across legal entities | Intercompany ownership/transfer and financial treatment explicitly recorded |

## 15. WP-12 handover

**Completed:** node/location distinctions, stock ledger/status, handling-unit genealogy, ATP, order/fulfilment, wholesale/reseller/retail/D2C/marketplace, replenishment, POS, delivery/proof, reverse logistics, data model/API/events/KPIs and 15 acceptance tests.

**Not claimed:** no live inventory, store, carrier, marketplace, payment gateway, POS device, offline mode, tax invoice integration or customer-specific channel licence has been provisioned or tested.

**Dependencies:** WP-03 tenant/entity/site/identity; WP-07 material/receipt; WP-09 production output/cartons; WP-11 quality hold/release; WP-13 finance/AR/AP/GL; WP-14 commissions and worker access; WP-15 integrations.

**Decision:** internally accepted under delegated execution. Proceed to WP-13 — Finance, Accounting, Treasury, Tax and Costing.
