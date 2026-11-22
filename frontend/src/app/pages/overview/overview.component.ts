import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';

import { ApiHealth, ApiHealthService } from '../../core/api/api-health.service';
import { ConnectionState, toConnectionState } from '../../core/api/connection-state';

@Component({
  selector: 'app-overview',
  standalone: true,
  templateUrl: './overview.component.html',
  styleUrl: './overview.component.css',
})
export class OverviewComponent implements OnInit {
  private readonly api = inject(ApiHealthService);

  readonly health = signal<ApiHealth | null>(null);
  readonly connectionError = signal(false);
  readonly connectionState = signal<ConnectionState>('checking');
  readonly checkedAt = signal<Date | null>(null);

  ngOnInit(): void {
    this.checkConnection();
  }

  checkConnection(): void {
    this.connectionError.set(false);
    this.connectionState.set('checking');

    this.api.getHealth().subscribe({
      next: (health) => {
        this.health.set(health);
        this.checkedAt.set(new Date());
        this.connectionState.set(toConnectionState(health, false));
      },
      error: (_error: HttpErrorResponse) => {
        this.health.set(null);
        this.connectionError.set(true);
        this.checkedAt.set(new Date());
        this.connectionState.set(toConnectionState(null, true));
      },
    });
  }

  get connectionLabel(): string {
    switch (this.connectionState()) {
      case 'connected':
        return 'API connected';
      case 'unavailable':
        return 'API unavailable';
      default:
        return 'Checking API';
    }
  }
}