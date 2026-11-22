import { DatePipe, JsonPipe } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import {
  ForecastDetail,
  ForecastItem,
  ForecastRun,
  LegalEntityOption,
  OtbPeriod,
  OtbPolicy,
  OtbScenario,
  PlanningWorkbenchService,
  PriceList,
  PriceListLine,
  Promotion,
} from './planning-workbench.service';

type PlanningTab = 'FORECAST' | 'OTB' | 'PRICING';

@Component({
  selector: 'app-planning-workbench',
  standalone: true,
  imports: [DatePipe, JsonPipe, ReactiveFormsModule],
  templateUrl: './planning-workbench.component.html',
  styleUrl: './planning-workbench.component.css',
})
export class PlanningWorkbenchComponent {
  private readonly api = inject(PlanningWorkbenchService);
  readonly session = inject(AuthSessionService);

  readonly activeTab = signal<PlanningTab>('FORECAST');
  readonly legalEntities = signal<LegalEntityOption[]>([]);
  readonly forecastRuns = signal<ForecastRun[]>([]);
  readonly selectedForecast = signal<ForecastDetail | null>(null);
  readonly policies = signal<OtbPolicy[]>([]);
  readonly periods = signal<OtbPeriod[]>([]);
  readonly lastScenario = signal<OtbScenario | null>(null);
  readonly priceLists = signal<PriceList[]>([]);
  readonly selectedPriceList = signal<PriceList | null>(null);
  readonly priceListLines = signal<PriceListLine[]>([]);
  readonly promotions = signal<Promotion[]>([]);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);

  private sequence = 0;

  readonly forecastForm = new FormGroup({
    grain: new FormControl('STYLE_WEEK', { nonNullable: true, validators: [Validators.required] }),
    method: new FormControl('MANUAL_CONSENSUS', { nonNullable: true, validators: [Validators.required] }),
    horizon: new FormControl('1_WEEK', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    scenario_code: new FormControl('BASELINE', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    forecast_start: new FormControl('2027-01-04', { nonNullable: true, validators: [Validators.required] }),
    forecast_end: new FormControl('2027-01-10', { nonNullable: true, validators: [Validators.required] }),
    style_reference: new FormControl('STYLE-001', { nonNullable: true, validators: [Validators.required, Validators.maxLength(120)] }),
    channel_code: new FormControl('OWN_RETAIL', { nonNullable: true, validators: [Validators.required, Validators.maxLength(60)] }),
    time_bucket: new FormControl('2027-01-04', { nonNullable: true, validators: [Validators.required] }),
    forecast_quantity: new FormControl('100.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    forecast_value: new FormControl('150000.0000', { nonNullable: true }),
    confidence_low: new FormControl('85.0000', { nonNullable: true }),
    confidence_high: new FormControl('120.0000', { nonNullable: true }),
  });

  readonly adjustmentForm = new FormGroup({
    forecast_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    baseline_value: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    override_value: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(5), Validators.maxLength(500)] }),
    evidence_reference: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(1000)] }),
  });

  readonly otbPolicyForm = new FormGroup({
    legal_entity_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    code: new FormControl('OTB-RETAIL-2027', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    budget_basis: new FormControl<'RETAIL' | 'COST'>('RETAIL', { nonNullable: true }),
    valuation_basis: new FormControl<'RETAIL' | 'COST'>('RETAIL', { nonNullable: true }),
    effective_from: new FormControl('2027-01-01', { nonNullable: true, validators: [Validators.required] }),
    effective_to: new FormControl('2027-12-31', { nonNullable: true }),
    commitments_mode: new FormControl<'ADDITIONAL' | 'INCLUDED_IN_ON_ORDER'>('ADDITIONAL', { nonNullable: true }),
    actual_receipts_reduce_otb: new FormControl(true, { nonNullable: true }),
  });

  readonly otbPeriodForm = new FormGroup({
    otb_policy_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    period_start: new FormControl('2027-01-01', { nonNullable: true, validators: [Validators.required] }),
    period_end: new FormControl('2027-01-31', { nonNullable: true, validators: [Validators.required] }),
    currency: new FormControl('ETB', { nonNullable: true, validators: [Validators.required, Validators.maxLength(3)] }),
    beginning_inventory: new FormControl('500.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    planned_sales: new FormControl('800.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    planned_markdowns: new FormControl('50.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    planned_ending_inventory: new FormControl('600.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    on_order_purchases: new FormControl('200.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    commitments: new FormControl('75.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    actual_receipts: new FormControl('25.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
  });

  readonly otbScenarioForm = new FormGroup({
    period_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    planned_sales: new FormControl('', { nonNullable: true, validators: [Validators.min(0)] }),
  });

  readonly priceListForm = new FormGroup({
    legal_entity_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    code: new FormControl('OWN-RETAIL-2027', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    channel_code: new FormControl('OWN_RETAIL', { nonNullable: true, validators: [Validators.required, Validators.maxLength(60)] }),
    market_scope: new FormControl('ADDIS_ABABA', { nonNullable: true, validators: [Validators.required, Validators.maxLength(160)] }),
    account_reference: new FormControl('', { nonNullable: true }),
    currency: new FormControl('ETB', { nonNullable: true, validators: [Validators.required, Validators.maxLength(3)] }),
    effective_from: new FormControl('2027-01-01T00:00', { nonNullable: true, validators: [Validators.required] }),
    effective_to: new FormControl('2027-12-31T23:59', { nonNullable: true }),
  });

  readonly priceLineForm = new FormGroup({
    product_reference: new FormControl('STYLE-001', { nonNullable: true, validators: [Validators.required, Validators.maxLength(120)] }),
    list_price: new FormControl('1500.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0)] }),
    wholesale_price: new FormControl('1000.0000', { nonNullable: true }),
    price_floor: new FormControl('900.0000', { nonNullable: true }),
    cost_basis: new FormControl('700.0000', { nonNullable: true }),
  });

  readonly promotionForm = new FormGroup({
    price_list_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    code: new FormControl('SUMMER-10', { nonNullable: true, validators: [Validators.required, Validators.maxLength(80)] }),
    promotion_type: new FormControl<'PERCENTAGE' | 'FIXED_AMOUNT'>('PERCENTAGE', { nonNullable: true }),
    discount_value: new FormControl('10.0000', { nonNullable: true, validators: [Validators.required, Validators.min(0.0001)] }),
    stack_policy: new FormControl('NO_STACK', { nonNullable: true }),
    starts_at: new FormControl('2027-06-01T00:00', { nonNullable: true, validators: [Validators.required] }),
    ends_at: new FormControl('2027-06-30T23:59', { nonNullable: true, validators: [Validators.required] }),
    margin_floor: new FormControl('15.0000', { nonNullable: true }),
    budget_amount: new FormControl('50000.0000', { nonNullable: true }),
    funded_by: new FormControl('BRAND', { nonNullable: true }),
  });

  readonly eligibilityForm = new FormGroup({
    promotion_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    scope_type: new FormControl('CHANNEL', { nonNullable: true, validators: [Validators.required] }),
    scope_reference: new FormControl('OWN_RETAIL', { nonNullable: true, validators: [Validators.required, Validators.maxLength(160)] }),
    is_exclusion: new FormControl(false, { nonNullable: true }),
  });

  private readonly tenantEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    const requestId = ++this.sequence;
    this.clearData();

    if (tenantId) {
      this.loadAll(tenantId, requestId);
    }
  });

  setTab(tab: PlanningTab): void {
    this.activeTab.set(tab);
    this.clearMessages();
  }

  loadAll(tenantId = this.session.activeTenant()?.id, requestId = this.sequence): void {
    if (!tenantId) return;
    this.loading.set(true);
    this.api.listLegalEntities().subscribe({
      next: (response) => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.legalEntities.set(response.data);
        if (response.data.length > 0) {
          const firstId = response.data[0]?.id ?? '';
          if (!this.otbPolicyForm.controls.legal_entity_id.value) this.otbPolicyForm.patchValue({ legal_entity_id: firstId });
          if (!this.priceListForm.controls.legal_entity_id.value) this.priceListForm.patchValue({ legal_entity_id: firstId });
        }
        this.loading.set(false);
      },
      error: () => {
        if (!this.isCurrent(tenantId, requestId)) return;
        this.loading.set(false);
        this.errorMessage.set('Legal entities could not be loaded. Complete enterprise setup first.');
      },
    });

    this.api.listForecastRuns().subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId)) this.forecastRuns.set(response.data.data);
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId)) this.forecastRuns.set([]);
      },
    });

    this.api.listOtbPolicies().subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId)) this.policies.set(response.data.data);
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId)) this.policies.set([]);
      },
    });

    this.api.listOtbPeriods().subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId)) this.periods.set(response.data.data);
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId)) this.periods.set([]);
      },
    });

    this.api.listPriceLists().subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId)) this.priceLists.set(response.data.data);
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId)) this.priceLists.set([]);
      },
    });

    this.api.listPromotions().subscribe({
      next: (response) => {
        if (this.isCurrent(tenantId, requestId)) this.promotions.set(response.data.data);
      },
      error: () => {
        if (this.isCurrent(tenantId, requestId)) this.promotions.set([]);
      },
    });
  }

  openForecastRun(run: ForecastRun): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    this.api.getForecastRun(run.id).subscribe({
      next: (detail) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.selectedForecast.set(detail);
      },
      error: () => this.errorMessage.set('Forecast detail could not be loaded.'),
    });
  }

  createForecast(): void {
    if (!this.valid(this.forecastForm)) return;
    const value = this.forecastForm.getRawValue();
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;

    this.perform(() => this.api.publishForecast({
      grain: value.grain,
      method: value.method,
      horizon: value.horizon.trim(),
      scenario_code: value.scenario_code.trim(),
      forecast_start: value.forecast_start,
      forecast_end: value.forecast_end,
      assumptions: { source: 'MANUAL_CONSENSUS', captured_in: 'PLANNING_WORKBENCH' },
      forecasts: [{
        dimension_refs: {
          style: value.style_reference.trim(),
          channel: value.channel_code.trim(),
        },
        time_bucket: value.time_bucket,
        forecast_quantity: value.forecast_quantity,
        forecast_value: value.forecast_value.trim() || null,
        currency: value.forecast_value.trim() ? 'ETB' : null,
        confidence_low: value.confidence_low.trim() || null,
        confidence_high: value.confidence_high.trim() || null,
      }],
    }), 'Forecast run published as a versioned planning input.', () => {
      this.selectedForecast.set(null);
      this.loadAll();
    });
  }

  prepareAdjustment(forecast: ForecastItem): void {
    this.adjustmentForm.patchValue({
      forecast_id: forecast.id,
      baseline_value: forecast.forecast_quantity,
      override_value: forecast.forecast_quantity,
    });
  }

  createAdjustment(): void {
    if (!this.valid(this.adjustmentForm)) return;
    const value = this.adjustmentForm.getRawValue();
    this.perform(() => this.api.createForecastAdjustment(value.forecast_id, {
      baseline_value: value.baseline_value,
      override_value: value.override_value,
      reason: value.reason.trim(),
      evidence_reference: value.evidence_reference.trim() || null,
    }), 'Forecast override saved pending independent approval.', () => {
      const runId = this.selectedForecast()?.data.id;
      if (runId) {
        const run = this.forecastRuns().find((item) => item.id === runId);
        if (run) this.openForecastRun(run);
      }
    });
  }

  approveAdjustment(adjustmentId: string): void {
    this.perform(() => this.api.approveForecastAdjustment(adjustmentId), 'Forecast adjustment approved; original forecast remains unchanged.', () => {
      const run = this.forecastRuns().find((item) => item.id === this.selectedForecast()?.data.id);
      if (run) this.openForecastRun(run);
    });
  }

  createOtbPolicy(): void {
    if (!this.valid(this.otbPolicyForm)) return;
    const value = this.otbPolicyForm.getRawValue();
    this.perform(() => this.api.createOtbPolicy({
      legal_entity_id: value.legal_entity_id,
      code: value.code.trim(),
      budget_basis: value.budget_basis,
      valuation_basis: value.valuation_basis,
      effective_from: value.effective_from,
      effective_to: value.effective_to || null,
      inclusion_rules: {
        commitments_mode: value.commitments_mode,
        actual_receipts_reduce_otb: value.actual_receipts_reduce_otb,
      },
      approval_thresholds: {},
    }), 'Draft OTB policy created. A different authorized user must activate it.', () => this.loadAll());
  }

  activateOtbPolicy(policyId: string): void {
    this.perform(() => this.api.activateOtbPolicy(policyId), 'OTB policy independently activated for its effective period.', () => this.loadAll());
  }

  createOtbPeriod(): void {
    if (!this.valid(this.otbPeriodForm)) return;
    const value = this.otbPeriodForm.getRawValue();
    this.perform(() => this.api.createOtbPeriod({
      otb_policy_id: value.otb_policy_id,
      period_start: value.period_start,
      period_end: value.period_end,
      currency: value.currency.toUpperCase(),
      beginning_inventory: value.beginning_inventory,
      planned_sales: value.planned_sales,
      planned_markdowns: value.planned_markdowns,
      planned_ending_inventory: value.planned_ending_inventory,
      on_order_purchases: value.on_order_purchases,
      commitments: value.commitments,
      actual_receipts: value.actual_receipts,
    }), 'OTB period calculated from the active policy and preserved valuation basis.', () => this.loadAll());
  }

  selectOtbPeriod(period: OtbPeriod): void {
    this.otbScenarioForm.patchValue({ period_id: period.id });
  }

  simulateOtb(): void {
    if (!this.valid(this.otbScenarioForm)) return;
    const value = this.otbScenarioForm.getRawValue();
    const assumptions: Record<string, string> = {};
    if (value.planned_sales.trim()) assumptions['planned_sales'] = value.planned_sales.trim();

    this.perform(() => this.api.simulateOtb(value.period_id, { assumptions }), 'OTB scenario saved as a non-posting simulation.', (scenario) => {
      this.lastScenario.set(scenario);
      this.loadAll();
    });
  }

  approveScenario(scenarioId: string): void {
    this.perform(() => this.api.approveOtbScenario(scenarioId), 'Scenario approved for planning use only; no purchase order or journal was created.', () => this.loadAll());
  }

  scenarioResult(): { available_budget: string; interpretation: string; formula_version: string; budget_basis: string; valuation_basis: string; warning: string } | null {
    const result = this.lastScenario()?.result;

    if (!result) return null;
    if (typeof result === 'string') {
      try {
        const parsed = JSON.parse(result) as {
          calculation?: { available_budget: string; interpretation: string };
          formula_version?: string;
          budget_basis?: string;
          valuation_basis?: string;
          warning?: string;
        };

        if (!parsed.calculation) return null;

        return {
          available_budget: parsed.calculation.available_budget,
          interpretation: parsed.calculation.interpretation,
          formula_version: parsed.formula_version ?? '',
          budget_basis: parsed.budget_basis ?? '',
          valuation_basis: parsed.valuation_basis ?? '',
          warning: parsed.warning ?? '',
        };
      } catch {
        return null;
      }
    }

    return {
      available_budget: result.calculation.available_budget,
      interpretation: result.calculation.interpretation,
      formula_version: result.formula_version,
      budget_basis: result.budget_basis,
      valuation_basis: result.valuation_basis,
      warning: result.warning,
    };
  }

  createPriceList(): void {
    if (!this.valid(this.priceListForm)) return;
    const value = this.priceListForm.getRawValue();
    this.perform(() => this.api.createPriceList({
      legal_entity_id: value.legal_entity_id,
      code: value.code.trim(),
      channel_code: value.channel_code.trim().toUpperCase(),
      market_scope: value.market_scope.trim(),
      account_reference: value.account_reference.trim() || null,
      currency: value.currency.trim().toUpperCase(),
      effective_from: new Date(value.effective_from).toISOString(),
      effective_to: value.effective_to.trim() ? new Date(value.effective_to).toISOString() : null,
    }), 'Draft price-list version created.', () => this.loadAll());
  }

  selectPriceList(priceList: PriceList): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    this.selectedPriceList.set(priceList);
    this.priceLineForm.patchValue({ product_reference: 'STYLE-001' });
    this.api.listPriceListLines(priceList.id).subscribe({
      next: (response) => {
        if (this.session.activeTenant()?.id === tenantId) this.priceListLines.set(response.data.data);
      },
      error: () => this.errorMessage.set('Price-list lines could not be loaded.'),
    });
  }

  addPriceListLine(): void {
    const priceList = this.selectedPriceList();
    if (!priceList || !this.valid(this.priceLineForm)) return;
    const value = this.priceLineForm.getRawValue();
    this.perform(() => this.api.addPriceListLine(priceList.id, {
      product_reference: value.product_reference.trim(),
      list_price: value.list_price,
      wholesale_price: value.wholesale_price.trim() || null,
      price_floor: value.price_floor.trim() || null,
      cost_basis: value.cost_basis.trim() || null,
    }), 'Price line added to the draft price list.', () => this.selectPriceList(priceList));
  }

  publishPriceList(priceListId: string): void {
    this.perform(() => this.api.publishPriceList(priceListId), 'Price list published with independent approval.', () => {
      this.selectedPriceList.set(null);
      this.priceListLines.set([]);
      this.loadAll();
    });
  }

  createPromotion(): void {
    if (!this.valid(this.promotionForm)) return;
    const value = this.promotionForm.getRawValue();
    this.perform(() => this.api.createPromotion({
      price_list_id: value.price_list_id,
      code: value.code.trim(),
      promotion_type: value.promotion_type,
      discount_value: value.discount_value,
      stack_policy: value.stack_policy,
      starts_at: new Date(value.starts_at).toISOString(),
      ends_at: new Date(value.ends_at).toISOString(),
      margin_floor: value.margin_floor.trim() || null,
      budget_amount: value.budget_amount.trim() || null,
      funded_by: value.funded_by.trim() || null,
    }), 'Promotion created as draft; it needs explicit eligibility and independent approval.', () => this.loadAll());
  }

  selectPromotion(promotion: Promotion): void {
    this.eligibilityForm.patchValue({ promotion_id: promotion.id });
  }

  addPromotionEligibility(): void {
    if (!this.valid(this.eligibilityForm)) return;
    const value = this.eligibilityForm.getRawValue();
    this.perform(() => this.api.addPromotionEligibility(value.promotion_id, {
      scope_type: value.scope_type,
      scope_reference: value.scope_reference.trim(),
      is_exclusion: value.is_exclusion,
    }), 'Promotion eligibility scope recorded.', () => this.loadAll());
  }

  approvePromotion(promotionId: string): void {
    this.perform(() => this.api.approvePromotion(promotionId), 'Promotion approved for its scoped effective period.', () => this.loadAll());
  }

  clearMessages(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);
  }

  private valid<T extends { invalid: boolean; markAllAsTouched: () => void }>(form: T): boolean {
    if (!form.invalid) return true;
    form.markAllAsTouched();
    return false;
  }

  private perform<T>(
    request: () => import('rxjs').Observable<T>,
    success: string,
    afterSuccess: (result: T) => void,
  ): void {
    if (this.saving()) return;
    this.saving.set(true);
    this.clearMessages();
    const tenantId = this.session.activeTenant()?.id;

    request().subscribe({
      next: () => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.saving.set(false);
        this.successMessage.set(success);
        afterSuccess(response);
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.saving.set(false);
        this.errorMessage.set(error.status === 422
          ? 'The request did not pass a business rule or approval gate. Review the inputs and current workflow state.'
          : error.status === 403
            ? 'This action requires a different authorized approver.'
            : 'The request could not be completed. Check the connection and retry.');
      },
    });
  }

  private clearData(): void {
    this.saving.set(false);
    this.legalEntities.set([]);
    this.forecastRuns.set([]);
    this.selectedForecast.set(null);
    this.policies.set([]);
    this.periods.set([]);
    this.lastScenario.set(null);
    this.priceLists.set([]);
    this.selectedPriceList.set(null);
    this.priceListLines.set([]);
    this.promotions.set([]);
    this.loading.set(false);
    this.clearMessages();
  }

  private isCurrent(tenantId: string, requestId: number): boolean {
    return this.session.activeTenant()?.id === tenantId && this.sequence === requestId;
  }
}
