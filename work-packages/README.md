# WP-01–WP-16 completion candidate

This directory contains the candidate database migrations, Laravel service/API code and verification workflow for the next Modern Garment End-to-End Addis ERP delivery review.

**Current status: NOT RELEASED.** See `docs/verification/WP01-WP16-implementation-and-verification-status-2026-11-26.md` for the evidence matrix and remaining acceptance gates.

- Migrations: `backend/database/migrations/2026_11_26_000015_*` through `000026_*`
- Domain services: `backend/app/Services/{Inventory,Sourcing,Manufacturing,Quality,Planning,Distribution,Finance,Integration,Agents,Authorization,Tenant,Support}`
- API controllers: `backend/app/Http/Controllers/{Sourcing,Manufacturing,Quality,Planning,Distribution,Finance,Integration,Agents}`
- Domain routes: `backend/routes/domain-api.php`
- CI: `.github/workflows/wp01-16-completion-verification.yml`

A passing source-package build is not equivalent to completed runtime feature acceptance. Do not merge to the delivery branch or release until the exact-head combined CI and the remaining human/professional gates are complete.
