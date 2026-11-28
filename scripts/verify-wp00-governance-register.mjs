import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';

const root = process.cwd();
const registerPath = 'docs/governance/decision-assumption-risk-exception-register-2026-11-28.md';
const register = fs.readFileSync(path.join(root, registerPath), 'utf8');
const fail = message => { console.error('FAIL WP-00 governance register: ' + message); process.exit(1); };

const patterns = [
  [/^\| (D-\d{4}) \|/gm, 13, 'decision'],
  [/^\| (A-\d{4}) \|/gm, 8, 'assumption'],
  [/^\| (R-\d{4}) \|/gm, 12, 'risk'],
  [/^\| (E-\d{4}) \|/gm, 6, 'exception']
];
const ids = [];
for (const [pattern, expectedCount, label] of patterns) {
  const found = [...register.matchAll(pattern)].map(match => match[1]);
  if (found.length !== expectedCount) fail(label + ' count: expected ' + expectedCount + ', found ' + found.length);
  ids.push(...found);
}
if (new Set(ids).size !== ids.length) fail('register IDs are not globally unique');

const requiredSections = [
  '## 1. Purpose and control rule',
  '## 2. Authority and approval boundaries',
  '## 3. Decision register',
  '## 4. Assumption register',
  '## 5. Risk register',
  '## 6. Exception register',
  '## 7. Change control and required record fields',
  '## 8. Current CAP checkpoint at publication',
  '## 9. Source evidence index'
];
for (const section of requiredSections) {
  if (!register.includes(section)) fail('required section missing: ' + section);
}

const requiredControls = [
  'Routine in-scope engineering',
  'Statutory interpretation and activation',
  'Financial authority and execution',
  'Production access, data and release operations',
  'DRAFT_REVIEW_REQUIRED',
  'canonical_implementation_status',
  'bc442495df842f8fb06ae8b8d540145c69155023'
];
for (const value of requiredControls) {
  if (!register.includes(value)) fail('required governance boundary/evidence missing: ' + value);
}

const sourceFiles = [
  ['docs/agent/project-governance-agreement.md', '36d5eff9490226c3a27eddc99095ea21c02c450f'],
  ['docs/work-packages/production-execution-plan-wp00-wp16.md', 'ce2e761071053f5acb35043f6fb176b4ddcf63e9'],
  ['docs/architecture/platform-standard.md', '6545f740b26226fd524ae0fa62576b2a5c1e86ba'],
  ['docs/engineering/code-quality.md', '56d48713f20e6dbd6bbb9bf3405a075d6b534356'],
  ['docs/security/saas-security.md', '305a0c34738e20607868e760cdcd976847af9781'],
  ['docs/engineering/development-deployment.md', 'c77af400eb1022696c654d6e30601eaa05882757'],
  ['docs/release/end-to-end-acceptance-security-deployment.md', 'ec0282421cad015f7a3ae113cfaba20c7db65266']
];
for (const [relative, expectedSha] of sourceFiles) {
  const absolute = path.join(root, relative);
  if (!fs.existsSync(absolute)) fail('source evidence file missing: ' + relative);
  const bytes = fs.readFileSync(absolute);
  const header = Buffer.from('blob ' + bytes.length + '\0', 'utf8');
  const actual = crypto.createHash('sha1')
    .update(Buffer.concat([header, bytes]))
    .digest('hex');
  if (actual !== expectedSha) fail('source blob SHA mismatch for ' + relative + ': expected ' + expectedSha + ', found ' + actual);
}

const expectedStatuses = [
  '| CAP-001 | CLOSED',
  '| CAP-002 | CLOSED FOR STRUCTURAL IDENTIFIER RECONCILIATION',
  '| CAP-003 | ACTIVE',
  '| CAP-004 | NEXT DEPENDENT ACTION'
];
for (const value of expectedStatuses) {
  if (!register.includes(value)) fail('CAP checkpoint stale or missing: ' + value);
}

const records = register.split(/\r?\n/).filter(line => /^\| [DARE]-\d{4} \|/.test(line));
if (records.some(line => !line.endsWith('|'))) fail('one or more governance record rows are not complete Markdown table rows');

console.log('PASS WP-00 governance register integrity');
console.log('Decision records: 13; assumptions: 8; risks: 12; bounded exceptions: 6; unique IDs: ' + ids.length);
console.log('All 7 cited source documents exist and match their pinned Git blob SHAs.');
console.log('Approval boundaries and CAP-001/CAP-002 closeout plus CAP-003/CAP-004 state are present.');
