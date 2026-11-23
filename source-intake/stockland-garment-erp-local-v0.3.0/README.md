# Stockland Garment ERP v0.3.0 — Source Intake

This folder is the isolated intake point for the submitted local application source. It is not the canonical Modern Garment End-to-End Addis ERP runtime and is not approved for merge, deployment or production release.

## What happens after the archive parts are committed

The GitHub Actions workflow reconstructs the original ZIP, verifies its exact SHA-256, extracts it on a GitHub-hosted runner, and commits the complete original project tree under:

`source-intake/stockland-garment-erp-local-v0.3.0/source/`

It then runs the app's declared checks and publishes the expanded source as a downloadable workflow artifact. No local terminal, VS Code or Codespaces are required.

## Source archive integrity

The original uploaded ZIP SHA-256 is:

`5e505f3ccccf930d8644187a3143c5277edae2ac9de3bd9f4305945232668fd1`

The archive is transported as ordered Base64 text parts in `archive-parts/`. The GitHub workflow reconstructs the exact binary ZIP before extracting it. The parts are retained so the original bytes remain reproducible.

## Architecture boundary

The submitted package describes itself as a Stockland-specific local Node.js/SQLite application. The canonical ERP baseline requires a customer-neutral multi-tenant SaaS on Angular/Laravel/PostgreSQL. Keep this package under the isolated source-intake folder; do not replace `backend/` or `frontend/`.
