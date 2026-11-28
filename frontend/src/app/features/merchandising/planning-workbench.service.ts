import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface Paginator<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

export interface PageEnvelope<T> {
  data: Paginator<T>;
}

export interface ResourceEnvelope<T> {
  data: T;
}

export interface LegalEntityOption {
  id: string;
  legal_name: string;
  registration_number: string;
  base_currency: string;
  status: string;
}

export interface ForecastRun {
  id: string;
  grain: string;
  method: string;
  model_version: string | null;
  horizon: string;
  scenario_code: string;
  forecast_start: string;
  forecast_end: string;
  status: string;
  created_at: string;
}

export interface ForecastItem {
  id: string;
  dimension_refs: string | Record<string, string>;
  time_bucket: string;
  forecast_quantity: string;
  forecast_value: string | null;
  currency: string | null;
  confidence_low: string | null;
  confidence_high: string | null;
}

export interface ForecastAdjustment {
  id: string;
  baseline_value: string;
  override_value: string;
  delta_value: string;
  reason: string;
  is_approved: boolean;
}

export interface ForecastDetail {
  data: ForecastRun;
  forecasts: ForecastItem[];
  adjustments: ForecastAdjustment[];
}

export interface OtbPolicy {
  id: string;
  legal_entity_id: string;
  code: string;
  budget_basis: 'RETAIL' | 'COST';
  valuation_basis: 'RETAIL' | 'COST';
  formula_version: string;
  effective_from: string;
  effective_to: string | null;
  status: string;
}

export interface OtbPeriod {
  id: string;
  otb_policy_id: string;
  policy_code?: string;
  period_start: string;
  period_end: string;
  currency: string;
  available_budget: string;
  status: string;
  formula_version?: string;
}

export interface OtbScenario {
  id: string;
  otb_period_id: string;
  status: string;
  assumptions: string | Record<string, unknown>;
  result: string | {
    formula_version: string;
    budget_basis: string;
    valuation_basis: string;
    components: Record<string, string>;
    calculation: { available_budget: string; interpretation: string };
    warning: string;
  };
  created_at: string;
}

export interface PriceList {
  id: string;
  legal_entity_id: string;
  code: string;
  version_no: number;
  channel_code: string;
  market_scope: string;
  currency: string;
  effective_from: string;
  effective_to: string | null;
  status: string;
}

export interface PriceListLine {
  id: string;
  price_list_id: string;
  product_reference: string;
  list_price: string;
  wholesale_price: string | null;
  price_floor: string | null;
  cost_basis: string | null;
}

export interface Promotion {
  id: string;
  price_list_id: string;
  price_list_code?: string;
  code: string;
  promotion_type: 'PERCENTAGE' | 'FIXED_AMOUNT';
  discount_value: string;
  stack_policy: string;
  starts_at: string;
  ends_at: string;
  status: string;
}

@Injectable({ providedIn: 'root' })
export class PlanningWorkbenchService {
  private readonly http = inject(HttpClient);

  listLegalEntities(): Observable<{ data: LegalEntityOption[] }> {
    return this.http.get<{ data: LegalEntityOption[] }>('/api/v1/enterprise/legal-entities');
  }

  listForecastRuns(): Observable<PageEnvelope<ForecastRun>> {
    return this.http.get<PageEnvelope<ForecastRun>>('/api/v1/product/forecast-runs');
  }

  publishForecast(input: Record<string, unknown>): Observable<ResourceEnvelope<ForecastRun> & { row_count: number }> {
    return this.http.post<ResourceEnvelope<ForecastRun> & { row_count: number }>(
      '/api/v1/product/forecast-runs',
      input,
    );
  }

  getForecastRun(runId: string): Observable<ForecastDetail> {
    return this.http.get<ForecastDetail>(
      '/api/v1/product/forecast-runs/' + encodeURIComponent(runId),
    );
  }

  createForecastAdjustment(forecastId: string, input: Record<string, unknown>): Observable<ResourceEnvelope<ForecastAdjustment> & { approval_status: string }> {
    return this.http.post<ResourceEnvelope<ForecastAdjustment> & { approval_status: string }>(
      '/api/v1/product/forecasts/' + encodeURIComponent(forecastId) + '/adjustments',
      input,
    );
  }

