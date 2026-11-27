<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Quality\QualityControlService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('postgresql')]
class QualityInspectionReviewTransitionTest extends TestCase
{
    public function test_pass_result_can_be_submitted_before_independent_review_and_is_not_release_eligible(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('The inspection review transition requires PostgreSQL.');
        }

        $admin = DB::connection('pgsql_test_admin');
        $tenantId = (string) Str::uuid();
        $inspectorId = (string) Str::uuid();
        $reviewerId = (string) Str::uuid();
        $inspectionId = (string) Str::uuid();
        $now = now();

        $admin->table('tenants')->insert([
            'id' => $tenantId,
            'code' => 'QUALITY-'.Str::upper(Str::random(10)),
            'name' => 'Quality review transition test tenant',
            'status' => 'ACTIVE',
            'locale' => 'en',
            'timezone' => 'Africa/Addis_Ababa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $admin->table('users')->insert([
            [
                'id' => $inspectorId,
                'name' => 'Quality Inspector',
                'email' => 'quality-inspector-'.Str::lower(Str::random(10)).'@example.test',
                'password' => Hash::make('quality-test-inspector-password'),
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $reviewerId,
                'name' => 'Independent Quality Reviewer',
                'email' => 'quality-reviewer-'.Str::lower(Str::random(10)).'@example.test',
                'password' => Hash::make('quality-test-reviewer-password'),
                'status' => 'ACTIVE',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $admin->table('quality_inspections')->insert([
            'id' => $inspectionId,
            'tenant_id' => $tenantId,
            'inspection_code' => 'FINAL-'.Str::upper(Str::random(10)),
            'inspection_type' => 'FINAL',
            'subject_type' => 'PRODUCTION_ORDER',
            'subject_id' => (string) Str::uuid(),
            'sample_quantity' => '1.0000',
            'accepted_quantity' => '0.0000',
            'rejected_quantity' => '0.0000',
            'result_status' => 'DRAFT',
            'measurements' => json_encode([], JSON_THROW_ON_ERROR),
            'observations' => json_encode([], JSON_THROW_ON_ERROR),
            'inspected_by' => $inspectorId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::transaction(function () use ($tenantId, $inspectorId, $reviewerId, $inspectionId): void {
            DB::selectOne("SELECT set_config('app.current_tenant_id', ?, true)", [$tenantId]);
            DB::selectOne("SELECT set_config('app.current_user_id', ?, true)", [$inspectorId]);

            $quality = app(QualityControlService::class);
            $submitted = $quality->submitInspectionResult(
                $tenantId,
                $inspectorId,
                $inspectionId,
                [
                    'accepted_quantity' => '1.0000',
                    'rejected_quantity' => '0.0000',
                    'measurements' => [],
                    'observations' => [],
                    'defects' => [],
                ],
            );

            self::assertSame('PASS', $submitted->result_status);
            self::assertNull($submitted->reviewed_by);
            self::assertNull($submitted->reviewed_at);

            $releaseEligibleBeforeReview = DB::table('quality_inspections')
                ->where('tenant_id', $tenantId)
                ->where('id', $inspectionId)
                ->whereIn('result_status', ['PASS', 'PASS_WITH_DEVIATION'])
                ->whereNotNull('reviewed_by')
                ->whereNotNull('reviewed_at')
                ->exists();

            self::assertFalse($releaseEligibleBeforeReview);

            $reviewed = $quality->reviewInspection(
                $tenantId,
                $reviewerId,
                $inspectionId,
                [
                    'decision' => 'ACCEPT',
                    'review_reason' => 'Independent review confirmed the submitted final inspection result.',
                ],
            );

            self::assertSame('PASS', $reviewed->result_status);
            self::assertSame($reviewerId, $reviewed->reviewed_by);
            self::assertNotNull($reviewed->reviewed_at);

            $releaseEligibleAfterReview = DB::table('quality_inspections')
                ->where('tenant_id', $tenantId)
                ->where('id', $inspectionId)
                ->whereIn('result_status', ['PASS', 'PASS_WITH_DEVIATION'])
                ->whereNotNull('reviewed_by')
                ->whereNotNull('reviewed_at')
                ->exists();

            self::assertTrue($releaseEligibleAfterReview);
        });
    }
}
