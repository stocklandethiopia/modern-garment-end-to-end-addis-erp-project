import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface LegalEntity {
  id: string;
  registration_number: string;
  legal_name: string;
  legal_form: string | null;
  country_code: string;
  tax_identifier: string | null;
  base_currency: string;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface LegalEntityInput {
  registration_number: string;
  legal_name: string;
  legal_form: string | null;
  country_code: string;
  tax_identifier: string | null;
  base_currency: string;
}

interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
}

interface ResourceResponse<T> {
  data: T;
}

@Injectable({ providedIn: 'root' })
export class LegalEntityService {
  private readonly http = inject(HttpClient);

  list(): Observable<PaginatedResponse<LegalEntity>> {
    return this.http.get<PaginatedResponse<LegalEntity>>('/api/v1/enterprise/legal-entities');
  }

  create(input: LegalEntityInput): Observable<ResourceResponse<LegalEntity>> {
    return this.http.post<ResourceResponse<LegalEntity>>('/api/v1/enterprise/legal-entities', input);
  }
}