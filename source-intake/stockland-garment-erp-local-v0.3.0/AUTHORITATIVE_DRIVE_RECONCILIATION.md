# Authoritative Drive source reconciliation — Stockland Garment ERP v0.3.0

**Review disposition:** SAME APPLICATION / REFERENCE AND ADAPTATION MATERIAL VERIFIED / PRESERVE MANIFEST-CORRECT GITHUB SOURCE  
**Authoritative Drive folder:** `146liRDU7W1-kWZxgN_LGklEgsUlAFTLE`  
**Candidate application root:** `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK/`  
**Reviewed:** 23 November 2026  
**Repository branch:** `audit/stockland-local-source-v0.3.0`

## 1. Application identity

The candidate Drive folder contains the Stockland Garment ERP application root with `package.json` and `BUILD_MANIFEST.json`.

| Check | Drive candidate | Existing GitHub source |
|---|---:|---:|
| Application | Stockland Garment ERP | Stockland Garment ERP |
| Version | 0.3.0 | 0.3.0 |
| Manifest entries | 106 | 106 |
| Build manifest SHA-256 | `0d7c03dac0be52df3a4699fc9db93b4da8b45cb853980dcc0a62a598568722c0` | Same |
| Application files including manifest | 107 expected | 107 committed |

The Drive candidate's build-manifest bytes are identical to the manifest in the committed application.

## 2. File-by-file verification

The GitHub-hosted selective audit downloaded all 106 manifest-listed Drive files and compared their actual SHA-256 values and byte sizes against both the candidate manifest and the current GitHub source.

- **105 / 106** Drive files matched the manifest and current GitHub source byte-for-byte.
- **0** manifest-listed files were blocked or unavailable in the successful selective audit.
- **1** file in the Drive folder deviates from its own manifest: `templates/README.md`.

### The single Drive-folder deviation

| Source | Size | SHA-256 |
|---|---:|---|
| Manifest expected | 1,948 bytes | `ae3d3f6e0618decf4a8f17e09c0cb2036efe015024f11bdf14bbddb7f8345c48` |
| Current GitHub file | 1,948 bytes | `ae3d3f6e0618decf4a8f17e09c0cb2036efe015024f11bdf14bbddb7f8345c48` |
| Actual file in new Drive folder | 446 bytes | `6d2acf2723e1e3feadd5fc86eadf1d74351c042f554dab40edfdfaa319d8a2ca` |

The 446-byte Drive copy is a shorter import-template README. The existing GitHub copy is the 1,948-byte file required by the candidate's own build manifest. **Keep the GitHub copy; do not overwrite it with the divergent Drive copy.**

### Files present in Drive but not in the application manifest

The Drive folder also contains these root-level files outside the 106-entry manifest:

- `templates/employee-template.csv`
- `templates/product-variant-template.csv`
- `templates/supplier-purchase-order-template.csv`

They are not part of the declared v0.3.0 application inventory. The manifest-tracked template files are under `templates/imports/`. The extra root-level copies are therefore excluded from the canonical source import unless separately reviewed and explicitly adopted.

## 3. Reference-source and adoption cross-check

The Drive candidate includes the complete reference kit. **All 29 reference/adaptation evidence files captured by the audit matched the candidate manifest and current GitHub source byte-for-byte**, including:

- `reference-sources/OPEN_SOURCE_REFERENCE_KIT/SOURCE_MANIFEST.json`
- `SOURCE_REGISTER.csv` and `LICENSE_COMPLIANCE.md`
- `source-review/SOURCE_REVIEW.md`
- `stockland-adaptation/MODULE_MAPPING.md`
- Contracts for catalog variants, inventory ledger, offline POS and POS import release
- `tests/STOCKLAND_ACCEPTANCE_MATRIX.md`
- The selected X POS source snapshot, its MIT license, origin record and upstream source files
- `docs/OPEN_SOURCE_PROVENANCE.md`

The adoption cross-check is expressed through the source review, module mapping, domain contracts and acceptance matrix; no separate file literally titled `ADOPTION_CROSS_CHECK` was identified.

The reference kit records the X POS `develop` snapshot at commit `4f65d681f766c0dcc73fcbb2debc7bd105c8223f`, its MIT license and six selected source files. The adaptation mapping covers inventory availability, barcode parsing, durable POS submission, offline policy and regression tests. The acceptance matrix records Stockland scenarios for stock concurrency, UOM, variants, barcode parsing, POS idempotency, offline blockers, branch-manager release and ledger integrity.

## 4. Decision for the repository

**Do not replace the committed application tree from the new Drive folder.** The candidate is the same v0.3.0 application by manifest identity, and its reference/adoption material already matches the current import. The one Drive file that differs is the Drive copy that fails the manifest; the GitHub copy matches the manifest. The three additional root-level CSV files are unmanifested.

The manifest-correct application remains at:

`source-intake/stockland-garment-erp-local-v0.3.0/source/`

Its original source import commit is `78ab018afaaa231b50c52b3a20fc33de37599f6c`. The successful import CI run is [GitHub Actions run #7](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45266965264).

The authoritative Drive comparison was performed in [GitHub Actions run](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45270035849), which completed successfully. Its detailed artifact is named `authoritative-stockland-source-comparison`.

No application source files were edited by this reconciliation. The work remains on the audit branch; no release, merge to `main`, or Codespace creation was performed.
