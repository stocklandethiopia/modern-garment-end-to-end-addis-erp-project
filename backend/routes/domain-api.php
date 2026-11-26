<?php

use App\Http\Controllers\Agents\AgentGovernanceController;

use App\Http\Controllers\Integration\DocumentIntegrationController;

use App\Http\Controllers\Finance\FinanceControlController;

use App\Http\Controllers\Distribution\FinishedGoodsController;

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
        Route::post('/production-operations/{operationId}/release', [QualityController::class, 'releaseProductionOperation']);
        Route::post('/material-lots/{lotId}/release', [QualityController::class, 'releaseMaterial']);
    });

    Route::prefix('distribution')->group(function (): void {
        Route::post('/finished-goods/production-receipts', [FinishedGoodsController::class, 'receiveProductionOutput']);
        Route::post('/finished-goods/{lotId}/release', [FinishedGoodsController::class, 'releaseLot']);
        Route::post('/customer-orders', [FinishedGoodsController::class, 'storeCustomerOrder']);
        Route::post('/customer-orders/{orderId}/confirm', [FinishedGoodsController::class, 'confirmCustomerOrder']);
        Route::post('/order-lines/{lineId}/reservations', [FinishedGoodsController::class, 'reserveOrderLine']);
        Route::post('/reservations/{reservationId}/dispatch', [FinishedGoodsController::class, 'dispatchReservation']);
    });

    Route::prefix('finance')->group(function (): void {
        Route::post('/journals', [FinanceControlController::class, 'storeJournal']);
        Route::post('/journals/{journalId}/submit', [FinanceControlController::class, 'submitJournal']);
        Route::post('/journals/{journalId}/decision', [FinanceControlController::class, 'decideJournal']);
        Route::post('/journals/{journalId}/post', [FinanceControlController::class, 'postJournal']);
        Route::post('/payroll-drafts', [FinanceControlController::class, 'storePayrollDraft']);
        Route::post('/payroll-drafts/{runId}/lines', [FinanceControlController::class, 'addPayrollDraftLine']);
        Route::post('/payroll-drafts/{runId}/submit', [FinanceControlController::class, 'submitPayroll']);
        Route::post('/payroll-drafts/{runId}/professional-decision', [FinanceControlController::class, 'decidePayroll']);
    });

    Route::prefix('documents')->group(function (): void {
        Route::post('/', [DocumentIntegrationController::class, 'registerDocument']);
        Route::post('/{documentId}/extraction-runs', [DocumentIntegrationController::class, 'beginExtraction']);
        Route::post('/extraction-runs/{runId}/result', [DocumentIntegrationController::class, 'stageExtractionResult']);
        Route::post('/extraction-runs/{runId}/review', [DocumentIntegrationController::class, 'reviewExtraction']);
    });

    Route::prefix('devices')->group(function (): void {
        Route::post('/scan-events', [DocumentIntegrationController::class, 'recordScan']);
    });

    Route::prefix('agents')->group(function (): void {
        Route::post('/runs', [AgentGovernanceController::class, 'startRun']);
        Route::post('/runs/{runId}/finish', [AgentGovernanceController::class, 'finishRun']);
        Route::post('/action-proposals', [AgentGovernanceController::class, 'proposeAction']);
        Route::post('/action-proposals/{proposalId}/decision', [AgentGovernanceController::class, 'decideAction']);
        Route::post('/action-proposals/{proposalId}/execute', [AgentGovernanceController::class, 'executeApprovedDraft']);
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
