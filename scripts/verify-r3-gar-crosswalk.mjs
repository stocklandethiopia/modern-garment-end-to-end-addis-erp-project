#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

function parseCsv(source) {
  const rows = [];
  let row = [];
  let field = '';
  let quoted = false;
  const input = source.replace(/^\uFEFF/, '').replace(/\r\n/g, '\n');
  for (let index = 0; index < input.length; index += 1) {
    const char = input[index];
    if (quoted) {
      if (char === '"' && input[index + 1] === '"') { field += '"'; index += 1; }
      else if (char === '"') quoted = false;
      else field += char;
    } else if (char === '"' && field.length === 0) quoted = true;
    else if (char === ',') { row.push(field); field = ''; }
    else if (char === '\n') { row.push(field); if (row.some((cell) => cell.length > 0)) rows.push(row); row = []; field = ''; }
    else field += char;
  }
  if (quoted) throw new Error('CSV contains an unterminated quoted field.');
  if (field.length > 0 || row.length > 0) { row.push(field); if (row.some((cell) => cell.length > 0)) rows.push(row); }
  if (rows.length < 2) throw new Error('CSV must contain a header and at least one data row.');
  const [header, ...records] = rows;
  if (new Set(header).size !== header.length) throw new Error('CSV header contains duplicate column names.');
  return records.map((cells, rowIndex) => {
    if (cells.length !== header.length) throw new Error(`CSV row ${rowIndex + 2} has ${cells.length} fields; expected ${header.length}.`);
    return Object.fromEntries(header.map((key, columnIndex) => [key, cells[columnIndex]]));
  });
}

const splitIds = (value) => value ? value.split(';').filter(Boolean) : [];
function assert(condition, message) { if (!condition) throw new Error(message); }

