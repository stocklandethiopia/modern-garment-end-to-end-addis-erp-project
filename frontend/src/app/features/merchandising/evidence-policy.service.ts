import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export type EvidenceInitiativeType =
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

export type EvidenceInsightType =
  | 'OBSERVED_FACT'
  | 'SURVEY_RESULT'
  | 'QUALITATIVE_EVIDENCE'
  | 'EXTERNAL_BENCHMARK'
  | 'HYPOTHESIS'
  | 'FORECAST_SIGNAL'
  | 'CUSTOMER_REQUEST'
  | 'FIT_OR_QUALITY_FEEDBACK'
  | 'SUPPLY_OR_CAPACITY_SIGNAL';

export interface EvidencePolicyRule {
  id: string;
  requirement_code: string;
  insight_type: EvidenceInsightType;
  minimum_records: number;
  minimum_distinct_sources: number;
  minimum_confidence: number | null;
  minimum_source_reliability: number | null;
  max_age_days: number | null;
  require_geography: boolean;
  require_target_segment: boolean;
  require_sample_denominator: boolean;
  is_mandatory: boolean;
}

export interface EvidencePolicy {
  id: string;
  tenant_id: string;
  code: string;
  name: string;
  initiative_type: EvidenceInitiativeType;
  stage: 'RESEARCH' | 'ASSESSMENT';
  target_market: string | null;
  version_no: number;
  effective_from: string;
  effective_to: string | null;
  status: 'DRAFT' | 'PENDING_APPROVAL' | 'APPROVED' | 'REJECTED';
  change_reason: string;
  created_by: string;
  approved_by: string | null;
  approved_at: string | null;
  created_at: string;
  rule_count?: number;
  rules?: EvidencePolicyRule[];
}

export interface EvidencePolicyInput {
  code: string;
  name: string;
  initiative_type: EvidenceInitiativeType;
  stage: 'RESEARCH' | 'ASSESSMENT';
  target_market: string | null;
  effective_from: string;
  effective_to: string | null;
  change_reason: string;
  rules: Omit<EvidencePolicyRule, 'id'>[];
}

export interface EvidencePolicyCoverage {
  initiative_id: string;
  stage: 'RESEARCH' | 'ASSESSMENT';
  evidence_coverage: {
    status: 'MET' | 'GAP' | 'NOT_CONFIGURED';
    coverage_percent: number | null;
    mandatory_rules: number;
    mandatory_rules_met: number;
    policy: Pick<EvidencePolicy, 'id' | 'code' | 'name' | 'version_no' | 'initiative_type' | 'stage' | 'target_market' | 'effective_from' | 'effective_to'> | null;
    rules: Array<{
      requirement_code: string;
      insight_type: EvidenceInsightType;
      mandatory: boolean;
      status: 'MET' | 'GAP';
      observed_records: number;
      qualifying_records: number;
      distinct_sources: number;
      gaps: string[];
    }>;
    message: string;
  };
}

interface Paginator<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

interface PageEnvelope<T> {
  data: Paginator<T>;
}

interface ResourceEnvelope<T> {
  data: T;
}

@Injectable({ providedIn: 'root' })
export class EvidencePolicyService {
  private readonly http = inject(HttpClient);

  list(): Observable<PageEnvelope<EvidencePolicy>> {
    return this.http.get<PageEnvelope<EvidencePolicy>>('/api/v1/product/evidence-policies');
  }

  create(input: EvidencePolicyInput): Observable<ResourceEnvelope<{ policy: EvidencePolicy; rules: EvidencePolicyRule[] }>> {
    return this.http.post<ResourceEnvelope<{ policy: EvidencePolicy; rules: EvidencePolicyRule[] }>>(
      '/api/v1/product/evidence-policies',
      input,
    );
  }

  submit(policyId: string): Observable<ResourceEnvelope<{ policy: EvidencePolicy; rules: EvidencePolicyRule[] }>> {
    return this.http.post<ResourceEnvelope<{ policy: EvidencePolicy; rules: EvidencePolicyRule[] }>>(
      `/api/v1/product/evidence-policies/${policyId}/submit`,
      {},
    );
  }

  decide(
    policyId: string,
    decision: 'APPROVE' | 'REJECT',
    reason: string,
  ): Observable<ResourceEnvelope<{ policy: EvidencePolicy; rules: EvidencePolicyRule[] }>> {
    return this.http.post<ResourceEnvelope<{ policy: EvidencePolicy; rules: EvidencePolicyRule[] }>>(
      `/api/v1/product/evidence-policies/${policyId}/decisions`,
      { decision, reason },
    );
  }

  coverage(initiativeId: string, stage: 'RESEARCH' | 'ASSESSMENT'): Observable<ResourceEnvelope<EvidencePolicyCoverage>> {
    return this.http.get<ResourceEnvelope<EvidencePolicyCoverage>>(
      `/api/v1/product/initiatives/${initiativeId}/evidence-coverage`,
      { params: { stage } },
    );
  }
}
