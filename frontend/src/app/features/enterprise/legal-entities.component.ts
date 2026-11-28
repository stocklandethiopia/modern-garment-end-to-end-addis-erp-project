import { Component, computed, effect, inject, signal } from '@angular/core';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { HttpErrorResponse } from '@angular/common/http';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { LegalEntity, LegalEntityService } from './legal-entity.service';

@Component({
  selector: 'app-legal-entities',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './legal-entities.component.html',
  styleUrl: './legal-entities.component.css',
})
export class LegalEntitiesComponent {
  private readonly api = inject(LegalEntityService);
  readonly session = inject(AuthSessionService);

  readonly entities = signal<LegalEntity[]>([]);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly showForm = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  private requestSequence = 0;

  private readonly tenantReloadEffect = effect(() => {
    const tenantId = this.session.activeTenant()?.id;
    this.requestSequence += 1;
    this.entities.set([]);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    if (tenantId) {
      this.load(tenantId, this.requestSequence);
    } else {
      this.loading.set(false);
    }
  });

  readonly canManage = computed(() => {
    const role = this.session.activeTenant()?.membership_role;
    return role === 'OWNER' || role === 'ADMIN';
  });

  readonly form = new FormGroup({
    registration_number: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(100)],
    }),
    legal_name: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(255)],
    }),
    legal_form: new FormControl('', {
      nonNullable: true,
      validators: [Validators.maxLength(100)],
    }),
    country_code: new FormControl('ET', {
      nonNullable: true,
      validators: [Validators.required, Validators.pattern(/^[A-Z]{2}$/)],
    }),
    tax_identifier: new FormControl('', {
      nonNullable: true,
      validators: [Validators.maxLength(100)],
    }),
    base_currency: new FormControl('ETB', {
      nonNullable: true,
      validators: [Validators.required, Validators.pattern(/^[A-Z]{3}$/)],
    }),
  });

  load(expectedTenantId?: string, expectedSequence?: number): void {
    const tenantId = expectedTenantId ?? this.session.activeTenant()?.id;
    if (!tenantId) {
      this.entities.set([]);
      this.loading.set(false);
      return;
    }

    const requestSequence = expectedSequence ?? ++this.requestSequence;
    this.loading.set(true);
    this.errorMessage.set(null);

    this.api.list().subscribe({
      next: (response) => {
        if (requestSequence !== this.requestSequence || this.session.activeTenant()?.id !== tenantId) {
          return;
        }
        this.entities.set(response.data);
        this.loading.set(false);
      },
      error: () => {
        if (requestSequence !== this.requestSequence || this.session.activeTenant()?.id !== tenantId) {
          return;
        }
        this.entities.set([]);
        this.errorMessage.set('Legal entities could not be loaded. Check your tenant access and API connection.');
        this.loading.set(false);
      },
    });
  }

  create(): void {
    if (!this.canManage() || this.form.invalid || this.saving()) {
      this.form.markAllAsTouched();
      return;
    }

    const value = this.form.getRawValue();
    this.saving.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.api.create({
      registration_number: value.registration_number.trim(),
      legal_name: value.legal_name.trim(),
      legal_form: value.legal_form.trim() || null,
      country_code: value.country_code.trim().toUpperCase(),
      tax_identifier: value.tax_identifier.trim() || null,
      base_currency: value.base_currency.trim().toUpperCase(),
    }).subscribe({
      next: ({ data }) => {
        this.entities.update((current) => [data, ...current]);
        this.saving.set(false);
        this.showForm.set(false);
        this.form.reset({
          registration_number: '',
          legal_name: '',
          legal_form: '',
          country_code: 'ET',
          tax_identifier: '',
          base_currency: 'ETB',
        });
        this.successMessage.set('Draft legal entity created. Legal registration, licences and tax applicability still require evidence and review.');
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        if (error.status === 422) {
          this.errorMessage.set('Some fields are invalid or the registration number is already in use.');
        } else if (error.status === 403) {
          this.errorMessage.set('Your tenant role does not allow legal-entity registration.');
        } else {
          this.errorMessage.set('The draft legal entity could not be saved. Please retry or contact your administrator.');
        }
      },
    });
  }
}