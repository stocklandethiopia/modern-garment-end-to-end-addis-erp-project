import { Routes } from '@angular/router';

import { OverviewComponent } from './pages/overview/overview.component';
import { LegalEntitiesComponent } from './features/enterprise/legal-entities.component';
import { BusinessSitesComponent } from './features/enterprise/business-sites.component';

export const routes: Routes = [
  { path: '', component: OverviewComponent, title: 'Overview · Modern Garment ERP' },
  { path: 'enterprise/legal-entities', component: LegalEntitiesComponent, title: 'Legal entities · Modern Garment ERP' },
  { path: 'enterprise/sites', component: BusinessSitesComponent, title: 'Business sites · Modern Garment ERP' },
  { path: '**', redirectTo: '' },
];