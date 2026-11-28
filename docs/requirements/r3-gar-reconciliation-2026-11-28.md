# WP-01 R3 ↔ GAR semantic reconciliation — 28 November 2026

**Project:** Modern Garment End-to-End Addis ERP  
**Corrective action:** CAP-002 — reconcile the R3 canonical requirements register with the preserved legacy GAR inventory  
**Working branch:** `work/wp01-cap-002-r3-gar-reconciliation-2026-11-28`  
**Exact branch base:** `217aa9848e1499617aeab76d77349f4bb1805066` (CAP-001 documentation integration)  
**R3 source archive SHA-256:** `44f69b36d6ed389633d4e305dacfa825fbdffb99d78cc903e3f050761811a6e7`  
**Legacy GAR matrix blob SHA:** `296897f5356461414761151050b29dd2d7815247`

## 1. Decision and scope

The reconciliation is a semantic crosswalk, not an ID-count subtraction exercise and not a declaration that either register is implemented. It preserves the two source catalogues as separate inventories:

- R3 contains **128 unique canonical requirement IDs** in 24 bounded contexts.
- The pre-existing GAR matrix contains **120 unique legacy requirement IDs**.
- R3 records are mapped to one or more GAR records only where there is a meaningful, bounded relationship. Every such relationship is classified as **partial**; this reconciliation asserts no one-to-one equivalence.
- R3 items that add a distinct capability remain explicit additions. Context-only references identify adjacent controls, not aliases or satisfaction.
- All 120 GAR IDs remain retained. All 128 R3 records retain `canonical_implementation_status=not_started`.

The arithmetic difference of eight is coincidental to the semantic analysis. It does not establish that eight legacy requirements were removed, that all R3 items are new, or that any adjacent GAR item is satisfied.

## 2. Authoritative inputs and reproducibility

### R3 release package

The R3 source is the release archive `WP01_R3_SOURCE_2026-11-26.tar.xz`, reconstructed from the 18 committed base64 parts beneath `work-packages/wp-01-requirements-traceability/source/`. Its exact SHA-256 is recorded above and matches the WP-01 repository import manifest. The source-of-record files are:

- `docs/requirements/canonical-requirements.csv`
- `docs/requirements/canonical-requirements.json`
- the package context catalogue, source-map candidate inventory, test suite, strict audit and release notes.

The source package validation completed with these results:

| Check | Result |
|---|---:|
| Canonical CSV records | 128 unique |
| Canonical JSON records | 128 unique |
| CSV/JSON field and record parity | 128 × 16 fields, identical |
| Bounded contexts | 24 |
| Candidate source-map rows | 45 |
| Node test suite | 20 / 20 passed |
| Strict package audit | 17 / 17 checks passed; 0 failed, open or not-run |
| All canonical implementation status values | `not_started` |

The 45 source-map rows are R3 v0.7 **candidate implementation/source evidence**. They are not the GAR crosswalk and are not proof that code or workflows are implemented.

### Legacy GAR inventory

The preserved input is `docs/requirements/requirements-traceability.md`, blob `296897f5356461414761151050b29dd2d7815247`, containing 120 unique GAR rows. This document and its 120 rows are not rewritten or retired by this CAP. The new disposition CSV annotates how each retained GAR row relates to R3 or where it remains governed by an existing architecture/quality standard.

## 3. Provenance and the eight R3 additions

R3 contains 122 baseline records and six records whose `governing_source` explicitly identifies the Product Owner-supplied agent-native ERP strategy. Those six records are **DWR-007 through DWR-012**. They are not DWR-005 through DWR-010.

The eight records classified as R3 additions with no direct legacy GAR equivalent are:

