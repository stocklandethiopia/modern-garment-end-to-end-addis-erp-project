# Architecture and business decisions

**Document ID:** STK-ADR-001  
**Application version:** 0.3.0

## ADR-001 — Use a native Node.js application stack

**Decision:** Node.js HTTP server, built-in SQLite, semantic HTML, CSS and browser JavaScript.  
**Reason:** dependency-light local full-stack delivery with one command to run and standard source files.  
**Effect:** no external npm runtime dependencies or frontend bundler are required.

## ADR-002 — Derive stock from an immutable movement ledger

**Decision:** every receipt, sale, transfer, return, damage and adjustment is represented by a signed inventory movement.  
**Reason:** stock history is traceable and current quantity is reproducible by aggregation.  
**Effect:** correction uses a new compensating movement rather than rewriting an earlier row.

## ADR-003 — Store currency in integer minor units

**Decision:** monetary storage uses integer cents; API values use decimal ETB.  
**Reason:** predictable accounting arithmetic and exact journal balance.  
**Effect:** conversion occurs at API and display boundaries.

## ADR-004 — Branch Manager is the final and sole POS import approver

**Decision:** assigned Branch Manager release is the final business approval and automatically posts the reconciled batch.  
**Reason:** Stockland's stated operational rule.  
**Effect:** Owner/Admin release is denied; no additional Owner/Management approval follows.

## ADR-005 — Stage and reconcile POS imports before posting

**Decision:** uploads are validated and stored in staged batches before posting.  
**Reason:** protect sales, cash totals and inventory from unreviewed file input.  
**Effect:** exact control-total reconciliation precedes Branch Manager release.

## ADR-006 — Use stable transaction identities for retries

**Decision:** POS sales and imports use external identities and source digests.  
**Reason:** repeated request delivery must return the existing transaction rather than post a second sale.  
**Effect:** same sale identity at the same location is unique.

## ADR-007 — Retain upstream source and license notices

**Decision:** third-party reference code remains in a separate provenance directory with its upstream license.  
**Reason:** clear ownership and redistribution boundaries.  
**Effect:** Stockland modules do not silently absorb framework-specific upstream source.

## ADR-008 — Keep local runtime dependency-free

**Decision:** use Node built-ins and no third-party npm runtime dependencies.  
**Reason:** reproducible local setup and reduced package installation requirements.  
**Effect:** runtime requirements are documented in `TECHNICAL_REQUIREMENTS.md`.