export function verifyCrosswalk(r3Csv, garCsv) {
  const r3 = parseCsv(r3Csv);
  const gar = parseCsv(garCsv);
  const expectedPoIds = new Set(['DWR-007', 'DWR-008', 'DWR-009', 'DWR-010', 'DWR-011', 'DWR-012']);
  const expectedAdditions = new Set(['PLT-002', 'DWR-006', ...expectedPoIds]);
  const expectedDisposition = new Set(['PARTIAL_SPLIT', 'PARTIAL_RELATION', 'PARTIAL_STANDARD', 'PRESERVED_STANDARD']);
  assert(r3.length === 128, `Expected 128 R3 records; found ${r3.length}.`);
  assert(gar.length === 120, `Expected 120 GAR records; found ${gar.length}.`);
  const r3Ids = r3.map((item) => item.r3_id);
  const garIds = gar.map((item) => item.gar_id);
  assert(new Set(r3Ids).size === 128, 'R3 requirement IDs are not unique.');
  assert(new Set(garIds).size === 120, 'GAR requirement IDs are not unique.');
  const r3ById = new Map(r3.map((item) => [item.r3_id, item]));
  const garIdSet = new Set(garIds);
  const poRecords = r3.filter((item) => item.provenance === 'PRODUCT_OWNER_SUPPLIED');
  const baselineRecords = r3.filter((item) => item.provenance === 'R3_BASELINE');
  const actualPoIds = new Set(poRecords.map((item) => item.r3_id));
  assert(baselineRecords.length === 122, `Expected 122 baseline R3 records; found ${baselineRecords.length}.`);
  assert(poRecords.length === 6, `Expected 6 Product Owner-supplied records; found ${poRecords.length}.`);
  assert([...expectedPoIds].every((id) => actualPoIds.has(id)), 'Product Owner-supplied IDs must be DWR-007 through DWR-012.');
  assert([...actualPoIds].every((id) => expectedPoIds.has(id)), 'Unexpected Product Owner-supplied requirement ID found.');
  const additions = r3.filter((item) => item.coverage_classification === 'R3_ADDITION');
  const actualAdditionIds = new Set(additions.map((item) => item.r3_id));
  assert(additions.length === 8, `Expected 8 R3 additions; found ${additions.length}.`);
  assert([...expectedAdditions].every((id) => actualAdditionIds.has(id)), 'R3 addition set must be PLT-002 and DWR-006 through DWR-012.');
  assert([...actualAdditionIds].every((id) => expectedAdditions.has(id)), 'Unexpected R3 addition classification found.');
  for (const item of r3) {
    assert(item.canonical_status === 'not_started', `${item.r3_id} implementation status must remain not_started.`);
    const direct = splitIds(item.legacy_partial_match_ids);
    const context = splitIds(item.context_only_gar_ids);
    assert(item.coverage_classification === 'PARTIAL' || item.coverage_classification === 'R3_ADDITION', `${item.r3_id} has an invalid coverage classification.`);
    for (const id of [...direct, ...context]) assert(garIdSet.has(id), `${item.r3_id} references unknown GAR ID ${id}.`);
    if (expectedAdditions.has(item.r3_id)) assert(direct.length === 0, `${item.r3_id} is an addition and must not claim a direct/partial GAR mapping.`);
    else assert(item.coverage_classification === 'PARTIAL', `${item.r3_id} baseline item must be classified PARTIAL.`);
  }
  const relatedGar = new Set();
  let standardsOnly = 0;
  const garById = new Map(gar.map((item) => [item.gar_id, item]));
  for (const item of gar) {
    assert(expectedDisposition.has(item.disposition), `${item.gar_id} has no valid disposition.`);
    assert(item.retired === 'NO', `${item.gar_id} must not be retired.`);
    const related = splitIds(item.related_r3_ids);
    for (const id of related) { assert(r3ById.has(id), `${item.gar_id} references unknown R3 ID ${id}.`); relatedGar.add(item.gar_id); }
    if (item.disposition === 'PRESERVED_STANDARD') {
      standardsOnly += 1;
      assert(related.length === 0, `${item.gar_id} preserved by standard must not claim an R3 row.`);
      assert(item.governing_standard.length > 0, `${item.gar_id} is missing its governing standard path.`);
    } else assert(related.length > 0, `${item.gar_id} requires at least one related R3 ID.`);
    if (item.disposition === 'PARTIAL_STANDARD') assert(item.governing_standard.length > 0, `${item.gar_id} requires its governing standard path.`);
  }
  assert(relatedGar.size === 117, `Expected 117 GAR IDs with related R3 rows; found ${relatedGar.size}.`);
  assert(standardsOnly === 3, `Expected 3 preserved-standard GAR IDs; found ${standardsOnly}.`);
  assert(garById.get('GAR-IE-006')?.gap_code === 'PREPRODUCTION_READINESS', 'GAR-IE-006 pre-production readiness gap is missing.');
  assert(garById.get('GAR-FIN-010')?.gap_code === 'PERIOD_CLOSE', 'GAR-FIN-010 period-close gap is missing.');
  assert(garById.get('GAR-PLT-005')?.gap_code === 'API_CONTRACT_GAPS', 'GAR-PLT-005 API contract gap is missing.');
  return {
    r3Records: r3.length, r3Baseline: baselineRecords.length, productOwnerSupplied: poRecords.length,
    r3Additions: additions.length, garRecords: gar.length, garWithPartialR3Relationships: relatedGar.size,
    garPreservedByStandards: standardsOnly, retiredGarRecords: gar.filter((item) => item.retired !== 'NO').length,
    implementationStatuses: [...new Set(r3.map((item) => item.canonical_status))],
  };
}

const currentFile = fileURLToPath(import.meta.url);
if (process.argv[1] && path.resolve(process.argv[1]) === currentFile) {
  const root = process.cwd();
  const r3Path = path.join(root, 'docs/requirements/r3-to-gar-crosswalk-2026-11-28.csv');
  const garPath = path.join(root, 'docs/requirements/gar-legacy-disposition-2026-11-28.csv');
  const result = verifyCrosswalk(fs.readFileSync(r3Path, 'utf8'), fs.readFileSync(garPath, 'utf8'));
  process.stdout.write(`${JSON.stringify(result, null, 2)}\n`);
}
