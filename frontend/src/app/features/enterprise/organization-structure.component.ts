import { HttpErrorResponse } from '@angular/common/http';
import { Component, computed, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { BusinessSite, BusinessSiteService } from './business-site.service';
import { LegalEntity, LegalEntityService } from './legal-entity.service';
import {
  EnterpriseOrganizationService,
  JobPosition,
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

  readonly canManage = computed(() => {
    const role = this.session.activeTenant()?.membership_role;
    return role === 'OWNER' || role === 'ADMIN';
  });

  readonly selectedUnit = computed(
    () => this.units().find((unit) => unit.id === this.selectedUnitId()) ?? null,
  );

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
    this.api.listUnits().subscribe({
      next: (response) => {
        if (!this.isCurrentTenant(tenantId, sequence)) return;
        this.units.set(response.data);
        if (response.data.length > 0 && !this.selectedUnitId()) {
          this.selectUnit(response.data[0]!.id);
        }
        this.loading.set(false);
      },
      error: () => this.failLoad(tenantId, sequence, 'Organizational units could not be loaded.'),
    });

    this.api.listPositions().subscribe({
      next: (response) => {
        if (this.isCurrentTenant(tenantId, sequence)) this.positions.set(response.data);
      },
      error: () => this.failLoad(tenantId, sequence, 'Job positions could not be loaded.'),
    });

    this.api.listReportingRelations().subscribe({
      next: (response) => {
        if (this.isCurrentTenant(tenantId, sequence)) this.reportingRelations.set(response.data);
      },
      error: () => this.failLoad(tenantId, sequence, 'Reporting relationships could not be loaded.'),
    });

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
    this.managerAssignments.set([]);
    this.managerRequestSequence += 1;
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId || !this.units().some((unit) => unit.id === unitId)) return;

    const sequence = ++this.managerRequestSequence;
    this.loadingManagers.set(true);
    this.api.listUnitManagers(unitId).subscribe({
      next: (response) => {
        if (sequence !== this.managerRequestSequence
          || this.session.activeTenant()?.id !== tenantId
          || this.selectedUnitId() !== unitId) return;
        this.managerAssignments.set(response.data);
        this.loadingManagers.set(false);
      },
      error: () => {
        if (sequence !== this.managerRequestSequence || this.selectedUnitId() !== unitId) return;
        this.managerAssignments.set([]);
        this.loadingManagers.set(false);
        this.errorMessage.set('Manager positions could not be loaded for the selected unit.');
      },
    });

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
        this.units.update((current) => [...current, data].sort((a, b) => a.unit_code.localeCompare(b.unit_code)));
        this.savingUnit.set(false);
        this.showUnitForm.set(false);
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
        this.selectUnit(data.id);
        this.successMessage.set('Organizational unit created. Add its positions and reporting structure; no transaction authority is granted by this record.');
      },
      error: (error: HttpErrorResponse) => {
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
        this.positions.update((current) => [...current, data].sort((a, b) => a.position_code.localeCompare(b.position_code)));
        this.savingPosition.set(false);
        this.showPositionForm.set(false);
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
        this.managerAssignments.update((current) => [data, ...current]);
        this.savingManager.set(false);
        this.showManagerForm.set(false);
        this.managerForm.reset({ manager_position_id: '', valid_from: '2026-01-01', valid_to: '', reason: '' });
        this.successMessage.set('Manager position assigned for the stated period. This identifies a job slot, not a human incumbent.');
      },
      error: (error: HttpErrorResponse) => {
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
        this.reportingRelations.update((current) => [data, ...current]);
        this.savingReporting.set(false);
        this.showReportingForm.set(false);
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
        this.savingReporting.set(false);
        this.errorMessage.set(error.status === 422
          ? 'Choose two different active positions and check effective dates. Solid-line cycles and overlapping managers are rejected.'
          : 'The reporting relationship could not be saved.');
      },
    });
  }

  unitName(unitId: string): string {
    const unit = this.units().find((item) => item.id === unitId);
    return unit ? unit.unit_name + ' (' + unit.unit_code + ')' : 'Unknown unit';
  }

  positionName(positionId: string): string {
    const position = this.positions().find((item) => item.id === positionId);
    return position ? position.title + ' (' + position.position_code + ')' : 'Unknown position';
  }
}
