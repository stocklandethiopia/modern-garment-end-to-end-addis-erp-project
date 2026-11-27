import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting, HttpTestingController } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { signal } from '@angular/core';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { OrganizationStructureComponent } from './organization-structure.component';
import { JobPosition, OrganizationUnit } from './enterprise-organization.service';

function page<T>(data: T[]) {
  return { data, current_page: 1, last_page: 1, total: data.length };
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

  function flushInitial(fixture: ComponentFixture<OrganizationStructureComponent>): void {
    http.expectOne('/api/v1/enterprise/organization-units').flush(page([unit]));
    http.expectOne('/api/v1/enterprise/job-positions').flush(page([position]));
    http.expectOne('/api/v1/enterprise/position-reporting-relations').flush(page([]));
    http.expectOne('/api/v1/enterprise/legal-entities').flush(page([]));
    http.expectOne('/api/v1/enterprise/sites').flush(page([]));
    http.expectOne('/api/v1/enterprise/organization-units/unit-1/manager-assignments').flush(page([]));
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

    const text = fixture.nativeElement.textContent as string;
    expect(text).toContain('Organization structure');
    expect(text).toContain('Factory Operations');
    expect(text).toContain('Factory General Manager');
    expect(text).toContain('Job positions');
    expect(text).toContain('no transaction authority');
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

    const managers = http.expectOne('/api/v1/enterprise/organization-units/unit-2/manager-assignments');
    expect(managers.request.method).toBe('GET');
    managers.flush(page([]));

    fixture.detectChanges();
    expect(component.units().some((item) => item.id === 'unit-2')).toBe(true);
    expect(fixture.nativeElement.textContent).toContain('Organizational unit created');
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
