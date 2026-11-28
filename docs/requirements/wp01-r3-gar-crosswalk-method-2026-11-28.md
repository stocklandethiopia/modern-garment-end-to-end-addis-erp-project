# WP-01 R3 ↔ legacy GAR requirement crosswalk — engineering proposal

**Status:** DRAFT — the semantic relationships are proposed for review; neither source register is replaced or retired.  
**Work package:** WP-01, with source ownership distributed across WP-02–WP-16.  
**Prepared:** 28 November 2026.  
**Working baseline:** `delivery/autonomous-wp-01-16` at `217aa9848e1499617aeab76d77349f4bb1805066`.

## 1. Source integrity and scope

The verified R3 source is the preserved WP-01 artifact from workflow [#45891155246](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45891155246), artifact `15151322957`. The archive SHA-256 is `44f69b36d6ed389633d4e305dacfa825fbdffb99d78cc903e3f050761811a6e7`. The source contains 128 unique R3 requirement records across 24 approved bounded contexts, with 45 candidate-source mappings. Its canonical implementation status is `not_started` for all 128 records.

The preserved repository matrix at `docs/requirements/requirements-traceability.md` contains 120 unique legacy `GAR-*` records across 13 prefixes. Its Git blob at the reconciliation baseline is `296897f5356461414761151050b29dd2d7815247`. These are different identifier systems with different domain partitions and requirement granularity. The numeric difference of eight is not evidence of eight missing rows and must not be treated as a row-by-row subtraction.

This crosswalk preserves every R3 ID and every legacy GAR ID. It adds no new runtime capability, removes no approved scope, changes no requirement text and does not classify any requirement as implemented. R3 remains the stable canonical requirement namespace for the engineering traceability path; the legacy GAR identifiers are preserved as source aliases where a semantic relationship is proposed.

## 2. Draft semantic relationship model

The file `wp01-r3-gar-crosswalk-2026-11-28.tsv.gz` has one row per R3 record and records its WP allocation, related legacy GAR IDs, relationship class, exact R3 acceptance-scenario ID, implementation status and review status. Multiple legacy IDs may be related to one R3 record, and one legacy GAR record may be represented across multiple R3 records. Links were assessed by business outcome, actors, trigger, workflow, data/evidence, authorization/control and package allocation—not by matching prefixes or numeric suffixes.

| Class | Meaning |
|---|---|
| `EXACT` | One-to-one semantic equivalence with no material scope or control difference. No row is currently asserted as exact. |
| `SPLIT` | A legacy GAR requirement is decomposed across multiple more-specific R3 records. |
| `CONSOLIDATED` | One R3 record consolidates multiple legacy GAR statements whose coverage is not otherwise split across R3 records. |
| `PARTIAL` | The requirements overlap, but one is broader, narrower or has different actors, controls, data/evidence or acceptance boundaries. |
| `R3_ADDITION` | R3 requirement has no direct legacy GAR predecessor and is preserved as additional canonical scope. |

Current proposal counts: **61 SPLIT, 47 PARTIAL, 18 CONSOLIDATED, 2 R3_ADDITION, 0 EXACT** across 128 R3 records. The uncompressed TSV SHA-256 is `f5cc4ff6c5accc93fdb4d6290b372faefbe5ec85146e9c8cdb4dbac884b48e70`; the repository gzip artifact SHA-256 is `3d8a617f95c5b5a391e9f3a0740f88608ce907913f675218255fa8f78a910832`. There are 169 proposed relationships. Both additions are explicit: `PLT-002` (subscription, plan limits and feature entitlements) and `DWR-006` (the controlled ERP evolution workflow). The inverse coverage check finds a proposed relationship for all 120 legacy GAR IDs; this is traceability coverage only, not proof that every legacy requirement is fully satisfied by its mapped R3 records.

## 3. Control and non-claims

Every row is marked `DRAFT_REVIEW_REQUIRED`; every R3 implementation status remains `not_started`. No `LEGACY_ONLY_REVIEW` or `RETIRED_WITH_APPROVAL` disposition is applied. No requirement is deleted, renamed or silently superseded. The classification is deliberately conservative: where a one-to-one relationship is not demonstrably exact, it is not labelled `EXACT`.

This mapping does **not** yet connect R3 acceptance scenarios to delivered code, migrations, APIs, UI, events, operational controls or executed tests. That implementation-level traceability is CAP-004 and must be populated with exact paths and exact-SHA test evidence. A mapped `AT-{R3_ID}` remains an acceptance scenario identifier, not evidence that the scenario already passes.

Legal and statutory records (especially `REG-*`, `PAY-*` and finance/tax relationships) remain subject to official-source verification and qualified local professional approval before statutory calculations or rules are activated. Requirements accountability metadata does not grant organization position or delegated transaction approval authority.

## 4. Verification

The source package was rebuilt from its preserved archive and independently rerun on 28 November 2026:

- `node scripts/validate-work-package.mjs` — PASS (128 records, 24 contexts, 45 candidate mappings).
- `npm test` — PASS, 20/20 tests.
- `npm run audit:strict` — PASS, A01–A10 and F01–F07 (17 checks).
- Crosswalk-specific checks validate the full R3 ID/WP set against the source JSON, the full legacy ID set against the visible matrix, exact acceptance IDs, allowed relationship classes, unique mapping rows, no unknown IDs, no missing legacy IDs, and preservation of all `not_started` states.

The GitHub WP-01–WP-16 verification workflow will run the crosswalk-specific validator against the exact PR commit as part of the WP-01 source job. The result will be linked in the CAP-002 handover.

## 5. CAP-002 exit conditions

CAP-002 may be closed when the crosswalk is checked against the exact preserved R3 archive and legacy matrix, all 128 R3 records and all 120 legacy GAR IDs have an explicit and justified disposition, no unknown/duplicate/unmapped identifiers remain, the proposed semantic relationships have been reviewed under the delegated WP-01 acceptance authority, and exact-head ERP/WP verification passes. Any later material scope retirement still requires explicit Product Owner approval. CAP-004 must then extend the approved crosswalk to owners, WP tasks, source paths, data tables, migrations, APIs, UI, events, tests, evidence and runbooks.