<?php

namespace Tests\Feature;

use App\Services\Quality\QualityControlService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('postgresql')]
class QualityInspectionReviewStateTest extends TestCase
{
    private string $tenantId;
    private string $inspectorId;
    private string $reviewerId;
    private string $inspectionId;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = DB::connection('pgsql_test_admin');
        $now = now();

        $this->tenantId = (string) Str::uuid();
        $this->inspectorId = (string) Str::uuid();
        $this->reviewerId = (string) Str::uuid();
        $this->inspectionId = (string) Str::uuid();

        $admin->transaction(function () use ($admin, $now): void {
            $admin->table('tenants')->insert([
                'id' => $this->tenantId,
                'code' => 'QUALITY-'.Str::upper(Str::random(8)),
                'name' => 'Quality review test tenant',
                'status' => 'ACTIVE',
                'locale' => 'en',
                'timezone' => 'Africa/Addis_Ababa',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $admin->table('users')->insert([
                [
                    'id' => $this->inspectorId,
                    'name' => 'Quality Inspector',
                    'email' => 'inspector-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('quality-inspector-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => $this->reviewerId,
                    'name' => 'Independent Quality Reviewer',
                    'email' => 'reviewer-'.Str::lower(Str::random(8)).'@example.test',
                    'password' => Hash::make('quality-reviewer-test-password'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);

            $admin->table('quality_inspections')->insert([
                'id' => $this->inspectionId,
                'tenant_id' => $this->tenantId,
                'inspection_code' => 'QI-'.Str::upper(Str::random(8)),
                'inspection_type' => 'FINAL',
                'subject_type' => 'PRODUCTION_ORDER',
                'subject_id' => (string) Str::uuid(),
                'sample_quantity' => '10.0000',
                'accepted_quantity' => '0.0000',
                'rejected_quantity' => '0.0000',
                'result_status' => 'DRAFT',
                'measurements' => json_encode([], JSON_THROW_ON_ERROR),
                'observations' => json_encode([], JSON_THROW_ON_ERROR),
                'inspected_by' => $this->inspectorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function test_capa_creation_and_nonconformance_state_change_are_committed_together(): void
    {
        $admin = DB::connection('pgsql_test_admin');
        $nonconformanceId = (string) Str::uuid();
        $admin->table('quality_nonconformances')->insert([
            'id' => $nonconformanceId,
            'tenant_id' => $this->tenantId,
            'inspection_id' => $this->inspectionId,
            'nonconformance_code' => 'NC-'.Str::upper(Str::random(8)),
            'defect_description' => 'Test quality nonconformance requiring corrective action.',
            'severity' => 'MEDIUM',
            'affected_quantity' => '1.0000',
            'containment_status' => 'REQUIRED',
            'disposition' => 'PENDING',
            'status' => 'OPEN',
            'opened_by' => $this->inspectorId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(QualityControlService::class);

        DB::transaction(function () use ($service, $nonconformanceId): void {
            DB::selectOne("SELECT set_config('app.current_tenant_id', ?, true)", [$this->tenantId]);

            $action = $service->createCorrectiveAction($this->tenantId, $this->inspectorId, $nonconformanceId, [
                'action_code' => 'CAPA-'.Str::upper(Str::random(8)),
                'action_type' => 'CORRECTIVE',
                'action_description' => 'Correct the source process and verify effectiveness.',
                'effectiveness_criteria' => 'No repeat defect in the defined verification period.',
            ]);

            self::assertSame($nonconformanceId, $action->nonconformance_id);
            self::assertSame('OPEN', $action->status);

            $nonconformance = DB::table('quality_nonconformances')
                ->where('tenant_id', $this->tenantId)
                ->where('id', $nonconformanceId)
                ->first();

            self::assertSame('CAPA_REQUIRED', $nonconformance->status);
        });
    }

    public function test_passing_inspection_can_be_submitted_then_independently_reviewed(): void
    {
        $service = app(QualityControlService::class);

        DB::transaction(function () use ($service): void {
            DB::selectOne("SELECT set_config('app.current_tenant_id', ?, true)", [$this->tenantId]);

            $submitted = $service->submitInspectionResult($this->tenantId, $this->inspectorId, $this->inspectionId, [
                'accepted_quantity' => '10.0000',
                'rejected_quantity' => '0.0000',
                'defects' => [],
                'observations' => [],
            ]);

            self::assertSame('SUBMITTED_PENDING_REVIEW', $submitted->result_status);
            self::assertSame('PASS', $submitted->submitted_result_status);
            self::assertNull($submitted->reviewed_by);
            self::assertNull($submitted->reviewed_at);

            try {
                $service->reviewInspection($this->tenantId, $this->inspectorId, $this->inspectionId, [
                    'decision' => 'ACCEPT',
                    'review_reason' => 'This must fail because the inspector cannot review their own result.',
                ]);
                self::fail('The inspector must not independently review their own inspection.');
            } catch (\Illuminate\Validation\ValidationException) {
                // Expected: maker-checker separation remains enforced.
            }

            $reviewed = $service->reviewInspection($this->tenantId, $this->reviewerId, $this->inspectionId, [
                'decision' => 'ACCEPT',
                'review_reason' => 'Independent review confirms the submitted passing result.',
            ]);

            self::assertSame('PASS', $reviewed->result_status);
            self::assertSame('PASS', $reviewed->submitted_result_status);
            self::assertSame($this->reviewerId, $reviewed->reviewed_by);
            self::assertNotNull($reviewed->reviewed_at);
        });
    }
}
