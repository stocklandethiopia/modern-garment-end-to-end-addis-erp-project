import { Routes } from '@angular/router';

import { OverviewComponent } from './pages/overview/overview.component';

export const routes: Routes = [
  { path: '', component: OverviewComponent, title: 'Overview · Modern Garment ERP' },
  { path: '**', redirectTo: '' },
];