  approveForecastAdjustment(adjustmentId: string): Observable<{ data: ForecastAdjustment; approval_status: string }> {
    return this.http.post<{ data: ForecastAdjustment; approval_status: string }>(
      '/api/v1/product/forecast-adjustments/' + encodeURIComponent(adjustmentId) + '/approve',
      {},
    );
  }

  listOtbPolicies(): Observable<PageEnvelope<OtbPolicy>> {
    return this.http.get<PageEnvelope<OtbPolicy>>('/api/v1/product/otb-policies');
  }

  createOtbPolicy(input: Record<string, unknown>): Observable<ResourceEnvelope<OtbPolicy>> {
    return this.http.post<ResourceEnvelope<OtbPolicy>>('/api/v1/product/otb-policies', input);
  }

  activateOtbPolicy(policyId: string): Observable<ResourceEnvelope<OtbPolicy>> {
    return this.http.post<ResourceEnvelope<OtbPolicy>>(
      '/api/v1/product/otb-policies/' + encodeURIComponent(policyId) + '/activate',
      {},
    );
  }

  listOtbPeriods(): Observable<PageEnvelope<OtbPeriod>> {
    return this.http.get<PageEnvelope<OtbPeriod>>('/api/v1/product/otb-periods');
  }

  createOtbPeriod(input: Record<string, unknown>): Observable<ResourceEnvelope<OtbPeriod> & { formula_version: string }> {
    return this.http.post<ResourceEnvelope<OtbPeriod> & { formula_version: string }>(
      '/api/v1/product/otb-periods',
      input,
    );
  }

  simulateOtb(periodId: string, input: Record<string, unknown>): Observable<ResourceEnvelope<OtbScenario>> {
    return this.http.post<ResourceEnvelope<OtbScenario>>(
      '/api/v1/product/otb-periods/' + encodeURIComponent(periodId) + '/scenarios',
      input,
    );
  }

  approveOtbScenario(scenarioId: string): Observable<{ status: string }> {
    return this.http.post<{ status: string }>(
      '/api/v1/product/otb-scenarios/' + encodeURIComponent(scenarioId) + '/approve',
      {},
    );
  }

  listPriceLists(): Observable<PageEnvelope<PriceList>> {
    return this.http.get<PageEnvelope<PriceList>>('/api/v1/product/price-lists');
  }

  createPriceList(input: Record<string, unknown>): Observable<ResourceEnvelope<PriceList>> {
    return this.http.post<ResourceEnvelope<PriceList>>('/api/v1/product/price-lists', input);
  }

  listPriceListLines(priceListId: string): Observable<PageEnvelope<PriceListLine>> {
    return this.http.get<PageEnvelope<PriceListLine>>(
      '/api/v1/product/price-lists/' + encodeURIComponent(priceListId) + '/lines',
    );
  }

  addPriceListLine(priceListId: string, input: Record<string, unknown>): Observable<ResourceEnvelope<PriceListLine>> {
    return this.http.post<ResourceEnvelope<PriceListLine>>(
      '/api/v1/product/price-lists/' + encodeURIComponent(priceListId) + '/lines',
      input,
    );
  }

  publishPriceList(priceListId: string): Observable<ResourceEnvelope<PriceList>> {
    return this.http.post<ResourceEnvelope<PriceList>>(
      '/api/v1/product/price-lists/' + encodeURIComponent(priceListId) + '/publish',
      {},
    );
  }

  listPromotions(): Observable<PageEnvelope<Promotion>> {
    return this.http.get<PageEnvelope<Promotion>>('/api/v1/product/promotions');
  }

  createPromotion(input: Record<string, unknown>): Observable<ResourceEnvelope<Promotion>> {
    return this.http.post<ResourceEnvelope<Promotion>>('/api/v1/product/promotions', input);
  }

  addPromotionEligibility(promotionId: string, input: Record<string, unknown>): Observable<ResourceEnvelope<Record<string, unknown>>> {
    return this.http.post<ResourceEnvelope<Record<string, unknown>>>(
      '/api/v1/product/promotions/' + encodeURIComponent(promotionId) + '/eligibility',
      input,
    );
  }

  approvePromotion(promotionId: string): Observable<ResourceEnvelope<Promotion>> {
    return this.http.post<ResourceEnvelope<Promotion>>(
      '/api/v1/product/promotions/' + encodeURIComponent(promotionId) + '/approve',
      {},
    );
  }
}
