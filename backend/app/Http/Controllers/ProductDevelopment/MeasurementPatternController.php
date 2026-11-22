<?php

namespace App\Http\Controllers\ProductDevelopment;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MeasurementPatternController extends Controller
{
    public function measurementSpecs(string $styleId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $this->findStyle($styleId, $tenantId);

        $versions = DB::table('measurement_spec_versions')
            ->where('tenant_id', $tenantId)
            ->where('style_id', $styleId)
            ->orderByDesc('style_revision_no')
            ->orderByDesc('version_no')
            ->get();

        foreach ($versions as $version) {
            $version->size_sequence = $this->decodeArray($version->size_sequence);
            $points = DB::table('measurement_spec_points')
                ->where('tenant_id', $tenantId)
                ->where('measurement_spec_version_id', $version->id)
                ->orderBy('point_code')
                ->get();

            foreach ($points as $point) {
                $sizeOrder = array_flip($version->size_sequence);
                $point->values = DB::table('measurement_spec_values')
                    ->where('tenant_id', $tenantId)
                    ->where('measurement_point_id', $point->id)
                    ->get()
                    ->sortBy(fn (object $value): int => $sizeOrder[$value->size_code] ?? PHP_INT_MAX)
                    ->values();
            }

            $version->points = $points;
        }

        return response()->json(['data' => $versions]);
    }

