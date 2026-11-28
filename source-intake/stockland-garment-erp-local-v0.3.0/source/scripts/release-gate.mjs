import fs from 'node:fs';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createApplication } from '../src/server.mjs';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const failures = [];
const pass = (message) => console.log(`PASS ${message}`);
const fail = (message) => { failures.push(message); console.error(`FAIL ${message}`); };

const requiredFiles = [
  'README.md', 'package.json', '.gitignore', '.env.example', 'BUILD_MANIFEST.json',
  'scripts/check.mjs', 'scripts/bootstrap.mjs', 'scripts/build-manifest.mjs', 'scripts/verify-manifest.mjs', 'scripts/release-gate.mjs',
  '.github/README.md', '.github/workflows/README.md', '.github/workflows/ci.yml',
  'docs/README.md', 'docs/PROJECT_CHARTER.md', 'docs/ARCHITECTURE.md',
  'docs/API_REFERENCE.md', 'docs/DATABASE_SCHEMA.md', 'docs/SECURITY_MODEL.md',
  'docs/WORKFLOWS.md', 'docs/POS_IMPORT_RELEASE.md', 'docs/TECHNICAL_REQUIREMENTS.md',
  'docs/TEST_STRATEGY.md', 'docs/RELEASE_GATES.md', 'docs/VERIFICATION_REPORT.md',
  'docs/IMPLEMENTATION_BOUNDARIES.md', 'docs/REQUIREMENTS_TRACEABILITY.csv',
  'docs/OPEN_SOURCE_PROVENANCE.md', 'docs/OPERATIONS_RUNBOOK.md',
  'docs/RISK_REGISTER.md', 'docs/DECISIONS.md', 'docs/RELEASE_NOTES.md',
  'docs/KNOWN_LIMITATIONS.md', 'docs/ARTIFACT_INVENTORY.md',
  'docs/standards/README.md', 'docs/standards/ENGINEERING_DOC_TEMPLATE.md',
  'docs/standards/RELEASE_EVIDENCE_TEMPLATE.md',
  'docs/modules/README.md', 'docs/modules/PRODUCT_CATALOG.md', 'docs/modules/INVENTORY.md',
  'docs/modules/PROCUREMENT.md', 'docs/modules/POS.md', 'docs/modules/WHOLESALE_CUSTOMERS.md',
  'docs/modules/FINANCE.md', 'docs/modules/HR_PAYROLL.md', 'docs/modules/BRANCH_OPERATIONS.md',
  'reference-sources/README.md',
  'reference-sources/OPEN_SOURCE_REFERENCE_KIT/README.md',
  'reference-sources/OPEN_SOURCE_REFERENCE_KIT/SOURCE_MANIFEST.json',
  'reference-sources/OPEN_SOURCE_REFERENCE_KIT/LICENSE_COMPLIANCE.md',
  'reference-sources/OPEN_SOURCE_REFERENCE_KIT/reference-sources/xpos/LICENSE',
  'src/README.md', 'src/modules/README.md', 'src/modules/enterprise.mjs',
  'src/migrations/README.md', 'src/migrations/002_enterprise.sql',
  'src/server.mjs', 'src/db.mjs', 'src/security.mjs',
  'public/README.md', 'public/index.html', 'public/app.js', 'public/app.css',
  'public/manifest.webmanifest', 'public/sw.js', 'public/icons/README.md',
  'public/icons/stockland.svg', 'public/physical-count-template.csv',
  'scripts/README.md', 'tests/README.md', 'tests/erp.test.mjs',
  'templates/README.md', 'templates/imports/README.md',
  'templates/pos-import-template.csv', 'templates/physical-count-template.csv',
  'templates/imports/product-variant-template.csv',
  'templates/imports/supplier-purchase-order-template.csv',
  'templates/imports/employee-template.csv'
];
for (const rel of requiredFiles) {
  if (!fs.existsSync(path.join(root, rel))) fail(`required artifact exists: ${rel}`);
}
if (!failures.length) pass(`required documentation and source artifacts exist (${requiredFiles.length} checked)`);

try {
  execFileSync(process.execPath, [path.join(root, 'scripts/verify-manifest.mjs')], { stdio: 'pipe' });
  pass('build manifest file inventory and SHA-256 values match');
} catch (error) {
  fail(`build manifest verification (${error.stderr?.toString().trim() || error.message})`);
}

