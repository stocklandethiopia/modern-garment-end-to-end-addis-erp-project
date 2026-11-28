# WP-09 — Departmental Manufacturing Execution and Product Genealogy

**Package:** WP-09  
**Status:** DESIGN COMPLETED — departmental execution model specified; runtime delivery follows  
**Scope:** Approved Domain H and route/SOP framework in WP-08. This package defines factory execution by specialist department, not a generic production-status table.

## 1. Purpose and core execution model

Manufacturing execution is a sequence of accountable transformations. Every department receives authorized inputs, verifies readiness, performs work against the pinned product/route/SOP revision, records quantity and quality evidence, and hands accepted output to the next authorized process.

The system must know:
- What product/style/order revision is being made.
- Which material lots, rolls, trims, bundles, panels and cartons are involved.
- Which route step is eligible and why.
- Who performed/checked the work, with what verified skill and equipment.
- What quantity entered, became good output, was rejected, scrapped, reworked or remains WIP.
- What changed in custody, location and cost.
- Which SOP, method, machine settings and quality plan were effective.
- Which next step can proceed and which blocker requires action.

One order-level status is inadequate. Each route step, handling unit, quality disposition, material movement and department work queue is tracked independently and reconciled.

## 2. Department boundary, input and output matrix

| Department/process | Inputs | Execution and controls | Outputs / next hand-off |
|---|---|---|---|
| Pre-production / planning | Released product pack, route, approved SAM, order, capacity and material status | Readiness validation, plan/line allocation and supervisor briefing | Authorized production order and pinned route |
| Fabric store / inspection | Fabric lot/roll, supplier docs, PO receipt, inspection plan | Shade/width/GSM/defect check, roll labeling, quarantine/release, roll location | Released roll/lot or controlled rejection |
| Relaxing / spreading | Eligible rolls, shade plan, marker revision, lay plan | Relaxation time, ply count, direction, alignment, spread tension and roll usage | Lay record, fabric consumption, remnant and cut ticket |
| Marker / cutting | Pattern/grade revision, marker file, lay, cutting machine | Marker utilization, shade grouping, cut accuracy, ply/size ratio, bundle/panel IDs | Cut panels/bundles, scrap, defect panels and roll balance |
| Bundling / panel preparation | Cut panels, bundle ticket, size/colour/lot | Count, shade/ply integrity, bundle labels, numbering, fusing/interlining if routed | Verified cut bundles ready for decoration/sewing |
| Printing / embroidery / embellishment | Approved artwork/placement, panels/garments, thread/ink/chemical lots, strike-off approval | Machine setup, recipe/settings, sample approval, placement, curing, inspection | Accepted decorated component/unit or defect/rework |
| Trim and accessory issue | Released BOM demand, trim lots, line/order/bundle | Pick by spec/colour/size/lot, quantity verification, return/shortage | Separate trim issue/return record and line kit |
| Sewing preparation | Bundles, route, operation bulletin, line balance, trims, machine and skills | Workstation setup, first-piece/sample approval, line readiness | Eligible bundle queue and approved first-piece |
| Sewing operations | Bundle WIP, operation sequence, worker skills, machine, SOP | Scan dispatch/output per operation; defect/repair; queue/buffer control | Accepted operation output and next-operation WIP |
| Wet processing / wash / dye | Authorized route, batch, chemical lots, recipe, HSE/permit evidence | Batch loading, recipe parameters, time/temp/pH or applicable measures, effluent/waste and inspection | Accepted/rejected batch, measured shade/finish and waste evidence |
| Finishing / pressing | Assembled garment WIP, finish instructions, machine/steam and quality criteria | Thread trimming, pressing, shape, measurement, repair and appearance checks | Finished garments eligible for final inspection |
| Final quality / needle or metal detection | Finished product, customer quality plan, lot/batch genealogy | Sampling, measurements, defects, safety/metal controls when required | Accepted shipment lot or hold/rework/reject disposition |
| Packing / cartonization | Released goods, pack ratio, SKU/size/carton rules, labels | Scan item/carton, quantity and label verification, seal/weight/dimensions | Closed carton, packing list, shipment-ready handling unit |
| Finished-goods warehouse | Released carton and shipment docs | Receive to location, reserve, pick/dispatch and count reconciliation | Inventory and shipment custody events |
| Subcontract process | Approved scope, WIP/material custody, supplier and route | Send/acknowledge, process evidence, return, inspection, variance | Accepted output, shortage/claim/rework case |

Actual route determines which departments are used and their order.

## 3. Production order release and work dispatch

