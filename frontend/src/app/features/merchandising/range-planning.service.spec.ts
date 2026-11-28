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
      expect(response.data.data[0]?.current_strategy_status).toBe('NOT_CREATED');
      expect(response.data.data[0]?.latest_strategy_status).toBe('PENDING_APPROVAL');
    });
    const request = http.expectOne('/api/v1/product/brands');
    expect(request.request.method).toBe('GET');
    request.flush({ data: { data: [{ id: 'brand-1', current_strategy_status: 'NOT_CREATED', latest_strategy_status: 'PENDING_APPROVAL', latest_strategy_version_id: 'strategy-1' }] } });
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

  it('loads published price-list and account-specific assortment terms', () => {
    service.listPublishedPriceLists().subscribe((response) => {
      expect(response.data.data[0]?.account_reference).toBe('DISTRIBUTOR-001');
      expect(response.data.data[0]?.status).toBe('PUBLISHED');
    });
    const lists = http.expectOne('/api/v1/product/price-lists');
    expect(lists.request.method).toBe('GET');
    lists.flush({
      data: {
        data: [{
          id: 'price-list-1',
          code: 'WHOLESALE-ET',
          version_no: 2,
          channel_code: 'WHOLESALE',
          market_scope: 'ETHIOPIA',
          account_reference: 'DISTRIBUTOR-001',
          currency: 'ETB',
          effective_from: '2026-11-22T00:00:00Z',
          effective_to: '2027-12-31T23:59:59Z',
          status: 'PUBLISHED',
        }],
        current_page: 1,
        last_page: 1,
        total: 1,
      },
    });

    service.listAssortmentAccountTerms('assortment-1').subscribe((response) => {
      expect(response.data.data[0]?.price_list_id).toBe('price-list-1');
      expect(response.data.data[0]?.status).toBe('APPROVED');
    });
    const terms = http.expectOne('/api/v1/product/assortment-plans/assortment-1/account-terms');
    expect(terms.request.method).toBe('GET');
    terms.flush({
      data: {
        data: [{
          id: 'terms-1',
          assortment_plan_id: 'assortment-1',
          price_list_id: 'price-list-1',
          price_list_code: 'WHOLESALE-ET',
          price_list_currency: 'ETB',
          version_no: 1,
          channel_code: 'WHOLESALE',
          market_scope: 'ETHIOPIA',
          account_reference: 'DISTRIBUTOR-001',
          account_tier: 'GOLD',
          commercial_terms_reference: 'CONTRACT-001',
          payment_terms_reference: 'PAYMENT-POLICY-NET30',
          delivery_terms_reference: 'DELIVERY-POLICY-ADDIS',
          returns_policy_reference: null,
          currency: 'ETB',
          minimum_order_units: 240,
          order_multiple_units: 24,
          effective_from: '2027-01-01',
          effective_to: '2027-06-30',
          change_reason: 'Initial account terms.',
          status: 'APPROVED',
          created_by: 'user-1',
          submitted_by: 'user-1',
          decided_by: 'user-2',
          decision_reason: 'Independently approved.',
          created_at: '2026-11-22T12:00:00+03:00',
        }],
        current_page: 1,
        last_page: 1,
        total: 1,
      },
    });
  });

  it('submits and independently approves an account terms version', () => {
    service.submitAssortmentAccountTerms('assortment-1', 'terms-1').subscribe((response) => {
      expect(response.data.status).toBe('PENDING_APPROVAL');
    });
    const submit = http.expectOne('/api/v1/product/assortment-plans/assortment-1/account-terms/terms-1/submit');
    expect(submit.request.method).toBe('POST');
    submit.flush({ data: { id: 'terms-1', status: 'PENDING_APPROVAL' } });

    service.decideAssortmentAccountTerms(
      'assortment-1',
      'terms-1',
      'APPROVE',
      'The account and published price list have been independently reviewed.',
    ).subscribe((response) => {
      expect(response.data.status).toBe('APPROVED');
    });
    const decide = http.expectOne('/api/v1/product/assortment-plans/assortment-1/account-terms/terms-1/decisions');
    expect(decide.request.method).toBe('POST');
    expect(decide.request.body).toEqual({
      decision: 'APPROVE',
      reason: 'The account and published price list have been independently reviewed.',
    });
    decide.flush({ data: { id: 'terms-1', status: 'APPROVED' } });
  });

});
