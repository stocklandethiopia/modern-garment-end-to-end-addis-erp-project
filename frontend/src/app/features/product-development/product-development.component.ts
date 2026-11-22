import { DatePipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin, Observable } from 'rxjs';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { RangeLineRecord, RangePlanRecord, RangePlanningService } from '../merchandising/range-planning.service';
import {
  ProductBrief,
  ProductBriefDetail,
  ProductBriefInput,
  ProductBriefVersionInput,
  ProductDevelopmentService,
  StyleDetail,
  StyleInput,
  StyleRecord,
  StyleRevisionInput,
} from './product-development.service';

type DevelopmentTab = 'BRIEFS' | 'STYLES';

@Component({
  selector: 'app-product-development',
  standalone: true,
  imports: [DatePipe, ReactiveFormsModule],
  templateUrl: './product-development.component.html',
  styleUrl: './product-development.component.css',
})
export class ProductDevelopmentComponent {
  private readonly api = inject(ProductDevelopmentService);
  private readonly rangeApi = inject(RangePlanningService);
  readonly session = inject(AuthSessionService);

  readonly activeTab = signal<DevelopmentTab>('BRIEFS');
  readonly rangePlans = signal<RangePlanRecord[]>([]);
  readonly rangeLines = signal<RangeLineRecord[]>([]);
  readonly briefs = signal<ProductBrief[]>([]);
  readonly styles = signal<StyleRecord[]>([]);
  readonly selectedBrief = signal<ProductBriefDetail | null>(null);
  readonly selectedStyle = signal<StyleDetail | null>(null);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  private sequence = 0;
  private detailSequence = 0;

