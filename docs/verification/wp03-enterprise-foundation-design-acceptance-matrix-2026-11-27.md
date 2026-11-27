# WP-03 Enterprise Foundation — Design Acceptance Matrix and Traceability

**Document ID:** WP03-AT-001 · **Version:** 1.0 · **Date:** 27 November 2026  
**Status:** Design and test specification; not execution evidence  
**Parent design:** docs/architecture/wp03-enterprise-foundation-implementation-lld-2026-11-27.md

## 1. Status vocabulary

- PASS: exact command/test executed against the exact recorded SHA and passed.
- FAIL: test actually executed and failed.
- NOT RUN: test source or plan exists, but execution did not occur.
- BLOCKED: a prerequisite or external condition prevents execution.
- IMPLEMENTED — NOT VERIFIED: source exists but exact verification is absent.
- REVIEW REQUIRED: legal/customer/qualified professional determination is incomplete.

A CI run that fails before steps/logs start is infrastructure-blocked and does not prove a test pass or code defect.

## 2. Current evidence snapshot

| Evidence | Branch / SHA | State | What it does and does not prove |
|---|---|---|---|
| Canonical delivery | delivery/autonomous-wp-01-16 @ fc01eced62b97c4ec27f0075264fc8ba0fb11851 | Integrated baseline; prior CI evidence applies only to its included source | Includes tenant, legal entity, site, node, inventory location, membership/invitation, domain role, owner succession and platform lifecycle/entitlement foundations. Not complete WP-03. |
| Organogram/DoA design | work/wp03-wp14-organogram-payroll-plan-2026-11-27; design source and PR #16 lineage | Design direction available; not merged into delivery | Defines configurable governance, position authority, DoA, P2P separation and legal boundaries. Not runtime acceptance. |
| Combined WP03/WP14 plan | branch work/wp03-wp14-organogram-payroll-plan-2026-11-27; PR #19 | Source plan available; unmerged | Defines sequence and detailed packages. Not runtime acceptance. |
| Org runtime candidate | work/wp03-org-master-runtime-2026-11-27; PR #21; latest known head f9aebe855a05dec30f0b1f4bc5f1acbd741f385b | IMPLEMENTED — NOT VERIFIED | Adds org units, job-position slots, unit manager-position assignments, position reporting APIs and initial Angular workbench. Human appointments/DoA engine remain out of scope. Exact-head workflows failed before steps/logs; test files NOT RUN. |
| This LLD and legal note | work/wp03-full-engineering-design-2026-11-27 | Design source added; no runtime code changes | Architecture, API/event contracts, sequence, security, legal-source analysis and acceptance plan. Does not prove application execution. |

## 3. Requirements crosswalk

This matrix supplements (does not supersede) docs/verification/WP03-implementation-acceptance-matrix.md and the canonical GAR IDs.

| Requirement family | GAR / platform link | Design coverage | Current status |
|---|---|---|---|
| Tenant isolation and legal entity boundary | GAR-ENT-001 / AT-ENT-001 | Tenant context, composite FK, RLS, site/entity checks and target-resource denial | PARTIAL; existing foundations; downstream resource coverage incomplete |
| Multi-entity setup | GAR-ENT-002 / AT-ENT-002 | Entity identifiers, fiscal/legal boundary, per-entity context and approvals | PARTIAL; existing entity master; lifecycle and entity-aware workflow acceptance open |
| Physical network | GAR-ENT-003 / AT-ENT-003 | Site, node, branch registration, capability and inventory location distinction | PARTIAL; current tables exist; legal/capability/effective-date acceptance open |
| Brand/channel/account/partner separation | GAR-ENT-004 / AT-ENT-004 | Independent master data and transaction references | PARTIAL; complete cross-entity/channel/partner mapping and gates open |
| Least privilege and scoped authorization | GAR-ENT-005 / AT-ENT-005 | RBAC+ABAC, appointment, resource scope, DoA resolver and fail-closed behavior | BLOCKED for incomplete scoped-member target checks; preserve denial until implemented |
| Maker-checker / SoD | GAR-ENT-006 / AT-ENT-006 | Position-based approval steps, conflict and delegation rules | PARTIAL; existing local maker-checker; central DoA workflow not implemented |
| Effective-dated critical master data | GAR-ENT-007 / AT-ENT-007 | Entity/site/org/position/appointment/policy validity and historical snapshots | PARTIAL; new candidate master slice only |
| Audit evidence | GAR-ENT-008 / AT-ENT-008 | Actor/scope/reason/correlation/source hash/outbox and immutable decisions | PARTIAL; no blanket tamper-proof claim; retention/export/integrity tests open |
| Licensing/activity/site gate | GAR-ENT-009 / AT-ENT-009 | Legal source register and entity/site/activity/channel applicability | REVIEW REQUIRED for affected activities and regional permits |
| Privacy/retention/data subject | GAR-ENT-010 / AT-ENT-010 | Classification, purpose, masking, retention/hold and access audit | PARTIAL / REVIEW REQUIRED |
| Architecture boundary | GAR-PLT-001 / AT-PLT-001 | Modular monolith and bounded context ownership | PARTIAL; add machine-enforced dependency checks |
| Transaction integrity/concurrency | GAR-PLT-003 / AT-PLT-003 | Transaction, row/advisory lock, idempotency and replay | PARTIAL; deterministic adversarial concurrency suite open |
| Redis non-authoritative | GAR-PLT-004 / AT-PLT-004 | PostgreSQL system of record, outbox replay | NOT RUN for Redis failure injection |
| REST/OpenAPI | GAR-PLT-005 / AT-PLT-005 | Versioned APIs, DTO, error, idempotency and OpenAPI 3.1 | PARTIAL; enterprise/identity contracts require full schemas and compatibility tests |

