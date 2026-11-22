import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface PlanningPage<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

export interface PlanningPageResponse<T> {
  data: PlanningPage<T>;
}

export interface PlanningResourceResponse<T> {
  data: T;
}

export interface BrandRecord {
  id: string;
  code: string;
  name: string;
  status: string;
  current_strategy_version: number;
  current_strategy_status: string;
  latest_strategy_status: string;
  latest_strategy_version_id: string | null;
  latest_strategy_version_no: number | null;
  latest_strategy_created_by: string | null;
}

export interface SeasonRecord {
  id: string;
  code: string;
  name: string;
  calendar_type: string;
  market_scope: string | null;
  timezone: string;
  design_start: string | null;
  buy_start: string | null;
  production_start: string | null;
  production_end: string | null;
  delivery_start: string | null;
  delivery_end: string | null;
  selling_start: string | null;
  selling_end: string | null;
  status: string;
}

export interface CollectionRecord {
  id: string;
  brand_id: string;
  season_id: string;
  brand_name: string;
  season_name: string;
  code: string;
  name: string;
  concept: string | null;
  target_market: string;
  current_version: number;
  status: string;
  latest_version_id: string | null;
  latest_version_no: number | null;
  latest_version_created_by: string | null;
  latest_version_status: string;
}

export interface VersionDraft {
  id: string;
  version_no: number;
  status: string;
}

export interface RangePlanRecord {
  id: string;
  collection_id: string;
  collection_version: number;
  code: string;
  plan_start: string;
  plan_end: string;
  status: string;
  owner_user_id: string;
}

export interface RangeLineRecord {
  id: string;
  range_plan_id: string;
  line_code: string;
  category_code: string;
  target_tier: string | null;
  product_hypothesis: string;
  planned_units: number;
  target_retail_price: string | null;
  target_wholesale_price: string | null;
  price_currency: string | null;
  target_margin_percent: string | null;
  channel_scope: string[] | string;
  size_colour_intent: Record<string, unknown> | string;
  lifecycle_type: string;
  forecast_basis: string;
  launch_date: string | null;
}

export interface AssortmentPlanRecord {
  id: string;
  range_plan_id: string;
  range_plan_code: string;
  range_plan_status: string;
  code: string;
  channel_code: string;
  market_scope: string;
  account_reference: string | null;
  location_cluster_reference: string | null;
  status: string;
  owner_user_id: string;
}

export interface AssortmentLineRecord {
  id: string;
  assortment_plan_id: string;
  range_line_id: string;
  line_code: string;
  category_code: string;
  variant_size_curve: Record<string, unknown> | string;
  planned_units: number;
  launch_date: string | null;
  minimum_depth: number | null;
  maximum_depth: number | null;
}

export interface BrandInput {
  code: string;
  name: string;
  owner_entity_id: string | null;
}

export interface BrandStrategyInput {
  positioning: string;
  target_consumer: string;
  value_proposition: string;
  quality_tier: string | null;
  approved_categories: string[];
  price_architecture: Record<string, unknown>;
  approved_channels: string[];
  effective_from: string | null;
  effective_to: string | null;
  change_reason: string;
}

export interface SeasonInput {
  code: string;
  name: string;
  calendar_type: string;
  market_scope: string | null;
  timezone: string;
  design_start: string | null;
  buy_start: string | null;
  production_start: string | null;
  production_end: string | null;
  delivery_start: string | null;
  delivery_end: string | null;
  selling_start: string | null;
  selling_end: string | null;
}

export interface CollectionInput {
  brand_id: string;
  season_id: string;
  code: string;
  name: string;
  concept: string | null;
  target_market: string;
}

export interface CollectionVersionInput {
  story: string;
  target_consumer: string;
  category_architecture: unknown[];
  price_margin_envelope: Record<string, unknown>;
  budget_amount: string | null;
  budget_currency: string | null;
  delivery_start: string | null;
  delivery_end: string | null;
  change_reason: string;
}

export interface RangePlanInput {
  collection_id: string;
  collection_version: number;
  code: string;
  plan_start: string;
  plan_end: string;
}

export interface RangeLineInput {
  line_code: string;
  category_code: string;
  target_tier: string | null;
  product_hypothesis: string;
  planned_units: number;
  target_retail_price: string | null;
  target_wholesale_price: string | null;
  price_currency: string | null;
  target_margin_percent: string | null;
  channel_scope: string[];
  size_colour_intent: Record<string, unknown>;
  lifecycle_type: string;
  forecast_basis: string;
  launch_date: string | null;
}

export interface AssortmentPlanInput {
  range_plan_id: string;
  code: string;
  channel_code: string;
  market_scope: string;
  account_reference: string | null;
  location_cluster_reference: string | null;
}

export interface AssortmentLineInput {
  range_line_id: string;
  variant_size_curve: Record<string, unknown>;
  planned_units: number;
  launch_date: string | null;
  minimum_depth: number | null;
  maximum_depth: number | null;
}

@Injectable({ providedIn: 'root' })
export class RangePlanningService {
  private readonly http = inject(HttpClient);
  private readonly base = '/api/v1/product';

