import { DatePipe, DecimalPipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin, Observable } from 'rxjs';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import {
  FitDecisionInput,
  FitObservationInput,
  FitSessionInput,
  FitSessionRecord,
  MeasurementSpecVersion,
  PatternSetRecord,
  ProductDevelopmentService,
  SampleRequestDetail,
  SampleRequestInput,
  SampleRequestRecord,
  SampleRoundRecord,
  StyleRecord,
} from './product-development.service';

@Component({
  selector: 'app-sample-fit',
  standalone: true,
  imports: [DatePipe, DecimalPipe, ReactiveFormsModule],
  templateUrl: './sample-fit.component.html',
  styleUrl: './sample-fit.component.css',
})
export class SampleFitComponent {
  private readonly api = inject(ProductDevelopmentService);
  readonly session = inject(AuthSessionService);

  readonly styles = signal<StyleRecord[]>([]);
  readonly selectedStyle = signal<StyleRecord | null>(null);
  readonly patternSets = signal<PatternSetRecord[]>([]);
  readonly measurementSpecs = signal<MeasurementSpecVersion[]>([]);
  readonly sampleRequests = signal<SampleRequestRecord[]>([]);
  readonly selectedSampleRequest = signal<SampleRequestDetail | null>(null);
  readonly selectedFitSessionId = signal<string | null>(null);
  readonly activeFitRound = signal<SampleRoundRecord | null>(null);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);

  private workspaceSequence = 0;
  private detailSequence = 0;

  readonly sampleForm = new FormGroup({
    pattern_set_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    sample_type: new FormControl('FIT', { nonNullable: true, validators: [Validators.required] }),
    purpose: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(4000)] }),
    requested_quantity: new FormControl('1', { nonNullable: true, validators: [Validators.required, Validators.min(1), Validators.max(200)] }),
    requested_size_codes: new FormControl('M', { nonNullable: true, validators: [Validators.required] }),
    requested_colour_codes: new FormControl('', { nonNullable: true }),
    required_by: new FormControl('', { nonNullable: true }),
  });

  readonly roundForm = new FormGroup({
    round_reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
    planned_quantity: new FormControl('1', { nonNullable: true, validators: [Validators.required, Validators.min(1), Validators.max(200)] }),
  });

  readonly completionForm = new FormGroup({
    actual_quantity: new FormControl('1', { nonNullable: true, validators: [Validators.required, Validators.min(0), Validators.max(200)] }),
    completion_summary: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(4000)] }),
  });

  readonly fitSessionForm = new FormGroup({
    fit_size_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(32)] }),
    fit_model_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.pattern(/^[A-Za-z0-9][A-Za-z0-9_-]{1,79}$/)] }),
    posture_code: new FormControl<'STANDING' | 'SITTING' | 'MOVING' | 'OTHER'>('STANDING', { nonNullable: true, validators: [Validators.required] }),
    condition_notes: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(2000)] }),
    session_date: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
  });

  readonly observationsForm = new FormGroup({
    observations_json: new FormControl('[]', { nonNullable: true, validators: [Validators.required] }),
  });

  readonly decisionForm = new FormGroup({
    decision: new FormControl<'APPROVE' | 'APPROVE_WITH_COMMENTS' | 'CORRECTION_REQUIRED' | 'REJECT'>('APPROVE', { nonNullable: true, validators: [Validators.required] }),
    reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(2000)] }),
    actions: new FormControl('', { nonNullable: true }),
  });

  private readonly tenantEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    const sequence = ++this.workspaceSequence;
    this.clearWorkspace();
    if (tenantId) this.loadStyles(tenantId, sequence);
  });

  loadStyles(tenantId = this.session.activeTenant()?.id, sequence = this.workspaceSequence): void {
    if (!tenantId) return;
    this.loading.set(true);
    this.api.listStyles().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, sequence)) return;
        this.styles.set(response.data.data);
        this.loading.set(false);
      },
      error: () => {
        if (!this.isCurrent(tenantId, sequence)) return;
        this.loading.set(false);
        this.errorMessage.set('Styles could not be loaded for the active tenant.');
      },
    });
  }

  selectStyle(styleId: string): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId || !styleId) return;
    const sequence = this.workspaceSequence;
    const detailSequence = ++this.detailSequence;
    this.clearMessages();
    this.selectedStyle.set(null);
    this.selectedSampleRequest.set(null);
    this.selectedFitSessionId.set(null);
    this.activeFitRound.set(null);
    this.loading.set(true);

    forkJoin({
      style: this.api.getStyle(styleId),
      patterns: this.api.listPatternSets(styleId),
      specs: this.api.listMeasurementSpecs(styleId),
      requests: this.api.listSampleRequests(styleId),
    }).subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, sequence) || detailSequence !== this.detailSequence) return;
        this.selectedStyle.set(response.style.data);
        this.patternSets.set(response.patterns.data);
        this.measurementSpecs.set(response.specs.data);
        this.sampleRequests.set(response.requests.data.data);
        this.loading.set(false);
      },
      error: () => {
        if (!this.isCurrent(tenantId, sequence) || detailSequence !== this.detailSequence) return;
        this.loading.set(false);
        this.errorMessage.set('The selected style sample and fit records could not be loaded.');
      },
    });
  }

  eligiblePatternSets(): PatternSetRecord[] {
    const style = this.selectedStyle();
    if (!style) return [];
    return this.patternSets().filter((pattern) =>
      pattern.status === 'APPROVED'
      && pattern.latest_revision_status === 'APPROVED'
      && !!pattern.latest_revision_id
      && pattern.style_revision_no === style.current_revision,
    );
  }

  availableFitSizes(): string[] {
    const request = this.selectedSampleRequest()?.data;
    return request ? this.toArray(request.requested_size_codes) : [];
  }

  selectSampleRequest(request: SampleRequestRecord): void {
    this.selectSampleRequestById(request.id);
  }

  selectSampleRequestById(id: string): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    const sequence = this.workspaceSequence;
    const detailSequence = ++this.detailSequence;
    this.clearMessages();
    this.selectedSampleRequest.set(null);
    this.selectedFitSessionId.set(null);
    this.activeFitRound.set(null);
    this.loading.set(true);

    this.api.getSampleRequest(id).subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, sequence) || detailSequence !== this.detailSequence) return;
        this.selectedSampleRequest.set(response);
        this.loading.set(false);
      },
      error: () => {
        if (!this.isCurrent(tenantId, sequence) || detailSequence !== this.detailSequence) return;
        this.loading.set(false);
        this.errorMessage.set('The selected sample request could not be opened.');
      },
    });
  }

  createSampleRequest(): void {
    const style = this.selectedStyle();
    if (!style || !this.canManageStyle() || !this.valid(this.sampleForm)) return;
    const value = this.sampleForm.getRawValue();
    const pattern = this.eligiblePatternSets().find((item) => item.id === value.pattern_set_id);
    if (!pattern?.latest_revision_id) {
      this.errorMessage.set('Select a current approved pattern revision before requesting a sample.');
      return;
    }

    const payload: SampleRequestInput = {
      pattern_set_id: pattern.id,
      pattern_revision_id: pattern.latest_revision_id,
      sample_type: value.sample_type,
      purpose: value.purpose.trim(),
      requested_quantity: Number(value.requested_quantity),
      requested_size_codes: this.csv(value.requested_size_codes),
      requested_colour_codes: this.csv(value.requested_colour_codes),
      required_by: value.required_by || null,
    };

    this.perform(
      () => this.api.createSampleRequest(style.id, payload),
      'Sample request created against the exact approved pattern and measurement revisions. Round 1 is ready for the sample room.',
      (response) => {
        this.sampleForm.reset({
          pattern_set_id: '',
          sample_type: 'FIT',
          purpose: '',
          requested_quantity: '1',
          requested_size_codes: 'M',
          requested_colour_codes: '',
          required_by: '',
        });
        this.refreshListsAndSelect(response.data.id);
      },
    );
  }

  startRound(round: SampleRoundRecord): void {
    const request = this.selectedSampleRequest()?.data;
    if (!request || !this.canManageStyle() || round.status !== 'REQUESTED') return;
    this.perform(
      () => this.api.startSampleRound(request.id, round.id),
      'Sample-room build round started. Construction work is recorded against the pinned pattern revision.',
      () => this.refreshSelectedRequest(request.id),
    );
  }

  prepareCompletion(round: SampleRoundRecord): void {
    this.completionForm.reset({ actual_quantity: String(round.planned_quantity), completion_summary: '' });
    this.activeFitRound.set(round);
    this.clearMessages();
  }

  completeRound(round: SampleRoundRecord): void {
    const request = this.selectedSampleRequest()?.data;
    if (!request || !this.canManageStyle() || round.status !== 'IN_PROGRESS' || !this.valid(this.completionForm)) return;
    const value = this.completionForm.getRawValue();
    this.perform(
      () => this.api.completeSampleRound(request.id, round.id, {
        actual_quantity: Number(value.actual_quantity),
        completion_summary: value.completion_summary.trim(),
      }),
      'Sample build completed. The round is now available for a size-specific fit session.',
      () => {
        this.completionForm.reset({ actual_quantity: '1', completion_summary: '' });
        this.refreshSelectedRequest(request.id);
      },
    );
  }

  prepareFitSession(round: SampleRoundRecord): void {
    const request = this.selectedSampleRequest()?.data;
    if (!request || round.status !== 'COMPLETED' || !this.canManageStyle()) return;
    this.activeFitRound.set(round);
    this.fitSessionForm.reset({
      fit_size_code: this.toArray(request.requested_size_codes)[0] ?? '',
      fit_model_code: '',
      posture_code: 'STANDING',
      condition_notes: '',
      session_date: new Date().toISOString().slice(0, 10),
    });
    this.clearMessages();
  }

  createFitSession(): void {
    const round = this.activeFitRound();
    const request = this.selectedSampleRequest()?.data;
    if (!round || !request || !this.canManageStyle() || !this.valid(this.fitSessionForm)) return;
    const value = this.fitSessionForm.getRawValue();
    const payload: FitSessionInput = {
      fit_size_code: value.fit_size_code,
      fit_model_code: value.fit_model_code.trim().toUpperCase(),
      posture_code: value.posture_code,
      condition_notes: value.condition_notes.trim(),
      session_date: value.session_date,
    };
    this.perform(
      () => this.api.createFitSession(round.id, payload),
      'Fit session created. Record each required point of measurement before submitting it for independent disposition.',
      (response) => {
        this.activeFitRound.set(null);
        this.refreshSelectedRequest(request.id, response.data.id);
      },
    );
  }

  selectFitSession(session: FitSessionRecord): void {
    this.selectedFitSessionId.set(session.id);
    this.observationsForm.reset({ observations_json: '[]' });
    this.decisionForm.reset({ decision: 'APPROVE', reason: '', actions: '' });
    this.clearMessages();
  }

  selectedFitSession(): FitSessionRecord | null {
    const id = this.selectedFitSessionId();
    if (!id) return null;
    return this.selectedSampleRequest()?.fit_sessions.find((item) => item.id === id) ?? null;
  }

  recordObservations(): void {
    const fitSession = this.selectedFitSession();
    if (!fitSession || fitSession.status !== 'DRAFT' || !this.valid(this.observationsForm)) return;
    let observations: FitObservationInput[];
    try {
      observations = this.parseArray<FitObservationInput>(this.observationsForm.controls.observations_json.value, 'Fit observations');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Fit observation JSON is invalid.');
      return;
    }
    if (observations.length === 0) {
      this.errorMessage.set('Add at least one measurement point observation.');
      return;
    }

    this.perform(
      () => this.api.recordFitObservations(fitSession.id, observations),
      'Fit observations recorded with the approved target and tolerance snapshot.',
      () => this.refreshSelectedRequest(fitSession.sample_request_id, fitSession.id),
    );
  }

  submitFitSession(): void {
    const fitSession = this.selectedFitSession();
    if (!fitSession || fitSession.status !== 'DRAFT' || !this.canManageStyle()) return;
    this.perform(
      () => this.api.submitFitSession(fitSession.id),
      'Fit session submitted. An independent tenant approver must record the disposition.',
      () => this.refreshSelectedRequest(fitSession.sample_request_id, fitSession.id),
    );
  }

  decideFitSession(): void {
    const fitSession = this.selectedFitSession();
    if (!fitSession || fitSession.status !== 'SUBMITTED_FOR_REVIEW' || !this.canApprove(fitSession.created_by) || !this.valid(this.decisionForm)) return;
    const value = this.decisionForm.getRawValue();
    const payload: FitDecisionInput = {
      decision: value.decision,
      reason: value.reason.trim(),
      actions: this.csv(value.actions),
    };
    this.perform(
      () => this.api.decideFitSession(fitSession.id, payload),
      'Independent fit disposition recorded. Existing observations and prior sample rounds remain immutable.',
      () => this.refreshSelectedRequest(fitSession.sample_request_id, fitSession.id),
    );
  }

  createCorrectionRound(): void {
    const request = this.selectedSampleRequest()?.data;
    if (!request || request.status !== 'CORRECTION_REQUIRED' || !this.canManageStyle() || !this.valid(this.roundForm)) return;
    const value = this.roundForm.getRawValue();
    this.perform(
      () => this.api.createSampleRound(request.id, {
        round_reason: value.round_reason.trim(),
        planned_quantity: value.planned_quantity.trim() ? Number(value.planned_quantity) : null,
      }),
      'Correction sample round created. The earlier sample and fit decision remain preserved.',
      () => {
        this.roundForm.reset({ round_reason: '', planned_quantity: '1' });
        this.refreshSelectedRequest(request.id);
      },
    );
  }

  canManageStyle(): boolean {
    const style = this.selectedStyle();
    const userId = this.session.user()?.id;
    const role = this.session.activeTenant()?.membership_role;
    return !!style && !!userId && (style.owner_user_id === userId || role === 'OWNER' || role === 'ADMIN');
  }

  canApprove(createdBy: string | null): boolean {
    const userId = this.session.user()?.id;
    const role = this.session.activeTenant()?.membership_role;
    return !!userId && !!createdBy && createdBy !== userId && (role === 'OWNER' || role === 'ADMIN');
  }

  fitSizeOptions(): string[] {
    return this.availableFitSizes();
  }

  private refreshListsAndSelect(requestId: string): void {
    const styleId = this.selectedStyle()?.id;
    if (!styleId) return;
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    const sequence = this.workspaceSequence;
    this.api.listSampleRequests(styleId).subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, sequence)) return;
        this.sampleRequests.set(response.data.data);
        this.refreshSelectedRequest(requestId);
      },
      error: () => {
        if (this.isCurrent(tenantId, sequence)) this.errorMessage.set('The sample request register could not be refreshed.');
      },
    });
  }

  private refreshSelectedRequest(requestId: string, fitSessionId: string | null = null): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    const sequence = this.workspaceSequence;
    const detailSequence = ++this.detailSequence;
    this.api.getSampleRequest(requestId).subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, sequence) || detailSequence !== this.detailSequence) return;
        this.selectedSampleRequest.set(response);
        if (fitSessionId) this.selectedFitSessionId.set(fitSessionId);
        const styleId = this.selectedStyle()?.id;
        if (styleId) {
          this.api.listSampleRequests(styleId).subscribe({
            next: (list) => {
              if (this.isCurrent(tenantId, sequence) && detailSequence === this.detailSequence) {
                this.sampleRequests.set(list.data.data);
              }
            },
          });
        }
      },
      error: () => {
        if (this.isCurrent(tenantId, sequence) && detailSequence === this.detailSequence) {
          this.errorMessage.set('The sample and fit history could not be refreshed.');
        }
      },
    });
  }

  private perform<T>(request: () => Observable<T>, success: string, afterSuccess: (response: T) => void): void {
    if (this.saving()) return;
    const tenantId = this.session.activeTenant()?.id;
    const sequence = this.workspaceSequence;
    if (!tenantId) return;
    this.saving.set(true);
    this.clearMessages();

    request().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, sequence)) return;
        this.saving.set(false);
        afterSuccess(response);
        this.successMessage.set(success);
      },
      error: (error: HttpErrorResponse) => {
        if (!this.isCurrent(tenantId, sequence)) return;
        this.saving.set(false);
        const validation = error.error?.errors as Record<string, string[]> | undefined;
        const firstValidation = validation ? Object.values(validation).flat()[0] : null;
        this.errorMessage.set(firstValidation
          ?? error.error?.message
          ?? (error.status === 403
            ? 'This action requires the style owner or an independent authorized tenant approver.'
            : error.status === 404
              ? 'The source record is not available in this tenant.'
              : 'The sample-room or fit action could not be completed.'));
      },
    });
  }

  private parseArray<T>(value: string, label: string): T[] {
    const parsed: unknown = JSON.parse(value);
    if (!Array.isArray(parsed)) throw new Error(label + ' must be a JSON array.');
    return parsed as T[];
  }

  private toArray(value: string[] | string): string[] {
    if (Array.isArray(value)) return value;
    try {
      const parsed: unknown = JSON.parse(value);
      return Array.isArray(parsed) ? parsed.map((item) => String(item)) : [];
    } catch {
      return [];
    }
  }

  private csv(value: string): string[] {
    return [...new Set(value.split(',').map((item) => item.trim().toUpperCase()).filter(Boolean))];
  }

  private valid<T extends { invalid: boolean; markAllAsTouched: () => void }>(form: T): boolean {
    if (!form.invalid) return true;
    form.markAllAsTouched();
    return false;
  }

  private isCurrent(tenantId: string, sequence: number): boolean {
    return this.session.activeTenant()?.id === tenantId && this.workspaceSequence === sequence;
  }

  private clearMessages(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);
  }

  private clearWorkspace(): void {
    this.styles.set([]);
    this.selectedStyle.set(null);
    this.patternSets.set([]);
    this.measurementSpecs.set([]);
    this.sampleRequests.set([]);
    this.selectedSampleRequest.set(null);
    this.selectedFitSessionId.set(null);
    this.activeFitRound.set(null);
    this.sampleForm.reset({
      pattern_set_id: '',
      sample_type: 'FIT',
      purpose: '',
      requested_quantity: '1',
      requested_size_codes: 'M',
      requested_colour_codes: '',
      required_by: '',
    });
    this.roundForm.reset({ round_reason: '', planned_quantity: '1' });
    this.completionForm.reset({ actual_quantity: '1', completion_summary: '' });
    this.fitSessionForm.reset({
      fit_size_code: '',
      fit_model_code: '',
      posture_code: 'STANDING',
      condition_notes: '',
      session_date: '',
    });
    this.observationsForm.reset({ observations_json: '[]' });
    this.decisionForm.reset({ decision: 'APPROVE', reason: '', actions: '' });
    this.loading.set(false);
    this.saving.set(false);
    this.clearMessages();
  }
}
