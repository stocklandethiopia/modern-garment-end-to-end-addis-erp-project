import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { AuthSessionService } from './auth-session.service';

describe('AuthSessionService platform context', () => {
  let service: AuthSessionService;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });

    service = TestBed.inject(AuthSessionService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    http.verify();
  });

  it('allows an active global platform operator to sign in without a tenant membership', () => {
    let memberships: unknown[] | undefined;
    service.login('platform@example.test', 'test-password').subscribe((result) => {
      memberships = result;
    });

    const login = http.expectOne('/api/v1/auth/tokens');
    expect(login.request.method).toBe('POST');
    login.flush({
      token_type: 'Bearer',
      access_token: 'test-access-token',
      user: { id: 'platform-user', name: 'Platform Operator', email: 'platform@example.test' },
    });

    const tenantList = http.expectOne('/api/v1/tenants');
    expect(tenantList.request.method).toBe('GET');
    tenantList.flush({ data: [] });

    const platformContext = http.expectOne('/api/v1/platform/context');
    expect(platformContext.request.method).toBe('GET');
    platformContext.flush({ data: { roles: ['PLATFORM_ADMIN'] } });

    expect(memberships).toEqual([]);
    expect(service.activeTenant()).toBeNull();
    expect(service.isPlatformOperator()).toBe(true);
    expect(service.canManagePlatform()).toBe(true);
  });

  it('does not confer global platform roles on an ordinary tenant member', () => {
    service.login('tenant@example.test', 'test-password').subscribe();

    http.expectOne('/api/v1/auth/tokens').flush({
      token_type: 'Bearer',
      access_token: 'tenant-access-token',
      user: { id: 'tenant-user', name: 'Tenant Member', email: 'tenant@example.test' },
    });
    http.expectOne('/api/v1/tenants').flush({
      data: [{
        id: 'tenant-1',
        code: 'TENANT-1',
        name: 'Tenant One',
        locale: 'en',
        timezone: 'Africa/Addis_Ababa',
        membership_role: 'OWNER',
      }],
    });
    http.expectOne('/api/v1/platform/context').flush({ data: { roles: [] } });

    expect(service.activeTenant()?.id).toBe('tenant-1');
    expect(service.isPlatformOperator()).toBe(false);
    expect(service.canManagePlatform()).toBe(false);
  });

  it('fails closed when the optional platform context endpoint is unavailable', () => {
    service.login('tenant@example.test', 'test-password').subscribe();

    http.expectOne('/api/v1/auth/tokens').flush({
      token_type: 'Bearer',
      access_token: 'tenant-access-token',
      user: { id: 'tenant-user', name: 'Tenant Member', email: 'tenant@example.test' },
    });
    http.expectOne('/api/v1/tenants').flush({
      data: [{
        id: 'tenant-1',
        code: 'TENANT-1',
        name: 'Tenant One',
        locale: 'en',
        timezone: 'Africa/Addis_Ababa',
        membership_role: 'OWNER',
      }],
    });
    http.expectOne('/api/v1/platform/context').flush(
      { message: 'Temporary platform context failure.' },
      { status: 503, statusText: 'Service Unavailable' },
    );

    expect(service.activeTenant()?.id).toBe('tenant-1');
    expect(service.isPlatformOperator()).toBe(false);
    expect(service.canManagePlatform()).toBe(false);
  });

  it('clears global platform roles on session reset', () => {
    service.clearSession();

    expect(service.platformRoles()).toEqual([]);
    expect(service.isPlatformOperator()).toBe(false);
  });
});
