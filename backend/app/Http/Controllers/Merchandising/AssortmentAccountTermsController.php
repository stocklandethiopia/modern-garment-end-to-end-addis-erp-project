<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Services\Merchandising\AssortmentAccountTermsService;
use App\Services\ScopedAccessService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AssortmentAccountTermsController extends Controller
{
    public function index(
        string $assortmentPlanId,
        TenantContext $tenantContext,
        ScopedAccessService $scopedAccess,
        AssortmentAccountTermsService $service,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) request()->user()->getAuthIdentifier();
        $this->assertPlanScope($tenantId, $assortmentPlanId, $actorId, $scopedAccess, 'READ');

        return response()->json([
            'data' => $service->index($tenantId, $assortmentPlanId),
        ]);
    }

    public function store(
        string $assortmentPlanId,
        Request $request,
        TenantContext $tenantContext,
        ScopedAccessService $scopedAccess,
        AssortmentAccountTermsService $service,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $this->assertPlanScope($tenantId, $assortmentPlanId, $actorId, $scopedAccess, 'CREATE');

        $data = $request->validate([
            'price_list_id' => [
                'required',
                'uuid',
                Rule::exists('price_lists', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
            'account_tier' => ['nullable', 'string', 'max:80'],
            'commercial_terms_reference' => ['required', 'string', 'min:2', 'max:160'],
            'payment_terms_reference' => ['required', 'string', 'min:2', 'max:120'],
            'delivery_terms_reference' => ['required', 'string', 'min:2', 'max:120'],
            'returns_policy_reference' => ['nullable', 'string', 'max:120'],
            'minimum_order_units' => ['sometimes', 'integer', 'min:0', 'max:1000000000'],
            'order_multiple_units' => ['sometimes', 'integer', 'min:1', 'max:1000000000'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'change_reason' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $terms = $service->create(
            $tenantId,
            $assortmentPlanId,
            $actorId,
            [
                ...$data,
                'minimum_order_units' => $data['minimum_order_units'] ?? 0,
                'order_multiple_units' => $data['order_multiple_units'] ?? 1,
            ],
        );

        return response()->json(['data' => $terms], 201);
    }

    public function submit(
        string $assortmentPlanId,
        string $termsId,
        Request $request,
        TenantContext $tenantContext,
        ScopedAccessService $scopedAccess,
        AssortmentAccountTermsService $service,
    ): JsonResponse {
        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $this->assertPlanScope($tenantId, $assortmentPlanId, $actorId, $scopedAccess, 'CREATE');

        return response()->json([
            'data' => $service->submit(
                $tenantId,
                $assortmentPlanId,
                $termsId,
                $actorId,
                $tenantContext->canAdministerTenant(),
            ),
        ]);
    }

    public function decide(
        string $assortmentPlanId,
        string $termsId,
        Request $request,
        TenantContext $tenantContext,
        ScopedAccessService $scopedAccess,
        AssortmentAccountTermsService $service,
    ): JsonResponse {
        abort_unless($tenantContext->canAdministerTenant(), 403, 'Tenant owner or administrator approval is required.');

        $tenantId = $tenantContext->tenantId();
        $actorId = (string) $request->user()->getAuthIdentifier();
        $this->assertPlanScope($tenantId, $assortmentPlanId, $actorId, $scopedAccess, 'APPROVE');
        $data = $request->validate([
            'decision' => ['required', Rule::in(['APPROVE', 'REJECT'])],
            'reason' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        return response()->json([
            'data' => $service->decide(
                $tenantId,
                $assortmentPlanId,
                $termsId,
                $actorId,
                $data['decision'],
                trim($data['reason']),
            ),
        ]);
    }

    private function assertPlanScope(
        string $tenantId,
        string $assortmentPlanId,
        string $actorId,
        ScopedAccessService $scopedAccess,
        string $permission,
    ): void {
        $plan = DB::table('assortment_plans')
            ->where('tenant_id', $tenantId)
            ->where('id', $assortmentPlanId)
            ->first();

        abort_if($plan === null, 404);

        $range = DB::table('range_plans')
            ->where('tenant_id', $tenantId)
            ->where('id', $plan->range_plan_id)
            ->first();

        abort_if($range === null, 404);

        $collection = DB::table('collections')
            ->where('tenant_id', $tenantId)
            ->where('id', $range->collection_id)
            ->first();

        abort_if($collection === null, 404);

        $scopedAccess->assertBrand($tenantId, $actorId, $collection->brand_id, $permission);
        $scopedAccess->assertMarket($tenantId, $actorId, $plan->market_scope, $permission);
        if ($plan->business_site_id !== null) {
            $scopedAccess->assertSite($tenantId, $actorId, $plan->business_site_id, $permission);
        }
    }
}
