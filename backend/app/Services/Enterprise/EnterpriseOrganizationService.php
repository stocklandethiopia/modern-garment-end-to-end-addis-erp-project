<?php

declare(strict_types=1);

namespace App\Services\Enterprise;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class EnterpriseOrganizationService
{
    public function units(string $tenantId): LengthAwarePaginator
    {
        return DB::table('org_units')
            ->where('tenant_id', $tenantId)
            ->orderBy('unit_code')
            ->paginate(100);
    }

    public function positions(string $tenantId): LengthAwarePaginator
    {
        return DB::table('job_positions')
            ->join('org_units', function ($join): void {
                $join->on('org_units.id', '=', 'job_positions.org_unit_id')
                    ->on('org_units.tenant_id', '=', 'job_positions.tenant_id');
            })
            ->where('job_positions.tenant_id', $tenantId)
            ->orderBy('job_positions.position_code')
            ->paginate(100, [
                'job_positions.*',
                'org_units.unit_code',
                'org_units.unit_name',
            ]);
    }

    public function reportingRelations(string $tenantId): LengthAwarePaginator
    {
        return DB::table('position_reporting_relations')
            ->join('job_positions as subject', function ($join): void {
                $join->on('subject.id', '=', 'position_reporting_relations.subject_position_id')
                    ->on('subject.tenant_id', '=', 'position_reporting_relations.tenant_id');
            })
            ->join('job_positions as manager', function ($join): void {
                $join->on('manager.id', '=', 'position_reporting_relations.manager_position_id')
                    ->on('manager.tenant_id', '=', 'position_reporting_relations.tenant_id');
            })
            ->where('position_reporting_relations.tenant_id', $tenantId)
            ->orderBy('position_reporting_relations.valid_from', 'desc')
            ->paginate(100, [
                'position_reporting_relations.*',
                'subject.position_code as subject_position_code',
                'subject.title as subject_position_title',
                'manager.position_code as manager_position_code',
                'manager.title as manager_position_title',
            ]);
    }

