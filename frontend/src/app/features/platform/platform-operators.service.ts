import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export type PlatformRoleCode = 'PLATFORM_ADMIN' | 'PLATFORM_SUPPORT';
export type PlatformGrantStatus = 'PENDING' | 'ACTIVE' | 'REVOKED' | 'EXPIRED';

export interface PlatformOperatorGrant {
  grant_id: string;
  user_id: string;
  user_name: string;
  user_email: string;
  role_code: PlatformRoleCode;
  grant_status: PlatformGrantStatus;
  valid_from: string;
  valid_to: string;
  grant_reason: string;
  granted_by: string;
  grantor_name: string;
  approved_by: string | null;
  approver_name: string | null;
  approved_at: string | null;
  revoked_by: string | null;
  revoker_name: string | null;
  revoked_at: string | null;
  revocation_reason: string | null;
  created_at: string;
  updated_at: string;
}

export interface PlatformOperatorGrantInput {
  user_email: string;
  role_code: PlatformRoleCode;
  valid_from: string;
  valid_to: string | null;
  reason: string;
}

interface ListResponse {
  data: PlatformOperatorGrant[];
}

interface ActionResponse {
  data: {
    grant_id: string;
    grant_status: PlatformGrantStatus;
  };
}

@Injectable({ providedIn: 'root' })
export class PlatformOperatorsService {
  private readonly http = inject(HttpClient);

  list(): Observable<ListResponse> {
    return this.http.get<ListResponse>('/api/v1/platform/operators/grants');
  }

  request(input: PlatformOperatorGrantInput): Observable<ActionResponse> {
    return this.http.post<ActionResponse>('/api/v1/platform/operators/grants', input);
  }

  approve(grantId: string, reason: string): Observable<ActionResponse> {
    return this.http.post<ActionResponse>(
      '/api/v1/platform/operators/grants/' + encodeURIComponent(grantId) + '/approve',
      { reason },
    );
  }

  revoke(grantId: string, reason: string): Observable<ActionResponse> {
    return this.http.post<ActionResponse>(
      '/api/v1/platform/operators/grants/' + encodeURIComponent(grantId) + '/revoke',
      { reason },
    );
  }
}
