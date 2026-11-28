<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessSiteController extends Controller
{
    private const SITE_TYPES = [
        'FACTORY_CAMPUS',
        'OFFICE',
        'STORE_PREMISES',
        'WAREHOUSE_CAMPUS',
        'DEPOT_PREMISES',
        'OTHER',
    ];

    private const NODE_TYPES = [
        'FACTORY',
        'WAREHOUSE',
        'DISTRIBUTION_CENTER',
        'BRANCH',
        'RETAIL_STORE',
        'WHOLESALE_DEPOT',
        'FULFILLMENT_CENTER',
        'OFFICE',
        'SUBCONTRACTOR_SITE',
    ];

    private const STOCK_CAPABLE_NODE_TYPES = [
        'FACTORY',
        'WAREHOUSE',
        'BRANCH',
        'DISTRIBUTION_CENTER',
        'RETAIL_STORE',
        'WHOLESALE_DEPOT',
        'FULFILLMENT_CENTER',
    ];

    private const CAPABILITIES = [
        'PRODUCTION',
        'STOCK_HOLDING',
        'RETAIL',
        'WHOLESALE',
        'FULFILLMENT',
        'OFFICE',
        'SUBCONTRACTING',
        'QUALITY_LAB',
        'SAMPLE_ROOM',
    ];

    public function index(TenantContext $tenantContext): JsonResponse
    {
        $sites = DB::table('business_sites')
            ->where('tenant_id', $tenantContext->tenantId())
            ->orderBy('name')
            ->paginate(50);

        return response()->json($sites);
    }

    public function store(Request $request, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $tenantId = $tenantContext->tenantId();
        $data = $request->validate([
            'legal_entity_id' => [
                'nullable',
                'uuid',
                Rule::exists('legal_entities', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/D',
                Rule::unique('business_sites', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'site_type' => ['required', Rule::in(self::SITE_TYPES)],
            'name' => ['required', 'string', 'max:180'],
            'country_code' => ['required', 'string', 'size:2', 'alpha:ascii', 'uppercase'],
            'region' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'max:64', 'timezone'],
        ]);

        $site = DB::transaction(function () use ($data, $tenantId, $request): object {
            $id = (string) Str::uuid();
            $now = now();

            DB::table('business_sites')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'legal_entity_id' => $data['legal_entity_id'] ?? null,
                'code' => $data['code'],
                'site_type' => $data['site_type'],
                'name' => $data['name'],
                'country_code' => $data['country_code'],
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'address_line' => $data['address_line'] ?? null,
                'timezone' => $data['timezone'],
                'status' => 'DRAFT',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->recordAudit(
                tenantId: $tenantId,
                actorId: (string) $request->user()->getAuthIdentifier(),
                action: 'BUSINESS_SITE_DRAFT_CREATED',
                objectType: 'BUSINESS_SITE',
                objectId: $id,
                scope: ['site_code' => $data['code'], 'site_type' => $data['site_type']],
            );

            return DB::table('business_sites')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();
        });

        return response()->json(['data' => $site], 201);
    }

    public function nodes(string $siteId, TenantContext $tenantContext): JsonResponse
    {
        $tenantId = $tenantContext->tenantId();
        $site = DB::table('business_sites')
            ->where('tenant_id', $tenantId)
            ->where('id', $siteId)
            ->first();

        abort_if($site === null, 404, 'Business site not found.');

        $nodes = DB::table('operational_nodes')
            ->where('tenant_id', $tenantId)
            ->where('business_site_id', $siteId)
            ->orderBy('name')
            ->paginate(50);

        $nodes->setCollection($nodes->getCollection()->map(function (object $node): object {
            $node->capabilities = json_decode((string) $node->capabilities, true, flags: JSON_THROW_ON_ERROR);

            return $node;
        }));

        return response()->json($nodes);
    }

    public function storeNode(Request $request, string $siteId, TenantContext $tenantContext): JsonResponse
    {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant administrator permission is required.');

        $tenantId = $tenantContext->tenantId();
        $site = DB::table('business_sites')
            ->where('tenant_id', $tenantId)
            ->where('id', $siteId)
            ->first();

        abort_if($site === null, 404, 'Business site not found.');

        if ($site->legal_entity_id === null) {
            return response()->json([
                'message' => 'Assign a legal entity to the business site before creating operating nodes.',
                'errors' => ['legal_entity_id' => ['Operating nodes require an owning legal entity.']],
            ], 422);
        }

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/D',
                Rule::unique('operational_nodes', 'code')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'node_type' => ['required', Rule::in(self::NODE_TYPES)],
            'name' => ['required', 'string', 'max:180'],
            'capabilities' => ['sometimes', 'array', 'max:12'],
            'capabilities.*' => ['string', Rule::in(self::CAPABILITIES)],
            'stock_holding_enabled' => ['sometimes', 'boolean'],
        ]);

        $stockHolding = (bool) ($data['stock_holding_enabled'] ?? false);
        if ($stockHolding && ! in_array($data['node_type'], self::STOCK_CAPABLE_NODE_TYPES, true)) {
            return response()->json([
                'message' => 'This node type cannot hold inventory. Create a separate stock-capable operational node where appropriate.',
                'errors' => ['stock_holding_enabled' => ['Office and subcontractor nodes cannot hold stock. A branch requires an explicit stock-holding decision.']],
            ], 422);
        }

        $capabilities = array_values(array_unique($data['capabilities'] ?? []));
        if ($stockHolding && ! in_array('STOCK_HOLDING', $capabilities, true)) {
            $capabilities[] = 'STOCK_HOLDING';
        }
        if (! $stockHolding) {
            $capabilities = array_values(array_filter(
                $capabilities,
                fn (string $capability): bool => $capability !== 'STOCK_HOLDING',
            ));
        }

        $node = DB::transaction(function () use (
            $data,
            $tenantId,
            $site,
            $siteId,
            $stockHolding,
            $capabilities,
            $request,
        ): object {
            $id = (string) Str::uuid();
            $now = now();

            DB::table('operational_nodes')->insert([
                'id' => $id,
                'tenant_id' => $tenantId,
                'business_site_id' => $siteId,
                'legal_entity_id' => $site->legal_entity_id,
                'code' => $data['code'],
                'node_type' => $data['node_type'],
                'name' => $data['name'],
                'capabilities' => json_encode($capabilities, JSON_THROW_ON_ERROR),
                'stock_holding_enabled' => $stockHolding,
                'status' => 'DRAFT',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $this->recordAudit(
                tenantId: $tenantId,
                actorId: (string) $request->user()->getAuthIdentifier(),
                action: 'OPERATIONAL_NODE_DRAFT_CREATED',
                objectType: 'OPERATIONAL_NODE',
                objectId: $id,
                scope: [
                    'site_id' => $siteId,
                    'node_code' => $data['code'],
                    'node_type' => $data['node_type'],
                    'stock_holding_enabled' => $stockHolding,
                ],
            );

            $node = DB::table('operational_nodes')
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            $node->capabilities = json_decode((string) $node->capabilities, true, flags: JSON_THROW_ON_ERROR);

            return $node;
        });

        return response()->json(['data' => $node], 201);
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
            'reason' => 'Authenticated tenant administrator created an enterprise foundation record',
            'correlation_id' => (string) Str::uuid(),
            'occurred_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
