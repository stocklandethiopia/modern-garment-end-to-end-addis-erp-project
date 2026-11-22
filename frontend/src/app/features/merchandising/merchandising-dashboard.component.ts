import { DatePipe, DecimalPipe } from '@angular/common';
import { HttpClient, HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';

export interface MerchandisingKpi {
  key: string;
  label: string;
  status: 'AVAILABLE' | 'NO_DATA' | 'UNAVAILABLE';
  value: number | null;
  unit: string;
  numerator: number | null;
  denominator: number | null;
  definition: string;
  sources: string[];
}

export interface MerchandisingWorkItem {
  type: string;
  id: string;
  code: string;
  title: string;
  stage: string;
  created_at: string;
  href: string;
}

export interface MerchandisingDashboardData {
  generated_at: string;
  tenant_role: string;
  initiative_funnel: Array<{ status: string; count: number }>;
  kpis: MerchandisingKpi[];
  range_lifecycle_mix: Array<{
    lifecycle_type: string;
    range_line_count: number;
    planned_units: number;
  }>;
  range_category_depth: Array<{
    category_code: string;
    target_tier: string | null;
    range_line_count: number;
    planned_units: number;
  }>;
  evidence_register: {
    source_count: number;
    insight_count: number;
    sufficiency_status: string;
    sufficiency_reason: string;
  };
  work_queue: {
    approval_scope: string;
    approvals: MerchandisingWorkItem[];
    my_work: MerchandisingWorkItem[];
  };
  unavailable_kpis: Array<{ key: string; reason: string; dependency: string }>;
}

interface DashboardEnvelope {
  data: MerchandisingDashboardData;
}

@Component({
  selector: 'app-merchandising-dashboard',
  standalone: true,
  imports: [DatePipe, DecimalPipe, RouterLink],
  templateUrl: './merchandising-dashboard.component.html',
  styleUrl: './merchandising-dashboard.component.css',
})
export class MerchandisingDashboardComponent implements OnInit {
  private readonly http = inject(HttpClient);

  readonly dashboard = signal<MerchandisingDashboardData | null>(null);
  readonly loading = signal(false);
  readonly errorMessage = signal<string | null>(null);

  ngOnInit(): void {
    this.refresh();
  }

  refresh(): void {
    this.loading.set(true);
    this.errorMessage.set(null);

    this.http.get<DashboardEnvelope>('/api/v1/product/merchandising-dashboard').subscribe({
      next: (response) => {
        this.dashboard.set(response.data);
        this.loading.set(false);
      },
      error: (error: HttpErrorResponse) => {
        this.dashboard.set(null);
        this.errorMessage.set(
          error.error?.message ?? 'The merchandising dashboard could not be loaded.',
        );
        this.loading.set(false);
      },
    });
  }

  formatKpi(value: number | null, unit: string): string {
    if (value === null) {
      return '—';
    }

    if (unit === 'percent') {
      return `${value.toFixed(2)}%`;
    }

    return new Intl.NumberFormat('en').format(value);
  }

  statusLabel(status: string): string {
    return status.replaceAll('_', ' ').toLowerCase();
  }
}
