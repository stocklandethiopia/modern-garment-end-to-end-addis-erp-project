import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';

export interface ApiHealth {
  status: 'ok';
  service: string;
  version: string;
  time: string;
}

@Injectable({ providedIn: 'root' })
export class ApiHealthService {
  private readonly http = inject(HttpClient);

  getHealth(): Observable<ApiHealth> {
    return this.http.get<ApiHealth>('/api/v1/health');
  }
}