const pkg = JSON.parse(fs.readFileSync(path.join(root, 'package.json'), 'utf8'));
if (pkg.version !== '0.3.0') fail(`application version is 0.3.0 (actual ${pkg.version})`);
else pass(`application package version ${pkg.version}`);
const [major, minor] = process.versions.node.split('.').map(Number);
if (major < 22 || (major === 22 && minor < 5)) fail(`Node.js runtime >=22.5.0 (actual ${process.versions.node})`);
else pass(`Node.js runtime ${process.versions.node} satisfies >=22.5.0`);
if (!pkg.private) fail('package is marked private until a deliberate public licence/release review');
else pass('package is marked private (no accidental npm publication)');
if (!pkg.scripts?.check || !pkg.scripts?.test || !pkg.scripts?.['release:gate'] || !pkg.scripts?.['manifest:build'] || !pkg.scripts?.['verify:manifest']) fail('package declares the complete validation and manifest command set');
else pass('syntax, integration, release-gate and manifest commands are declared');

const tracePath = path.join(root, 'docs/REQUIREMENTS_TRACEABILITY.csv');
if (fs.existsSync(tracePath)) {
  const lines = fs.readFileSync(tracePath, 'utf8').trim().split(/\r?\n/);
  const header = lines.shift()?.split(',') || [];
  const requiredColumns = ['requirement_id', 'requirement', 'implementation_reference', 'verification', 'coverage'];
  if (requiredColumns.some(c => !header.includes(c))) fail('traceability CSV has the required columns');
  else if (!lines.length) fail('traceability CSV contains at least one requirement');
  else if (lines.some(line => !line.split(',')[0]?.trim())) fail('every traceability row has a requirement ID');
  else if (lines.some(line => !line.includes('local-pass'))) fail('every traceability row has local verification evidence');
  else pass(`requirements traceability is populated and verified (${lines.length} entries)`);
}

const gitignore = fs.readFileSync(path.join(root, '.gitignore'), 'utf8');
for (const pattern of ['.env', '/data/', '/node_modules/']) {
  if (!gitignore.split(/\r?\n/).includes(pattern)) fail(`.gitignore excludes ${pattern}`);
}
if (!failures.some(x => x.includes('.gitignore'))) pass('environment secrets, local database and dependencies are ignored by Git');

