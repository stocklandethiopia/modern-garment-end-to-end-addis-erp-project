<?php

namespace App\Services\Quality;

use App\Services\Inventory\InventoryLedgerService;
use App\Services\Support\DecimalQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class QualityControlService
{
    public function __construct(private readonly InventoryLedgerService $inventoryLedger) {}

    public function createInspection(string $tenantId, string $actorId, array $data): object
    {
        $this->assertSubjectExists($tenantId, $data['subject_type'], $data['subject_id']);

        $id = (string) Str::uuid();
        DB::table('quality_inspections')->insert([
            'id' => $id,
            'tenant_id' => $tenantId,
            'inspection_code' => $data['inspection_code'],
            'inspection_type' => $data['inspection_type'],
            'subject_type' => strtoupper($data['subject_type']),
            'subject_id' => $data['subject_id'],
            'sample_quantity' => $data['sample_quantity'],
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'result_status' => 'DRAFT',
            'inspection_standard_reference' => $data['inspection_standard_reference'] ?? null,
            'sampling_plan_reference' => $data['sampling_plan_reference'] ?? null,
            'measurements' => json_encode($data['measurements'] ?? [], JSON_THROW_ON_ERROR),
            'observations' => json_encode([], JSON_THROW_ON_ERROR),
            'inspected_by' => $actorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('quality_inspections')->where('tenant_id', $tenantId)->where('id', $id)->first();
    }

    public function submitInspectionResult(string $tenantId, string $actorId, string $inspectionId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $inspectionId, $data): object {
            $inspection = DB::table('quality_inspections')
                ->where('tenant_id', $tenantId)
                ->where('id', $inspectionId)
                ->lockForUpdate()
                ->first();

            if ($inspection === null || $inspection->result_status !== 'DRAFT') {
                throw ValidationException::withMessages(['inspection' => 'Only a draft quality inspection can be submitted.']);
            }
            if ($inspection->inspected_by !== $actorId) {
                throw ValidationException::withMessages(['inspected_by' => 'Only the assigned inspector can submit this inspection result.']);
            }

            $accepted = (string) $data['accepted_quantity'];
            $rejected = (string) $data['rejected_quantity'];
            if (! DecimalQuantity::equal(
                DecimalQuantity::add($accepted, $rejected),
                (string) $inspection->sample_quantity
            )) {
                throw ValidationException::withMessages(['quantities' => 'Every sampled unit must be accounted for as accepted or rejected before the inspection result can be submitted.']);
            }

            $defects = $data['defects'] ?? [];
            $observations = $data['observations'] ?? [];
            $result = 'PASS';
            if ($rejected > 0 || count($defects) > 0) {
                $result = ($data['containment_confirmed'] ?? false) ? 'CONTAINED' : 'FAIL';
            } elseif (($data['deviation_approved'] ?? false) === true) {
                $result = 'PASS_WITH_DEVIATION';
            }

            $requiresIndependentReview = in_array($result, ['PASS', 'PASS_WITH_DEVIATION'], true);

            DB::table('quality_inspections')->where('tenant_id', $tenantId)->where('id', $inspectionId)->update([
                'accepted_quantity' => $accepted,
                'rejected_quantity' => $rejected,
                'result_status' => $requiresIndependentReview ? 'SUBMITTED_PENDING_REVIEW' : $result,
                'submitted_result_status' => $requiresIndependentReview ? $result : null,
                'measurements' => json_encode($data['measurements'] ?? json_decode($inspection->measurements, true, 512, JSON_THROW_ON_ERROR), JSON_THROW_ON_ERROR),
                'observations' => json_encode($observations, JSON_THROW_ON_ERROR),
                'inspected_at' => $data['inspected_at'] ?? now(),
                'updated_at' => now(),
            ]);

            foreach ($defects as $index => $defect) {
                $catalog = null;
                if (! empty($defect['defect_catalog_id'])) {
                    $catalog = DB::table('quality_defect_catalog')
                        ->where('tenant_id', $tenantId)
                        ->where('id', $defect['defect_catalog_id'])
                        ->first();
                    if ($catalog === null) {
                        throw ValidationException::withMessages(["defects.{$index}.defect_catalog_id" => 'The selected defect code is not available in this tenant.']);
                    }
                }

                $id = (string) Str::uuid();
                DB::table('quality_nonconformances')->insert([
                    'id' => $id,
                    'tenant_id' => $tenantId,
                    'inspection_id' => $inspectionId,
                    'defect_catalog_id' => $catalog?->id,
                    'nonconformance_code' => $defect['nonconformance_code'],
                    'defect_description' => $defect['defect_description'],
                    'severity' => strtoupper($defect['severity'] ?? $catalog?->default_severity ?? 'MEDIUM'),
                    'affected_quantity' => $defect['affected_quantity'] ?? 1,
                    'affected_reference' => $defect['affected_reference'] ?? null,
                    'containment_status' => ($data['containment_confirmed'] ?? false) ? 'CONTAINED' : 'REQUIRED',
                    'disposition' => 'PENDING',
                    'status' => 'OPEN',
                    'containment_notes' => $data['containment_notes'] ?? null,
                    'opened_by' => $actorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return DB::table('quality_inspections')->where('tenant_id', $tenantId)->where('id', $inspectionId)->first();
        }, 3);
    }

    public function reviewInspection(string $tenantId, string $reviewerId, string $inspectionId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $reviewerId, $inspectionId, $data): object {
            $inspection = DB::table('quality_inspections')
                ->where('tenant_id', $tenantId)
                ->where('id', $inspectionId)
                ->lockForUpdate()
                ->first();

            if ($inspection === null || ! in_array($inspection->result_status, ['SUBMITTED_PENDING_REVIEW', 'FAIL', 'CONTAINED'], true)) {
                throw ValidationException::withMessages(['inspection' => 'Only a submitted inspection result awaiting review can be reviewed.']);
            }
            if ($inspection->inspected_by === $reviewerId) {
                throw ValidationException::withMessages(['reviewer' => 'The inspector cannot review their own inspection.']);
            }
            if ($inspection->reviewed_by !== null || $inspection->reviewed_at !== null) {
                throw ValidationException::withMessages(['inspection' => 'A quality inspection can only receive one final independent review. Reinspection requires a new inspection record.']);
            }
            if (! in_array($data['decision'], ['ACCEPT', 'REJECT'], true)) {
                throw ValidationException::withMessages(['decision' => 'Unsupported inspection review decision. Create a new inspection record for reinspection rather than reopening this one.']);
            }
            $submittedResult = $inspection->result_status === 'SUBMITTED_PENDING_REVIEW'
                ? $inspection->submitted_result_status
                : $inspection->result_status;

            if ($data['decision'] === 'ACCEPT' && ! in_array($submittedResult, ['PASS', 'PASS_WITH_DEVIATION'], true)) {
                throw ValidationException::withMessages(['decision' => 'A failed or contained inspection cannot be accepted; resolve the nonconformance and create a new inspection.']);
            }
            if (trim((string) ($data['review_reason'] ?? '')) === '') {
                throw ValidationException::withMessages(['review_reason' => 'A review decision reason is required.']);
            }

            $newStatus = $data['decision'] === 'ACCEPT'
                ? $submittedResult
                : 'FAIL';

            DB::table('quality_inspections')->where('tenant_id', $tenantId)->where('id', $inspectionId)->update([
                'result_status' => $newStatus,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'review_reason' => $data['review_reason'],
                'updated_at' => now(),
            ]);

            return DB::table('quality_inspections')->where('tenant_id', $tenantId)->where('id', $inspectionId)->first();
        }, 3);
    }

    public function createCorrectiveAction(string $tenantId, string $actorId, string $nonconformanceId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $nonconformanceId, $data): object {
            $nonconformance = DB::table('quality_nonconformances')
                ->where('tenant_id', $tenantId)
                ->where('id', $nonconformanceId)
                ->lockForUpdate()
                ->first();

            if ($nonconformance === null) {
                throw ValidationException::withMessages(['nonconformance' => 'The nonconformance is not available in this tenant.']);
            }
            if (in_array($nonconformance->status, ['RESOLVED', 'CLOSED'], true)) {
                throw ValidationException::withMessages(['nonconformance' => 'A resolved or closed nonconformance cannot be reopened by creating another corrective action. Use the controlled reopening workflow.']);
            }

            $id = (string) Str::uuid();
            DB::table('quality_corrective_actions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'nonconformance_id' => $nonconformanceId,
                'action_code' => $data['action_code'],
                'action_type' => strtoupper($data['action_type']),
                'action_description' => $data['action_description'],
                'assigned_to_user_id' => $data['assigned_to_user_id'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'status' => 'OPEN',
                'implementation_evidence' => json_encode([], JSON_THROW_ON_ERROR),
                'effectiveness_criteria' => $data['effectiveness_criteria'] ?? null,
                'created_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('quality_nonconformances')
                ->where('tenant_id', $tenantId)
                ->where('id', $nonconformanceId)
                ->update([
                    'status' => 'CAPA_REQUIRED',
                    'updated_at' => now(),
                ]);

            $action = DB::table('quality_corrective_actions')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            if ($action === null) {
                throw new RuntimeException('The corrective action was not persisted.');
            }

            return $action;
        }, 3);
    }

    public function decideMaterialRelease(string $tenantId, string $actorId, string $lotId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $lotId, $data): object {
            $lot = DB::table('material_lots')->where('tenant_id', $tenantId)->where('id', $lotId)->lockForUpdate()->first();
            if ($lot === null || $lot->status !== 'QUARANTINE') {
                throw ValidationException::withMessages(['material_lot' => 'Only a quarantined material lot can be quality released.']);
            }

            $inspection = DB::table('quality_inspections')->where('tenant_id', $tenantId)
                ->where('id', $data['inspection_id'])
                ->where('subject_type', 'MATERIAL_LOT')
                ->where('subject_id', $lotId)
                ->first();

            if ($inspection === null || ! in_array($inspection->result_status, ['PASS', 'PASS_WITH_DEVIATION'], true) ||
                $inspection->reviewed_by === null || $inspection->reviewed_at === null) {
                throw ValidationException::withMessages(['inspection_id' => 'A reviewed passing inspection linked to the exact material lot is required for release.']);
            }
            if ($inspection->inspected_by === $actorId || $inspection->reviewed_by === $actorId) {
                throw ValidationException::withMessages(['release_approver' => 'The inspector and inspection reviewer cannot perform the material release decision.']);
            }

            $openIssues = DB::table('quality_nonconformances')->where('tenant_id', $tenantId)
                ->where('inspection_id', $inspection->id)->whereNotIn('status', ['RESOLVED', 'CLOSED'])->exists();
            if ($openIssues) {
                throw ValidationException::withMessages(['nonconformances' => 'Open nonconformances must be resolved before material release.']);
            }

            $sourceLocation = DB::table('inventory_locations')->where('tenant_id', $tenantId)
                ->where('id', $data['source_quarantine_location_id'])->first();
            $destination = DB::table('inventory_locations')->where('tenant_id', $tenantId)
                ->where('id', $data['destination_location_id'])->first();

            if ($sourceLocation === null || strtoupper($sourceLocation->location_type) !== 'QUARANTINE' ||
                $destination === null || strtoupper($destination->location_type) === 'QUARANTINE' ||
                ! $destination->allow_stock) {
                throw ValidationException::withMessages(['location' => 'Release must transfer stock from a quarantine location into a stock-enabled non-quarantine location.']);
            }

            $rollId = DB::table('fabric_rolls')->where('tenant_id', $tenantId)->where('material_lot_id', $lotId)->value('id');
            $trimId = DB::table('trim_lots')->where('tenant_id', $tenantId)->where('material_lot_id', $lotId)->value('id');
            $balance = $this->inventoryLedger->balanceAt($tenantId, $lotId, $rollId, $trimId, $sourceLocation->id);
            if ($this->toUnits($balance) <= 0 || $this->toUnits($balance) !== $this->toUnits((string) $data['release_quantity'])) {
                throw ValidationException::withMessages(['release_quantity' => 'The release quantity must equal the full quarantined balance for this lot. Split lots before partial disposition.']);
            }

            $decisionId = (string) Str::uuid();
            DB::table('quality_release_decisions')->insert([
                'id' => $decisionId,
                'tenant_id' => $tenantId,
                'subject_type' => 'MATERIAL_LOT',
                'subject_id' => $lotId,
                'release_stage' => 'MATERIAL',
                'decision' => 'RELEASE',
                'reason' => $data['reason'],
                'linked_inspection_id' => $inspection->id,
                'conditions' => json_encode($data['conditions'] ?? [], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
                'created_at' => now(),
            ]);

            $this->inventoryLedger->postMovement($tenantId, $actorId, [
                'material_lot_id' => $lotId,
                'fabric_roll_id' => $rollId,
                'trim_lot_id' => $trimId,
                'from_location_id' => $sourceLocation->id,
                'to_location_id' => $destination->id,
                'movement_type' => 'QUALITY_RELEASE',
                'quantity' => $balance,
                'unit_code' => $data['unit_code'],
                'idempotency_key' => 'quality-release:'.$decisionId,
                'source_document_type' => 'QUALITY_RELEASE',
                'source_document_id' => $decisionId,
                'reason' => $data['reason'],
            ]);

            DB::table('material_lots')->where('tenant_id', $tenantId)->where('id', $lotId)->update(['status' => 'AVAILABLE', 'updated_at' => now()]);
            if ($rollId !== null) {
                DB::table('fabric_rolls')->where('tenant_id', $tenantId)->where('id', $rollId)->update(['status' => 'AVAILABLE', 'updated_at' => now()]);
            }
            if ($trimId !== null) {
                DB::table('trim_lots')->where('tenant_id', $tenantId)->where('id', $trimId)->update(['status' => 'AVAILABLE', 'updated_at' => now()]);
            }

            return DB::table('quality_release_decisions')->where('tenant_id', $tenantId)->where('id', $decisionId)->first();
        }, 3);
    }

    public function setNonconformanceDisposition(string $tenantId, string $actorId, string $nonconformanceId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $nonconformanceId, $data): object {
            $record = DB::table('quality_nonconformances')->where('tenant_id', $tenantId)
                ->where('id', $nonconformanceId)->lockForUpdate()->first();
            if ($record === null || ! in_array($record->status, ['OPEN', 'UNDER_REVIEW', 'CAPA_REQUIRED'], true)) {
                throw ValidationException::withMessages(['nonconformance' => 'The nonconformance is not available for disposition.']);
            }
            if (! in_array($data['disposition'], ['USE_AS_IS', 'REWORK', 'REPAIR', 'SORT', 'RETURN_TO_SUPPLIER', 'SCRAP', 'CUSTOMER_CONCESSION'], true)) {
                throw ValidationException::withMessages(['disposition' => 'Unsupported nonconformance disposition.']);
            }
            if (trim((string) ($data['reason'] ?? '')) === '') {
                throw ValidationException::withMessages(['reason' => 'A disposition reason is required.']);
            }

            DB::table('quality_nonconformances')->where('tenant_id', $tenantId)->where('id', $nonconformanceId)->update([
                'disposition' => $data['disposition'],
                'containment_status' => $data['containment_status'] ?? $record->containment_status,
                'containment_notes' => $data['containment_notes'] ?? $record->containment_notes,
                'root_cause_category' => $data['root_cause_category'] ?? $record->root_cause_category,
                'root_cause_analysis' => $data['root_cause_analysis'] ?? $record->root_cause_analysis,
                'status' => 'UNDER_REVIEW',
                'updated_at' => now(),
            ]);

            return DB::table('quality_nonconformances')->where('tenant_id', $tenantId)->where('id', $nonconformanceId)->first();
        }, 3);
    }

    public function implementCorrectiveAction(string $tenantId, string $actorId, string $actionId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $actionId, $data): object {
            $action = DB::table('quality_corrective_actions')->where('tenant_id', $tenantId)
                ->where('id', $actionId)->lockForUpdate()->first();
            if ($action === null || ! in_array($action->status, ['OPEN', 'IN_PROGRESS'], true)) {
                throw ValidationException::withMessages(['action' => 'Only an open corrective action can be implemented.']);
            }
            if ($action->assigned_to_user_id !== null && $action->assigned_to_user_id !== $actorId) {
                throw ValidationException::withMessages(['assignee' => 'Only the assigned owner can submit implementation evidence.']);
            }
            if (trim((string) ($data['implementation_summary'] ?? '')) === '' || empty($data['evidence'])) {
                throw ValidationException::withMessages(['evidence' => 'Implementation summary and evidence are required.']);
            }

            $previous = json_decode($action->implementation_evidence, true, 512, JSON_THROW_ON_ERROR);
            $previous[] = [
                'summary' => $data['implementation_summary'],
                'evidence' => $data['evidence'],
                'submitted_by' => $actorId,
                'submitted_at' => now()->toISOString(),
            ];

            DB::table('quality_corrective_actions')->where('tenant_id', $tenantId)->where('id', $actionId)->update([
                'status' => 'EFFECTIVENESS_REVIEW',
                'implementation_evidence' => json_encode($previous, JSON_THROW_ON_ERROR),
                'implemented_by' => $actorId,
                'implemented_at' => now(),
                'updated_at' => now(),
            ]);

            return DB::table('quality_corrective_actions')->where('tenant_id', $tenantId)->where('id', $actionId)->first();
        }, 3);
    }

    public function verifyCorrectiveActionEffectiveness(string $tenantId, string $verifierId, string $actionId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $verifierId, $actionId, $data): object {
            $action = DB::table('quality_corrective_actions')->where('tenant_id', $tenantId)
                ->where('id', $actionId)->lockForUpdate()->first();
            if ($action === null || $action->status !== 'EFFECTIVENESS_REVIEW') {
                throw ValidationException::withMessages(['action' => 'Only an implemented action awaiting effectiveness review can be verified.']);
            }
            if ($action->implemented_by === $verifierId || $action->created_by === $verifierId) {
                throw ValidationException::withMessages(['verifier' => 'The action creator or implementer cannot verify its effectiveness.']);
            }
            if (trim((string) ($data['effectiveness_result'] ?? '')) === '' ||
                trim((string) ($data['evidence_reference'] ?? '')) === '') {
                throw ValidationException::withMessages(['effectiveness_result' => 'Effectiveness result and independent evidence reference are required.']);
            }

            $effective = (bool) $data['effective'];
            DB::table('quality_corrective_actions')->where('tenant_id', $tenantId)->where('id', $actionId)->update([
                'status' => $effective ? 'EFFECTIVE' : 'INEFFECTIVE',
                'effectiveness_result' => $data['effectiveness_result'].' | evidence: '.$data['evidence_reference'],
                'verified_by' => $verifierId,
                'verified_at' => now(),
                'updated_at' => now(),
            ]);

            $otherOpen = DB::table('quality_corrective_actions')->where('tenant_id', $tenantId)
                ->where('nonconformance_id', $action->nonconformance_id)
                ->where('id', '<>', $actionId)
                ->whereNotIn('status', ['EFFECTIVE', 'CANCELLED'])->exists();

            if ($effective && ! $otherOpen) {
                DB::table('quality_nonconformances')->where('tenant_id', $tenantId)->where('id', $action->nonconformance_id)->update([
                    'status' => 'RESOLVED',
                    'resolved_by' => $verifierId,
                    'resolved_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif (! $effective) {
                DB::table('quality_nonconformances')->where('tenant_id', $tenantId)->where('id', $action->nonconformance_id)->update([
                    'status' => 'OPEN',
                    'resolved_by' => null,
                    'resolved_at' => null,
                    'updated_at' => now(),
                ]);
            }

            return DB::table('quality_corrective_actions')->where('tenant_id', $tenantId)->where('id', $actionId)->first();
        }, 3);
    }

    public function decideOperationRelease(string $tenantId, string $actorId, string $operationId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $operationId, $data): object {
            $operation = DB::table('production_order_operations')->where('tenant_id', $tenantId)
                ->where('id', $operationId)->lockForUpdate()->first();
            if ($operation === null || ! in_array($operation->status, ['IN_PROGRESS', 'COMPLETED'], true)) {
                throw ValidationException::withMessages(['operation' => 'A quality release decision requires an active or completed production operation.']);
            }

            $inspection = DB::table('quality_inspections')->where('tenant_id', $tenantId)
                ->where('id', $data['inspection_id'])
                ->where('subject_type', 'PRODUCTION_ORDER_OPERATION')
                ->where('subject_id', $operationId)
                ->whereIn('result_status', ['PASS', 'PASS_WITH_DEVIATION'])
                ->whereNotNull('reviewed_by')->whereNotNull('reviewed_at')->first();
            if ($inspection === null) {
                throw ValidationException::withMessages(['inspection_id' => 'A reviewed passing inspection linked to the exact operation is required.']);
            }
            if ($inspection->inspected_by === $actorId || $inspection->reviewed_by === $actorId) {
                throw ValidationException::withMessages(['approver' => 'The inspector and reviewer cannot release their own inspection.']);
            }
            if (DB::table('quality_nonconformances')->where('tenant_id', $tenantId)
                ->where('inspection_id', $inspection->id)->whereNotIn('status', ['RESOLVED', 'CLOSED'])->exists()) {
                throw ValidationException::withMessages(['nonconformances' => 'Open nonconformances block operation release.']);
            }

            $id = (string) Str::uuid();
            DB::table('quality_release_decisions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'subject_type' => 'PRODUCTION_ORDER_OPERATION',
                'subject_id' => $operationId,
                'release_stage' => 'PRODUCTION_OPERATION',
                'decision' => $data['decision'],
                'reason' => $data['reason'],
                'linked_inspection_id' => $inspection->id,
                'conditions' => json_encode($data['conditions'] ?? [], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
                'created_at' => now(),
            ]);

            return DB::table('quality_release_decisions')->where('tenant_id', $tenantId)->where('id', $id)->first();
        }, 3);
    }

    private function assertSubjectExists(string $tenantId, string $subjectType, string $subjectId): void
    {
        $table = match (strtoupper($subjectType)) {
            'MATERIAL_LOT' => 'material_lots',
            'GOODS_RECEIPT_LINE' => 'goods_receipt_lines',
            'PRODUCTION_ORDER_OPERATION' => 'production_order_operations',
            'PRODUCTION_ORDER' => 'production_orders',
            'CUTTING_BUNDLE' => 'cutting_bundles',
            'SHIPMENT' => 'shipments',
            'CUSTOMER_ORDER' => 'customer_orders',
            default => null,
        };
        if ($table === null || ! DB::table($table)->where('tenant_id', $tenantId)->where('id', $subjectId)->exists()) {
            throw ValidationException::withMessages(['subject_id' => 'The inspection subject type or tenant-scoped record is invalid.']);
        }
    }

    private function toUnits(string $value): int
    {
        if (! preg_match('/^(\d+)(?:\.(\d{1,6}))?$/', $value, $matches)) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be a non-negative decimal with at most six fractional digits.']);
        }

        return ((int) $matches[1] * 1_000_000) + (int) str_pad($matches[2] ?? '', 6, '0');
    }
}
