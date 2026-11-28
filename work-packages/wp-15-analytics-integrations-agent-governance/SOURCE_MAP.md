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


## Candidate verification checkpoint — 28 November 2026
- PR #23 head `8497cfc76200e1263f0f5ce47afdb668773758bc` is stale/diverged against current delivery (2 ahead / 63 behind; merge base `fc01eced62b97c4ec27f0075264fc8ba0fb11851`). Its associated ERP CI #46016837226 and WP verification #46016837218 concluded FAILURE, but the retrieved job records had `steps: null`; executed code-test status/root cause remains unverified. Its migration 000033 collides with PRs #13 and #20.
- PR #27 head `b6e5486324e0eca18c065b253d1640ea7e6f2416` passed ERP CI #46080661268 and component/WP-01 verification #46080661241 on that candidate SHA; it is unmerged and its migration 000038 participates in the CAP-030 ordering.
- Neither candidate is a delivery integration. Rebase/permission/replay tests and consolidated migration verification remain required. See CAP-025/026/030 in the corrective-action ledger.
