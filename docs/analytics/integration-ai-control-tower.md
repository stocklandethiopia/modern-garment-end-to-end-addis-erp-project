# WP-15 — Analytics, Integration, AI/Optimization and Enterprise Control Tower

**Package:** WP-15  
**Status:** DESIGN COMPLETED — specific connectors/models require source credentials, data agreements and validation  
**Scope:** Approved Domain L, modular architecture, governance, privacy and end-to-end value streams. This package adds cross-domain decision support; it does not create a second transaction platform or allow AI to post consequential transactions.

## 1. Purpose

The intelligence layer makes the ERP a decision-supporting and improving operating system. It joins reliable operational facts into consistent KPI definitions, detects risk/constraint early, forecasts and optimizes within explicit constraints, and routes actions to accountable owners.

The source-of-truth for a transaction remains its business domain. Analytics stores derived facts and dimensions, not a duplicate editable version of stock, payroll, orders, journals or production. Forecasts and AI recommendations remain distinguishable from actual facts and approved human decisions.

## 2. Data architecture and ownership

### 2.1 Operational event and reporting flow

1. A domain command validates and commits its aggregate, audit record and outbox message in one PostgreSQL transaction.
2. Outbox publisher delivers versioned event with event ID, tenant, entity scope, aggregate ID/version, timestamp, correlation and schema version.
3. Consumer inbox deduplicates delivery and validates schema/authorization.
4. Integration/read-model consumer applies projection idempotently and emits reconciliation status.
5. Analytics pipeline extracts authorized data to tenant-secure reporting store, preserving source IDs, effective time, source timestamp and data lineage.
6. KPI semantic layer computes versioned definitions.
7. Forecast/optimization process reads scoped features and returns a signed/traceable recommendation.
8. Responsible planner reviews, accepts/rejects/adjusts, and invokes normal domain command. Consequential action is not performed by the model itself.

### 2.2 Data layers

- **OLTP:** PostgreSQL domain-owned transactional aggregates.
- **Outbox/inbox:** durable event delivery and dedupe.
- **Operational read models:** task queues, dashboards and cross-domain views, rebuildable from source.
- **Analytics raw/landing:** minimized source snapshots with provenance and retention.
- **Conformed dimensions:** tenant/entity, time, product/style/variant, brand/collection, supplier, customer/account, channel, site, department, line, route/operation, currency and legal rule version.
- **Curated facts:** sales/returns, inventory/movements, procurement/receipts, production operation/output/downtime, quality/defects, workforce/skills/payroll aggregate, cost and finance.
- **Semantic model:** KPI definitions, aggregation grain, filters, time basis and calculation version.
- **Model feature/output store:** approved, versioned model inputs/output and evaluation results, segregated from transaction tables.

Sensitive health, grievance, biometric, bank and identity data are excluded from general analytics by default. Payroll analytics uses minimum aggregate data and role/purpose controls. Personal-data use requires lawful purpose, retention and cross-border processing review.

## 3. Event architecture, reliability and replay

Every event envelope includes:
- event_id (UUID), event_type, event_version, occurred_at, recorded_at.
- tenant_id, legal_entity_id where relevant, source_domain and aggregate reference/version.
- correlation_id, causation_id, idempotency reference and actor/system principal.
- data classification and purpose tag where relevant.
- payload schema and source data references.

Rules:
- Publish only after source transaction commits.
- Events are immutable; schema changes are additive or explicitly versioned.
- Consumers keep inbox receipt and processed outcome.
- Duplicate delivery is expected and has one business effect.
- Out-of-order events are resolved using aggregate version/effective time, not naive last-write wins.
- Failed events enter retry/dead-letter queue with owner, error class and replay approval.
- Replay is idempotent and scoped; replay cannot create a second journal, payment, stock issue or order.
- Poison event quarantined; no infinite retry.
- Reconciliation compares source totals/count/hash against consumer projection and analytics load.
- Sensitive event payload contains only minimum required data; files are referenced, not copied into message bodies.

## 4. Integration adapter framework

