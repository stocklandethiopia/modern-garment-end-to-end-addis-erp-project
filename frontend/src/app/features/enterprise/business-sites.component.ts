import { Component, computed, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { BusinessSite, BusinessSiteService, InventoryLocation, OperationalNode } from './business-site.service';
import { LegalEntity, LegalEntityService } from './legal-entity.service';

@Component({
  selector: 'app-business-sites',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './business-sites.component.html',
  styleUrl: './business-sites.component.css',
})
export class BusinessSitesComponent {
  private readonly api = inject(BusinessSiteService);
  private readonly legalEntityApi = inject(LegalEntityService);
  readonly session = inject(AuthSessionService);

  readonly sites = signal<BusinessSite[]>([]);
  readonly legalEntities = signal<LegalEntity[]>([]);
  readonly nodes = signal<OperationalNode[]>([]);
  readonly locations = signal<InventoryLocation[]>([]);
  readonly activeSite = signal<BusinessSite | null>(null);
  readonly activeNode = signal<OperationalNode | null>(null);

  readonly loading = signal(false);
  readonly loadingNodes = signal(false);
  readonly loadingLocations = signal(false);
  readonly savingSite = signal(false);
  readonly savingNode = signal(false);
  readonly savingLocation = signal(false);
  readonly showSiteForm = signal(false);
  readonly showNodeForm = signal(false);
  readonly showLocationForm = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);

  private siteRequestSequence = 0;
  private nodeRequestSequence = 0;
  private locationRequestSequence = 0;

  readonly canManage = computed(() => {
    const role = this.session.activeTenant()?.membership_role;
    return role === 'OWNER' || role === 'ADMIN';
  });

  private readonly tenantReloadEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    const requestId = ++this.siteRequestSequence;
    this.nodeRequestSequence += 1;
    this.locationRequestSequence += 1;

    this.sites.set([]);
    this.legalEntities.set([]);
    this.nodes.set([]);
    this.locations.set([]);
    this.activeSite.set(null);
    this.activeNode.set(null);
    this.errorMessage.set(null);
    this.successMessage.set(null);
    this.loading.set(false);
    this.loadingNodes.set(false);
    this.loadingLocations.set(false);
    this.savingSite.set(false);
    this.savingNode.set(false);
    this.savingLocation.set(false);

    if (tenantId) {
      this.loadSites(tenantId, requestId);
      this.loadLegalEntities(tenantId, requestId);
    } else {
      this.loading.set(false);
      this.loadingNodes.set(false);
    }
  });

  readonly siteForm = new FormGroup({
    legal_entity_id: new FormControl('', { nonNullable: true }),
    code: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(80), Validators.pattern(/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/)],
    }),
    site_type: new FormControl('FACTORY_CAMPUS', {
      nonNullable: true,
      validators: [Validators.required],
    }),
    name: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(180)],
    }),
    country_code: new FormControl('ET', {
      nonNullable: true,
      validators: [Validators.required, Validators.pattern(/^[A-Z]{2}$/)],
    }),
    region: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(120)] }),
    city: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(120)] }),
    address_line: new FormControl('', { nonNullable: true, validators: [Validators.maxLength(255)] }),
    timezone: new FormControl('Africa/Addis_Ababa', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(64)],
    }),
  });

  readonly nodeForm = new FormGroup({
    code: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(80), Validators.pattern(/^[A-Za-z0-9][A-Za-z0-9._-]{1,79}$/)],
    }),
    node_type: new FormControl('BRANCH', { nonNullable: true, validators: [Validators.required] }),
    name: new FormControl('', { nonNullable: true, validators: [Validators.required, Validators.maxLength(180)] }),
    stock_holding_enabled: new FormControl(false, { nonNullable: true }),
  });

  readonly locationForm = new FormGroup({
    code: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(100), Validators.pattern(/^[A-Za-z0-9][A-Za-z0-9._-]{1,99}$/)],
    }),
    location_type: new FormControl('BIN', { nonNullable: true, validators: [Validators.required] }),
    parent_id: new FormControl('', { nonNullable: true }),
    allow_stock: new FormControl(true, { nonNullable: true }),
  });

  private loadSites(tenantId: string, requestId: number): void {
    this.loading.set(true);
    this.api.listSites().subscribe({
      next: (response) => {
        if (requestId !== this.siteRequestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.sites.set(response.data);
        this.loading.set(false);
      },
      error: () => {
        if (requestId !== this.siteRequestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.sites.set([]);
        this.errorMessage.set('Business sites could not be loaded for the selected tenant.');
        this.loading.set(false);
      },
    });
  }

  private loadLegalEntities(tenantId: string, requestId: number): void {
    this.legalEntityApi.list().subscribe({
      next: (response) => {
        if (requestId !== this.siteRequestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.legalEntities.set(response.data);
      },
      error: () => {
        if (requestId !== this.siteRequestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.legalEntities.set([]);
        this.errorMessage.set('Legal entity options could not be loaded.');
      },
    });
  }

  refreshActiveSite(): void {
    const site = this.activeSite();
    if (site) this.selectSite(site);
  }

  selectSite(site: BusinessSite): void {
    this.nodeRequestSequence += 1;
    this.locationRequestSequence += 1;
    this.savingNode.set(false);
    this.savingLocation.set(false);
    this.loadingLocations.set(false);
    this.activeNode.set(null);
    this.locations.set([]);
    this.showLocationForm.set(false);
    this.activeSite.set(site);
    this.nodes.set([]);
    this.showNodeForm.set(false);
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;

    const requestId = ++this.nodeRequestSequence;
    this.loadingNodes.set(true);
    this.api.listNodes(site.id).subscribe({
      next: (response) => {
        if (
          requestId !== this.nodeRequestSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.activeSite()?.id !== site.id
        ) return;
        this.nodes.set(response.data);
        this.loadingNodes.set(false);
      },
      error: () => {
        if (
          requestId !== this.nodeRequestSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.activeSite()?.id !== site.id
        ) return;
        this.nodes.set([]);
        this.errorMessage.set('Operational nodes could not be loaded for this site.');
        this.loadingNodes.set(false);
      },
    });
  }

  selectNode(node: OperationalNode): void {
    this.locationRequestSequence += 1;
    this.activeNode.set(node);
    this.locations.set([]);
    this.showLocationForm.set(false);
    this.savingLocation.set(false);

    if (!node.stock_holding_enabled) {
      this.loadingLocations.set(false);
      this.errorMessage.set('This node has no stock-holding capability, so it cannot contain inventory locations.');
      return;
    }

    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;

    const requestId = ++this.locationRequestSequence;
    this.loadingLocations.set(true);
    this.api.listLocations(node.id).subscribe({
      next: (response) => {
        if (
          requestId !== this.locationRequestSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.activeNode()?.id !== node.id
        ) return;
        this.locations.set(response.data);
        this.loadingLocations.set(false);
      },
      error: () => {
        if (
          requestId !== this.locationRequestSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.activeNode()?.id !== node.id
        ) return;
        this.locations.set([]);
        this.errorMessage.set('Inventory locations could not be loaded for this node.');
        this.loadingLocations.set(false);
      },
    });
  }

  createLocation(): void {
    const node = this.activeNode();
    const tenantId = this.session.activeTenant()?.id;
    if (!node || !tenantId || !node.stock_holding_enabled || !this.canManage() || this.locationForm.invalid || this.savingLocation()) {
      this.locationForm.markAllAsTouched();
      return;
    }

    const value = this.locationForm.getRawValue();
    const requestId = ++this.locationRequestSequence;
    this.savingLocation.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.createLocation(node.id, {
      code: value.code.trim(),
      location_type: value.location_type,
      parent_id: value.parent_id || null,
      allow_stock: value.allow_stock,
    }).subscribe({
      next: ({ data }) => {
        if (
          requestId !== this.locationRequestSequence ||
          this.session.activeTenant()?.id !== tenantId ||
          this.activeNode()?.id !== node.id
        ) return;
        this.locations.update((current) => [data, ...current]);
        this.savingLocation.set(false);
        this.showLocationForm.set(false);
        this.locationForm.reset({
          code: '',
          location_type: 'BIN',
          parent_id: '',
          allow_stock: true,
        });
        this.successMessage.set('Draft inventory location created. It has no stock balance until approved inventory movements are posted.');
      },
      error: (error: HttpErrorResponse) => {
        if (requestId !== this.locationRequestSequence || this.session.activeTenant()?.id !== tenantId) return;
        this.savingLocation.set(false);
        this.errorMessage.set(error.status === 422
          ? 'Check the location code, parent and stock capability of this node.'
          : 'The inventory location could not be created.');
      },
    });
  }

  createSite(): void {
    if (!this.canManage() || this.siteForm.invalid || this.savingSite()) {
      this.siteForm.markAllAsTouched();
      return;
    }

    const value = this.siteForm.getRawValue();
    const tenantId = this.session.activeTenant()?.id;
    if (!tenantId) return;

    this.savingSite.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.createSite({
      legal_entity_id: value.legal_entity_id || null,
      code: value.code.trim(),
      site_type: value.site_type,
      name: value.name.trim(),
      country_code: value.country_code.trim().toUpperCase(),
      region: value.region.trim() || null,
      city: value.city.trim() || null,
      address_line: value.address_line.trim() || null,
      timezone: value.timezone.trim(),
    }).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.sites.update((current) => [data, ...current]);
        this.savingSite.set(false);
        this.showSiteForm.set(false);
        this.siteForm.reset({
          legal_entity_id: '',
          code: '',
          site_type: 'FACTORY_CAMPUS',
          name: '',
          country_code: 'ET',
          region: '',
          city: '',
          address_line: '',
          timezone: 'Africa/Addis_Ababa',
        });
        this.successMessage.set('Draft business site created. Address registration, permits and activity licences remain separate reviews.');
        this.selectSite(data);
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.savingSite.set(false);
        this.errorMessage.set(error.status === 422
          ? 'Check the site fields and confirm the code is unique in this tenant.'
          : 'The business site could not be created.');
      },
    });
  }

  createNode(): void {
    const site = this.activeSite();
    const tenantId = this.session.activeTenant()?.id;
    if (!site || !tenantId || !this.canManage() || this.nodeForm.invalid || this.savingNode()) {
      this.nodeForm.markAllAsTouched();
      return;
    }

    const value = this.nodeForm.getRawValue();
    this.savingNode.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.createNode(site.id, {
      code: value.code.trim(),
      node_type: value.node_type,
      name: value.name.trim(),
      capabilities: value.stock_holding_enabled ? ['STOCK_HOLDING'] : [],
      stock_holding_enabled: value.stock_holding_enabled,
    }).subscribe({
      next: ({ data }) => {
        if (this.session.activeTenant()?.id !== tenantId || this.activeSite()?.id !== site.id) return;
        this.nodes.update((current) => [data, ...current]);
        this.savingNode.set(false);
        this.showNodeForm.set(false);
        this.nodeForm.reset({
          code: '',
          node_type: 'BRANCH',
          name: '',
          stock_holding_enabled: false,
        });
        this.successMessage.set('Draft operational node created. Stock capability is not a warehouse record or a legal permission.');
      },
      error: (error: HttpErrorResponse) => {
        if (this.session.activeTenant()?.id !== tenantId) return;
        this.savingNode.set(false);
        this.errorMessage.set(error.status === 422
          ? 'The node settings are invalid. A branch or office cannot be configured as stock-holding.'
          : 'The operational node could not be created.');
      },
    });
  }
}