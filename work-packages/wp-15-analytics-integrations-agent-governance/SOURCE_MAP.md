# WP-15 — analytics, integrations, intelligent operations and agent governance source map

**Disposition:** integration outbox, document, device and agent-governance foundations exist; complete connector lifecycle, event reliability, KPI catalogue and human-governed intelligent operations remain partial.

## Canonical source paths
- Controllers: `backend/app/Http/Controllers/{Agents/AgentGovernanceController,Integration/DocumentIntegrationController}.php`.
- Services: `backend/app/Services/{Agents/AgentGovernanceService,Integration/DeviceScanResolutionService,Integration/DocumentIntegrationService,Integration/IntegrationOutboxService}.php`.
- Baseline migration: `backend/database/migrations/2026_11_26_000021_create_documents_integrations_and_agent_controls.php`.
- Specs: `docs/analytics/integration-ai-control-tower.md`, `docs/references/reference-repositories.md`.
- Candidate PR #23: agent/device runtime transition grants, migration named `000033_grant_agent_and_device_runtime_transition_columns.php` on `fix/wp15-agent-device-runtime-grants-2026-11-27`.
- Candidate PR #27: private document-storage/RLS inventory corrections and quality test migration 000038 on `work/fix-private-document-storage-rls-inventory-2026-11-27`.

## Collected source/artifacts
- X POS limited reference files: durable invoice submit/idempotency and offline policy examples.
- Local app integration catalogue documents barcode, spreadsheet, document management, accounting and analytics adapters; not full canonical connector runtime.

## Remaining build / verification
Complete tenant-scoped connector registry, secret references, webhook/EDI contracts, outbox/inbox retries, dead-letter/replay/reconciliation, device registration and trust, scan provenance, object storage retention/access, BI KPI dictionary and event contracts. AI/optimization must provide explainable recommendations with human approval for consequential mutations. Verify no cross-tenant leaks, exact one-effect retry, replay and security grants on restricted roles.
