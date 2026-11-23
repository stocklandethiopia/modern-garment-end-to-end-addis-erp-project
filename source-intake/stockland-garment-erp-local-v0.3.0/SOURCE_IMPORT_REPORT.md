# Source import report — Stockland Garment ERP v0.3.0

## Source identity

- Application: Stockland Garment ERP — Full-Stack Local Application
- Version: 0.3.0
- Source archive: `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip`
- Source archive location: Product Owner's supplied public Google Drive folder
- Google Drive folder ID: `1Kl3hwG0yeyncn_8Gw3Dr3QrVCciArX3U`
- Downloaded archive SHA-256: `5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1`
- Result: archive SHA-256 matched the previously verified uploaded ZIP

## Extraction and committed destination

- Extraction performed on a GitHub-hosted Actions runner after the archive checksum passed
- Application root within repository: `source-intake/stockland-garment-erp-local-v0.3.0/source/`
- Original application directory layout preserved; no source code or document contents were reformatted
- Expected directories present: `.github/`, `docs/`, `public/`, `reference-sources/`, `scripts/`, `src/`, `templates/`, `tests/`
- Application file count committed: 107 total files
- Internal build manifest: `source/BUILD_MANIFEST.json`
- Maintained files recorded by manifest: 106
- Build manifest SHA-256: `0d7c03dac0be52df3a4699fc9db93b4da8b45cb853980dcc0a62a598568722c0`
- Full imported file checksum inventory: `SOURCE_TREE_SHA256SUMS.txt` (107 entries)

## GitHub commit and verification

- Branch: `audit/stockland-local-source-v0.3.0`
- Source import commit: `78ab018afaaa231b50c52b3a20fc33de37599f6c`
- GitHub Actions run: [run #7 — successful](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45266965264)
- Manifest verification after commit: PASS
- `npm run check`: PASS
- `npm test`: PASS
- `npm run verify:manifest`: PASS
- `npm run release:gate`: PASS
- Import timestamp (UTC): 2026-11-23T21:10:55Z

The source tree is committed in the isolated intake folder. The ZIP itself is not committed as a binary; its checksum was verified before extraction. This import does not modify `main` or `delivery/autonomous-wp-01-16`, and does not authorize merging this local Node.js/SQLite application into the canonical ERP runtime.
