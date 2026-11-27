<?php

declare(strict_types=1);

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\PlatformAdministrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformAdministrationController extends Controller
{
    public function context(PlatformAdministrationService $platform): JsonResponse
    {
        return response()->json(['data' => ['roles' => $platform->currentOperatorRoles()]]);
    }

    public function index(PlatformAdministrationService $platform): JsonResponse
    {
        return response()->json(['data' => $platform->tenants()]);
    }

    public function changeLifecycle(
        Request $request,
        string $tenantId,
        PlatformAdministrationService $platform,
    ): JsonResponse {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:ACTIVE,SUSPENDED,CLOSED'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        return response()->json([
            'data' => $platform->changeTenantStatus($tenantId, $data['status'], trim($data['reason'])),
        ]);
    }

    public function updateEntitlements(
        Request $request,
        string $tenantId,
        PlatformAdministrationService $platform,
    ): JsonResponse {
        $data = $request->validate([
            'plan_code' => ['required', 'string', 'in:STARTER,BUSINESS,ENTERPRISE,CUSTOM'],
            'status' => ['required', 'string', 'in:ACTIVE,REVIEW_REQUIRED,SUSPENDED,EXPIRED'],
            'feature_flags' => ['required', 'array'],
            'feature_flags.*' => ['boolean'],
            'limits' => ['required', 'array'],
            'limits.*' => ['integer', 'min:0'],
            'valid_from' => ['nullable', 'date_format:Y-m-d'],
            'valid_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:valid_from'],
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        return response()->json([
            'data' => $platform->setTenantEntitlements(
                $tenantId,
                $data['plan_code'],
                $data['status'],
                $data['feature_flags'],
                $data['limits'],
                $data['valid_from'] ?? null,
                $data['valid_to'] ?? null,
                trim($data['reason']),
            ),
        ]);
    }
}