## 4. Detailed acceptance cases

### A. Tenant, entity and location

| ID | Test | Expected result | Required evidence |
|---|---|---|---|
| WP03-AT-001 | Read/write/search/export another tenant's org, identity, appointment or policy | Denied at API and RLS | API test under app_runtime; database-policy assertion |
| WP03-AT-002 | Link an org unit to a legal entity from another tenant | Rejected | Composite FK + validation test |
| WP03-AT-003 | Link business site to a different legal entity than selected entity | Rejected | Composite key + API test |
| WP03-AT-004 | Register operational branch and attempt stock movement without stock capability/location | Denied | Capability test and no inventory balance created |
| WP03-AT-005 | Use expired branch/licence/activity record for a restricted sale | Held / REVIEW REQUIRED | Legal applicability test with effective-dated fixtures |
| WP03-AT-006 | Disable or suspend tenant then call domain APIs/background jobs | Denied; no unauthorized job execution | Middleware, queue and session revocation tests |

### B. Organization and position

| ID | Test | Expected result | Required evidence |
|---|---|---|---|
| WP03-AT-007 | Create org unit with valid parent/date/entity/site | Created in tenant with audit/outbox | Migration/API/integration test |
| WP03-AT-008 | Parent cycle, self-parent or child date outside parent validity | Rejected | DB/API graph and temporal tests |
| WP03-AT-009 | Create position under inactive/nonexistent/wrong-tenant org unit | Rejected | FK and API test |
| WP03-AT-010 | Attach user_id or tenant membership directly to job position | Rejected | Validation/API and schema inspection |
| WP03-AT-011 | Add overlapping primary solid-line manager | Rejected | Date-overlap concurrency test |
| WP03-AT-012 | Add date-overlap solid-line reporting cycle | Rejected | Recursive graph test using intersecting valid-time windows |
| WP03-AT-013 | Add functional reporting relationship | Separate relationship type; no line-management authority inferred | API/authorization test |
| WP03-AT-014 | Query org tree as of historic date | Correct historic unit/position/reporting state | Effective-date fixture and as-of query |
| WP03-AT-015 | Abolish or move a position that has live appointments/approvals | Block or transition through an approved lifecycle command; history retained | Lifecycle/domain test and migration plan |

### C. Appointment, DoA and authority

| ID | Test | Expected result | Required evidence |
|---|---|---|---|
| WP03-AT-016 | Position has no active appointment | No position-derived transaction authority | Resolver negative test |
| WP03-AT-017 | Appointment is future, expired, revoked or wrong legal entity/site | Denied for action date/scope | Temporal resolver matrix |
| WP03-AT-018 | Acting appointment without required approval/evidence | Denied | Appointment workflow tests |
| WP03-AT-019 | User has domain role but lacks valid position/scope where position required | Denied | Gate regression |
| WP03-AT-020 | Tenant OWNER/ADMIN attempts PO approval solely by tenant role | Denied unless separately appointed and matching DoA rule | Authorization regression |
| WP03-AT-021 | No applicable DoA policy/rule for transaction | Held; no fallback approver | Resolver fail-closed test |
| WP03-AT-022 | Amount/currency/cumulative exposure exceeds limit | Escalate to next configured authority or hold | Boundary and conversion test |
| WP03-AT-023 | Budget absent/exceeded where required | Held | Budget integration test |
| WP03-AT-024 | Requester/preparer tries own approval via second role/session | Denied by human identity/conflict resolver | Adversarial SoD test |
| WP03-AT-025 | Delegate attempts action outside time/scope/limit | Denied | Delegation expiry/scope tests |
| WP03-AT-026 | Policy updated after request | Request retains snapshot or re-evaluates per approved transition; no silent policy drift | Policy-version test |
| WP03-AT-027 | Material PO mutation after approval | Affected approvals reopened | Transaction-version/hash test |
| WP03-AT-028 | Two approvals race or command replay occurs | Single deterministic result; no double approval | Concurrency/idempotency test |

