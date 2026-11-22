import { DatePipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin, Observable } from 'rxjs';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import {
  MeasurementPointInput,
  MeasurementSpecInput,
  MeasurementSpecVersion,
  PatternGradeRuleInput,
  PatternPieceInput,
  PatternRevisionInput,
  PatternSetInput,
  PatternSetRecord,
  ProductDevelopmentService,
  StyleRecord,
} from './product-development.service';

@Component({
  selector: 'app-technical-product-development',
  standalone: true,
  imports: [DatePipe, ReactiveFormsModule],
  templateUrl: './technical-product-development.component.html',
  styleUrl: './technical-product-development.component.css',
})
export class TechnicalProductDevelopmentComponent {
  private readonly api = inject(ProductDevelopmentService);
  readonly session = inject(AuthSessionService);

  readonly styles = signal<StyleRecord[]>([]);
  readonly selectedStyle = signal<StyleRecord | null>(null);
  readonly measurementSpecs = signal<MeasurementSpecVersion[]>([]);
  readonly patternSets = signal<PatternSetRecord[]>([]);
  readonly selectedPattern = signal<PatternSetRecord | null>(null);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  private workspaceSequence = 0;
  private detailSequence = 0;

  readonly measurementForm = new FormGroup({
    size_system_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    size_sequence_csv: new FormControl('XS, S, M, L', { nonNullable: true, validators: [Validators.required] }),
    base_size_code: new FormControl('M', { nonNullable: true, validators: [Validators.required, Validators.maxLength(32)] }),
    measurement_condition: new FormControl('Garment measured flat in relaxed condition.', { nonNullable: true, validators: [Validators.required, Validators.maxLength(120)] }),
    points_json: new FormControl('[]', { nonNullable: true, validators: [Validators.required] }),
    change_reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
  });

