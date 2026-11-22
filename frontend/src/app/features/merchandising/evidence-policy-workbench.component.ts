import { DatePipe, DecimalPipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormArray, FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import {
  EvidenceInitiativeType,
  EvidenceInsightType,
  EvidencePolicy,
  EvidencePolicyInput,
  EvidencePolicyService,
} from './evidence-policy.service';

@Component({
  selector: 'app-evidence-policy-workbench',
  standalone: true,
  imports: [DatePipe, ReactiveFormsModule],
  templateUrl: './evidence-policy-workbench.component.html',
  styleUrl: './evidence-policy-workbench.component.css',
})
export class EvidencePolicyWorkbenchComponent {
  private readonly api = inject(EvidencePolicyService);
  readonly session = inject(AuthSessionService);

  readonly policies = signal<EvidencePolicy[]>([]);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  readonly showPolicyForm = signal(false);
  readonly reviewingPolicy = signal<EvidencePolicy | null>(null);
  readonly reviewDecision = signal<'APPROVE' | 'REJECT'>('APPROVE');
  private nextRuleNumber = 2;

  readonly initiativeTypes: Array<{ value: EvidenceInitiativeType; label: string }> = [
    { value: 'NEW_BRAND', label: 'New brand' },
    { value: 'NEW_COLLECTION', label: 'New collection' },
    { value: 'CATEGORY_EXPANSION', label: 'Category expansion' },
    { value: 'MARKET_ENTRY', label: 'Market entry' },
    { value: 'PRODUCT_INNOVATION', label: 'Product innovation' },
    { value: 'CUSTOMER_OPPORTUNITY', label: 'Customer opportunity' },
    { value: 'CAPABILITY_LED', label: 'Capability-led initiative' },
    { value: 'REPLENISHMENT', label: 'Replenishment' },
    { value: 'SUSTAINABILITY_IMPROVEMENT', label: 'Sustainability improvement' },
    { value: 'QUALITY_IMPROVEMENT', label: 'Quality improvement' },
  ];

  readonly insightTypes: Array<{ value: EvidenceInsightType; label: string }> = [
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

  readonly ruleForms = new FormArray<FormGroup>([this.createRuleForm()]);

  readonly policyForm = new FormGroup({
    code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    name: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(3), Validators.maxLength(180)] }),
    initiative_type: new FormControl<EvidenceInitiativeType>('NEW_COLLECTION', { nonNullable: true, validators: [Validators.required] }),
    stage: new FormControl<'RESEARCH' | 'ASSESSMENT'>('ASSESSMENT', { nonNullable: true, validators: [Validators.required] }),
    target_market: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(160)] }),
    effective_from: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    effective_to: new FormControl('', { nonNullable: true }),
    change_reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(5000)] }),
    rules: this.ruleForms,
  });

  readonly decisionForm = new FormGroup({
    reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(5000)] }),
  });

  constructor() {
    this.load();
  }

  canAdminister(): boolean {
    return ['OWNER', 'ADMIN'].includes(this.session.activeTenant()?.membership_role ?? '');
  }

  canSubmit(policy: EvidencePolicy): boolean {
    const userId = this.session.user()?.id;
    return policy.status === 'DRAFT' && (this.canAdminister() || policy.created_by === userId);
  }

  canReview(policy: EvidencePolicy): boolean {
    const userId = this.session.user()?.id;
    return this.canAdminister() && policy.status === 'PENDING_APPROVAL' && policy.created_by !== userId;
  }

  load(): void {
    this.loading.set(true);
    this.errorMessage.set(null);
    this.api.list().subscribe({
      next: (response) => {
        this.policies.set(response.data.data);
        this.loading.set(false);
      },
      error: (error: HttpErrorResponse) => {
        this.errorMessage.set(error.error?.message ?? 'Evidence policies could not be loaded.');
        this.loading.set(false);
      },
    });
  }

  addRule(): void {
    if (this.ruleForms.length >= 20) {
      return;
    }
    this.ruleForms.push(this.createRuleForm(`EVIDENCE_REQUIREMENT_${this.nextRuleNumber++}`));
  }

  removeRule(index: number): void {
    if (this.ruleForms.length > 1) {
      this.ruleForms.removeAt(index);
    }
  }

  createPolicy(): void {
    if (!this.canAdminister()) {
      this.errorMessage.set('Only tenant owners and administrators may configure evidence policies.');
      return;
    }
    if (this.policyForm.invalid || this.ruleForms.length === 0) {
      this.policyForm.markAllAsTouched();
      return;
    }

    const raw = this.policyForm.getRawValue();
    const input: EvidencePolicyInput = {
      code: raw.code.trim().toUpperCase(),
      name: raw.name.trim(),
      initiative_type: raw.initiative_type,
      stage: raw.stage,
      target_market: raw.target_market.trim() || null,
      effective_from: raw.effective_from,
      effective_to: raw.effective_to || null,
      change_reason: raw.change_reason.trim(),
      rules: raw.rules.map((rule) => ({
        requirement_code: String(rule['requirement_code']).trim().toUpperCase(),
        insight_type: rule['insight_type'] as EvidenceInsightType,
        minimum_records: Number(rule['minimum_records']),
        minimum_distinct_sources: Number(rule['minimum_distinct_sources']),
        minimum_confidence: this.optionalNumber(rule['minimum_confidence']),
        minimum_source_reliability: this.optionalNumber(rule['minimum_source_reliability']),
        max_age_days: this.optionalNumber(rule['max_age_days']),
        require_geography: Boolean(rule['require_geography']),
        require_target_segment: Boolean(rule['require_target_segment']),
        require_sample_denominator: Boolean(rule['require_sample_denominator']),
        is_mandatory: Boolean(rule['is_mandatory']),
      })),
    };

    this.saving.set(true);
    this.api.create(input).subscribe({
      next: (response) => {
        this.successMessage.set(`Draft evidence policy ${response.data.policy.code} v${response.data.policy.version_no} created.`);
        this.showPolicyForm.set(false);
        this.policyForm.reset({
          code: '',
          name: '',
          initiative_type: 'NEW_COLLECTION',
          stage: 'ASSESSMENT',
          target_market: '',
          effective_from: '',
          effective_to: '',
          change_reason: '',
          rules: [],
        });
        this.ruleForms.clear();
        this.nextRuleNumber = 2;
        this.ruleForms.push(this.createRuleForm());
        this.saving.set(false);
        this.load();
      },
      error: (error: HttpErrorResponse) => {
        this.errorMessage.set(error.error?.message ?? 'The evidence policy could not be saved.');
        this.saving.set(false);
      },
    });
  }

  submitPolicy(policy: EvidencePolicy): void {
    this.saving.set(true);
    this.api.submit(policy.id).subscribe({
      next: () => {
        this.successMessage.set(`${policy.code} v${policy.version_no} submitted for independent approval.`);
        this.saving.set(false);
        this.load();
      },
      error: (error: HttpErrorResponse) => {
        this.errorMessage.set(error.error?.message ?? 'The policy could not be submitted.');
        this.saving.set(false);
      },
    });
  }

  beginReview(policy: EvidencePolicy, decision: 'APPROVE' | 'REJECT'): void {
    this.reviewingPolicy.set(policy);
    this.reviewDecision.set(decision);
    this.decisionForm.reset({ reason: '' });
  }

  cancelReview(): void {
    this.reviewingPolicy.set(null);
    this.decisionForm.reset({ reason: '' });
  }

  decidePolicy(): void {
    const policy = this.reviewingPolicy();
    if (!policy || this.decisionForm.invalid) {
      this.decisionForm.markAllAsTouched();
      return;
    }

    this.saving.set(true);
    this.api.decide(policy.id, this.reviewDecision(), this.decisionForm.controls.reason.value.trim()).subscribe({
      next: () => {
        this.successMessage.set(`${policy.code} v${policy.version_no} ${this.reviewDecision() === 'APPROVE' ? 'approved' : 'rejected'}.`);
        this.reviewingPolicy.set(null);
        this.saving.set(false);
        this.load();
      },
      error: (error: HttpErrorResponse) => {
        this.errorMessage.set(error.error?.message ?? 'The evidence policy decision could not be recorded.');
        this.saving.set(false);
      },
    });
  }

  private createRuleForm(requirementCode = 'PRIMARY_MARKET_EVIDENCE'): FormGroup {
    return new FormGroup({
      requirement_code: new FormControl(requirementCode, { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
      insight_type: new FormControl<EvidenceInsightType>('OBSERVED_FACT', { nonNullable: true, validators: [Validators.required] }),
      minimum_records: new FormControl(1, { nonNullable: true, validators: [Validators.required, Validators.min(1), Validators.max(500)] }),
      minimum_distinct_sources: new FormControl(1, { nonNullable: true, validators: [Validators.required, Validators.min(1), Validators.max(500)] }),
      minimum_confidence: new FormControl<number | null>(null, [Validators.min(0), Validators.max(1)]),
      minimum_source_reliability: new FormControl<number | null>(null, [Validators.min(0), Validators.max(1)]),
      max_age_days: new FormControl<number | null>(null, [Validators.min(1), Validators.max(3650)]),
      require_geography: new FormControl(false, { nonNullable: true }),
      require_target_segment: new FormControl(false, { nonNullable: true }),
      require_sample_denominator: new FormControl(false, { nonNullable: true }),
      is_mandatory: new FormControl(true, { nonNullable: true }),
    });
  }

  private optionalNumber(value: unknown): number | null {
    return value === null || value === undefined || value === '' ? null : Number(value);
  }
}
