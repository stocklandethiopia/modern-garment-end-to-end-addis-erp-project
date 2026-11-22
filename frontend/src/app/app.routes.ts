import { Routes } from '@angular/router';

import { OverviewComponent } from './pages/overview/overview.component';
import { LegalEntitiesComponent } from './features/enterprise/legal-entities.component';

export const routes: Routes = [
  { path: '', component: OverviewComponent, title: 'Overview · Modern Garment ERP' },
  { path: 'enterprise/legal-entities', component: LegalEntitiesComponent, title: 'Legal entities · Modern Garment ERP' },
  { path: '**', redirectTo: '' },
];