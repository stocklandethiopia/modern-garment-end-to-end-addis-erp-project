import fs from 'node:fs';
import path from 'node:path';
import { gunzipSync } from 'node:zlib';

const root = process.cwd();
const r3Root = process.env.WP01_R3_ROOT ?? '/tmp/wp01-r3-source/wp01-r2-release/package';
const r3Path = path.join(r3Root, 'docs/requirements/canonical-requirements.json');
const garPath = path.join(root, 'docs/requirements/requirements-traceability.md');
const crosswalkPath = path.join(root, 'docs/requirements/wp01-r3-gar-crosswalk-2026-11-28.tsv.gz');
const fail = message => { console.error('FAIL WP-01 R3↔GAR crosswalk: ' + message); process.exit(1); };

for (const file of [r3Path, garPath, crosswalkPath]) {
  if (!fs.existsSync(file)) fail('required input not found: ' + file);
}

const r3 = JSON.parse(fs.readFileSync(r3Path, 'utf8'));
if (!Array.isArray(r3)) fail('canonical R3 register is not a JSON array');
const sourceById = new Map();
for (const item of r3) {
  const id = item.requirement_id;
  if (!id || sourceById.has(id)) fail('missing or duplicate R3 ID: ' + (id ?? '(blank)'));
  sourceById.set(id, item);
}

const garText = fs.readFileSync(garPath, 'utf8');
const garIds = [...garText.matchAll(/^\|\s*(GAR-[A-Z]+-\d{3})\s*\|/gm)].map(match => match[1]);
const garSet = new Set(garIds);
if (garSet.size !== garIds.length) fail('legacy GAR matrix contains duplicate IDs');

const tsv = gunzipSync(fs.readFileSync(crosswalkPath)).toString('utf8');
const lines = tsv.trimEnd().split(/\r?\n/);
const expectedHeader = ['r3_requirement_id', 'legacy_gar_ids', 'match_classification'];
const header = lines.shift()?.split('\t') ?? [];
if (JSON.stringify(header) !== JSON.stringify(expectedHeader)) fail('crosswalk TSV header mismatch');

const allowedClasses = new Set(['EXACT', 'SPLIT', 'CONSOLIDATED', 'PARTIAL', 'R3_ADDITION']);
const rows = lines.map((line, index) => {
  const cells = line.split('\t');
  if (cells.length !== 3) fail('row ' + (index + 2) + ' does not have three fields');
  return Object.fromEntries(expectedHeader.map((field, i) => [field, cells[i]]));
});
if (rows.length !== 128) fail('expected 128 crosswalk rows; found ' + rows.length);

const mappedById = new Map();
const r3ByGar = new Map([...garSet].map(id => [id, []]));
const classCounts = new Map();
let edges = 0;
for (const row of rows) {
  const id = row.r3_requirement_id;
  const source = sourceById.get(id);
  if (!source) fail('unknown R3 ID: ' + id);
  if (mappedById.has(id)) fail('duplicate R3 row: ' + id);
  mappedById.set(id, row);
  if (source.canonical_implementation_status !== 'not_started') fail(id + ' implementation status changed from not_started');
  if (!allowedClasses.has(row.match_classification)) fail(id + ' has invalid relationship class');
  const aliases = row.legacy_gar_ids ? row.legacy_gar_ids.split(';').filter(Boolean) : [];
  if (new Set(aliases).size !== aliases.length) fail(id + ' repeats a legacy alias');
  for (const alias of aliases) {
    if (!garSet.has(alias)) fail(id + ' links to unknown legacy GAR ID ' + alias);
    r3ByGar.get(alias).push(id);
    edges += 1;
  }
  if (row.match_classification === 'R3_ADDITION' && aliases.length !== 0) fail(id + ' R3_ADDITION has legacy aliases');
  if (row.match_classification !== 'R3_ADDITION' && aliases.length === 0) fail(id + ' lacks a legacy alias without R3_ADDITION');
  classCounts.set(row.match_classification, (classCounts.get(row.match_classification) ?? 0) + 1);
}

if (mappedById.size !== sourceById.size) {
  const missing = [...sourceById.keys()].filter(id => !mappedById.has(id));
  fail('missing R3 IDs: ' + missing.join(', '));
}
const orphanGar = [...r3ByGar].filter(([, linked]) => linked.length === 0).map(([id]) => id);
if (orphanGar.length) fail('legacy IDs without R3 mapping: ' + orphanGar.join(', '));

for (const row of rows) {
  const aliases = row.legacy_gar_ids ? row.legacy_gar_ids.split(';').filter(Boolean) : [];
  const inverseCounts = aliases.map(alias => r3ByGar.get(alias).length);
  if (row.match_classification === 'EXACT' && (aliases.length !== 1 || inverseCounts[0] !== 1)) fail(row.r3_requirement_id + ' EXACT is not one-to-one');
  if (row.match_classification === 'SPLIT' && !inverseCounts.some(count => count > 1)) fail(row.r3_requirement_id + ' SPLIT has no multi-R3 legacy source');
  if (row.match_classification === 'CONSOLIDATED' && (aliases.length < 2 || inverseCounts.some(count => count !== 1))) fail(row.r3_requirement_id + ' CONSOLIDATED is not many-to-one');
}

const expectedCounts = { CONSOLIDATED: 18, PARTIAL: 47, R3_ADDITION: 2, SPLIT: 61 };
for (const [name, count] of Object.entries(expectedCounts)) {
  if ((classCounts.get(name) ?? 0) !== count) fail(name + ' count changed; expected ' + count);
}
if ((classCounts.get('EXACT') ?? 0) !== 0) fail('EXACT relationships require a new evidence review');

console.log('PASS WP-01 R3↔GAR crosswalk integrity');
console.log('R3 records: ' + mappedById.size + '; legacy GAR records: ' + garSet.size + '; semantic links: ' + edges);
console.log('Classes: ' + Object.entries(expectedCounts).map(([key, value]) => key + '=' + value).join(', ') + ', EXACT=0');
console.log('All legacy GAR IDs retained; all R3 IDs preserved; no canonical implementation status changed.');