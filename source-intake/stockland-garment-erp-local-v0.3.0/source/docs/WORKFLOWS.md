# End-to-end business workflows

**Document ID:** STK-WF-001  
**Application version:** 0.3.0

## 1. Company setup and identity

`EMPTY DATABASE → FIRST OWNER → FIVE LOCATIONS → USER/ROLE ASSIGNMENT → AUTHENTICATED WORKSPACE`

The first Owner is provisioned once. The standard locations are Central Warehouse, Goro, Gerji, Mexico and Megenagna. Operational roles receive an assigned location and are restricted by server-side scope.

## 2. Product master

`SKU INPUT → FIELD VALIDATION → UNIQUE SKU/BARCODE CONSTRAINT → ACTIVE PRODUCT RECORD`

The saleable SKU stores brand, product name, category, department, age group, size, colour, barcode, unit, cost, retail and wholesale prices. Product creation defines identity only; stock enters through a posted movement.

## 3. Procurement and goods receiving

`SUPPLIER → PURCHASE ORDER → ORDER LINES → PARTIAL/FULL RECEIPT → STOCK MOVEMENTS → SUPPLIER BILL → GL JOURNAL`

Purchase order quantity and unit cost are captured by SKU. Receiving creates a goods receipt, increases inventory and updates received quantities. The transaction creates a supplier bill and balanced journal. An order moves from `ordered` to `part_received` and then `received` as quantities are completed.

## 4. Direct receipt and stock transfer

`RECEIPT INPUT → SKU/LOCATION CHECK → STOCK RECEIPT → LEDGER + JOURNAL + AUDIT`

`TRANSFER REQUEST → SOURCE AVAILABLE CHECK → TRANSFER_OUT + TRANSFER_IN → AUDIT`

Stock transfer writes both locations in one transaction. Source shortages reject the full transfer. Manual adjustments require a reason and create the corresponding stock and accounting records.

## 5. Requisition fulfilment

`REQUEST → SUBMITTED → APPROVED → FULFILLED`

A requisition identifies a source and destination location and one or more SKUs. Role scope determines who can request, approve and fulfil. Fulfilment checks source availability and posts paired movement legs.

## 6. Reservation

`CUSTOMER HOLD → AVAILABLE STOCK CHECK → ACTIVE RESERVATION → RELEASE OR FULFILMENT`

Active, unexpired reservations are subtracted from available quantity during POS and reservation validation. Releasing or fulfilling a reservation updates its state and audit history.

## 7. Physical stock count

`COUNT WORKSHEET → EXPECTED VS COUNTED → VARIANCE CALCULATION → RELEASE → MOVEMENT + JOURNAL`

Counts may be keyed through the UI or loaded from the Excel-compatible CSV worksheet. The system stores expected, counted and variance quantities. Release compares the count to the current ledger, posts a signed adjustment and writes the accounting impact.

## 8. Retail POS

`CART → SERVER SKU/PRICE RESOLUTION → RESERVED/ON-HAND VALIDATION → SALE + LINES + STOCK + JOURNAL + AUDIT`

The server resolves the retail price from Product Master. It aggregates requested quantity by SKU, respects active reservations, creates sale lines and inventory deductions and posts revenue/tender plus cost-of-goods-sold journals. A stable idempotency key returns the original sale on retry.

## 9. POS file import and final release

`UPLOAD → AUTO-VALIDATE → STAGE → RECONCILE → BRANCH MANAGER RELEASE → AUTO-POST`

The import records all rows, validation issues and the control total without changing final sales or inventory. Exact reconciliation is required. The assigned Branch Manager provides the final and sole business release; automatic posting is atomic and idempotent.

## 10. Returns and damage

Returns are tied to original sale line IDs and cumulative returned quantity cannot exceed the quantity sold. Refund method determines the settlement account; returned items restore stock and reverse the related revenue/cost effects. Damage reports identify location, reason and SKU quantities, reduce inventory and post shrinkage/damage accounting.

## 11. Wholesale

`CUSTOMER → WHOLESALE DRAFT → CONFIRMATION → STOCK DEDUCTION → REVENUE/COGS → AR (CREDIT SALES)`

Wholesale price defaults from Product Master; an authorized order can specify line price. Confirmation checks stock and posts the inventory and accounting effect. Credit orders create an accounts-receivable invoice.

## 12. Finance and accounting

Expenses, supplier bills, customer invoices, payments and manual journals post double-entry records. The trial balance aggregates posted journal lines. Each journal must balance exactly before posting. AP and AR payment allocation cannot exceed the open document balance.

## 13. HR and payroll

Employee and contract records establish the workforce master. Attendance is recorded by date and employee. Payroll calculates gross pay, statutory and other deductions, then creates a draft payroll run. Authorized posting writes the payroll journal and closes the run.

## 14. Daily close

A branch closing records opening cash, expected tender totals, counted cash and variance. The assigned Branch Manager or authorized company role closes the submitted record. Closing status and variance remain in the operational audit trail.
