import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { finalize } from 'rxjs';

import { AuthSessionService } from '../../core/auth/auth-session.service';
import { TenantMember, TenantMemberService } from './tenant-member.service';

@Component({
  selector: 'app-tenant-members',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './tenant-members.component.html',
  styleUrl: './tenant-members.component.css',
})
export class TenantMembersComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder);
  private readonly service = inject(TenantMemberService);
  readonly session = inject(AuthSessionService);

  readonly members = signal<TenantMember[]>([]);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly error = signal('');
  readonly notice = signal('');
  readonly showAddForm = signal(false);

  readonly form = this.formBuilder.nonNullable.group({
    email: ['', [Validators.required, Validators.email, Validators.maxLength(254)]],
    membership_role: ['MEMBER' as 'ADMIN' | 'MEMBER', Validators.required],
    reason: ['', [Validators.required, Validators.minLength(10), Validators.maxLength(2000)]],
  });

  ngOnInit(): void {
    this.refresh();
  }

  canManage(): boolean {
    return ['OWNER', 'ADMIN'].includes(this.session.activeTenant()?.membership_role ?? '');
  }

  isOwner(): boolean {
    return this.session.activeTenant()?.membership_role === 'OWNER';
  }

  isSelf(member: TenantMember): boolean {
    return member.user_id === this.session.user()?.id;
  }

  refresh(): void {
    this.loading.set(true);
    this.error.set('');
    this.service.list().pipe(finalize(() => this.loading.set(false))).subscribe({
      next: (response) => this.members.set(response.data.data),
      error: () => this.error.set('Unable to load tenant members. Check your access and connection, then retry.'),
    });
  }

  add(): void {
    if (this.form.invalid || this.saving()) {
      this.form.markAllAsTouched();
      return;
    }

    const value = this.form.getRawValue();
    this.saving.set(true);
    this.error.set('');
    this.notice.set('');

    this.service.add(value).pipe(finalize(() => this.saving.set(false))).subscribe({
      next: () => {
        this.form.reset({ email: '', membership_role: 'MEMBER', reason: '' });
        this.showAddForm.set(false);
        this.notice.set('Tenant membership added and audited.');
        this.refresh();
      },
      error: (response: { error?: { message?: string; errors?: Record<string, string[]> } }) => {
        this.error.set(response.error?.errors?.['email']?.[0] ?? response.error?.message ?? 'The member could not be added.');
      },
    });
  }

  changeRole(member: TenantMember, role: 'ADMIN' | 'MEMBER'): void {
    if (!this.isOwner() || member.membership_role === 'OWNER' || member.status !== 'ACTIVE') {
      return;
    }

    const reason = window.prompt('Enter a reason (at least 10 characters) for the role change:');
    if (!reason || reason.trim().length < 10) {
      return;
    }

    this.saving.set(true);
    this.error.set('');
    this.service.changeRole(member.user_id, { membership_role: role, reason: reason.trim() })
      .pipe(finalize(() => this.saving.set(false)))
      .subscribe({
        next: () => {
          this.notice.set('Member role updated and audited.');
          this.refresh();
        },
        error: (response: { error?: { message?: string } }) => this.error.set(response.error?.message ?? 'The role could not be changed.'),
      });
  }

  revoke(member: TenantMember): void {
    if (!this.canManage() || member.membership_role === 'OWNER' || this.isSelf(member) || member.status !== 'ACTIVE') {
      return;
    }

    const reason = window.prompt('Enter a reason (at least 10 characters) for revoking this membership:');
    if (!reason || reason.trim().length < 10) {
      return;
    }

    this.saving.set(true);
    this.error.set('');
    this.service.revoke(member.user_id, reason.trim())
      .pipe(finalize(() => this.saving.set(false)))
      .subscribe({
        next: () => {
          this.notice.set('Membership revoked. Its history remains recorded.');
          this.refresh();
        },
        error: (response: { error?: { message?: string } }) => this.error.set(response.error?.message ?? 'The membership could not be revoked.'),
      });
  }

  reinstate(member: TenantMember): void {
    if (!this.canManage() || member.status !== 'REVOKED' || (member.membership_role === 'ADMIN' && !this.isOwner())) {
      return;
    }

    const reason = window.prompt('Enter a reason (at least 10 characters) for reinstating this membership:');
    if (!reason || reason.trim().length < 10) {
      return;
    }

    const role = member.membership_role === 'ADMIN' && this.isOwner() ? 'ADMIN' : 'MEMBER';
    this.saving.set(true);
    this.error.set('');
    this.service.add({ email: member.email, membership_role: role, reason: reason.trim() })
      .pipe(finalize(() => this.saving.set(false)))
      .subscribe({
        next: () => {
          this.notice.set('Membership reinstated and audited.');
          this.refresh();
        },
        error: (response: { error?: { message?: string } }) => this.error.set(response.error?.message ?? 'The membership could not be reinstated.'),
      });
  }
}
