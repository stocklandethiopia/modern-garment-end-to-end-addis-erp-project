# Stockland Garment ERP v0.3.0 — Source Intake

This directory preserves the source package supplied for independent audit. It is **not** the canonical Modern Garment End-to-End Addis ERP runtime and is not approved for merge, deployment or release.

## Contents

- `STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip` — original uploaded source archive, to be uploaded unchanged as the binary ZIP file at this exact path; the current Git branch contains the audit controls and checksum while the raw archive upload remains the final intake step.
- `SHA256SUMS.txt` — user-supplied checksum manifest for the ZIP and its internal build manifest.
- `SOURCE_AUDIT.md` — scope and architecture assessment against the approved WP-00…WP-16 plan.

## Reconstruct the original ZIP

From this folder, concatenate the parts in filename order and decode:

```sh
cat STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip.part-*.b64 | base64 --decode > STOCKLAND_GARMENT_ERP_LOCAL_FULLSTACK_v0.3.0.zip
sha256sum -c SHA256SUMS.txt
```

After the ZIP is committed at this path, the CI workflow verifies its SHA-256, extracts it on a GitHub-hosted runner, runs the package's declared checks, and publishes the expanded source as a downloadable workflow artifact.

## Important boundary

The source package describes itself as a Stockland-specific local application using Node.js and SQLite. The canonical product baseline requires a customer-neutral multi-tenant SaaS on Angular/Laravel/PostgreSQL with the full garment value stream. The archive is therefore isolated for comparison and audit. No canonical source files are replaced by this intake.
