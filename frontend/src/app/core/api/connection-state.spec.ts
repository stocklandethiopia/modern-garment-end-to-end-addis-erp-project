import { describe, expect, it } from 'vitest';

import { ApiHealth } from './api-health.service';
import { toConnectionState } from './connection-state';

describe('toConnectionState', () => {
  it('reports a healthy API as connected', () => {
    const health: ApiHealth = {
      status: 'ok',
      service: 'modern-garment-erp-api',
      version: '0.1.0',
      time: '2026-11-22T00:00:00Z',
    };

    expect(toConnectionState(health, false)).toBe('connected');
  });

  it('reports a failed request as unavailable', () => {
    expect(toConnectionState(null, true)).toBe('unavailable');
  });

  it('does not mark an unchecked API as connected', () => {
    expect(toConnectionState(null, false)).toBe('checking');
  });
});