  listBrands(): Observable<PlanningPageResponse<BrandRecord>> {
    return this.http.get<PlanningPageResponse<BrandRecord>>(this.base + '/brands');
  }

  createBrand(input: BrandInput): Observable<PlanningResourceResponse<BrandRecord>> {
    return this.http.post<PlanningResourceResponse<BrandRecord>>(this.base + '/brands', input);
  }

  createBrandStrategy(brandId: string, input: BrandStrategyInput): Observable<PlanningResourceResponse<VersionDraft>> {
    return this.http.post<PlanningResourceResponse<VersionDraft>>(
      this.base + '/brands/' + encodeURIComponent(brandId) + '/strategy-versions',
      input,
    );
  }

  approveBrandStrategy(brandId: string, versionId: string): Observable<PlanningResourceResponse<BrandRecord>> {
    return this.http.post<PlanningResourceResponse<BrandRecord>>(
      this.base + '/brands/' + encodeURIComponent(brandId) + '/strategy-versions/' + encodeURIComponent(versionId) + '/approve',
      {},
    );
  }

  listSeasons(): Observable<PlanningPageResponse<SeasonRecord>> {
    return this.http.get<PlanningPageResponse<SeasonRecord>>(this.base + '/seasons');
  }

  createSeason(input: SeasonInput): Observable<PlanningResourceResponse<SeasonRecord>> {
    return this.http.post<PlanningResourceResponse<SeasonRecord>>(this.base + '/seasons', input);
  }

  listCollections(): Observable<PlanningPageResponse<CollectionRecord>> {
    return this.http.get<PlanningPageResponse<CollectionRecord>>(this.base + '/collections');
  }

  createCollection(input: CollectionInput): Observable<PlanningResourceResponse<CollectionRecord>> {
    return this.http.post<PlanningResourceResponse<CollectionRecord>>(this.base + '/collections', input);
  }

  createCollectionVersion(collectionId: string, input: CollectionVersionInput): Observable<PlanningResourceResponse<VersionDraft>> {
    return this.http.post<PlanningResourceResponse<VersionDraft>>(
      this.base + '/collections/' + encodeURIComponent(collectionId) + '/versions',
      input,
    );
  }

  approveCollectionVersion(collectionId: string, versionId: string): Observable<PlanningResourceResponse<CollectionRecord>> {
    return this.http.post<PlanningResourceResponse<CollectionRecord>>(
      this.base + '/collections/' + encodeURIComponent(collectionId) + '/versions/' + encodeURIComponent(versionId) + '/approve',
      {},
    );
  }

  listRangePlans(): Observable<PlanningPageResponse<RangePlanRecord>> {
    return this.http.get<PlanningPageResponse<RangePlanRecord>>(this.base + '/range-plans');
  }

  createRangePlan(input: RangePlanInput): Observable<PlanningResourceResponse<RangePlanRecord>> {
    return this.http.post<PlanningResourceResponse<RangePlanRecord>>(this.base + '/range-plans', input);
  }

  listRangeLines(rangePlanId: string): Observable<PlanningPageResponse<RangeLineRecord>> {
    return this.http.get<PlanningPageResponse<RangeLineRecord>>(
      this.base + '/range-plans/' + encodeURIComponent(rangePlanId) + '/lines',
    );
  }

  createRangeLine(rangePlanId: string, input: RangeLineInput): Observable<PlanningResourceResponse<RangeLineRecord>> {
    return this.http.post<PlanningResourceResponse<RangeLineRecord>>(
      this.base + '/range-plans/' + encodeURIComponent(rangePlanId) + '/lines',
      input,
    );
  }

  freezeRangePlan(rangePlanId: string): Observable<PlanningResourceResponse<RangePlanRecord>> {
    return this.http.post<PlanningResourceResponse<RangePlanRecord>>(
      this.base + '/range-plans/' + encodeURIComponent(rangePlanId) + '/freeze',
      {},
    );
  }

  listAssortmentPlans(): Observable<PlanningPageResponse<AssortmentPlanRecord>> {
    return this.http.get<PlanningPageResponse<AssortmentPlanRecord>>(this.base + '/assortment-plans');
  }

  createAssortmentPlan(input: AssortmentPlanInput): Observable<PlanningResourceResponse<AssortmentPlanRecord>> {
    return this.http.post<PlanningResourceResponse<AssortmentPlanRecord>>(this.base + '/assortment-plans', input);
  }

  listAssortmentLines(assortmentPlanId: string): Observable<PlanningPageResponse<AssortmentLineRecord>> {
    return this.http.get<PlanningPageResponse<AssortmentLineRecord>>(
      this.base + '/assortment-plans/' + encodeURIComponent(assortmentPlanId) + '/lines',
    );
  }

  createAssortmentLine(assortmentPlanId: string, input: AssortmentLineInput): Observable<PlanningResourceResponse<AssortmentLineRecord>> {
    return this.http.post<PlanningResourceResponse<AssortmentLineRecord>>(
      this.base + '/assortment-plans/' + encodeURIComponent(assortmentPlanId) + '/lines',
      input,
    );
  }
}
