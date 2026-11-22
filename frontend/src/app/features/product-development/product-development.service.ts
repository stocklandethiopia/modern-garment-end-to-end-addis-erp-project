import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface ProductDevelopmentPage<T> {
  data: { data: T[]; current_page: number; last_page: number; total: number };
}

export interface ProductDevelopmentResource<T> {
  data: T;
}

export interface ProductBrief {
  id: string;
  initiative_id: string | null;
  collection_id: string;
  range_plan_id: string;
  range_line_id: string;
  line_code: string;
  category_code: string;
  range_plan_code: string;
  collection_code: string;
  collection_name: string;
  target_consumer: string;
  product_need: string;
  intended_use: string;
  target_price: string | null;
  target_cost: string | null;
  target_currency: string;
  quality_tier: string | null;
  delivery_need_by: string | null;
  sustainability_intent: string[] | string;
  status: 'DRAFT' | 'SUBMITTED_FOR_REVIEW' | 'ACCEPTED' | 'REJECTED';
  current_version: number;
  accepted_version: number | null;
  owner_user_id: string;
}

export interface ProductBriefVersion {
  id: string;
  version_no: number;
  snapshot: Record<string, unknown>;
  change_reason: string;
  created_by: string;
  created_at: string;
}

export interface ProductBriefDecision {
  id: string;
  version_no: number;
  decision: 'ACCEPT' | 'REJECT';
  reason: string;
  actions: string[] | string;
  decided_by: string;
  decided_at: string;
}

export interface ProductBriefDetail {
  data: ProductBrief;
  versions: ProductBriefVersion[];
  decisions: ProductBriefDecision[];
  styles: StyleRecord[];
}

export interface StyleRecord {
  id: string;
  product_brief_id: string;
  product_brief_version: number;
  style_code: string;
  name: string;
  category_code: string;
  status: string;
  current_revision: number;
  owner_user_id: string;
  target_consumer?: string;
  product_brief_status?: string;
  latest_revision_id?: string;
  latest_revision_no?: number;
  latest_revision_status?: string;
  latest_change_summary?: string;
}

export interface StyleRevision {
  id: string;
  revision_no: number;
  design_direction: string;
  silhouette: string | null;
  fit_intent: string;
  construction_intent: string;
  material_direction: Record<string, unknown> | string;
  colour_direction: Record<string, unknown> | string;
  target_size_range: Record<string, unknown> | string;
  status: string;
  change_summary: string;
  created_by: string;
  created_at: string;
}

export interface StyleDetail {
  data: StyleRecord;
  revisions: StyleRevision[];
}

export interface ProductBriefInput {
  range_line_id: string;
  initiative_id: string | null;
  target_consumer: string;
  product_need: string;
  intended_use: string;
  target_price: number | null;
  target_cost: number | null;
  target_currency: string;
  quality_tier: string | null;
  delivery_need_by: string | null;
  sustainability_intent: string[];
  change_reason: string;
}

export type ProductBriefVersionInput = Omit<ProductBriefInput, 'range_line_id' | 'initiative_id'>;

export interface ProductBriefDecisionInput {
  decision: 'ACCEPT' | 'REJECT';
  reason: string;
  actions: string[];
}

export interface StyleInput {
  product_brief_id: string;
  style_code: string;
  name: string;
  category_code: string;
  design_direction: string;
  silhouette: string | null;
  fit_intent: string;
  construction_intent: string;
  material_direction: Record<string, unknown>;
  colour_direction: Record<string, unknown>;
  target_size_range: Record<string, unknown>;
  change_summary: string;
}

export type StyleRevisionInput = Omit<StyleInput, 'product_brief_id' | 'style_code' | 'name' | 'category_code'>;

@Injectable({ providedIn: 'root' })
export class ProductDevelopmentService {
  private readonly http = inject(HttpClient);
  private readonly base = '/api/v1';

  listProductBriefs(): Observable<ProductDevelopmentPage<ProductBrief>> {
    return this.http.get<ProductDevelopmentPage<ProductBrief>>(this.base + '/product-briefs');
  }

  getProductBrief(id: string): Observable<ProductBriefDetail> {
    return this.http.get<ProductBriefDetail>(this.base + '/product-briefs/' + encodeURIComponent(id));
  }

  createProductBrief(input: ProductBriefInput): Observable<ProductDevelopmentResource<ProductBrief> & { version: number }> {
    return this.http.post<ProductDevelopmentResource<ProductBrief> & { version: number }>(this.base + '/product-briefs', input);
  }

  createProductBriefVersion(id: string, input: ProductBriefVersionInput): Observable<ProductDevelopmentResource<ProductBrief>> {
    return this.http.post<ProductDevelopmentResource<ProductBrief>>(
      this.base + '/product-briefs/' + encodeURIComponent(id) + '/versions',
      input,
    );
  }

  submitProductBrief(id: string): Observable<ProductDevelopmentResource<ProductBrief>> {
    return this.http.post<ProductDevelopmentResource<ProductBrief>>(
      this.base + '/product-briefs/' + encodeURIComponent(id) + '/submit',
      {},
    );
  }

  decideProductBrief(id: string, input: ProductBriefDecisionInput): Observable<ProductDevelopmentResource<ProductBrief>> {
    return this.http.post<ProductDevelopmentResource<ProductBrief>>(
      this.base + '/product-briefs/' + encodeURIComponent(id) + '/decisions',
      input,
    );
  }

  listStyles(): Observable<ProductDevelopmentPage<StyleRecord>> {
    return this.http.get<ProductDevelopmentPage<StyleRecord>>(this.base + '/styles');
  }

  getStyle(id: string): Observable<StyleDetail> {
    return this.http.get<StyleDetail>(this.base + '/styles/' + encodeURIComponent(id));
  }

