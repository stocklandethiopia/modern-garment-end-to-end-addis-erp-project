<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnterpriseOrganizationStructureTest extends TestCase
{
    private string $tenantA;
    private string $tenantB;
    private string $ownerA;
    private string $memberA;
    private string $ownerB;
    private string $legalEntityA;
    private string $siteA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = (string) Str::uuid();
        $this->tenantB = (string) Str::uuid();
        $this->ownerA = (string) Str::uuid();
        $this->memberA = (string) Str::uuid();
        $this->ownerB = (string) Str::uuid();
        $this->legalEntityA = (string) Str::uuid();
        $this->siteA = (string) Str::uuid();

        $admin = DB::connection('pgsql_test_admin');
        $now = now();

        $admin->transaction(function () use ($admin, $now): void {
            foreach ([
                [$this->tenantA, 'ORG-A-'.Str::upper(Str::random(6)), 'Organization Test A'],
                [$this->tenantB, 'ORG-B-'.Str::upper(Str::random(6)), 'Organization Test B'],
            ] as [$tenantId, $code, $name]) {
                $admin->table('tenants')->insert([
                    'id' => $tenantId,
                    'code' => $code,
                    'name' => $name,
                    'status' => 'ACTIVE',
                    'locale' => 'en',
                    'timezone' => 'Africa/Addis_Ababa',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ([
                [$this->ownerA, 'org-owner-a-'.Str::lower(Str::random(8)).'@example.test', 'Organization Owner A'],
                [$this->memberA, 'org-member-a-'.Str::lower(Str::random(8)).'@example.test', 'Organization Member A'],
                [$this->ownerB, 'org-owner-b-'.Str::lower(Str::random(8)).'@example.test', 'Organization Owner B'],
            ] as [$id, $email, $name]) {
                $admin->table('users')->insert([
                    'id' => $id,
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('long-test-password-for-organization'),
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ([
                [$this->tenantA, $this->ownerA, 'OWNER'],
                [$this->tenantA, $this->memberA, 'MEMBER'],
                [$this->tenantB, $this->ownerB, 'OWNER'],
            ] as [$tenantId, $userId, $role]) {
                $admin->table('tenant_memberships')->insert([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'membership_role' => $role,
                    'status' => 'ACTIVE',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        $admin->table('legal_entities')->insert([
            'id' => $this->legalEntityA,
            'tenant_id' => $this->tenantA,
            'registration_number' => 'ORG-LEGAL-'.Str::upper(Str::random(8)),
            'legal_name' => 'Organization Test Legal Entity',
            'legal_form' => 'PRIVATE_COMPANY',
            'country_code' => 'ET',
            'tax_identifier' => 'ORG-TAX-'.Str::upper(Str::random(8)),
            'base_currency' => 'ETB',
            'status' => 'DRAFT',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $admin->table('business_sites')->insert([
            'id' => $this->siteA,
            'tenant_id' => $this->tenantA,
            'legal_entity_id' => $this->legalEntityA,
            'code' => 'ORG-SITE-'.Str::upper(Str::random(8)),
            'site_type' => 'HEAD_OFFICE',
            'name' => 'Organization Test Head Office',
            'country_code' => 'ET',
            'region' => 'Addis Ababa',
            'city' => 'Addis Ababa',
            'timezone' => 'Africa/Addis_Ababa',
            'status' => 'ACTIVE',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function actAs(string $userId, string $tenantId): void
    {
        Sanctum::actingAs(User::query()->findOrFail($userId), ['tenant:select', 'erp:access']);
        $this->withHeader('X-Tenant-Id', $tenantId);
    }

    private function createUnit(string $code, array $overrides = []): string
    {
        $payload = array_merge([
            'unit_code' => $code,
            'unit_name' => 'Organizational unit '.$code,
            'unit_type' => 'DEPARTMENT',
            'legal_entity_id' => $this->legalEntityA,
            'valid_from' => '2026-01-01',
        ], $overrides);

        $response = $this->postJson('/api/v1/enterprise/organization-units', $payload);
        $response->assertCreated();

        return (string) $response->json('data.id');
    }

    private function createPosition(string $unitId, string $code): string
    {
        $response = $this->postJson('/api/v1/enterprise/job-positions', [
            'org_unit_id' => $unitId,
            'position_code' => $code,
            'title' => 'Position '.$code,
            'job_family' => 'ENTERPRISE',
            'position_class' => 'MANAGEMENT',
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
        ]);
        $response->assertCreated();

        return (string) $response->json('data.id');
    }

    private function createSolidLine(string $subject, string $manager): void
    {
        $this->postJson('/api/v1/enterprise/position-reporting-relations', [
            'subject_position_id' => $subject,
            'manager_position_id' => $manager,
            'reporting_type' => 'SOLID_LINE',
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
            'reason' => 'Approved organizational reporting structure.',
        ])->assertCreated();
    }

    public function test_tenant_administrator_can_create_unit_with_audit_and_outbox_evidence(): void
    {
        $this->actAs($this->ownerA, $this->tenantA);

        $response = $this->postJson('/api/v1/enterprise/organization-units', [
            'unit_code' => 'FACTORY-OPS',
            'unit_name' => 'Factory Operations',
            'unit_type' => 'FACTORY',
            'legal_entity_id' => $this->legalEntityA,
            'business_site_id' => $this->siteA,
            'valid_from' => '2026-01-01',
        ])->assertCreated()
            ->assertJsonPath('data.unit_code', 'FACTORY-OPS')
            ->assertJsonPath('data.status', 'ACTIVE');

        $unitId = $response->json('data.id');

        $this->assertDatabaseHas('org_units', [
            'tenant_id' => $this->tenantA,
            'id' => $unitId,
            'legal_entity_id' => $this->legalEntityA,
            'business_site_id' => $this->siteA,
            'created_by' => $this->ownerA,
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantA,
            'action' => 'ORGANIZATION_UNIT_CREATED',
            'object_id' => $unitId,
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('integration_outbox_events', [
            'tenant_id' => $this->tenantA,
            'event_type' => 'OrganizationUnitCreated.v1',
            'aggregate_id' => $unitId,
            'status' => 'PENDING',
        ], 'pgsql_test_admin');
    }

    public function test_ordinary_member_can_read_but_cannot_configure_organization_structure(): void
    {
        $this->actAs($this->ownerA, $this->tenantA);
        $this->createUnit('MEMBER-READ');

        $this->actAs($this->memberA, $this->tenantA);
        $this->getJson('/api/v1/enterprise/organization-units')->assertOk();
        $this->postJson('/api/v1/enterprise/organization-units', [
            'unit_code' => 'MEMBER-WRITE',
            'unit_name' => 'Unauthorized Unit',
            'unit_type' => 'DEPARTMENT',
            'valid_from' => '2026-01-01',
        ])->assertForbidden();
    }

    public function test_unit_parent_period_and_site_entity_relationship_are_validated(): void
    {
        $this->actAs($this->ownerA, $this->tenantA);

        $parentId = $this->createUnit('PARENT-UNIT', ['valid_to' => '2026-12-31']);

        $this->postJson('/api/v1/enterprise/organization-units', [
            'parent_id' => $parentId,
            'unit_code' => 'CHILD-OUT-OF-RANGE',
            'unit_name' => 'Child outside parent period',
            'unit_type' => 'SECTION',
            'valid_from' => '2027-01-01',
        ])->assertUnprocessable();

        $otherEntityId = (string) Str::uuid();
        DB::connection('pgsql_test_admin')->table('legal_entities')->insert([
            'id' => $otherEntityId,
            'tenant_id' => $this->tenantA,
            'registration_number' => 'ORG-OTHER-'.Str::upper(Str::random(8)),
            'legal_name' => 'Another legal entity',
            'country_code' => 'ET',
            'base_currency' => 'ETB',
            'status' => 'DRAFT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/v1/enterprise/organization-units', [
            'unit_code' => 'WRONG-SITE-ENTITY',
            'unit_name' => 'Wrong Site Entity',
            'unit_type' => 'DEPARTMENT',
            'legal_entity_id' => $otherEntityId,
            'business_site_id' => $this->siteA,
            'valid_from' => '2026-01-01',
        ])->assertUnprocessable();
    }

    public function test_positions_are_job_slots_and_reject_login_or_employee_identity_binding(): void
    {
        $this->actAs($this->ownerA, $this->tenantA);
        $unitId = $this->createUnit('POSITION-UNIT');

        $response = $this->postJson('/api/v1/enterprise/job-positions', [
            'org_unit_id' => $unitId,
            'position_code' => 'FACTORY-GM',
            'title' => 'Factory General Manager',
            'job_family' => 'OPERATIONS',
            'position_class' => 'MANAGEMENT',
            'valid_from' => '2026-01-01',
            'user_id' => $this->ownerA,
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('job_positions', [
            'tenant_id' => $this->tenantA,
            'position_code' => 'FACTORY-GM',
        ], 'pgsql_test_admin');

        $positionId = $this->createPosition($unitId, 'IE-MANAGER');

        $this->assertDatabaseHas('job_positions', [
            'tenant_id' => $this->tenantA,
            'id' => $positionId,
            'org_unit_id' => $unitId,
            'created_by' => $this->ownerA,
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('integration_outbox_events', [
            'tenant_id' => $this->tenantA,
            'event_type' => 'JobPositionCreated.v1',
            'aggregate_id' => $positionId,
        ], 'pgsql_test_admin');
    }

    public function test_unit_manager_assignment_is_effective_dated_and_position_scoped(): void
    {
        $this->actAs($this->ownerA, $this->tenantA);
        $unitId = $this->createUnit('MANAGED-UNIT');
        $managerPositionId = $this->createPosition($unitId, 'UNIT-HEAD');

        $response = $this->postJson('/api/v1/enterprise/organization-units/'.$unitId.'/manager-assignments', [
            'manager_position_id' => $managerPositionId,
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
            'reason' => 'Appoint the designated head position for this unit.',
        ])->assertCreated()
            ->assertJsonPath('data.manager_position_code', 'UNIT-HEAD')
            ->assertJsonPath('data.manager_position_title', 'Position UNIT-HEAD');

        $assignmentId = $response->json('data.id');

        $this->assertDatabaseHas('org_unit_manager_assignments', [
            'tenant_id' => $this->tenantA,
            'id' => $assignmentId,
            'org_unit_id' => $unitId,
            'manager_position_id' => $managerPositionId,
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('audit_events', [
            'tenant_id' => $this->tenantA,
            'action' => 'ORG_UNIT_MANAGER_POSITION_ASSIGNED',
            'object_id' => $assignmentId,
        ], 'pgsql_test_admin');

        $this->assertDatabaseHas('integration_outbox_events', [
            'tenant_id' => $this->tenantA,
            'event_type' => 'OrganizationUnitManagerAssigned.v1',
            'aggregate_id' => $assignmentId,
        ], 'pgsql_test_admin');

        $anotherManager = $this->createPosition($unitId, 'UNIT-HEAD-ALT');
        $this->postJson('/api/v1/enterprise/organization-units/'.$unitId.'/manager-assignments', [
            'manager_position_id' => $anotherManager,
            'valid_from' => '2026-06-01',
            'valid_to' => '2026-10-31',
            'reason' => 'Attempt to assign a second manager during the same period.',
        ])->assertUnprocessable();

        $this->getJson('/api/v1/enterprise/organization-units/'.$unitId.'/manager-assignments')
            ->assertOk()
            ->assertJsonFragment(['manager_position_id' => $managerPositionId]);
    }

    public function test_solid_line_reporting_rejects_overlapping_managers_and_cycles(): void
    {
        $this->actAs($this->ownerA, $this->tenantA);
        $unitId = $this->createUnit('REPORTING-UNIT');
        $positionA = $this->createPosition($unitId, 'POS-A');
        $positionB = $this->createPosition($unitId, 'POS-B');
        $positionC = $this->createPosition($unitId, 'POS-C');

        $this->postJson('/api/v1/enterprise/position-reporting-relations', [
            'subject_position_id' => $positionA,
            'manager_position_id' => $positionB,
            'reporting_type' => 'SOLID_LINE',
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
            'reason' => 'Record the first valid solid-line reporting assignment.',
        ])->assertCreated()
            ->assertJsonPath('data.subject_position_code', 'POS-A')
            ->assertJsonPath('data.manager_position_code', 'POS-B');

        $this->postJson('/api/v1/enterprise/position-reporting-relations', [
            'subject_position_id' => $positionA,
            'manager_position_id' => $positionC,
            'reporting_type' => 'SOLID_LINE',
            'valid_from' => '2026-06-01',
            'valid_to' => '2026-10-31',
            'reason' => 'Attempt a second overlapping solid-line manager.',
        ])->assertUnprocessable();

        $this->createSolidLine($positionB, $positionC);

        $this->postJson('/api/v1/enterprise/position-reporting-relations', [
            'subject_position_id' => $positionC,
            'manager_position_id' => $positionA,
            'reporting_type' => 'SOLID_LINE',
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
            'reason' => 'Attempt a cyclic solid-line reporting relationship.',
        ])->assertUnprocessable();

        $this->assertSame(2, DB::connection('pgsql_test_admin')->table('position_reporting_relations')->where('tenant_id', $this->tenantA)->count());
    }

    public function test_reporting_relationship_requires_valid_active_positions_and_reason(): void
    {
        $this->actAs($this->ownerA, $this->tenantA);
        $unitId = $this->createUnit('REPORTING-VALIDATION');
        $positionA = $this->createPosition($unitId, 'VALID-A');
        $positionB = $this->createPosition($unitId, 'VALID-B');

        $this->postJson('/api/v1/enterprise/position-reporting-relations', [
            'subject_position_id' => $positionA,
            'manager_position_id' => $positionA,
            'reporting_type' => 'FUNCTIONAL',
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
            'reason' => 'Self reporting is not permitted.',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/enterprise/position-reporting-relations', [
            'subject_position_id' => $positionA,
            'manager_position_id' => $positionB,
            'reporting_type' => 'FUNCTIONAL',
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
            'reason' => 'Too short',
        ])->assertUnprocessable();
    }

    public function test_tenant_scoped_lists_and_foreign_keys_do_not_cross_tenants(): void
    {
        $admin = DB::connection('pgsql_test_admin');
        $tenantBUnitId = (string) Str::uuid();
        $admin->table('org_units')->insert([
            'id' => $tenantBUnitId,
            'tenant_id' => $this->tenantB,
            'unit_code' => 'TENANT-B-UNIT',
            'unit_name' => 'Tenant B Private Unit',
            'unit_type' => 'DEPARTMENT',
            'status' => 'ACTIVE',
            'valid_from' => '2026-01-01',
            'created_by' => $this->ownerB,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actAs($this->ownerA, $this->tenantA);

        $list = $this->getJson('/api/v1/enterprise/organization-units')->assertOk();
        $this->assertStringNotContainsString('Tenant B Private Unit', $list->getContent());

        $this->postJson('/api/v1/enterprise/job-positions', [
            'org_unit_id' => $tenantBUnitId,
            'position_code' => 'CROSS-TENANT-POS',
            'title' => 'Cross tenant position',
            'valid_from' => '2026-01-01',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('job_positions', [
            'tenant_id' => $this->tenantA,
            'position_code' => 'CROSS-TENANT-POS',
        ], 'pgsql_test_admin');
    }

    public function test_organization_registers_support_tenant_scoped_search_and_pagination(): void
    {
        $this->actAs($this->ownerA, $this->tenantA);

        $unitA = $this->createUnit('PAGE-UNIT-A');
        $unitB = $this->createUnit('PAGE-UNIT-B');
        $unitC = $this->createUnit('PAGE-UNIT-C');

        $firstPage = $this->getJson('/api/v1/enterprise/organization-units?per_page=2&page=1')
            ->assertOk()
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 3);
        $firstIds = collect($firstPage->json('data'))->pluck('id')->all();

        $secondPage = $this->getJson('/api/v1/enterprise/organization-units?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 3);
        $secondIds = collect($secondPage->json('data'))->pluck('id')->all();
        $this->assertCount(1, $secondIds);
        $this->assertSame(3, count(array_unique(array_merge($firstIds, $secondIds))));
        $this->assertContains($unitA, array_merge($firstIds, $secondIds));
        $this->assertContains($unitB, array_merge($firstIds, $secondIds));
        $this->assertContains($unitC, array_merge($firstIds, $secondIds));

        $this->getJson('/api/v1/enterprise/organization-units?q=PAGE-UNIT-C&per_page=2')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['id' => $unitC]);

        $positionA = $this->createPosition($unitA, 'PAGE-POS-A');
        $positionB = $this->createPosition($unitA, 'PAGE-POS-B');
        $positionC = $this->createPosition($unitA, 'PAGE-POS-C');

        $this->getJson('/api/v1/enterprise/job-positions?org_unit_id='.$unitA.'&per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 3)
            ->assertJsonFragment(['id' => $positionC]);

        $this->getJson('/api/v1/enterprise/job-positions?org_unit_id='.$unitA.'&q=PAGE-POS-B')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['id' => $positionB]);

        $this->getJson('/api/v1/enterprise/job-positions?org_unit_id='.$unitB.'&per_page=101')
            ->assertUnprocessable();

        $this->postJson('/api/v1/enterprise/organization-units/'.$unitA.'/manager-assignments', [
            'manager_position_id' => $positionA,
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-06-30',
            'reason' => 'Assign the first effective-dated manager position.',
        ])->assertCreated();
        $this->postJson('/api/v1/enterprise/organization-units/'.$unitA.'/manager-assignments', [
            'manager_position_id' => $positionB,
            'valid_from' => '2026-07-01',
            'valid_to' => '2026-12-31',
            'reason' => 'Assign the successor manager position.',
        ])->assertCreated();

        $this->getJson('/api/v1/enterprise/organization-units/'.$unitA.'/manager-assignments?per_page=1&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 2);

        $subjectB = $this->createPosition($unitB, 'PAGE-SUBJECT-B');
        $subjectC = $this->createPosition($unitC, 'PAGE-SUBJECT-C');
        $this->createReportingRelationForTest($positionA, $positionB, 'FUNCTIONAL');
        $this->createReportingRelationForTest($subjectB, $positionC, 'FUNCTIONAL');
        $this->createReportingRelationForTest($subjectC, $positionA, 'FUNCTIONAL');

        $this->getJson('/api/v1/enterprise/position-reporting-relations?per_page=2&page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 2)
            ->assertJsonPath('total', 3);

        $this->getJson('/api/v1/enterprise/position-reporting-relations?q=PAGE-SUBJECT-C')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonFragment(['subject_position_id' => $subjectC]);

        $this->actAs($this->ownerB, $this->tenantB);
        $this->getJson('/api/v1/enterprise/organization-units?q=PAGE-UNIT-A')
            ->assertOk()
            ->assertJsonPath('total', 0);
        $this->getJson('/api/v1/enterprise/job-positions?q=PAGE-POS-A')
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    private function createReportingRelationForTest(string $subjectPositionId, string $managerPositionId, string $type): void
    {
        $this->postJson('/api/v1/enterprise/position-reporting-relations', [
            'subject_position_id' => $subjectPositionId,
            'manager_position_id' => $managerPositionId,
            'reporting_type' => $type,
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
            'reason' => 'Record an effective-dated functional reporting relationship.',
        ])->assertCreated();
    }

    public function test_runtime_table_privileges_and_forced_rls_are_restricted(): void
    {
        $runtime = DB::connection('pgsql');

        foreach (['org_units', 'job_positions', 'org_unit_manager_assignments', 'position_reporting_relations'] as $table) {
            $this->assertTrue((bool) $runtime->scalar(
                'SELECT has_table_privilege(?, ?, ?)',
                ['app_runtime', $table, 'SELECT'],
            ));
            $this->assertTrue((bool) $runtime->scalar(
                'SELECT has_table_privilege(?, ?, ?)',
                ['app_runtime', $table, 'INSERT'],
            ));
            $this->assertFalse((bool) $runtime->scalar(
                'SELECT has_table_privilege(?, ?, ?)',
                ['app_runtime', $table, 'UPDATE'],
            ));
            $this->assertFalse((bool) $runtime->scalar(
                'SELECT has_table_privilege(?, ?, ?)',
                ['app_runtime', $table, 'DELETE'],
            ));
            $this->assertTrue((bool) $runtime->scalar(
                'SELECT relrowsecurity AND relforcerowsecurity FROM pg_class WHERE oid = ?::regclass',
                [$table],
            ));
        }
    }
}
