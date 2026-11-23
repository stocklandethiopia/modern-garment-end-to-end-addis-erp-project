# Stockland Garment ERP v0.3.0 — Source Intake

This is the isolated source-intake location for the submitted local application. It is not the canonical Modern Garment End-to-End Addis ERP runtime and is not approved for merge, deployment or production release.

## Committed application source

The complete extracted application is committed under:

`source-intake/stockland-garment-erp-local-v0.3.0/source/`

The source root preserves the submitted project structure, including `.github/`, `docs/`, `public/`, `reference-sources/`, `scripts/`, `src/`, `templates/`, `tests/`, and root configuration files.

The GitHub-hosted intake workflow downloaded the original archive from the Product Owner's supplied Google Drive folder, verified the archive SHA-256, extracted it without renaming the internal source paths, verified the internal build manifest, and committed the full application tree. No local terminal, VS Code or Codespace was used for the import.

## Integrity and verification evidence

Original source archive SHA-256:

`5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1`

Internal `BUILD_MANIFEST.json` SHA-256:

`0d7c03dac0be52df3a4699fc9db93b4da8b45cb853980dcc0a62a598568722c0`

The application contains 106 manifest-tracked files plus `BUILD_MANIFEST.json`, for 107 committed application files. The complete file checksum inventory is stored in `SOURCE_TREE_SHA256SUMS.txt`.

GitHub Actions run #7 completed successfully. It verified the source manifest after commit and passed `npm run check`, `npm test`, `npm run verify:manifest`, and `npm run release:gate`.

See `SOURCE_IMPORT_REPORT.md` for the source identity, destination, commit and run evidence. See `SOURCE_AUDIT.md` for the architectural and functional scope comparison.

## Architecture boundary

The submitted package describes itself as a Stockland-specific local Node.js/SQLite application. The canonical ERP baseline requires a customer-neutral multi-tenant SaaS on Angular/Laravel/PostgreSQL. Keep this package under this isolated source-intake folder; do not replace `backend/` or `frontend/`. Any integration or architecture change requires explicit Product Owner change control.