### D. Workflow segregation

| ID | Test | Expected result | Required evidence |
|---|---|---|---|
| WP03-AT-029 | Requester raises requisition | Budget and sourcing path start; no auto-approval | P2P integration |
| WP03-AT-030 | Buyer prepares PO | Cannot approve same PO where policy prohibits | SoD regression |
| WP03-AT-031 | Receiver records goods receipt | Does not perform independent quality acceptance | Actor separation test |
| WP03-AT-032 | AP records invoice | Must match PO/receipt/invoice; cannot unilaterally release bank payment | 3-way match and treasury role test |
| WP03-AT-033 | Treasury prepares bank payment | Separate bank mandate/signatory approval required | Treasury integration test |
| WP03-AT-034 | Quality hold exists | Production/warehouse cannot bypass quality disposition | Quality authorization test |

### E. Integration, agents, privacy and operations

| ID | Test | Expected result | Required evidence |
|---|---|---|---|
| WP03-AT-035 | Create org/position/DoA update | Aggregate, audit and outbox commit atomically | Failure-injection and rollback test |
| WP03-AT-036 | Duplicate outbox delivery | Idempotent consumer; no duplicate business effect | Inbox/replay test |
| WP03-AT-037 | Agent requests a read-only org summary | Permission-filtered response with source reference | Agent tool integration test |
| WP03-AT-038 | Agent attempts appointment, approval, payroll post, bank release or legal activation | Denied / A4 prohibited | Tool-policy negative test |
| WP03-AT-039 | Prompt injection in uploaded source/document asks to change authority | Treated as untrusted data; no tool privilege escalation | Red-team test |
| WP03-AT-040 | Ordinary org-chart viewer opens restricted HR/payroll detail | Denied or masked | Privacy authorization test |
| WP03-AT-041 | RLS context missing or tenant context leaks across reused DB connection | Zero rows/denial; no cross-tenant leakage | Database pool/context tests |
| WP03-AT-042 | Redis stopped/flushed after DB commit | Committed business data intact; outbox replay restores async delivery | Failure injection |
| WP03-AT-043 | Restore database and outbox from backup | Policy, appointment, audit, sequence and idempotency state coherent | WP-16 recovery rehearsal |

## 5. Test layer and command mapping

| Layer | Tool / command | Evidence expected |
|---|---|---|
| PHP code style | composer exec pint -- --test | Exit 0 on exact candidate SHA |
| PHP syntax / static checks | repository CI scripts | Exit 0; no skipped required checks |
| Backend API | php artisan test --testsuite=Feature | Named tests and assertions executed |
| PostgreSQL/RLS | migration + tests using app_runtime NOBYPASSRLS | Forced RLS, grants, missing-context and cross-tenant tests |
| Angular | npm run test -- --run (or repository canonical script) | Vitest test counts and file list |
| Angular build | npm run build | Production build success |
| API contract | OpenAPI validation + route:list | Contract and route parity |
| Outbox | integration/failure-injection tests | Atomicity, retry and idempotent replay |
| Combined package | repository WP completion verification | Every required job executed on exact SHA |
| Artifact | combined candidate assembly | Artifact identifier, byte size, digest and run reference |

The canonical workflow command is authoritative if it differs from the examples above. Do not claim a command ran unless the log shows that command and its outcome.

## 6. Handover gates

- [ ] GAR-ENT-001..010 and GAR-PLT-001/003/004/005 crosswalk reviewed.
- [ ] Entity/relationship model and bounded-context ownership approved.
- [ ] API schemas, error catalogue and event contracts published.
- [ ] Migration impact report, dry-run and rollback rehearsal completed.
- [ ] User/appointment/DoA workflows and Angular UI complete.
- [ ] Position resolver, target-resource scope and SoD proven fail-closed.
- [ ] Official legal sources and qualified applicability review linked to rules.
- [ ] Privacy, retention, legal hold and operator controls complete.
- [ ] Exact-head backend, PostgreSQL/RLS, Angular, contract and combined CI passed.
- [ ] Installer/operator runbook, monitoring, recovery and support handover accepted.
- [ ] All NOT RUN, BLOCKED and REVIEW REQUIRED items explicitly resolved or retained as approved exceptions.

**WP-03 remains open until the full approved package definition of done is satisfied.**
