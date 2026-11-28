# Local release gates

**Document ID:** STK-REL-001  
**Application version:** 0.3.0

## Gate sequence

Execute all commands from the project root, using the declared Node.js runtime:

```bash
npm run manifest:build
npm run check
npm test
npm run verify:manifest
npm run release:gate
```

| Gate | Acceptance rule | Evidence |
|---|---|---|
| Runtime | Node.js 22.5.0+ | Node version output |
| Static syntax | Every application/test JavaScript file parses | `npm run check` |
| Functional suite | All business acceptance scenarios pass | `npm test` |
| Documentation | All required documents and folder guides exist | `npm run release:gate` |
| Traceability | CSV has required columns and a populated record for each requirement ID | Local release gate |
| Markdown | Every relative Markdown link resolves inside the package | Local release gate |
| HTTP/PWA | Health, UI, JS/CSS, PWA manifest, service worker, icon and CSV template are served | Local release gate and integration tests |
| Security headers | CSP, frame denial and nosniff response headers are present | Local release gate |
| Package integrity | Manifest file list, byte size and SHA-256 match | `npm run verify:manifest` |
| CI workflow | Same command sequence runs on GitHub Actions Node 22.x | `.github/workflows/ci.yml` |

## Release evidence package

`BUILD_MANIFEST.json` records the release version, date, Node runtime and every project file except the manifest itself, including exact bytes and SHA-256. ZIP archives are generated outside the project root and have their own SHA-256 checksum.

## GitHub Actions job

The workflow checks out the source, configures Node 22.x, runs syntax checks, verifies the build manifest, executes integration tests and runs the documentation/HTTP/security release gate. A GitHub-hosted run produces the standard Actions job result and log evidence.

## Local release report

The current execution record is maintained in [Verification report](VERIFICATION_REPORT.md). Run `npm run manifest:build` after the final source and documentation change so the SHA-256 manifest represents the exact packaged source; then run all validation gates.
