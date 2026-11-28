<?php

namespace App\Http\Controllers\ProductDevelopment;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SampleFitController extends Controller
{
    public function sampleRequests(string $styleId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $this->findStyle($styleId, $tenantId);

        return response()->json([
            'data' => DB::table('sample_requests as sr')
                ->join('pattern_sets as ps', function ($join): void {
                    $join->on('ps.tenant_id', '=', 'sr.tenant_id')
                        ->on('ps.id', '=', 'sr.pattern_set_id');
                })
                ->where('sr.tenant_id', $tenantId)
                ->where('sr.style_id', $styleId)
                ->orderByDesc('sr.created_at')
                ->paginate(50, [
                    'sr.*',
                    'ps.pattern_code',
                    'ps.name as pattern_name',
                ]),
        ]);
    }

    public function showSampleRequest(string $sampleRequestId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $sampleRequest = $this->findSampleRequest($sampleRequestId, $tenantId);

        $rounds = DB::table('sample_rounds')
            ->where('tenant_id', $tenantId)
            ->where('sample_request_id', $sampleRequestId)
            ->orderBy('round_no')
            ->get();

        $sessions = DB::table('fit_sessions')
            ->where('tenant_id', $tenantId)
            ->where('sample_request_id', $sampleRequestId)
            ->orderBy('session_date')
            ->orderBy('created_at')
            ->get();

        foreach ($sessions as $session) {
            $session->observations = DB::table('fit_observations')
                ->where('tenant_id', $tenantId)
                ->where('fit_session_id', $session->id)
                ->orderBy('created_at')
                ->get();

            $session->decisions = DB::table('fit_decisions')
                ->where('tenant_id', $tenantId)
                ->where('fit_session_id', $session->id)
                ->orderBy('decided_at')
                ->get()
                ->values();
        }

        return response()->json([
            'data' => $sampleRequest,
            'rounds' => $rounds,
            'fit_sessions' => $sessions,
        ]);
    }

