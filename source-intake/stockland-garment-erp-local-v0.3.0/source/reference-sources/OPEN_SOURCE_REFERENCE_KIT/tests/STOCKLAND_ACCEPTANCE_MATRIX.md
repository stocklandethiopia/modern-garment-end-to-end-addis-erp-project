# Stockland acceptance matrix for ported patterns

These cases define the Stockland acceptance criteria and identify the expected behavior for each workflow.

| ID | Area | Scenario | Expected result |
|---|---|---|---|
| INV-01 | Oversell | Two POS terminals attempt to sell the final unit concurrently | Row locking/transaction allows only a valid posting; no negative stock |
| INV-02 | UOM | One carton of 24 is requested while only 5 stock units exist | Converted stock quantity is 24; transaction is blocked |
| INV-03 | Warehouse | Stock query targets a warehouse group | Availability sums allowed child warehouses only |
| INV-04 | Variant | Same style, different size or colour | Distinct saleable SKU/variant; no stock crossover |
| BAR-01 | Barcode | Barcode prefix/segment configuration is invalid | Parser rejects; does not guess another SKU |
| BAR-02 | Barcode | Scale barcode includes quantity/price data | SKU is resolved; price is checked against server price policy |
| POS-01 | Idempotency | Browser loses response after server posts sale | Retry with same sale ID returns existing invoice; no duplicate |
| POS-02 | Durability | Browser closes during submit | Locally persisted sale remains recoverable |
| POS-03 | Rejection | Server returns structured stock/business rejection | Sale stays visible with reason; not silently deleted |
| OFF-01 | Offline | Catalogue not complete or wrong branch scope | Offline checkout blocked |
| OFF-02 | Offline | Unsupported payment mode / credit / return | Offline checkout blocked according to policy |
| IMP-01 | Import | Upload file without Branch Manager release | Batch remains staged; no final posting |
| IMP-02 | Import | Same source file is submitted twice | Duplicate batch detected; no double-post |
| IMP-03 | Import | Branch Manager releases reconciled batch | Automatic, atomic final posting occurs once |
| IMP-04 | Approval | Owner/Management attempts an extra approval after Branch Manager release | No second approval step exists |
| LED-01 | Ledger | Posted stock movement needs correction | Compensating/reversal movement; original row remains immutable |
| LED-02 | Transfer | Transfer fails mid-process | Both source and destination legs roll back; no partial movement |

## Required evidence per test

Capture test name, fixture, input, expected value, actual value, database assertions, audit-trail assertion and CI run URL/commit SHA. Keep source-project tests separate from Stockland acceptance tests.
