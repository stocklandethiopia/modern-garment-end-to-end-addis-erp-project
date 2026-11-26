import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { PlatformOperatorsService } from './platform-operators.service';

describe('PlatformOperatorsService', () => {
  let service: PlatformOperatorsService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });

    service = TestBed.inject(PlatformOperatorsService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    http.verify();
  });

  it('uses the same-origin platform grants API for list, request, approval and revocation', () => {
    service.list().subscribe((response) => {
      expect(response.data).toHaveLength(0);
    });
    const list = http.expectOne('/api/v1/platform/operators/grants');
    expect(list.request.method).toBe('GET');
    list.flush({ data: [] });

    const input = {
      user_email: 'operator@example.test',
      role_code: 'PLATFORM_SUPPORT' as const,
      valid_from: '2026-11-27',
      valid_to: '2027-11-27',
      reason: 'Read-only platform support for incident triage.',
    };
    service.request(input).subscribe((response) => {
      expect(response.data.grant_status).toBe('PENDING');
    });
    const request = http.expectOne('/api/v1/platform/operators/grants');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual(input);
    request.flush({ data: { grant_id: 'grant-1', grant_status: 'PENDING' } });

    service.approve('grant-1', 'Independent approval after role review.').subscribe((response) => {
      expect(response.data.grant_status).toBe('ACTIVE');
    });
    const approval = http.expectOne('/api/v1/platform/operators/grants/grant-1/approve');
    expect(approval.request.method).toBe('POST');
    approval.flush({ data: { grant_id: 'grant-1', grant_status: 'ACTIVE' } });

    service.revoke('grant-1', 'Incident access is no longer required.').subscribe((response) => {
      expect(response.data.grant_status).toBe('REVOKED');
    });
    const revocation = http.expectOne('/api/v1/platform/operators/grants/grant-1/revoke');
    expect(revocation.request.method).toBe('POST');
    revocation.flush({ data: { grant_id: 'grant-1', grant_status: 'REVOKED' } });
  });
});