  readonly patternForm = new FormGroup({
    pattern_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    name: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(180)] }),
    measurement_spec_version_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    pieces_json: new FormControl('[]', { nonNullable: true, validators: [Validators.required] }),
    grade_rules_json: new FormControl('[]', { nonNullable: true, validators: [Validators.required] }),
    change_summary: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
  });

  readonly patternRevisionForm = new FormGroup({
    measurement_spec_version_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    pieces_json: new FormControl('[]', { nonNullable: true, validators: [Validators.required] }),
    grade_rules_json: new FormControl('[]', { nonNullable: true, validators: [Validators.required] }),
    change_summary: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
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
    this.clearMessages();
    const sequence = this.workspaceSequence;
    const detail = ++this.detailSequence;
    this.selectedStyle.set(null);
    this.selectedPattern.set(null);
    this.measurementSpecs.set([]);
    this.patternSets.set([]);
    this.loading.set(true);
    forkJoin({
      style: this.api.getStyle(styleId),
      specs: this.api.listMeasurementSpecs(styleId),
      patterns: this.api.listPatternSets(styleId),
    }).subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, sequence) || detail !== this.detailSequence) return;
        this.selectedStyle.set(response.style.data);
        this.measurementSpecs.set(response.specs.data);
        this.patternSets.set(response.patterns.data);
        this.loading.set(false);
      },
      error: (error: HttpErrorResponse) => {
        if (!this.isCurrent(tenantId, sequence) || detail !== this.detailSequence) return;
        this.loading.set(false);
        this.errorMessage.set(error.status === 404
          ? 'The selected style or its technical development records are not available in this tenant.'
          : 'Technical development records could not be loaded.');
      },
    });
  }

  createMeasurementSpec(): void {
    const style = this.selectedStyle();
    if (!style || !this.canManageStyle() || !this.valid(this.measurementForm)) return;
    const value = this.measurementForm.getRawValue();
    let points: MeasurementPointInput[];
    try {
      points = this.parseArray<MeasurementPointInput>(value.points_json, 'Measurement points');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Measurement point JSON is invalid.');
      return;
    }
    const sizes = this.csv(value.size_sequence_csv);
    if (sizes.length < 2 || new Set(sizes).size !== sizes.length || !sizes.includes(value.base_size_code.trim())) {
      this.errorMessage.set('Enter a unique ordered size sequence of at least two sizes and choose a base size from that sequence.');
      return;
    }
    const payload: MeasurementSpecInput = {
      size_system_code: value.size_system_code.trim().toUpperCase(),
      size_sequence: sizes,
      base_size_code: value.base_size_code.trim(),
      measurement_condition: value.measurement_condition.trim(),
      change_reason: value.change_reason.trim(),
      points,
    };
    this.perform(() => this.api.createMeasurementSpec(style.id, payload), 'Measurement specification saved as an immutable draft version. An independent tenant approver must approve it before pattern development.', () => {
      this.measurementForm.reset({
        size_system_code: '', size_sequence_csv: 'XS, S, M, L', base_size_code: 'M',
        measurement_condition: 'Garment measured flat in relaxed condition.', points_json: '[]', change_reason: '',
      });
      this.refreshTechnical(style.id);
    });
  }

  approveMeasurementSpec(spec: MeasurementSpecVersion): void {
    const style = this.selectedStyle();
    if (!style || spec.status !== 'DRAFT' || !this.canApprove(spec.created_by)) return;
    this.perform(() => this.api.approveMeasurementSpec(style.id, spec.id), 'Measurement specification independently approved.', () => this.refreshTechnical(style.id));
  }

  createPatternSet(): void {
    const style = this.selectedStyle();
    if (!style || !this.canManageStyle() || !this.valid(this.patternForm)) return;
    const value = this.patternForm.getRawValue();
    let pieces: PatternPieceInput[];
    let gradeRules: PatternGradeRuleInput[];
    try {
      pieces = this.parseArray<PatternPieceInput>(value.pieces_json, 'Pattern pieces');
      gradeRules = this.parseArray<PatternGradeRuleInput>(value.grade_rules_json, 'Grade rules');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Pattern JSON is invalid.');
      return;
    }
    const payload: PatternSetInput = {
      pattern_code: value.pattern_code.trim().toUpperCase(),
      name: value.name.trim(),
      measurement_spec_version_id: value.measurement_spec_version_id,
      pieces,
      grade_rules: gradeRules,
      change_summary: value.change_summary.trim(),
    };
    this.perform(() => this.api.createPatternSet(style.id, payload), 'Pattern set and revision 1 created. It remains draft until every required grade transition is validated and independently approved.', () => {
      this.patternForm.reset({ pattern_code: '', name: '', measurement_spec_version_id: '', pieces_json: '[]', grade_rules_json: '[]', change_summary: '' });
      this.refreshTechnical(style.id);
    });
  }

  selectPattern(pattern: PatternSetRecord | null): void {
    this.selectedPattern.set(pattern);
    this.patternRevisionForm.reset({
      measurement_spec_version_id: this.approvedMeasurementSpecs()[0]?.id ?? '',
      pieces_json: '[]',
      grade_rules_json: '[]',
      change_summary: '',
    });
    this.clearMessages();
  }

  createPatternRevision(): void {
    const style = this.selectedStyle();
    const pattern = this.selectedPattern();
    if (!style || !pattern || !this.canManageStyle() || !this.valid(this.patternRevisionForm)) return;
    const value = this.patternRevisionForm.getRawValue();
    let pieces: PatternPieceInput[];
    let gradeRules: PatternGradeRuleInput[];
    try {
      pieces = this.parseArray<PatternPieceInput>(value.pieces_json, 'Pattern pieces');
      gradeRules = this.parseArray<PatternGradeRuleInput>(value.grade_rules_json, 'Grade rules');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Pattern revision JSON is invalid.');
      return;
    }
    const payload: PatternRevisionInput = {
      measurement_spec_version_id: value.measurement_spec_version_id,
      pieces,
      grade_rules: gradeRules,
      change_summary: value.change_summary.trim(),
    };
    this.perform(() => this.api.createPatternRevision(pattern.id, payload), 'New pattern revision appended. The previous approved revision remains unchanged until this revision passes approval.', () => {
      this.refreshTechnical(style.id);
    });
  }

  approvePattern(pattern: PatternSetRecord): void {
    if (!pattern.latest_revision_id || pattern.latest_revision_status !== 'DRAFT' || !this.canApprove(pattern.latest_revision_created_by)) return;
    this.perform(() => this.api.approvePatternRevision(pattern.id, pattern.latest_revision_id as string), 'Pattern revision approved with full mandatory grade coverage. It is now the current approved pattern revision.', () => {
      const styleId = this.selectedStyle()?.id;
      if (styleId) this.refreshTechnical(styleId);
    });
  }

  approvedMeasurementSpecs(): MeasurementSpecVersion[] {
    const style = this.selectedStyle();
    if (!style) return [];
    return this.measurementSpecs().filter((spec) =>
      spec.status === 'APPROVED' && spec.style_revision_no === style.current_revision,
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

  private refreshTechnical(styleId: string): void {
    this.selectStyle(styleId);
  }

  private perform<T>(request: () => Observable<T>, success: string, afterSuccess: () => void): void {
    if (this.saving()) return;
    const tenantId = this.session.activeTenant()?.id;
    const sequence = this.workspaceSequence;
    if (!tenantId) return;
    this.saving.set(true);
    this.clearMessages();
    request().subscribe({
      next: () => {
        if (!this.isCurrent(tenantId, sequence)) return;
        this.saving.set(false);
        afterSuccess();
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
            : 'The technical-development action could not be completed.'));
      },
    });
  }

  private parseArray<T>(value: string, label: string): T[] {
    const parsed: unknown = JSON.parse(value);
    if (!Array.isArray(parsed)) throw new Error(label + ' must be a JSON array.');
    return parsed as T[];
  }

  private csv(value: string): string[] {
    return [...new Set(value.split(',').map((item) => item.trim()).filter(Boolean))];
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
    this.measurementSpecs.set([]);
    this.patternSets.set([]);
    this.selectedPattern.set(null);
    this.measurementForm.reset({
      size_system_code: '', size_sequence_csv: 'XS, S, M, L', base_size_code: 'M',
      measurement_condition: 'Garment measured flat in relaxed condition.', points_json: '[]', change_reason: '',
    });
    this.patternForm.reset({ pattern_code: '', name: '', measurement_spec_version_id: '', pieces_json: '[]', grade_rules_json: '[]', change_summary: '' });
    this.patternRevisionForm.reset({ measurement_spec_version_id: '', pieces_json: '[]', grade_rules_json: '[]', change_summary: '' });
    this.loading.set(false);
    this.saving.set(false);
    this.clearMessages();
  }
}
