# `scripts/` — project automation

**Owner:** Application Engineering / Release Engineering  
**Application version:** 0.3.0

| File | Command | Purpose |
|---|---|---|
| `bootstrap.mjs` | `npm run bootstrap` | Interactive first-owner setup, password hashing and five-location initialization |
| `build-manifest.mjs` | `npm run manifest:build` | Generate the complete SHA-256 build inventory for the current source tree |
| `check.mjs` | `npm run check` | Run Node.js syntax checks on JavaScript and MJS files throughout the project |
| `release-gate.mjs` | `npm run release:gate` | Verify documentation, traceability, package metadata, database initialization, HTTP, PWA assets and security headers |
| `verify-manifest.mjs` | `npm run verify:manifest` | Compare every manifested path, byte count and SHA-256 digest with the source tree |

## Automation conventions

- Bootstrap creates the initial Owner account after validating the supplied name and email; the Owner password is hashed before storage.
- Runtime secrets come from the environment and stay outside version control.
- Release gates use a fresh in-memory database and an ephemeral local HTTP port.
- CI runs syntax, manifest, integration and release gates as separate visible steps.
- Add new automation to `package.json`, the documentation inventory and the release gate where appropriate.

## Related documents

- [Operations runbook](../docs/OPERATIONS_RUNBOOK.md)
- [Release gates](../docs/RELEASE_GATES.md)
- [Verification report](../docs/VERIFICATION_REPORT.md)
