<?php

use App\Http\Controllers\Manufacturing\ManufacturingController;
use App\Http\Controllers\Quality\QualityController;
use App\Http\Controllers\Sourcing\SourcingController;
use App\Http\Middleware\ResolveTenantContext;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', ResolveTenantContext::class])->prefix('v1')->group(function (): void {
    Route::prefix('sourcing')->group(function (): void {
        Route::post('/suppliers', [SourcingController::class, 'storeSupplier']);
        Route::post('/suppliers/{supplierId}/approve', [SourcingController::class, 'approveSupplier']);
        Route::post('/purchase-orders', [SourcingController::class, 'storePurchaseOrder']);
        Route::post('/purchase-orders/{orderId}/submit', [SourcingController::class, 'submitPurchaseOrder']);
        Route::post('/purchase-orders/{orderId}/decision', [SourcingController::class, 'decidePurchaseOrder']);
        Route::post('/purchase-orders/{orderId}/send', [SourcingController::class, 'markPurchaseOrderSent']);
        Route::post('/goods-receipts', [SourcingController::class, 'recordGoodsReceipt']);
        Route::post('/goods-receipt-lines/{receiptLineId}/materialize', [SourcingController::class, 'materializeReceiptLine']);
    });

    Route::prefix('quality')->group(function (): void {
        Route::post('/inspections', [QualityController::class, 'storeInspection']);
        Route::post('/inspections/{inspectionId}/results', [QualityController::class, 'submitInspectionResult']);
        Route::post('/inspections/{inspectionId}/review', [QualityController::class, 'reviewInspection']);
        Route::post('/nonconformances/{nonconformanceId}/corrective-actions', [QualityController::class, 'createCorrectiveAction']);
        Route::post('/material-lots/{lotId}/release', [QualityController::class, 'releaseMaterial']);
    });

    Route::prefix('manufacturing')->group(function (): void {
        Route::post('/routes', [ManufacturingController::class, 'storeRoute']);
        Route::post('/routes/{routeId}/steps', [ManufacturingController::class, 'addRouteSteps']);
        Route::post('/routes/{routeId}/submit', [ManufacturingController::class, 'submitRoute']);
        Route::post('/routes/{routeId}/decision', [ManufacturingController::class, 'decideRoute']);
        Route::post('/production-orders', [ManufacturingController::class, 'storeProductionOrder']);
        Route::post('/production-orders/{orderId}/release', [ManufacturingController::class, 'releaseProductionOrder']);
        Route::post('/production-orders/{orderId}/start', [ManufacturingController::class, 'startProductionOrder']);
        Route::post('/production-orders/{orderId}/operations/{operationId}/events', [ManufacturingController::class, 'recordOperationEvent']);
    });
});
