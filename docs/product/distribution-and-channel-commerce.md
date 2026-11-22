# Distribution Network, Order Management and Channel Commerce

## 1. Product requirement

The ERP must operate the complete commercial and physical distribution lifecycle after manufacturing. Finished goods must be saleable and fulfilable through multiple routes:

- The company's central distribution centre (DC) and regional DCs/depot network.
- The company's own branches, stores, outlets and retail POS.
- Local wholesale accounts buying in bulk.
- Independent retailers buying for resale.
- Distributors, dealers, agents and resellers.
- Direct customers and consumers.
- E-commerce, B2B portals, EDI/API partners and marketplace channels.
- Approved third-party logistics (3PL), drop-ship and fulfilment partners.

Manufacturing is one source of supply, not the boundary of the product. A tenant may manufacture, buy finished products from other suppliers, distribute third-party brands, or combine all three models.

## 2. Separate organizational, commercial and inventory concepts

Do not collapse these concepts into one generic "branch" or "warehouse" table.

| Concept | Meaning | Examples |
|---|---|---|
| Tenant | SaaS customer/security boundary | A garment group |
| Legal entity | Registered company and accounting boundary | Manufacturing company, trading company |
| Business unit | Management responsibility | Apparel, footwear, accessories |
| Branch | Organizational/commercial operating unit | Addis Ababa sales branch |
| Distribution node | Physical/logical network point | Central DC, regional depot, cross-dock |
| Inventory location | Exact stock-holding or handling location | Warehouse, zone, rack, bin, store backroom |
| Retail store | Customer-facing selling location | Company-owned shop or outlet |
| Channel | Route to market | Wholesale, retailer, reseller, own retail, D2C |
| Customer/account | Commercial counterparty | Wholesaler, retailer, reseller, consumer |
| Fulfilment partner | External logistics or selling partner | 3PL, carrier, marketplace |

One branch may operate several stock locations and stores. One DC may serve multiple branches and channels. A retail store may also be a fulfilment node. The data model must represent these relationships without duplicating physical stock.

## 3. Network and inventory model

Every stock balance must be traceable by tenant, legal owner, SKU/variant, location, inventory status, unit of measure and applicable lot/serial/batch attributes.

Separate at least:
- **Owner:** the legal party that owns the goods.
- **Custodian:** the organization or 3PL physically holding the goods.
- **Location:** where the goods are currently recorded.
- **Status:** whether they are available, reserved, quarantined, damaged, in transit or otherwise restricted.
- **Commercial allocation:** which order, customer, channel or policy has reserved the goods.

Support company-owned, consignment, customer-owned, supplier-owned and third-party stock. Inventory visibility does not automatically imply ownership or availability for sale.

The inventory movement ledger is the source of truth for stock quantity and location changes. Posted movements are immutable; corrections use controlled reversal or adjustment entries. Every movement references its source document and records actor, timestamp, reason, origin and destination. Financial valuation and ownership changes must reconcile with the general ledger.

### Core node types
- Factory raw-material warehouse and production/WIP store.
- Factory finished-goods warehouse.
- Central finished-goods distribution centre.
- Regional distribution centre and wholesale depot.
- Cross-dock and transit/staging point.
- Branch stockroom and company-owned retail store.
- Outlet/clearance store.
- Returns and quality-inspection hub.
- Third-party logistics warehouse or marketplace fulfilment location.

## 4. Unified order management

All sales sources must enter a common order-management service: account managers, sales representatives, B2B customer portal, EDI/API, retail POS, e-commerce, marketplaces and approved manual entry.

A common order lifecycle should support these states (exact state names may be refined during implementation):

Draft → Submitted → Validated → Credit/Payment Review → Approved → Sourced/Allocated → Reserved → Picking → Packed → Dispatched → Delivered → Invoiced → Settled

Controlled exception states include On Hold, Partially Allocated, Backordered, Partially Shipped, Cancelled, Delivery Failed, Returned and Disputed. Do not force every order through every state: POS cash sales, credit sales, wholesale orders and marketplace orders have different payment and fulfilment paths.

