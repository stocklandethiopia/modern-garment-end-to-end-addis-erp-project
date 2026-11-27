import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const manifestPath = path.join(root, 'BUILD_MANIFEST.json');
const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const pkg = JSON.parse(fs.readFileSync(path.join(root, 'package.json'), 'utf8'));
if (manifest.version !== pkg.version) throw new Error(`Manifest version ${manifest.version} does not match package version ${pkg.version}.`);

const actual = [];
function walk(dir) {
  for (const item of fs.readdirSync(dir, { withFileTypes: true })) {
    if (['.git', 'node_modules', 'data'].includes(item.name)) continue;
    const full = path.join(dir, item.name);
    if (item.isDirectory()) walk(full);
    else if (item.name !== 'BUILD_MANIFEST.json') actual.push(path.relative(root, full).split(path.sep).join('/'));
  }
}
walk(root);
const expected = (manifest.files || []).map(f => f.path).sort();
actual.sort();
if (JSON.stringify(expected) !== JSON.stringify(actual)) {
  const missing = expected.filter(x => !actual.includes(x));
  const unlisted = actual.filter(x => !expected.includes(x));
  throw new Error(`Manifest file set mismatch. Missing: ${missing.join(', ') || 'none'}. Unlisted: ${unlisted.join(', ') || 'none'}.`);
}
for (const entry of manifest.files) {
  const full = path.join(root, entry.path);
  const bytes = fs.readFileSync(full);
  const digest = crypto.createHash('sha256').update(bytes).digest('hex');
  if (bytes.length !== entry.size_bytes) throw new Error(`Size mismatch: ${entry.path}`);
  if (digest !== entry.sha256) throw new Error(`SHA-256 mismatch: ${entry.path}`);
}
console.log(`PASS build manifest: ${manifest.files.length} files, version ${manifest.version}, all sizes and SHA-256 hashes match`);
