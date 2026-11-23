# Stockland Garment ERP v0.3.0 — Source Intake

This is the isolated source-intake location for the submitted local application. It is not the canonical Modern Garment End-to-End Addis ERP runtime and is not approved for merge, deployment or production release.

## Automated extraction and source commit

The original archive was integrity-checked and extracted. A lossless TAR.XZ transfer bundle is stored as ordered Base64 parts under `archive-parts/`. GitHub Actions reconstructs and SHA-256-verifies that bundle, extracts it on a GitHub-hosted runner, and commits the full application tree under:

`source-intake/stockland-garment-erp-local-v0.3.0/source/`

The extracted directory preserves the submitted project structure: `src/`, `public/`, `docs/`, `tests/`, `scripts/`, `templates/`, `reference-sources/`, `.github/`, and the root package/configuration files.

The same workflow runs the package's declared checks and publishes the expanded source as a downloadable workflow artifact. No local terminal, VS Code or Codespaces are required.

## Integrity evidence

Original uploaded ZIP SHA-256:

`5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1`

Lossless transfer TAR.XZ SHA-256:

`0b965ccc8f4e34e9887790f37ff6de96127ab53066db074725ce290b4d40cd9d`

The application's internal `BUILD_MANIFEST.json` records the SHA-256 and byte size of all 106 maintained source/documentation files. The GitHub workflow runs `npm run verify:manifest` after extraction.

## Architecture boundary

The submitted package describes itself as a Stockland-specific local Node.js/SQLite application. The canonical ERP baseline requires a customer-neutral multi-tenant SaaS on Angular/Laravel/PostgreSQL. Keep this package under this isolated source-intake folder; do not replace `backend/` or `frontend/`.
