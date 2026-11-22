import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { RangePlanningService } from './range-planning.service';

describe('RangePlanningService', () => {
  let service: RangePlanningService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(RangePlanningService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('loads tenant brand strategy state and approval metadata', () => {
    service.listBrands().subscribe((response) => {
      expect(response.data.data[0]?.latest_strategy_version_id).toBe('strategy-1');
      expect(response.data.data[0]?.current_strategy_status).toBe('PENDING_APPROVAL');
    });
    const request = http.expectOne('/api/v1/product/brands');
    expect(request.request.method).toBe('GET');
    request.flush({ data: { data: [{ id: 'brand-1', current_strategy_status: 'PENDING_APPROVAL', latest_strategy_version_id: 'strategy-1' }] } });
  });

  it('creates a collection version as pending and approves it through a separate command', () => {
    service.createCollectionVersion('collection-1', {
      story: 'Collection story and seasonal direction.',
      target_consumer: 'Urban adults',
      category_architecture: ['CASUALWEAR'],
      price_margin_envelope: { currency: 'ETB' },
      budget_amount: null,
      budget_currency: null,
      delivery_start: null,
      delivery_end: null,
      change_reason: 'Initial commercial architecture.',
    }).subscribe((response) => expect(response.data.status).toBe('PENDING_APPROVAL'));

    const create = http.expectOne('/api/v1/product/collections/collection-1/versions');
    expect(create.request.method).toBe('POST');
    create.flush({ data: { id: 'version-1', version_no: 1, status: 'PENDING_APPROVAL' } });

    service.approveCollectionVersion('collection-1', 'version-1').subscribe((response) => {
      expect(response.data.status).toBe('APPROVED');
    });
    const approve = http.expectOne('/api/v1/product/collections/collection-1/versions/version-1/approve');
    expect(approve.request.method).toBe('POST');
    approve.flush({ data: { id: 'collection-1', status: 'APPROVED' } });
  });

  it('reads persisted range and channel assortment rows using tenant-scoped routes', () => {
    service.listRangeLines('range-1').subscribe((response) => expect(response.data.data[0]?.line_code).toBe('STYLE-01'));
    const range = http.expectOne('/api/v1/product/range-plans/range-1/lines');
    expect(range.request.method).toBe('GET');
    range.flush({ data: { data: [{ id: 'line-1', line_code: 'STYLE-01' }] } });

    service.listAssortmentLines('assortment-1').subscribe((response) => expect(response.data.data[0]?.planned_units).toBe(75));
    const assortment = http.expectOne('/api/v1/product/assortment-plans/assortment-1/lines');
    expect(assortment.request.method).toBe('GET');
    assortment.flush({ data: { data: [{ id: 'allocation-1', planned_units: 75 }] } });
  });
});
