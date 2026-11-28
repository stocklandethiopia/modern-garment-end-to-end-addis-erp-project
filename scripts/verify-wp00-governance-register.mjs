import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { pathToFileURL } from 'node:url';

export const REGISTER_PATH = 'docs/governance/decision-assumption-risk-exception-register-2026-11-28.md';
export const DELIVERY_BASELINE = '0a12c85cbfdc6a18c1bf1b67bf0e543e513b4374';

export const SOURCE_PINS = [
  ['docs/agent/project-governance-agreement.md', '36d5eff9490226c3a27eddc99095ea21c02c450f'],
  ['docs/work-packages/production-execution-plan-wp00-wp16.md', 'ce2e761071053f5acb35043f6fb176b4ddcf63e9'],
  ['docs/architecture/platform-standard.md', '6545f740b26226fd524ae0fa62576b2a5c1e86ba'],
  ['docs/engineering/code-quality.md', '56d48713f20e6dbd6bbb9bf3405a075d6b534356'],
  ['docs/security/saas-security.md', '305a0c34738e20607868e760cdcd976847af9781'],
  ['docs/engineering/development-deployment.md', 'c77af400eb1022696c654d6d30601eaa05882757'],
  ['docs/release/end-to-end-acceptance-security-deployment.md', 'ec0282421cad015f7a3ae113cfaba20c7db65266']
];

const TABLES = [
  {
    heading: '## 3. Decision register',
    label: 'decision',
    prefix: 'D',
    count: 13,
    headers: ['ID','Decision and current disposition','Authority / evidence','Affected WPs','Risk and required control','Review trigger / expiry'],
    evidenceColumns: [2,3,4,5]
  },
  {
    heading: '## 4. Assumption register',
    label: 'assumption',
    prefix: 'A',
    count: 8,
    headers: ['ID','Assumption or unresolved condition','Owner role','Status / evidence','Impacted WPs','Validation action','Review trigger / expiry'],
    evidenceColumns: [2,3,4,5,6]
  },
  {
    heading: '## 5. Risk register',
    label: 'risk',
    prefix: 'R',
    count: 12,
    headers: ['ID','Risk / consequence','Rating','Owner role','Current mitigation / linked action','Status','Review trigger / target'],
    evidenceColumns: [2,3,4,5,6]
  },
  {
    heading: '## 6. Exception register',
    label: 'exception',
    prefix: 'E',
    count: 6,
    headers: ['ID','Bounded exception / current status','Allowed use','Prohibited inference or use','Owner role','Expiry / review trigger'],
    evidenceColumns: [2,3,4,5]
  }
];

function parseTableRow(line) {
  const value = line.trim();
  if (!value.startsWith('|') || !value.endsWith('|')) return null;
  return value.slice(1, -1).split('|').map(cell => cell.trim());
}

function isSeparator(line) {
  const cells = parseTableRow(line);
  return Array.isArray(cells) && cells.length > 0 && cells.every(cell => /^:?-{3,}:?$/.test(cell));
}

function sectionTable(markdown, heading) {
  const lines = markdown.split(/\r?\n/);
  const start = lines.findIndex(line => line.trim() === heading);
  if (start < 0) return { error: 'missing section ' + heading };
  let headerIndex = start + 1;
  while (headerIndex < lines.length && !lines[headerIndex].trim().startsWith('|')) headerIndex += 1;
  if (headerIndex >= lines.length) return { error: 'missing table in ' + heading };
  const headers = parseTableRow(lines[headerIndex]);
  const separator = lines[headerIndex + 1];
  if (!headers || !isSeparator(separator)) return { error: 'invalid table header/separator in ' + heading };
  const rows = [];
  for (let index = headerIndex + 2; index < lines.length; index += 1) {
    if (!lines[index].trim()) {
      if (rows.length) break;
      continue;
    }
    if (!lines[index].trim().startsWith('|')) break;
    const cells = parseTableRow(lines[index]);
    if (!cells) return { error: 'malformed table row in ' + heading + ' at line ' + (index + 1) };
    rows.push({ line: index + 1, cells });
  }
  return { headers, rows };
}

