# Source-to-application path index — 28 November 2026

**Canonical integration SHA at index creation:** `dbdf112272f7f1e0d1ccf933e45061a79cc5e59b`  
**Source-intake working branch:** `work/source-intake-and-wp-mapping-2026-11-28`

## Repository ownership
| Repository path | Responsibility | Source authority |
|---|---|---|
| `backend/app/Http/Controllers/{Domain}/` | HTTP validation, authorization invocation and response contracts | Canonical Laravel application |
| `backend/app/Services/{Domain}/` | Business use cases, transactional orchestration, domain controls and audit | Canonical Laravel application |
| `backend/database/migrations/` | Ordered PostgreSQL schema, constraints, indexes, RLS and security functions | Canonical database history |
| `backend/routes/` | API route registration and middleware boundary | Canonical Laravel application |
| `backend/tests/{Feature,Unit}/` | Service/API/PostgreSQL/RLS regression and acceptance tests | Canonical verification |
| `frontend/src/app/features/{domain}/` | Angular operator workflows and forms | Canonical Angular application |
| `docs/{product,architecture,localization,security,manufacturing,finance,hr,quality,distribution}/` | Approved product/domain rules and operator controls | Product and domain specifications |
| `work-packages/wp-XX-*/SOURCE_MAP.md` | WP-scoped intake→target path→gap→evidence map | Work-package source register |
| `source-intake/stockland-garment-erp-local-v0.3.0/source/` | Preserved original application source and artifacts | Historical source/reference only |

## Actual domain-to-code locations
- WP-03 tenant/identity: `backend/app/Http/Controllers/Enterprise/`, `backend/app/Services/Tenant/`, `backend/app/Services/Authorization/`, `backend/app/Http/Middleware/`, `frontend/src/app/features/{auth,enterprise}/`.
- WP-04 merchandising: `backend/app/Http/Controllers/Merchandising/`, `backend/app/Services/Merchandising/`, `frontend/src/app/features/merchandising/`.
- WP-05 PLM: `backend/app/Http/Controllers/ProductDevelopment/`, `backend/app/Services/ProductDevelopment/`, `frontend/src/app/features/product-development/`.
- WP-06/WP-10 IE: `backend/app/Http/Controllers/Planning/`, `backend/app/Services/Planning/`.
- WP-07 sourcing and inventory: `backend/app/Http/Controllers/Sourcing/`, `backend/app/Services/Sourcing/`, `backend/app/Services/Inventory/`.
- WP-08/WP-09 MES: `backend/app/Http/Controllers/Manufacturing/`, `backend/app/Services/Manufacturing/`.
- WP-11 quality: `backend/app/Http/Controllers/Quality/`, `backend/app/Services/Quality/`.
- WP-12 distribution/WMS: `backend/app/Http/Controllers/Distribution/`, `backend/app/Services/Distribution/`.
- WP-13 finance: `backend/app/Http/Controllers/Finance/`, `backend/app/Services/Finance/`.
- WP-14 HR/payroll: `docs/hr/`, current review foundation migration `2026_11_26_000020_*`; dedicated employee lifecycle/payroll runtime must be mapped when built/accepted.
- WP-15 integrations/AI: `backend/app/Http/Controllers/{Integration,Agents}/`, `backend/app/Services/{Integration,Agents}/`.
- WP-16 assurance: `.github/workflows/`, `backend/tests/`, frontend specs, release and verification docs.

## No duplicate app tree
The package folders under `work-packages/` are evidence indexes. Runtime modules must be placed in the existing canonical backend/frontend/migration/test locations and then linked from the relevant WP map. Do not create a second runtime under `work-packages/` or copy the Node/SQLite local source as the production app.
