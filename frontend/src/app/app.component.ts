import { Component, inject } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';

import { AuthSessionService } from './core/auth/auth-session.service';
import { LoginComponent } from './features/auth/login.component';
import { TenantPickerComponent } from './features/auth/tenant-picker.component';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, RouterOutlet, LoginComponent, TenantPickerComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
})
export class AppComponent {
  readonly session = inject(AuthSessionService);

  readonly navigation = [
    { label: 'Overview', path: '/', icon: '◫' },
    { label: 'Legal entities', path: '/enterprise/legal-entities', icon: '▦' },
    { label: 'Business sites & nodes', path: '/enterprise/sites', icon: '⌖' },
    { label: 'Initiatives & opportunities', path: '/product/initiatives', icon: '⌁' },
    { label: 'Market insight register', path: '/product/market-insights', icon: '◉' },
    { label: 'Merchandising dashboard', path: '/product/merchandising-dashboard', icon: '▤' },
    { label: 'Evidence requirement policies', path: '/product/evidence-policies', icon: '◈' },
    { label: 'Brand & range planning', path: '/product/brand-range', icon: '◇' },
    { label: 'Product development studio', path: '/product/product-development', icon: '✳' },
    { label: 'Measurement & pattern engineering', path: '/product/technical-development', icon: '▦' },
    { label: 'Sample room & fit review', path: '/product/sample-fit', icon: '✓' },
    { label: 'Demand & commercial planning', path: '/product/planning-workbench', icon: '∑' },
  ];

  readonly domainGroups = [
    {
      label: 'Product & commercial',
      items: ['Design studio & PLM', 'Industrial engineering'],
    },
    {
      label: 'Supply & operations',
      items: ['Procurement & materials', 'Production routes & SOP', 'Factory execution', 'Quality & HSE'],
    },
    {
      label: 'Enterprise',
      items: ['Warehouse & distribution', 'Sales channels & retail', 'Finance & treasury', 'People & payroll'],
    },
    {
      label: 'Intelligence',
      items: ['Analytics & control tower', 'Integrations & AI governance'],
    },
  ];

  changeTenant(tenantId: string): void {
    this.session.selectTenant(tenantId);
  }

  signOut(): void {
    this.session.logout().subscribe();
  }
}
