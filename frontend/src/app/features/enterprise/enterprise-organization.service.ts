import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface OrganizationUnit {
  id: string;
  parent_id: string | null;
  legal_entity_id: string | null;
  business_site_id: string | null;
  unit_code: string;
  unit_name: string;
  unit_type: string;
  status: string;
  valid_from: string;
  valid_to: string | null;
}

export interface JobPosition {
  id: string;
  org_unit_id: string;
  position_code: string;
  title: string;
  job_family: string | null;
  grade: string | null;
  position_class: string;
  status: string;
  valid_from: string;
  valid_to: string | null;
  unit_code?: string;
  unit_name?: string;
}

export interface OrganizationUnitManagerAssignment {
  id: string;
  org_unit_id: string;
  manager_position_id: string;
  valid_from: string;
  valid_to: string | null;
  reason: string;
  manager_position_code: string;
  manager_position_title: string;
}

export interface PositionReportingRelation {
  id: string;
  subject_position_id: string;
  manager_position_id: string;
  reporting_type: 'SOLID_LINE' | 'FUNCTIONAL';
  valid_from: string;
  valid_to: string | null;
  reason: string;
  subject_position_code: string;
  subject_position_title: string;
  manager_position_code: string;
  manager_position_title: string;
}

export interface OrganizationListQuery {
  page?: number;
  per_page?: number;
  q?: string;
  unit_type?: string;
  status?: string;
  legal_entity_id?: string;
  business_site_id?: string;
  parent_id?: string;
  org_unit_id?: string;
  position_class?: string;
  reporting_type?: 'SOLID_LINE' | 'FUNCTIONAL';
  position_id?: string;
  valid_on?: string;
}

export interface ApiPage<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

interface ResourceResponse<T> {
  data: T;
}

export interface OrganizationUnitInput {
  parent_id: string | null;
  legal_entity_id: string | null;
  business_site_id: string | null;
  unit_code: string;
  unit_name: string;
  unit_type: string;
  valid_from: string;
  valid_to: string | null;
  reason: string;
}

export interface JobPositionInput {
  org_unit_id: string;
  position_code: string;
  title: string;
  job_family: string | null;
  grade: string | null;
  position_class: string;
  valid_from: string;
  valid_to: string | null;
  reason: string;
}

export interface UnitManagerInput {
  manager_position_id: string;
  valid_from: string;
  valid_to: string | null;
  reason: string;
}

export interface PositionReportingInput {
  subject_position_id: string;
  manager_position_id: string;
  reporting_type: 'SOLID_LINE' | 'FUNCTIONAL';
  valid_from: string;
  valid_to: string | null;
  reason: string;
}

@Injectable({ providedIn: 'root' })
export class EnterpriseOrganizationService {
  private readonly http = inject(HttpClient);
  private readonly base = '/api/v1/enterprise';

  private queryParams(query: OrganizationListQuery = {}): HttpParams {
    let params = new HttpParams();
    for (const [key, value] of Object.entries(query)) {
      if (value !== undefined && value !== null && String(value).trim() !== '') {
        params = params.set(key, String(value));
      }
    }
    return params;
  }

  listUnits(query: OrganizationListQuery = {}): Observable<ApiPage<OrganizationUnit>> {
    return this.http.get<ApiPage<OrganizationUnit>>(
      this.base + '/organization-units',
      { params: this.queryParams(query) },
    );
  }

  createUnit(input: OrganizationUnitInput): Observable<ResourceResponse<OrganizationUnit>> {
    return this.http.post<ResourceResponse<OrganizationUnit>>(this.base + '/organization-units', input);
  }

  listPositions(query: OrganizationListQuery = {}): Observable<ApiPage<JobPosition>> {
    return this.http.get<ApiPage<JobPosition>>(
      this.base + '/job-positions',
      { params: this.queryParams(query) },
    );
  }

  createPosition(input: JobPositionInput): Observable<ResourceResponse<JobPosition>> {
    return this.http.post<ResourceResponse<JobPosition>>(this.base + '/job-positions', input);
  }

  listUnitManagers(unitId: string, query: OrganizationListQuery = {}): Observable<ApiPage<OrganizationUnitManagerAssignment>> {
    return this.http.get<ApiPage<OrganizationUnitManagerAssignment>>(
      this.base + '/organization-units/' + encodeURIComponent(unitId) + '/manager-assignments',
      { params: this.queryParams(query) },
    );
  }

  assignUnitManager(unitId: string, input: UnitManagerInput): Observable<ResourceResponse<OrganizationUnitManagerAssignment>> {
    return this.http.post<ResourceResponse<OrganizationUnitManagerAssignment>>(
      this.base + '/organization-units/' + encodeURIComponent(unitId) + '/manager-assignments',
      input,
    );
  }

  listReportingRelations(query: OrganizationListQuery = {}): Observable<ApiPage<PositionReportingRelation>> {
    return this.http.get<ApiPage<PositionReportingRelation>>(
      this.base + '/position-reporting-relations',
      { params: this.queryParams(query) },
    );
  }

  createReportingRelation(input: PositionReportingInput): Observable<ResourceResponse<PositionReportingRelation>> {
    return this.http.post<ResourceResponse<PositionReportingRelation>>(
      this.base + '/position-reporting-relations',
      input,
    );
  }
}
