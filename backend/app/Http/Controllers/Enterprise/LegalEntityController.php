<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Models\LegalEntity;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LegalEntityController extends Controller
{
    public function index(TenantContext $tenantContext): JsonResponse
    {
        $entities = LegalEntity::query()
            ->where('tenant_id', $tenantContext->tenantId())
            ->orderBy('legal_name')
            ->paginate(50);

        return response()->json($entities);
    }

    public function store(Request $request, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'registration_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('legal_entities', 'registration_number')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'legal_name' => ['required', 'string', 'max:255'],
            'legal_form' => ['nullable', 'string', 'max:100'],
            'country_code' => ['required', 'string', 'size:2', 'alpha:upper'],
            'tax_identifier' => ['nullable', 'string', 'max:100'],
            'base_currency' => ['required', 'string', 'size:3', 'alpha:upper'],
        ]);

        $entity = DB::transaction(function () use ($data, $tenantId, $request): LegalEntity {
            $entity = LegalEntity::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                ...$data,
                'status' => 'DRAFT',
            ]);

            DB::table('audit_events')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'actor_user_id' => $request->user()->getAuthIdentifier(),
                'action' => 'LEGAL_ENTITY_DRAFT_CREATED',
                'object_type' => 'LEGAL_ENTITY',
                'object_id' => $entity->id,
                'scope' => json_encode(['registration_number' => $entity->registration_number], JSON_THROW_ON_ERROR),
                'reason' => 'Authenticated tenant administrator created a draft legal entity',
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $entity;
        });

        return response()->json(['data' => $entity], 201);
    }
}
