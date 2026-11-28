# Offline POS safety contract

Offline mode is a bounded capability, not a promise of global real-time inventory.

## Sale persistence and retry

1. Persist the sale payload and receipt snapshot locally before network submission.
2. Give every local sale a stable globally unique ID.
3. A timeout, proxy failure or lost response is an uncertain outcome, not proof that posting failed.
4. Retry with the same sale ID. The server must deduplicate and return the already-posted result if it exists.
5. Delete a local pending sale only after a confirmed server response or an explicitly authorized human deletion path.
6. Distinguish transient failures from structured business rejection; keep failed sales visible with their reason.

## Offline blockers

Consider blocking returns, credit sales, partial payments, customer-credit allocations, gift-card activity, unsupported payment modes, incomplete/stale catalogue, wrong branch/profile scope, unavailable local storage and full storage.

Payment modes allowed offline must be explicitly configured by management. An offline payment record is not independently verified by the payment provider until synchronization.

## Stock caution

Two disconnected branches or tills can each sell against stale stock. A local till can limit itself to its last synced quantity less its own queued sales, but cannot guarantee global stock across disconnected tills. Choose a business policy for paid offline sales rejected by the server before enabling offline selling broadly.
