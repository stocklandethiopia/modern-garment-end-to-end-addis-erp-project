# Local Reference Runtime — verification evidence record

**Evidence status:** user-supplied report PLUS independently re-run GitHub Actions evidence for the archived Python source.  
**Role:** reference control fixture only. The report itself explicitly says this is a stand-in, not the canonical stack.

## Supplied report (recorded values)

**Result: PASS** · `2026-11-24T09:35:38+00:00` · Python 3.12.3 · SQLite 3.45.1

### User-supplied run (as stated in the report; environment Python 3.12.3 / SQLite 3.45.1)
- 16 unit/integration tests: 0 failures, 0 errors.
- Full 19-stage replay for one order: 100 cut, 99 good finished goods received, 1 reject; stage 10 skipped by route; final stage 19; FG receipt 99; outbox events 1.
- Reported ledger nets: `{'FABRIC': 120.0, 'TRIM': 600.0, 'CHEMICAL': 5.0, 'PACKAGING': 100.0}`.

### Controls reported as covered
Route-order enforcement; technical-release gate; `UNVERIFIED_WP07` gate; maker-checker for release and sampling plan; four non-combinable append-only ledgers with single reversal; lot acceptance/stock/over-issue; chemical SDS and expiry; cutting/bundle reconciliation; idempotent scan events and key-reuse rejection; independent re-inspection; pack quantity no greater than accepted-good; no default AQL plan and failed audit blocks release; FG receipt exactly once and outbox dedupe; cross-tenant isolation; SAM planning calculation requires approval.

### Explicitly NOT verified by the supplied report
Laravel 13 / PHP 8.5; Angular 22; PostgreSQL 18 and database RLS; real concurrency (SQLite single-writer); HTTP API, UI and authentication; bundle split/merge; reservations; costing/GL; WMS beyond FG receipt; POS/retail/distribution; security, performance, backup/restore; GitHub CI at an exact SHA.

## Seven source-file SHA-256 values declared in the supplied report
| File | Declared SHA-256 |
|---|---|
| `README.md` | `f4f5b4b19a270d0e2f47049e23dd0c2cc444a81a8e90750338cf1a1d18a9b75a` |
| `install.py` | `b08b8e930f019dc72162992fcd335e7b3e43a5061453f29b3eaf7c56106323b1` |
| `stockland_local/__init__.py` | `a813d51ae82dbe56a9885526c4e85955c6c35546d784fc860278f8c4bd393219` |
| `stockland_local/engine.py` | `f7b3997f9fd87bb2e04841fa46c4a458cb6752fdeccf5e4422f2f0909201f379` |
| `stockland_local/scenario.py` | `e30bed48a5a1a72483ff3dad1bd825cfa0c0dd78076bc2f2128a319d336a0c4a` |
| `tests/__init__.py` | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `tests/test_engine.py` | `550d6b3637a23ed6c2ff8bf58c1bb297c194ca7b6035bf998b22e301ceb776fc` |

All seven values were initially transcribed from the supplied report. The corresponding source was subsequently found in the GitHub intake reference archive and independently verified by GitHub Actions run [45397701554](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45397701554).

**Independent result: 7/7 per-file SHA-256 values matched.** The verified source root was extracted from `source/reference-sources/stockland-local-erp-v0.1.zip` (archive SHA-256 `c2124bb8a625c7221c9f9ee8701ae70e1f5b18b235703654ea2a0156e8f02c0b`). The companion `files.zip` SHA-256 was `b8b58c8d25655777ed1e97bafd62f3418975701d17f08c06c3746f4a714e40eb`.

The GitHub runner used Python 3.12.16, executed `python -m unittest discover -s tests -v`, and recorded **16 tests, 0 failures, return code 0**. It independently passed the 19-stage scenario test `test_e2e_full_19_stage_route`. This verifies the Python reference runtime files and tests, not the canonical Laravel/Angular/PostgreSQL stack.

## Independent GitHub-hosted reproduction

- Workflow: [Verify Local Reference Runtime source archives](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45397701554).
- Run ID: `45397701554`; result: **success**.
- Exact workflow head SHA: `5e21f48cc9991213474cd395ab8d9ecddc19e609`.
- Source checkout SHA: `0730df72b382663c13022be6ed5e17bcab6a64a6`.
- Python runner: `3.12.16`.
- Source archive hashes: `files.zip` = `b8b58c8d25655777ed1e97bafd62f3418975701d17f08c06c3746f4a714e40eb`; `stockland-local-erp-v0.1.zip` = `c2124bb8a625c7221c9f9ee8701ae70e1f5b18b235703654ea2a0156e8f02c0b`.
- Seven per-file SHA declarations: **7/7 matched**.
- Python unit tests: **16 passed, 0 failed**.
- The run publishes source and verification evidence as artifact `stockland-local-reference-runtime-verification`, artifact ID `14922111521`, SHA-256 `0014ff217bb9a2a02e00ef768baf0f27b159e7264fdf32c85ba9a86bdc88ebaf`.

This report is a reference runtime re-test only. It does not upgrade its explicit NOT-VERIFIED list below.

## Canonical interpretation
- Stage 10 was omitted because the selected route reportedly did not contain that step. Canonical routing must permit explicit optional/conditional nodes; it must not silently bypass a required operation.
- The 100/99/1/99/1 replay and ledger figures are deterministic test fixtures, not universal business defaults. Define units and cost basis in the canonical test.
- Separate fabric, trim, chemical and packaging movement ledgers; append-only movement; exactly one authorized reversal; accepted-lot/roll controls; expiry/SDS checks; scan idempotency; quality blocking; FG/outbox exactly-once effects; tenant isolation; and approved SAM are useful acceptance criteria.
- SQLite tests do not establish PostgreSQL RLS, concurrent issue/scan correctness, HTTP/UI/auth, full WMS, costing/GL or production recovery.

**Disposition:** preserve as a separate reference-runtime evidence record. Do not substitute it for the v0.3 Node/SQLite source archive or for the canonical ERP.
