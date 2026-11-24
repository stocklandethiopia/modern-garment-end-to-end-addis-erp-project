---
name: agent-tool-implementation
description: Create or change an Operational ERP Agent capability exposed through the Laravel Agent Gateway.
---

# Agent Tool Implementation

1. Keep gateway orchestration separate from domain business logic.
2. Define versioned capability name, purpose, typed input/output, data classification, owner, permissions and risk tier.
3. Call a first-party Laravel application service; never expose unrestricted SQL, shell, filesystem or generic CRUD.
4. Resolve identity and tenant/branch from authenticated server context, not model arguments.
5. Re-check roles, scope, workflow state, amount/quantity limits and maker-checker at execution.
6. Use idempotency and transaction boundaries for retryable multi-record actions.
7. Emit audit and trace events without logging secrets or unnecessary personal data.
8. Use draft/staging/preview before material commits and enforce approvals server-side.
9. Test valid, invalid, unauthorized, cross-tenant, duplicate, concurrent, timeout, outage and rollback cases.
10. Keep conventional ERP UI/API functional if the model provider is offline.

## Deliverables

Capability contract/manifest; Laravel service and policy; audit/outbox behavior; approval matrix; unit/integration/security/concurrency tests; monitoring and disable/rollback instructions.
