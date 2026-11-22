import { DatePipe, DecimalPipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, computed, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { EvidencePolicyCoverage, EvidencePolicyService } from './evidence-policy.service';
import { LegalEntity, LegalEntityService } from '../enterprise/legal-entity.service';
import {
  Initiative,
  InitiativeDecisionInput,
  InitiativeDetailResponse,
  InitiativeType,
  MerchandisingService,
} from './merchandising.service';

@Component({
  selector: 'app-initiatives',
  standalone: true,
  imports: [DatePipe, DecimalPipe, ReactiveFormsModule, RouterLink],
  templateUrl: './initiatives.component.html',
  styleUrl: './initiatives.component.css',
})
export class InitiativesComponent {
  private readonly api = inject(MerchandisingService);
  private readonly evidencePolicyApi = inject(EvidencePolicyService);
  private readonly legalEntityApi = inject(LegalEntityService);
  readonly session = inject(AuthSessionService);

  readonly initiatives = signal<Initiative[]>([]);
  readonly legalEntities = signal<LegalEntity[]>([]);
  readonly selectedId = signal<string | null>(null);
  readonly detail = signal<InitiativeDetailResponse | null>(null);
  readonly evidenceCoverage = signal<EvidencePolicyCoverage | null>(null);
  readonly loadingEvidenceCoverage = signal(false);
  readonly evidenceCoverageError = signal<string | null>(null);

  readonly loading = signal(false);
  readonly loadingDetail = signal(false);
  readonly saving = signal(false);
  readonly showCreateForm = signal(false);
  readonly showDecisionForm = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);

  private requestSequence = 0;
  private detailSequence = 0;
  private evidenceCoverageSequence = 0;

  readonly canDecide = computed(() => {
    const role = this.session.activeTenant()?.membership_role;
    const creatorId = this.detail()?.data.owner_user_id;
    const currentUserId = this.session.user()?.id;
    return (role === 'OWNER' || role === 'ADMIN')
      && !!creatorId
      && !!currentUserId
      && creatorId !== currentUserId;
  });

  readonly selectedStatus = computed(() => this.detail()?.data.status ?? null);

  readonly decisionOptions = computed(() => {
    switch (this.selectedStatus()) {
      case 'SCREENING':
        return [
          { value: 'APPROVE_RESEARCH', label: 'Approve research' },
          { value: 'PARK', label: 'Park for later' },
          { value: 'REJECT', label: 'Reject initiative' },
        ];
      case 'ASSESSMENT':
        return [
          { value: 'APPROVE', label: 'Approve initiative' },
          { value: 'APPROVE_CONDITIONAL', label: 'Approve with conditions' },
          { value: 'RETURN_TO_RESEARCH', label: 'Return to research' },
          { value: 'PARK', label: 'Park for later' },
          { value: 'REJECT', label: 'Reject initiative' },
        ];
      default:
        return [];
    }
  });

  readonly initiativeTypes: Array<{ value: InitiativeType; label: string }> = [
    { value: 'NEW_BRAND', label: 'New brand' },
    { value: 'NEW_COLLECTION', label: 'New collection' },
    { value: 'CATEGORY_EXPANSION', label: 'Category expansion' },
    { value: 'MARKET_ENTRY', label: 'Market entry' },
    { value: 'PRODUCT_INNOVATION', label: 'Product innovation' },
    { value: 'CUSTOMER_OPPORTUNITY', label: 'Customer / buyer opportunity' },
    { value: 'CAPABILITY_LED', label: 'Capability-led opportunity' },
    { value: 'REPLENISHMENT', label: 'Replenishment opportunity' },
    { value: 'SUSTAINABILITY_IMPROVEMENT', label: 'Sustainability improvement' },
    { value: 'QUALITY_IMPROVEMENT', label: 'Quality improvement' },
  ];

  readonly createForm = new FormGroup({
    code: new FormControl('', {
      nonNullable: true,
      validators: [
        Validators.required,
        Validators.maxLength(80),
        Validators.pattern(/^[A-Z0-9][A-Z0-9_-]{1,79}$/),
      ],
    }),
    initiative_type: new FormControl<InitiativeType>('NEW_COLLECTION', {
      nonNullable: true,
      validators: [Validators.required],
    }),
    title: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(200)],
    }),
    problem_statement: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.minLength(10), Validators.maxLength(10000)],
    }),
    objective: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.minLength(5), Validators.maxLength(10000)],
    }),
    target_market: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(160)],
    }),
    target_consumer: new FormControl('', {
      nonNullable: true,
      validators: [Validators.maxLength(160)],
    }),
    expected_benefit: new FormControl('', {
      nonNullable: true,
      validators: [Validators.maxLength(5000)],
    }),
    sponsor_entity_id: new FormControl('', { nonNullable: true }),
  });

  readonly decisionForm = new FormGroup({
    decision: new FormControl<InitiativeDecisionInput['decision']>('APPROVE_RESEARCH', {
      nonNullable: true,
      validators: [Validators.required],
    }),
    reason: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.minLength(10), Validators.maxLength(5000)],
    }),
    actions: new FormControl('', {
      nonNullable: true,
      validators: [Validators.maxLength(5000)],
    }),
    review_date: new FormControl('', { nonNullable: true }),
  });

  private readonly tenantReloadEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    const requestId = ++this.requestSequence;
    this.detailSequence += 1;

    this.initiatives.set([]);
    this.legalEntities.set([]);
    this.selectedId.set(null);
    this.detail.set(null);
    this.evidenceCoverage.set(null);
    this.loadingEvidenceCoverage.set(false);
    this.evidenceCoverageError.set(null);
    this.evidenceCoverageSequence += 1;
    this.errorMessage.set(null);
    this.successMessage.set(null);
    this.loading.set(false);
    this.loadingDetail.set(false);
    this.saving.set(false);

    if (tenantId) {
      this.loadInitiatives(tenantId, requestId);
      this.loadLegalEntities(tenantId, requestId);
    }
  });

  loadInitiatives(expectedTenantId?: string, expectedSequence?: number): void {
    const tenantId = expectedTenantId ?? this.session.activeTenant()?.id;
    if (!tenantId) {
      this.initiatives.set([]);
      return;
    }

    const requestId = expectedSequence ?? ++this.requestSequence;
    this.loading.set(true);
    this.errorMessage.set(null);

    this.api.listInitiatives().subscribe({
      next: (response) => {
        if (requestId !== this.requestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.initiatives.set(response.data);
        this.loading.set(false);

        const selected = this.selectedId();
        if (selected && response.data.some((item) => item.id === selected)) {
          this.loadDetail(selected, tenantId);
        } else if (selected) {
          this.selectedId.set(null);
          this.detail.set(null);
        }
      },
      error: () => {
        if (requestId !== this.requestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.initiatives.set([]);
        this.loading.set(false);
        this.errorMessage.set('Initiatives could not be loaded for the selected tenant.');
      },
    });
  }

  private loadLegalEntities(tenantId: string, requestId: number): void {
    this.legalEntityApi.list().subscribe({
      next: (response) => {
        if (requestId !== this.requestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.legalEntities.set(response.data);
      },
      error: () => {
        if (requestId !== this.requestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.legalEntities.set([]);
      },
    });
  }

  selectInitiative(initiative: Initiative): void {
    this.selectedId.set(initiative.id);
    this.loadDetail(initiative.id);
    this.showDecisionForm.set(false);
  }

  private loadDetail(initiativeId: string, expectedTenantId?: string): void {
    const tenantId = expectedTenantId ?? this.session.activeTenant()?.id;
    if (!tenantId) return;

    const requestId = ++this.detailSequence;
    this.loadingDetail.set(true);
    this.evidenceCoverage.set(null);
    this.loadingEvidenceCoverage.set(false);
    this.evidenceCoverageError.set(null);
    this.evidenceCoverageSequence += 1;
    this.errorMessage.set(null);

    this.api.getInitiative(initiativeId).subscribe({
      next: (response) => {
        if (
          requestId !== this.detailSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.selectedId() !== initiativeId
        ) return;
        this.detail.set(response);
        this.loadingDetail.set(false);
        if (response.data.status === 'SCREENING') {
          this.loadEvidenceCoverage(initiativeId, tenantId, 'RESEARCH');
        } else if (response.data.status === 'RESEARCH' || response.data.status === 'ASSESSMENT') {
          this.loadEvidenceCoverage(initiativeId, tenantId, 'ASSESSMENT');
        }
        this.decisionForm.patchValue({
          decision: response.data.status === 'SCREENING' ? 'APPROVE_RESEARCH' : 'APPROVE',
        });
      },
      error: (error: HttpErrorResponse) => {
        if (
          requestId !== this.detailSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.selectedId() !== initiativeId
        ) return;
        this.detail.set(null);
        this.loadingDetail.set(false);
        this.errorMessage.set(error.status === 404
          ? 'The selected initiative is no longer available in this tenant.'
          : 'Initiative detail could not be loaded.');
      },
    });
  }

  private loadEvidenceCoverage(
    initiativeId: string,
    tenantId: string,
    stage: 'RESEARCH' | 'ASSESSMENT',
  ): void {
    const sequence = ++this.evidenceCoverageSequence;
    this.loadingEvidenceCoverage.set(true);
    this.evidenceCoverageError.set(null);

    this.evidencePolicyApi.coverage(initiativeId, stage).subscribe({
      next: (response) => {
        if (
          sequence !== this.evidenceCoverageSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.selectedId() !== initiativeId
        ) return;
        this.evidenceCoverage.set(response.data);
        this.loadingEvidenceCoverage.set(false);
      },
      error: () => {
        if (
          sequence !== this.evidenceCoverageSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.selectedId() !== initiativeId
        ) return;
        this.evidenceCoverage.set(null);
        this.loadingEvidenceCoverage.set(false);
        this.evidenceCoverageError.set('Evidence coverage could not be loaded for this initiative.');
      },
    });
  }

  create(): void {
    if (this.createForm.invalid || this.saving()) {
      this.createForm.markAllAsTouched();
      return;
    }

    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;

    const value = this.createForm.getRawValue();
    this.saving.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.createInitiative({
      code: value.code.trim().toUpperCase(),
      initiative_type: value.initiative_type,
      title: value.title.trim(),
      problem_statement: value.problem_statement.trim(),
      objective: value.objective.trim(),
      target_market: value.target_market.trim(),
      target_consumer: value.target_consumer.trim() || null,
      expected_benefit: value.expected_benefit.trim() || null,
      sponsor_entity_id: value.sponsor_entity_id || null,
    }).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.initiatives.update((current) => [data, ...current]);
        this.saving.set(false);
        this.showCreateForm.set(false);
        this.createForm.reset({
          code: '',
          initiative_type: 'NEW_COLLECTION',
          title: '',
          problem_statement: '',
          objective: '',
          target_market: '',
          target_consumer: '',
          expected_benefit: '',
          sponsor_entity_id: '',
        });
        this.successMessage.set('Initiative captured as a draft. It does not create a product, purchase order or production order.');
        this.selectInitiative(data);
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.saving.set(false);
        this.errorMessage.set(error.status === 422
          ? 'Check the required fields and confirm the initiative code is unique in this tenant.'
          : 'The initiative could not be saved.');
      },
    });
  }

  submitForScreening(): void {
    const selected = this.detail()?.data;
    if (!selected || selected.status !== 'DRAFT' || this.saving()) return;
    this.runCommand(
      () => this.api.submitInitiative(selected.id),
      'Initiative submitted for screening.',
    );
  }

  advanceToAssessment(): void {
    const selected = this.detail()?.data;
    if (!selected || selected.status !== 'RESEARCH' || this.saving()) return;
    this.runCommand(
      () => this.api.advanceToAssessment(selected.id),
      'Research evidence accepted. The initiative is now in assessment.',
    );
  }

  recordDecision(): void {
    const selected = this.detail()?.data;
    if (!selected || !this.canDecide() || this.decisionForm.invalid || this.saving()) {
      this.decisionForm.markAllAsTouched();
      return;
    }

    const value = this.decisionForm.getRawValue();
    const payload: InitiativeDecisionInput = {
      decision: value.decision,
      reason: value.reason.trim(),
      actions: value.actions.split('\n').map((item) => item.trim()).filter(Boolean),
      review_date: value.review_date || null,
    };

    this.runCommand(
      () => this.api.decideInitiative(selected.id, payload),
      'Decision recorded with approver, reason and initiative version evidence.',
    );
  }

  private runCommand(
    request: () => ReturnType<MerchandisingService['submitInitiative']>,
    successMessage: string,
  ): void {
    const selectedId = this.selectedId();
    const tenantId = this.session.activeTenant()?.id;
    if (!selectedId || !tenantId) return;

    this.saving.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    request().subscribe({
      next: () => {
        if (this.session.activeTenant()?.id !== tenantId || this.selectedId() !== selectedId) return;
        this.saving.set(false);
        this.successMessage.set(successMessage);
        this.loadInitiatives();
        this.loadDetail(selectedId, tenantId);
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId || this.selectedId() !== selectedId) return;
        this.saving.set(false);
        this.errorMessage.set(error.status === 403
          ? 'This decision requires tenant owner/admin approval.'
          : error.status === 422
            ? error.error?.message || 'The initiative cannot take this action in its current state.'
            : 'The initiative action could not be completed.');
      },
    });
  }
}