Each external connector is a bounded adapter with:
- Provider identity, tenant/entity scope, legal owner and business purpose.
- Credential reference in managed secret vault; secret values never stored in DB, logs or frontend.
- Authentication method, scopes, rotation, expiry and verification.
- Input/output schema mapping and contract version.
- Rate limit, timeout, retry with bounded exponential backoff and idempotency.
- Webhook signature, timestamp/replay protection and source event ID.
- Request/response metadata with payload redaction, hash and retention.
- Error class, dead-letter/reconciliation queue and support owner.
- Provider environment (sandbox/test/production) and explicit activation status.

Connector families:
- Identity/OIDC/SSO.
- Tax invoice, VAT/withholding and tax authority e-services (where supported and approved).
- Banks, payment providers and marketplace settlement.
- NBE/FX approved reference rates and import payment/bank evidence.
- Customs/broker and trade documentation.
- Carriers/3PL and delivery proof.
- Supplier EDI/ASN and vendor portals.
- CAD/PLM asset management and 2D/3D specialist applications.
- POS terminals and payment devices.
- Biometric/time terminal (minimal data, privacy review).
- Email/SMS/notification.
- Document storage/e-signature.
- BI/data warehouse and customer-approved external analytics.

No connector is assumed available simply because an API is listed. Each integration requires vendor documentation, authorization, test credentials, mapping, data protection assessment, security review, sandbox tests and operational reconciliation.

## 5. KPI semantic governance

A KPI is a controlled, versioned business definition:
- Name/code, business purpose, owner/steward and decision it supports.
- Formula and formula version, numerator/denominator.
- Grain and dimensions, units/currency and rounding.
- Time basis (event time, posting time, promise date, shift time) and calendar.
- Filters and exclusions, status logic and returns/quality treatment.
- Source facts/tables/events and lineage to transactions.
- Refresh frequency, latency, staleness and completeness thresholds.
- Tenant/entity scope and user permissions.
- Target/threshold policy and escalation owner.
- Approval/effective date and regression test.

A shared metric such as "production efficiency" cannot be published with different definitions in two dashboards without distinct names/version. The semantic layer exposes formula, denominator, sample/time period, and source lineage.

## 6. Executive and operational control tower

Control tower is an exception/action system, not a wall of red widgets. Each alert:
- Has rule/version, severity, threshold, detection window and scope.
- Links source event, product/order/site/line/supplier/customer and impact.
- Identifies responsible role/team and SLA/due time.
- Suggests safe next actions and affected policy/approval.
- Tracks acknowledged, assigned, actioned, escalated, mitigated and closed state.
- Requires closure reason and evidence.
- Detects recurrence and false-positive/threshold drift.

### Example domains and alerts

| Signal | Detection/impact | Owner / safe next action |
|---|---|---|
| Material shortage | Released production order lacks critical fabric/trim by need date | Material planner/procurement: expedite, substitute review or replan |
| Shade mismatch | Rolls intended for one lay differ by shade control | Fabric/QC: quarantine and alternate eligible rolls |
| Route/WIP bottleneck | Step queue age rises beyond plan; downstream starvation | IE/line supervisor: inspect method/capacity/material |
| First-piece failure | Bulk route remains blocked after failed first piece | Quality/process owner: contain, correct and reinspect |
| Supplier risk | Qualification expires or repeated delivery/quality defect | Supplier quality: hold new award and reassess |
| Capacity overload | Planned SAM load exceeds available qualified labour/machine | Production planner/IE: rebalance, sequence or approved subcontract |
| Delivery risk | Promised dispatch date at risk due to material/quality/capacity | Order owner: recovery scenario and customer communication |
| Inventory anomaly | Negative/duplicate movement or count variance beyond policy | Inventory controller: reconcile before adjustment |
| Tax rule expiry/change | Legal source superseded or effective date approaching | Tax/legal owner: review impact; do not auto-activate |
| FX modality gap | Import payment lacks a current approved modality/reference | Treasury/import specialist: legal/bank review |
| Payroll anomaly | Unexplained shift in gross/net, tax/pension, duplicate time | Payroll controller: exception review before approval |
| Quality recurrence | Defect repeats after CAPA | Quality director: reopen CAPA and verify effectiveness |
| Channel settlement variance | Marketplace/card settlement differs from gross order/refund/fee | AR/treasury: reconcile source transactions |

## 7. Forecasting and optimization portfolio

