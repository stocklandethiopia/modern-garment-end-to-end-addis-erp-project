# Source SHA-256 reconciliation — 24 November 2026

**Disposition: PASS for the previously imported v0.3.0 archive. This is not a release approval or proof of any different Drive archive.**

## Source identity
- Application: Stockland Garment ERP — Full-Stack Local Application, v0.3.0.
- Archive: `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip`.
- Drive folder identified by the original import report: `1Kl3hwG0yeyncn_8Gw3Dr3QrVCciArX3U`.
- Archive SHA-256 supplied by the Product Owner: `5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1`.
- Embedded `BUILD_MANIFEST.json` SHA-256 supplied by the Product Owner: `0d7c03dac0be52df3a4699fcdb93b4da8b45cb853980dcc0a62a598568722c0`.
- Existing import branch: `audit/stockland-local-source-v0.3.0`.
- Source import commit stated in `SOURCE_IMPORT_REPORT.md`: `78ab018afaaa231b50c52b3a20fc33de37599f6c`.

## Checks repeated locally in this session
| Check | Result |
|---|---|
| Local archive SHA-256 equals supplied sidecar | **MATCH** |
| Extracted embedded manifest SHA-256 equals supplied sidecar | **MATCH** |
| Manifest entries | 106 |
| Files present after extraction | 106/106 |
| File sizes match manifest | 106/106 |
| Per-file SHA-256 matches manifest | 106/106 |
| Total extracted files including manifest | 107 |
| ZIP path traversal review | No absolute or parent-traversal entries observed |

The v0.3 manifest declares version 0.3.0, schema version 2 and 45 application tables. These identify the historical local app, not the canonical SaaS target.

## GitHub Actions run #7 independently re-checked
- Run: https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45266965264
- Run ID: `45266965264`; conclusion: **success**.
- Run head SHA: `5edf4d1feb9aedd1d49ce7f151c15b5f6ff7fa71`.
- Job ID: `138016314689`; source import/download, archive verification, extracted tree commit, post-commit manifest, syntax, tests and release-gate steps all completed successfully.
- Artifact ID: `14862248279`; name `stockland-garment-erp-local-source-v0.3.0`; digest `sha256:5654eff3ffb98ef121f47234f65f36c55e326fe4418f39b9bfafafe0f75cda36`.

The job log records the 106-entry manifest, matching embedded manifest hash, 107 total files and a successful post-commit manifest check.

### Exact Node.js test count
The v0.3 package TAP output is `# tests 15`, `# pass 15`, `# fail 0`: one top-level suite plus 14 nested business-workflow tests. A fresh local run in this session reproduced 15/15. The same run passed:
- `npm run check`: 12 JavaScript/MJS files;
- `npm run verify:manifest`: 106 files;
- `npm run release:gate`: 78 required artefacts, 30 traceability rows, 182 Markdown links, 19 UI modules, SQLite schema v2 / 45 tables.

This count is distinct from the separately supplied Python Local Reference Runtime report, which states 16 tests.

## Python Local Reference Runtime archive — independently re-verified

The two Python reference archives were subsequently added to the isolated source tree in source-audit commit `0730df72b382663c13022be6ed5e17bcab6a64a6`:

| Committed archive | Git blob SHA | Archive SHA-256 recomputed by Actions | ZIP entries |
|---|---|---|---:|
| `source/reference-sources/files.zip` | `98de6062e1686917ebbade6eca45e07e52773697` | `b8b58c8d25655777ed1e97bafd62f3418975701d17f08c06c3746f4a714e40eb` | 2 |
| `source/reference-sources/stockland-local-erp-v0.1.zip` | `b31b81b83d730aac8a2fac041c86bb1ca5afe6ea` | `c2124bb8a625c7221c9f9ee8701ae70e1f5b18b235703654ea2a0156e8f02c0b` | 9 |

GitHub Actions run [45397701554](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45397701554), head SHA `5e21f48cc9991213474cd395ab8d9ecddc19e609`, checked out source audit commit `0730df...`, extracted both archives with path/symlink checks, and independently recomputed all seven declared Python source-file hashes. **7/7 matched.** It then ran `python -m unittest discover -s tests -v` using Python 3.12.16: **16 tests, 0 failures, return code 0**. The test output includes `test_e2e_full_19_stage_route`, four separate ledger controls, single reversal, chemical SDS/expiry, route-order, independent reinspection, outbox exactly-once, cross-tenant isolation and SAM approval tests.

The original supplied report's Python 3.12.3 / SQLite 3.45.1 environment is a separate reported run. The GitHub-hosted re-run used Python 3.12.16; this workflow did not independently record the SQLite version.

**Hash-label correction:** the earlier source-audit commit message called `f4f5b4b...` a “Verification package SHA-256”. The recomputed file checks show this value is the SHA-256 of `README.md`, not either ZIP archive. The two actual archive hashes are the distinct `b8b58...` and `c2124...` values above.


## Drive IDs must remain distinct
- `1Kl3hwG0yeyncn_8Gw3Dr3QrVCciArX3U`: the folder cited by the successful v0.3 import report/run #7.
- `1o4y6s2hP81sNra93112sV7ldJNty9-2`: a different later folder. The later audit workflow recorded intermittent listing failure and a final `BLOCKED_DRIVE_LISTING` diagnostic. That does not invalidate the first import and the first import does not verify the second folder.

## Limits and disposition
The checksums prove the identity and integrity of the previously imported v0.3.0 archive and, separately, all seven files in the Python reference runtime ZIP stored in the GitHub source tree. They do not prove the contents of the separate later Drive folder `1o4y...`, or authorize replacement of canonical `backend/` / `frontend/`, a change to the Angular/Laravel/PostgreSQL baseline, merge to `main`, or production release.

**Current disposition:** existing v0.3.0 isolated intake integrity **PASS**; separate/new archive replacement **NOT VERIFIED**; canonical release **NOT APPROVED**.
