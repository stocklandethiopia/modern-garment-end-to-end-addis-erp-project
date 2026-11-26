<?php

declare(strict_types=1);

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Services\PlatformAdministrationService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;

class TenantEntitlementController extends Controller
{
    public function show(
        TenantContext $tenantContext,
        PlatformAdministrationService $platform,
    ): JsonResponse {
        return response()->json([
            'data' => $platform->currentEntitlement($tenantContext->tenantId()),
        ]);
    }
}