  createStyle(input: StyleInput): Observable<ProductDevelopmentResource<StyleRecord> & { revision: StyleRevision }> {
    return this.http.post<ProductDevelopmentResource<StyleRecord> & { revision: StyleRevision }>(this.base + '/styles', input);
  }

  createStyleRevision(id: string, input: StyleRevisionInput): Observable<ProductDevelopmentResource<StyleRecord>> {
    return this.http.post<ProductDevelopmentResource<StyleRecord>>(
      this.base + '/styles/' + encodeURIComponent(id) + '/revisions',
      input,
    );
  }

export interface MeasurementSpecValue {
  id: string;
  size_code: string;
  nominal_value: string;
  tolerance_minus: string;
  tolerance_plus: string;
}

export interface MeasurementSpecPoint {
  id: string;
  point_code: string;
  name: string;
  measurement_method: string;
  unit_code: string;
  is_required: boolean;
  values: MeasurementSpecValue[];
}

export interface MeasurementSpecVersion {
  id: string;
  style_revision_no: number;
  version_no: number;
  size_system_code: string;
  size_sequence: string[];
  base_size_code: string;
  measurement_condition: string;
  status: 'DRAFT' | 'APPROVED' | 'REJECTED';
  change_reason: string;
  created_by: string;
  approved_by: string | null;
  approved_at: string | null;
  points: MeasurementSpecPoint[];
}

export interface MeasurementPointInput {
  point_code: string;
  name: string;
  measurement_method: string;
  unit_code: 'CM' | 'MM' | 'IN';
  is_required: boolean;
  diagram_reference?: string | null;
  values: Array<{
    size_code: string;
    nominal_value: number;
    tolerance_minus: number;
    tolerance_plus: number;
  }>;
}

export interface MeasurementSpecInput {
  size_system_code: string;
  size_sequence: string[];
  base_size_code: string;
  measurement_condition: string;
  change_reason: string;
  points: MeasurementPointInput[];
}

export interface PatternGradeRuleInput {
  point_code: string;
  from_size_code: string;
  to_size_code: string;
  increment: number;
  unit_code: 'CM' | 'MM' | 'IN';
}

export interface PatternPieceInput {
  piece_code: string;
  description: string;
  material_reference?: string | null;
  grain_direction?: string | null;
  seam_allowance?: Record<string, number>;
  piece_count: number;
}

export interface PatternSetRecord {
  id: string;
  style_id: string;
  pattern_code: string;
  name: string;
  status: string;
  current_revision: number;
  latest_revision_id: string | null;
  latest_revision_no: number | null;
  latest_revision_status: string | null;
  measurement_spec_version_id: string | null;
  style_revision_no: number | null;
  latest_revision_created_by: string | null;
  latest_change_summary: string | null;
}

export interface PatternSetInput {
  pattern_code: string;
  name: string;
  measurement_spec_version_id: string;
  pieces: PatternPieceInput[];
  grade_rules: PatternGradeRuleInput[];
  change_summary: string;
}

export type PatternRevisionInput = Omit<PatternSetInput, 'pattern_code' | 'name'>;


  listMeasurementSpecs(styleId: string): Observable<ProductDevelopmentResource<MeasurementSpecVersion[]>> {
    return this.http.get<ProductDevelopmentResource<MeasurementSpecVersion[]>>(
      this.base + '/styles/' + encodeURIComponent(styleId) + '/measurement-specs',
    );
  }

  createMeasurementSpec(styleId: string, input: MeasurementSpecInput): Observable<ProductDevelopmentResource<MeasurementSpecVersion>> {
    return this.http.post<ProductDevelopmentResource<MeasurementSpecVersion>>(
      this.base + '/styles/' + encodeURIComponent(styleId) + '/measurement-specs',
      input,
    );
  }

  approveMeasurementSpec(styleId: string, specVersionId: string): Observable<ProductDevelopmentResource<MeasurementSpecVersion>> {
    return this.http.post<ProductDevelopmentResource<MeasurementSpecVersion>>(
      this.base + '/styles/' + encodeURIComponent(styleId) + '/measurement-specs/' + encodeURIComponent(specVersionId) + '/approve',
      {},
    );
  }

  listPatternSets(styleId: string): Observable<ProductDevelopmentResource<PatternSetRecord[]>> {
    return this.http.get<ProductDevelopmentResource<PatternSetRecord[]>>(
      this.base + '/styles/' + encodeURIComponent(styleId) + '/patterns',
    );
  }

  createPatternSet(styleId: string, input: PatternSetInput): Observable<ProductDevelopmentResource<PatternSetRecord> & { revision: { id: string; revision_no: number } }> {
    return this.http.post<ProductDevelopmentResource<PatternSetRecord> & { revision: { id: string; revision_no: number } }>(
      this.base + '/styles/' + encodeURIComponent(styleId) + '/patterns',
      input,
    );
  }

  createPatternRevision(patternSetId: string, input: PatternRevisionInput): Observable<ProductDevelopmentResource<{ id: string; revision_no: number; status: string }>> {
    return this.http.post<ProductDevelopmentResource<{ id: string; revision_no: number; status: string }>>(
      this.base + '/patterns/' + encodeURIComponent(patternSetId) + '/revisions',
      input,
    );
  }

  approvePatternRevision(patternSetId: string, revisionId: string): Observable<ProductDevelopmentResource<{ id: string; revision_no: number; status: string }> & { pattern_set: PatternSetRecord }> {
    return this.http.post<ProductDevelopmentResource<{ id: string; revision_no: number; status: string }> & { pattern_set: PatternSetRecord }>(
      this.base + '/patterns/' + encodeURIComponent(patternSetId) + '/revisions/' + encodeURIComponent(revisionId) + '/approve',
      {},
    );
  }

}
