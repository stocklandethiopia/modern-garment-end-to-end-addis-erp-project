import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface BusinessSite {
  id: string;
  legal_entity_id: string | null;
  code: string;
  site_type: string;
  name: string;
  country_code: string;
  region: string | null;
  city: string | null;
  address_line: string | null;
  timezone: string;
  status: string;
}

export interface InventoryLocation {
  id: string;
  operational_node_id: string;
  parent_id: string | null;
  code: string;
  location_type: string;
  allow_stock: boolean;
  status: string;
}

export interface InventoryLocationInput {
  code: string;
  location_type: string;
  parent_id: string | null;
  allow_stock: boolean;
}

export interface OperationalNode {
  id: string;
  business_site_id: string;
  legal_entity_id: string | null;
  code: string;
  node_type: string;
  name: string;
  capabilities: string[] | string;
  stock_holding_enabled: boolean;
  status: string;
}

export interface BusinessSiteInput {
  legal_entity_id: string | null;
  code: string;
  site_type: string;
  name: string;
  country_code: string;
  region: string | null;
  city: string | null;
  address_line: string | null;
  timezone: string;
}

export interface OperationalNodeInput {
  code: string;
  node_type: string;
  name: string;
  capabilities: string[];
  stock_holding_enabled: boolean;
}

interface PageResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

interface ResourceResponse<T> {
  data: T;
}

@Injectable({ providedIn: 'root' })
export class BusinessSiteService {
  private readonly http = inject(HttpClient);

  listSites(): Observable<PageResponse<BusinessSite>> {
    return this.http.get<PageResponse<BusinessSite>>('/api/v1/enterprise/sites');
  }

  createSite(input: BusinessSiteInput): Observable<ResourceResponse<BusinessSite>> {
    return this.http.post<ResourceResponse<BusinessSite>>('/api/v1/enterprise/sites', input);
  }

  listNodes(siteId: string): Observable<PageResponse<OperationalNode>> {
    return this.http.get<PageResponse<OperationalNode>>(
      '/api/v1/enterprise/sites/' + encodeURIComponent(siteId) + '/nodes',
    );
  }

  createNode(siteId: string, input: OperationalNodeInput): Observable<ResourceResponse<OperationalNode>> {
    return this.http.post<ResourceResponse<OperationalNode>>(
      '/api/v1/enterprise/sites/' + encodeURIComponent(siteId) + '/nodes',
      input,
    );
  }
}