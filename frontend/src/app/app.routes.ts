import { Routes } from '@angular/router';

import { OverviewComponent } from './pages/overview/overview.component';
import { LegalEntitiesComponent } from './features/enterprise/legal-entities.component';
import { TenantMembersComponent } from './features/enterprise/tenant-members.component';
import { BusinessSitesComponent } from './features/enterprise/business-sites.component';
import { InitiativesComponent } from './features/merchandising/initiatives.component';
import { MarketInsightsComponent } from './features/merchandising/market-insights.component';
import { MerchandisingDashboardComponent } from './features/merchandising/merchandising-dashboard.component';
import { EvidencePolicyWorkbenchComponent } from './features/merchandising/evidence-policy-workbench.component';
import { PlanningWorkbenchComponent } from './features/merchandising/planning-workbench.component';
import { RangePlanningComponent } from './features/merchandising/range-planning.component';
import { ProductDevelopmentComponent } from './features/product-development/product-development.component';
import { TechnicalProductDevelopmentComponent } from './features/product-development/technical-product-development.component';
import { SampleFitComponent } from './features/product-development/sample-fit.component';
import { PlatformOperatorsComponent } from './features/platform/platform-operators.component';
import { PlatformTenantsComponent } from './features/platform/platform-tenants.component';

export const routes: Routes = [
  { path: '', component: OverviewComponent, title: 'Overview · Modern Garment ERP' },
  { path: 'enterprise/members', component: TenantMembersComponent, title: 'Tenant members · Modern Garment ERP' },
  { path: 'enterprise/legal-entities', component: LegalEntitiesComponent, title: 'Legal entities · Modern Garment ERP' },
  { path: 'enterprise/sites', component: BusinessSitesComponent, title: 'Business sites · Modern Garment ERP' },
  { path: 'platform/tenants', component: PlatformTenantsComponent, title: 'Platform tenant register · Modern Garment ERP' },
  { path: 'platform/operators', component: PlatformOperatorsComponent, title: 'Platform operators · Modern Garment ERP' },
  { path: 'product/initiatives', component: InitiativesComponent, title: 'Initiatives · Modern Garment ERP' },
  { path: 'product/market-insights', component: MarketInsightsComponent, title: 'Market insights · Modern Garment ERP' },
  { path: 'product/merchandising-dashboard', component: MerchandisingDashboardComponent, title: 'Merchandising dashboard · Modern Garment ERP' },
  { path: 'product/evidence-policies', component: EvidencePolicyWorkbenchComponent, title: 'Evidence requirements · Modern Garment ERP' },
  { path: 'product/brand-range', component: RangePlanningComponent, title: 'Brand & range planning · Modern Garment ERP' },
  { path: 'product/product-development', component: ProductDevelopmentComponent, title: 'Product development studio · Modern Garment ERP' },
  { path: 'product/technical-development', component: TechnicalProductDevelopmentComponent, title: 'Measurement & pattern engineering · Modern Garment ERP' },
  { path: 'product/sample-fit', component: SampleFitComponent, title: 'Sample room & fit review · Modern Garment ERP' },
  { path: 'product/planning-workbench', component: PlanningWorkbenchComponent, title: 'Demand & commercial planning · Modern Garment ERP' },
  { path: '**', redirectTo: '' },
];