import { ApiHealth } from './api-health.service';

export type ConnectionState = 'checking' | 'connected' | 'unavailable';

export function toConnectionState(
  health: ApiHealth | null,
  hasError: boolean,
): ConnectionState {
  if (health?.status === 'ok' && !hasError) {
    return 'connected';
  }

  return hasError ? 'unavailable' : 'checking';
}