| Use case | Inputs | Output / decision | Hard constraints |
|---|---|---|---|
| Demand forecasting | Sales, returns, stockout, price/promotion, season, market and range | Probabilistic forecast by declared grain | Actual sales never overwritten; bias/uncertainty visible |
| OTB scenario | Forecast, approved budget, stock, commitments, margin and cost | Buy/range depth scenario | Not purchase authorization |
| Material planning | Released BOM, orders, stock/PO/in-transit, lead times and supplier performance | Planned shortage/date/quantity | Only approved BOM substitutes; legal/customs hold respected |
| Capacity planning | SAM, route, resource calendars, shifts, skills, maintenance and material | Feasible capacity, bottleneck, load proposal | Legal hours, skill, safety and quality gates |
| Line balancing | Operation content, precedence, machine and skills | Balanced assignment recommendations | No unqualified assignment or unsafe work method |
| Quality risk | Inspections, defects, process deviation, supplier/lot history | Risk signal and inspection intensity suggestion | Human quality authority sets holds/release |
| Inventory positioning | Demand, lead time, holding/transfer costs, shelf/storage and service | Replenishment/transfer suggestion | Owner/status/location and legal movement controls |
| Delivery reliability | Order due date, WIP, capacity, materials, carrier signals | ETA and recovery scenario | No false delivery promise; authorized customer change |
| Cost anomaly | BOM, receipt, FX, labour, overhead, yield and quality | Variance cause candidates | Accountant approves policy and postings |
| Fraud/control risk | Duplicate invoices/payments/receipts, changed beneficiary, unusual approval pattern | Review queue and evidence | No automatic accusation, denial of lawful rights or payment hold without policy |

Forecast/optimization may start as transparent deterministic rules and statistical models before advanced ML. Do not use AI to hide missing data or manufacture a precise score from weak evidence.

## 8. AI governance and action boundaries

Each model has:
- Use-case owner, accountable business decision owner and data owner.
- Purpose, prohibited use, risk tier and affected populations.
- Model/provider/version, release date, change log and evaluation.
- Training/feature data provenance, legal basis and retention.
- Performance baseline, bias/fairness and subgroup test where people decisions are involved.
- Uncertainty/confidence calibration, drift and failure detection.
- Explanation/factor attribution suitable for role.
- Prompt/data boundary and external model processing policy.
- Human review, approval and appeal route.
- Monitoring, incident response, rollback and decommissioning.

### Permitted autonomy boundary

- Low impact: classify/route non-sensitive support tickets or flag duplicates; human override and audit available.
- Medium impact: produce forecasts, recommendations, root-cause candidates, replenishment or line-balance scenarios; authorized planner must decide.
- High impact: tax/payroll calculation, worker evaluation/discipline, product safety release, finance posting/payment, supplier award, customer pricing, production stop/release, customs/FX declaration or legal compliance. These require deterministic approved rules and accountable human approval; generative AI cannot make final decision or execute the transaction.
- Prohibited: infer protected/sensitive traits or health from worker productivity, covert surveillance, manipulate worker incentives, bypass legal controls, automatically post cash/journal, issue inventory, release unsafe product or make legal determinations from incomplete sources.

## 9. Data quality, lineage and reconciliation

Data-quality dimensions:
- Completeness, validity, uniqueness, consistency, timeliness, accuracy and referential integrity.
- Source-of-truth owner and definition of actual vs forecast.
- Reconciliation totals, counts, control sums and status coverage.
- Late/changed/cancelled/reversed event treatment.
- Duplicate, missing, out-of-order and corrupted feed handling.
- Privacy/data classification and permitted use.
- Data freshness label in every dashboard.

A metric marked stale or unreconciled is never displayed as current without warning. Failed pipeline does not silently show zero. Unknown/null is not equivalent to zero.

## 10. Data model