The order must retain:
- Customer PO and ERP sales order identifiers.
- Ordering account, bill-to, ship-to and delivery contact.
- Selling channel, branch, sales representative and territory.
- Legal entity, currency, tax treatment and payment/credit terms.
- Requested delivery date, service level and delivery instructions.
- SKU/style/variant, size, colour, quantity, unit and agreed price.
- Discounts, promotions, rebates, commissions, freight and other charges.
- Inventory source, reservations, shipment(s), proof of delivery, invoice and payment links.

Use idempotency for order imports and all retryable posting operations. Duplicate external order IDs must be detected within the relevant tenant/channel/integration.

## 5. Available-to-promise and sourcing

ATP must not be a simple global on-hand quantity. It must calculate saleable supply by fulfilment node, date and policy:

ATP = eligible on-hand − existing reservations − protected safety stock + eligible incoming supply

The implementation must also apply channel allocation, inventory status, customer commitments, lead time, cut-off, capacity, legal ownership and delivery restrictions. Planned production may feed CTP only when its dates, material readiness and capacity confidence are explicit.

The sourcing policy can rank nodes by:
1. Approved customer/territory source.
2. Channel and product eligibility.
3. Available stock and existing commitments.
4. Promised delivery date and service level.
5. Distance, freight and fulfilment cost.
6. Node capacity and operating calendar.

Allow split fulfilment only when policy permits it and the customer promise remains clear. Keep backorders, substitutions, partial delivery and cancellation explicit and auditable.

## 6. Central DC, regional depot and branch replenishment

Support periodic, demand-driven and exception-driven replenishment. Inputs may include forecast demand, point-of-sale sell-through, open orders, safety stock, minimum/maximum, reorder point, days of cover, seasonality, promotions, supplier lead time and stock already in transit.

A standard transfer lifecycle is:

Proposed → Reviewed → Approved → Reserved → Picking → Dispatched → In Transit → Partially Received/Received → Reconciled → Closed

The transfer must preserve source node, destination node, owner, items, lots, quantities, shipment, expected arrival, actual receipt, shortages/overages, damage and reason codes. Dispatch decreases source available stock and creates in-transit stock; receipt moves the confirmed quantity into the destination. Never silently increase destination stock at dispatch.

Transfers within one legal entity are internal stock movements. Transfers between legal entities may require intercompany sales/purchase documents, transfer pricing, tax and settlement. Those rules must be effective-dated and approved for the applicable jurisdiction.

Branch/store replenishment should generate proposals and allow governed manual overrides. Scarce inventory allocation across wholesale commitments, key retail accounts, resellers, own stores and direct channels must follow transparent, approved priority rules.

## 7. Channel-specific commercial operations

### Wholesale
- Account hierarchy, buyer contacts, territories, credit limits, credit holds, negotiated price agreements and payment terms.
- Customer purchase orders, case packs, minimum order quantities, order multiples, bulk allocation and scheduled/blanket orders.
- Sales quotations, order confirmation, backorder management, bulk picking, pallet/carton labels, delivery appointments and customer claims.
- Rebates, volume tiers, sales commissions, freight agreements, credit notes and account statements.

### Retailer and reseller networks
- Separate account types and partner profiles while allowing one party to have multiple commercial roles.
- Partner-specific assortments, price tiers, wholesale discounts, resale terms, territories, incentives, returns allowances and credit policies.
- Dealer/reseller sell-in reporting; optionally ingest sell-through/stock reports when partners provide them.
- Partner portal for catalog, availability, orders, invoices, statements, delivery status and returns.

### Company-owned retail and outlets
- Store assortment, size/colour availability, store-specific price zones, promotions, markdown approvals and stock transfers.
- POS registers, cashier shifts, float management, tender types, receipts, refunds, exchanges, sales returns and end-of-day cash/tender reconciliation.
- Retail sales must update stock and finance exactly once, including during offline operation and later synchronization where the tenant enables it.
- Store backroom-to-shelf movements, counts, shrinkage, damages, stock ageing and replenishment.

