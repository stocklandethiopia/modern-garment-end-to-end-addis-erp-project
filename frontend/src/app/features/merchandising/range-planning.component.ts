import { DatePipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Observable } from 'rxjs';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import {
  AssortmentLineRecord,
  AssortmentPlanRecord,
  BrandRecord,
  CollectionRecord,
  RangeLineRecord,
  RangePlanRecord,
  RangePlanningService,
  SeasonRecord,
} from './range-planning.service';

type PlanningTab = 'FOUNDATION' | 'COLLECTIONS' | 'RANGE' | 'ASSORTMENT';

@Component({
  selector: 'app-range-planning',
  standalone: true,
  imports: [DatePipe, ReactiveFormsModule],
  templateUrl: './range-planning.component.html',
  styleUrl: './range-planning.component.css',
})
export class RangePlanningComponent {
  private readonly api = inject(RangePlanningService);
  readonly session = inject(AuthSessionService);

  readonly activeTab = signal<PlanningTab>('FOUNDATION');
  readonly brands = signal<BrandRecord[]>([]);
  readonly seasons = signal<SeasonRecord[]>([]);
  readonly collections = signal<CollectionRecord[]>([]);
  readonly rangePlans = signal<RangePlanRecord[]>([]);
  readonly rangeLines = signal<RangeLineRecord[]>([]);
  readonly assortmentPlans = signal<AssortmentPlanRecord[]>([]);
  readonly assortmentLines = signal<AssortmentLineRecord[]>([]);
  readonly selectedBrand = signal<BrandRecord | null>(null);
  readonly selectedCollection = signal<CollectionRecord | null>(null);
  readonly selectedRangePlan = signal<RangePlanRecord | null>(null);
  readonly selectedAssortment = signal<AssortmentPlanRecord | null>(null);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  private sequence = 0;