A production order is released only against a pinned set of:
- Legal entity, site, factory, work centres and cost responsibility.
- Customer/brand/collection/order or initiative context.
- Style/product revision, tech pack, BOM, operation bulletin, SAM and route version.
- Quality plans, control plans, SOP/work-instruction revisions and mandatory process permits.
- Planned quantity, size/colour/variant matrix, target completion/delivery dates.
- Material and trim reservation/readiness, supplier and subcontract schedule.
- Machine/resource/skill availability and capacity plan.
- Authorized pre-production readiness decision.

Work queue dispatch selects eligible route step instances, not all future operations. Eligibility requires predecessor completion or approved skip, materials present, quality holds cleared, competent authorized worker, available equipment and valid instruction.

## 4. Fabric receiving, inspection, relaxing and spreading

Fabric is measured and controlled by roll/lot and shade. The fabric ledger must support length and/or weight based on the material and calibrated measurement convention. Roll identity never becomes only a text label in a spreadsheet.

### Receiving and inspection
Record supplier, receipt, original lot, roll ID, shade, width, GSM/weight, composition, defects, bow/skew, shrinkage/test, inspection plan, grade, quarantine/release and storage location. A failed or uninspected roll cannot be reserved as production-eligible.

### Relaxing and spreading
Record:
- Roll selected, shade/colour/lot and width compatibility.
- Relaxing start/end and required condition.
- Marker revision and lay plan, style/size mix, ply count and planned/actual length.
- Fabric direction/nap/face, splice/join points, tension, alignment and spread defects.
- Actual consumed length/weight, residual length and remnant handling.
- Operator, machine/table, work shift and quality check.

A fabric roll split into remnant or cut allocation must preserve parent lineage and reconcile the total quantity. A different shade lot must not be mixed into the same lay unless an approved style/customer policy permits it.

## 5. Cutting room, panel and bundle genealogy

Cutting record links marker revision, pattern revision, lay, roll(s), ply, size ratio, cut quantity, defects, re-cuts, scrap and yield. Each panel/bundle identifier must identify:
- Source production order and style revision.
- Fabric roll/lot/shade and lay/marker.
- Panel piece type and size/colour/quantity.
- Cut date, cutter/operator, table/machine and quality status.
- Bundle split/merge genealogy and current location/status.

When bundle is split or merged, all source panel IDs remain queryable and child quantities reconcile. Recut pieces require their own source reason and lineage. Cut bundle status cannot be set to complete unless the expected panel/size/quantity reconciliation passes.

## 6. Trim and accessory issue is a separate transaction

Trim store issues against a released BOM and route step:
- Identify item, specification revision, supplier lot, colour/size and approved status.
- Calculate required quantity by order/garment/pack and approved allowance.
- Reserve/pick and verify quantity and lot.
- Issue to line, bundle, workstation or subcontract custodian under a unique issue event.
- Record unused return, damaged/expired stock, shortage, substitution request and actual consumption.
- Reconcile expected vs issued vs returned vs scrapped/consumed.

Trim cannot be deducted from fabric-roll consumption. Fabric cannot be treated as a proxy for buttons, zips, labels, thread, elastic, packaging or accessories. Substitution follows WP-05 technical/quality approval; storekeeper cannot unilaterally change BOM.

## 7. Printing, embroidery and embellishment

The route step references the approved artwork/placement revision, strike-off/sample approval, colour/profile, material compatibility and customer/brand requirement. Execution captures:
- Machine/equipment, operator skill, setup and approved artwork checksum.
- Thread/ink/chemical lot, recipe, colour and placement.
- Parameters appropriate to process (e.g. stitch density, temperature/cure time or print registration) with measured values and tolerance.
- Input panels/garments and unit/bundle IDs.
- First-piece/strike-off and in-process inspection.
- Good quantity, rejected/damaged, rework and remaining WIP.
- Chemical handling and waste evidence where applicable.

A print/embroidery process can occur on cut panel, sewn component or complete garment according to the approved route. It is not hard-coded to one factory sequence.

## 8. Sewing-line execution by operation

Sewing execution is operation-level:
- Line/shift/work centre and active balance version.
- Released operation bulletin and standard.
- Bundle/garment identifiers and quantity entering.
- Operator identity and verified skill authorization.
- Machine/attachment and working condition.
- Start/finish, accepted output, reject/rework and reason.
- First-piece/inline checks, defect code and rework route.
- Handoff destination queue and physical location.

The system must support bundle scanning, station reporting, barcode/mobile/tablet and controlled manual capture where connectivity/device constraints exist. Offline capture is synchronized with unique event ID and source time; it cannot bypass current permissions or create duplicate output. Supervisors see operation WIP, starvation, blocking, cycle/earned minutes and next-action owner.

## 9. Wet processing, washing and dyeing

