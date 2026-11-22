import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { MerchandisingDashboardComponent } from './merchandising-dashboard.component';

describe('MerchandisingDashboardComponent', () => {
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [MerchandisingDashboardComponent],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    });

    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    http.verify();
  });

  it('loads tenant-scoped planning indicators and labels unavailable measures', () => {
    const fixture = TestBed.createComponent(MerchandisingDashboardComponent);
    fixture.detectChanges();

    const request = http.expectOne('/api/v1/product/merchandising-dashboard');
    expect(request.request.method).toBe('GET');
    request.flush({
      data: {
        generated_at: '2026-11-22T12:00:00+03:00',
        tenant_role: 'OWNER',
        initiative_funnel: [{ status: 'ASSESSMENT', count: 2 }],
        kpis: [{
          key: 'range_brief_readiness',
          label: 'Frozen range lines with current accepted briefs',
          status: 'AVAILABLE',
          value: 75,
          unit: 'percent',
          numerator: 3,
          denominator: 4,
          definition: 'Accepted current briefs divided by frozen range lines.',
          sources: ['range_plans', 'range_lines', 'product_briefs'],
        }],
        range_lifecycle_mix: [{
          lifecycle_type: 'NEW',
          range_line_count: 4,
          planned_units: 1200,
        }],
        range_category_depth: [{
          category_code: 'CASUALWEAR',
          target_tier: 'GOOD',
          range_line_count: 4,
          planned_units: 1200,
        }],
        evidence_register: {
          source_count: 2,
          insight_count: 5,
          sufficiency_status: 'UNAVAILABLE',
          sufficiency_reason: 'No evidence requirement catalogue is configured.',
        },
        work_queue: {
          approval_scope: 'TENANT_APPROVALS',
          approvals: [],
          my_work: [],
        },
        unavailable_kpis: [{
          key: 'sell_through',
          reason: 'Posted sales actuals are not available yet.',
          dependency: 'WP-12 commerce actuals',
        }],
      },
    });
    fixture.detectChanges();

    const text = fixture.nativeElement.textContent as string;
    expect(text).toContain('Merchandising decision dashboard');
    expect(text).toContain('Frozen range lines with current accepted briefs');
    expect(text).toContain('75.00%');
    expect(text).toContain('Evidence sufficiency is not calculated.');
    expect(text).toContain('sell through');
    expect(fixture.componentInstance.dashboard()?.tenant_role).toBe('OWNER');
  });
});
