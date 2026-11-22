<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LicensingEvidenceController extends Controller
{
    public function listBranchRegistrations(string $siteId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $site = DB::table('business_sites')
            ->where('tenant_id', $tenantId)
            ->where('id', $siteId)
            ->first();

        abort_if($site === null, 404, 'Business site not found.');

        $registrations = DB::table('branch_registrations')
            ->where('tenant_id', $tenantId)
            ->where('business_site_id', $siteId)
            ->orderByDesc('created_at')
            ->paginate(50);

        $registrations->setCollection($registrations->getCollection()->map(
            function (object $registration): object {
                $registration->activity_codes = json_decode(
                    (string) $registration->activity_codes,
                    true,
                    flags: JSON_THROW_ON_ERROR,
                );

                return $registration;
            },
        ));

        return response()->json($registrations);
    }

    public function createBranchRegistration(
        Request $request,
        string $siteId,
        TenantContext $tenantContext,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $tenantId = $tenantContext->tenantId();
        $site = DB::table('business_sites')
            ->where('tenant_id', $tenantId)
            ->where('id', $siteId)
            ->first();

        abort_if($site === null, 404, 'Business site not found.');

        if ($site->legal_entity_id === null) {
            return response()->json([
                'message' => 'A legal entity must own the site before branch-registration evidence can be recorded.',
                'errors' => ['legal_entity_id' => ['Assign the site to a legal entity first.']],
            ], 422);
        }

        $data = $request->validate([
            'commercial_register_reference' => [
                'required',
                'string',
                'max:120',
                Rule::unique('branch_registrations', 'commercial_register_reference')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'activity_codes' => ['required', 'array', 'min:1', 'max:20'],
            'activity_codes.*' => ['required', 'string', 'max:80'],
            'issuing_authority' => ['required', 'string', 'max:180'],
            'registered_at' => ['nullable', 'date'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]);

        $registration = DB::transaction(function () use ($data, $tenantId, $siteId, $site, $request): object {
            $id = (string) Str::uuid();
            $now = now();

            DB::table('branch_registrations')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $site->legal_entity_id,
                'business_site_id' => $siteId,
                'commercial_register_reference' => trim($data['commercial_register_reference']),
                'activity_codes' => json_encode(array_values(array_unique($data['activity_codes'])), JSON_THROW_ON_ERROR),
                'issuing_authority' => trim($data['issuing_authority']),
                'registered_at' => $data['registered_at'] ?? null,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_to' => $data['valid_to'] ?? null,
                'status' => 'REVIEW_REQUIRED',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->recordAudit(
                tenantId: $tenantId,
                actorId: (string) $request->user()->getAuthIdentifier(),
                action: 'BRANCH_REGISTRATION_EVIDENCE_SUBMITTED',
                objectType: 'BRANCH_REGISTRATION',
                objectId: $id,
                scope: ['site_id' => $siteId, 'legal_entity_id' => $site->legal_entity_id],
            );

            $registration = DB::table('branch_registrations')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            $registration->activity_codes = json_decode(
                (string) $registration->activity_codes,
                true,
                flags: JSON_THROW_ON_ERROR,
            );

            return $registration;
        });

        return response()->json(['data' => $registration], 201);
    }

    public function listLicences(string $entityId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $entity = DB::table('legal_entities')
            ->where('tenant_id', $tenantId)
            ->where('id', $entityId)
            ->first();

        abort_if($entity === null, 404, 'Legal entity not found.');

        $licences = DB::table('entity_licences')
            ->where('tenant_id', $tenantId)
            ->where('legal_entity_id', $entityId)
            ->orderBy('activity_code')
            ->paginate(50);

        return response()->json($licences);
    }

    public function createLicence(Request $request, string $entityId, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $tenantId = $tenantContext->tenantId();
        $entity = DB::table('legal_entities')
            ->where('tenant_id', $tenantId)
            ->where('id', $entityId)
            ->first();

        abort_if($entity === null, 404, 'Legal entity not found.');

        $data = $request->validate([
            'site_id' => [
                'nullable',
                'uuid',
                Rule::exists('business_sites', 'id')->where(
                    fn ($query) => $query
                        ->where('tenant_id', $tenantId)
                        ->where('legal_entity_id', $entityId),
                ),
            ],
            'activity_code' => ['required', 'string', 'max:80'],
            'licence_number' => [
                'required',
                'string',
                'max:120',
                Rule::unique('entity_licences', 'licence_number')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)->where('legal_entity_id', $entityId)),
            ],
            'issuing_authority' => ['required', 'string', 'max:180'],
            'issue_date' => ['nullable', 'date'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'evidence_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $licence = DB::transaction(function () use ($data, $tenantId, $entityId, $request): object {
            $id = (string) Str::uuid();
            $now = now();

            DB::table('entity_licences')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $entityId,
                'site_id' => $data['site_id'] ?? null,
                'activity_code' => trim($data['activity_code']),
                'licence_number' => trim($data['licence_number']),
                'issuing_authority' => trim($data['issuing_authority']),
                'issue_date' => $data['issue_date'] ?? null,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_to' => $data['valid_to'] ?? null,
                'evidence_reference' => $data['evidence_reference'] ?? null,
                'status' => 'REVIEW_REQUIRED',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->recordAudit(
                tenantId: $tenantId,
                actorId: (string) $request->user()->getAuthIdentifier(),
                action: 'ENTITY_LICENCE_EVIDENCE_SUBMITTED',
                objectType: 'ENTITY_LICENCE',
                objectId: $id,
                scope: [
                    'legal_entity_id' => $entityId,
                    'site_id' => $data['site_id'] ?? null,
                    'activity_code' => $data['activity_code'],
                ],
            );

            return DB::table('entity_licences')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();
        });

        return response()->json(['data' => $licence], 201);
    }

    private function recordAudit(
        string $tenantId,
        string $actorId,
        string $action,
        string $objectType,
        string $objectId,
        array $scope,
    ): void {
        $now = now();

        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'actor_user_id' => $actorId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'scope' => json_encode($scope, JSON_THROW_ON_ERROR),
            'reason' => 'Authorized tenant administrator submitted licensing evidence for professional review',
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
