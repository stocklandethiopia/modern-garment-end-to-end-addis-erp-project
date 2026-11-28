import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { validateRegisterText, validateSourcePins } from '../scripts/verify-wp00-governance-register.mjs';

const registerPath = path.join(process.cwd(), 'docs/governance/decision-assumption-risk-exception-register-2026-11-28.md');
const register = fs.readFileSync(registerPath, 'utf8');

test('governance register contains the complete, uniquely numbered control records', () => {
  const result = validateRegisterText(register);
  assert.deepEqual(result.errors, []);
  assert.equal(result.counts.decision, 13);
  assert.equal(result.counts.assumption, 8);
  assert.equal(result.counts.risk, 12);
  assert.equal(result.counts.exception, 6);
  assert.equal(result.uniqueIds, 39);
});

test('governance validator rejects a missing mandatory decision field', () => {
  const originalRow = register.split('\n').find(line => line.startsWith('| D-0001 |'));
  assert.ok(originalRow);
  const cells = originalRow.split('|');
  cells[2] = ' ';
  const corrupted = register.replace(originalRow, cells.join('|'));
  assert.notEqual(corrupted, register);
  assert.ok(validateRegisterText(corrupted).errors.some(error => error.includes('empty required field')));
});

test('governance validator rejects duplicate or out-of-order record identifiers', () => {
  const corrupted = register.replace('| D-0002 |', '| D-0001 |');
  assert.notEqual(corrupted, register);
  assert.ok(validateRegisterText(corrupted).errors.some(error => error.includes('must use ID D-0002')));
});

test('governance validator rejects a stale canonical delivery baseline', () => {
  const corrupted = register.replace('0a12c85cbfdc6a18c1bf1b67bf0e543e513b4374', 'bc442495df842f8fb06ae8b8d540145c69155023');
  assert.notEqual(corrupted, register);
  assert.ok(validateRegisterText(corrupted).errors.some(error => error.includes('canonical delivery baseline')));
});

test('governance validator verifies every pinned source file against its Git blob SHA', () => {
  assert.deepEqual(validateSourcePins(process.cwd()), []);
});

test('governance register records the exact CAP-003 tested head, merge and workflow receipts', () => {
  const result = validateRegisterText(register);
  assert.deepEqual(result.errors, []);
  for (const receipt of ['e42db8d4347c01000ce1b189bcc80d10a7235330', '56ff9a88906f0749e5dba9883d4293d2a305c1f8', '3a46fd213516ad91fb5f41b680a56cdfa827537e', '46164088400', '46164088349', '46164088337', '15270747713', '1db1212e48178d8045a6eedd568688ffd7ee3660b172694975db5185e333d5af']) {
    assert.ok(register.includes(receipt), 'missing exact CAP-003 evidence receipt: ' + receipt);
  }
});