| Entity/table | Purpose / minimum fields |
|---|---|
| event_schemas | event type/version, schema, owner, compatibility status and effective date |
| outbox_messages / inbox_receipts | durable publish/consumer event ID, scope, state, retry and dedupe |
| integration_providers | provider, product, owner, documentation, support and risk status |
| integration_connections | tenant/entity, auth type, secret reference, scopes, env, status and rotation |
| integration_mappings | provider schema version, internal event mapping, field/classification policy |
| webhook_receipts | provider event ID, signature result, timestamp, payload hash and processing status |
| integration_runs | request/correlation, outcome, latency, attempts, redacted evidence and error class |
| reconciliation_cases | source/projection, difference, amount/count, owner, SLA and resolution |
| data_sources / data_assets | source system, classification, owner, purpose, retention and lineage |
| analytics_dimensions | conformed dimension keys, type, tenant scope and effective versions |
| analytics_facts | facts with source event refs, grain, measures and event/record time |
| kpi_definitions | formula, dimensions, denominator, owner, version, sources and approvals |
| kpi_thresholds | KPI version, scope, target, severity, period and approver |
| model_registry | model/use case, version, owner, risk tier, artifacts and approval |
| model_evaluations | baseline, data set, test results, robustness, bias and reviewer |
| model_features / feature_lineage | source, definition, purpose, freshness, access and version |
| model_recommendations | model version, input snapshot, output, confidence, explanation, expiry |
| human_decisions | recommendation ID, actor, decision, reason, override and timestamp |
| alert_rules / alert_events | condition/version, severity, source, scope, owner, state and resolution |
| control_tower_cases | grouped alerts, impact, SLA, action plan, escalation and closure |
| slos / service_metrics | target, service, measurement window, actual, breach and review |
| data_quality_checks | asset/KPI, dimension, test, threshold, result, timestamp and owner |

## 11. API/event examples

Illustrative endpoints:
- POST /api/v1/integrations/{provider}/connections; POST /api/v1/integrations/{id}/test.
- POST /api/v1/events/replay (restricted approval); GET /api/v1/reconciliation-cases.
- POST /api/v1/kpi-definitions; POST /api/v1/kpi-definitions/{id}/publish.
- POST /api/v1/forecast-runs; POST /api/v1/optimization-runs.
- POST /api/v1/model-recommendations/{id}/decisions.
- POST /api/v1/alert-rules; POST /api/v1/control-tower-cases/{id}/actions.

Events: IntegrationConnectionVerified.v1, SourceEventQuarantined.v1, ProjectionReconciled.v1, DataQualityBreached.v1, ForecastPublished.v1, ModelRecommendationCreated.v1, HumanDecisionRecorded.v1, AlertRaised.v1, ControlTowerCaseEscalated.v1 and ModelRollbackInitiated.v1.

## 12. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-ANA-001 | KPI displayed on dashboard | Formula, denominator, scope and source lineage available |
| AT-ANA-002 | Source event is delivered twice | One read-model/analytics fact effect |
| AT-ANA-003 | Event arrives out of aggregate-version order | Projection detects/repairs ordering and records state |
| AT-ANA-004 | Provider webhook signature invalid or replayed | Request rejected and security event recorded |
| AT-ANA-005 | Connector credential expires | Integration health/owner alert; secrets not logged |
| AT-ANA-006 | Data pipeline fails after last successful refresh | Dashboard displays stale status, not zero/current |
| AT-ANA-007 | Forecast model changes | New version/evaluation; historic forecast and actual backtest remain |
| AT-ANA-008 | AI suggests inventory transfer | Human planner decision required; no automatic stock movement |
| AT-ANA-009 | AI recommendation has low confidence/out-of-distribution data | Uncertainty and hold/review visible |
| AT-ANA-010 | Worker-performance model attempts protected-trait inference | Prohibited feature/use blocked |
| AT-ANA-011 | Model recommendation is accepted by authorized planner | Normal domain transaction and approval path used |
| AT-ANA-012 | Duplicate marketplace/finance events appear | Reconciliation flags source duplicate; no double journal/payment |
| AT-ANA-013 | KPI formula is changed | New version, owner approval and regression comparison |
| AT-ANA-014 | Tenant user queries analytics outside scope | Tenant/entity/site isolation enforced |
| AT-ANA-015 | Legal rule source superseded | Affected KPI/rule/dashboard alerted; statutory activation not automatic |

## 13. WP-15 handover

**Completed:** event architecture, integration adapter rules, semantic KPI governance, control tower, forecast/optimization use cases, AI risk/action boundary, data quality/lineage, data model, APIs/events and 15 acceptance scenarios.

