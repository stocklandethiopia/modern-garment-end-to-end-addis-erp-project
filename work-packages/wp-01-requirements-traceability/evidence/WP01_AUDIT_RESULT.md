# WP-01 quality audit result

**Generated:** 2026-11-26T07:36:39.571Z · Node v22.23.5 · Linux x64
**Integrity:** PASS (10 pass, 0 fail) · **Open findings for owner decision:** 6 · **Not run:** 1

| ID | Check | Status | Evidence |
|---|---|---|---|
| A01 | Register and source map parse as JSON arrays | PASS | 127 requirements; 45 mappings |
| A02 | Every record has all 16 required traceability fields | PASS | 127 records complete |
| A03 | Requirement IDs: format AAA-999, unique, contiguous per prefix | PASS | 24 prefixes, 127 unique IDs |
| A04 | work_package is a valid delivery package (WP-02..WP-16) | PASS | all valid |
| A05 | CSV export equals JSON register cell-for-cell | PASS | 127 rows x 16 columns identical |
| A06 | No canonical implementation is claimed (all not_started) | PASS | all not_started |
| A07 | Source map: complete, unique, candidate-only, targets exist, SHA matches audit record, no copy decisions | PASS | 45 mappings; source SHA e5962e1449fb… |
| A08 | No X POS / vendor / third-party source or source-code files in the package | PASS | none found (whole-tree scan) |
| A09 | No legal/statutory rate, percentage or currency amount in the register | PASS | none found |
| A10 | Counts quoted in LOCAL_VERIFICATION and CHANGELOG equal computed values | PASS | 127/24/45; 4 tests |
| F01 | Every delivery package WP-02..WP-16 has traceable requirements | OPEN FINDING | no requirements assigned to: WP-08, WP-16; counts 02:5 03:15 04:10 05:5 06:10 07:10 08:0 09:10 10:10 11:5 12:20 13:5 14:10 15:12 16:0 |
| F02 | Requirements carry distinct workflow/controls/evidence (not context templates) | OPEN FINDING | 127 records share only 30 distinct trigger+workflow+controls+evidence sets |
| F03 | Each requirement has its own specific acceptance test | OPEN FINDING | 121/127 tests end in a generic suffix; only 30 distinct test cores (requirement-to-test is not 1:1) |
| F04 | Register evidence labels agree with the source map | OPEN FINDING | 4 mapped targets labelled not_in_v0.7 (DWR-008, REG-001, MRP-002, LOG-001); 46 of 91 records claim v0.7 evidence but no mapping row references them |
| F05 | port_decision is a controlled vocabulary (so a copy decision is machine-detectable) | OPEN FINDING | 45 distinct free-text decisions across 45 rows; no enumerated values |
| F06 | Requirement-count wording is unambiguous | OPEN FINDING | docs/WP01_HANDOVER.md and CHANGELOG.md read as 127 + 6; computed truth: 127 total including 6 Product-Owner-supplied agent/device records (121 baseline + 6) |
| F07 | The 24 contexts are the approved 24 (by name) | NOT RUN | only the count (24) is checkable here; names need docs/product/domain-map.md from the canonical repository
