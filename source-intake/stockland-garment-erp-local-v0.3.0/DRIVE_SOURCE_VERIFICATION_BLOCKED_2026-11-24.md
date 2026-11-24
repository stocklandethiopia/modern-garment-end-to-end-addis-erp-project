# Drive source verification report — 24 November 2026

**Request:** inspect the Product Owner supplied Google Drive folder and, if its exact source is available and validated, update the isolated GitHub source-intake folder.

## 1. Requested source and destination

- Drive folder: https://drive.google.com/drive/folders/1o4y6s2hP81sNra93112sV7ldJNty9-2
- Drive folder ID: `1o4y6s2hP81sNra93112sV7ldJNty9-2`
- Repository: `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`
- Requested isolated source-intake path: `source-intake/stockland-garment-erp-local-v0.3.0/source/`
- Existing source audit branch: `audit/stockland-local-source-v0.3.0`
- Separate inspection branch: `audit/inspect-drive-folder-2026-11-24`
- Default branch: `main`

## 2. Access and GitHub permissions

| Check | Result | Evidence |
|---|---|---|
| Authenticated GitHub repository access | PASS | Connected GitHub identity is `stocklandethiopia`; repository permission is `admin` and `push=true`. |
| Google Drive ChatGPT connector | BLOCKED | Integration discovery reports Google Drive `DISABLED_BY_ADMIN` / `NOT_AVAILABLE`; no Drive read/list/download action is exposed. |
| Public Drive folder listing from GitHub Actions | INTERMITTENT / CURRENTLY BLOCKED | One run listed 12 items; two later runs could not retrieve the listing. |
| Classic personal access token | NOT USED | No PAT was requested, disclosed, stored or committed. GitHub operations used the authenticated GitHub connection. |

## 3. Drive audit attempts

### Run #4 — partial listing only

- Workflow run: https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45369840344
- Head commit: `7805d3b4893a63ec0e8743fa58b52995dfdc8d56`
- Drive folder listing step: PASS; GitHub Actions printed `Drive folder listing resolved: 12 files.`
- Candidate-manifest step: FAIL; its logic expected `package.json` and `BUILD_MANIFEST.json` to be directly represented in the listing and found zero matching application roots.
- The job did not reach the download/hash/compare step; no artifact containing the listing or file bytes was published.
- This run establishes that the public listing was visible once, but it does not identify which 12 items were present and does not prove that any source file was downloaded.

### Run #5 — listing unavailable

- Workflow run: https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45375967814
- Head commit: `f908a4854a0e1efe4c166d074e6d682065aa9dae`
- The `gdown` folder listing step returned `Failed to retrieve folder contents`; no files were downloaded and no comparison could run.

### Run #6 — four listing retries, diagnostic artifact published

- Workflow run: https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45376258465
- Head commit: `798a25ed4ff023e830415d832c36818336ae42d6`
- Workflow result: success as a diagnostic/inventory workflow only.
- Listing attempts: 4; all returned zero items (`Failed to retrieve folder contents`).
- Result status: `BLOCKED_DRIVE_LISTING`.
- Listed files: 0; downloaded files: 0; failed downloads: 0; archive candidates: 0.
- GitHub Actions artifact: `authoritative-drive-folder-download`, artifact ID `14910739607`, SHA-256 `50fe3b4339bd69f17cb840a7218e9ca34b8d74fa20c1fcd2cd800f71b854d576`.
- The artifact contains only `drive-folder-inventory.json`, `drive-folder-inventory.md` and an empty `SHA256SUMS.txt`; it contains no Drive source files.

**Conclusion:** the new folder's contents have not been retrieved. No exact source archive, application root, manifest, adoption documents or source file bytes from this folder are available for inspection. The 12-item listing from run #4 was not retained and cannot be reconstructed from the recorded logs.

## 4. Checksum sidecar and the previously supplied local archive

A separate checksum sidecar uploaded in the conversation contains these two hashes:

- `5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1` — `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip`
- `0d7c03dac0be52df3a4699fcdb93b4da8b45cb853980dcc0a62a598568722c0` — `BUILD_MANIFEST.json`

The previously supplied local file `/mnt/data/STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip` was re-hashed in this session:

- ZIP SHA-256: `5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1` — **MATCH**.
- Embedded `BUILD_MANIFEST.json` SHA-256: `0d7c03dac0be52df3a4699fcdb93b4da8b45cb853980dcc0a62a598568722c0` — **MATCH**.

These hashes verify only the old v0.3.0 archive already present in the conversation workspace. They do not establish that this archive is contained in the newly supplied Drive folder or that it is the latest source there.

## 5. Current repository state and preservation

| Ref | Verified HEAD | Source/intake state |
|---|---|---|
| `main` | `adca71a053290eb768987a7644d4b13c267cf2a9` | Canonical customer-neutral project baseline; no requested v0.3 source-intake directory at repository root. Unchanged. |
| `audit/stockland-local-source-v0.3.0` | `df31553cf36e3d56dc9fc603d21368861ab26273` | Existing isolated v0.3 intake, 107 files and 31 directories. Unchanged. |
| `audit/inspect-drive-folder-2026-11-24` | `798a25ed4ff023e830415d832c36818336ae42d6` | Separate read-only Drive inspection workflow and trigger. No application source was changed. |

The inspection workflow/report work did not alter the source application directory. No file was replaced or deleted in the existing source-intake tree. No merge, release tag, production deployment, database migration or Codespace transfer occurred.

## 6. Canonical product boundary and release disposition

The canonical repository and accepted governance define a customer-neutral, multi-company apparel operating system using Angular/TypeScript, Laravel/PHP, PostgreSQL and Redis. The previously supplied Stockland v0.3.0 package is a Stockland-specific local Node.js/SQLite application. Repository source-audit documentation explicitly keeps that package isolated as reference/intake material; it is not a substitute for the canonical runtime.

Accordingly:

- The isolated source-intake folder may be refreshed only after the exact new source is accessible and verified.
- Any adoption or porting into the canonical product must follow the approved WP sequence and architecture; no unrelated local-app runtime is to be copied into canonical `backend/` or `frontend/` merely because its ZIP passes its own tests.
- **Release disposition: BLOCKED / NOT APPROVED.** The new Drive source has not been obtained or tested.

## 7. Required prerequisite

Please attach the exact latest application ZIP from the new Drive folder directly to the conversation, or have the workspace administrator enable the Google Drive connector and restore folder-listing access. A direct download link to the archive (not only the folder URL) can also help if the file is shared for link-based viewing.

After the bytes are available, the safe sequence is: enumerate the folder; identify the exact archive and source root; verify archive and per-file SHA-256; inspect licenses/provenance and reference/adoption documents; compare the full tree to the current intake; run the exact package's declared tests; assess compatibility with the canonical architecture; then update the isolated intake on a separate review branch with a verification report. Do not merge to `main` or issue a production release from an unreviewed local source package.