  readonly briefForm = new FormGroup({
    range_plan_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    range_line_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    target_consumer: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(200)] }),
    product_need: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(10000)] }),
    intended_use: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(500)] }),
    target_price: new FormControl('', { nonNullable: true }),
    target_cost: new FormControl('', { nonNullable: true }),
    target_currency: new FormControl('ETB', { nonNullable: true, validators: [Validators.required, Validators.pattern(/^[A-Za-z]{3}$/)] }),
    quality_tier: new FormControl('', { nonNullable: true }),
    delivery_need_by: new FormControl('', { nonNullable: true }),
    sustainability_intent: new FormControl('', { nonNullable: true }),
    change_reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
  });

  readonly briefVersionForm = new FormGroup({
    target_consumer: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(200)] }),
    product_need: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(10000)] }),
    intended_use: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(500)] }),
    target_price: new FormControl('', { nonNullable: true }),
    target_cost: new FormControl('', { nonNullable: true }),
    target_currency: new FormControl('ETB', { nonNullable: true, validators: [Validators.required, Validators.pattern(/^[A-Za-z]{3}$/)] }),
    quality_tier: new FormControl('', { nonNullable: true }),
    delivery_need_by: new FormControl('', { nonNullable: true }),
    sustainability_intent: new FormControl('', { nonNullable: true }),
    change_reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
  });

  readonly decisionForm = new FormGroup({
    decision: new FormControl<'ACCEPT' | 'REJECT'>('ACCEPT', { nonNullable: true, validators: [Validators.required] }),
    reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(2000)] }),
    actions: new FormControl('', { nonNullable: true }),
  });

  readonly styleForm = new FormGroup({
    product_brief_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    style_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    name: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(180)] }),
    category_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    design_direction: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(10000)] }),
    silhouette: new FormControl('', { nonNullable: true }),
    fit_intent: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(5000)] }),
    construction_intent: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(5000)] }),
    material_direction_json: new FormControl('{}', { nonNullable: true, validators: [Validators.required] }),
    colour_direction_json: new FormControl('{}', { nonNullable: true, validators: [Validators.required] }),
    target_size_range_json: new FormControl('{}', { nonNullable: true, validators: [Validators.required] }),
    change_summary: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
  });

  readonly revisionForm = new FormGroup({
    design_direction: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(10000)] }),
    silhouette: new FormControl('', { nonNullable: true }),
    fit_intent: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(5000)] }),
    construction_intent: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(5000)] }),
    material_direction_json: new FormControl('{}', { nonNullable: true, validators: [Validators.required] }),
    colour_direction_json: new FormControl('{}', { nonNullable: true, validators: [Validators.required] }),
    target_size_range_json: new FormControl('{}', { nonNullable: true, validators: [Validators.required] }),
    change_summary: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
  });

  private readonly tenantEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    const requestId = ++this.sequence;
    this.clearData();
    if (tenantId) this.loadAll(tenantId, requestId);
  });

  setTab(tab: DevelopmentTab): void {
    this.activeTab.set(tab);
    this.clearMessages();
  }

  loadAll(tenantId = this.session.activeTenant()?.id, requestId = this.sequence): void {
    if (!tenantId) return;
    this.loading.set(true);
    forkJoin({
      briefs: this.api.listProductBriefs(),
      styles: this.api.listStyles(),
      rangePlans: this.rangeApi.listRangePlans(),
    }).subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.briefs.set(response.briefs.data.data);
        this.styles.set(response.styles.data.data);
        this.rangePlans.set(response.rangePlans.data.data);
        this.loading.set(false);
      },
      error: () => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.loading.set(false);
        this.errorMessage.set('Product development records could not be loaded. Refresh after checking the tenant connection.');
      },
    });
  }

  loadRangePlanLines(rangePlanId: string): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    this.briefForm.patchValue({ range_line_id: '' });
    this.rangeLines.set([]);
    if (!rangePlanId) return;
    const requestId = this.sequence;
    this.rangeApi.listRangeLines(rangePlanId).subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId) && this.briefForm.controls.range_plan_id.value === rangePlanId) {
          this.rangeLines.set(response.data.data);
        }
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId)) this.errorMessage.set('The selected frozen range lines could not be loaded.');
      },
    });
  }

  createBrief(): void {
    if (!this.valid(this.briefForm)) return;
    const value = this.briefForm.getRawValue();
    const payload: ProductBriefInput = {
      range_line_id: value.range_line_id,
      initiative_id: null,
      target_consumer: value.target_consumer.trim(),
      product_need: value.product_need.trim(),
      intended_use: value.intended_use.trim(),
      target_price: this.numberOrNull(value.target_price),
      target_cost: this.numberOrNull(value.target_cost),
      target_currency: value.target_currency.trim().toUpperCase(),
      quality_tier: value.quality_tier.trim() || null,
      delivery_need_by: value.delivery_need_by || null,
      sustainability_intent: this.csv(value.sustainability_intent),
      change_reason: value.change_reason.trim(),
    };
    this.perform(() => this.api.createProductBrief(payload), 'Product brief created as a versioned draft. Submit it for independent acceptance before starting a style.', (response) => {
      this.briefForm.reset({
        range_plan_id: '', range_line_id: '', target_consumer: '', product_need: '', intended_use: '',
        target_price: '', target_cost: '', target_currency: 'ETB', quality_tier: '',
        delivery_need_by: '', sustainability_intent: '', change_reason: '',
      });
      this.rangeLines.set([]);
      this.loadAll();
      this.selectBriefById(response.data.id);
    });
  }

  selectBrief(brief: ProductBrief): void {
    this.selectBriefById(brief.id);
  }

  selectBriefById(id: string): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    const requestId = this.sequence;
    const detailRequest = ++this.detailSequence;
    this.selectedBrief.set(null);
    this.api.getProductBrief(id).subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId) && detailRequest === this.detailSequence) {
          this.selectedBrief.set(response);
        }
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId) && detailRequest === this.detailSequence) {
          this.errorMessage.set('The selected product brief could not be opened.');
        }
      },
    });
    this.clearMessages();
  }

  submitBrief(): void {
    const brief = this.selectedBrief()?.data;
    if (!brief || !this.canSubmitBrief(brief)) return;
    this.perform(() => this.api.submitProductBrief(brief.id), 'Product brief submitted for independent acceptance review.', () => this.refreshBrief(brief.id));
  }

  createBriefVersion(): void {
    const brief = this.selectedBrief()?.data;
    if (!brief || !this.canReviseBrief(brief) || !this.valid(this.briefVersionForm)) return;
    const value = this.briefVersionForm.getRawValue();
    const payload: ProductBriefVersionInput = {
      target_consumer: value.target_consumer.trim(),
      product_need: value.product_need.trim(),
      intended_use: value.intended_use.trim(),
      target_price: this.numberOrNull(value.target_price),
      target_cost: this.numberOrNull(value.target_cost),
      target_currency: value.target_currency.trim().toUpperCase(),
      quality_tier: value.quality_tier.trim() || null,
      delivery_need_by: value.delivery_need_by || null,
      sustainability_intent: this.csv(value.sustainability_intent),
      change_reason: value.change_reason.trim(),
    };
    this.perform(() => this.api.createProductBriefVersion(brief.id, payload), 'New product brief revision saved as a draft. It must be submitted and accepted before new styles can use it.', () => {
      this.briefVersionForm.reset({
        target_consumer: '', product_need: '', intended_use: '', target_price: '', target_cost: '',
        target_currency: 'ETB', quality_tier: '', delivery_need_by: '',
        sustainability_intent: '', change_reason: '',
      });
      this.refreshBrief(brief.id);
    });
  }

  decideBrief(): void {
    const brief = this.selectedBrief()?.data;
    if (!brief || !this.canDecideBrief(brief) || !this.valid(this.decisionForm)) return;
    const value = this.decisionForm.getRawValue();
    this.perform(() => this.api.decideProductBrief(brief.id, {
      decision: value.decision,
      reason: value.reason.trim(),
      actions: this.csv(value.actions),
    }), 'Independent product brief decision recorded.', () => {
      this.decisionForm.reset({ decision: 'ACCEPT', reason: '', actions: '' });
      this.refreshBrief(brief.id);
    });
  }

  selectStyle(style: StyleRecord): void {
    this.selectStyleById(style.id);
  }

  private selectStyleById(id: string): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    const requestId = this.sequence;
    const detailRequest = ++this.detailSequence;
    this.selectedStyle.set(null);
    this.api.getStyle(id).subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId) && detailRequest === this.detailSequence) {
          this.selectedStyle.set(response);
        }
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId) && detailRequest === this.detailSequence) {
          this.errorMessage.set('The selected style revision history could not be opened.');
        }
      },
    });
    this.clearMessages();
  }

  createStyle(): void {
    if (!this.valid(this.styleForm)) return;
    const value = this.styleForm.getRawValue();
    let material: Record<string, unknown>;
    let colour: Record<string, unknown>;
    let sizes: Record<string, unknown>;
    try {
      material = this.parseObject(value.material_direction_json, 'Material direction');
      colour = this.parseObject(value.colour_direction_json, 'Colour direction');
      sizes = this.parseObject(value.target_size_range_json, 'Target size range');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Style direction JSON is invalid.');
      return;
    }
    const payload: StyleInput = {
      product_brief_id: value.product_brief_id,
      style_code: value.style_code.trim().toUpperCase(),
      name: value.name.trim(),
      category_code: value.category_code.trim().toUpperCase(),
      design_direction: value.design_direction.trim(),
      silhouette: value.silhouette.trim() || null,
      fit_intent: value.fit_intent.trim(),
      construction_intent: value.construction_intent.trim(),
      material_direction: material,
      colour_direction: colour,
      target_size_range: sizes,
      change_summary: value.change_summary.trim(),
    };
    this.perform(() => this.api.createStyle(payload), 'Style created against the exact accepted product brief version.', (response) => {
      this.styleForm.reset({
        product_brief_id: '', style_code: '', name: '', category_code: '',
        design_direction: '', silhouette: '', fit_intent: '', construction_intent: '',
        material_direction_json: '{}', colour_direction_json: '{}', target_size_range_json: '{}', change_summary: '',
      });
      this.loadAll();
      this.selectStyleById(response.data.id);
    });
  }

  createStyleRevision(): void {
    const style = this.selectedStyle()?.data;
    if (!style || style.status !== 'DESIGN_IN_PROGRESS' || !this.valid(this.revisionForm)) return;
    const value = this.revisionForm.getRawValue();
    let material: Record<string, unknown>;
    let colour: Record<string, unknown>;
    let sizes: Record<string, unknown>;
    try {
      material = this.parseObject(value.material_direction_json, 'Material direction');
      colour = this.parseObject(value.colour_direction_json, 'Colour direction');
      sizes = this.parseObject(value.target_size_range_json, 'Target size range');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Style revision JSON is invalid.');
      return;
    }
    const payload: StyleRevisionInput = {
      design_direction: value.design_direction.trim(),
      silhouette: value.silhouette.trim() || null,
      fit_intent: value.fit_intent.trim(),
      construction_intent: value.construction_intent.trim(),
      material_direction: material,
      colour_direction: colour,
      target_size_range: sizes,
      change_summary: value.change_summary.trim(),
    };
    this.perform(() => this.api.createStyleRevision(style.id, payload), 'New immutable design revision appended to the style history.', () => {
      this.revisionForm.reset({
        design_direction: '', silhouette: '', fit_intent: '', construction_intent: '',
        material_direction_json: '{}', colour_direction_json: '{}', target_size_range_json: '{}', change_summary: '',
      });
      this.refreshStyle(style.id);
    });
  }

  canSubmitBrief(brief: ProductBrief): boolean {
    const userId = this.session.user()?.id;
    const role = this.session.activeTenant()?.membership_role;
    return brief.status === 'DRAFT' && (brief.owner_user_id === userId || role === 'OWNER' || role === 'ADMIN');
  }

  canReviseBrief(brief: ProductBrief): boolean {
    const userId = this.session.user()?.id;
    const role = this.session.activeTenant()?.membership_role;
    return brief.status !== 'SUBMITTED_FOR_REVIEW' && (brief.owner_user_id === userId || role === 'OWNER' || role === 'ADMIN');
  }

  canDecideBrief(brief: ProductBrief): boolean {
    const userId = this.session.user()?.id;
    const role = this.session.activeTenant()?.membership_role;
    return brief.status === 'SUBMITTED_FOR_REVIEW'
      && (role === 'OWNER' || role === 'ADMIN')
      && !!userId
      && brief.owner_user_id !== userId;
  }

  acceptedBriefs(): ProductBrief[] {
    return this.briefs().filter((brief) =>
      brief.status === 'ACCEPTED'
      && brief.accepted_version !== null
      && brief.accepted_version === brief.current_version,
    );
  }

  private refreshBrief(id: string): void {
    this.loadAll();
    this.selectBriefById(id);
  }

  private refreshStyle(id: string): void {
    this.loadAll();
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    const requestId = this.sequence;
    this.api.getStyle(id).subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId)) this.selectedStyle.set(response);
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId)) this.errorMessage.set('The revised style could not be reloaded.');
      },
    });
  }

  private perform<T>(
    request: () => Observable<T>,
    success: string,
    afterSuccess: (response: T) => void,
  ): void {
    if (this.saving()) return;
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    const requestId = this.sequence;
    this.saving.set(true);
    this.clearMessages();
    request().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.saving.set(false);
        this.successMessage.set(success);
        afterSuccess(response);
      },
      error: (error: HttpErrorResponse) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.saving.set(false);
        this.errorMessage.set(error.status === 422
          ? 'The product development gate rejected this action. Check the approved source version, status, required content and approval separation.'
          : error.status === 403
            ? 'This action requires the product brief owner or a different authorized tenant approver.'
            : error.status === 404
              ? 'The selected source record is not available in this tenant.'
              : 'The request could not be completed. Check the tenant connection and try again.');
      },
    });
  }

  private numberOrNull(value: string): number | null {
    if (!value.trim()) return null;
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : null;
  }

  private csv(value: string): string[] {
    return [...new Set(value.split(',').map((part) => part.trim()).filter(Boolean))];
  }

  private parseObject(value: string, label: string): Record<string, unknown> {
    const parsed: unknown = JSON.parse(value);
    if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
      throw new Error(label + ' must be a JSON object.');
    }
    return parsed as Record<string, unknown>;
  }

  private valid<T extends { invalid: boolean; markAllAsTouched: () => void }>(form: T): boolean {
    if (!form.invalid) return true;
    form.markAllAsTouched();
    return false;
  }

  private isCurrent(tenantId: string, requestId: number): boolean {
    return this.session.activeTenant()?.id === tenantId && this.sequence === requestId;
  }

  private clearMessages(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);
  }

  private clearData(): void {
    this.rangePlans.set([]);
    this.rangeLines.set([]);
    this.briefs.set([]);
    this.styles.set([]);
    this.selectedBrief.set(null);
    this.selectedStyle.set(null);
    this.briefForm.reset();
    this.briefVersionForm.reset();
    this.decisionForm.reset({ decision: 'ACCEPT', reason: '', actions: '' });
    this.styleForm.reset({
      product_brief_id: '', style_code: '', name: '', category_code: '', design_direction: '',
      silhouette: '', fit_intent: '', construction_intent: '',
      material_direction_json: '{}', colour_direction_json: '{}', target_size_range_json: '{}', change_summary: '',
    });
    this.revisionForm.reset({
      design_direction: '', silhouette: '', fit_intent: '', construction_intent: '',
      material_direction_json: '{}', colour_direction_json: '{}', target_size_range_json: '{}', change_summary: '',
    });
    this.loading.set(false);
    this.saving.set(false);
    this.clearMessages();
  }
}