### Direct and digital channels
- D2C orders, e-commerce and marketplace connector contracts, catalog/price synchronization and order imports.
- Payment authorization/capture/refund integration, fraud/risk holds where configured, delivery promise and customer notifications.
- Click-and-collect, ship-from-store, home delivery and partner fulfilment where enabled.
- Channel fees, payment fees, commissions, promotions, returns and settlement reconciliation.

## 8. Delivery, returns and reverse logistics

Plan loads, carriers, routes, stops, delivery windows and 3PL hand-offs. Track shipment milestones and proof of delivery. Reconcile dispatched quantity to received quantity and make failed delivery, refusal, loss, damage and shortage visible.

Use a return merchandise authorization (RMA) lifecycle linked to the original order and shipment. On receipt, inspect and grade returned goods, then route them to:
- Available stock after approved inspection.
- Quarantine pending investigation.
- Repair/rework.
- Supplier or carrier claim.
- Outlet/markdown stock.
- Scrap or controlled disposal.

Refunds, exchanges, replacement orders and credit notes must be linked to the sale and posted to inventory and finance using governed workflows. Wholesale claims, retailer claims, reseller returns, inter-branch returns and consumer returns may have different policies.

## 9. Pricing, credit and financial integration

Pricing is a governed service, not a hard-coded channel flag. The resolved price must be explainable by its source: contract, customer agreement, channel price list, store price zone, promotion, quantity tier, currency, effective date and approval override.

The commercial engine must support gross/net price, discounts, tax, freight, rebates, commissions, payment fees, credit notes and returns. Use decimal arithmetic and effective-dated configuration. Do not hard-code Ethiopia tax, withholding or other statutory rates; use the localization approval process.

Sales invoices, receivables, receipts, refunds, settlement batches, inventory cost of goods sold, freight, discounts and channel fees must post to finance through controlled, idempotent integration. Preserve legal-entity boundaries and intercompany accounting for cross-company stock flows.

## 10. Minimum operational dashboards

Provide role-scoped dashboards for:
- Group distribution control tower: stock across all nodes, orders at risk, transfers in transit, late deliveries and allocation conflicts.
- DC operations: inbound, put-away, picking productivity, fill rate, dock/loading plan, stock accuracy and ageing.
- Branch/store operations: sell-through, available sizes/colours, replenishment due, stock variance, markdown and shrinkage.
- Wholesale and key accounts: open orders, fill rate, credit exposure, overdue balances, claims and account margin.
- Retail: sales by store/SKU/size/colour, basket, returns, cash variance, promotion performance and stock cover.
- Transport/customer service: on-time delivery, failed delivery, proof-of-delivery gaps, claims and return cycle time.
- Executive: channel revenue and contribution margin, working capital, inventory turns, returns, freight and service performance.

All dashboards must drill down to the source transaction and obey tenant, legal-entity, branch, role and data-scope permissions.

## 11. Acceptance criteria before this domain is called complete

1. A finished-goods receipt can be put away at a central DC and is visible with correct ownership, status, quantity and valuation.
2. The DC can fulfil a wholesale order and produce linked shipment, delivery evidence, invoice and receivable.
3. A DC can replenish a regional depot, branch and own retail store with in-transit and receipt reconciliation.
4. A retailer and a reseller can order under distinct account/channel terms and receive their correct price, credit and fulfilment policy.
5. A store can sell through POS, reconcile tender, update inventory and post finance once.
6. A direct customer can order through an integration and receive delivery status and a linked invoice/payment record.
7. ATP prevents sale of unavailable, quarantined, already-reserved or wrong-owner stock.
8. Split shipments, partial receipts, shortages, damages, returns, refunds and credit notes remain traceable and auditable.
9. Cross-legal-entity transfers produce the configured intercompany documents and postings.
10. Tenant isolation, role/branch scope, audit, idempotency, concurrency and inventory/financial reconciliation tests pass.
