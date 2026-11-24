import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface TenantMember {
  id: string;
  user_id: string;
  name: string;
  email: string;
  membership_role: 'OWNER' | 'ADMIN' | 'MEMBER';
  status: 'ACTIVE' | 'REVOKED';
  joined_at: string | null;
  revoked_at: string | null;
}

export interface TenantInvitation {
  id: string;
  email_normalized: string;
  membership_role: 'ADMIN' | 'MEMBER';
  status: 'PENDING' | 'DELIVERY_FAILED' | 'ACCEPTED' | 'REVOKED' | 'EXPIRED';
  reason: string;
  expires_at: string;
  sent_at: string | null;
  accepted_at: string | null;
  revoked_at: string | null;
  delivery_attempts: number;
  last_delivery_error_code: string | null;
}

export interface TenantMembersPage {
  data: TenantMember[];
  current_page: number;
  last_page: number;
  total: number;
}

interface PageResponse {
  data: TenantMembersPage;
}

interface ResourceResponse<T> {
  data: T;
}

@Injectable({ providedIn: 'root' })
export class TenantMemberService {
  private readonly http = inject(HttpClient);

  listInvitations(): Observable<{ data: { data: TenantInvitation[]; current_page: number; last_page: number; total: number } }> {
    return this.http.get<{ data: { data: TenantInvitation[]; current_page: number; last_page: number; total: number } }>(
      '/api/v1/enterprise/invitations',
    );
  }

  invite(input: { email: string; membership_role: 'ADMIN' | 'MEMBER'; reason: string }): Observable<ResourceResponse<{ id: string; status: string }>> {
    return this.http.post<ResourceResponse<{ id: string; status: string }>>('/api/v1/enterprise/invitations', input);
  }

  resendInvitation(id: string, reason: string): Observable<ResourceResponse<{ id: string; status: string }>> {
    return this.http.post<ResourceResponse<{ id: string; status: string }>>(
      '/api/v1/enterprise/invitations/' + encodeURIComponent(id) + '/resend',
      { reason },
    );
  }

  revokeInvitation(id: string, reason: string): Observable<ResourceResponse<{ id: string; status: string }>> {
    return this.http.post<ResourceResponse<{ id: string; status: string }>>(
      '/api/v1/enterprise/invitations/' + encodeURIComponent(id) + '/revoke',
      { reason },
    );
  }

  list(): Observable<PageResponse> {
    return this.http.get<PageResponse>('/api/v1/enterprise/members');
  }

  add(input: { email: string; membership_role: 'ADMIN' | 'MEMBER'; reason: string }): Observable<ResourceResponse<unknown>> {
    return this.http.post<ResourceResponse<unknown>>('/api/v1/enterprise/members', input);
  }

  changeRole(userId: string, input: { membership_role: 'ADMIN' | 'MEMBER'; reason: string }): Observable<ResourceResponse<unknown>> {
    return this.http.patch<ResourceResponse<unknown>>(
      '/api/v1/enterprise/members/' + encodeURIComponent(userId) + '/role',
      input,
    );
  }

  revoke(userId: string, reason: string): Observable<ResourceResponse<unknown>> {
    return this.http.post<ResourceResponse<unknown>>(
      '/api/v1/enterprise/members/' + encodeURIComponent(userId) + '/revoke',
      { reason },
    );
  }
}
