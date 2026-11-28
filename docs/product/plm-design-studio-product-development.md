# WP-05 — Design Studio, Product Development, Samples, Fit, PLM, BOM and Costing

**Package:** WP-05  
**Status:** DESIGN COMPLETED — implementation follows as domain vertical slices  
**Scope:** Approved Domain D and Apparel Business Operating System Charter. Receives approved product briefs from WP-04 and hands a released product package to WP-06/WP-07/WP-08/WP-09.

## 1. Purpose and operating model

The design studio and product-development process is the controlled bridge between commercial intent and reproducible manufacturing. It preserves creative work, technical specification, sample/fit evidence, product structure, cost assumptions and approvals as linked, versioned records.

A sketch is not a production instruction. Sample approval is not a substitute for a released tech pack. A 3D render does not automatically replace a physical fit approval. A BOM substitution is not permitted merely because a store or factory has material in stock. Only a complete, effective product-development release may be used to engineer a production route and procurement demand.

The process supports in-house designers, outsourced designers, customer/buyer tech packs, licensed artwork, carry-over styles, locally developed products, sourced products and mixed make/buy models.

## 2. End-to-end development lifecycle

Approved WP-04 product brief → design brief and research → concept/sketch/CAD/artwork → technical design and construction → pattern/size specification/grading → 2D/3D visualization → sample request and sample room → fit session → correction loop or approval → BOM/consumption/substitute rules → operation and quality specification → cost sheet/margin review → technical and commercial approvals → immutable product release package → WP-06 operation standards, WP-07 sourcing specifications and WP-08 process route/SOP binding.

### Product lifecycle states

IDEA_BRIEF → DESIGN_IN_PROGRESS → TECHNICAL_DESIGN → PATTERN_DEVELOPMENT → SAMPLE_REQUESTED → SAMPLE_IN_PROGRESS → FIT_REVIEW → CORRECTION_REQUIRED → COSTING → TECHNICAL_APPROVAL → COMMERCIAL_APPROVAL → RELEASED → SUPERSEDED.

Alternative terminal states: PARKED, REJECTED, CANCELLED. A transition is a command evaluated against required fields, actor permissions, approval matrix and dependency status. Users cannot directly edit status. A correction to any released specification creates a new revision and change-impact record.

## 3. Design brief and creative workflow

The brief links initiative, brand strategy, collection, range line, target consumer, market, channel, price tier and target delivery window. It records:
- Product use, wearer, occasion, climate/season and performance expectation.
- Silhouette, construction, fit intent, detail, fabric handle and finish.
- Target size range, body/fit model basis, measurement and tolerance requirements.
- Colour palette, material/trim direction, print/embroidery/artwork direction.
- Target retail/wholesale price, target cost/margin and quality tier.
- Sustainability, care, safety, labelling and claim-substantiation requirements.
- Buyer/customer specifications, licensing or IP restrictions where applicable.
- Design assets, inspiration provenance and usage/ownership rights.

Creative concepts are assets with author, source, rights status, timestamp, checksum and revision. Comments do not mutate original source assets. Selected concepts are approved into technical design; rejected concepts remain in history, not production candidates.

## 4. Technical design, pattern, measurement and grading

### 4.1 Style architecture

PLM owns product/style identity and classification, product variant dimensions (colour, size, fit, length, inseam, cup, width or pack), SKU identifiers, size-system references, measurement specs, pattern model/pieces, construction details, seam/stitch, reinforcement, label/care placement and finish, artwork/placement, tech-pack release and revisions.

A style may use different size systems and grading rules by market, brand or customer. Preserve the original sizing convention and conversion/grade rule version. Do not assume every range uses S/M/L or a global size chart.

### 4.2 Pattern and grading control

Each pattern revision comprises pattern pieces, grain direction, notches, drill marks, seam allowance, piece identity, material compatibility and CAD source file. Grade rules reference size range, base size, points of measure and increments. Validate:
- Every mandatory size and point of measure exists.
- Grade increments are internally consistent and within configured tolerance.
- Pattern piece and measurement tables refer to the approved revision.
- Material stretch/shrinkage and process allowances are explicit.
- Marker/lay planning receives only approved pattern revisions.

