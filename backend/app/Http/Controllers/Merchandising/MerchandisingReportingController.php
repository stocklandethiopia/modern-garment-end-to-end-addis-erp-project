<?php

namespace App\Http\Controllers\Merchandising;

use App\Http\Controllers\Controller;
use App\Services\Merchandising\MerchandisingDashboardService;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;

class MerchandisingReportingController extends Controller
{
    public function dashboard(
        MerchandisingDashboardService $dashboardService,
        TenantContext $tenantContext,
    ): JsonResponse {
        return response()->json($dashboardService->dashboard($tenantContext));
    }
}
