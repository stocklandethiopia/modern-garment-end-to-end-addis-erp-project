<?php

use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Enterprise\BusinessSiteController;
use App\Http\Controllers\Enterprise\InventoryLocationController;
use App\Http\Controllers\Enterprise\LegalEntityController;
use App\Http\Controllers\Enterprise\TenantController;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'service' => 'modern-garment-erp-api',
            'version' => config('app.version', '0.1.0'),
            'time' => now()->toIso8601String(),
        ]);
    });

    Route::post('/auth/tokens', [TokenController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::middleware('token.ability:tenant:select')->get('/tenants', [TenantController::class, 'index']);
        Route::delete('/auth/tokens/current', [TokenController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum', 'token.ability:erp:access', 'tenant.context'])->group(function (): void {
        Route::get('/me', function (Request $request, TenantContext $tenantContext) {
            return response()->json([
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
                'tenant' => [
                    'id' => $tenantContext->tenantId(),
                    'role' => $tenantContext->membershipRole(),
                ],
            ]);
        });

        Route::get('/enterprise/legal-entities', [LegalEntityController::class, 'index']);
        Route::post('/enterprise/legal-entities', [LegalEntityController::class, 'store']);
        Route::get('/enterprise/sites', [BusinessSiteController::class, 'index']);
        Route::post('/enterprise/sites', [BusinessSiteController::class, 'store']);
        Route::get('/enterprise/sites/{siteId}/nodes', [BusinessSiteController::class, 'nodes']);
        Route::post('/enterprise/sites/{siteId}/nodes', [BusinessSiteController::class, 'storeNode']);
        Route::get('/enterprise/nodes/{nodeId}/inventory-locations', [InventoryLocationController::class, 'index']);
        Route::post('/enterprise/nodes/{nodeId}/inventory-locations', [InventoryLocationController::class, 'store']);
    });
});