// Check relative Markdown links in all docs and folder READMEs.
const mdFiles = [];
function walk(dir) {
  for (const ent of fs.readdirSync(dir, { withFileTypes: true })) {
    if (ent.name === 'node_modules' || ent.name === 'data' || ent.name === '.git') continue;
    const abs = path.join(dir, ent.name);
    if (ent.isDirectory()) walk(abs);
    else if (ent.name.endsWith('.md')) mdFiles.push(abs);
  }
}
walk(root);
let linkCount = 0;
for (const file of mdFiles) {
  const body = fs.readFileSync(file, 'utf8');
  for (const match of body.matchAll(/\[[^\]]+\]\(([^)]+)\)/g)) {
    const link = match[1].trim();
    if (/^(https?:|mailto:|#)/i.test(link)) continue;
    const target = decodeURIComponent(link.split('#')[0]);
    if (!target) continue;
    linkCount++;
    const resolved = path.resolve(path.dirname(file), target);
    if (!resolved.startsWith(root + path.sep) && resolved !== root) {
      fail(`Markdown link escapes package: ${path.relative(root, file)} -> ${link}`);
    } else if (!fs.existsSync(resolved)) {
      fail(`Markdown link target exists: ${path.relative(root, file)} -> ${link}`);
    }
  }
}
if (!failures.some(x => x.startsWith('Markdown link'))) pass(`Markdown relative links validated (${linkCount} checked across ${mdFiles.length} files)`);

const workflowPath = path.join(root, '.github/workflows/ci.yml');
const workflow = fs.readFileSync(workflowPath, 'utf8');
for (const command of ['npm run check', 'npm test', 'npm run release:gate', 'npm run verify:manifest']) {
  if (!workflow.includes(command)) fail(`GitHub Actions workflow includes ${command}`);
}
if (!failures.some(x => x.includes('GitHub Actions workflow'))) pass('GitHub Actions workflow includes all local validation commands');

const frontend = fs.readFileSync(path.join(root, 'public/app.js'), 'utf8');
const requiredViews = ['dashboard','products','suppliers','customers','procurement','inventory','requisitions','reservations','counts','pos','returns','wholesale','imports','finance','hr','closing','reports','users','audit'];
const missingViews = requiredViews.filter(view => !frontend.includes(`['${view}'`));
if (missingViews.length) fail(`browser workspace navigation includes every ERP module (${missingViews.join(', ')})`);
else pass(`browser workspace navigation exposes all ${requiredViews.length} ERP modules`);

// Runtime smoke using an in-memory database; no local customer data is touched.
let app;
try {
  app = createApplication({ dbPath: ':memory:', publicDir: path.join(root, 'public') });
  await new Promise((resolve, reject) => {
    app.server.once('error', reject);
    app.server.listen(0, '127.0.0.1', resolve);
  });
  const base = `http://127.0.0.1:${app.server.address().port}`;
  const [health, home, js, css, manifest, worker, icon, countTemplate] = await Promise.all([
    fetch(`${base}/api/health`), fetch(`${base}/`), fetch(`${base}/app.js`), fetch(`${base}/app.css`),
    fetch(`${base}/manifest.webmanifest`), fetch(`${base}/sw.js`), fetch(`${base}/icons/stockland.svg`),
    fetch(`${base}/physical-count-template.csv`)
  ]);
  const healthBody = await health.json();
  const tableCount = Number(app.db.prepare("SELECT COUNT(*) n FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'").get().n);
  const schemaVersion = Number(app.db.prepare('PRAGMA user_version').get().user_version);
  if (health.status !== 200 || healthBody.database !== 'connected' || healthBody.setupRequired !== true || healthBody.version !== '0.3.0') fail('health endpoint returns version 0.3.0 and connected empty-database state');
  else pass('HTTP /api/health returns version 0.3.0 and a connected database');
  if (tableCount !== 45 || schemaVersion !== 2) fail(`database schema version 2 with 45 tables (actual v${schemaVersion}, ${tableCount} tables)`);
  else pass('SQLite schema v2 initializes all 45 application tables');
  const dbTables = app.db.prepare("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name").all().map(row => row.name);
  const schemaDoc = fs.readFileSync(path.join(root, 'docs/DATABASE_SCHEMA.md'), 'utf8');
  const documentedTables = [...schemaDoc.matchAll(/^### `([^`]+)`$/gm)].map(match => match[1]);
  const undocumentedTables = dbTables.filter(name => !documentedTables.includes(name));
  const duplicateDocs = documentedTables.filter((name, index) => documentedTables.indexOf(name) !== index);
  if (undocumentedTables.length || duplicateDocs.length || documentedTables.length !== dbTables.length) fail(`database dictionary contains exactly one column-level section for every table (undocumented: ${undocumentedTables.join(', ') || 'none'}; duplicate: ${duplicateDocs.join(', ') || 'none'})`);
  else pass(`database dictionary documents all ${dbTables.length} tables with column-level sections`);
  if (home.status !== 200 || !(await home.text()).includes('Stockland ERP')) fail('web application root loads');
  else pass('web application root returns HTTP 200');
  const jsBody = await js.text();
  if (js.status !== 200 || !jsBody.includes('start()')) fail('frontend JavaScript asset loads');
  else pass('frontend JavaScript asset returns HTTP 200');
  if (css.status !== 200 || !(await css.text()).includes(':root')) fail('frontend stylesheet loads');
  else pass('frontend stylesheet returns HTTP 200');
  if (manifest.status !== 200 || !(await manifest.text()).includes('Stockland')) fail('PWA manifest loads');
  else pass('PWA manifest returns HTTP 200');
  if (worker.status !== 200 || !(await worker.text()).includes('stockland-shell-v0.3.0')) fail('PWA service worker loads');
  else pass('PWA service worker returns HTTP 200');
  if (icon.status !== 200 || !(await icon.text()).includes('<svg')) fail('application SVG icon loads');
  else pass('application icon returns HTTP 200');
  if (countTemplate.status !== 200 || !(await countTemplate.text()).includes('sku,countedQty')) fail('physical count CSV template loads');
  else pass('physical-count CSV template returns HTTP 200');
  if (health.headers.get('x-frame-options') !== 'DENY' || !health.headers.get('content-security-policy') || health.headers.get('x-content-type-options') !== 'nosniff') fail('baseline security headers are present');
  else pass('CSP, frame-denial and nosniff security headers are present');
  const unsupported = await fetch(`${base}/api/setup`, { method: 'POST', headers: { 'Content-Type': 'text/plain' }, body: 'bad' });
  if (unsupported.status !== 415) fail('unsupported content type is rejected');
  else pass('unsupported content type returns HTTP 415');
} catch (error) {
  fail(`runtime HTTP smoke test (${error.message})`);
} finally {
  if (app) await app.close();
}

if (failures.length) {
  console.error(`\nRELEASE GATE: FAIL (${failures.length} failure(s))`);
  process.exitCode = 1;
} else {
  console.log('\nSTOCKLAND LOCAL RELEASE GATE: PASS');
  console.log('Automated integration tests are run by the separate `npm test` step in the release workflow.');
}
