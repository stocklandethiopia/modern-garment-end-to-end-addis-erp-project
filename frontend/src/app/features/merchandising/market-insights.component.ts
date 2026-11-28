import { DatePipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, RouterLink } from '@angular/router';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import {
  Initiative,
  InsightSource,
  InsightSourceInput,
  InsightSourceType,
  MarketInsight,
  MarketInsightInput,
  MarketInsightType,
  MerchandisingService,
} from './merchandising.service';

@Component({
  selector: 'app-market-insights',
  standalone: true,
  imports: [DatePipe, ReactiveFormsModule, RouterLink],
  templateUrl: './market-insights.component.html',
  styleUrl: './market-insights.component.css',
})
export class MarketInsightsComponent {
  private readonly api = inject(MerchandisingService);
  private readonly route = inject(ActivatedRoute);
  readonly session = inject(AuthSessionService);

  readonly sources = signal<InsightSource[]>([]);
  readonly initiatives = signal<Initiative[]>([]);
  readonly insights = signal<MarketInsight[]>([]);
  readonly loading = signal(false);
  readonly savingSource = signal(false);
  readonly savingInsight = signal(false);
  readonly showSourceForm = signal(false);
  readonly showInsightForm = signal(false);
  readonly selectedInitiativeId = signal('');
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);

  private sequence = 0;

  readonly sourceTypes: Array<{ value: InsightSourceType; label: string }> = [
    { value: 'STORE_OBSERVATION', label: 'Store observation' },
    { value: 'CUSTOMER_FEEDBACK', label: 'Customer feedback' },
    { value: 'WHOLESALE_REQUEST', label: 'Wholesale buyer request' },
    { value: 'ECOMMERCE_ANALYTICS', label: 'E-commerce analytics' },
    { value: 'SURVEY', label: 'Survey' },
    { value: 'COMPETITOR_RESEARCH', label: 'Competitor research' },
    { value: 'EXTERNAL_BENCHMARK', label: 'External benchmark' },
    { value: 'INTERNAL_SYSTEM', label: 'Internal system' },
    { value: 'SUPPLIER_SIGNAL', label: 'Supplier signal' },
    { value: 'PRODUCTION_CAPABILITY', label: 'Production capability' },
    { value: 'OTHER', label: 'Other' },
  ];

  readonly insightTypes: Array<{ value: MarketInsightType; label: string }> = [
    { value: 'OBSERVED_FACT', label: 'Observed fact' },
    { value: 'SURVEY_RESULT', label: 'Survey result' },
    { value: 'QUALITATIVE_EVIDENCE', label: 'Qualitative evidence' },
    { value: 'EXTERNAL_BENCHMARK', label: 'External benchmark' },
    { value: 'HYPOTHESIS', label: 'Hypothesis / assumption' },
    { value: 'FORECAST_SIGNAL', label: 'Forecast / model signal' },
    { value: 'CUSTOMER_REQUEST', label: 'Customer request' },
    { value: 'FIT_OR_QUALITY_FEEDBACK', label: 'Fit / quality feedback' },
    { value: 'SUPPLY_OR_CAPACITY_SIGNAL', label: 'Supply / capacity signal' },
  ];

  readonly sourceForm = new FormGroup({
    source_type: new FormControl<InsightSourceType>('STORE_OBSERVATION', { nonNullable: true }),
    source_name: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(200)] }),
    source_reference: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(1000)] }),
    capture_method: new FormControl('MANUAL_RESEARCH', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    geography: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(160)] }),
    reliability_score: new FormControl('', { nonNullable: true }),
    permitted_use_basis: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(3), Validators.maxLength(200)] }),
    limitations: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(5000)] }),
  });

  readonly insightForm = new FormGroup({
    source_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    initiative_id: new FormControl('', { nonNullable: true }),
    insight_type: new FormControl<MarketInsightType>('OBSERVED_FACT', { nonNullable: true }),
    title: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(200)] }),
    summary: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(10000)] }),
    observed_at: new FormControl(this.toLocalDateTime(new Date()), { nonNullable: true, validators: [Validators.required] }),
    geography: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(160)] }),
    target_segment: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(160)] }),
    channel_code: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(60)] }),
    category_code: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(80)] }),
    metric_name: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(120)] }),
    metric_value_numeric: new FormControl('', { nonNullable: true }),
    metric_value_text: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(300)] }),
    metric_unit: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(40)] }),
    sample_size: new FormControl('', { nonNullable: true }),
    denominator: new FormControl('', { nonNullable: true }),
    confidence: new FormControl('', { nonNullable: true }),
    limitations: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(5000)] }),
    privacy_basis: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(3), Validators.maxLength(200)] }),
  });

  private readonly tenantReloadEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    const requestId = ++this.sequence;
    this.sources.set([]);
    this.initiatives.set([]);
    this.insights.set([]);
    this.loading.set(false);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    if (tenantId) {
      this.loadAll(tenantId, requestId);
    }
  });

  constructor() {
    this.route.queryParamMap.subscribe((params) => {
      const initiativeId = params.get('initiative_id') ?? '';
      this.selectedInitiativeId.set(initiativeId);
      this.insightForm.patchValue({ initiative_id: initiativeId });
      if (initiativeId) {
        this.loadInsights(initiativeId);
      }
    });
  }

  private loadAll(tenantId: string, requestId: number): void {
    this.loading.set(true);
    this.api.listInsightSources().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.sources.set(response.data);
        this.loading.set(false);
      },
      error: () => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.sources.set([]);
        this.loading.set(false);
        this.errorMessage.set('Insight sources could not be loaded.');
      },
    });

    this.api.listInitiatives().subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId)) {
          this.initiatives.set(response.data);
        }
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId)) {
          this.initiatives.set([]);
        }
      },
    });

    this.loadInsights(this.selectedInitiativeId() || undefined, tenantId, requestId);
  }

  private loadInsights(initiativeId?: string, tenantId?: string, requestId?: number): void {
    const currentTenant = tenantId ?? this.session.activeTenant()?.id;
    const currentSequence = requestId ?? this.sequence;
    if (!currentTenant) return;

    this.api.listMarketInsights(initiativeId).subscribe({
      next: (response) => {
        if (this.isCurrent(currentTenant, currentSequence)) {
          this.insights.set(response.data);
        }
      },
      error: () => {
        if (this.isCurrent(currentTenant, currentSequence)) {
          this.insights.set([]);
          this.errorMessage.set('Market insights could not be loaded.');
        }
      },
    });
  }

  createSource(): void {
    if (this.sourceForm.invalid || this.savingSource()) {
      this.sourceForm.markAllAsTouched();
      return;
    }
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;

    const value = this.sourceForm.getRawValue();
    const reliability = value.reliability_score.trim() === '' ? null : Number(value.reliability_score);
    if (reliability !== null && (!Number.isFinite(reliability) || reliability < 0 || reliability > 1)) {
      this.errorMessage.set('Reliability score must be between 0 and 1.');
      return;
    }

    const input: InsightSourceInput = {
      source_type: value.source_type,
      source_name: value.source_name.trim(),
      source_reference: value.source_reference.trim() || null,
      capture_method: value.capture_method.trim(),
      geography: value.geography.trim() || null,
      reliability_score: reliability,
      permitted_use_basis: value.permitted_use_basis.trim(),
      limitations: value.limitations.trim() || null,
    };

    this.savingSource.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);
    this.api.createInsightSource(input).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.sources.update((items) => [data, ...items]);
        this.insightForm.patchValue({ source_id: data.id });
        this.savingSource.set(false);
        this.showSourceForm.set(false);
        this.sourceForm.reset({
          source_type: 'STORE_OBSERVATION',
          source_name: '',
          source_reference: '',
          capture_method: 'MANUAL_RESEARCH',
          geography: '',
          reliability_score: '',
          permitted_use_basis: '',
          limitations: '',
        });
        this.successMessage.set('Source provenance recorded. Add an observation or finding linked to this source.');
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.savingSource.set(false);
        this.errorMessage.set(error.status === 422
          ? 'Check the required source provenance and permitted-use fields.'
          : 'The insight source could not be saved.');
      },
    });
  }

  createInsight(): void {
    if (this.insightForm.invalid || this.savingInsight()) {
      this.insightForm.markAllAsTouched();
      return;
    }
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;

    const value = this.insightForm.getRawValue();
    const sampleSize = this.optionalInteger(value.sample_size);
    const denominator = this.optionalInteger(value.denominator);
    const metric = this.optionalNumber(value.metric_value_numeric);
    const confidence = this.optionalNumber(value.confidence);

    if (sampleSize !== null && denominator !== null && denominator < sampleSize) {
      this.errorMessage.set('The denominator cannot be smaller than the sample size.');
      return;
    }
    if (confidence !== null && (confidence < 0 || confidence > 1)) {
      this.errorMessage.set('Confidence must be between 0 and 1.');
      return;
    }

    const input: MarketInsightInput = {
      source_id: value.source_id,
      initiative_id: value.initiative_id || null,
      insight_type: value.insight_type,
      title: value.title.trim(),
      summary: value.summary.trim(),
      observed_at: new Date(value.observed_at).toISOString(),
      geography: value.geography.trim() || null,
      target_segment: value.target_segment.trim() || null,
      channel_code: value.channel_code.trim() || null,
      category_code: value.category_code.trim() || null,
      metric_name: value.metric_name.trim() || null,
      metric_value_numeric: metric,
      metric_value_text: value.metric_value_text.trim() || null,
      metric_unit: value.metric_unit.trim() || null,
      sample_size: sampleSize,
      denominator,
      confidence,
      limitations: value.limitations.trim() || null,
      privacy_basis: value.privacy_basis.trim(),
    };

    this.savingInsight.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.createMarketInsight(input).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.insights.update((items) => [data, ...items]);
        this.savingInsight.set(false);
        this.showInsightForm.set(false);
        this.insightForm.patchValue({
          title: '',
          summary: '',
          observed_at: this.toLocalDateTime(new Date()),
          geography: '',
          target_segment: '',
          channel_code: '',
          category_code: '',
          metric_name: '',
          metric_value_numeric: '',
          metric_value_text: '',
          metric_unit: '',
          sample_size: '',
          denominator: '',
          confidence: '',
          limitations: '',
        });
        this.successMessage.set('Market insight captured with source, timestamp, limitations and permitted-use basis.');
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.savingInsight.set(false);
        this.errorMessage.set(error.status === 422
          ? error.error?.message || 'Check the insight source, sample and evidence fields.'
          : 'The market insight could not be saved.');
      },
    });
  }

  onInitiativeFilter(value: string): void {
    this.selectedInitiativeId.set(value);
    this.insightForm.patchValue({ initiative_id: value });
    this.loadInsights(value || undefined);
  }

  private isCurrent(tenantId: string, requestId: number): boolean {
    return this.sequence === requestId && this.session.activeTenant()?.id === tenantId;
  }

  private optionalNumber(value: string): number | null {
    if (value.trim() === '') return null;
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  private optionalInteger(value: string): number | null {
    if (value.trim() === '') return null;
    const parsed = Number(value);
    return Number.isSafeInteger(parsed) && parsed >= 0 ? parsed : null;
  }

  private toLocalDateTime(date: Date): string {
    const offset = date.getTimezoneOffset() * 60000;
    return new Date(date.getTime() - offset).toISOString().slice(0, 16);
  }
}