    public function createMeasurementSpec(string $styleId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'size_system_code' => ['required', 'string', 'max:80'],
            'size_sequence' => ['required', 'array', 'min:2', 'max:30'],
            'size_sequence.*' => ['required', 'string', 'max:32', 'distinct'],
            'base_size_code' => ['required', 'string', 'max:32'],
            'measurement_condition' => ['required', 'string', 'max:120'],
            'change_reason' => ['required', 'string', 'min:10', 'max:500'],
            'points' => ['required', 'array', 'min:1', 'max:100'],
            'points.*.point_code' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/'],
            'points.*.name' => ['required', 'string', 'max:180'],
            'points.*.measurement_method' => ['required', 'string', 'min:5', 'max:2000'],
            'points.*.unit_code' => ['required', 'string', 'in:CM,MM,IN'],
            'points.*.is_required' => ['required', 'boolean'],
            'points.*.diagram_reference' => ['nullable', 'string', 'max:240'],
            'points.*.values' => ['required', 'array', 'min:2', 'max:30'],
            'points.*.values.*.size_code' => ['required', 'string', 'max:32'],
            'points.*.values.*.nominal_value' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
            'points.*.values.*.tolerance_minus' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
            'points.*.values.*.tolerance_plus' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
        ]);

        $this->validateMeasurementPayload($data);
        $style = $this->findStyle($styleId, $tenantId);
        $this->assertStyleEditor($style, $actorId, $tenantContext);

        $result = DB::transaction(function () use ($styleId, $tenantId, $actorId, $data): object {
            $style = DB::table('styles')
                ->where('tenant_id', $tenantId)
                ->where('id', $styleId)
                ->lockForUpdate()
                ->first();

            abort_if($style === null, 404);
            $revisionNo = (int) $style->current_revision;
            abort_unless($revisionNo > 0, 422, 'A style design revision is required before technical measurements can be specified.');

            $versionNo = (int) DB::table('measurement_spec_versions')
                ->where('tenant_id', $tenantId)
                ->where('style_id', $styleId)
                ->where('style_revision_no', $revisionNo)
                ->max('version_no') + 1;
            $specId = (string) Str::uuid();
            $now = now();

            DB::table('measurement_spec_versions')->insert([
                'id' => $specId,
                'tenant_id' => $tenantId,
                'style_id' => $styleId,
                'style_revision_no' => $revisionNo,
                'version_no' => $versionNo,
                'size_system_code' => trim($data['size_system_code']),
                'size_sequence' => json_encode(array_values($data['size_sequence']), JSON_THROW_ON_ERROR),
                'base_size_code' => trim($data['base_size_code']),
                'measurement_condition' => trim($data['measurement_condition']),
                'status' => 'DRAFT',
                'change_reason' => trim($data['change_reason']),
                'created_by' => $actorId,
                'approved_by' => null,
                'approved_at' => null,
                'created_at' => $now,
            ]);

            foreach ($data['points'] as $point) {
                $pointId = (string) Str::uuid();
                DB::table('measurement_spec_points')->insert([
                    'id' => $pointId,
                    'tenant_id' => $tenantId,
                    'measurement_spec_version_id' => $specId,
                    'point_code' => strtoupper(trim($point['point_code'])),
                    'name' => trim($point['name']),
                    'measurement_method' => trim($point['measurement_method']),
                    'unit_code' => strtoupper($point['unit_code']),
                    'is_required' => (bool) $point['is_required'],
                    'diagram_reference' => $point['diagram_reference'] ?? null,
                    'created_at' => $now,
                ]);

                foreach ($point['values'] as $value) {
                    DB::table('measurement_spec_values')->insert([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'measurement_point_id' => $pointId,
                        'size_code' => trim($value['size_code']),
                        'nominal_value' => $value['nominal_value'],
                        'tolerance_minus' => $value['tolerance_minus'],
                        'tolerance_plus' => $value['tolerance_plus'],
                        'created_at' => $now,
                    ]);
                }
            }

            $this->writeAudit($tenantId, $actorId, 'MEASUREMENT_SPEC_VERSION_CREATED', 'MEASUREMENT_SPEC', $specId, trim($data['change_reason']));

            return DB::table('measurement_spec_versions')->where('tenant_id', $tenantId)->where('id', $specId)->first();
        });

        return response()->json(['data' => $result], 201);
    }

    public function approveMeasurementSpec(string $styleId, string $specVersionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or admin approval is required.');

        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $result = DB::transaction(function () use ($tenantId, $styleId, $specVersionId, $actorId): object {
            $spec = DB::table('measurement_spec_versions')
                ->where('tenant_id', $tenantId)
                ->where('style_id', $styleId)
                ->where('id', $specVersionId)
                ->lockForUpdate()
                ->first();

            abort_if($spec === null, 404);
            abort_unless($spec->created_by !== $actorId, 403, 'The measurement specification creator cannot approve their own version.');
            abort_unless($spec->status === 'DRAFT', 422, 'Only a draft measurement specification can be approved.');

            $pointsCount = DB::table('measurement_spec_points')
                ->where('tenant_id', $tenantId)
                ->where('measurement_spec_version_id', $specVersionId)
                ->count();
            abort_unless($pointsCount > 0, 422, 'A measurement specification requires at least one point.');

            DB::table('measurement_spec_versions')->where('tenant_id', $tenantId)->where('id', $specVersionId)->update([
                'status' => 'APPROVED',
                'approved_by' => $actorId,
                'approved_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'MEASUREMENT_SPEC_VERSION_APPROVED', 'MEASUREMENT_SPEC', $specVersionId, 'Independent approval of the measurement specification version.');

            return DB::table('measurement_spec_versions')->where('tenant_id', $tenantId)->where('id', $specVersionId)->first();
        });

        return response()->json(['data' => $result]);
    }

    public function patternSets(string $styleId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $this->findStyle($styleId, $tenantId);

        return response()->json([
            'data' => DB::table('pattern_sets as ps')
                ->leftJoin('pattern_revisions as pr', function ($join): void {
                    $join->on('pr.tenant_id', '=', 'ps.tenant_id')
                        ->on('pr.pattern_set_id', '=', 'ps.id')
                        ->whereRaw('pr.revision_no = (SELECT MAX(pr2.revision_no) FROM pattern_revisions AS pr2 WHERE pr2.tenant_id = ps.tenant_id AND pr2.pattern_set_id = ps.id)');
                })
                ->where('ps.tenant_id', $tenantId)
                ->where('ps.style_id', $styleId)
                ->orderBy('ps.pattern_code')
                ->get([
                    'ps.*',
                    'pr.id as latest_revision_id',
                    'pr.revision_no as latest_revision_no',
                    'pr.status as latest_revision_status',
                    'pr.measurement_spec_version_id',
                    'pr.style_revision_no',
                    'pr.created_by as latest_revision_created_by',
                    'pr.change_summary as latest_change_summary',
                ]),
        ]);
    }

    public function createPatternSet(string $styleId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'pattern_code' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/'],
            'name' => ['required', 'string', 'max:180'],
            'measurement_spec_version_id' => ['required', 'uuid'],
            'pieces' => ['required', 'array', 'min:1', 'max:500'],
            'pieces.*.piece_code' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/'],
            'pieces.*.description' => ['required', 'string', 'max:240'],
            'pieces.*.material_reference' => ['nullable', 'string', 'max:120'],
            'pieces.*.grain_direction' => ['nullable', 'string', 'max:40'],
            'pieces.*.seam_allowance' => ['nullable', 'array', 'max:30'],
            'pieces.*.piece_count' => ['required', 'integer', 'min:1', 'max:20'],
            'grade_rules' => ['required', 'array', 'min:1', 'max:3000'],
            'grade_rules.*.point_code' => ['required', 'string', 'max:80'],
            'grade_rules.*.from_size_code' => ['required', 'string', 'max:32'],
            'grade_rules.*.to_size_code' => ['required', 'string', 'max:32'],
            'grade_rules.*.increment' => ['required', 'numeric', 'decimal:0,4'],
            'grade_rules.*.unit_code' => ['required', 'string', 'in:CM,MM,IN'],
            'change_summary' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $style = $this->findStyle($styleId, $tenantId);
        $this->assertStyleEditor($style, $actorId, $tenantContext);
        abort_unless((int) $style->current_revision > 0, 422, 'A style revision is required before pattern development.');
        $spec = $this->findApprovedMeasurementSpec($tenantId, $styleId, $data['measurement_spec_version_id']);
        abort_unless((int) $spec->style_revision_no === (int) $style->current_revision, 422, 'The measurement specification must reference the current style revision.');

        $patternSetId = (string) Str::uuid();
        $patternRevisionId = (string) Str::uuid();
        $now = now();
        $gradeRules = $this->normalizeGradeRules($tenantId, $spec, $data['grade_rules'], false);
        $pieces = $this->normalizePieces($data['pieces']);

        DB::transaction(function () use ($tenantId, $actorId, $styleId, $data, $patternSetId, $patternRevisionId, $spec, $gradeRules, $pieces, $now, $style): void {
            DB::table('pattern_sets')->insert([
                'id' => $patternSetId,
                'tenant_id' => $tenantId,
                'style_id' => $styleId,
                'pattern_code' => strtoupper(trim($data['pattern_code'])),
                'name' => trim($data['name']),
                'status' => 'DRAFT',
                'current_revision' => 0,
                'owner_user_id' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->insertPatternRevision($tenantId, $actorId, $patternSetId, $styleId, (int) $style->current_revision, $spec, 1, trim($data['change_summary']), $patternRevisionId, $pieces, $gradeRules, $now);
            $this->writeAudit($tenantId, $actorId, 'PATTERN_SET_CREATED', 'PATTERN_SET', $patternSetId, trim($data['change_summary']));
        });

        return response()->json([
            'data' => DB::table('pattern_sets')->where('tenant_id', $tenantId)->where('id', $patternSetId)->first(),
            'revision' => DB::table('pattern_revisions')->where('tenant_id', $tenantId)->where('id', $patternRevisionId)->first(),
        ], 201);
    }

    public function createPatternRevision(string $patternSetId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'measurement_spec_version_id' => ['required', 'uuid'],
            'pieces' => ['required', 'array', 'min:1', 'max:500'],
            'pieces.*.piece_code' => ['required', 'string', 'max:80', 'regex:/^[A-Za-z0-9][A-Za-z0-9_-]*$/'],
            'pieces.*.description' => ['required', 'string', 'max:240'],
            'pieces.*.material_reference' => ['nullable', 'string', 'max:120'],
            'pieces.*.grain_direction' => ['nullable', 'string', 'max:40'],
            'pieces.*.seam_allowance' => ['nullable', 'array', 'max:30'],
            'pieces.*.piece_count' => ['required', 'integer', 'min:1', 'max:20'],
            'grade_rules' => ['required', 'array', 'min:1', 'max:3000'],
            'grade_rules.*.point_code' => ['required', 'string', 'max:80'],
            'grade_rules.*.from_size_code' => ['required', 'string', 'max:32'],
            'grade_rules.*.to_size_code' => ['required', 'string', 'max:32'],
            'grade_rules.*.increment' => ['required', 'numeric', 'decimal:0,4'],
            'grade_rules.*.unit_code' => ['required', 'string', 'in:CM,MM,IN'],
            'change_summary' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($patternSetId, $tenantId, $actorId, $data, $tenantContext): object {
            $patternSet = DB::table('pattern_sets')
                ->where('tenant_id', $tenantId)
                ->where('id', $patternSetId)
                ->lockForUpdate()
                ->first();

            abort_if($patternSet === null, 404);
            $style = $this->findStyle($patternSet->style_id, $tenantId);
            $this->assertStyleEditor($style, $actorId, $tenantContext);
            $spec = $this->findApprovedMeasurementSpec($tenantId, $patternSet->style_id, $data['measurement_spec_version_id']);
            abort_unless((int) $spec->style_revision_no === (int) $style->current_revision, 422, 'The measurement specification must reference the current style revision.');

            $rules = $this->normalizeGradeRules($tenantId, $spec, $data['grade_rules'], false);
            $pieces = $this->normalizePieces($data['pieces']);
            $revisionNo = (int) DB::table('pattern_revisions')
                ->where('tenant_id', $tenantId)
                ->where('pattern_set_id', $patternSetId)
                ->max('revision_no') + 1;
            $revisionId = (string) Str::uuid();
            $now = now();

            $this->insertPatternRevision($tenantId, $actorId, $patternSetId, $patternSet->style_id, (int) $style->current_revision, $spec, $revisionNo, trim($data['change_summary']), $revisionId, $pieces, $rules, $now);
            DB::table('pattern_sets')->where('tenant_id', $tenantId)->where('id', $patternSetId)->update([
                'status' => 'REVISION_PENDING',
                'updated_at' => $now,
            ]);
            $this->writeAudit($tenantId, $actorId, 'PATTERN_REVISION_CREATED', 'PATTERN_SET', $patternSetId, trim($data['change_summary']));

            return DB::table('pattern_revisions')->where('tenant_id', $tenantId)->where('id', $revisionId)->first();
        });

        return response()->json(['data' => $result], 201);
    }

    public function approvePatternRevision(string $patternSetId, string $revisionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or admin approval is required.');

        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $result = DB::transaction(function () use ($tenantId, $actorId, $patternSetId, $revisionId): array {
            $revision = DB::table('pattern_revisions as pr')
                ->join('pattern_sets as ps', function ($join): void {
                    $join->on('ps.tenant_id', '=', 'pr.tenant_id')
                        ->on('ps.id', '=', 'pr.pattern_set_id');
                })
                ->where('pr.tenant_id', $tenantId)
                ->where('pr.pattern_set_id', $patternSetId)
                ->where('pr.id', $revisionId)
                ->lockForUpdate()
                ->first(['pr.*', 'ps.style_id']);

            abort_if($revision === null, 404);
            abort_unless($revision->created_by !== $actorId, 403, 'The pattern revision creator cannot approve their own revision.');
            abort_unless($revision->status === 'DRAFT', 422, 'Only a draft pattern revision can be approved.');

            $style = $this->findStyle($revision->style_id, $tenantId);
            abort_unless((int) $revision->style_revision_no === (int) $style->current_revision, 422, 'This pattern revision is based on a superseded style revision.');
            $spec = $this->findApprovedMeasurementSpec($tenantId, $revision->style_id, $revision->measurement_spec_version_id);
            abort_unless((int) $spec->style_revision_no === (int) $revision->style_revision_no, 422, 'The approved measurement specification must reference the exact style revision used by the pattern.');
            $pieceCount = DB::table('pattern_pieces')->where('tenant_id', $tenantId)->where('pattern_revision_id', $revisionId)->count();
            abort_unless($pieceCount > 0, 422, 'A pattern revision requires at least one pattern piece.');

            $rules = DB::table('grade_rules as gr')
                ->join('measurement_spec_points as mp', function ($join): void {
                    $join->on('mp.tenant_id', '=', 'gr.tenant_id')
                        ->on('mp.id', '=', 'gr.measurement_point_id');
                })
                ->where('gr.tenant_id', $tenantId)
                ->where('gr.pattern_revision_id', $revisionId)
                ->get(['mp.point_code', 'gr.from_size_code', 'gr.to_size_code', 'gr.increment', 'gr.unit_code'])
                ->map(fn (object $rule): array => (array) $rule)
                ->all();
            $this->normalizeGradeRules($tenantId, $spec, $rules, true);

            $now = now();
            DB::table('pattern_revisions')->where('tenant_id', $tenantId)->where('id', $revisionId)->update([
                'status' => 'APPROVED',
                'approved_by' => $actorId,
                'approved_at' => $now,
            ]);
            DB::table('pattern_sets')->where('tenant_id', $tenantId)->where('id', $patternSetId)->update([
                'status' => 'APPROVED',
                'current_revision' => $revision->revision_no,
                'updated_at' => $now,
            ]);
            DB::table('styles')->where('tenant_id', $tenantId)->where('id', $revision->style_id)->update([
                'status' => 'PATTERN_DEVELOPMENT',
                'updated_at' => $now,
            ]);
            $this->writeAudit($tenantId, $actorId, 'PATTERN_REVISION_APPROVED', 'PATTERN_SET', $patternSetId, 'Independent approval of a pattern revision with complete validated grade coverage.');

            return [
                'data' => DB::table('pattern_revisions')->where('tenant_id', $tenantId)->where('id', $revisionId)->first(),
                'pattern_set' => DB::table('pattern_sets')->where('tenant_id', $tenantId)->where('id', $patternSetId)->first(),
            ];
        });

        return response()->json($result);
    }

    private function validateMeasurementPayload(array $data): void
    {
        $sizes = array_values($data['size_sequence']);
        if (! in_array($data['base_size_code'], $sizes, true)) {
            throw ValidationException::withMessages(['base_size_code' => 'The base size must be included in the ordered size sequence.']);
        }

        $pointCodes = [];
        foreach ($data['points'] as $index => $point) {
            $code = strtoupper(trim($point['point_code']));
            if (in_array($code, $pointCodes, true)) {
                throw ValidationException::withMessages(["points.{$index}.point_code" => 'Measurement point codes must be unique within a specification version.']);
            }
            $pointCodes[] = $code;

            $valueSizes = array_map(fn (array $value): string => $value['size_code'], $point['values']);
            if (count(array_unique($valueSizes)) !== count($valueSizes) || array_diff($sizes, $valueSizes) !== [] || array_diff($valueSizes, $sizes) !== []) {
                throw ValidationException::withMessages(["points.{$index}.values" => 'Each measurement point must contain exactly one nominal value and tolerance for every size in the ordered size sequence.']);
            }
        }
    }

    private function normalizePieces(array $pieces): array
    {
        $seen = [];
        $normalized = [];
        foreach ($pieces as $index => $piece) {
            $code = strtoupper(trim($piece['piece_code']));
            if (in_array($code, $seen, true)) {
                throw ValidationException::withMessages(["pieces.{$index}.piece_code" => 'Pattern piece codes must be unique within a revision.']);
            }
            $seen[] = $code;
            $normalized[] = [
                'piece_code' => $code,
                'description' => trim($piece['description']),
                'material_reference' => isset($piece['material_reference']) ? trim($piece['material_reference']) : null,
                'grain_direction' => isset($piece['grain_direction']) ? trim($piece['grain_direction']) : null,
                'seam_allowance' => $piece['seam_allowance'] ?? [],
                'piece_count' => (int) $piece['piece_count'],
            ];
        }

        return $normalized;
    }

    private function normalizeGradeRules(string $tenantId, object $spec, array $rules, bool $requireComplete): array
    {
        $sizes = $this->decodeArray($spec->size_sequence);
        $sizeIndex = array_flip($sizes);
        $points = DB::table('measurement_spec_points')
            ->where('tenant_id', $tenantId)
            ->where('measurement_spec_version_id', $spec->id)
            ->get()
            ->keyBy('point_code');
        $values = DB::table('measurement_spec_values as mv')
            ->join('measurement_spec_points as mp', function ($join): void {
                $join->on('mp.tenant_id', '=', 'mv.tenant_id')
                    ->on('mp.id', '=', 'mv.measurement_point_id');
            })
            ->where('mv.tenant_id', $tenantId)
            ->where('mp.measurement_spec_version_id', $spec->id)
            ->get(['mp.point_code', 'mv.size_code', 'mv.nominal_value', 'mv.tolerance_minus', 'mv.tolerance_plus'])
            ->groupBy('point_code');

        $seen = [];
        $normalized = [];
        foreach ($rules as $index => $rule) {
            $pointCode = strtoupper(trim($rule['point_code']));
            $point = $points->get($pointCode);
            if ($point === null) {
                throw ValidationException::withMessages(["grade_rules.{$index}.point_code" => 'The grade rule references a point outside the selected approved measurement specification.']);
            }
            $from = $rule['from_size_code'];
            $to = $rule['to_size_code'];
            if (! isset($sizeIndex[$from], $sizeIndex[$to]) || $sizeIndex[$to] !== $sizeIndex[$from] + 1) {
                throw ValidationException::withMessages(["grade_rules.{$index}.to_size_code" => 'A grade rule must connect adjacent sizes in the measurement specification sequence.']);
            }
            if (strtoupper($rule['unit_code']) !== strtoupper($point->unit_code)) {
                throw ValidationException::withMessages(["grade_rules.{$index}.unit_code" => 'The grade increment unit must match the measurement point unit.']);
            }
            $key = $pointCode.'|'.$from.'|'.$to;
            if (isset($seen[$key])) {
                throw ValidationException::withMessages(["grade_rules.{$index}" => 'Duplicate grade rule for the same point and adjacent size pair.']);
            }
            $seen[$key] = true;

            $pointValues = $values->get($pointCode, collect())->keyBy('size_code');
            $fromValue = $pointValues->get($from);
            $toValue = $pointValues->get($to);
            if ($fromValue === null || $toValue === null) {
                throw ValidationException::withMessages(["grade_rules.{$index}" => 'Both sizes must have nominal measurement values before a grade rule can be recorded.']);
            }

            $expectedIncrement = (float) $toValue->nominal_value - (float) $fromValue->nominal_value;
            $allowedDeviation = (float) $fromValue->tolerance_minus
                + (float) $fromValue->tolerance_plus
                + (float) $toValue->tolerance_minus
                + (float) $toValue->tolerance_plus;
            if (abs((float) $rule['increment'] - $expectedIncrement) > $allowedDeviation + 0.0001) {
                throw ValidationException::withMessages(["grade_rules.{$index}.increment" => 'The grade increment is inconsistent with the approved size measurements and tolerances.']);
            }

            $normalized[] = [
                'measurement_spec_version_id' => $spec->id,
                'measurement_point_id' => $point->id,
                'from_size_code' => $from,
                'to_size_code' => $to,
                'increment' => $rule['increment'],
                'unit_code' => strtoupper($rule['unit_code']),
            ];
        }

        if ($requireComplete) {
            foreach ($points as $point) {
                if (! $point->is_required) {
                    continue;
                }
                foreach (array_slice($sizes, 0, -1) as $index => $from) {
                    $to = $sizes[$index + 1];
                    $key = $point->point_code.'|'.$from.'|'.$to;
                    if (! isset($seen[$key])) {
                        throw ValidationException::withMessages(['grade_rules' => "Every required measurement point needs an approved grade increment for adjacent sizes; missing {$point->point_code}: {$from} to {$to}."]);
                    }
                }
            }
        }

        return $normalized;
    }

    private function insertPatternRevision(
        string $tenantId,
        string $actorId,
        string $patternSetId,
        string $styleId,
        int $styleRevisionNo,
        object $spec,
        int $revisionNo,
        string $changeSummary,
        string $revisionId,
        array $pieces,
        array $gradeRules,
        mixed $now,
    ): void {
        DB::table('pattern_revisions')->insert([
            'id' => $revisionId,
            'tenant_id' => $tenantId,
            'pattern_set_id' => $patternSetId,
            'style_id' => $styleId,
            'style_revision_no' => $styleRevisionNo,
            'measurement_spec_version_id' => $spec->id,
            'revision_no' => $revisionNo,
            'size_system_code' => $spec->size_system_code,
            'base_size_code' => $spec->base_size_code,
            'status' => 'DRAFT',
            'change_summary' => $changeSummary,
            'created_by' => $actorId,
            'approved_by' => null,
            'approved_at' => null,
            'created_at' => $now,
        ]);

        foreach ($pieces as $piece) {
            DB::table('pattern_pieces')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'pattern_revision_id' => $revisionId,
                'piece_code' => $piece['piece_code'],
                'description' => $piece['description'],
                'material_reference' => $piece['material_reference'],
                'grain_direction' => $piece['grain_direction'],
                'seam_allowance' => json_encode($piece['seam_allowance'], JSON_THROW_ON_ERROR),
                'piece_count' => $piece['piece_count'],
                'created_at' => $now,
            ]);
        }

        foreach ($gradeRules as $rule) {
            DB::table('grade_rules')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'pattern_revision_id' => $revisionId,
                'measurement_spec_version_id' => $rule['measurement_spec_version_id'],
                'measurement_point_id' => $rule['measurement_point_id'],
                'from_size_code' => $rule['from_size_code'],
                'to_size_code' => $rule['to_size_code'],
                'increment' => $rule['increment'],
                'unit_code' => $rule['unit_code'],
                'created_at' => $now,
            ]);
        }
    }

    private function findStyle(string $styleId, string $tenantId): object
    {
        $style = DB::table('styles')
            ->where('tenant_id', $tenantId)
            ->where('id', $styleId)
            ->first();

        abort_if($style === null, 404);

        return $style;
    }

    private function findApprovedMeasurementSpec(string $tenantId, string $styleId, string $specId): object
    {
        $spec = DB::table('measurement_spec_versions')
            ->where('tenant_id', $tenantId)
            ->where('style_id', $styleId)
            ->where('id', $specId)
            ->where('status', 'APPROVED')
            ->first();

        abort_if($spec === null, 422, 'The selected measurement specification must be approved for this style.');

        return $spec;
    }

    private function assertStyleEditor(object $style, string $actorId, TenantContext $tenantContext): void
    {
        abort_unless($style->owner_user_id === $actorId || $tenantContext->canAdministerTenant(), 403, 'Only the style owner or an authorized tenant administrator may manage technical product development.');
    }

    private function decodeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
    }

    private function writeAudit(string $tenantId, string $actorId, string $action, string $objectType, string $objectId, string $reason): void
    {
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'scope' => json_encode(['module' => 'PRODUCT_DEVELOPMENT'], JSON_THROW_ON_ERROR),
            'reason' => $reason,
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
