import { HttpClient } from '@angular/common/http';
import { Injectable, inject, signal } from '@angular/core';
import { Observable, catchError, finalize, map, of, switchMap, tap, throwError } from 'rxjs';

export interface AuthenticatedUser {
  id: string;
  name: string;
  email: string;
}

export interface TenantMembership {
  id: string;
  code: string;
  name: string;
  locale: string;
  timezone: string;
  membership_role: string;
}

interface TokenResponse {
  token_type: 'Bearer';
  access_token: string;
  user: AuthenticatedUser;
}

interface TenantListResponse {
  data: TenantMembership[];
}

interface PlatformContextResponse {
  data: {
    roles: string[];
  };
}

@Injectable({ providedIn: 'root' })
export class AuthSessionService {
  private readonly http = inject(HttpClient);

  private readonly tokenState = signal<string | null>(null);
  readonly accessToken = this.tokenState.asReadonly();

  private readonly userState = signal<AuthenticatedUser | null>(null);
  readonly user = this.userState.asReadonly();

  private readonly membershipsState = signal<TenantMembership[]>([]);
  readonly memberships = this.membershipsState.asReadonly();

  private readonly activeTenantState = signal<TenantMembership | null>(null);
  readonly activeTenant = this.activeTenantState.asReadonly();

  private readonly platformRolesState = signal<string[]>([]);
  readonly platformRoles = this.platformRolesState.asReadonly();

  isPlatformOperator(): boolean {
    return this.platformRolesState().some((role) => role === 'PLATFORM_ADMIN' || role === 'PLATFORM_SUPPORT');
  }

  canManagePlatform(): boolean {
    return this.platformRolesState().includes('PLATFORM_ADMIN');
  }

  login(email: string, password: string): Observable<TenantMembership[]> {
    return this.http.post<TokenResponse>('/api/v1/auth/tokens', {
      email: email.trim().toLowerCase(),
      password,
      device_name: 'modern-garment-web',
    }).pipe(
      tap((response) => {
        this.tokenState.set(response.access_token);
        this.userState.set(response.user);
      }),
      switchMap(() => this.http.get<TenantListResponse>('/api/v1/tenants')),
      map((response) => response.data),
      switchMap((memberships) => this.http.get<PlatformContextResponse>('/api/v1/platform/context').pipe(
        map((context) => ({ memberships, platformRoles: context.data.roles ?? [] })),
        catchError(() => of({ memberships, platformRoles: [] as string[] })),
      )),
      map(({ memberships, platformRoles }) => {
        this.platformRolesState.set(platformRoles);
        this.membershipsState.set(memberships);
        this.activeTenantState.set(memberships.length === 1 ? memberships[0] ?? null : null);
        return memberships;
      }),
      catchError((error: unknown) => {
        this.clearSession();
        return throwError(() => error);
      }),
    );
  }

  selectTenant(tenantId: string): void {
    const membership = this.membershipsState().find((item) => item.id === tenantId);
    if (membership) {
      this.activeTenantState.set(membership);
    }
  }

  logout(): Observable<unknown> {
    if (!this.tokenState()) {
      this.clearSession();
      return of(null);
    }

    return this.http.delete('/api/v1/auth/tokens/current').pipe(
      catchError(() => of(null)),
      finalize(() => this.clearSession()),
    );
  }

  clearSession(): void {
    this.tokenState.set(null);
    this.userState.set(null);
    this.membershipsState.set([]);
    this.activeTenantState.set(null);
    this.platformRolesState.set([]);
  }
}