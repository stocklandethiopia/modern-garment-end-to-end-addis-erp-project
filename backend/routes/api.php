<?php

use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Enterprise\LegalEntityController;
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

    Route::middleware(['auth:sanctum', 'tenant.context'])->group(function (): void {
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

        Route::delete('/auth/tokens/current', [TokenController::class, 'destroy']);

        Route::get('/enterprise/legal-entities', [LegalEntityController::class, 'index']);
        Route::post('/enterprise/legal-entities', [LegalEntityController::class, 'store']);
    });
});
