import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { PlanningWorkbenchService } from './planning-workbench.service';

describe('PlanningWorkbenchService', () => {
  let service: PlanningWorkbenchService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    });

    service = TestBed.inject(PlanningWorkbenchService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    http.verify();
  });

  it('publishes a versioned forecast run with source dimensions and assumptions', () => {
    const input = {
      grain: 'STYLE_WEEK',
      method: 'MANUAL_CONSENSUS',
      scenario_code: 'BASELINE',
      forecast_start: '2027-01-04',
      forecast_end: '2027-01-10',
      assumptions: { source: 'MANUAL_CONSENSUS' },
      forecasts: [{
        dimension_refs: { style: 'STYLE-001', channel: 'OWN_RETAIL' },
        time_bucket: '2027-01-04',
        forecast_quantity: '100.0000',
      }],
    };

    service.publishForecast(input).subscribe((response) => {
      expect(response.data.id).toBe('forecast-run-1');
      expect(response.row_count).toBe(1);
    });

    const request = http.expectOne('/api/v1/product/forecast-runs');
    expect(request.request.method).toBe('POST');
    expect(request.request.body.forecasts[0].dimension_refs.style).toBe('STYLE-001');
    request.flush({
      data: { id: 'forecast-run-1', status: 'PUBLISHED' },
      row_count: 1,
    });
  });

  it('loads published forecast rows and independent adjustments', () => {
    service.getForecastRun('run-1').subscribe((response) => {
      expect(response.forecasts).toHaveLength(1);
      expect(response.adjustments[0]?.is_approved).toBe(true);
    });

    const request = http.expectOne('/api/v1/product/forecast-runs/run-1');
    expect(request.request.method).toBe('GET');
    request.flush({
      data: { id: 'run-1', status: 'PUBLISHED' },
      forecasts: [{ id: 'forecast-row-1', forecast_quantity: '90.0000' }],
      adjustments: [{ id: 'adjustment-1', is_approved: true }],
    });
  });

  it('creates and independently approves an OTB policy', () => {
    service.createOtbPolicy({ code: 'OTB-2027' }).subscribe((response) => {
      expect(response.data.code).toBe('OTB-2027');
    });

    const create = http.expectOne('/api/v1/product/otb-policies');
    expect(create.request.method).toBe('POST');
    create.flush({ data: { id: 'policy-1', code: 'OTB-2027', status: 'DRAFT' } });

    service.activateOtbPolicy('policy-1').subscribe((response) => {
      expect(response.data.status).toBe('ACTIVE');
    });

    const activate = http.expectOne('/api/v1/product/otb-policies/policy-1/activate');
    expect(activate.request.method).toBe('POST');
    activate.flush({ data: { id: 'policy-1', code: 'OTB-2027', status: 'ACTIVE' } });
  });

  it('simulates OTB without creating a procurement transaction', () => {
    service.simulateOtb('period-1', { assumptions: { planned_sales: '1200.0000' } }).subscribe((response) => {
      expect(response.data.id).toBe('scenario-1');
    });

    const request = http.expectOne('/api/v1/product/otb-periods/period-1/scenarios');
    expect(request.request.method).toBe('POST');
    expect(request.request.body.assumptions.planned_sales).toBe('1200.0000');
    request.flush({ data: { id: 'scenario-1', status: 'SIMULATED' } });
  });

  it('publishes a price list and submits promotion eligibility separately', () => {
    service.publishPriceList('price-list-1').subscribe((response) => {
      expect(response.data.status).toBe('PUBLISHED');
    });

    const publish = http.expectOne('/api/v1/product/price-lists/price-list-1/publish');
    expect(publish.request.method).toBe('POST');
    publish.flush({ data: { id: 'price-list-1', status: 'PUBLISHED' } });

    service.addPromotionEligibility('promotion-1', {
      scope_type: 'CHANNEL',
      scope_reference: 'OWN_RETAIL',
      is_exclusion: false,
    }).subscribe((response) => {
      expect(response.data['scope_type']).toBe('CHANNEL');
    });

    const eligibility = http.expectOne('/api/v1/product/promotions/promotion-1/eligibility');
    expect(eligibility.request.method).toBe('POST');
    eligibility.flush({ data: { id: 'eligibility-1', scope_type: 'CHANNEL' } });
  });
});