  readonly brandForm = new FormGroup({
    code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    name: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(180)] }),
  });

  readonly strategyForm = new FormGroup({
    positioning: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(500)] }),
    target_consumer: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(200)] }),
    value_proposition: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(10000)] }),
    quality_tier: new FormControl('', { nonNullable: true }),
    approved_categories: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    price_architecture_json: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    approved_channels: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    effective_from: new FormControl('', { nonNullable: true }),
    effective_to: new FormControl('', { nonNullable: true }),
    change_reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(500)] }),
  });

  readonly seasonForm = new FormGroup({
    code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    name: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(180)] }),
    calendar_type: new FormControl('FASHION', { nonNullable: true, validators: [Validators.required] }),
    market_scope: new FormControl('', { nonNullable: true }),
    timezone: new FormControl(this.session.activeTenant()?.timezone ?? 'Africa/Addis_Ababa', { nonNullable: true, validators: [Validators.required] }),
    design_start: new FormControl('', { nonNullable: true }),
    buy_start: new FormControl('', { nonNullable: true }),
    production_start: new FormControl('', { nonNullable: true }),
    production_end: new FormControl('', { nonNullable: true }),
    delivery_start: new FormControl('', { nonNullable: true }),
    delivery_end: new FormControl('', { nonNullable: true }),
    selling_start: new FormControl('', { nonNullable: true }),
    selling_end: new FormControl('', { nonNullable: true }),
  });

  readonly collectionForm = new FormGroup({
    brand_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    season_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    name: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(180)] }),
    concept: new FormControl('', { nonNullable: true }),
    target_market: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(160)] }),
  });

  readonly collectionVersionForm = new FormGroup({
    story: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10)] }),
    target_consumer: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(180)] }),
    category_architecture_json: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    price_margin_envelope_json: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    budget_amount: new FormControl('', { nonNullable: true }),
    budget_currency: new FormControl('', { nonNullable: true }),
    delivery_start: new FormControl('', { nonNullable: true }),
    delivery_end: new FormControl('', { nonNullable: true }),
    change_reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(500)] }),
  });

  readonly rangePlanForm = new FormGroup({
    collection_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    collection_version: new FormControl('1', { nonNullable: true, validators: [Validators.required, Validators.min(1)] }),
    code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    plan_start: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    plan_end: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
  });

  readonly rangeLineForm = new FormGroup({
    line_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    category_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    target_tier: new FormControl('', { nonNullable: true }),
    product_hypothesis: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(500)] }),
    planned_units: new FormControl('0', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    target_retail_price: new FormControl('', { nonNullable: true }),
    target_wholesale_price: new FormControl('', { nonNullable: true }),
    price_currency: new FormControl('', { nonNullable: true }),
    target_margin_percent: new FormControl('', { nonNullable: true }),
    channel_scope: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    size_colour_intent_json: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    lifecycle_type: new FormControl('NEW', { nonNullable: true, validators: [Validators.required] }),
    forecast_basis: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(500)] }),
    launch_date: new FormControl('', { nonNullable: true }),
  });

  readonly assortmentForm = new FormGroup({
    range_plan_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    channel_code: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(60)] }),
    market_scope: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(160)] }),
    account_reference: new FormControl('', { nonNullable: true }),
    location_cluster_reference: new FormControl('', { nonNullable: true }),
  });

  readonly assortmentLineForm = new FormGroup({
    range_line_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    variant_size_curve_json: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    planned_units: new FormControl('0', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    launch_date: new FormControl('', { nonNullable: true }),
    minimum_depth: new FormControl('', { nonNullable: true }),
    maximum_depth: new FormControl('', { nonNullable: true }),
  });

  private readonly tenantEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    const requestId = ++this.sequence;
    this.clearData();
    if (tenantId) this.loadAll(tenantId, requestId);
    this.seasonForm.patchValue({ timezone: this.session.activeTenant()?.timezone ?? 'Africa/Addis_Ababa' });
  });

  setTab(tab: PlanningTab): void {
    this.activeTab.set(tab);
    this.clearMessages();
  }

  loadAll(tenantId = this.session.activeTenant()?.id, requestId = this.sequence): void {
    if (!tenantId) return;
    this.loading.set(true);
    this.api.listBrands().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.brands.set(response.data.data);
        const selectedId = this.selectedBrand()?.id;
        if (selectedId) this.selectedBrand.set(response.data.data.find((item) => item.id === selectedId) ?? null);
      },
      error: () => { if (this.isCurrent(tenantId, requestId)) this.errorMessage.set('Brands could not be loaded.'); },
    });
    this.api.listSeasons().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.seasons.set(response.data.data);
      },
      error: () => { if (this.isCurrent(tenantId, requestId)) this.errorMessage.set('Season calendars could not be loaded.'); },
    });
    this.api.listCollections().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.collections.set(response.data.data);
        const selectedId = this.selectedCollection()?.id;
        if (selectedId) this.selectedCollection.set(response.data.data.find((item) => item.id === selectedId) ?? null);
      },
      error: () => { if (this.isCurrent(tenantId, requestId)) this.errorMessage.set('Collections could not be loaded.'); },
    });
    this.api.listRangePlans().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.rangePlans.set(response.data.data);
        const selectedId = this.selectedRangePlan()?.id;
        if (selectedId) this.selectedRangePlan.set(response.data.data.find((item) => item.id === selectedId) ?? null);
      },
      error: () => { if (this.isCurrent(tenantId, requestId)) this.errorMessage.set('Range plans could not be loaded.'); },
    });
    this.api.listAssortmentPlans().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.assortmentPlans.set(response.data.data);
        const selectedId = this.selectedAssortment()?.id;
        if (selectedId) this.selectedAssortment.set(response.data.data.find((item) => item.id === selectedId) ?? null);
        this.loading.set(false);
      },
      error: () => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.assortmentPlans.set([]);
        this.loading.set(false);
      },
    });
  }

  createBrand(): void {
    if (!this.valid(this.brandForm)) return;
    const value = this.brandForm.getRawValue();
    this.perform(() => this.api.createBrand({ code: value.code.trim().toUpperCase(), name: value.name.trim(), owner_entity_id: null }),
      'Brand created in draft state. Add its strategy and obtain independent approval.',
      (result) => {
        this.brandForm.reset({ code: '', name: '' });
        this.selectedBrand.set(result.data);
        this.loadAll();
      });
  }

  selectBrand(brand: BrandRecord): void {
    this.selectedBrand.set(brand);
    this.clearMessages();
  }

  createBrandStrategy(): void {
    const brand = this.selectedBrand();
    if (!brand || !this.valid(this.strategyForm)) return;
    const value = this.strategyForm.getRawValue();
    let architecture: Record<string, unknown>;
    try {
      architecture = this.parseObject(value.price_architecture_json, 'Price architecture');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Price architecture JSON is invalid.');
      return;
    }
    this.perform(() => this.api.createBrandStrategy(brand.id, {
      positioning: value.positioning.trim(),
      target_consumer: value.target_consumer.trim(),
      value_proposition: value.value_proposition.trim(),
      quality_tier: value.quality_tier.trim() || null,
      approved_categories: this.csv(value.approved_categories),
      price_architecture: architecture,
      approved_channels: this.csv(value.approved_channels),
      effective_from: value.effective_from || null,
      effective_to: value.effective_to || null,
      change_reason: value.change_reason.trim(),
    }), 'Strategy version submitted for independent approval; the version history remains immutable.', () => {
      this.strategyForm.reset({
        positioning: '', target_consumer: '', value_proposition: '', quality_tier: '',
        approved_categories: '', price_architecture_json: '', approved_channels: '',
        effective_from: '', effective_to: '', change_reason: '',
      });
      this.loadAll();
    });
  }

  approveBrandStrategy(brand: BrandRecord): void {
    if (!brand.latest_strategy_version_id) return;
    this.perform(() => this.api.approveBrandStrategy(brand.id, brand.latest_strategy_version_id as string),
      'Brand strategy independently approved.', () => this.loadAll());
  }

  createSeason(): void {
    if (!this.valid(this.seasonForm)) return;
    const value = this.seasonForm.getRawValue();
    this.perform(() => this.api.createSeason({
      code: value.code.trim().toUpperCase(),
      name: value.name.trim(),
      calendar_type: value.calendar_type,
      market_scope: value.market_scope.trim() || null,
      timezone: value.timezone.trim(),
      design_start: value.design_start || null,
      buy_start: value.buy_start || null,
      production_start: value.production_start || null,
      production_end: value.production_end || null,
      delivery_start: value.delivery_start || null,
      delivery_end: value.delivery_end || null,
      selling_start: value.selling_start || null,
      selling_end: value.selling_end || null,
    }), 'Season calendar created.', () => {
      this.seasonForm.reset({
        code: '', name: '', calendar_type: 'FASHION', market_scope: '',
        timezone: this.session.activeTenant()?.timezone ?? 'Africa/Addis_Ababa',
        design_start: '', buy_start: '', production_start: '', production_end: '',
        delivery_start: '', delivery_end: '', selling_start: '', selling_end: '',
      });
      this.loadAll();
    });
  }

  createCollection(): void {
    if (!this.valid(this.collectionForm)) return;
    const value = this.collectionForm.getRawValue();
    this.perform(() => this.api.createCollection({
      brand_id: value.brand_id,
      season_id: value.season_id,
      code: value.code.trim().toUpperCase(),
      name: value.name.trim(),
      concept: value.concept.trim() || null,
      target_market: value.target_market.trim(),
    }), 'Collection created as draft. Its commercial architecture needs a version and independent approval.', () => {
      this.collectionForm.reset({ brand_id: '', season_id: '', code: '', name: '', concept: '', target_market: '' });
      this.loadAll();
    });
  }

  selectCollection(collection: CollectionRecord): void {
    this.selectedCollection.set(collection);
    this.rangePlanForm.patchValue({ collection_id: collection.id, collection_version: String(collection.current_version || collection.latest_version_no || 1) });
    this.clearMessages();
  }

  createCollectionVersion(): void {
    const collection = this.selectedCollection();
    if (!collection || !this.valid(this.collectionVersionForm)) return;
    const value = this.collectionVersionForm.getRawValue();
    let categories: unknown[];
    let envelope: Record<string, unknown>;
    try {
      categories = this.parseArray(value.category_architecture_json, 'Category architecture');
      envelope = this.parseObject(value.price_margin_envelope_json, 'Price and margin envelope');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Collection JSON is invalid.');
      return;
    }
    this.perform(() => this.api.createCollectionVersion(collection.id, {
      story: value.story.trim(),
      target_consumer: value.target_consumer.trim(),
      category_architecture: categories,
      price_margin_envelope: envelope,
      budget_amount: value.budget_amount.trim() || null,
      budget_currency: value.budget_currency.trim().toUpperCase() || null,
      delivery_start: value.delivery_start || null,
      delivery_end: value.delivery_end || null,
      change_reason: value.change_reason.trim(),
    }), 'Collection version saved pending independent approval.', (result) => {
      this.collectionVersionForm.reset({
        story: '', target_consumer: '', category_architecture_json: '',
        price_margin_envelope_json: '', budget_amount: '', budget_currency: '',
        delivery_start: '', delivery_end: '', change_reason: '',
      });
      this.selectedCollection.set({
        ...collection,
        latest_version_id: result.data.id,
        latest_version_no: result.data.version_no,
        latest_version_created_by: this.session.user()?.id ?? null,
        latest_version_status: result.data.status,
      });
      this.loadAll();
    });
  }

  approveCollectionVersion(collection: CollectionRecord): void {
    if (!collection.latest_version_id) return;
    this.perform(() => this.api.approveCollectionVersion(collection.id, collection.latest_version_id as string),
      'Collection version approved by a different authorized user.', () => this.loadAll());
  }

  createRangePlan(): void {
    if (!this.valid(this.rangePlanForm)) return;
    const value = this.rangePlanForm.getRawValue();
    this.perform(() => this.api.createRangePlan({
      collection_id: value.collection_id,
      collection_version: Number(value.collection_version),
      code: value.code.trim().toUpperCase(),
      plan_start: value.plan_start,
      plan_end: value.plan_end,
    }), 'Range plan created in draft state.', () => {
      this.rangePlanForm.reset({ collection_id: '', collection_version: '1', code: '', plan_start: '', plan_end: '' });
      this.loadAll();
    });
  }

  selectRangePlan(plan: RangePlanRecord): void {
    this.selectedRangePlan.set(plan);
    this.rangeLines.set([]);
    this.api.listRangeLines(plan.id).subscribe({
      next: (response) => {
        if (this.selectedRangePlan()?.id === plan.id) this.rangeLines.set(response.data.data);
      },
      error: () => this.errorMessage.set('Range lines could not be loaded for this plan.'),
    });
    this.clearMessages();
  }

  createRangeLine(): void {
    const plan = this.selectedRangePlan();
    if (!plan || !this.valid(this.rangeLineForm)) return;
    const value = this.rangeLineForm.getRawValue();
    let intent: Record<string, unknown>;
    try {
      intent = this.parseObject(value.size_colour_intent_json, 'Size and colour intent');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Size and colour JSON is invalid.');
      return;
    }
    this.perform(() => this.api.createRangeLine(plan.id, {
      line_code: value.line_code.trim().toUpperCase(),
      category_code: value.category_code.trim().toUpperCase(),
      target_tier: value.target_tier.trim() || null,
      product_hypothesis: value.product_hypothesis.trim(),
      planned_units: Number(value.planned_units),
      target_retail_price: value.target_retail_price.trim() || null,
      target_wholesale_price: value.target_wholesale_price.trim() || null,
      price_currency: value.price_currency.trim().toUpperCase() || null,
      target_margin_percent: value.target_margin_percent.trim() || null,
      channel_scope: this.csv(value.channel_scope),
      size_colour_intent: intent,
      lifecycle_type: value.lifecycle_type,
      forecast_basis: value.forecast_basis.trim(),
      launch_date: value.launch_date || null,
    }), 'Range line added to the draft plan.', () => {
      this.rangeLineForm.reset({
        line_code: '', category_code: '', target_tier: '', product_hypothesis: '',
        planned_units: '0', target_retail_price: '', target_wholesale_price: '',
        price_currency: '', target_margin_percent: '', channel_scope: '',
        size_colour_intent_json: '', lifecycle_type: 'NEW', forecast_basis: '', launch_date: '',
      });
      this.selectRangePlan(plan);
    });
  }

  freezeRangePlan(plan: RangePlanRecord): void {
    this.perform(() => this.api.freezeRangePlan(plan.id), 'Range plan frozen with a separate administrator approval.', () => {
      this.loadAll();
      if (this.selectedRangePlan()?.id === plan.id) {
        this.selectedRangePlan.set({ ...plan, status: 'FROZEN' });
      }
    });
  }

  createAssortmentPlan(): void {
    if (!this.valid(this.assortmentForm)) return;
    const value = this.assortmentForm.getRawValue();
    this.perform(() => this.api.createAssortmentPlan({
      range_plan_id: value.range_plan_id,
      code: value.code.trim().toUpperCase(),
      channel_code: value.channel_code.trim().toUpperCase(),
      market_scope: value.market_scope.trim(),
      account_reference: value.account_reference.trim() || null,
      location_cluster_reference: value.location_cluster_reference.trim() || null,
    }), 'Channel assortment draft created against the frozen core range.', () => {
      this.assortmentForm.reset({
        range_plan_id: '', code: '', channel_code: '', market_scope: '',
        account_reference: '', location_cluster_reference: '',
      });
      this.loadAll();
    });
  }

  selectAssortment(plan: AssortmentPlanRecord): void {
    this.selectedAssortment.set(plan);
    this.assortmentLineForm.reset({
      range_line_id: '', variant_size_curve_json: '', planned_units: '0',
      launch_date: '', minimum_depth: '', maximum_depth: '',
    });
    this.api.listRangeLines(plan.range_plan_id).subscribe({
      next: (response) => {
        if (this.selectedAssortment()?.id === plan.id) this.rangeLines.set(response.data.data);
      },
      error: () => this.errorMessage.set('The frozen range lines could not be loaded for this assortment.'),
    });
    this.api.listAssortmentLines(plan.id).subscribe({
      next: (response) => {
        if (this.selectedAssortment()?.id === plan.id) this.assortmentLines.set(response.data.data);
      },
      error: () => this.errorMessage.set('Assortment lines could not be loaded.'),
    });
    this.clearMessages();
  }

  rangeLinesForSelectedAssortment(): RangeLineRecord[] {
    return this.rangeLines();
  }

  createAssortmentLine(): void {
    const plan = this.selectedAssortment();
    if (!plan || !this.valid(this.assortmentLineForm)) return;
    const value = this.assortmentLineForm.getRawValue();
    let curve: Record<string, unknown>;
    try {
      curve = this.parseObject(value.variant_size_curve_json, 'Variant and size curve');
    } catch (error) {
      this.errorMessage.set(error instanceof Error ? error.message : 'Variant/size curve JSON is invalid.');
      return;
    }
    this.perform(() => this.api.createAssortmentLine(plan.id, {
      range_line_id: value.range_line_id,
      variant_size_curve: curve,
      planned_units: Number(value.planned_units),
      launch_date: value.launch_date || null,
      minimum_depth: value.minimum_depth.trim() ? Number(value.minimum_depth) : null,
      maximum_depth: value.maximum_depth.trim() ? Number(value.maximum_depth) : null,
    }), 'Assortment depth added; core range intent remains unchanged.', () => this.selectAssortment(plan));
  }

  canApprove(createdBy: string | null): boolean {
    const role = this.session.activeTenant()?.membership_role;
    return (role === 'OWNER' || role === 'ADMIN') && !!this.session.user()?.id && createdBy !== this.session.user()?.id;
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

  private parseArray(value: string, label: string): unknown[] {
    const parsed: unknown = JSON.parse(value);
    if (!Array.isArray(parsed)) throw new Error(label + ' must be a JSON array.');
    return parsed;
  }

  private valid<T extends { invalid: boolean; markAllAsTouched: () => void }>(form: T): boolean {
    if (!form.invalid) return true;
    form.markAllAsTouched();
    return false;
  }

  private perform<T>(
    request: () => Observable<T>,
    success: string,
    afterSuccess: (response: T) => void,
  ): void {
    if (this.saving()) return;
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    this.saving.set(true);
    this.clearMessages();
    request().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, this.sequence)) return;
        this.saving.set(false);
        this.successMessage.set(success);
        afterSuccess(response);
      },
      error: (error: HttpErrorResponse) => {
        if (!this.isCurrent(tenantId, this.sequence)) return;
        this.saving.set(false);
        this.errorMessage.set(error.status === 422
          ? 'The request did not pass a workflow rule. Check the selected revision, dates, quantities and approval state.'
          : error.status === 403
            ? 'This action requires a different authorized tenant approver.'
            : error.status === 404
              ? 'The selected record is no longer available in this workspace.'
              : 'The request could not be completed. Check the connection and try again.');
      },
    });
  }

  private isCurrent(tenantId: string, requestId: number): boolean {
    return this.session.activeTenant()?.id === tenantId && this.sequence === requestId;
  }

  private clearMessages(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);
  }

  private clearData(): void {
    this.brands.set([]);
    this.seasons.set([]);
    this.collections.set([]);
    this.rangePlans.set([]);
    this.rangeLines.set([]);
    this.assortmentPlans.set([]);
    this.assortmentLines.set([]);
    this.selectedBrand.set(null);
    this.selectedCollection.set(null);
    this.selectedRangePlan.set(null);
    this.selectedAssortment.set(null);
    this.loading.set(false);
    this.saving.set(false);
    this.clearMessages();
  }
}
