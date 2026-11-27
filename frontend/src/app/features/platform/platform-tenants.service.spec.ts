import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { PlatformTenantsService } from './platform-tenants.service';

describe('PlatformTenantsService', () => {
  let service: PlatformTenantsService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });

    service = TestBed.inject(PlatformTenantsService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    http.verify();
  });

  it('reads the global tenant register through the same-origin platform API', () => {
    service.list().subscribe((response) => {
      expect(response.data).toHaveLength(1);
      expect(response.data[0]?.tenant_code).toBe('GARMENT-001');
      expect(response.data[0]?.tenant_status).toBe('ACTIVE');
    });

    const request = http.expectOne('/api/v1/platform/tenants');
    expect(request.request.method).toBe('GET');
    request.flush({
      data: [{
        tenant_id: 'tenant-1',
        tenant_code: 'GARMENT-001',
        tenant_name: 'Garment Tenant One',
        tenant_status: 'ACTIVE',
        plan_code: 'UNASSIGNED',
        entitlement_status: 'REVIEW_REQUIRED',
        valid_from: null,
        valid_to: null,
        lifecycle_version: 1,
      }],
    });
  });
});