Pattern changes trigger impact analysis for marker, consumption, sample, BOM, fit, costing, operation method and open production orders. A released order pins its product revision unless an authorized engineering-change/rework decision is approved.

### 4.3 Measurement and tolerances

Store point-of-measure definitions with diagram/coordinate, measurement method, nominal value by size, tolerance (+/- or asymmetric), unit, condition (relaxed/washed/stretched), sample method and approval. Keep distinct:
- Design target measurement.
- Pattern-derived measurement.
- Sample-measured result.
- Bulk-inspection result.

Do not mix these measures in one mutable field. Record instrument, assessor, date, conditions, rounding and calibration evidence where required.

## 5. 2D/3D, virtual review and digital assets

2D flats, CAD pattern files, graded nest files, 3D garments, texture/material maps, avatar/fit-model definitions, render snapshots and artwork masters are distinct asset types. Store:
- Asset type, format, size, checksum, source application/version, creator and rights.
- Parent style/revision and linked component/measurement.
- Derivative relationships (preview, thumbnail, export, render).
- Access classification, private storage key, retention, licence/usage scope.
- Review comments, fit simulation method, avatar body/pose and simulation engine version.

Virtual fit reports include model/cloth settings, ease/tension/strain/pressure visualization where available, simulation caveats, selected sizes, reviewer, comparison to prior fit and disposition. A virtual pass cannot auto-approve physical fit unless a product/customer policy expressly permits it and the exception is documented.

CAD/3D authoring remains in specialized applications accessed through integration adapters. The ERP manages asset metadata, approved revisions, derivative previews, access and handover integrity; it does not attempt to replace all specialist CAD authoring tools.

## 6. Sample-room workflow and fit session

Sample types include development/prototype, fit, size set, salesman/showroom, photo/marketing, pre-production, wash-test, print/embroidery strike-off, pilot and counter sample. Tenants may configure labels while preserving semantic stage and approval controls.

Request-to-sample:
1. Product developer requests a sample against an exact style revision, sample type, quantity, size, colour, due date and purpose.
2. Sample coordinator validates capacity, fabric/trim/special-process resources and technical-file completeness.
3. Materials are reserved/issued to the sample job; unused material is returned with lot lineage.
4. Construction method, operator/cell, deviations and actual material usage are recorded.
5. Sample completion triggers fit/quality review; no sample stage auto-approves the next stage.
6. Fit session records participants/roles, fit-model code and size, posture, garment measurements, photos/annotations, points of concern, severity, action owner, due date and decision.
7. Outcome: approve, approve with controlled minor comments, correction required, reject, or additional size/market evaluation.
8. A new sample round references the exact correction list and a new revision. Prior samples and fit sessions remain immutable.

Sensitive fit-model/employee/consumer body data is minimized, purpose-restricted and access-controlled under the privacy policy. Do not collect unnecessary biometric/body information for general product notes.

## 7. Tech pack and controlled product release

A tech-pack release bundle contains:
- Style identity, product/variant/size matrix, brand/collection/range references.
- Design concept and approved 2D technical flats.
- Construction details, BOM and approved substitute rules.
- Pattern/grade reference and size spec/tolerance.
- Artwork placement, colour and print/embroidery specification.
- Label/care/packaging/consumer information requirements.
- Seam/stitch/workmanship/appearance standards.
- Process sequence suggestions and known special processes.
- Sample/fit evidence and approval/waiver record.
- Quality inspection plan and defect criticality guidance.
- Approved cost sheet and commercial restrictions where appropriate.
- Engineering change/revision history, approvers and effective date.
- Attachments with cryptographic checksums.

The production handover bundle is immutable and identifies the exact released version. Any correction creates a new version, compares field-level changes, and identifies affected orders/materials/patterns/SOPs/costs and open purchase/production work. Downstream recipients acknowledge the version they will use.

## 8. BOM, material consumption and substitution governance

BOM supports hierarchical components, subassemblies, trims, labels, packaging and optional process inputs. Each line contains component identity, supplier reference if locked/approved, UOM, consumption per finished unit, wastage allowance, size/colour/variant applicability, attachment/position, effective period and optional/mandatory flag.

Consumption separates net product consumption, process loss/cutting wastage, sample/approval usage, production buffer, rework/replacement allowance and packaging consumption. Do not use one uncontrolled extra percentage across fabric, trims, chemicals and packaging. Wastage is process-specific, evidence-backed and versioned.

