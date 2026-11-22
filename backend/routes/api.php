<?php

use App\Http\Controllers\Auth\TokenController;
use App\Http\Controllers\Enterprise\BusinessSiteController;
use App\Http\Controllers\Enterprise\InventoryLocationController;
use App\Http\Controllers\Enterprise\LegalEntityController;
use App\Http\Controllers\Enterprise\LicensingEvidenceController;
use App\Http\Controllers\Enterprise\TenantController;
use App\Http\Controllers\Merchandising\CommercialPricingController;
use App\Http\Controllers\Merchandising\DemandPlanningController;
use App\Http\Controllers\Merchandising\InitiativeController;
use App\Http\Controllers\Merchandising\InsightSourceController;
use App\Http\Controllers\Merchandising\MarketInsightController;
use App\Http\Controllers\Merchandising\MerchandisingPlanningController;
use App\Http\Controllers\Merchandising\OtbPlanningController;
use App\Http\Controllers\ProductDevelopment\ProductDevelopmentController;
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
        Route::get('/enterprise/sites/{siteId}/branch-registrations', [LicensingEvidenceController::class, 'listBranchRegistrations']);
        Route::post('/enterprise/sites/{siteId}/branch-registrations', [LicensingEvidenceController::class, 'createBranchRegistration']);
        Route::get('/enterprise/legal-entities/{entityId}/licences', [LicensingEvidenceController::class, 'listLicences']);
        Route::post('/enterprise/legal-entities/{entityId}/licences', [LicensingEvidenceController::class, 'createLicence']);

        Route::get('/product/initiatives', [InitiativeController::class, 'index']);
        Route::post('/product/initiatives', [InitiativeController::class, 'store']);
        Route::get('/product/initiatives/{initiativeId}', [InitiativeController::class, 'show']);
        Route::post('/product/initiatives/{initiativeId}/submit', [InitiativeController::class, 'submit']);
        Route::post('/product/initiatives/{initiativeId}/advance-to-assessment', [InitiativeController::class, 'advanceToAssessment']);
        Route::post('/product/initiatives/{initiativeId}/decisions', [InitiativeController::class, 'decide']);

        Route::get('/product/insight-sources', [InsightSourceController::class, 'index']);
        Route::post('/product/insight-sources', [InsightSourceController::class, 'store']);
        Route::get('/product/market-insights', [MarketInsightController::class, 'index']);
        Route::post('/product/market-insights', [MarketInsightController::class, 'store']);

        Route::get('/product/brands', [MerchandisingPlanningController::class, 'brands']);
        Route::post('/product/brands', [MerchandisingPlanningController::class, 'createBrand']);
        Route::post('/product/brands/{brandId}/strategy-versions', [MerchandisingPlanningController::class, 'createBrandStrategy']);
        Route::post('/product/brands/{brandId}/strategy-versions/{versionId}/approve', [MerchandisingPlanningController::class, 'approveBrandStrategy']);

        Route::get('/product/seasons', [MerchandisingPlanningController::class, 'seasons']);
        Route::post('/product/seasons', [MerchandisingPlanningController::class, 'createSeason']);

        Route::get('/product/collections', [MerchandisingPlanningController::class, 'collections']);
        Route::post('/product/collections', [MerchandisingPlanningController::class, 'createCollection']);
        Route::post('/product/collections/{collectionId}/versions', [MerchandisingPlanningController::class, 'createCollectionVersion']);
        Route::post('/product/collections/{collectionId}/versions/{versionId}/approve', [MerchandisingPlanningController::class, 'approveCollectionVersion']);

        Route::get('/product/range-plans', [MerchandisingPlanningController::class, 'rangePlans']);
        Route::post('/product/range-plans', [MerchandisingPlanningController::class, 'createRangePlan']);
        Route::get('/product/range-plans/{rangePlanId}/lines', [MerchandisingPlanningController::class, 'rangeLines']);
        Route::post('/product/range-plans/{rangePlanId}/lines', [MerchandisingPlanningController::class, 'createRangeLine']);
        Route::post('/product/range-plans/{rangePlanId}/freeze', [MerchandisingPlanningController::class, 'freezeRangePlan']);

        Route::get('/product/assortment-plans', [MerchandisingPlanningController::class, 'assortmentPlans']);
        Route::post('/product/assortment-plans', [MerchandisingPlanningController::class, 'createAssortmentPlan']);
        Route::get('/product/assortment-plans/{assortmentPlanId}/lines', [MerchandisingPlanningController::class, 'assortmentLines']);
        Route::post('/product/assortment-plans/{assortmentPlanId}/lines', [MerchandisingPlanningController::class, 'createAssortmentLine']);

        Route::get('/product/forecast-runs', [DemandPlanningController::class, 'runs']);
        Route::post('/product/forecast-runs', [DemandPlanningController::class, 'publishRun']);
        Route::get('/product/forecast-runs/{runId}', [DemandPlanningController::class, 'showRun']);
        Route::post('/product/forecasts/{forecastId}/adjustments', [DemandPlanningController::class, 'createAdjustment']);
        Route::post('/product/forecast-adjustments/{adjustmentId}/approve', [DemandPlanningController::class, 'approveAdjustment']);

        Route::get('/product/otb-policies', [OtbPlanningController::class, 'policies']);
        Route::post('/product/otb-policies', [OtbPlanningController::class, 'createPolicy']);
        Route::post('/product/otb-policies/{policyId}/activate', [OtbPlanningController::class, 'activatePolicy']);
        Route::get('/product/otb-periods', [OtbPlanningController::class, 'periods']);
        Route::post('/product/otb-periods', [OtbPlanningController::class, 'createPeriod']);
        Route::post('/product/otb-periods/{periodId}/scenarios', [OtbPlanningController::class, 'simulateScenario']);
        Route::post('/product/otb-scenarios/{scenarioId}/approve', [OtbPlanningController::class, 'approveScenario']);

        Route::get('/product/price-lists', [CommercialPricingController::class, 'priceLists']);
        Route::post('/product/price-lists', [CommercialPricingController::class, 'createPriceList']);
        Route::get('/product/price-lists/{priceListId}/lines', [CommercialPricingController::class, 'priceListLines']);
        Route::post('/product/price-lists/{priceListId}/lines', [CommercialPricingController::class, 'createPriceListLine']);
        Route::post('/product/price-lists/{priceListId}/publish', [CommercialPricingController::class, 'publishPriceList']);
        Route::get('/product/promotions', [CommercialPricingController::class, 'promotions']);
        Route::post('/product/promotions', [CommercialPricingController::class, 'createPromotion']);
        Route::post('/product/promotions/{promotionId}/eligibility', [CommercialPricingController::class, 'addPromotionEligibility']);
        Route::get('/product-briefs', [ProductDevelopmentController::class, 'productBriefs']);
        Route::post('/product-briefs', [ProductDevelopmentController::class, 'createProductBrief']);
        Route::get('/product-briefs/{productBriefId}', [ProductDevelopmentController::class, 'showProductBrief']);
        Route::post('/product-briefs/{productBriefId}/versions', [ProductDevelopmentController::class, 'createProductBriefVersion']);
        Route::post('/product-briefs/{productBriefId}/submit', [ProductDevelopmentController::class, 'submitProductBrief']);
        Route::post('/product-briefs/{productBriefId}/decisions', [ProductDevelopmentController::class, 'decideProductBrief']);

        Route::get('/styles', [ProductDevelopmentController::class, 'styles']);
        Route::post('/styles', [ProductDevelopmentController::class, 'createStyle']);
        Route::get('/styles/{styleId}', [ProductDevelopmentController::class, 'showStyle']);
        Route::post('/styles/{styleId}/revisions', [ProductDevelopmentController::class, 'createStyleRevision']);
        Route::post('/product/promotions/{promotionId}/approve', [CommercialPricingController::class, 'approvePromotion']);
    });
});
