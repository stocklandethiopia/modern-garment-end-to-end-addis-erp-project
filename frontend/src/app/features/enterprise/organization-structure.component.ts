import { HttpErrorResponse } from '@angular/common/http';
import { Component, computed, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { BusinessSite, BusinessSiteService } from './business-site.service';
import { LegalEntity, LegalEntityService } from './legal-entity.service';
import {
  ApiPage,
  EnterpriseOrganizationService,
  JobPosition,
  OrganizationListQuery,
  OrganizationUnit,
  OrganizationUnitManagerAssignment,
  PositionReportingRelation,
} from './enterprise-organization.service';

@Component({
  selector: 'app-organization-structure',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './organization-structure.component.html',
  styleUrl: './organization-structure.component.css',
})
export class OrganizationStructureComponent {
  private readonly api = inject(EnterpriseOrganizationService);
  private readonly legalEntityApi = inject(LegalEntityService);
  private readonly siteApi = inject(BusinessSiteService);
  readonly session = inject(AuthSessionService);

  readonly units = signal<OrganizationUnit[]>([]);
  readonly positions = signal<JobPosition[]>([]);
  readonly managerAssignments = signal<OrganizationUnitManagerAssignment[]>([]);
  readonly reportingRelations = signal<PositionReportingRelation[]>([]);
  readonly legalEntities = signal<LegalEntity[]>([]);
  readonly sites = signal<BusinessSite[]>([]);
  readonly selectedUnitId = signal<string | null>(null);
  readonly selectedUnitRecord = signal<OrganizationUnit | null>(null);
  readonly knownUnits = signal<OrganizationUnit[]>([]);
  readonly knownPositions = signal<JobPosition[]>([]);
  readonly unitPage = signal<ApiPage<OrganizationUnit> | null>(null);
  readonly positionPage = signal<ApiPage<JobPosition> | null>(null);
  readonly managerPage = signal<ApiPage<OrganizationUnitManagerAssignment> | null>(null);
  readonly reportingPage = signal<ApiPage<PositionReportingRelation> | null>(null);
  readonly unitSearch = new FormControl('', { nonNullable: true });
  readonly positionSearch = new FormControl('', { nonNullable: true });
  readonly managerSearch = new FormControl('', { nonNullable: true });
  readonly reportingSearch = new FormControl('', { nonNullable: true });
  readonly reportingPositionSearch = new FormControl('', { nonNullable: true });
  readonly reportingPositionChoices = signal<JobPosition[]>([]);

  readonly loading = signal(false);
  readonly loadingManagers = signal(false);
  readonly savingUnit = signal(false);
  readonly savingPosition = signal(false);
  readonly savingManager = signal(false);
  readonly savingReporting = signal(false);
  readonly showUnitForm = signal(false);
  readonly showPositionForm = signal(false);
  readonly showManagerForm = signal(false);
  readonly showReportingForm = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);

  private requestSequence = 0;
  private managerRequestSequence = 0;
  private unitListSequence = 0;
  private positionListSequence = 0;
  private managerListSequence = 0;
  private reportingListSequence = 0;
  private reportingPositionSequence = 0;
  private readonly pageSize = 25;

  readonly canManage = computed(() => {
    const role = this.session.activeTenant()?.membership_role;
    return role === 'OWNER' || role === 'ADMIN';
  });

  readonly selectedUnit = computed(
    () => this.units().find((unit) => unit.id === this.selectedUnitId())
      ?? this.selectedUnitRecord(),
  );

  readonly parentUnitOptions = computed(() => {
    const byId = new Map<string, OrganizationUnit>();
    for (const unit of [...this.knownUnits(), ...this.units()]) byId.set(unit.id, unit);
    return [...byId.values()].sort((a, b) => a.unit_code.localeCompare(b.unit_code));
  });

  readonly managerPositionChoices = computed(() => this.knownPositions()
    .filter((position) => position.org_unit_id === this.selectedUnitId())
    .sort((a, b) => a.position_code.localeCompare(b.position_code)));

  readonly reportingPositionOptions = computed(() => {
    const byId = new Map<string, JobPosition>();
    for (const position of [...this.knownPositions(), ...this.reportingPositionChoices()]) {
      byId.set(position.id, position);
    }
    return [...byId.values()].sort((a, b) => a.position_code.localeCompare(b.position_code));
  });

  readonly childUnits = computed(
    () => this.units().filter((unit) => unit.parent_id === this.selectedUnitId()),
  );

  readonly positionsInSelectedUnit = computed(
    () => this.positions().filter((position) => position.org_unit_id === this.selectedUnitId()),
  );

  private readonly tenantReloadEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    const sequence = ++this.requestSequence;
    this.managerRequestSequence += 1;

    this.units.set([]);
    this.positions.set([]);
    this.managerAssignments.set([]);
    this.reportingRelations.set([]);
    this.legalEntities.set([]);
    this.sites.set([]);
    this.selectedUnitId.set(null);
    this.selectedUnitRecord.set(null);
    this.knownUnits.set([]);
    this.knownPositions.set([]);
    this.unitPage.set(null);
    this.positionPage.set(null);
    this.managerPage.set(null);
    this.reportingPage.set(null);
    this.reportingPositionChoices.set([]);
    this.errorMessage.set(null);
    this.successMessage.set(null);
    this.loading.set(false);
    this.loadingManagers.set(false);
    this.savingUnit.set(false);
    this.savingPosition.set(false);
    this.savingManager.set(false);
    this.savingReporting.set(false);

    if (tenantId) this.loadAll(tenantId, sequence);
  });

  readonly unitForm = new FormGroup({
    parent_id: new FormControl('', { nonNullable: true }),
    legal_entity_id: new FormControl('', { nonNullable: true }),
    business_site_id: new FormControl('', { nonNullable: true }),
    unit_code: new FormControl('', {
      nonNullable: true,
      validators: [
        Validators.required,
        Validators.maxLength(80),
        Validators.pattern(/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/),
      ],
    }),
    unit_name: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(180)],
    }),
    unit_type: new FormControl('DEPARTMENT', { nonNullable: true, validators: [Validators.required] }),
    valid_from: new FormControl('2026-01-01', { nonNullable: true, validators: [Validators.required] }),
    valid_to: new FormControl('', { nonNullable: true }),
  });

  readonly positionForm = new FormGroup({
    position_code: new FormControl('', {
      nonNullable: true,
      validators: [
        Validators.required,
        Validators.maxLength(80),
        Validators.pattern(/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/),
      ],
    }),
    title: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(180)] }),
    job_family: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(100)] }),
    grade: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(80)] }),
    position_class: new FormControl('PROFESSIONAL', { nonNullable: true, validators: [Validators.required] }),
    valid_from: new FormControl('2026-01-01', { nonNullable: true, validators: [Validators.required] }),
    valid_to: new FormControl('', { nonNullable: true }),
  });

  readonly managerForm = new FormGroup({
    manager_position_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    valid_from: new FormControl('2026-01-01', { nonNullable: true, validators: [Validators.required] }),
    valid_to: new FormControl('', { nonNullable: true }),
    reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(1000)] }),
  });

  readonly reportingForm = new FormGroup({
    subject_position_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    manager_position_id: new FormControl('', { nonNullable: true, validators: [Validators.required] }),
    reporting_type: new FormControl<'SOLID_LINE' | 'FUNCTIONAL'>('SOLID_LINE', { nonNullable: true }),
    valid_from: new FormControl('2026-01-01', { nonNullable: true, validators: [Validators.required] }),
    valid_to: new FormControl('', { nonNullable: true }),
    reason: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.minLength(10), Validators.maxLength(1000)] }),
  });

  private loadAll(tenantId: string, sequence: number): void {
    this.loading.set(true);
    this.loadUnitPage(tenantId, 1, sequence);
    this.loadReportingPage(tenantId, 1, sequence);
    this.loadReportingPositionChoices(tenantId, '', sequence);

    this.legalEntityApi.list().subscribe({
      next: (response) => {
        if (this.isCurrentTenant(tenantId, sequence)) this.legalEntities.set(response.data);
      },
      error: () => this.failLoad(tenantId, sequence, 'Legal-entity options could not be loaded.'),
    });

    this.siteApi.listSites().subscribe({
      next: (response) => {
        if (this.isCurrentTenant(tenantId, sequence)) this.sites.set(response.data);
      },
      error: () => this.failLoad(tenantId, sequence, 'Business-site options could not be loaded.'),
    });
  }

  private loadUnitPage(tenantId: string, page: number, tenantSequence = this.requestSequence): void {
    const request = ++this.unitListSequence;
    const query: OrganizationListQuery = {
      page,
      per_page: this.pageSize,
      q: this.unitSearch.value.trim() || undefined,
    };
    this.loading.set(true);
    this.api.listUnits(query).subscribe({
      next: (response) => {
        if (request !== this.unitListSequence || !this.isCurrentTenant(tenantId, tenantSequence)) return;
        this.unitPage.set(response);
        this.units.set(response.data);
        this.knownUnits.update((current) => {
          const byId = new Map(current.map((unit) => [unit.id, unit]));
          for (const unit of response.data) byId.set(unit.id, unit);
          return [...byId.values()];
        });
        const currentSelected = this.selectedUnitRecord();
        const query = this.unitSearch.value.trim().toLocaleLowerCase();
        const stillMatches = currentSelected === null || query === ''
          || currentSelected.unit_code.toLocaleLowerCase().includes(query)
          || currentSelected.unit_name.toLocaleLowerCase().includes(query);
        const selected = response.data.find((unit) => unit.id === this.selectedUnitId())
          ?? (this.selectedUnitId() === null || !stillMatches ? response.data[0] ?? null : currentSelected);
        if (selected) {
          if (selected.id !== this.selectedUnitId()) this.selectUnit(selected.id);
          else this.selectedUnitRecord.set(selected);
        } else {
          this.selectedUnitId.set(null);
          this.selectedUnitRecord.set(null);
          this.managerAssignments.set([]);
          this.managerPage.set(null);
          this.positions.set([]);
          this.positionPage.set(null);
        }
        this.loading.set(false);
      },
      error: () => {
        if (request !== this.unitListSequence || !this.isCurrentTenant(tenantId, tenantSequence)) return;
        this.loading.set(false);
        this.errorMessage.set('Organizational units could not be loaded.');
      },
    });
  }

  private loadPositionPage(tenantId: string, page: number, unitId: string, tenantSequence = this.requestSequence): void {
    const request = ++this.positionListSequence;
    const query: OrganizationListQuery = {
      page,
      per_page: this.pageSize,
      org_unit_id: unitId,
      q: this.positionSearch.value.trim() || undefined,
    };
    this.api.listPositions(query).subscribe({
      next: (response) => {
        if (request !== this.positionListSequence || !this.isCurrentTenant(tenantId, tenantSequence)
          || this.selectedUnitId() !== unitId) return;
        this.positionPage.set(response);
        this.positions.set(response.data);
        this.knownPositions.update((current) => {
          const byId = new Map(current.map((position) => [position.id, position]));
          for (const position of response.data) byId.set(position.id, position);
          return [...byId.values()];
        });
      },
      error: () => {
        if (request !== this.positionListSequence || !this.isCurrentTenant(tenantId, tenantSequence)) return;
        this.errorMessage.set('Job positions could not be loaded for the selected unit.');
      },
    });
  }

  private loadManagerPage(tenantId: string, page: number, unitId: string, tenantSequence = this.requestSequence): void {
    const request = ++this.managerListSequence;
    const query: OrganizationListQuery = {
      page,
      per_page: this.pageSize,
      q: this.managerSearch.value.trim() || undefined,
    };
    this.loadingManagers.set(true);
    this.api.listUnitManagers(unitId, query).subscribe({
      next: (response) => {
        if (request !== this.managerListSequence || !this.isCurrentTenant(tenantId, tenantSequence)
          || this.selectedUnitId() !== unitId) return;
        this.managerPage.set(response);
        this.managerAssignments.set(response.data);
        this.loadingManagers.set(false);
      },
      error: () => {
        if (request !== this.managerListSequence || !this.isCurrentTenant(tenantId, tenantSequence)
          || this.selectedUnitId() !== unitId) return;
        this.managerAssignments.set([]);
        this.loadingManagers.set(false);
        this.errorMessage.set('Manager-position history could not be loaded.');
      },
    });
  }

  private loadReportingPage(tenantId: string, page: number, tenantSequence = this.requestSequence): void {
    const request = ++this.reportingListSequence;
    const query: OrganizationListQuery = {
      page,
      per_page: this.pageSize,
      q: this.reportingSearch.value.trim() || undefined,
    };
    this.api.listReportingRelations(query).subscribe({
      next: (response) => {
        if (request !== this.reportingListSequence || !this.isCurrentTenant(tenantId, tenantSequence)) return;
        this.reportingPage.set(response);
        this.reportingRelations.set(response.data);
      },
      error: () => {
        if (request !== this.reportingListSequence || !this.isCurrentTenant(tenantId, tenantSequence)) return;
        this.errorMessage.set('Position reporting relationships could not be loaded.');
      },
    });
  }

  private loadReportingPositionChoices(tenantId: string, term: string, tenantSequence = this.requestSequence): void {
    const request = ++this.reportingPositionSequence;
    this.api.listPositions({ page: 1, per_page: 100, q: term.trim() || undefined }).subscribe({
      next: (response) => {
        if (request !== this.reportingPositionSequence || !this.isCurrentTenant(tenantId, tenantSequence)) return;
        this.reportingPositionChoices.set(response.data);
        this.knownPositions.update((current) => {
          const byId = new Map(current.map((position) => [position.id, position]));
          for (const position of response.data) byId.set(position.id, position);
          return [...byId.values()];
        });
      },
      error: () => {
        if (request !== this.reportingPositionSequence || !this.isCurrentTenant(tenantId, tenantSequence)) return;
        this.reportingPositionChoices.set([]);
        this.errorMessage.set('Position choices could not be searched.');
      },
    });
  }

  private isCurrentTenant(tenantId: string, sequence: number): boolean {
    return sequence === this.requestSequence && this.session.activeTenant()?.id === tenantId;
  }

  private failLoad(tenantId: string, sequence: number, message: string): void {
    if (!this.isCurrentTenant(tenantId, sequence)) return;
    this.errorMessage.set(message);
    this.loading.set(false);
  }

  refresh(): void {
    const tenantId = this.session.activeTenant()?.id;
    if (tenantId) this.loadAll(tenantId, ++this.requestSequence);
  }

  selectUnit(unitId: string): void {
    this.selectedUnitId.set(unitId);
    this.selectedUnitRecord.set(this.units().find((unit) => unit.id === unitId)
      ?? this.knownUnits().find((unit) => unit.id === unitId)
      ?? null);
    this.savingPosition.set(false);
    this.savingManager.set(false);
    this.managerAssignments.set([]);
    this.managerPage.set(null);
    this.managerRequestSequence += 1;
    this.managerSearch.setValue('');
    this.positionSearch.setValue('');
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId || !this.units().some((unit) => unit.id === unitId)) return;

    this.loadPositionPage(tenantId, 1, unitId);
    this.loadManagerPage(tenantId, 1, unitId);

    this.positionForm.reset({
      position_code: '',
      title: '',
      job_family: '',
      grade: '',
      position_class: 'PROFESSIONAL',
      valid_from: '2026-01-01',
      valid_to: '',
    });
    this.managerForm.reset({
      manager_position_id: '',
      valid_from: '2026-01-01',
      valid_to: '',
      reason: '',
    });
  }

  searchUnits(): void {
    const tenantId = this.session.activeTenant()?.id;
    if (tenantId) this.loadUnitPage(tenantId, 1);
  }

  changeUnitPage(page: number): void {
    const tenantId = this.session.activeTenant()?.id;
    const last = this.unitPage()?.last_page ?? 1;
    if (tenantId && page >= 1 && page <= last) this.loadUnitPage(tenantId, page);
  }

  searchPositions(): void {
    const tenantId = this.session.activeTenant()?.id;
    const unitId = this.selectedUnitId();
    if (tenantId && unitId) this.loadPositionPage(tenantId, 1, unitId);
  }

  changePositionPage(page: number): void {
    const tenantId = this.session.activeTenant()?.id;
    const unitId = this.selectedUnitId();
    const last = this.positionPage()?.last_page ?? 1;
    if (tenantId && unitId && page >= 1 && page <= last) this.loadPositionPage(tenantId, page, unitId);
  }

  searchManagers(): void {
    const tenantId = this.session.activeTenant()?.id;
    const unitId = this.selectedUnitId();
    if (tenantId && unitId) this.loadManagerPage(tenantId, 1, unitId);
  }

  changeManagerPage(page: number): void {
    const tenantId = this.session.activeTenant()?.id;
    const unitId = this.selectedUnitId();
    const last = this.managerPage()?.last_page ?? 1;
    if (tenantId && unitId && page >= 1 && page <= last) this.loadManagerPage(tenantId, page, unitId);
  }

  searchReporting(): void {
    const tenantId = this.session.activeTenant()?.id;
    if (tenantId) this.loadReportingPage(tenantId, 1);
  }

  changeReportingPage(page: number): void {
    const tenantId = this.session.activeTenant()?.id;
    const last = this.reportingPage()?.last_page ?? 1;
    if (tenantId && page >= 1 && page <= last) this.loadReportingPage(tenantId, page);
  }

  searchReportingPositions(): void {
    const tenantId = this.session.activeTenant()?.id;
    if (tenantId) this.loadReportingPositionChoices(tenantId, this.reportingPositionSearch.value);
  }


  createUnit(): void {
    if (!this.canManage() || this.unitForm.invalid || this.savingUnit()) {
      this.unitForm.markAllAsTouched();
      return;
    }
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;
    const value = this.unitForm.getRawValue();
    this.savingUnit.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.createUnit({
      parent_id: value.parent_id || null,
      legal_entity_id: value.legal_entity_id || null,
      business_site_id: value.business_site_id || null,
      unit_code: value.unit_code.trim(),
      unit_name: value.unit_name.trim(),
      unit_type: value.unit_type,
      valid_from: value.valid_from,
      valid_to: value.valid_to || null,
    }).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.loadUnitPage(tenantId, 1);
        this.savingUnit.set(false);
        this.showUnitForm.set(false);
        this.unitSearch.setValue(data.unit_code);
        this.selectedUnitId.set(null);
        this.selectedUnitRecord.set(null);
        this.unitForm.reset({
          parent_id: '',
          legal_entity_id: '',
          business_site_id: '',
          unit_code: '',
          unit_name: '',
          unit_type: 'DEPARTMENT',
          valid_from: '2026-01-01',
          valid_to: '',
        });
        this.successMessage.set('Organizational unit created. Add its positions and reporting structure; no transaction authority is granted by this record.');
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.savingUnit.set(false);
        this.errorMessage.set(error.status === 422
          ? 'Check the unit code, parent period, legal entity and business-site relationship.'
          : 'The organizational unit could not be saved.');
      },
    });
  }

  createPosition(): void {
    const unitId = this.selectedUnitId();
    const tenantId = this.session.activeTenant()?.id;
    if (!unitId || !tenantId || !this.canManage() || this.positionForm.invalid || this.savingPosition()) {
      this.positionForm.markAllAsTouched();
      return;
    }
    const value = this.positionForm.getRawValue();
    this.savingPosition.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.createPosition({
      org_unit_id: unitId,
      position_code: value.position_code.trim(),
      title: value.title.trim(),
      job_family: value.job_family.trim() || null,
      grade: value.grade.trim() || null,
      position_class: value.position_class,
      valid_from: value.valid_from,
      valid_to: value.valid_to || null,
    }).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId || this.selectedUnitId() !== unitId) return;
        this.loadPositionPage(tenantId, 1, unitId);
        this.savingPosition.set(false);
        this.showPositionForm.set(false);
        this.positionSearch.setValue(data.position_code);
        this.positionForm.reset({
          position_code: '',
          title: '',
          job_family: '',
          grade: '',
          position_class: 'PROFESSIONAL',
          valid_from: '2026-01-01',
          valid_to: '',
        });
        this.successMessage.set('Job position created as a vacant-capable slot. It is not a person, employee or login account.');
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId || this.selectedUnitId() !== unitId) return;
        this.savingPosition.set(false);
        this.errorMessage.set(error.status === 422
          ? 'Check the position code and confirm the selected unit is active.'
          : 'The job position could not be saved.');
      },
    });
  }

  assignManager(): void {
    const unitId = this.selectedUnitId();
    const tenantId = this.session.activeTenant()?.id;
    if (!unitId || !tenantId || !this.canManage() || this.managerForm.invalid || this.savingManager()) {
      this.managerForm.markAllAsTouched();
      return;
    }
    const value = this.managerForm.getRawValue();
    this.savingManager.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.assignUnitManager(unitId, {
      manager_position_id: value.manager_position_id,
      valid_from: value.valid_from,
      valid_to: value.valid_to || null,
      reason: value.reason.trim(),
    }).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId || this.selectedUnitId() !== unitId) return;
        this.loadManagerPage(tenantId, 1, unitId);
        this.savingManager.set(false);
        this.showManagerForm.set(false);
        this.managerForm.reset({ manager_position_id: '', valid_from: '2026-01-01', valid_to: '', reason: '' });
        this.successMessage.set('Manager position assigned for the stated period. This identifies a job slot, not a human incumbent.');
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId || this.selectedUnitId() !== unitId) return;
        this.savingManager.set(false);
        this.errorMessage.set(error.status === 422
          ? 'The manager position must belong to this unit and cannot overlap another manager period.'
          : 'The manager-position assignment could not be saved.');
      },
    });
  }

  createReportingRelation(): void {
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId || !this.canManage() || this.reportingForm.invalid || this.savingReporting()) {
      this.reportingForm.markAllAsTouched();
      return;
    }
    const value = this.reportingForm.getRawValue();
    this.savingReporting.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.createReportingRelation({
      subject_position_id: value.subject_position_id,
      manager_position_id: value.manager_position_id,
      reporting_type: value.reporting_type,
      valid_from: value.valid_from,
      valid_to: value.valid_to || null,
      reason: value.reason.trim(),
    }).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.loadReportingPage(tenantId, 1);
        this.savingReporting.set(false);
        this.showReportingForm.set(false);
        this.reportingSearch.setValue('');
        this.reportingForm.reset({
          subject_position_id: '',
          manager_position_id: '',
          reporting_type: 'SOLID_LINE',
          valid_from: '2026-01-01',
          valid_to: '',
          reason: '',
        });
        this.successMessage.set('Reporting relationship recorded. Solid-line and functional relationships are informational organization data, not approval rights.');
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.savingReporting.set(false);
        this.errorMessage.set(error.status === 422
          ? 'Choose two different active positions and check effective dates. Solid-line cycles and overlapping managers are rejected.'
          : 'The reporting relationship could not be saved.');
      },
    });
  }

  unitName(unitId: string): string {
    const unit = this.units().find((item) => item.id === unitId)
      ?? this.knownUnits().find((item) => item.id === unitId);
    return unit ? unit.unit_name + ' (' + unit.unit_code + ')' : 'Unknown unit';
  }

  entityName(entityId: string | null): string {
    if (!entityId) return 'Not assigned';
    return this.legalEntities().find((entity) => entity.id === entityId)?.legal_name ?? 'Assigned';
  }

  siteName(siteId: string | null): string {
    if (!siteId) return 'Not assigned';
    return this.sites().find((site) => site.id === siteId)?.name ?? 'Assigned';
  }
}