| R3 ID | Provenance | Requirement intent | GAR disposition |
|---|---|---|---|
| `PLT-002` | R3 baseline | Plan limits, feature entitlements, trial periods, billing states and suspension consistently across UI/API/jobs/integrations | No direct GAR equivalent. `GAR-ENT-009` is context only; legal activity/licence applicability is not SaaS subscription entitlement. |
| `DWR-006` | R3 baseline | Trusted ERP evolution workflow: inspect requirements/source, make isolated changes, test and prepare review artifacts; no independent production merge/deploy | No direct legacy GAR equivalent. Keep distinct from the runtime operational agent. |
| `DWR-007` | Product Owner supplied | Operational agents execute only approved Laravel application capabilities; no unrestricted DB/shell | Related context only: `GAR-ANA-005`, `GAR-ENT-005`. These do not fully define the owner-supplied runtime capability boundary. |
| `DWR-008` | Product Owner supplied | Agent-assisted data creation/file import stages a validated draft and exception report before sensitive posting | Related context only: `GAR-ANA-003`, `GAR-ANA-004`. Existing integration/idempotency controls are adjacent, not equivalent. |
| `DWR-009` | Product Owner supplied | Device-independent, versioned barcode/QR/RFID adapters emit canonical authenticated tenant/site-scoped scan events | Related context only: `GAR-ANA-004`, `GAR-DST-003`. Generic integrations and stock genealogy do not define the complete device trust/event contract. |
| `DWR-010` | Product Owner supplied | Independently auditable agent execution, approval, handoff, tool I/O and provider traces without secrets or unnecessary personal data | Related context only: `GAR-ANA-003`, `GAR-ANA-005`, `GAR-ENT-008`. The trace surface is broader and agent-specific. |
| `DWR-011` | Product Owner supplied | Replaceable AI provider/model/orchestration behind a stable abstraction; conventional ERP remains usable during AI outage | Related context only: `GAR-ANA-005`. Explainable AI use does not equal provider portability or offline continuity. |
| `DWR-012` | Product Owner supplied | Govern extension lifecycle: discover, review, install, validate, enable, safe-disable with version/dependency/permission/migration/rollback/audit | Related context only: `GAR-PLT-009`, `GAR-PLT-011`. Build/release and migration controls are adjacent, not a complete extension lifecycle. |

Additional distinctions:

- `DWR-005` is a baseline R3 requirement for the operational agent gateway. It is partially related to `GAR-ANA-005`, but the two are not equivalent.
- `INV-005` is a baseline R3 device/offline scan adapter requirement. It is partially related to `GAR-ANA-004` and `GAR-DST-003`; device registration, trust, scan replay and offline reconciliation remain explicit.
- `DWR-013` is a baseline release-governance requirement, partially related to `GAR-PLT-009` through `GAR-PLT-012`. Exact-commit evidence and accountable environment approval are explicit additions to the release control chain.
- `PLT-002` and `DWR-006` are R3 baseline additions to the legacy GAR set; the six DWR-007–012 rows are the separately identified Product Owner-supplied additions.

## 4. Legacy GAR disposition

The 120 legacy IDs are accounted for as follows:

| Disposition | Count | Meaning |
|---|---:|---|
| `PARTIAL_SPLIT` | 98 | The GAR control relates to multiple R3 requirements; the combined R3 set still needs its own acceptance proof. |
| `PARTIAL_RELATION` | 18 | A bounded relationship exists, but R3 is not a full replacement or equivalent statement. |
| `PARTIAL_STANDARD` | 1 | `GAR-PLT-005` relates partly to `DWR-004` and the REST/OpenAPI platform standard; API contract gaps remain. |
| `PRESERVED_STANDARD` | 3 | No direct R3 row is asserted: preserve `GAR-PLT-001`, `GAR-PLT-002`, and `GAR-PLT-004` in the governing platform/Angular/quality standards. |
| **Total** | **120** | No GAR ID is deleted, retired or silently absorbed. |

Thus 117 GAR IDs have at least one related R3 record (partial relationship only); the remaining three are explicitly preserved by standards. `GAR-PLT-005` has both a partial R3 relationship and a governing-standard reference. All 120 records have a disposition.

### Specific legacy controls that must not disappear

