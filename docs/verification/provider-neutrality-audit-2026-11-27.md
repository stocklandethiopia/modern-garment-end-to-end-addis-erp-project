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


## Exact repository fingerprint checks requested on 2026-11-27

The following searches were performed against the repository's indexed source and configuration. All returned zero matching files:

| Fingerprint or dependency family | Result |
|---|---:|
| Supplied Google/Firebase project ID | 0 |
| Supplied Google Cloud project number | 0 |
| Supplied Android application package | 0 |
| Supplied Firebase Storage bucket hostname | 0 |
| Both supplied OAuth client IDs | 0 |
| Exact supplied API-key fingerprint and API-key prefix | 0 |
| Firebase domains: firebaseapp.com, firebaseio.com, firebasestorage.app | 0 |
| Google API hosts: googleapis.com, identitytoolkit, securetoken, Firestore, Storage and Generative Language API endpoints | 0 |
| Firebase initialization names, Firebase Admin SDK, Firebase Storage/Auth SDK, google-services.json, GoogleService-Info.plist, firebase.json and .firebaserc | 0 |
| AngularFire, Firebase Admin, Google Cloud Storage and Google auth client packages | 0 |
| Cloudflare, cloudflared, Wrangler, Workers/Pages and tunnel-token fingerprints | 0 |

The source-tree review found no Firebase, Android or Cloudflare configuration file names. The inspected Angular manifest contains Angular framework/tooling dependencies and no Firebase/Google client SDK. The inspected Composer manifest contains Laravel/Sanctum and ordinary PHP dependencies and no Firebase/Google server SDK. Composer/npm lockfiles showed no Firebase/Google SDK dependency in the prior dependency review. The audit did not copy any provided key, project configuration or OAuth identifier into the repository.

**Search limitation:** GitHub's indexed code-search endpoint searches the repository's default branch. The supplied work-branch tree and source/configuration paths were also inspected directly; the current report does not claim that GitHub's indexed search alone exhaustively searches every historical Git blob, inaccessible secret store, deleted file or external account. No conclusion about Google AI Studio/Cloudflare account resources can be drawn from Git alone.

## Core ERP versus external API boundary

The user clarified that Google AI Studio is intended only as a source-code authoring/commit workflow. The ERP must not require Firebase, Google Cloud, Cloudflare, an AI vendor, or any provider-specific public URL to boot, authenticate users, create business records or perform core workflows. The Angular UI continues to use the same-origin Laravel API. That Laravel API is an internal application contract, not a third-party-hosted API dependency.

No external AI agent provider key or connector is required or configured in this core. Future agent integrations are deferred until explicitly designed and must be optional backend-side adapters: disabled by default, secrets injected only at runtime/server-side, independently permissioned and tenant-scoped, and covered by tests that pass with no provider key configured. Angular/browser bundles must never contain provider API keys. Provider unavailability must not disable core ERP transactions.

## Final CI evidence

**PASS** on exact source commit `6cd5dca97aa01ef9572bbdce54d0ab3c0d104faf`: [GitHub Actions run 45899378369](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899378369).

All required jobs completed successfully:
- PHP 8.5 / Laravel backend: dependency install and Composer security audit, controlled PostgreSQL migrations, forced-RLS checks, Pint formatting, Laravel unit/integration tests, and route registration.
- Angular 22 / Node 26: npm dependency audit, Angular unit tests and production build.
- WP-01 r3 source package: checksum/integrity verification and source-package tests.

No provider credentials were required by this run. The verification workflow confirms the provider-neutral storage code and new local-disk unit test compile and pass alongside the existing migration/RLS and application suites. This is non-production CI evidence; it does not verify external Google/Cloudflare account state or constitute a production deployment.
