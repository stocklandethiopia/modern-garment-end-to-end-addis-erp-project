# WP-01–WP-16 implementation and verification status

**Snapshot date:** 2026-11-26  
**Latest integrated merge commit:** `bd41c83e141835db3c0f03ac8d711096728a07b4` (PR #9 merged 27 November 2026; PRs #5–#7 are earlier integrated increments).  
**Canonical integration branch:** `delivery/autonomous-wp-01-16`  
**Last verified WP-03 source SHA:** `f0688d04c9d9f28722a783086c7efd15500798a2` (ERP CI #45899259026 and combined WP verification #45899259071 passed; 93 backend tests / 1,079 assertions; 27 Angular tests / 7 files; combined artifact ID 15154948146).  
**Parallel WP-03 implementation branch:** `work/wp03-identity-platform-completion-2026-11-26`.  
**ChatGPT continuation branch:** `agent/chatgpt-wp03-continuation-2026-11-27`; follow-on `PLATFORM_TENANT_REGISTER_VIEWED` audit change is pending exact-head ERP/WP CI.  
**Production status:** NOT RELEASED — full WP-16 production acceptance and the listed professional/operational gates remain outstanding.

## Executive disposition

This branch adds a candidate implementation slice and a combined CI workflow. The recorded exact-head CI run passed and authorizes promotion of verified source increments into the canonical delivery branch under the Product Owner's standing instruction; routine package-by-package owner acceptance is not a release prerequisite. It does **not** establish that WP-01–WP-16 are all complete. A successful migration/test build is a software gate for the code it covers, not a substitute for full domain acceptance, factory/store UAT, independent security/performance testing, data migration rehearsals, backup/restore, rollback, statutory validation or accountable production deployment.

## Work-package evidence

| Work package | Candidate evidence in this snapshot | Acceptance status |
|---|---|---|
| WP-00 | Existing governance baseline and fixed WP sequence are preserved. | Existing approved baseline; not changed here |
| WP-01 | R3 requirements/traceability source, import manifest, checksums, validator, 20 tests and audit build are preserved on the branch. GitHub Actions run #45805045720 succeeded for this package. | Source package verified; requirements are still planning records, not delivered application functions |
| WP-02 | Existing platform/tenant foundation is retained; new migrations consistently use tenant IDs, composite foreign keys and forced RLS. | Combined migration/RLS CI pending |
| WP-03 | Enterprise, tenant, organisation, site and identity foundation | PARTIAL FOUNDATION — PR #9 PLATFORM/LIFECYCLE INTEGRATION CI VERIFIED; PACKAGE ACCEPTANCE OPEN | Tenant provisioning/context, membership/invitation service paths, legal entities/sites/nodes/licensing, tenant role/scope grants, domain-role lifecycle and revocation, non-overlapping effective dates, single-active-owner invariant and owner succession/controlled leave are implemented foundations. PR #9 adds global PLATFORM_ADMIN/PLATFORM_SUPPORT separation, tenant ACTIVE/SUSPENDED/CLOSED lifecycle and tenant entitlement APIs with safe defaults. | PR #9 source SHA `f0688d04c9d9f28722a783086c7efd15500798a2` passed [ERP CI #45899259026](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259026): **93 backend tests / 1,079 assertions**, Angular **27 tests / 7 files**, PostgreSQL 18 migrations/forced RLS, Pint/syntax and dependency audits. [Combined WP verification #45899259071](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259071) passed all four jobs including WP-01 R3 strict integrity and artifact assembly; artifact ID `15154948146`. PR #9 merged at `bd41c83e141835db3c0f03ac8d711096728a07b4`; delivery push/PR CI runs #45899593787 and #45899601895 both passed. **Still open:** tenant export/archive, retention/legal hold/recovery; operator grant expiry/revocation; commercial plan catalogue and module-level entitlement enforcement; effective-dated entity/site/department/warehouse/channel target-resource checks; MFA/OIDC/SSO/recovery; identity/email and invitation/domain-role/ownership UI; platform admin UI; installer/operator, secrets, rollback/deployment; full GAR-ENT/platform acceptance. `SCOPED` domain access remains fail-closed. No operator or commercial plan is seeded. The follow-on global tenant-register read audit is a candidate on the isolated branch and awaits exact-head CI. |
| WP-04 | Existing baseline code is retained. | Not separately revalidated in this snapshot |
| WP-05 | Existing product development/PLM foundation is retained; new production-order creation references approved tech-pack revision. | Not separately revalidated in this snapshot |
| WP-06 | Effective-dated IE standards, work-centres, shifts, capacity slots, production plan versions and worker skill/assignment services are added. | Candidate implementation; PostgreSQL migration, API tests and capacity/overlap tests pending |
| WP-07 | Supplier qualification, sourcing/quote/PO/receipt, material lots, separate fabric rolls and trim lots, idempotent append-only stock ledger and receipt genealogy are added. | Candidate implementation; full AP/3-way match and non-inventory service receipt workflow remain outstanding |
| WP-08 | Versioned conditional route steps, predecessors, optional/subcontract steps and quality gate decisions are added. | Candidate implementation; route-condition evaluation and department SOP coverage need acceptance tests |
| WP-09 | Material issue/return/consumption ledgers and lot genealogy are foundation pieces. | Full cutting/trim/print/embroidery/WIP flow, yield/reconciliation and bundle traceability require end-to-end tests |
| WP-10 | Production order snapshots, operation queues, MES events, skill-checked start, output/defect/downtime and completion logic are added. | Candidate implementation; concurrency/race, line balancing, real device and factory UAT pending |
| WP-11 | Inspections, defects, nonconformance, disposition, CAPA, independent effectiveness review and quality release decisions are added. | Candidate implementation; sampling/quality-plan rules, full test suite and CAPA workflow UAT pending |
| WP-12 | Finished-goods lots, location-aware reservation, customer credit decision evidence, order/shipment records and dispatch ledger are added. | Candidate implementation; returns/claims, POS/e-commerce/marketplace connectors and channel UAT remain outstanding |
| WP-13 | Accounts, periods, balanced journals, separate posting record and reversals foundation are added. | Candidate implementation; full subledger integrations, costing, reconciliation and accounting UAT pending |
| WP-14 | Statutory rule version registry and payroll calculation drafts with professional approval boundary are added. | Candidate foundation only; current Ethiopian legal/tax/pension rates and rules require verified sources and professional approval; bank file/payment/statutory filing not implemented |
| WP-15 | Document file storage metadata, extraction staging/review, integration outbox/retries, scan intake/resolution and agent run/action/approval audit services are added. | Candidate implementation; external connectors, worker delivery, provider runtime, chatbot/voice UI and document importer for each domain are not fully implemented |
| WP-16 | Combined PostgreSQL 18 / Redis 8.2 / PHP 8.5 / Laravel / Node 26 / Angular CI workflow, forced-RLS checks and release-artifact job are added. | Verification workflow authored; run result and end-to-end acceptance must be checked before release |

## Database migrations added by this candidate

- `2026_11_26_000015_create_sourcing_material_traceability.php`
- `2026_11_26_000016_create_route_and_manufacturing_execution.php`
- `2026_11_26_000017_create_quality_capa_and_release_gates.php`
- `2026_11_26_000018_create_distribution_order_fulfilment.php`
- `2026_11_26_000019_create_ie_capacity_and_workforce_controls.php`
- `2026_11_26_000020_create_finance_and_payroll_review_foundation.php`
- `2026_11_26_000021_create_documents_integrations_and_agent_controls.php`
- `2026_11_26_000022_add_receipt_traceability_links_and_immutability_guards.php`
- `2026_11_26_000023_create_finished_goods_inventory_and_reservations.php`
- `2026_11_26_000024_add_finished_goods_location_and_append_only_controls.php`
- `2026_11_26_000025_create_finance_posting_and_agent_uniqueness_controls.php`
- `2026_11_26_000026_create_customer_credit_decision_controls.php`

All must migrate in order on PostgreSQL 18 using the intended migration role. New tenant-owned tables are required to have RLS enabled and forced; the runtime role must be NOBYPASSRLS. Cross-tenant references must remain blocked by composite foreign keys and tenant context.

## Code/API areas added

- Inventory and sourcing ledger services.
- Approved route and MES event services.
- Inspection/CAPA/release services.
- IE standards, capacity planning, workforce skills and assignments.
- Finished-goods quarantine, reservations, customer orders and shipment dispatch.
- Journal approval/posting and statutory-rule-gated payroll drafts.
- Private document storage metadata, extraction staging/review, outbox retry/dead-letter and scan resolution.
- Tenant-role capability authorizer and agent proposal/approval/allowlisted draft-execution service.

## Explicitly not claimed

1. This is not a full production-ready Angular application release. Complete role-specific frontend journeys for every domain are not proven by this backend/API candidate.
2. No full OpenAI/Realtime voice provider integration, model evaluation, guardrail test suite, or production chatbot/voice experience is claimed by the new agent governance service.
3. No live payment, bank-file transmission, payroll disbursement, tax declaration, customs/FX calculation or legal sign-off has occurred.
4. No production database migration, customer data load, or destructive change has been run.
5. No production concurrency/load/soak test, deadlock campaign, backup/restore rehearsal, DR test, rollback drill, independent penetration test or full garment-factory/store UAT is evidenced here.
6. No final **production** release should be claimed until all required WP-16 CI, business, security, operational, recovery and applicable human/professional gates pass. This does not prohibit or delay a verified source/integration release to the canonical delivery branch after exact-head CI and scope-specific software checks pass.

## Required release gates

- [ ] All migrations pass on PostgreSQL 18 in the exact commit CI run.
- [ ] RLS enabled and forced for every tenant table; no tenant context returns zero rows; cross-tenant insert/select/update denied under `app_runtime NOBYPASSRLS`.
- [ ] Raw-material and finished-goods ledgers are append-only to the application runtime role.
- [ ] Laravel unit, feature, API and concurrency tests pass.
- [ ] Angular tests, security audit and production build pass.
- [ ] WP-01 R3 source checksum, validator, tests and audit pass.
- [ ] Each WP has traceability to executable tests and source evidence; all open requirements/findings are reconciled.
- [ ] Frontend end-to-end user journeys pass for merchandising, PLM, sourcing, receiving, production, quality, warehouse/DC, branches, wholesale, POS, returns, finance and HR.
- [ ] Device scan idempotency, offline retry/reconciliation and external integration outbox are tested.
- [ ] AI agent permissions, prompt-injection, A0–A4 risk tiers, approvals, traceability and rollback are independently tested.
- [ ] Production concurrency, peak load, soak, deadlock/race, backup/restore, DR and rollback rehearsals pass.
- [ ] Legal, tax, payroll, banking, statutory, security and business UAT sign-offs are collected where applicable.
- [ ] Exact-SHA combined artifact is created only after every mandatory CI job succeeds; artifact digest and source commit are attached to release evidence.

## Release location

The eventual combined candidate is published by `.github/workflows/wp01-16-completion-verification.yml` as a GitHub Actions artifact named:

`modern-garment-addis-erp-wp01-wp16-candidate`

It is intentionally not a production deployment and must not be represented as such.

## 26 November 2026 — Miret and Angular adoption clarification

Two design/engineering standards have been added on the candidate branch:
- docs/product/miret-system-operating-model.md: product identity, quality at source, monthly-to-hourly efficiency and role-specific technology/agent acceptance, traced to the existing WP-00–WP-16 sequence.
- docs/architecture/angular-current-capabilities-and-adoption-standard.md: observed Angular main/roadmap snapshot, production-ready feature maturity, experimental-feature boundary and project adoption practices.

These are requirement/design clarifications. They do not change the implementation acceptance status in the matrix above. No site-specific time-study calibration, customer AQL/inspection plan approval, certification, complete operational agent rollout or production release is claimed.

## Superseding combined exact-head CI result — 26 November 2026

**PASS:** [Unified WP-01–WP-16 candidate verification run #45883478022](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45883478022), source SHA `14f9420b29b062fe15f9b863f5a23a6d6218dbaf`.

All required jobs passed on the same SHA:
- Laravel / PHP 8.5 / PostgreSQL 18 / Redis 8.2: migrations, forced RLS, Pint, route registration, Composer metadata and security audit, and **78 tests / 967 assertions**.
- Angular 22 / Node 26: dependency audit, unit tests and production build.
- WP-01 R3 source package: archive checksum, validator, package tests and strict quality audit.
- Combined artifact assembly: succeeded.

Artifact: `modern-garment-addis-erp-wp01-wp16-candidate`, GitHub Actions artifact ID `15148571903`, 701,359 bytes, expires 24 February 2027. It contains the combined repository source archive, checksum manifest and build-verification record, keyed to the SHA above.

This changes the **combined candidate CI status** to PASS for source SHA `14f9420b29b062fe15f9b863f5a23a6d6218dbaf` only. It does not change individual WP implementation acceptance statuses or certify end-to-end factory/customer operation. That earlier snapshot was subsequently superseded by exact-head CI on `dbd01b56510e87beebfac454ae49723bfbf5c633`; PR #5 merged at `6bd8990d434d3cbc9f51e58489cc76fb240a83b6`, then PRs #6 and #7 advanced the canonical delivery branch to `f7516de080c24ecd71ce599820e91f8f03ebab56`. Production installer, Codespaces devcontainer, release container images, production rollout, load/soak/concurrency, recovery, external integration UAT, independent security review and legal/customer/factory sign-offs remain separate work.


## Owner standing delivery amendment — 26 November 2026

The Product Owner has expressly replaced routine owner-acceptance stops with continuous verified delivery. ChatGPT is authorized to promote verified work into `delivery/autonomous-wp-01-16` after exact-head CI and relevant technical gates pass, without a further acceptance form, signature or notification acknowledgement. This status file's `NOT RELEASED` language applies only to the **final production ERP release**, not to verified source commits or integration increments.

This candidate still has the package-level limitations recorded above. The remaining production and domain gates must be implemented, tested and reported; they are not waived. The next task-level report must list every WP task, the governing standard, evidence, exact commit/workflow, and completed/partial/blocked/failed/not-run counts. If an external or professional action cannot be completed autonomously, isolate that exact action and continue all other work.


## Latest integrated delivery checkpoint — 26 November 2026

- PR [#5](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/5) is **CLOSED / MERGED**, not draft or pending owner acceptance.
- Destination: `delivery/autonomous-wp-01-16`; latest merge commit: `f7516de080c24ecd71ce599820e91f8f03ebab56`.
- Exact source SHA before merge: `dbd01b56510e87beebfac454ae49723bfbf5c633`.
- Combined run [#45891155076](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45891155076) passed Laravel/PostgreSQL 18/Redis 8.2, forced RLS, Angular 22/Node 26, WP-01 R3 source integrity and final artifact assembly. Backend: **78 tests / 967 assertions**. Angular: **27 tests / 7 files** plus production build.
- General CI [#45891155255](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45891155255) passed; WP-01 R3 [#45891155246](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45891155246) passed.
- Combined artifact: `modern-garment-addis-erp-wp01-wp16-candidate`, ID `15151632103`, 704,980 bytes, expires 24 February 2027.
- The delivery merge commit then passed delivery-branch CI: [push run #45891439094](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45891439094) and [PR run #45891445136](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45891445136). Each passed 78 backend tests / 967 assertions, 27 Angular tests / 7 files, and production Angular build.
- No change to `main` and no production deployment. PR #1 remains the draft release path to `main`; it is not a current WP-03 working branch.

## Active continuation checkpoint — WP-03

The dedicated continuation branch `work/wp03-identity-platform-completion-2026-11-26` was created from the merged delivery commit. The next package remains WP-03 because the accepted tenant/identity architecture and requirements have not yet reached full implementation acceptance.

The current code map is `docs/architecture/application-folder-code-map.md`. It records exact backend/frontend file locations and the active WP-03 service/controller/route/test paths.

Known WP-03 implementation gaps to close and test include:
- Tenant status lifecycle (suspension, closure, retention/export/recovery) and platform entitlements/administration.
- Owner succession/transfer, owner leave and last-owner guard, including concurrent succession/leave tests.
- Effective-dated domain-role grants currently only support TENANT scope. The `DomainCapabilityAuthorizer` intentionally denies members in `SCOPED` mode rather than guessing a target scope; implement approved legal-entity/site/department/warehouse/channel scoping and prove each downstream record access before enabling it.
- Complete role mapping and downstream scope enforcement across existing/future domains.
- MFA, enterprise OIDC/SSO, recovery and refresh/session rotation/reuse detection.
- Invitation administration UI and complete operator provisioning/recovery workflow.
- Installer/operator runbook, secret provisioning/rotation, migration rollback and non-production deployment drill.
- Reconcile WP-03 requirements GAR-ENT-001..010 and the WP-03-assigned platform requirements to executable API/UI/database acceptance evidence.

The Product Owner's 26 November standing amendment removes routine owner acceptance waits; it does not close these tasks. Continue independent safe implementation and keep final production release gated by WP-16.


## Latest WP-03 owner-succession integration — 26 November 2026

- PR [#7](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/pull/7) merged into the canonical delivery branch at `f7516de080c24ecd71ce599820e91f8f03ebab56`.
- Exact candidate source SHA `7d8d3cc8d94c7cb3e763a7b1174030315bd11382` passed ERP CI [#45896122644](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45896122644) and combined WP verification [#45896122613](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45896122613). Backend **86 tests / 1,026 assertions**; Angular **27 tests / 7 files** plus production build; Pint/syntax across 132 files; PostgreSQL 18 migrations/forced RLS; WP-01 R3 integrity and artifact assembly.
- Artifact: `modern-garment-addis-erp-wp01-wp16-candidate`, ID `15154530114`, 726,099 bytes.
- Post-merge destination ERP CI [#45896477349](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45896477349) passed on merge SHA `f7516de...`.
- Owner succession, member self-leave and exact-one-active-owner DB constraints are implemented foundations, not full WP-03 acceptance. Tenant lifecycle, platform entitlements, scoped target-resource authorization, MFA/SSO, operator UI/recovery and full WP-03 acceptance remain open.


## WP-03 platform lifecycle/entitlement integration and follow-on audit — 27 November 2026

- Parallel WP-03 branch: `work/wp03-identity-platform-completion-2026-11-26`; ChatGPT continuation branch: `agent/chatgpt-wp03-continuation-2026-11-27`.
- PR #9 integrated migration `2026_11_26_000032_add_platform_entitlements_and_tenant_lifecycle.php`, `PlatformAdministrationService.php`, platform controllers, routes and seven focused feature tests. It separates PLATFORM_ADMIN/PLATFORM_SUPPORT from tenant OWNER/ADMIN, creates tenant entitlements with `UNASSIGNED / REVIEW_REQUIRED` defaults, and adds reasoned lifecycle transitions and append-only platform audit. No real platform operator or paid entitlement is provisioned.
- Exact source SHA `f0688d04c9d9f28722a783086c7efd15500798a2` passed ERP CI [#45899259026](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259026) (**93 backend tests / 1,079 assertions; 27 Angular tests / 7 files**) and combined WP verification [#45899259071](https://github.com/stocklandethiopia/modern-garment-end-to-end-addis-erp-project/actions/runs/45899259071). Artifact ID `15154948146`, 739,862 bytes. PR #9 merged at `bd41c83e141835db3c0f03ac8d711096728a07b4`; post-merge destination CI #45899593787 and #45899601895 both passed.
- The isolated continuation adds immutable `PLATFORM_TENANT_REGISTER_VIEWED` events for global tenant-directory reads by PLATFORM_ADMIN and PLATFORM_SUPPORT, plus explicit feature-test assertions. This prevents authorized privileged metadata reads from being invisible to audit.
- **Follow-on status:** candidate source branch currently at `e466ecc15de889c59c6b5280686114372ad1b79b`; formatter passed on its two PHP commits, but full exact-head ERP CI and combined WP verification have not yet run on this final source head. The follow-on must pass those gates before integration.
- Remaining work is not waived: tenant export/archive/retention/legal hold/recovery; two-person operator grant expiry/revocation; full feature-gate enforcement and commercial plan approval; platform Angular UI; effective-dated entity/site/department/warehouse/channel authorization on target resources; MFA/OIDC/SSO; invitation/domain-role/ownership UI; installer/operator/secret/rollback/deployment runbooks and full WP-03 acceptance.
