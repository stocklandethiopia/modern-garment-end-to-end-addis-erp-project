import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';

import { AuthSessionService } from './auth-session.service';

export const authInterceptor: HttpInterceptorFn = (request, next) => {
  if (!request.url.startsWith('/api/')) {
    return next(request);
  }

  const session = inject(AuthSessionService);
  const token = session.accessToken();
  const tenant = session.activeTenant();

  const headers: Record<string, string> = {};

  if (token) {
    headers['Authorization'] = \`Bearer \${token}\`;
  }

  if (tenant) {
    headers['X-Tenant-Id'] = tenant.id;
  }

  return next(Object.keys(headers).length > 0 ? request.clone({ setHeaders: headers }) : request);
};