    public function createSampleRequest(string $styleId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'pattern_set_id' => ['required', 'uuid'],
            'pattern_revision_id' => ['required', 'uuid'],
            'sample_type' => ['required', Rule::in(['PROTOTYPE', 'FIT', 'SIZE_SET', 'SALESMAN', 'PHOTO', 'PRE_PRODUCTION', 'WASH_TEST', 'STRIKE_OFF', 'PILOT', 'COUNTER'])],
            'purpose' => ['required', 'string', 'min:10', 'max:4000'],
            'requested_quantity' => ['required', 'integer', 'min:1', 'max:200'],
            'requested_size_codes' => ['required', 'array', 'min:1', 'max:30'],
            'requested_size_codes.*' => ['required', 'string', 'max:32', 'distinct'],
            'requested_colour_codes' => ['nullable', 'array', 'max:60'],
            'requested_colour_codes.*' => ['required', 'string', 'max:40', 'distinct'],
            'required_by' => ['nullable', 'date'],
        ]);

        $style = $this->findStyle($styleId, $tenantId);
        $this->assertStyleEditor($style, $actorId, $tenantContext);
        abort_unless($style->status === 'PATTERN_DEVELOPMENT', 422, 'A currently approved pattern is required before sample development can start.');

        $source = DB::table('pattern_revisions as pr')
            ->join('pattern_sets as ps', function ($join): void {
                $join->on('ps.tenant_id', '=', 'pr.tenant_id')
                    ->on('ps.id', '=', 'pr.pattern_set_id');
            })
            ->where('pr.tenant_id', $tenantId)
            ->where('pr.style_id', $styleId)
            ->where('pr.pattern_set_id', $data['pattern_set_id'])
            ->where('pr.id', $data['pattern_revision_id'])
            ->first([
                'pr.id as pattern_revision_id',
                'pr.revision_no as pattern_revision_no',
                'pr.style_revision_no',
                'pr.measurement_spec_version_id',
                'pr.status as pattern_revision_status',
                'ps.id as pattern_set_id',
                'ps.status as pattern_set_status',
                'ps.current_revision',
            ]);

        abort_if($source === null, 404);
        abort_unless(
            $source->pattern_revision_status === 'APPROVED'
            && $source->pattern_set_status === 'APPROVED'
            && (int) $source->current_revision === (int) $source->pattern_revision_no
            && (int) $source->style_revision_no === (int) $style->current_revision,
            422,
            'Sample requests require the current approved pattern revision for the current style revision.',
        );

        $spec = $this->findApprovedMeasurementSpec($tenantId, $styleId, $source->measurement_spec_version_id);
        abort_unless((int) $spec->style_revision_no === (int) $source->style_revision_no, 422, 'The pattern and measurement specification must reference the same style revision.');

        $sizeSequence = $this->decodeArray($spec->size_sequence);
        $requestedSizes = array_values(array_unique($data['requested_size_codes']));
        $invalidSizes = array_values(array_diff($requestedSizes, $sizeSequence));
        if ($invalidSizes !== []) {
            throw ValidationException::withMessages([
                'requested_size_codes' => 'Every requested sample size must exist in the approved measurement size sequence.',
            ]);
        }

        $sampleRequestId = (string) Str::uuid();
        $sampleRoundId = (string) Str::uuid();
        $now = now();

        DB::transaction(function () use ($tenantId, $actorId, $styleId, $source, $spec, $data, $requestedSizes, $sampleRequestId, $sampleRoundId, $now): void {
            DB::table('sample_requests')->insert([
                'id' => $sampleRequestId,
                'tenant_id' => $tenantId,
                'style_id' => $styleId,
                'style_revision_no' => $source->style_revision_no,
                'pattern_set_id' => $source->pattern_set_id,
                'pattern_revision_id' => $source->pattern_revision_id,
                'measurement_spec_version_id' => $spec->id,
                'sample_type' => $data['sample_type'],
                'purpose' => trim($data['purpose']),
                'requested_quantity' => $data['requested_quantity'],
                'requested_size_codes' => json_encode($requestedSizes, JSON_THROW_ON_ERROR),
                'requested_colour_codes' => json_encode(array_values($data['requested_colour_codes'] ?? []), JSON_THROW_ON_ERROR),
                'required_by' => $data['required_by'] ?? null,
                'status' => 'REQUESTED',
                'current_round_no' => 1,
                'owner_user_id' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('sample_rounds')->insert([
                'id' => $sampleRoundId,
                'tenant_id' => $tenantId,
                'sample_request_id' => $sampleRequestId,
                'round_no' => 1,
                'planned_quantity' => $data['requested_quantity'],
                'actual_quantity' => null,
                'status' => 'REQUESTED',
                'round_reason' => 'Initial sample build round.',
                'completion_summary' => null,
                'started_at' => null,
                'completed_at' => null,
                'created_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->writeAudit($tenantId, $actorId, 'SAMPLE_REQUEST_CREATED', 'SAMPLE_REQUEST', $sampleRequestId, trim($data['purpose']));
        });

        return response()->json([
            'data' => DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequestId)->first(),
            'round' => DB::table('sample_rounds')->where('tenant_id', $tenantId)->where('id', $sampleRoundId)->first(),
        ], 201);
    }

    public function createSampleRound(string $sampleRequestId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'round_reason' => ['required', 'string', 'min:10', 'max:500'],
            'planned_quantity' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $result = DB::transaction(function () use ($tenantId, $actorId, $sampleRequestId, $data, $tenantContext): array {
            $sampleRequest = DB::table('sample_requests')
                ->where('tenant_id', $tenantId)
                ->where('id', $sampleRequestId)
                ->lockForUpdate()
                ->first();

            abort_if($sampleRequest === null, 404);
            $this->assertSampleRequestEditor($sampleRequest, $actorId, $tenantContext);
            abort_unless($sampleRequest->status === 'CORRECTION_REQUIRED', 422, 'A correction round can only be created after a fit decision requests correction.');

            $roundNo = (int) $sampleRequest->current_round_no + 1;
            $roundId = (string) Str::uuid();
            $now = now();

            DB::table('sample_rounds')->insert([
                'id' => $roundId,
                'tenant_id' => $tenantId,
                'sample_request_id' => $sampleRequestId,
                'round_no' => $roundNo,
                'planned_quantity' => $data['planned_quantity'] ?? $sampleRequest->requested_quantity,
                'actual_quantity' => null,
                'status' => 'REQUESTED',
                'round_reason' => trim($data['round_reason']),
                'completion_summary' => null,
                'started_at' => null,
                'completed_at' => null,
                'created_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequestId)->update([
                'status' => 'REQUESTED',
                'current_round_no' => $roundNo,
                'updated_at' => $now,
            ]);

            $this->writeAudit($tenantId, $actorId, 'SAMPLE_CORRECTION_ROUND_CREATED', 'SAMPLE_REQUEST', $sampleRequestId, trim($data['round_reason']));

            return [
                'data' => DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequestId)->first(),
                'round' => DB::table('sample_rounds')->where('tenant_id', $tenantId)->where('id', $roundId)->first(),
            ];
        });

        return response()->json($result, 201);
    }

    public function startSampleRound(string $sampleRequestId, string $roundId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();

        $round = DB::transaction(function () use ($tenantId, $actorId, $sampleRequestId, $roundId, $tenantContext): object {
            $sampleRequest = DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequestId)->lockForUpdate()->first();
            abort_if($sampleRequest === null, 404);
            $this->assertSampleRequestEditor($sampleRequest, $actorId, $tenantContext);

            $round = DB::table('sample_rounds')
                ->where('tenant_id', $tenantId)
                ->where('sample_request_id', $sampleRequestId)
                ->where('id', $roundId)
                ->lockForUpdate()
                ->first();

            abort_if($round === null, 404);
            abort_unless($round->status === 'REQUESTED' && (int) $round->round_no === (int) $sampleRequest->current_round_no, 422, 'Only the current requested sample round can be started.');

            $now = now();
            DB::table('sample_rounds')->where('tenant_id', $tenantId)->where('id', $roundId)->update([
                'status' => 'IN_PROGRESS',
                'started_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequestId)->update([
                'status' => 'SAMPLE_IN_PROGRESS',
                'updated_at' => $now,
            ]);

            $this->writeAudit($tenantId, $actorId, 'SAMPLE_ROUND_STARTED', 'SAMPLE_REQUEST', $sampleRequestId, 'Sample-room build started.');

            return DB::table('sample_rounds')->where('tenant_id', $tenantId)->where('id', $roundId)->first();
        });

        return response()->json(['data' => $round]);
    }

    public function completeSampleRound(string $sampleRequestId, string $roundId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'actual_quantity' => ['required', 'integer', 'min:1', 'max:200'],
            'completion_summary' => ['required', 'string', 'min:10', 'max:4000'],
        ]);

        $round = DB::transaction(function () use ($tenantId, $actorId, $sampleRequestId, $roundId, $data, $tenantContext): object {
            $sampleRequest = DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequestId)->lockForUpdate()->first();
            abort_if($sampleRequest === null, 404);
            $this->assertSampleRequestEditor($sampleRequest, $actorId, $tenantContext);

            $round = DB::table('sample_rounds')
                ->where('tenant_id', $tenantId)
                ->where('sample_request_id', $sampleRequestId)
                ->where('id', $roundId)
                ->lockForUpdate()
                ->first();

            abort_if($round === null, 404);
            abort_unless($round->status === 'IN_PROGRESS', 422, 'Only an in-progress sample round can be completed.');
            abort_unless((int) $data['actual_quantity'] <= (int) $round->planned_quantity, 422, 'Actual sample quantity cannot exceed the approved planned quantity.');

            $now = now();
            DB::table('sample_rounds')->where('tenant_id', $tenantId)->where('id', $roundId)->update([
                'status' => 'COMPLETED',
                'actual_quantity' => $data['actual_quantity'],
                'completion_summary' => trim($data['completion_summary']),
                'completed_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequestId)->update([
                'status' => 'AWAITING_FIT',
                'updated_at' => $now,
            ]);

            $this->writeAudit($tenantId, $actorId, 'SAMPLE_ROUND_COMPLETED', 'SAMPLE_REQUEST', $sampleRequestId, trim($data['completion_summary']));

            return DB::table('sample_rounds')->where('tenant_id', $tenantId)->where('id', $roundId)->first();
        });

        return response()->json(['data' => $round]);
    }

    public function createFitSession(string $roundId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'fit_size_code' => ['required', 'string', 'max:32'],
            'fit_model_code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9][A-Z0-9_-]{1,79}$/'],
            'posture_code' => ['required', Rule::in(['STANDING', 'SITTING', 'MOVING', 'OTHER'])],
            'condition_notes' => ['required', 'string', 'min:5', 'max:2000'],
            'session_date' => ['required', 'date'],
        ]);

        $round = DB::table('sample_rounds as sr')
            ->join('sample_requests as sreq', function ($join): void {
                $join->on('sreq.tenant_id', '=', 'sr.tenant_id')
                    ->on('sreq.id', '=', 'sr.sample_request_id');
            })
            ->where('sr.tenant_id', $tenantId)
            ->where('sr.id', $roundId)
            ->first([
                'sr.id as sample_round_id',
                'sr.sample_request_id',
                'sr.status as round_status',
                'sreq.style_id',
                'sreq.status as sample_request_status',
                'sreq.owner_user_id',
                'sreq.measurement_spec_version_id',
                'sreq.requested_size_codes',
            ]);

        abort_if($round === null, 404);
        $this->assertSampleRequestEditor($round, $actorId, $tenantContext);
        abort_unless($round->round_status === 'COMPLETED', 422, 'A fit session can only be created from a completed sample build round.');
        abort_unless(in_array($round->sample_request_status, ['AWAITING_FIT', 'CORRECTION_REQUIRED'], true), 422, 'The sample request is not ready for fit review.');

        $requestedSizes = $this->decodeArray($round->requested_size_codes);
        abort_unless(in_array($data['fit_size_code'], $requestedSizes, true), 422, 'Fit size must be one of the sizes requested for this sample.');

        $sessionId = (string) Str::uuid();
        $now = now();

        DB::transaction(function () use ($tenantId, $actorId, $round, $data, $sessionId, $now): void {
            DB::table('fit_sessions')->insert([
                'id' => $sessionId,
                'tenant_id' => $tenantId,
                'sample_request_id' => $round->sample_request_id,
                'sample_round_id' => $round->sample_round_id,
                'measurement_spec_version_id' => $round->measurement_spec_version_id,
                'fit_size_code' => $data['fit_size_code'],
                'fit_model_code' => strtoupper($data['fit_model_code']),
                'posture_code' => $data['posture_code'],
                'condition_notes' => trim($data['condition_notes']),
                'session_date' => $data['session_date'],
                'status' => 'DRAFT',
                'created_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->writeAudit($tenantId, $actorId, 'FIT_SESSION_CREATED', 'FIT_SESSION', $sessionId, 'Fit session created against the pinned approved measurement specification.');
        });

        return response()->json([
            'data' => DB::table('fit_sessions')->where('tenant_id', $tenantId)->where('id', $sessionId)->first(),
        ], 201);
    }

    public function recordFitObservations(string $fitSessionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'observations' => ['required', 'array', 'min:1', 'max:100'],
            'observations.*.point_code' => ['required', 'string', 'max:80'],
            'observations.*.observed_value' => ['required', 'numeric', 'min:0', 'decimal:0,4'],
            'observations.*.issue_code' => ['required', 'string', 'max:80'],
            'observations.*.severity' => ['required', Rule::in(['NONE', 'MINOR', 'MAJOR', 'CRITICAL'])],
            'observations.*.finding' => ['nullable', 'string', 'max:2000'],
            'observations.*.corrective_action' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($tenantId, $actorId, $fitSessionId, $data, $tenantContext): void {
            $session = DB::table('fit_sessions')
                ->where('tenant_id', $tenantId)
                ->where('id', $fitSessionId)
                ->lockForUpdate()
                ->first();

            abort_if($session === null, 404);
            $sampleRequest = $this->findSampleRequest($session->sample_request_id, $tenantId);
            $this->assertSampleRequestEditor($sampleRequest, $actorId, $tenantContext);
            abort_unless($session->status === 'DRAFT', 422, 'Fit observations cannot be changed after the session is submitted.');

            $seenPointCodes = [];
            foreach ($data['observations'] as $index => $observation) {
                $pointCode = strtoupper(trim($observation['point_code']));
                if (in_array($pointCode, $seenPointCodes, true)) {
                    throw ValidationException::withMessages(["observations.{$index}.point_code" => 'A measurement point can only be recorded once in a fit session.']);
                }
                $seenPointCodes[] = $pointCode;

                $point = DB::table('measurement_spec_points')
                    ->where('tenant_id', $tenantId)
                    ->where('measurement_spec_version_id', $session->measurement_spec_version_id)
                    ->where('point_code', $pointCode)
                    ->first();

                abort_if($point === null, 422, 'A fit observation references a point outside the pinned measurement specification.');

                $target = DB::table('measurement_spec_values')
                    ->where('tenant_id', $tenantId)
                    ->where('measurement_point_id', $point->id)
                    ->where('size_code', $session->fit_size_code)
                    ->first();

                abort_if($target === null, 422, 'The approved measurement specification does not contain a nominal value for the fit size.');

                $delta = round((float) $observation['observed_value'] - (float) $target->nominal_value, 4);
                $withinTolerance = $delta >= -((float) $target->tolerance_minus) && $delta <= (float) $target->tolerance_plus;

                DB::table('fit_observations')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'fit_session_id' => $fitSessionId,
                    'measurement_spec_version_id' => $session->measurement_spec_version_id,
                    'measurement_point_id' => $point->id,
                    'size_code' => $session->fit_size_code,
                    'nominal_value' => $target->nominal_value,
                    'tolerance_minus' => $target->tolerance_minus,
                    'tolerance_plus' => $target->tolerance_plus,
                    'observed_value' => $observation['observed_value'],
                    'unit_code' => $point->unit_code,
                    'issue_code' => strtoupper(trim($observation['issue_code'])),
                    'severity' => $observation['severity'],
                    'within_tolerance' => $withinTolerance,
                    'finding' => $observation['finding'] ?? null,
                    'corrective_action' => $observation['corrective_action'] ?? null,
                    'created_by' => $actorId,
                    'created_at' => now(),
                ]);
            }

            $this->writeAudit($tenantId, $actorId, 'FIT_OBSERVATIONS_RECORDED', 'FIT_SESSION', $fitSessionId, 'Fit observations stored with the nominal and tolerance snapshot from the pinned measurement specification.');
        });

        return response()->json([
            'data' => DB::table('fit_observations')->where('tenant_id', $tenantId)->where('fit_session_id', $fitSessionId)->orderBy('created_at')->get(),
        ], 201);
    }

    public function submitFitSession(string $fitSessionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();

        $session = DB::transaction(function () use ($tenantId, $actorId, $fitSessionId, $tenantContext): object {
            $session = DB::table('fit_sessions')
                ->where('tenant_id', $tenantId)
                ->where('id', $fitSessionId)
                ->lockForUpdate()
                ->first();

            abort_if($session === null, 404);
            $sampleRequest = $this->findSampleRequest($session->sample_request_id, $tenantId);
            $this->assertSampleRequestEditor($sampleRequest, $actorId, $tenantContext);
            abort_unless($session->status === 'DRAFT', 422, 'Only a draft fit session can be submitted for review.');

            $requiredPoints = DB::table('measurement_spec_points')
                ->where('tenant_id', $tenantId)
                ->where('measurement_spec_version_id', $session->measurement_spec_version_id)
                ->where('is_required', true)
                ->pluck('id')
                ->all();
            $recordedPoints = DB::table('fit_observations')
                ->where('tenant_id', $tenantId)
                ->where('fit_session_id', $fitSessionId)
                ->pluck('measurement_point_id')
                ->all();
            $missing = array_values(array_diff($requiredPoints, $recordedPoints));
            abort_unless($missing === [], 422, 'Every required measurement point must be recorded before fit review.');

            DB::table('fit_sessions')->where('tenant_id', $tenantId)->where('id', $fitSessionId)->update([
                'status' => 'SUBMITTED_FOR_REVIEW',
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'FIT_SESSION_SUBMITTED', 'FIT_SESSION', $fitSessionId, 'Complete fit measurement set submitted for independent disposition.');

            return DB::table('fit_sessions')->where('tenant_id', $tenantId)->where('id', $fitSessionId)->first();
        });

        return response()->json(['data' => $session]);
    }

    public function decideFitSession(string $fitSessionId, Request $request, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or admin approval is required.');

        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $data = $request->validate([
            'decision' => ['required', Rule::in(['APPROVE', 'APPROVE_WITH_COMMENTS', 'CORRECTION_REQUIRED', 'REJECT'])],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'actions' => ['nullable', 'array', 'max:20'],
            'actions.*' => ['string', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($tenantId, $actorId, $fitSessionId, $data): array {
            $session = DB::table('fit_sessions')
                ->where('tenant_id', $tenantId)
                ->where('id', $fitSessionId)
                ->lockForUpdate()
                ->first();

            abort_if($session === null, 404);
            abort_unless($session->created_by !== $actorId, 403, 'The fit session creator cannot approve their own fit disposition.');
            abort_unless($session->status === 'SUBMITTED_FOR_REVIEW', 422, 'Only a submitted fit session can receive an independent disposition.');

            $observations = DB::table('fit_observations')
                ->where('tenant_id', $tenantId)
                ->where('fit_session_id', $fitSessionId)
                ->get();
            abort_unless($observations->isNotEmpty(), 422, 'A fit disposition requires at least one recorded observation.');

            $outsideTolerance = $observations->contains(fn (object $item): bool => ! (bool) $item->within_tolerance);
            $seriousFinding = $observations->contains(fn (object $item): bool => in_array($item->severity, ['MAJOR', 'CRITICAL'], true));
            $minorFinding = $observations->contains(fn (object $item): bool => $item->severity === 'MINOR');
            $anyFinding = $observations->contains(fn (object $item): bool => $item->severity !== 'NONE');

            if ($data['decision'] === 'APPROVE') {
                abort_unless(! $outsideTolerance && ! $anyFinding, 422, 'Fit cannot be approved while a required measurement is outside tolerance or an issue is recorded.');
            }

            if ($data['decision'] === 'APPROVE_WITH_COMMENTS') {
                abort_unless(! $outsideTolerance && ! $seriousFinding, 422, 'Fit with comments cannot be approved while measurements are outside tolerance or a major/critical issue remains.');
                abort_unless($minorFinding || ! empty($data['actions']), 422, 'Approval with comments requires a recorded minor finding or an explicit follow-up action.');
            }

            if ($data['decision'] === 'CORRECTION_REQUIRED') {
                abort_unless($outsideTolerance || $anyFinding || ! empty($data['actions']), 422, 'Correction disposition requires an out-of-tolerance measurement, a recorded finding or an explicit corrective action.');
            }

            DB::table('fit_decisions')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'fit_session_id' => $fitSessionId,
                'decision' => $data['decision'],
                'reason' => trim($data['reason']),
                'actions' => json_encode($data['actions'] ?? [], JSON_THROW_ON_ERROR),
                'decided_by' => $actorId,
                'decided_at' => now(),
            ]);

            DB::table('fit_sessions')->where('tenant_id', $tenantId)->where('id', $fitSessionId)->update([
                'status' => 'DECIDED',
                'updated_at' => now(),
            ]);

            $sampleRequest = $this->findSampleRequest($session->sample_request_id, $tenantId);
            $nextStatus = $sampleRequest->status;

            if ($data['decision'] === 'CORRECTION_REQUIRED') {
                $nextStatus = 'CORRECTION_REQUIRED';
            } elseif ($data['decision'] === 'REJECT') {
                $nextStatus = 'REJECTED';
            } else {
                $requestedSizes = $this->decodeArray($sampleRequest->requested_size_codes);
                $approvedSizes = DB::table('fit_sessions as fs')
                    ->join('fit_decisions as fd', function ($join): void {
                        $join->on('fd.tenant_id', '=', 'fs.tenant_id')
                            ->on('fd.fit_session_id', '=', 'fs.id');
                    })
                    ->where('fs.tenant_id', $tenantId)
                    ->where('fs.sample_request_id', $sampleRequest->id)
                    ->whereIn('fd.decision', ['APPROVE', 'APPROVE_WITH_COMMENTS'])
                    ->pluck('fs.fit_size_code')
                    ->unique()
                    ->all();

                $nextStatus = array_diff($requestedSizes, $approvedSizes) === [] ? 'FIT_APPROVED' : 'AWAITING_FIT';
            }

            DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequest->id)->update([
                'status' => $nextStatus,
                'updated_at' => now(),
            ]);

            $this->writeAudit($tenantId, $actorId, 'FIT_DISPOSITION_RECORDED', 'FIT_SESSION', $fitSessionId, trim($data['reason']));

            return [
                'data' => DB::table('fit_sessions')->where('tenant_id', $tenantId)->where('id', $fitSessionId)->first(),
                'sample_request' => DB::table('sample_requests')->where('tenant_id', $tenantId)->where('id', $sampleRequest->id)->first(),
            ];
        });

        return response()->json($result);
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

    private function findSampleRequest(string $sampleRequestId, string $tenantId): object
    {
        $sampleRequest = DB::table('sample_requests')
            ->where('tenant_id', $tenantId)
            ->where('id', $sampleRequestId)
            ->first();

        abort_if($sampleRequest === null, 404);

        return $sampleRequest;
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
        abort_unless(
            $style->owner_user_id === $actorId || $tenantContext->canAdministerTenant(),
            403,
            'Only the style owner or an authorized tenant administrator may manage sample-room work.',
        );
    }

    private function assertSampleRequestEditor(object $sampleRequest, string $actorId, TenantContext $tenantContext): void
    {
        abort_unless(
            $sampleRequest->owner_user_id === $actorId || $tenantContext->canAdministerTenant(),
            403,
            'Only the sample request owner or an authorized tenant administrator may manage this sample.',
        );
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
