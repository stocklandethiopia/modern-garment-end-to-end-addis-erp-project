# Stockland Garment ERP v0.3.0 — Source Intake

This folder is the isolated intake point for the original source archive supplied for audit. It is **not** the canonical Modern Garment End-to-End Addis ERP runtime and is not approved for merge, deployment or release.

## Files in this folder

- `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip` — upload the original ZIP here unchanged.
- `SHA256SUMS.txt` — supplied checksum record.
- `SOURCE_AUDIT.md` — comparison with the canonical WP-00…WP-16 plan.

## Upload from GitHub's browser interface

1. Open branch `audit/stockland-local-source-v0.3.0`.
2. Navigate to this folder.
3. Select **Add file → Upload files**.
4. Upload the original `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip` without extracting or modifying it.
5. Commit to `audit/stockland-local-source-v0.3.0`, not `main`.

The workflow is configured to start when that exact ZIP path is committed. It verifies the archive SHA-256, extracts the files on a GitHub-hosted runner, runs `npm run check`, `npm test`, `npm run verify:manifest` and `npm run release:gate`, then publishes an expanded-source artifact for download.

No local terminal, VS Code or Codespaces are required. Do not create a production release from this intake branch.

## Architecture boundary

The source describes itself as a Stockland-specific local application using Node.js and SQLite. The canonical ERP baseline is a customer-neutral multi-tenant SaaS on Angular/Laravel/PostgreSQL with the full garment value stream. Preserve this package separately for audit; do not copy it over `backend/` or `frontend/`.
