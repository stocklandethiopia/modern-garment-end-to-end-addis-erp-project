# WP-01 r2 — review findings for Product Owner disposition

r2 changes **no requirement, mapping, plan or handover content**. It adds an independent audit and records the gaps below. Each needs an owner decision before WP-01 is accepted; none was "fixed" silently because the plan forbids silently expanding or reordering packages.

| ID | Finding | Evidence | Decision needed |
|---|---|---|---|
| F01 | **WP-08 and WP-16 have zero requirements.** WP-08 (route/SOP framework) is delivered by the route-orchestration context, which the register assigns entirely to WP-09. | package counts 02:5 03:15 04:10 05:5 06:10 07:10 **08:0** 09:10 10:10 11:5 12:20 13:5 14:10 15:12 **16:0** | Reassign some MFG-* records to WP-08 (or add WP-08 records), and decide whether WP-16 is verification-only by design. |
| F02 | **Records are context templates.** 127 records share only 30 distinct trigger+workflow+controls+evidence sets (max reuse 6). | `scripts/lib/audit.mjs` F02 | Accept as "context-level register" (the handover says so) or require per-requirement decomposition before WP-01 acceptance. |
| F03 | **Acceptance tests are not 1:1.** 121/127 end in a generic suffix; 30 distinct test cores serve 127 requirements. | F03 | Require a specific test per requirement, or relabel tests as context-level. |
| F04 | **Evidence labels disagree with the source map.** DWR-008, REG-001, MRP-002, LOG-001 are mapped from v0.7 but labelled `not_in_v0.7`; 46 of 91 records claim v0.7 evidence but no mapping row references them. | F04 | Correct the labels or add the missing mapping rows. |
| F05 | **`port_decision` is free text** (45 distinct values), so the validator's `copy_source` guard can never fire. r2's A07 adds a "reads as copying" check, but an enum is the real fix. | F05 | Approve an enum (e.g. reimplement / extend / replace / exclude). |
| F06 | **Count wording is ambiguous.** Handover and CHANGELOG read as "127 plus six"; the truth is 127 total including six Product-Owner-supplied agent/device records (121 + 6). | F06 | Approve a wording correction. |
| F07 | **Approved context names not verified** (only the count of 24). | NOT RUN | Run against `docs/product/domain-map.md` in the canonical repo. |

## Verified clean (executed)
CSV equals JSON cell-for-cell; unique contiguous IDs; all 16 fields populated; all records `not_started`; every mapping target exists; source SHA agrees with the audit record; no X POS / vendor / third-party source or paths; no legal rate, percentage or currency amount in the register; documentation counts equal computed counts. (Earlier "xpos"/"Stockland" text hits were false positives: the word "exposing" and a candidate-evidence label.)
