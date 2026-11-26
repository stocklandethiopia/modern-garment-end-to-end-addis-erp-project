import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { finalize } from 'rxjs';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { PlatformTenantRecord, PlatformTenantsService } from './platform-tenants.service';

@Component({
  selector: 'app-platform-tenants',
  standalone: true,
  imports: [DatePipe],
  templateUrl: './platform-tenants.component.html',
  styleUrl: './platform-tenants.component.css',
})
export class PlatformTenantsComponent implements OnInit {
  private readonly service = inject(PlatformTenantsService);
  readonly session = inject(AuthSessionService);

  readonly tenants = signal<PlatformTenantRecord[]>([]);
  readonly loading = signal(false);
  readonly error = signal('');
  readonly notice = signal('');

  ngOnInit(): void {
    if (this.canRead()) {
      this.refresh();
    } else {
      this.error.set('An active PLATFORM_ADMIN or PLATFORM_SUPPORT grant is required to view the global tenant register.');
    }
  }

  canRead(): boolean {
    return this.session.isPlatformOperator();
  }

  isReadOnly(): boolean {
    return this.session.platformRoles().includes('PLATFORM_SUPPORT')
      && !this.session.canManagePlatform();
  }

  refresh(): void {
    if (!this.canRead()) {
      this.error.set('An active platform operator grant is required to view the global tenant register.');
      return;
    }

    this.loading.set(true);
    this.error.set('');
    this.service.list().pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (response) => this.tenants.set(response.data),
      error: (response: { error?: { message?: string } }) => {
        this.error.set(response.error?.message ?? 'The global tenant register could not be loaded.');
      },
    });
  }
}
