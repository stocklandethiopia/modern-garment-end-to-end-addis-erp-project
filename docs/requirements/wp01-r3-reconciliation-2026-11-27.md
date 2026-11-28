# WP-01 R3 Requirement Register Reconciliation

**Date:** 27 November 2026  
**Work package:** WP-01 — Requirements inventory and traceability  
**Status:** RECONCILIATION OPEN — source integrity verified; ID/content crosswalk not yet completed  
**Canonical integration baseline inspected:** `delivery/autonomous-wp-01-16` at `fc01eced62b97c4ec27f0075264fc8ba0fb11851`

## 1. Evidence inspected

The exact verified WP-01 R3 source was retrieved from GitHub Actions workflow run [#45891155246](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45891155246), artifact **15151322957** (`wp01-r3-source-and-build-dee0e3ad9f83590e53bac080155f6ae533841b94`). The package README records the canonical source archive SHA-256 as:

`44f69b36d6ed389633d4e305dacfa825fbdffb99d78cc903e3f050761811a6e7`

The artifact was downloaded and its inner R3 source archive and ZIP inspected. The package contains:

- 128 canonical requirement records in both `canonical-requirements.csv` and `canonical-requirements.json`.
- 24 approved bounded contexts with exact-name validation.
- 45 v0.7 candidate-source map rows.
- 20 package tests and strict quality checks recorded as PASS by the R3 package.
- 128/128 `canonical_implementation_status` values equal to `not_started`.

The R3 package says the total is **122 baseline + 6 supplied agent/device/capability records = 128**. The six are included in the total, not added after it. The package review findings F01–F07 are recorded as resolved at the requirements-artifact level only; they do not evidence implementation of the Laravel/Angular/PostgreSQL application.

## 2. The two visible ID sets are not yet reconciled

The repository's current `docs/requirements/requirements-traceability.md` contains 120 distinct `GAR-*` identifiers across 13 prefixes:

| Legacy GAR prefix | Rows |
|---|---:|
| ANA | 6 |
| DST | 11 |
| ENT | 10 |
| FIN | 10 |
| HR | 8 |
| IE | 6 |
| MER | 8 |
| MFG | 12 |
| OPS | 8 |
| PLM | 10 |
| PLT | 12 |
| QLT | 9 |
| SRC | 10 |
| **Total** | **120** |

The verified R3 CSV/JSON contains 128 distinct identifiers across 24 bounded-context prefixes:

| R3 prefix | Rows | R3 prefix | Rows |
|---|---:|---|---:|
| PLT | 5 | ORG | 5 |
| IAM | 5 | CRM | 5 |
| INI | 5 | PLM | 5 |
| CST | 5 | PRC | 5 |
| MRP | 5 | INV | 5 |
| IEP | 5 | MFG | 5 |
| QLT | 5 | DST | 5 |
| OMS | 5 | LOG | 5 |
| FIN | 5 | HRM | 5 |
| PAY | 5 | LND | 5 |
| PER | 5 | MNT | 5 |
| REG | 5 | DWR | 13 |
| **Total** | **128** | | |

There is no direct identifier equality between these two sets. The arithmetic difference is eight, but it is **not evidence of exactly eight missing requirements**. The registers use different identifiers, different domain partitions and different work-package allocations. One older GAR statement may map to several R3 records; several old records may consolidate into one new statement; or a requirement may be genuinely additional, partial, superseded or unmatched. Do not map by similar prefix or row number alone.

## 3. Accountability labels must not become approval authority

The R3 data includes `business_owner` metadata such as `Platform Product Owner`, `Enterprise Architecture Owner`, `Procurement Owner`, `Planning Owner`, `Inventory Control Owner`, `Quality Owner`, `HR Owner`, `Ethiopia Legal and Regulatory Owner` and `Finance Controller`. Some `primary_roles` fields also contain labels such as `Platform Owner`, `Product Owner`, `Process Owner` and `CAPA Owner`.

Those fields identify domain accountability/stewardship in a requirements record. They do **not** establish a company position, a line-management post, or delegated transaction authority. In particular, `Procurement Owner` must not be interpreted as the person who automatically approves every purchase order. Each accountable function must be mapped to the real organization position(s) (for example Procurement Manager, Finance Controller, COO, CEO/Managing Director or the applicable Board authority) and to a separately approved, scoped DoA rule. This is consistent with the enterprise organogram framework being prepared under WP-03.

## 4. Required reconciliation method

A controlled crosswalk must be produced before closing WP-01 matrix reconciliation. For every legacy GAR record and every R3 record, record:

1. Legacy GAR ID (nullable) and R3 canonical ID (nullable).
2. Match classification: `EXACT`, `SPLIT`, `CONSOLIDATED`, `PARTIAL`, `R3_ADDITION`, `LEGACY_ONLY_REVIEW` or `RETIRED_WITH_APPROVAL`.
3. Semantic rationale based on requirement outcome, actors, workflow, authorization/control, data/evidence, acceptance scenario and package allocation.
4. Preserved source references and any source-map evidence boundary.
5. Current WP owner package and downstream API/UI/schema/test evidence target.
6. Review status and approver for any explicit retirement or material scope disposition.

Rules:

- Retain all original GAR text and provenance until its disposition is evidenced.
- Retain every R3 record and stable R3 ID; do not silently drop or renumber them.
- A many-to-many mapping is permitted where justified; write the rationale for each link.
- Map acceptance tests by their exact identifiers. Do not assume an old AT identifier is equivalent to an R3 `AT-{requirement_id}` scenario.
- Preserve the source archive checksum and source-map decisions; candidate-v0.7 evidence is not canonical application implementation.
- Keep all 128 implementation statuses at `not_started` until the relevant later WP delivers and verifies executable application behavior.
- Use canonical R3 IDs as traceable requirement keys after the crosswalk is approved. Preserve a legacy GAR ID only as a source alias where the mapping is justified.

## 5. Immediate next steps

1. Build and review the complete semantic crosswalk; list unmatched records on both sides.
2. Compare each of the 24 R3 bounded-context names against the approved platform-standard catalogue and the existing domain map.
3. Confirm the fixed WP-02–WP-16 allocation for every R3 record, including MFG-001 in WP-08 and DWR-013 in WP-16.
4. Update the visible traceability matrix, WP-01 handover and downstream task map without changing the approved product scope.
5. Run the R3 validator, all 20 package tests, strict audit, exact-source checksum and exact-head CI; preserve PASS/FAIL/NOT RUN separately.
6. Continue with WP-02 legal-source verification in the approved sequence. A legal source register is not a legal opinion or authority to activate statutory rates.

## 6. Non-claims

This report does not declare WP-01 fully accepted. It confirms the R3 source artifact and identifies the outstanding repository reconciliation accurately. It does not implement any ERP feature, grant legal interpretation, activate statutory rules, or claim that any of the 128 requirements has been implemented.
