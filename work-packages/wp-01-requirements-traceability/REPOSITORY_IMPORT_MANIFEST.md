# Repository import manifest — WP-01 r2 (26 November 2026)

## Branch and scope
- Repository: `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`
- Branch: `feature/wp01-r2-review-bundle-2026-11-26`
- Base: `audit/corrective-action-plan-2026-11-24`
- Base commit: `0d005ab32ab6b0e4c24ac87727f2d9ae3fb12024`
- Status: isolated review candidate; PR to audit branch only; not merged.
- Scope: preserve submitted WP-01 r2 source and add the two separately supplied audit/findings documents plus reproducible build instructions.

## Submitted input integrity
| Input | SHA-256 | Bytes |
|---|---|---:|
| Original WP-01 r2 source archive (reassembled exact content) | `5c0a0ba496a86cac7ae52c70e057d3cc050600d168e362194b8af722c233e2d7` | 46,084 |
| Outer uploaded bundle `files (1).zip` (contains original source ZIP and both Markdown reports) | `a34418886fe89e6653bd3dd8e28e61beff0d0e89a9c396dd40d52dd28ea4bb56` | 83,525 |
| `WP01_AUDIT_RESULT.md` | `65d87f31a9fe11f7eb803eed55d053bfc376de59a9ef0d0f063ca69e8806d65c` | — |
| `WP01_REVIEW_FINDINGS.md` | `71dd2ea423aa5aa1bc2fb2eeebd332c0883dc827c57af50360ab1d26873e69b2` | — |
| Locally assembled repository-candidate ZIP | `315dca44d2bfbacb4bbc6658c70010779b1a7b0447c1731e2b618ed39a5fbc7f` | 94,652 |

The exact original source archive is stored in Base64 segments under `source/`; the GitHub workflow decodes it and verifies the first SHA-256 above before extraction. The incoming outer ZIP is represented by its recorded digest; its contents are preserved individually as the source archive plus both Markdown evidence files.

## Verification
- Embedded source archive checksum file: 20 file checksums previously reported OK.
- Original validator: PASS.
- Original Node test suite: 18 passed, 0 failed.
- Independent structural/integrity audit: 10 PASS, 0 FAIL.
- Owner-decision findings: F01–F06 OPEN.
- F07: NOT RUN. Canonical domain map inspected; no controlled 24-name register exists in that file.
- Application runtime build: NOT APPLICABLE / NOT CLAIMED. This package is a requirements and traceability work package.

## Required disposition
No requirements, mappings, plans, or handover content are silently changed. Do not mark WP-01 accepted until F01–F07 are explicitly dispositioned according to the governance agreement. Do not advance or reorder work packages by implication.