    public function createUnit(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $this->lockOrganizationGraph($tenantId);

            $this->assertEntityAndSite($tenantId, $data['legal_entity_id'] ?? null, $data['business_site_id'] ?? null);

            if (($data['parent_id'] ?? null) !== null) {
                $parent = DB::table('org_units')
                    ->where('tenant_id', $tenantId)
                    ->where('id', $data['parent_id'])
                    ->lockForUpdate()
                    ->first();

                if ($parent === null || $parent->status !== 'ACTIVE') {
                    throw ValidationException::withMessages([
                        'parent_id' => 'The parent organizational unit must exist and be active in this tenant.',
                    ]);
                }

                $this->assertContainedDateRange(
                    $data['valid_from'],
                    $data['valid_to'] ?? null,
                    $parent->valid_from,
                    $parent->valid_to,
                    'parent_id',
                );
            }

            $id = (string) Str::uuid();
            $now = now();

            DB::table('org_units')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'parent_id' => $data['parent_id'] ?? null,
                'legal_entity_id' => $data['legal_entity_id'] ?? null,
                'business_site_id' => $data['business_site_id'] ?? null,
                'unit_code' => $data['unit_code'],
                'unit_name' => $data['unit_name'],
                'unit_type' => $data['unit_type'],
                'status' => 'ACTIVE',
                'valid_from' => $data['valid_from'],
                'valid_to' => $data['valid_to'] ?? null,
                'created_by' => $actorId,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->recordChange(
                $tenantId,
                $actorId,
                'ORGANIZATION_UNIT_CREATED',
                'ORG_UNIT',
                $id,
                [
                    'unit_code' => $data['unit_code'],
                    'unit_type' => $data['unit_type'],
                    'parent_id' => $data['parent_id'] ?? null,
                    'legal_entity_id' => $data['legal_entity_id'] ?? null,
                    'business_site_id' => $data['business_site_id'] ?? null,
                    'valid_from' => $data['valid_from'],
                    'valid_to' => $data['valid_to'] ?? null,
                ],
                'OrganizationUnitCreated.v1',
            );

            return DB::table('org_units')->where('tenant_id', $tenantId)->where('id', $id)->first()
                ?? throw new RuntimeException('The organization unit was created but could not be reloaded.');
        }, 3);
    }

    public function createPosition(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $unit = DB::table('org_units')
                ->where('tenant_id', $tenantId)
                ->where('id', $data['org_unit_id'])
                ->lockForUpdate()
                ->first();

            if ($unit === null || $unit->status !== 'ACTIVE') {
                throw ValidationException::withMessages([
                    'org_unit_id' => 'The organizational unit must exist and be active in this tenant.',
                ]);
            }

            $this->assertContainedDateRange(
                $data['valid_from'],
                $data['valid_to'] ?? null,
                $unit->valid_from,
                $unit->valid_to,
                'org_unit_id',
            );

            $id = (string) Str::uuid();
            $now = now();

            DB::table('job_positions')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'org_unit_id' => $unit->id,
                'position_code' => $data['position_code'],
                'title' => $data['title'],
                'job_family' => $data['job_family'] ?? null,
                'grade' => $data['grade'] ?? null,
                'position_class' => $data['position_class'] ?? 'PROFESSIONAL',
                'status' => 'ACTIVE',
                'valid_from' => $data['valid_from'],
                'valid_to' => $data['valid_to'] ?? null,
                'created_by' => $actorId,
                'updated_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->recordChange(
                $tenantId,
                $actorId,
                'JOB_POSITION_CREATED',
                'JOB_POSITION',
                $id,
                [
                    'position_code' => $data['position_code'],
                    'title' => $data['title'],
                    'org_unit_id' => $unit->id,
                    'position_class' => $data['position_class'] ?? 'PROFESSIONAL',
                    'valid_from' => $data['valid_from'],
                    'valid_to' => $data['valid_to'] ?? null,
                ],
                'JobPositionCreated.v1',
            );

            return DB::table('job_positions')->where('tenant_id', $tenantId)->where('id', $id)->first()
                ?? throw new RuntimeException('The job position was created but could not be reloaded.');
        }, 3);
    }

    public function createReportingRelation(string $tenantId, string $actorId, array $data): object
    {
        return DB::transaction(function () use ($tenantId, $actorId, $data): object {
            $this->lockOrganizationGraph($tenantId);

            if ($data['subject_position_id'] === $data['manager_position_id']) {
                throw ValidationException::withMessages([
                    'manager_position_id' => 'A position cannot report to itself.',
                ]);
            }

            $positions = DB::table('job_positions')
                ->where('tenant_id', $tenantId)
                ->whereIn('id', [$data['subject_position_id'], $data['manager_position_id']])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subject = $positions->get($data['subject_position_id']);
            $manager = $positions->get($data['manager_position_id']);

            if ($subject === null || $manager === null || $subject->status !== 'ACTIVE' || $manager->status !== 'ACTIVE') {
                throw ValidationException::withMessages([
                    'subject_position_id' => 'Both positions must exist and be active in this tenant.',
                    'manager_position_id' => 'Both positions must exist and be active in this tenant.',
                ]);
            }

            $this->assertContainedDateRange(
                $data['valid_from'],
                $data['valid_to'] ?? null,
                $subject->valid_from,
                $subject->valid_to,
                'valid_from',
            );
            $this->assertContainedDateRange(
                $data['valid_from'],
                $data['valid_to'] ?? null,
                $manager->valid_from,
                $manager->valid_to,
                'valid_from',
            );

            if ($data['reporting_type'] === 'SOLID_LINE') {
                $overlap = DB::table('position_reporting_relations')
                    ->where('tenant_id', $tenantId)
                    ->where('subject_position_id', $subject->id)
                    ->where('reporting_type', 'SOLID_LINE')
                    ->where('valid_from', '<=', $data['valid_to'] ?? '9999-12-31')
                    ->where(function ($query) use ($data): void {
                        $query->whereNull('valid_to')
                            ->orWhere('valid_to', '>=', $data['valid_from']);
                    })
                    ->exists();

                if ($overlap) {
                    throw ValidationException::withMessages([
                        'subject_position_id' => 'A solid-line manager is already assigned for an overlapping effective period.',
                    ]);
                }

                if ($this->solidLineWouldCreateCycle(
                    $tenantId,
                    $subject->id,
                    $manager->id,
                    $data['valid_from'],
                    $data['valid_to'] ?? null,
                )) {
                    throw ValidationException::withMessages([
                        'manager_position_id' => 'This reporting relationship would create a solid-line reporting cycle.',
                    ]);
                }
            }

            $id = (string) Str::uuid();
            $now = now();

            DB::table('position_reporting_relations')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'subject_position_id' => $subject->id,
                'manager_position_id' => $manager->id,
                'reporting_type' => $data['reporting_type'],
                'valid_from' => $data['valid_from'],
                'valid_to' => $data['valid_to'] ?? null,
                'reason' => $data['reason'],
                'created_by' => $actorId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->recordChange(
                $tenantId,
                $actorId,
                'POSITION_REPORTING_RELATION_CREATED',
                'POSITION_REPORTING_RELATION',
                $id,
                [
                    'subject_position_id' => $subject->id,
                    'manager_position_id' => $manager->id,
                    'reporting_type' => $data['reporting_type'],
                    'valid_from' => $data['valid_from'],
                    'valid_to' => $data['valid_to'] ?? null,
                ],
                'PositionReportingRelationCreated.v1',
            );

            return DB::table('position_reporting_relations')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first()
                ?? throw new RuntimeException('The reporting relationship was created but could not be reloaded.');
        }, 3);
    }

    private function assertEntityAndSite(string $tenantId, ?string $legalEntityId, ?string $businessSiteId): void
    {
        if ($legalEntityId !== null && ! DB::table('legal_entities')
            ->where('tenant_id', $tenantId)
            ->where('id', $legalEntityId)
            ->exists()) {
            throw ValidationException::withMessages([
                'legal_entity_id' => 'The legal entity is not available in this tenant.',
            ]);
        }

        if ($businessSiteId !== null) {
            if ($legalEntityId === null || ! DB::table('business_sites')
                ->where('tenant_id', $tenantId)
                ->where('id', $businessSiteId)
                ->where('legal_entity_id', $legalEntityId)
                ->exists()) {
                throw ValidationException::withMessages([
                    'business_site_id' => 'The business site must belong to the selected legal entity in this tenant.',
                ]);
            }
        }
    }

    private function assertContainedDateRange(
        string $childFrom,
        ?string $childTo,
        string $parentFrom,
        ?string $parentTo,
        string $field,
    ): void {
        if ($childFrom < $parentFrom
            || ($parentTo !== null && ($childTo === null || $childTo > $parentTo))) {
            throw ValidationException::withMessages([
                $field => 'The effective period must remain within the referenced organizational record period.',
            ]);
        }
    }

    private function lockOrganizationGraph(string $tenantId): void
    {
        DB::selectOne(
            "SELECT pg_advisory_xact_lock(hashtextextended(?::text, 0))",
            ['organization-graph:'.$tenantId],
        );
    }

    private function solidLineWouldCreateCycle(
        string $tenantId,
        string $subjectPositionId,
        string $managerPositionId,
        string $validFrom,
        ?string $validTo,
    ): bool {
        $result = DB::selectOne(<<<'SQL'
WITH RECURSIVE manager_chain(position_id, overlap_from, overlap_to, path) AS (
    SELECT
        CAST(? AS uuid),
        CAST(? AS date),
        COALESCE(CAST(? AS date), DATE '9999-12-31'),
        ARRAY[CAST(? AS uuid)]
    UNION ALL
    SELECT
        relation.manager_position_id,
        GREATEST(chain.overlap_from, relation.valid_from),
        LEAST(chain.overlap_to, COALESCE(relation.valid_to, DATE '9999-12-31')),
        chain.path || relation.manager_position_id
    FROM manager_chain chain
    JOIN public.position_reporting_relations relation
      ON relation.tenant_id = CAST(? AS uuid)
     AND relation.subject_position_id = chain.position_id
     AND relation.reporting_type = 'SOLID_LINE'
     AND relation.valid_from <= chain.overlap_to
     AND COALESCE(relation.valid_to, DATE '9999-12-31') >= chain.overlap_from
    WHERE GREATEST(chain.overlap_from, relation.valid_from)
          <= LEAST(chain.overlap_to, COALESCE(relation.valid_to, DATE '9999-12-31'))
      AND NOT relation.manager_position_id = ANY(chain.path)
)
SELECT EXISTS (
    SELECT 1 FROM manager_chain WHERE position_id = CAST(? AS uuid)
) AS cycle
SQL, [
            $managerPositionId,
            $validFrom,
            $validTo,
            $managerPositionId,
            $tenantId,
            $subjectPositionId,
        ]);

        return in_array(strtolower((string) ($result->cycle ?? '')), ['1', 't', 'true'], true);
    }

    private function recordChange(
        string $tenantId,
        string $actorId,
        string $action,
        string $aggregateType,
        string $aggregateId,
        array $attributes,
        string $eventType,
    ): void {
        $now = now();
        $correlationId = (string) Str::uuid();

        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $aggregateType,
            'object_id' => $aggregateId,
            'scope' => json_encode($attributes, JSON_THROW_ON_ERROR),
            'reason' => 'Authorized tenant administrator changed enterprise organization structure',
            'correlation_id' => $correlationId,
            'occurred_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $payload = json_encode([
            'schema_version' => 1,
            'event_type' => $eventType,
            'tenant_id' => $tenantId,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'actor_id' => $actorId,
            'correlation_id' => $correlationId,
            'occurred_at' => $now->toIso8601String(),
            'attributes' => $attributes,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        DB::table('integration_outbox_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'endpoint_id' => null,
            'event_key' => $eventType.':'.$aggregateId,
            'event_type' => $eventType,
            'aggregate_type' => $aggregateType,
            'aggregate_id' => $aggregateId,
            'payload' => $payload,
            'payload_sha256' => hash('sha256', $payload),
            'status' => 'PENDING',
            'attempt_count' => 0,
            'available_at' => $now,
            'locked_at' => null,
            'delivered_at' => null,
            'last_error_code' => null,
            'last_error_summary' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