Fabric consumption is linked to fabric spec, usable width, weight/GSM, shade group, shrinkage/relaxation, marker efficiency and roll/lot compatibility. Trim/accessory consumption is separate by item/colour/size/lot and issued separately downstream. Material substitute equivalence requires technical and quality approval; inventory availability alone cannot authorize substitution.

## 9. Costing and commercial feasibility

Costing explains and versions the cost stack:
- Fabric and trims: BOM quantity × approved purchase/landed cost.
- Special material/chemical/printing/embroidery/process cost.
- Direct labour from approved operation content/SAM and labour-rate basis.
- Factory overhead allocation method and effective period.
- Subcontract process cost.
- Packaging and testing/certification.
- Import duty/freight/insurance/clearance where applicable.
- Wastage, expected yield and rework assumptions.
- Channel-specific costs (commission, marketplace fee, fulfilment, payment fees and returns).
- Currency/rate source/date, FX exposure and scenario.
- Tax and non-recoverable amounts only where qualified tax review approves applicability.

A cost sheet stores formula version, currency, source rate references, quantity/UOM, landed-cost basis, labour method/SAM version, overhead allocation policy, yield and assumptions, scenario owner and approval. It separates standard/target cost, estimated cost, actual purchase cost and actual production cost.

Price/margin decisions consume WP-04 price architecture and WP-13 finance policy. Costing does not create a GL journal or silently update a published selling price.

## 10. Engineering change and version control

An engineering change request records request source (designer, quality, IE, supplier, customer, legal or production), affected styles/revisions/patterns/BOM/tech-pack/process instructions/orders, reason/risk/urgency, disposition (use-as-is, rework, repair, sort, replace, stop/hold or next-revision-only), material purchased/issued and disposition, open samples/WIP/finished goods/shipments, cost/delivery/compliance impact, reviewers, decision, effective date and transition plan.

Changes to released products are never edited in place. A new revision is approved and an effectivity rule determines which new orders can use it. Existing orders remain pinned; changing them requires an explicit engineering-change disposition.

## 11. Data model and ownership

| Entity/table | Minimum fields / purpose |
|---|---|
| product_briefs | tenant_id, initiative_id, collection_id, range_line_id, target_consumer, need, target_price/cost, owner, state |
| styles / style_revisions | tenant_id, code, brand/category, revision_no, effective_from, status, parent_revision, change_summary |
| product_variants / skus | style_revision, variant dimensions, colour, size, market scope, SKU/barcode, lifecycle |
| size_systems | tenant_id, code, market/category applicability, unit and conversion |
| measurement_points / measurement_specs | point diagram/method/unit/tolerance; style revision, size, point, nominal, lower/upper tolerance, condition |
| pattern_sets / pattern_pieces | style revision, pattern revision, CAD asset, base size; piece code, grainline, seam allowance, material |
| grade_rules | pattern revision, size system, base size, point increments and revision |
| design_assets | style revision, asset type, private storage key, checksum, rights, creator, version |
| sample_requests / samples | style revision, sample type, requested sizes, due date, purpose, requester; sample round, status, maker, completion, actual material |
| fit_sessions / fit_observations | sample, model reference, size, protocol, result, reviewer, date; measurement point, issue, severity, action and disposition |
| construction_specs | style revision, seam/stitch/workmanship/finish rules and process |
| tech_packs / tech_pack_approvals | style revision, version, manifest checksum, state, release date; approver, decision, reason |
| bom_versions / bom_lines | style revision, version, effectivity; component, quantity, UOM, waste code, applicability, optional flag and substitution policy |
| substitute_rules | primary component, alternative, validation, restriction and approver |
| cost_sheets / cost_lines | style revision, version, currency, method, formula and assumptions; category, source ref, quantity, rate, amount and allocation |
| engineering_changes / change_impacts | request, source, reason, priority, affected revisions, disposition, effective date; impacted object/version, required action, owner and status |

All tenant-owned rows inherit the WP-03 composite tenant key, RLS and entity-scoping patterns. Released bundles reference immutable IDs and checksums rather than uncontrolled file copies.

## 12. API and event contracts

