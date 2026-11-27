# POS import and release contract

This is the required Stockland sequence:

`UPLOAD → VALIDATE → STAGE → RECONCILE → BRANCH MANAGER RELEASE → AUTO-POST`

## Rules

- File upload is intake only. It is not approval.
- Validation, duplicate detection, mapping, calculation, variance checks and reconciliation are automatic.
- A staged batch cannot alter final posted sales, cash totals or inventory balances.
- The Branch Manager performs the final and sole business release for that branch's batch.
- After a successful release, final posting runs automatically and atomically.
- Do not add another Owner/Management approval after Branch Manager release.
- Record uploader, releasing Branch Manager, file digest, batch key, row counts, rejected rows, variance totals, release timestamp, posting result and immutable audit references.
- Re-importing the same file or source batch must resolve to the same batch/result and must not double-post.
- Failed posting must leave the batch in a recoverable state with no partially posted lines.
- A release action must be explicit; uploading, previewing, reconciling or opening the screen must not set the approval flag.

## Acceptance scenarios

1. Upload-only batch remains staged and unposted.
2. Any row validation error is surfaced before release; invalid rows cannot leak into final posting.
3. Reconciliation mismatch blocks release until resolved according to policy.
4. Non-Branch-Manager cannot release.
5. Branch Manager release triggers one posting attempt.
6. Repeated click / request retry does not duplicate posting.
7. The audit trail identifies the same Branch Manager who released the batch.
8. No subsequent Owner approval state exists in this workflow.
