# Integration catalogue and extension interfaces

**Document ID:** STK-EXT-001  
**Application version:** 0.3.0

This catalogue defines supported architecture boundaries for future Stockland integrations. Each extension connects through a dedicated adapter and preserves the existing service, identity, movement-ledger, journal and audit contracts.

| Extension domain | Integration surface | Contract to preserve |
|---|---|---|
| WordPress delivery | Deployment adapter within the Stockland WordPress architecture | Preserve API authorization, database migration and session boundaries |
| ERP/accounting bridge | Dedicated accounting integration service | Use stable source IDs, journal references and reconciliation totals |
| Excel workbook ingestion | XLSX import adapter | Preserve row validation, source checksum and staged release semantics |
| Barcode devices | Browser scanner input and SKU lookup API | Resolve a unique SKU/variant and validate server-side price/stock |
| Offline POS | Durable local queue and synchronization service | Stable sale identity, server deduplication and explicit stock/payment policy |
| Mobile client | Same-origin REST API or authenticated API gateway | Reuse role, location, CSRF/session and source-identity rules |
| Tax/fiscal provider | Dedicated payment/tax integration adapter | Preserve document trace, control totals, idempotency and balanced accounting |
| Advanced analytics | Read-only reporting service over curated views | Respect user location scope and use posted financial/stock records |
| Document management | Secure file metadata and storage adapter | Audit upload/download activity and restrict employee/contract records |
| Enterprise database | SQL adapter and migration plan | Preserve movement-ledger and double-entry posting invariants |

New extension work uses the engineering document template, creates acceptance tests and updates `REQUIREMENTS_TRACEABILITY.csv` and `BUILD_MANIFEST.json`.
