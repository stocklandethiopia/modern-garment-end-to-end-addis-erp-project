# Provider-neutrality and Cloudflare trace audit

**Audit date:** 2026-11-27  
**Repository:** stocklandethiopia/modern-garment-end-to-end-addis-erp-project  
**Audited baseline:** work/wp01-16-completion-2026-11-26  
**Baseline commit:** dbd01b56510e87beebfac454ae49723bfbf5c633  
**Remediation branch:** audit/provider-neutral-storage-2026-11-27

## Executive result

No Cloudflare-specific application integration was identified in the audited branch's tracked file tree or the repository searches performed. No Wrangler configuration, Cloudflare Worker/Pages entry point, Cloudflare tunnel configuration, or Cloudflare-specific runtime dependency was identified. Repository commit-message searches for Cloudflare and Google AI Studio returned no matches.

This does not establish whether a Cloudflare account, DNS zone, tunnel, preview, browser session, or Google AI Studio integration was used outside the Git repository. Those external account and session settings are not represented by GitHub source history and were not accessible through this audit.

The frontend/API design is already primarily host-neutral: Angular API calls use same-origin relative paths, and the production origin is not compiled into those API calls.

## Evidence and classification

| Finding | Evidence | Classification | Disposition |
|---|---|---|---|
| Angular API calls | Reviewed API health and authentication services use relative paths such as /api/v1/health and /api/v1/auth/tokens | Provider-neutral | Keep |
| Developer API proxy | frontend/proxy.conf.json targets localhost:8000 | Local development only | Keep and document as non-production |
| Laravel base URL | APP_URL and FRONTEND_URL are environment settings; example values refer to local development | Environment configuration | Keep configurable; do not bake a production host into source |
| Database and cache | DB_HOST, Redis host/port, and credentials are environment settings; Compose and CI use local service endpoints | Local/CI configuration | Keep; not a public backend-domain dependency |
| Cloudflare | No Cloudflare-specific path/configuration was present in the inspected tree; repository searches for cloudflare, cloudflared, wrangler and trycloudflare returned no indexed code results | No integration identified | No Cloudflare code deletion was justified |
| Commit history | Search for Cloudflare and Google AI Studio commit messages returned no matches | No attributable commit identified | Cannot infer an external browser/session connection from Git |
| Private document storage | Document upload defaulted to the s3 disk, while filesystems config declared local and public disks only | Actual application defect and unnecessary provider assumption | Remedied on this branch by selecting the local private disk through configuration |
| Domain routes/controllers/services | domain-api.php registers sourcing, manufacturing, quality, distribution, finance, planning, agent, device and tenant-role workflows | Core ERP functionality; not Internet domain hosting | Retain |
| Database migrations | Migrations create tenant, inventory, manufacturing, finance, quality, documents, integrations, agent and security schema | Core persistence/security design; not hosting-provider code | Retain |

## Remediation performed on the isolated branch

1. Added filesystems.private_documents_disk, configurable with PRIVATE_DOCUMENTS_DISK and defaulting to the existing local private storage disk.
2. Changed document uploads to default to local private storage instead of an undeclared s3 disk.
3. Added the PRIVATE_DOCUMENTS_DISK=local example setting.
4. Imported RuntimeException in DocumentIntegrationService so its upload-failure path resolves the correct PHP exception class.
5. Removed a duplicate localhost:4200 entry from SANCTUM_STATEFUL_DOMAINS.
6. Added a unit test for the default private disk and non-served local storage.
7. Added provider-neutrality rules to the development/deployment standard.

## What was deliberately not deleted

The term "domain" in this codebase primarily means an ERP business domain or tenant-scoped capability, not an Internet domain name. The controllers, services, routes and migrations in those modules are part of the approved modular-monolith backend. Deleting them would remove actual business workflows and could weaken tenant isolation, authorization, audit history and data integrity.

The backend remains portable: it can be deployed behind a reverse proxy or ingress under an operator-selected origin. The Angular application should continue to call same-origin relative API paths. The local developer proxy is not a production hosting contract.

## Validation and remaining limits

- Source-tree/path inspection and targeted source/configuration review: completed.
- Cloudflare-related repository code/commit searches: no matches returned.
- Local private-disk test: added; CI execution is pending for this remediation branch.
- Full PHP test suite, migrations/RLS, frontend tests and production build: not yet run for this remediation branch.
- External Cloudflare and Google AI Studio account/session state: not accessible from this repository audit.

No baseline branch, default branch, production environment, customer data or live deployment was modified.
