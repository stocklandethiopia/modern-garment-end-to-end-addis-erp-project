# POS import and Branch Manager release contract

**Document ID:** STK-POS-IMP-001  
**Application version:** 0.3.0

## Mandatory state sequence

```text
UPLOAD
  ↓
AUTOMATIC VALIDATION
  ↓
STAGING (NO FINAL SALES OR STOCK POSTING)
  ↓
CONTROL-TOTAL RECONCILIATION
  ↓
BRANCH MANAGER RELEASE (FINAL AND SOLE BUSINESS APPROVAL)
  ↓
AUTOMATIC ATOMIC POSTING
  ↓
POSTED + AUDIT EVIDENCE
```

## Approval authority

The assigned Branch Manager is the final and sole business approver for a branch POS import. Uploading a file is an intake action and is not approval. Branch Manager release triggers automatic posting. The workflow has no additional Owner/Management approval after the Branch Manager action.

## CSV contract

Required columns:

```csv
branch_code,external_sale_id,sku,qty,unit_price,payment_method,sale_time
GORO_BRANCH,POS-0001,STK-001,2,750.00,CASH,2026-11-22T10:30:00Z
```

Upload body: `fileName`, `csv`, `declaredTotal`. The source control total is mandatory. One upload contains exactly one branch. The file digest uses SHA-256 of CSV content and declared total.

## Validation rules

- Required headers and unique column names are checked.
- The branch must be an active branch location and be in the user's allowed scope.
- SKU must exist and be active.
- External sale ID must be present and not already posted for the branch.
- Quantity must be positive and within three decimal places.
- Unit price must be a non-negative valid monetary amount.
- Payment method must be one of the configured POS payment codes.
- Sale timestamp must parse as a valid date/time.
- Rows grouped under one external sale ID must share payment method and sale time.
- The sum of row value must exactly match the declared source total before release.

## State outcomes

| Condition | Batch state | Stock effect |
|---|---|---|
| All rows valid, exact total | `staged` | None |
| One or more invalid rows | `needs_correction` | None |
| Total differs from source control | `blocked` | None |
| Branch Manager successfully releases | `posted` | Atomic sale, line, inventory and journal posting |

## Atomic posting and retry behavior

Release executes the grouped sales, sale lines, stock movements, balanced journals, batch state and audit event within one database transaction. A transaction failure rolls back the sale and stock work; the batch remains staged and records the error for review. Retrying the same batch after correction of the operational cause posts it once. Repeated release of an already posted batch returns the existing posted result.

## Verification coverage

The local acceptance suite verifies upload-only staging, control-total variance, duplicate file digest, Owner release denial, assigned Branch Manager release, duplicate release, location scope and injected journal failure with full transaction rollback followed by a successful retry.
