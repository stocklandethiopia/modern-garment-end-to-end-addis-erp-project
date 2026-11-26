import { describe, expect, it } from 'vitest';

import { toLocalDateInputValue } from './date-input';

describe('toLocalDateInputValue', () => {
  it('uses the browser local calendar date rather than the UTC date', () => {
    const date = new Date(2026, 10, 27, 1, 30, 0);

    expect(toLocalDateInputValue(date)).toBe('2026-11-27');
  });

  it('zero-pads single-digit month and day values', () => {
    const date = new Date(2026, 0, 5, 12, 0, 0);

    expect(toLocalDateInputValue(date)).toBe('2026-01-05');
  });
});