Illustrative versioned endpoints:
- POST /api/v1/product-briefs; POST /api/v1/styles; POST /api/v1/styles/{id}/revisions.
- POST /api/v1/patterns/{id}/revisions; POST /api/v1/grade-rules; POST /api/v1/measurement-specs.
- POST /api/v1/sample-requests; POST /api/v1/samples/{id}/complete; POST /api/v1/fit-sessions.
- POST /api/v1/tech-packs/{id}/submit; POST /api/v1/tech-packs/{id}/release.
- POST /api/v1/boms/{id}/versions; POST /api/v1/cost-sheets/{id}/simulate; POST /api/v1/engineering-changes.

Events: ProductBriefAccepted.v1, StyleCreated.v1, PatternRevisionApproved.v1, FitDecisionRecorded.v1, BOMVersionApproved.v1, CostScenarioApproved.v1, TechPackReleased.v1 and EngineeringChangeApproved.v1.

All writes validate tenant/entity/channel scope, use idempotency where retriable, record actor and source version, and return stable errors. Submit/approve operations are commands; arbitrary direct status changes are rejected. Asset downloads are tenant-scoped, private and audited.

## 13. Decision support and validation

- Compare theoretical BOM consumption with sample usage and marker/lay yield; highlight unexplained variance.
- Validate fabric width/shrinkage and pattern pieces before marker release.
- Detect missing size/measurement points, out-of-tolerance grade steps and incompatible components.
- Check artwork rights/expiry before bulk print/embroidery release.
- Compare target cost with forecast/actual purchase and manufacturing costs; explain variance.
- Compare physical fit and virtual simulation separately, with confidence and caveats.
- Predict engineering-change impact on material, orders and WIP but leave disposition with authorized roles.
- Prevent superseded tech pack, pattern, BOM, cost or artwork from reaching production.

## 14. Acceptance test catalogue

| Test | Scenario | Expected result |
|---|---|---|
| AT-PLM-001 | Approved WP-04 range line creates product brief | Initiative/range lineage remains linked |
| AT-PLM-002 | Designer changes approved design concept | New asset/version; old release immutable |
| AT-PLM-003 | Pattern grade misses size/POM or exceeds tolerance | Approval rejected with exact gaps/out-of-range values |
| AT-PLM-004 | 3D fit passes but physical fit is configured mandatory | Physical fit still blocks release |
| AT-PLM-005 | Sample consumes multiple rolls and returns remnants | Issue/return genealogy and usage reconcile by roll |
| AT-PLM-006 | Fit session identifies critical construction issue | Correction required and new sample/revision approval |
| AT-PLM-007 | BOM substitutes trim because preferred item is unavailable | Blocked unless approved technical/quality equivalence exists |
| AT-PLM-008 | Cost scenario uses FX and supplier rate | Source/rate date/version retained; replay is deterministic |
| AT-PLM-009 | Tech pack lacks approved size spec/care instruction | Release rejected by completeness gate |
| AT-PLM-010 | Engineering change affects open purchase/production orders | Impact list created; existing orders stay pinned pending disposition |
| AT-PLM-011 | Superseded revision requested for new production order | Blocked; only released revision selectable |
| AT-PLM-012 | User without design/technical release rights submits approval | Server-side denial and audit event |
| AT-PLM-013 | Approved tech pack sent to factory | Immutable manifest/checksum and recipient acknowledgement stored |
| AT-PLM-014 | User tries to delete approved fit/quality record | Deletion denied; correction/annotation required |

## 15. WP-05 handover

**Completed:** product brief, design/creative review, pattern/grade, measurement, 2D/3D asset governance, sample room, fit sessions, tech pack, BOM/substitution, cost stack, engineering change, APIs/events, data ownership and 14 acceptance scenarios.

**Not claimed:** no CAD/3D integration, asset store, PLM screen, API, database migration, executable test or costing integration has been implemented by this specification.

**Dependencies:** WP-04 approved product brief; WP-03 tenant/identity/audit; WP-06 operation/SAM/capacity; WP-07 sourcing and actual material prices; WP-08 routes/SOP; WP-11 quality; WP-13 financial cost policy.

**Decision:** internally accepted under delegated execution. Proceed to WP-06 — Industrial Engineering, Method/Time Study, SAM/SMV and Capacity.
