import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { ProductDevelopmentService } from './product-development.service';

describe('ProductDevelopmentService', () => {
  let service: ProductDevelopmentService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(ProductDevelopmentService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('lists product briefs and retrieves immutable brief versions and decisions', () => {
    service.listProductBriefs().subscribe((response) => {
      expect(response.data.data[0]?.status).toBe('ACCEPTED');
      expect(response.data.data[0]?.accepted_version).toBe(1);
    });
    const list = http.expectOne('/api/v1/product-briefs');
    expect(list.request.method).toBe('GET');
    list.flush({ data: { data: [{ id: 'brief-1', status: 'ACCEPTED', accepted_version: 1 }] } });

    service.getProductBrief('brief-1').subscribe((response) => {
      expect(response.versions[0]?.version_no).toBe(1);
      expect(response.decisions[0]?.decision).toBe('ACCEPT');
    });
    const detail = http.expectOne('/api/v1/product-briefs/brief-1');
    expect(detail.request.method).toBe('GET');
    detail.flush({
      data: { id: 'brief-1', status: 'ACCEPTED' },
      versions: [{ id: 'brief-version-1', version_no: 1 }],
      decisions: [{ id: 'decision-1', decision: 'ACCEPT' }],
      styles: [],
    });
  });

  it('submits and independently accepts a product brief through command endpoints', () => {
    service.submitProductBrief('brief-1').subscribe((response) => {
      expect(response.data.status).toBe('SUBMITTED_FOR_REVIEW');
    });
    const submit = http.expectOne('/api/v1/product-briefs/brief-1/submit');
    expect(submit.request.method).toBe('POST');
    submit.flush({ data: { id: 'brief-1', status: 'SUBMITTED_FOR_REVIEW' } });

    service.decideProductBrief('brief-1', {
      decision: 'ACCEPT',
      reason: 'The design team can now proceed from this brief.',
      actions: [],
    }).subscribe((response) => expect(response.data.status).toBe('ACCEPTED'));
    const decision = http.expectOne('/api/v1/product-briefs/brief-1/decisions');
    expect(decision.request.body.decision).toBe('ACCEPT');
    decision.flush({ data: { id: 'brief-1', status: 'ACCEPTED' } });
  });

  it('creates a style and appends a new immutable design revision', () => {
    service.createStyle({
      product_brief_id: 'brief-1',
      style_code: 'ST-001',
      name: 'Everyday overshirt',
      category_code: 'CASUALWEAR',
      design_direction: 'An everyday overshirt with a relaxed silhouette.',
      silhouette: 'Relaxed',
      fit_intent: 'Comfortable movement through shoulder and chest.',
      construction_intent: 'Durable seams and reinforced stress points.',
      material_direction: { fibre: 'Cotton blend' },
      colour_direction: { core: ['NAVY'] },
      target_size_range: { sizes: ['S', 'M', 'L'] },
      change_summary: 'Initial approved-brief style direction.',
    }).subscribe((response) => {
      expect(response.data.product_brief_version).toBe(1);
      expect(response.revision.revision_no).toBe(1);
    });
    const create = http.expectOne('/api/v1/styles');
    expect(create.request.method).toBe('POST');
    create.flush({ data: { id: 'style-1', product_brief_version: 1 }, revision: { id: 'rev-1', revision_no: 1 } });

    service.createStyleRevision('style-1', {
      design_direction: 'Refined overshirt with reduced seam bulk.',
      silhouette: 'Relaxed straight',
      fit_intent: 'Improve sleeve reach without restricting movement.',
      construction_intent: 'Reinforce pocket corners and stress points.',
      material_direction: { fibre: 'Cotton blend', weight: 'Midweight' },
      colour_direction: { core: ['NAVY', 'OLIVE'] },
      target_size_range: { sizes: ['S', 'M', 'L', 'XL'] },
      change_summary: 'Refine sleeve and shoulder balance.',
    }).subscribe((response) => expect(response.data.current_revision).toBe(2));
    const revision = http.expectOne('/api/v1/styles/style-1/revisions');
    expect(revision.request.method).toBe('POST');
    revision.flush({ data: { id: 'style-1', current_revision: 2 } });
  });
});