**Not claimed:** no real bank/tax/customs/POS/marketplace/carrier/biometric connector is configured, no model is trained or deployed, no customer data is ingested, no performance score is validated and no AI transaction autonomy is enabled.

**Dependencies:** all domain source transactions WP-03–WP-14; privacy/legal rule source WP-02; platform/runtime WP-16.

**Decision:** internally accepted under delegated execution. Proceed to WP-16 — End-to-End Acceptance, Security, Reliability, Build, Deployment and Rollback.

## 14. Miret operational-agent contract and role-specific catalogue

Miret is the cross-cutting four-pillar operating model defined in [Miret — Four-Pillar Garment Operating Model](../product/miret-system-operating-model.md). WP-15 must provide actual operational assistance for each activity, not a generic chat surface. Every agent entry below is a bounded task, not an autonomous department manager.

| Role and trigger | Agent's useful work | Capability boundary |
|---|---|---|
| Merchandiser starts an initiative/range review | Summarize approved market, sell-through, returns and cost signals; draft opportunity/range/size-curve scenarios | Cite sources, assumptions and confidence; human owner approves portfolio, price and launch |
| Designer/technical developer opens a style revision | Compare approved tech-pack, measurement, tolerance, BOM, artwork, care/label and packaging revisions | Stage proposed changes only; preserve approved snapshots and reviewer separation |
| Buyer/material controller reviews inbound supply | Extract GRN/PO/certificate evidence, compare spec/quantity/UOM/lead time and surface shortage or supplier-quality pattern | Do not award supplier, approve PO, change bank details or post stock |
| Incoming/inline QC opens an inspection task | Resolve product/material/route context, retrieve effective inspection plan, prepare measurement/defect capture and summarize similar prior defects | Inspector records result; quality authority owns disposition, deviation, hold and release |
| IE/planner runs monthly or daily planning | Calculate available capacity from effective SAM, calendars, people/skills, machines, materials, maintenance and changeover | Explain formula, inputs, bottleneck and alternatives; authorized planner approves plan/target |
| Operator/supervisor scans a bundle or sees a line exception | Resolve eligible next operation/SOP, show required skill/machine/quality check; open Andon and suggest recovery | No route skip, skill bypass, quality-hold bypass or direct machine control |
| Finishing/packing operator closes a carton | Validate pack specification, SKU/size/colour ratio, quantity and label; reconcile item-to-carton-to-order genealogy | Cannot mark unreconciled/held goods shipment-ready |
| DC/branch/store user receives, counts, transfers or picks | Scan item/lot/carton/location, detect duplicate/mismatch and stage movement or discrepancy | Laravel validates custody, reservation, stock and idempotency |
| Wholesale/retail/POS operator imports a workbook or return | Parse, map SKU/variant, detect duplicate tender/order rows, stage preview and reconcile quantities/value | User approval and owning POS/OMS/Finance command required for posting/refund |
| Quality/IE/management reviews a KPI exception | Explain KPI formula, period, denominator and original event evidence; group recurring constraints and suggest owner/action | No fabricated root cause, opaque ranking or cross-tenant disclosure |
| HR/Finance reviewer needs an explanation | Retrieve approved policy and explain a calculation draft or missing evidence | No autonomous adverse employment decision, payroll run, tax activation, payment or journal posting |

### Required capability metadata

Each operational tool must declare its owner/domain, semantic version, typed schema, required permission and tenant/site scope, data classification, allowed state transition, risk tier, approval requirement, idempotency key, timeout/retry semantics, audit/correlation fields, source evidence, evaluation cases and disable/rollback behavior. A model cannot provide its own tenant, user, authority or approval identity.

Every agent result must label source facts separately from inference/recommendation. It must show the relevant ERP record, exact specification or KPI version, assumptions, uncertainty, proposed effect and the human action still required. If a required source or permission is missing, the safe outcome is “cannot determine / needs review”, not a guessed transaction.

Agent quality is measured by task outcomes: reduced scan resolution time and exception rate, fewer duplicate imports, faster shortage/quality escalation, more reproducible plan scenarios, shorter defect containment time and better traceability completeness. Conversation count or generated text volume is not an operational success metric.
