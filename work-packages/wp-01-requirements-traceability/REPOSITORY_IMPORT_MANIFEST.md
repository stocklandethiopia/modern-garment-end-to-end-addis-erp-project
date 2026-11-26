# WP-01 r3 repository import manifest

- Repository: `stocklandethiopia/modern-garment-end-to-end-addis-erp-project`
- Integration branch: `work/wp01-16-completion-2026-11-26`
- Intended review base: `delivery/autonomous-wp-01-16`
- Source archive: `WP01_R3_SOURCE_2026-11-26.tar.xz`
- Source archive SHA-256: `44f69b36d6ed389633d4e305dacfa825fbdffb99d78cc903e3f050761811a6e7`
- Repository archive ZIP SHA-256: `72808c5c8d8a74996c7ec3732483ffe2b249ac4c1d50b6b5b599b973ff4b4bff`
- 128 requirements, 24 controlled bounded contexts, 45 map rows.
- 20 Node tests pass; strict audit A01–A10/F01–F07 passes.
- Every requirement remains `not_started`; no application implementation is claimed by WP-01.
- The source archive is represented as ordered Base64 segments in Git; CI rebuilds it, checks SHA-256 and embedded checksums, executes validator/tests/audit and publishes binary source and ZIP outputs.

The uploaded r3 source is a corrective revision of the user-supplied r2 source. The original r2 archive remains available separately in the conversation. Changes to r3 are documented in its release notes and WP01_REVIEW_FINDINGS.md.