export function validateRegisterText(markdown) {
  const errors = [];
  const counts = {};
  const allIds = [];
  if (!markdown.includes('Canonical baseline') || !markdown.includes(DELIVERY_BASELINE)) {
    errors.push('canonical delivery baseline does not match ' + DELIVERY_BASELINE);
  }

  const requiredSections = [
    '## 1. Purpose and control rule',
    '## 2. Authority and approval boundaries',
    ...TABLES.map(table => table.heading),
    '## 7. Change control and required record fields',
    '## 8. Current CAP checkpoint at publication',
    '## 9. Source evidence index',
    '## 10. Exact delivery and candidate-branch observation'
  ];
  for (const section of requiredSections) {
    if (!markdown.includes(section)) errors.push('required section missing: ' + section);
  }

  for (const table of TABLES) {
    const parsed = sectionTable(markdown, table.heading);
    if (parsed.error) {
      errors.push(parsed.error);
      continue;
    }
    if (JSON.stringify(parsed.headers) !== JSON.stringify(table.headers)) {
      errors.push(table.label + ' table headers do not match the governed schema');
      continue;
    }
    counts[table.label] = parsed.rows.length;
    if (parsed.rows.length !== table.count) {
      errors.push(table.label + ' count: expected ' + table.count + ', found ' + parsed.rows.length);
    }

    const expectedIds = Array.from({ length: table.count }, (_, index) =>
      table.prefix + '-' + String(index + 1).padStart(4, '0')
    );
    for (let index = 0; index < parsed.rows.length; index += 1) {
      const row = parsed.rows[index];
      if (row.cells.length !== table.headers.length) {
        errors.push(table.label + ' row at line ' + row.line + ' has ' + row.cells.length + ' columns; expected ' + table.headers.length);
        continue;
      }
      const expectedId = expectedIds[index];
      if (row.cells[0] !== expectedId) {
        errors.push(table.label + ' row ' + (index + 1) + ' must use ID ' + expectedId + '; found ' + row.cells[0]);
      }
      if (row.cells.some(cell => !cell)) {
        errors.push(table.label + ' row ' + row.cells[0] + ' has an empty required field');
      }
      for (const column of table.evidenceColumns) {
        if (!row.cells[column]) errors.push(table.label + ' row ' + row.cells[0] + ' lacks required evidence/control field ' + table.headers[column]);
      }
      allIds.push(row.cells[0]);
      if (table.label === 'exception' && !row.cells[1].includes('BOUNDED_EXCEPTION')) {
        errors.push('exception ' + row.cells[0] + ' must be explicitly marked BOUNDED_EXCEPTION');
      }
    }
  }

  if (new Set(allIds).size !== allIds.length) errors.push('record IDs are not globally unique');
  const requiredControls = [
    'Routine in-scope engineering',
    'Statutory interpretation and activation',
    'Financial authority and execution',
    'Production access, data and release operations',
    'No blanket waiver is granted',
    'DRAFT_REVIEW_REQUIRED',
    'not_started',
    '| CAP-001 | CLOSED',
    '| CAP-002 | CLOSED FOR STRUCTURAL IDENTIFIER RECONCILIATION; SEMANTIC REVIEW OPEN',
    '| CAP-003 | ACTIVE',
    '| CAP-004 | NEXT DEPENDENT ACTION',
    '37d88f175fffb805d4de38d108045ab8e8859825',
    '7a687a8954be0d9bf198ecee742f300f4e994439',
    '46162557764',
    '46162557766',
    '46162557765'
  ];
  for (const value of requiredControls) {
    if (!markdown.includes(value)) errors.push('required control/evidence missing: ' + value);
  }
  const expectedCandidateHeads = [
    '735d404fdf996e58bd07140cd900dc55c50c13ed',
    '4b89e9f7cbbc939d3080b70e3fc48b247e72abb5',
    '8497cfc76200e1263f0f5ce47afdb668773758bc',
    'b6e5486324e0eca18c065b253d1640ea7e6f2416',
    'c716757e3b0fe091b2e6223b416237d32e455c8e'
  ];
  for (const sha of expectedCandidateHeads) {
    if (!markdown.includes(sha)) errors.push('candidate PR inventory missing head SHA ' + sha);
  }
  return { errors, counts, uniqueIds: new Set(allIds).size };
}

function gitBlobSha(bytes) {
  const header = Buffer.from('blob ' + bytes.length + '\0', 'utf8');
  return crypto.createHash('sha1').update(Buffer.concat([header, bytes])).digest('hex');
}

export function validateSourcePins(root = process.cwd()) {
  const errors = [];
  for (const [relative, expectedSha] of SOURCE_PINS) {
    const absolute = path.join(root, relative);
    if (!fs.existsSync(absolute)) {
      errors.push('pinned source file missing: ' + relative);
      continue;
    }
    const actual = gitBlobSha(fs.readFileSync(absolute));
    if (actual !== expectedSha) errors.push('source blob SHA mismatch for ' + relative + ': expected ' + expectedSha + ', found ' + actual);
  }
  return errors;
}

function run() {
  const registerFile = path.join(process.cwd(), REGISTER_PATH);
  if (!fs.existsSync(registerFile)) {
    console.error('FAIL WP-00 governance register: missing ' + REGISTER_PATH);
    process.exitCode = 1;
    return;
  }
  const result = validateRegisterText(fs.readFileSync(registerFile, 'utf8'));
  const sourceErrors = validateSourcePins(process.cwd());
  const errors = [...result.errors, ...sourceErrors];
  if (errors.length) {
    for (const error of errors) console.error('FAIL WP-00 governance register: ' + error);
    process.exitCode = 1;
    return;
  }
  console.log('PASS WP-00 governance register integrity');
  console.log('Decision records: ' + result.counts.decision + '; assumptions: ' + result.counts.assumption +
    '; risks: ' + result.counts.risk + '; bounded exceptions: ' + result.counts.exception +
    '; unique IDs: ' + result.uniqueIds);
  console.log('All ' + SOURCE_PINS.length + ' cited source documents match their pinned Git blob SHAs.');
  console.log('CAP-002 semantic review remains open; human/professional and production approval boundaries remain intact.');
}

if (process.argv[1] && import.meta.url === pathToFileURL(path.resolve(process.argv[1])).href) run();
