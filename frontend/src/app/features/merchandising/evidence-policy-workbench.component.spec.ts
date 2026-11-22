import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { EvidencePolicyWorkbenchComponent } from './evidence-policy-workbench.component';

describe('EvidencePolicyWorkbenchComponent', () => {
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [EvidencePolicyWorkbenchComponent],
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    http.verify();
  });

  it('loads and displays tenant evidence policy versions', () => {
    const fixture = TestBed.createComponent(EvidencePolicyWorkbenchComponent);
    fixture.detectChanges();

    const request = http.expectOne('/api/v1/product/evidence-policies');
    expect(request.request.method).toBe('GET');
    request.flush({
      data: {
        data: [{
          id: 'policy-1',
          tenant_id: 'tenant-1',
          code: 'NEW_COLLECTION_ASSESSMENT',
          name: 'New collection assessment evidence',
          initiative_type: 'NEW_COLLECTION',
          stage: 'ASSESSMENT',
          target_market: 'Ethiopia',
          version_no: 1,
          effective_from: '2026-11-22',
          effective_to: null,
          status: 'APPROVED',
          change_reason: 'Initial approved evidence requirements.',
          created_by: 'user-1',
          approved_by: 'user-2',
          approved_at: '2026-11-22T10:00:00+03:00',
          created_at: '2026-11-22T09:00:00+03:00',
          rule_count: 2,
        }],
        current_page: 1,
        last_page: 1,
        total: 1,
      },
    });
    fixture.detectChanges();

    const text = fixture.nativeElement.textContent as string;
    expect(text).toContain('Evidence requirement policies');
    expect(text).toContain('NEW_COLLECTION_ASSESSMENT');
    expect(text).toContain('New collection assessment evidence');
    expect(text).toContain('APPROVED');
    expect(text).toContain('2 rules');
  });

  it('shows that tenant thresholds are configurable rather than global defaults', () => {
    const fixture = TestBed.createComponent(EvidencePolicyWorkbenchComponent);
    fixture.detectChanges();
    http.expectOne('/api/v1/product/evidence-policies').flush({
      data: { data: [], current_page: 1, last_page: 1, total: 0 },
    });
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('Tenant-configured thresholds — not universal defaults');
    expect(fixture.nativeElement.textContent).toContain('No evidence policies configured');
  });
});
