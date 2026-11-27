import { Component, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';

import { AuthSessionService } from '../../core/auth/auth-session.service';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [ReactiveFormsModule],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css',
})
export class LoginComponent {
  private readonly session = inject(AuthSessionService);
  private readonly router = inject(Router);

  readonly busy = signal(false);
  readonly errorMessage = signal<string | null>(null);

  readonly form = new FormGroup({
    email: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.email, Validators.maxLength(254)],
    }),
    password: new FormControl('', {
      nonNullable: true,
      validators: [Validators.required, Validators.maxLength(200)],
    }),
  });

  submit(): void {
    if (this.form.invalid || this.busy()) {
      this.form.markAllAsTouched();
      return;
    }

    this.errorMessage.set(null);
    this.busy.set(true);

    this.session.login(this.form.controls.email.value, this.form.controls.password.value)
      .subscribe({
        next: () => {
          this.busy.set(false);
          if (!this.session.activeTenant() && this.session.isPlatformOperator()) {
            this.router.navigateByUrl('/platform/tenants');
          }
        },
        error: (error: unknown) => {
          this.busy.set(false);
          this.errorMessage.set(
            error instanceof Error && error.message
              ? error.message
              : 'Sign-in failed. Check your email and password, then try again.',
          );
        },
      });
  }
}