# Stockland Garment ERP v0.3.0 — Source Intake

This directory preserves the source package supplied for independent audit. It is **not** the canonical Modern Garment End-to-End Addis ERP runtime and is not approved for merge, deployment or release.

## Contents

- `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip` — original uploaded source archive, stored unchanged as ordered Base64 Git blobs (`*.part-XX.b64`) because GitHub's repository contents API accepts text content while this source is a binary ZIP.
- `SHA256SUMS.txt` — user-supplied checksum manifest for the ZIP and its internal build manifest.
- `SOURCE_AUDIT.md` — scope and architecture assessment against the approved WP-00…WP-16 plan.

## Reconstruct the original ZIP

From this folder, concatenate the parts in filename order and decode:

```sh
cat STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip.part-*.b64 | base64 --decode > STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip
sha256sum -c SHA256SUMS.txt
```

The CI workflow reconstructs the ZIP on a GitHub-hosted runner, verifies its SHA-256, extracts it, runs the package's declared checks, and publishes the expanded source as a downloadable workflow artifact.

## Important boundary

The source package describes itself as a Stockland-specific local application using Node.js and SQLite. The canonical product baseline requires a customer-neutral multi-tenant SaaS on Angular/Laravel/PostgreSQL with the full garment value stream. The archive is therefore isolated for comparison and audit. No canonical source files are replaced by this intake.
