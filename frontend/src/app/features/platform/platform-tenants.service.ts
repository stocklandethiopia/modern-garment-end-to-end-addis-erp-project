import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface PlatformTenantRecord {
  tenant_id: string;
  tenant_code: string;
  tenant_name: string;
  tenant_status: 'ACTIVE' | 'SUSPENDED' | 'CLOSED';
  plan_code: string;
  entitlement_status: 'UNASSIGNED' | 'REVIEW_REQUIRED' | 'ACTIVE' | 'SUSPENDED' | 'EXPIRED';
  valid_from: string | null;
  valid_to: string | null;
  lifecycle_version: number;
}

interface ListResponse {
  data: PlatformTenantRecord[];
}

@Injectable({ providedIn: 'root' })
export class PlatformTenantsService {
  private readonly http = inject(HttpClient);

  list(): Observable<ListResponse> {
    return this.http.get<ListResponse>('/api/v1/platform/tenants');
  }
}
