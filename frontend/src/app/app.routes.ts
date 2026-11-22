import { Routes } from '@angular/router';

import { OverviewComponent } from './pages/overview/overview.component';
import { LegalEntitiesComponent } from './features/enterprise/legal-entities.component';
import { BusinessSitesComponent } from './features/enterprise/business-sites.component';
import { InitiativesComponent } from './features/merchandising/initiatives.component';
import { MarketInsightsComponent } from './features/merchandising/market-insights.component';
import { PlanningWorkbenchComponent } from './features/merchandising/planning-workbench.component';

export const routes: Routes = [
  { path: '', component: OverviewComponent, title: 'Overview · Modern Garment ERP' },
  { path: 'enterprise/legal-entities', component: LegalEntitiesComponent, title: 'Legal entities · Modern Garment ERP' },
  { path: 'enterprise/sites', component: BusinessSitesComponent, title: 'Business sites · Modern Garment ERP' },
  { path: 'product/initiatives', component: InitiativesComponent, title: 'Initiatives · Modern Garment ERP' },
  { path: 'product/market-insights', component: MarketInsightsComponent, title: 'Market insights · Modern Garment ERP' },
  { path: 'product/planning-workbench', component: PlanningWorkbenchComponent, title: 'Demand & commercial planning · Modern Garment ERP' },
  { path: '**', redirectTo: '' },
];