Only route-authorized styles and permitted sites can enter a wet-process batch. Preconditions include current approved recipe/process, material compatibility, competent personnel, valid chemical lots/SDS, equipment status, batch capacity, quality control plan and relevant site permit/HSE/environmental evidence.

Capture:
- Batch/lot and input garment/panel genealogy.
- Recipe revision, process type, equipment and operating settings.
- Chemical lot and quantity, water/energy/process consumption where required.
- Time, temperature, pH or other critical measured parameters as appropriate.
- Sample/approval and shade/colour/hand-feel/measurement results.
- Deviations, reprocess, rewash, shade banding, damage, rejected quantities.
- Effluent/waste/chemical returns, disposal manifest and environmental records.
- Operator, approver, date and batch release.

The software cannot make an unpermitted facility compliant or authorize discharge. Site-specific legal/HSE hold prevents processing. Recipe and quality limits must be supplied and approved by qualified process experts; no universal recipe or chemical limit is invented.

## 10. Finishing, pressing and final quality

Finishing steps may include trimming thread, cleaning, spot removal, button/zip function, measurement, pressing, shape, labelling, appearance, folding and presentation. Process requirements are selected by style/customer/route.

Final quality:
- Inspection plan and sample size or 100% check policy.
- Critical/major/minor defect classification configured by authorized customer/quality spec.
- Measurement and workmanship results linked to sample, size, style revision and production lot.
- Needle/metal detection or safety test where applicable.
- Quantity inspected, accepted, rejected, rework and hold.
- AQL or customer standard/version source, sampling decision and inspector.
- Shipment release authorization and attachment/evidence.

No unapproved AQL value, legal product-safety certification or customer tolerance is hard-coded. A failed final/shipment inspection blocks dispatch until valid disposition.

## 11. Packing and carton genealogy

Packing order derives from approved channel/customer packing specification and finished SKU/size/colour quantity. It validates:
- Carton assortment / ratio and maximum weight/dimensions.
- Correct barcode, hangtag, care/size label and language.
- Customer-specific packing instructions, export/shipment label and price ticket.
- Accepted final QC status and no hold.
- Carton ID, serial/seal, packer, time, weight and count.
- Source garments/bundles/lots and handling-unit genealogy.
- Carton closure and later shipment/dispatch link.

Repacking/reopening records a controlled event, not overwrite. A carton label cannot be printed as shipment-ready before contents/quality/quantity reconciliation succeeds.

## 12. Subcontract operation and material custody

For outsourced processing or full make:
- Link supplier site, approved qualification scope, PO/contract, operation/route step and SLA.
- Record quantity and lot/bundle/carton custody transfer with sender, receiver, transport and date.
- Record supplied material, trims, labels, customer-owned property and permitted consumption.
- Obtain processor acknowledgment and process batch evidence.
- Receive actual good output, rejects, scrap, left-over material and unexplained variance.
- Inspect returned goods before WIP/finished stock release.
- Reconcile supplier invoice to accepted service/output and approved contract/PO.
- Escalate loss, late return, spec change, unauthorized subcontracting or compliance gap.

External users receive only time-limited access to their assigned jobs and related evidence; no other tenant, supplier or customer records.

## 13. Departmental work queues and visual control

Role-tailored queues:
- Fabric/trim: receipt due, uninspected, shortage, wrong shade, reservation and issue/return.
- Cutting: readiness, lay plan, marker, roll selection, cut queue, re-cut and bundle shortage.
- Special process: strike-off/sample approval, batch due, recipe/chemical readiness, quality hold.
- Sewing: line dispatch, hourly target/actual, bottleneck, WIP, Andon, absent skill and material starvation.
- Wet process: authorized batch, chemical/HSE permit, process measurements and environmental evidence.
- Finishing/quality: defect containment, rework, reinspection, final lot and shipment release.
- Packing: pack ratio, carton variance, label validation and dispatch cutoff.
- Supervisor/control tower: aging holds, delayed WIP, downtime, cross-department constraint, delivery risk and next action.

Every exception has an owner, priority, due time, related order/handling unit, source evidence and resolution. Andon alerts are not simply red dashboards; they route actionable support to material, quality, maintenance, IE or management.

## 14. Core data entities