- **`GAR-IE-006` — pre-production readiness gate:** linked partially to `IEP-003`, `MFG-001`, `MRP-003` and `PLM-005`. These records cover capacity, route, material planning and product-handover inputs; they do not, by themselves, prove one coordinated release gate that blocks bulk production when any critical dependency is missing. Retain the explicit gate and the gap. Related corrective actions: CAP-016, CAP-018 and CAP-021.
- **`GAR-FIN-010` — month/year close:** linked partially to `FIN-001`, `FIN-002`, `FIN-003`, `FIN-005` and `REG-005`. They cover ledger, journals, AP/AR/treasury and statutory rule snapshots; they do not prove reconciled subledgers, inventory/payroll/fixed-assets reconciliation, consolidation, close checklist, unresolved-difference blocking and immutable close evidence. Retain the explicit close requirement. Related corrective action: CAP-023.
- **`GAR-PLT-005` — REST API contract:** partially related to `DWR-004` and `docs/architecture/platform-standard.md`. The remaining contract work includes stable error envelopes, validation, pagination, correlation IDs, version/compatibility/deprecation rules and contract tests. Related corrective action: CAP-029.
- **`GAR-PLT-001`, `GAR-PLT-002`, `GAR-PLT-004`:** preserve the modular-monolith/bounded-context, Angular accessible role-specific journey, and Redis-ephemeral-only requirements in their existing governing architecture/quality standards. The source map identifies the exact standard paths.

## 5. Crosswalk rules and field meaning

The companion CSV files are machine-readable and are deliberately conservative.

### `r3-to-gar-crosswalk-2026-11-28.csv`

One row per R3 ID. Fields:

- `legacy_partial_match_ids`: semantically related GAR IDs; never an equivalence or implementation claim.
- `context_only_gar_ids`: adjacent legacy context for an R3 addition; it must not be counted as coverage.
- `coverage_classification`: `PARTIAL` or `R3_ADDITION`.
- `canonical_status`: preserved directly from R3 as `not_started`.

### `gar-legacy-disposition-2026-11-28.csv`

One row per GAR ID. Fields:

- `related_r3_ids`: R3 IDs with a bounded partial relationship.
- `disposition`: `PARTIAL_SPLIT`, `PARTIAL_RELATION`, `PARTIAL_STANDARD` or `PRESERVED_STANDARD`.
- `governing_standard`: exact repository standard path where the legacy requirement remains preserved outside a direct R3 row.
- `gap_code`: explicit remaining proof/gap category.
- `retired`: always `NO`.

The mapping is many-to-many. The files make no assertion of one-to-one equivalence, implementation, acceptance, or permission to remove existing requirements.

## 6. Work-package implications and open gaps

The crosswalk is a WP allocation aid, not a rewrite of the approved package plan. R3 continues to allocate records to WP-02 through WP-16. Cross-package dependencies remain explicit:

- WP-02: legal authority register and activation governance; no statutory activation is implied.
- WP-03: SaaS plan/entitlements, tenant/entity/site identity and scoped authorization; `PLT-002` is additive.
- WP-06/WP-08/WP-09/WP-11: `GAR-IE-006` requires one verifiable pre-production gate across technical pack, material, route, capacity, quality and compliance inputs.
- WP-13: `GAR-FIN-010` requires end-to-end period close and source-ledger reconciliation; partial journals/AP/AR are not enough.
- WP-15: operational runtime agents, the separate trusted developer/evolution plane, document staging, authenticated devices, audit, provider abstraction and extension lifecycle remain distinct capabilities.
- WP-16: exact-SHA release governance remains an additional acceptance/evidence requirement, not a replacement for the full end-to-end test plan.

No source requirement is removed, no acceptance criterion is marked passed by this reconciliation, no implementation status is advanced, and no statutory, financial or employment rule is changed. The six owner-supplied additions preserve their provenance. Professional/legal and high-impact human approval boundaries remain unchanged.

## 7. CAP-002 verification and exit gate

CAP-002 may close only when all of the following evidence is present:

1. Both CSVs parse and pass a repository-owned validator for counts, unique IDs, provenance, valid cross-references, full 120-ID disposition, preserved standards and non-retirement.
2. The original R3 archive can still be reassembled and passes its checksum, 128/24/45 validation, 20 tests and strict audit.
3. The legacy GAR matrix remains unchanged except for a short navigation note pointing to this reconciliation.
4. WP-01 source map, handover register, CAP ledger and README all identify this exact crosswalk and its bounded claims.
5. ERP CI, dedicated WP-01 source/crosswalk verification and configured WP-01–WP-16 verification pass on the exact final PR head.
6. After integration, delivery push CI passes on the resulting merge SHA before CAP-003 starts.

**Current result:** source-package tests and strict audit pass; the semantic crosswalk and disposition have been prepared and validated locally. Repository integration, exact-PR verification and post-merge delivery verification remain pending. `CAP-002` therefore remains **IN PROGRESS**.
