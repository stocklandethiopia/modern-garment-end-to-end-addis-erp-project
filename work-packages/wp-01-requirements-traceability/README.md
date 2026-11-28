# WP-01 — requirements and traceability (r3)

**Status:** internal requirements-package verification PASS; canonical application implementation NOT CLAIMED.

The attached source is the corrected r3 requirements deliverable. It is stored as a SHA-256-verified TAR.XZ archive in ordered Base64 text parts because the repository connector's UTF-8 file interface does not write binary Git blobs directly. GitHub Actions reconstructs the exact archive and publishes the source and ZIP as a build artifact.

## Contents and current verified counts
- 128 total requirements across the approved 24 bounded contexts.
- Six Product-Owner-supplied agent/device/capability records are included in the total (122 baseline + 6).
- 45 candidate-source map rows; controlled decisions use `reimplement`, `extend`, `replace`, `exclude`.
- WP-08 route/SOP is explicitly allocated; WP-09 is departmental execution; WP-16 has an exact-SHA release assurance requirement.
- Each requirement has a distinct workflow/control/evidence profile and named acceptance scenario.
- Candidate-source evidence is limited to requirement IDs actually present in the source map.
- All requirement canonical_implementation_status values remain `not_started`.

## Integrity results
A01–A10 and F01–F07 PASS in the source package's strict audit. This is traceability-level verification, not proof of the downstream Laravel/Angular/PostgreSQL implementation. See `docs/WP01_AUDIT_RESULT.md` and `docs/WP01_REVIEW_FINDINGS.md` inside the reconstructed source.

## Rebuild locally
From the repository root:

```bash
cat work-packages/wp-01-requirements-traceability/source/WP01_R3_SOURCE_2026-11-26.tar.xz.b64.part-* | base64 --decode > WP01_R3_SOURCE_2026-11-26.tar.xz
echo '44f69b36d6ed389633d4e305dacfa825fbdffb99d78cc903e3f050761811a6e7  WP01_R3_SOURCE_2026-11-26.tar.xz' | sha256sum --check
tar -xJf WP01_R3_SOURCE_2026-11-26.tar.xz
cd wp01-r2-release/package
node scripts/validate-work-package.mjs
node --test
node scripts/audit-quality.mjs --strict
```

The source archive digest and embedded package checksums are both verified by the repository workflow. For downstream ERP acceptance, use the pinned toolchain and required WP-specific tests.