| Entity/table | Purpose |
|---|---|
| production_orders / production_order_lines | Authorized order, quantity/variant plan, site, route/product revision and schedule |
| production_order_releases | Immutable release snapshot and readiness approval |
| fabric_inspections / roll_receipts | Receiving/inspection, roll IDs, shade, width/GSM, defects and disposition |
| fabric_rolls / roll_genealogy | Roll parent/child, length/weight, source lot, shade and location |
| relax_spread_orders / spread_lays | Relaxing and lay details, marker, ply, roll consumption and residual |
| marker_files / marker_versions | CAD marker checksum, pattern revision, utilization and approval |
| cutting_orders / cut_events | Cutter, machine/table, lay, good cut, scrap, re-cut and defect results |
| panels / panel_genealogy | Piece type, source lay/roll/shade, size, bundle membership and lineage |
| cut_bundles / bundle_events | Bundle identity, split/merge/move, quantity, current route step and location |
| trim_issue_requests / trim_issues / trim_returns | Separate BOM demand, reserve, lot issue, return, shortage and reconciliation |
| embellishment_orders / special_process_batches | Artwork, recipe, strike-off, machine, input lot, output/defect and inspection |
| sewing_dispatches / operation_events | Operation, bundle, worker, skill, machine, timestamps, good/rework/reject and handoff |
| wet_process_batches / chemical_consumptions | Recipe, equipment, parameters, chemical lot, output, wastewater/waste evidence |
| finish_lots / finish_checks | Press/finish steps, measurements, defects, repair and final appearance |
| final_inspections / shipment_releases | Plan, sample/results, accepted quantity, hold and approval |
| packing_orders / carton_contents / cartons | Pack ratio, SKU/size, source garment/bundle, carton seal/weight and shipment link |
| subcontract_custody_events | Materials/handling unit, custodian, from/to, quantity, acknowledgement, variance |
| wip_snapshots / wip_events | Process-level WIP, location, age, input/output and reconciliation |
| downtime_events / andon_events | Loss reason, duration, related work centre/order, owner and resolution |
| production_handoffs | Source/target step, quantity/UOM, handling unit, QC disposition, actor and timestamp |

All tenant-owned events use WP-03 tenant scope, and entity/site IDs. Operational physical stock movement is committed through the Inventory/WMS owner. Manufacturing execution produces commands/events; it does not directly rewrite stock balances or GL.

## 15. Acceptance tests

| Test | Scenario | Expected result |
|---|---|---|
| AT-MFG-001 | Fabric receipt contains rolls from multiple shade lots | Separate roll identities/inspection and shade groups retained |
| AT-MFG-002 | One roll is split between two cutting lays with remnant | Parent/child balance and roll length reconcile |
| AT-MFG-003 | Marker uses wrong pattern revision | Cutting release blocked |
| AT-MFG-004 | Bundle is split and merged across operations | Full panel/bundle genealogy and counts preserved |
| AT-MFG-005 | Fabric issued but trims not issued | Sewing readiness shows distinct trim shortage |
| AT-MFG-006 | Embroidery artwork revision changed after strike-off | Old approval invalid for new revision; new strike-off required |
| AT-MFG-007 | Sewing operation is repeated because of rework | Good/rework/reject counts remain distinct and no double earned output |
| AT-MFG-008 | Wet processing requested without current site permit evidence | Batch dispatch blocked; no supervisor bypass |
| AT-MFG-009 | Wash batch is outside approved parameter | Quality/environment hold and disposition case created |
| AT-MFG-010 | Finished garment fails measurement check | Controlled rework/reinspection; not pack-eligible |
| AT-MFG-011 | Carton has wrong size/ratio/label | Seal/dispatch blocked and mismatch shown |
| AT-MFG-012 | Subcontract return is short against custody | Variance/claim and material genealogy retained |
| AT-MFG-013 | Route condition says wash does not apply | Explicit NOT_APPLICABLE route event with condition evidence |
| AT-MFG-014 | Duplicate operation scan arrives after sync retry | One output effect; duplicate event retained safely |
| AT-MFG-015 | One department has a WIP bottleneck | Correct next-action owner alerted and route/WIP visible |

## 16. WP-09 handover

**Completed:** manufacturing department input/output controls, fabric receiving and spreading, cutting/panel/bundle traceability, separate trim issue, print/embroidery, sewing, optional wet processing, finishing, final QC, packing/carton, subcontract custody, WIP/Andon, data model and 15 acceptance scenarios.

**Not claimed:** no actual factory SOP, cutting marker, sewing operation, wash recipe, HSE permit, machine configuration or production run has been supplied or validated. No MES screen, barcode interface, database migration or automated factory test has been implemented by this specification.

**Dependencies:** WP-05 released product pack; WP-06 method/SAM/line capacity; WP-07 approved materials/suppliers; WP-08 route engine; WP-10 planning and skills; WP-11 inspection/CAPA/HSE; WP-12 WMS; WP-13 costing; WP-14 workforce.

**Decision:** internally accepted under delegated execution. Proceed to WP-10 — Production Planning, Line Performance, Skill, Training, Incentive Governance and Improvement.
