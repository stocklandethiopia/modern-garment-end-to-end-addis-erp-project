# WP-01 r2 — repository candidate

**Status:** review candidate; not accepted; not merged.

This directory contains the WP-01 r2 source package, its review evidence, and a reproducible GitHub Actions build. The files are intentionally isolated on `feature/wp01-r2-review-bundle-2026-11-26` and are proposed against `audit/corrective-action-plan-2026-11-24`, not `main`.

## Package contents

- `source/` — the complete original WP-01 r2 source release, preserved as a SHA-256-verified TAR.XZ archive encoded into ordered Base64 segments because the repository connector writes UTF-8 files. The fifth logical segment is split into `part-05a` and `part-05b`.
- `evidence/WP01_AUDIT_RESULT.md` — the 26 November independent quality audit.
- `evidence/WP01_REVIEW_FINDINGS.md` — the Product Owner disposition register.
- `.github/workflows/wp01-r2-build.yml` — reassembles the source, verifies its hash and embedded checksums, runs validation/tests/audit, and publishes ZIP and TAR.XZ outputs as a GitHub Actions artifact.

## Reassemble the exact source locally

Run from this directory:

```bash
cat source/WP01_R2_SOURCE_2026-11-26.tar.xz.b64.part-* | base64 --decode > WP01_R2_SOURCE_2026-11-26.tar.xz
echo '5c0a0ba496a86cac7ae52c70e057d3cc050600d168e362194b8af722c233e2d7  WP01_R2_SOURCE_2026-11-26.tar.xz' | sha256sum --check
tar -xJf WP01_R2_SOURCE_2026-11-26.tar.xz
```

The ordered fragments are all ASCII Base64. The exact expected TAR.XZ SHA-256 is recorded above and rechecked by CI.

## Local verification

With Node.js 22 installed:

```bash
cd wp01-r2-release
sha256sum -c CHECKSUMS.sha256
cd package
node scripts/validate-work-package.mjs
node --test
node scripts/audit-quality.mjs
```

The submitted release passed its original embedded checksums, validator, and 18 automated tests in the local review. The independent audit passed 10/10 structural/integrity checks. It separately records six open Product Owner findings and one check not run; those are not converted into passes by this build.

## Current audit disposition

- **PASS:** A01–A10 integrity checks.
- **OPEN:** F01–F06, requiring explicit Product Owner decisions and/or corrections.
- **NOT RUN:** F07, by-name confirmation of the approved 24 contexts. The canonical `docs/product/domain-map.md` is a domain narrative and does not expose a controlled list of 24 context names, so this check cannot be closed by count alone.
- All 127 register entries remain `not_started`; this is planning/traceability, not a claim of canonical implementation.
- Correct count is 127 total records, including six Product-Owner-supplied agent/device records (121 baseline + 6), not 127 plus six.

## Important boundary

The output is a WP-01 requirements/traceability package with scripts and tests. It is **not** a compiled Angular/Laravel application, production release, or evidence that domain workflows, database migrations, tenant RLS, or production transaction concurrency have been implemented by WP-01. The 6 open decisions and F07 must be dispositioned before WP-01 acceptance under the project governance.
