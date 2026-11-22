import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, RouterOutlet],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
})
export class AppComponent {
  readonly navigation = [
    { label: 'Overview', path: '/', icon: '◫' },
  ];

  readonly domainGroups = [
    {
      label: 'Product & commercial',
      items: ['Initiatives & market insight', 'Brand & merchandising', 'Design studio & PLM', 'Industrial engineering'],
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
}