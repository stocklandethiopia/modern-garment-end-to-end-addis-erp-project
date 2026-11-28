import assert from 'node:assert/strict';
import fs from 'node:fs';
import test from 'node:test';
import { verifyCrosswalk } from './verify-r3-gar-crosswalk.mjs';

const r3 = fs.readFileSync(new URL('../docs/requirements/r3-to-gar-crosswalk-2026-11-28.csv', import.meta.url), 'utf8');
const gar = fs.readFileSync(new URL('../docs/requirements/gar-legacy-disposition-2026-11-28.csv', import.meta.url), 'utf8');

test('R3 and GAR crosswalk preserves inventory, provenance and implementation status', () => {
  assert.deepEqual(verifyCrosswalk(r3, gar), {
    r3Records: 128, r3Baseline: 122, productOwnerSupplied: 6, r3Additions: 8,
    garRecords: 120, garWithPartialR3Relationships: 117, garPreservedByStandards: 3,
    retiredGarRecords: 0, implementationStatuses: ['not_started'],
  });
});

test('rejects a falsely inferred Product Owner requirement ID', () => {
  const changed = r3.replace('DWR-007,WP-15,PRODUCT_OWNER_SUPPLIED', 'DWR-014,WP-15,PRODUCT_OWNER_SUPPLIED');
  assert.throws(() => verifyCrosswalk(changed, gar), /Product Owner-supplied IDs/);
});

test('rejects a removed GAR requirement', () => {
  const changed = gar.split('\n').filter((line) => !line.startsWith('GAR-FIN-010,')).join('\n');
  assert.throws(() => verifyCrosswalk(r3, changed), /Expected 120 GAR records/);
});

test('rejects an implementation status incorrectly advanced by reconciliation', () => {
  const changed = r3.replace('PLT-001,WP-03,R3_BASELINE,GAR-ENT-001,,PARTIAL,not_started',
    'PLT-001,WP-03,R3_BASELINE,GAR-ENT-001,,PARTIAL,implemented');
  assert.throws(() => verifyCrosswalk(changed, gar), /implementation status must remain not_started/);
});
