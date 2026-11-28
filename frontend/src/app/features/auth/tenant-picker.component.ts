import { Component, inject } from '@angular/core';

import { AuthSessionService } from '../../core/auth/auth-session.service';

@Component({
  selector: 'app-tenant-picker',
  standalone: true,
  templateUrl: './tenant-picker.component.html',
  styleUrl: './tenant-picker.component.css',
})
export class TenantPickerComponent {
  readonly session = inject(AuthSessionService);

  choose(tenantId: string): void {
    this.session.selectTenant(tenantId);
  }

  signOut(): void {
    this.session.logout().subscribe();
  }
}