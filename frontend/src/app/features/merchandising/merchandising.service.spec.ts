import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { MerchandisingService } from './merchandising.service';

describe('MerchandisingService', () => {
  let service: MerchandisingService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
      ],
    });

    service = TestBed.inject(MerchandisingService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    http.verify();
  });

  it('loads initiatives from the tenant-scoped product API', () => {
    service.listInitiatives().subscribe((response) => {
      expect(response.data).toHaveLength(1);
      expect(response.data[0]?.code).toBe('SS27-URBAN-01');
    });

    const request = http.expectOne('/api/v1/product/initiatives');
    expect(request.request.method).toBe('GET');
    request.flush({
      data: [{
        id: 'initiative-1',
        code: 'SS27-URBAN-01',
        title: 'Urban everyday collection',
        status: 'DRAFT',
        current_version: 1,
      }],
      current_page: 1,
      last_page: 1,
      total: 1,
    });
  });

  it('filters captured market insights by initiative without changing the source endpoint', () => {
    service.listMarketInsights('initiative-1').subscribe((response) => {
      expect(response.data).toHaveLength(0);
    });

    const request = http.expectOne((candidate) =>
      candidate.url === '/api/v1/product/market-insights'
      && candidate.params.get('initiative_id') === 'initiative-1',
    );
    expect(request.request.method).toBe('GET');
    request.flush({ data: [], current_page: 1, last_page: 1, total: 0 });
  });

  it('posts source provenance independently from the market insight record', () => {
    service.createInsightSource({
      source_type: 'STORE_OBSERVATION',
      source_name: 'Addis store visit notes',
      source_reference: 'internal-research://store/visit/01',
      capture_method: 'STRUCTURED_STORE_OBSERVATION',
      geography: 'Addis Ababa',
      reliability_score: 0.85,
      permitted_use_basis: 'Internal merchandising research',
      limitations: 'Small convenience sample',
    }).subscribe((response) => {
      expect(response.data.id).toBe('source-1');
    });

    const request = http.expectOne('/api/v1/product/insight-sources');
    expect(request.request.method).toBe('POST');
    expect(request.request.body.source_reference).toBe('internal-research://store/visit/01');
    request.flush({ data: { id: 'source-1', source_name: 'Addis store visit notes' } });
  });
});
