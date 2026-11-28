# Product and garment-variant contract

## Identity

A saleable garment must resolve to a unique Stockland SKU. At minimum, the catalogue needs:

- SKU / item code (unique, stable; current project convention may use `STK-...`);
- brand and product/style name;
- product category and department/gender;
- age group (including newborn / baby / kids / adult where applicable);
- size and colour as variant dimensions;
- barcode / GTIN where supplied;
- unit of measure and valid conversion factors;
- cost, retail and wholesale price lists;
- active status and branch/warehouse eligibility.

## Invariants

1. One distinct size/colour combination that is separately sold or counted must have a distinct SKU or a formally modelled variant key.
2. Barcode uniqueness must be enforced at the intended scope; duplicate scans must not silently select an arbitrary product.
3. The server recomputes authoritative price, tax, discount and stock values. Client-provided totals are not trusted.
4. UOM conversion is explicit. A carton/pack quantity must be converted to stock UOM before availability checks.
5. Product imports must be idempotent and cross-reference rows before committing. Unknown brand/size/colour values go to an exception report, not silent auto-creation.
