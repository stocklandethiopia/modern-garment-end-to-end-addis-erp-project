<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InventoryLocationController extends Controller
{
    private const LOCATION_TYPES = [
        'AREA',
        'ZONE',
        'AISLE',
        'RACK',
        'SHELF',
        'BIN',
        'FLOOR',
        'STAGING',
        'QUARANTINE',
        'RETURNS',
    ];

    public function index(string $nodeId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $node = DB::table('operational_nodes')
            ->where('tenant_id', $tenantId)
            ->where('id', $nodeId)
            ->first();

        abort_if($node === null, 404, 'Operational node not found.');

        abort_unless((bool) $node->stock_holding_enabled, 422, 'This operational node is not configured to hold stock.');

        $locations = DB::table('inventory_locations')
            ->where('tenant_id', $tenantId)
            ->where('operational_node_id', $nodeId)
            ->orderBy('code')
            ->paginate(100);

        return response()->json($locations);
    }

    public function store(Request $request, string $nodeId, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $tenantId = $tenantContext->tenantId();
        $node = DB::table('operational_nodes')
            ->where('tenant_id', $tenantId)
            ->where('id', $nodeId)
            ->first();

        abort_if($node === null, 404, 'Operational node not found.');

        if (! (bool) $node->stock_holding_enabled) {
            return response()->json([
                'message' => 'This operational node is not configured to hold inventory.',
                'errors' => ['operational_node' => ['Enable stock-holding capability on an eligible operational node before creating inventory locations.']],
            ], 422);
        }

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]{1,99}$/D',
                Rule::unique('inventory_locations', 'code')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)->where('operational_node_id', $nodeId),
                ),
            ],
            'location_type' => ['required', Rule::in(self::LOCATION_TYPES)],
            'parent_id' => [
                'nullable',
                'uuid',
                Rule::exists('inventory_locations', 'id')->where(
                    fn ($query) => $query->where('tenant_id', $tenantId)->where('operational_node_id', $nodeId),
                ),
            ],
            'allow_stock' => ['sometimes', 'boolean'],
        ]);

        $location = DB::transaction(function () use ($data, $tenantId, $nodeId, $request): object {
            $id = (string) Str::uuid();
            $now = now();

            DB::table('inventory_locations')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'operational_node_id' => $nodeId,
                'parent_id' => $data['parent_id'] ?? null,
                'code' => $data['code'],
                'location_type' => $data['location_type'],
                'allow_stock' => (bool) ($data['allow_stock'] ?? true),
                'status' => 'DRAFT',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('audit_events')->insert([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'actor_user_id' => $request->user()->getAuthIdentifier(),
                'action' => 'INVENTORY_LOCATION_DRAFT_CREATED',
                'object_type' => 'INVENTORY_LOCATION',
                'object_id' => $id,
                'scope' => json_encode([
                    'operational_node_id' => $nodeId,
                    'location_code' => $data['code'],
                    'location_type' => $data['location_type'],
                ], JSON_THROW_ON_ERROR),
                'reason' => 'Authorized tenant administrator created an inventory location draft',
                'correlation_id' => (string) Str::uuid(),
                'occurred_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return DB::table('inventory_locations')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();
        });

        return response()->json(['data' => $location], 201);
    }
}
