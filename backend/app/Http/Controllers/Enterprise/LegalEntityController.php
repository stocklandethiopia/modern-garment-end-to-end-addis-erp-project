<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Models\LegalEntity;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $data = $request->validate([
            'registration_number' => ['required', 'string', 'max:100'],
            'legal_name' => ['required', 'string', 'max:255'],
            'legal_form' => ['nullable', 'string', 'max:100'],
            'country_code' => ['required', 'string', 'size:2', 'alpha:upper'],
            'tax_identifier' => ['nullable', 'string', 'max:100'],
            'base_currency' => ['required', 'string', 'size:3', 'alpha:upper'],
        ]);

        $entity = LegalEntity::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantContext->tenantId(),
            ...$data,
            'status' => 'DRAFT',
        ]);

        return response()->json(['data' => $entity], 201);
    }
}
