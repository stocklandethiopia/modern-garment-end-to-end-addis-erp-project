import { DatePipe } from '@angular/common';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { finalize } from 'rxjs';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { PlatformOperatorGrant, PlatformOperatorsService, PlatformRoleCode } from './platform-operators.service';

@Component({
  selector: 'app-platform-operators',
  standalone: true,
  imports: [DatePipe, ReactiveFormsModule],
  templateUrl: './platform-operators.component.html',
  styleUrl: './platform-operators.component.css',
})
export class PlatformOperatorsComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly service = inject(PlatformOperatorsService);
  readonly session = inject(AuthSessionService);

  readonly grants = signal<PlatformOperatorGrant[]>([]);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly error = signal('');
  readonly notice = signal('');

  readonly form = this.formBuilder.nonNullable.group({
    user_email: ['', [Validators.required, Validators.email, Validators.maxLength(254)]],
    role_code: ['PLATFORM_SUPPORT' as PlatformRoleCode, Validators.required],
    valid_from: [new Date().toISOString().slice(0, 10), Validators.required],
    valid_to: [''],
    reason: ['', [Validators.required, Validators.minLength(10), Validators.maxLength(500)]],
  });

  ngOnInit(): void {
    if (this.canManage()) {
      this.refresh();
    } else {
      this.error.set('An active PLATFORM_ADMIN grant is required to administer global platform operators.');
    }
  }

  canManage(): boolean {
    return this.session.canManagePlatform();
  }

  canApprove(grant: PlatformOperatorGrant): boolean {
    const actorId = this.session.user()?.id;
    return this.canManage()
      && grant.grant_status === 'PENDING'
      && !!actorId
      && grant.granted_by !== actorId
      && grant.user_id !== actorId;
  }

  canRevoke(grant: PlatformOperatorGrant): boolean {
    const actorId = this.session.user()?.id;
    return this.canManage()
      && ['PENDING', 'ACTIVE'].includes(grant.grant_status)
      && !!actorId
      && grant.user_id !== actorId;
  }

  refresh(): void {
    if (!this.canManage()) {
      this.error.set('An active PLATFORM_ADMIN grant is required to administer global platform operators.');
      return;
    }

    this.loading.set(true);
    this.error.set('');
    this.service.list().pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (response) => this.grants.set(response.data),
      error: (response: { error?: { message?: string } }) => {
        this.error.set(response.error?.message ?? 'The platform operator register could not be loaded.');
      },
    });
  }

  requestGrant(): void {
    if (this.form.invalid || this.saving() || !this.canManage()) {
      this.form.markAllAsTouched();
      return;
    }

    const value = this.form.getRawValue();
    this.saving.set(true);
    this.error.set('');
    this.notice.set('');

    this.service.request({
      user_email: value.user_email.trim().toLowerCase(),
      role_code: value.role_code,
      valid_from: value.valid_from,
      valid_to: value.valid_to || null,
      reason: value.reason.trim(),
    }).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => {
        this.form.reset({
          user_email: '',
          role_code: 'PLATFORM_SUPPORT',
          valid_from: new Date().toISOString().slice(0, 10),
          valid_to: '',
          reason: '',
        });
        this.notice.set('Grant request recorded. A different active platform administrator must approve it before the role becomes effective.');
        this.refresh();
      },
      error: (response: { error?: { message?: string; errors?: Record<string, string[]> } }) => {
        this.error.set(
          response.error?.errors?.['platform']?.[0]
          ?? response.error?.errors?.['user_email']?.[0]
          ?? response.error?.message
          ?? 'The platform operator grant request was rejected.',
        );
      },
    });
  }

  approve(grant: PlatformOperatorGrant): void {
    if (!this.canApprove(grant) || this.saving()) {
      return;
    }

    const reason = window.prompt('Enter the independent approval reason (at least 10 characters):');
    if (!reason || reason.trim().length < 10) {
      return;
    }

    this.saving.set(true);
    this.error.set('');
    this.notice.set('');

    this.service.approve(grant.grant_id, reason.trim())
      .pipe(finalize(() => this.saving.set(false)))
      .subscribe({
        next: () => {
          this.notice.set('Platform operator grant approved and recorded in the global audit history.');
          this.refresh();
        },
        error: (response: { error?: { message?: string } }) => {
          this.error.set(response.error?.message ?? 'The platform operator grant could not be approved.');
        },
      });
  }

  revoke(grant: PlatformOperatorGrant): void {
    if (!this.canRevoke(grant) || this.saving()) {
      return;
    }

    const reason = window.prompt('Enter the revocation reason (at least 10 characters):');
    if (!reason || reason.trim().length < 10) {
      return;
    }

    this.saving.set(true);
    this.error.set('');
    this.notice.set('');

    this.service.revoke(grant.grant_id, reason.trim())
      .pipe(finalize(() => this.saving.set(false)))
      .subscribe({
        next: () => {
          this.notice.set('Platform operator grant revoked. Its historical record remains in the global audit history.');
          this.refresh();
        },
        error: (response: { error?: { message?: string } }) => {
          this.error.set(response.error?.message ?? 'The platform operator grant could not be revoked.');
        },
      });
  }
}
