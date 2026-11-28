import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export type InitiativeType =
  | 'NEW_BRAND'
  | 'NEW_COLLECTION'
  | 'CATEGORY_EXPANSION'
  | 'MARKET_ENTRY'
  | 'PRODUCT_INNOVATION'
  | 'CUSTOMER_OPPORTUNITY'
  | 'CAPABILITY_LED'
  | 'REPLENISHMENT'
  | 'SUSTAINABILITY_IMPROVEMENT'
  | 'QUALITY_IMPROVEMENT';

export type InitiativeStatus =
  | 'DRAFT'
  | 'SCREENING'
  | 'RESEARCH'
  | 'ASSESSMENT'
  | 'APPROVED'
  | 'CONDITIONALLY_APPROVED'
  | 'PARKED'
  | 'REJECTED';

export interface InitiativeInput {
  code: string;
  initiative_type: InitiativeType;
  title: string;
  problem_statement: string;
  objective: string;
  target_market: string;
  target_consumer: string | null;
  expected_benefit: string | null;
  sponsor_entity_id: string | null;
}

export interface Initiative {
  id: string;
  sponsor_entity_id: string | null;
  owner_user_id: string;
  code: string;
  initiative_type: InitiativeType;
  title: string;
  problem_statement: string;
  objective: string;
  target_market: string;
  target_consumer: string | null;
  expected_benefit: string | null;
  status: InitiativeStatus;
  current_version: number;
  created_at: string;
  updated_at: string;
  current_snapshot?: InitiativeInput;
}

export interface InitiativeDecisionInput {
  decision: 'APPROVE_RESEARCH' | 'APPROVE' | 'APPROVE_CONDITIONAL' | 'RETURN_TO_RESEARCH' | 'PARK' | 'REJECT';
  reason: string;
  actions: string[];
  review_date: string | null;
}

export interface InitiativeDecision {
  id: string;
  stage: string;
  decision: string;
  reason: string;
  actions: string[] | string;
  review_date: string | null;
  decided_at: string;
}

export interface InitiativeVersion {
  id: string;
  version_no: number;
  snapshot: InitiativeInput | string;
  change_reason: string;
  created_at: string;
}

export interface InitiativeDetailResponse {
  data: Initiative;
  versions: InitiativeVersion[];
  decisions: InitiativeDecision[];
  insights: MarketInsight[];
}

export type InsightSourceType =
  | 'STORE_OBSERVATION'
  | 'CUSTOMER_FEEDBACK'
  | 'WHOLESALE_REQUEST'
  | 'ECOMMERCE_ANALYTICS'
  | 'SURVEY'
  | 'COMPETITOR_RESEARCH'
  | 'EXTERNAL_BENCHMARK'
  | 'INTERNAL_SYSTEM'
  | 'SUPPLIER_SIGNAL'
  | 'PRODUCTION_CAPABILITY'
  | 'OTHER';

export interface InsightSourceInput {
  source_type: InsightSourceType;
  source_name: string;
  source_reference: string | null;
  capture_method: string;
  geography: string | null;
  reliability_score: number | null;
  permitted_use_basis: string;
  limitations: string | null;
}

export interface InsightSource {
  id: string;
  source_type: InsightSourceType;
  source_name: string;
  source_reference: string | null;
  capture_method: string;
  geography: string | null;
  reliability_score: number | string | null;
  permitted_use_basis: string;
  limitations: string | null;
  created_at: string;
}

export type MarketInsightType =
  | 'OBSERVED_FACT'
  | 'SURVEY_RESULT'
  | 'QUALITATIVE_EVIDENCE'
  | 'EXTERNAL_BENCHMARK'
  | 'HYPOTHESIS'
  | 'FORECAST_SIGNAL'
  | 'CUSTOMER_REQUEST'
  | 'FIT_OR_QUALITY_FEEDBACK'
  | 'SUPPLY_OR_CAPACITY_SIGNAL';

export interface MarketInsightInput {
  source_id: string;
  initiative_id: string | null;
  insight_type: MarketInsightType;
  title: string;
  summary: string;
  observed_at: string;
  geography: string | null;
  target_segment: string | null;
  channel_code: string | null;
  category_code: string | null;
  metric_name: string | null;
  metric_value_numeric: number | null;
  metric_value_text: string | null;
  metric_unit: string | null;
  sample_size: number | null;
  denominator: number | null;
  confidence: number | null;
  limitations: string | null;
  privacy_basis: string;
}

export interface MarketInsight {
  id: string;
  initiative_id: string | null;
  insight_type: MarketInsightType;
  title: string;
  summary: string;
  observed_at: string;
  captured_at: string;
  geography: string | null;
  target_segment: string | null;
  channel_code: string | null;
  category_code: string | null;
  metric_name: string | null;
  metric_value_numeric: string | number | null;
  metric_value_text: string | null;
  metric_unit: string | null;
  sample_size: number | null;
  denominator: number | null;
  confidence: string | number | null;
  limitations: string | null;
  source_id: string;
  source_name: string;
  source_type: InsightSourceType;
}

export interface PageResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

interface ResourceResponse<T> {
  data: T;
}

@Injectable({ providedIn: 'root' })
export class MerchandisingService {
  private readonly http = inject(HttpClient);

  listInitiatives(): Observable<PageResponse<Initiative>> {
    return this.http.get<PageResponse<Initiative>>('/api/v1/product/initiatives');
  }

  getInitiative(id: string): Observable<InitiativeDetailResponse> {
    return this.http.get<InitiativeDetailResponse>(
      '/api/v1/product/initiatives/' + encodeURIComponent(id),
    );
  }

  createInitiative(input: InitiativeInput): Observable<ResourceResponse<Initiative> & { version: number }> {
    return this.http.post<ResourceResponse<Initiative> & { version: number }>(
      '/api/v1/product/initiatives',
      input,
    );
  }

  submitInitiative(id: string): Observable<ResourceResponse<Initiative>> {
    return this.http.post<ResourceResponse<Initiative>>(
      '/api/v1/product/initiatives/' + encodeURIComponent(id) + '/submit',
      {},
    );
  }

  advanceToAssessment(id: string): Observable<ResourceResponse<Initiative>> {
    return this.http.post<ResourceResponse<Initiative>>(
      '/api/v1/product/initiatives/' + encodeURIComponent(id) + '/advance-to-assessment',
      {},
    );
  }

  decideInitiative(id: string, input: InitiativeDecisionInput): Observable<ResourceResponse<Initiative>> {
    return this.http.post<ResourceResponse<Initiative>>(
      '/api/v1/product/initiatives/' + encodeURIComponent(id) + '/decisions',
      input,
    );
  }

  listInsightSources(): Observable<PageResponse<InsightSource>> {
    return this.http.get<PageResponse<InsightSource>>('/api/v1/product/insight-sources');
  }

  createInsightSource(input: InsightSourceInput): Observable<ResourceResponse<InsightSource>> {
    return this.http.post<ResourceResponse<InsightSource>>('/api/v1/product/insight-sources', input);
  }

  listMarketInsights(initiativeId?: string): Observable<PageResponse<MarketInsight>> {
    let params = new HttpParams();
    if (initiativeId) {
      params = params.set('initiative_id', initiativeId);
    }

    return this.http.get<PageResponse<MarketInsight>>('/api/v1/product/market-insights', { params });
  }

  createMarketInsight(input: MarketInsightInput): Observable<ResourceResponse<MarketInsight>> {
    return this.http.post<ResourceResponse<MarketInsight>>('/api/v1/product/market-insights', input);
  }
}
