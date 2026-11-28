import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { signal } from '@angular/core';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { OrganizationStructureComponent } from './organization-structure.component';
import { JobPosition, OrganizationUnit } from './enterprise-organization.service';

function page<T>(data: T[], currentPage = 1, lastPage = 1, total = data.length) {
  return { data, current_page: currentPage, last_page: lastPage, total };
}

const unit: OrganizationUnit = {
  id: 'unit-1',
  parent_id: null,
  legal_entity_id: null,
  business_site_id: null,
  unit_code: 'FACTORY-OPS',
  unit_name: 'Factory Operations',
  unit_type: 'FACTORY',
  status: 'ACTIVE',
  valid_from: '2026-01-01',
  valid_to: null,
};

const position: JobPosition = {
  id: 'position-1',
  org_unit_id: 'unit-1',
  position_code: 'FACTORY-GM',
  title: 'Factory General Manager',
  job_family: 'OPERATIONS',
  grade: 'M3',
  position_class: 'MANAGEMENT',
  status: 'ACTIVE',
  valid_from: '2026-01-01',
  valid_to: null,
  unit_code: 'FACTORY-OPS',
  unit_name: 'Factory Operations',
};

describe('OrganizationStructureComponent', () => {
  let http: HttpTestingController;
  const tenantState = signal({
    id: 'tenant-1',
    code: 'TEST-ORG',
    name: 'Organization Test Tenant',
    locale: 'en',
    timezone: 'Africa/Addis_Ababa',
    membership_role: 'OWNER',
  });

  function flushInitial(fixture: ComponentFixture<OrganizationStructureComponent>, unitTotal = 1, unitLastPage = 1): void {
    http.expectOne((request) => request.url === '/api/v1/enterprise/organization-units'
      && request.params.get('page') === '1'
      && request.params.get('per_page') === '25').flush(page([unit], 1, unitLastPage, unitTotal));

    http.expectOne((request) => request.url === '/api/v1/enterprise/job-positions'
      && request.params.get('page') === '1'
      && request.params.get('per_page') === '25'
      && request.params.get('org_unit_id') === 'unit-1').flush(page([position]));

    http.expectOne((request) => request.url === '/api/v1/enterprise/organization-units/unit-1/manager-assignments'
      && request.params.get('page') === '1'
      && request.params.get('per_page') === '25').flush(page([]));

    http.expectOne((request) => request.url === '/api/v1/enterprise/position-reporting-relations'
      && request.params.get('page') === '1'
      && request.params.get('per_page') === '25').flush(page([]));

    http.expectOne((request) => request.url === '/api/v1/enterprise/job-positions'
      && request.params.get('page') === '1'
      && request.params.get('per_page') === '100'
      && request.params.get('status') === 'ACTIVE').flush(page([position]));

    http.expectOne('/api/v1/enterprise/legal-entities').flush(page([]));
    http.expectOne('/api/v1/enterprise/sites').flush(page([]));
    fixture.detectChanges();
  }

  beforeEach(() => {
    tenantState.set({
      id: 'tenant-1',
      code: 'TEST-ORG',
      name: 'Organization Test Tenant',
      locale: 'en',
      timezone: 'Africa/Addis_Ababa',
      membership_role: 'OWNER',
    });
    TestBed.configureTestingModule({
      imports: [OrganizationStructureComponent],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: AuthSessionService, useValue: { activeTenant: tenantState.asReadonly() } },
      ],
    });
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    http.verify();
  });

  it('loads tenant organization masters and displays positions separately from people', () => {
    const fixture = TestBed.createComponent(OrganizationStructureComponent);
    fixture.detectChanges();
    flushInitial(fixture);

    const component = fixture.componentInstance;
    expect(component.entityName(null)).toBe('Not assigned');
    expect(component.siteName(null)).toBe('Not assigned');
    expect(component.entityName('missing-entity')).toBe('Assigned');
    expect(component.siteName('missing-site')).toBe('Assigned');

    const text = fixture.nativeElement.textContent as string;
    expect(text).toContain('Organization structure');
    expect(text).toContain('Factory Operations');
    expect(text).toContain('Factory General Manager');
    expect(text).toContain('Job positions');
    expect(text).toContain('No transaction authority is implied');
    expect(fixture.componentInstance.positionsInSelectedUnit()).toHaveLength(1);
  });

  it('allows a tenant administrator to create a unit and reloads its manager-position history', () => {
    const fixture = TestBed.createComponent(OrganizationStructureComponent);
    fixture.detectChanges();
    flushInitial(fixture);

    const component = fixture.componentInstance;
    component.unitForm.patchValue({
      unit_code: 'QUALITY',
      unit_name: 'Quality Assurance',
      unit_type: 'DEPARTMENT',
      valid_from: '2026-01-01',
      valid_to: '',
      reason: 'Create the Quality Assurance organizational unit for factory governance.',
    });
    component.createUnit();

    const request = http.expectOne('/api/v1/enterprise/organization-units');
    expect(request.request.method).toBe('POST');
    expect(request.request.body.unit_code).toBe('QUALITY');

    const created: OrganizationUnit = {
      ...unit,
      id: 'unit-2',
      unit_code: 'QUALITY',
      unit_name: 'Quality Assurance',
      unit_type: 'DEPARTMENT',
    };
    request.flush({ data: created });

    const unitReload = http.expectOne((candidate) => candidate.url === '/api/v1/enterprise/organization-units'
      && candidate.params.get('page') === '1'
      && candidate.params.get('per_page') === '25'
      && candidate.params.get('q') === 'QUALITY');
    unitReload.flush(page([created]));

    const positions = http.expectOne((candidate) => candidate.url === '/api/v1/enterprise/job-positions'
      && candidate.params.get('org_unit_id') === 'unit-2');
    positions.flush(page([]));

    const managers = http.expectOne((candidate) => candidate.url === '/api/v1/enterprise/organization-units/unit-2/manager-assignments');
    expect(managers.request.method).toBe('GET');
    managers.flush(page([]));

    fixture.detectChanges();
    expect(component.units().some((item) => item.id === 'unit-2')).toBe(true);
    expect(fixture.nativeElement.textContent).toContain('Organizational unit created');
  });

  it('creates a vacant-capable position with an audit reason and reloads the filtered register', () => {
    const fixture = TestBed.createComponent(OrganizationStructureComponent);
    fixture.detectChanges();
    flushInitial(fixture);

    const component = fixture.componentInstance;
    component.positionForm.patchValue({
      position_code: 'QUALITY-LEAD',
      title: 'Quality Assurance Lead',
      job_family: 'QUALITY',
      grade: 'M2',
      position_class: 'MANAGEMENT',
      valid_from: '2026-01-01',
      valid_to: '',
      reason: 'Create the Quality Assurance Lead slot for the approved factory organization.',
    });
    component.createPosition();

    const request = http.expectOne('/api/v1/enterprise/job-positions');
    expect(request.request.method).toBe('POST');
    expect(request.request.body.position_code).toBe('QUALITY-LEAD');
    expect(request.request.body.reason).toBe('Create the Quality Assurance Lead slot for the approved factory organization.');

    const created: JobPosition = {
      ...position,
      id: 'position-2',
      position_code: 'QUALITY-LEAD',
      title: 'Quality Assurance Lead',
      job_family: 'QUALITY',
      grade: 'M2',
    };
    request.flush({ data: created });

    const reload = http.expectOne((candidate) => candidate.url === '/api/v1/enterprise/job-positions'
      && candidate.params.get('page') === '1'
      && candidate.params.get('per_page') === '25'
      && candidate.params.get('org_unit_id') === 'unit-1'
      && candidate.params.get('q') === 'QUALITY-LEAD');
    reload.flush(page([created]));

    fixture.detectChanges();
    expect(component.positions().some((item) => item.id === 'position-2')).toBe(true);
    expect(fixture.nativeElement.textContent).toContain('Job position created as a vacant-capable slot');
  });

  it('requests later pages and applies tenant-scoped search filters to all organization registers', () => {
    const fixture = TestBed.createComponent(OrganizationStructureComponent);
    fixture.detectChanges();
    flushInitial(fixture, 26, 2);

    const component = fixture.componentInstance;
    component.changeUnitPage(2);
    const unitPageTwo = http.expectOne((request) => request.url === '/api/v1/enterprise/organization-units'
      && request.params.get('page') === '2'
      && request.params.get('per_page') === '25');
    unitPageTwo.flush(page([unit], 2, 2, 26));
    expect(component.unitPage()?.total).toBe(26);

    component.selectUnit('unit-1');
    const positionPage = http.expectOne((request) => request.url === '/api/v1/enterprise/job-positions'
      && request.params.get('org_unit_id') === 'unit-1');
    positionPage.flush(page([position]));

    const managerPage = http.expectOne((request) => request.url === '/api/v1/enterprise/organization-units/unit-1/manager-assignments');
    managerPage.flush(page([]));

    component.positionSearch.setValue('CUTTING');
    component.searchPositions();
    http.expectOne((request) => request.url === '/api/v1/enterprise/job-positions'
      && request.params.get('q') === 'CUTTING'
      && request.params.get('org_unit_id') === 'unit-1').flush(page([position], 1, 1, 1));

    component.managerSearch.setValue('UNIT HEAD');
    component.searchManagers();
    http.expectOne((request) => request.url === '/api/v1/enterprise/organization-units/unit-1/manager-assignments'
      && request.params.get('q') === 'UNIT HEAD').flush(page([], 1, 1, 0));

    component.reportingSearch.setValue('FACTORY');
    component.searchReporting();
    http.expectOne((request) => request.url === '/api/v1/enterprise/position-reporting-relations'
      && request.params.get('q') === 'FACTORY').flush(page([], 1, 1, 0));

    component.reportingPositionSearch.setValue('GENERAL MANAGER');
    component.searchReportingPositions();
    http.expectOne((request) => request.url === '/api/v1/enterprise/job-positions'
      && request.params.get('q') === 'GENERAL MANAGER'
      && request.params.get('per_page') === '100'
      && request.params.get('status') === 'ACTIVE').flush(page([position]));

    fixture.detectChanges();
    expect(component.positionPage()?.total).toBe(1);
    expect(component.reportingPositionChoices()).toHaveLength(1);
  });

  it('does not expose organization configuration controls to an ordinary tenant member', () => {
    tenantState.set({
      id: 'tenant-1',
      code: 'TEST-ORG',
      name: 'Organization Test Tenant',
      locale: 'en',
      timezone: 'Africa/Addis_Ababa',
      membership_role: 'MEMBER',
    });
    const fixture = TestBed.createComponent(OrganizationStructureComponent);
    fixture.detectChanges();
    flushInitial(fixture);

    expect(fixture.componentInstance.canManage()).toBe(false);
    expect(fixture.nativeElement.textContent).not.toContain('Add organizational unit');
    expect(fixture.nativeElement.textContent).not.toContain('Add position');
    expect(fixture.nativeElement.textContent).toContain('Factory Operations');
  });
});
