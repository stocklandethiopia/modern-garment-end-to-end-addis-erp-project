import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const pkg = JSON.parse(fs.readFileSync(path.join(root, 'package.json'), 'utf8'));
const files = [];
function walk(dir) {
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (['.git', 'node_modules', 'data', 'coverage'].includes(entry.name)) continue;
    const absolute = path.join(dir, entry.name);
    if (entry.isDirectory()) walk(absolute);
    else if (entry.name !== 'BUILD_MANIFEST.json') {
      const bytes = fs.readFileSync(absolute);
      files.push({
        path: path.relative(root, absolute).split(path.sep).join('/'),
        size_bytes: bytes.length,
        sha256: crypto.createHash('sha256').update(bytes).digest('hex')
      });
    }
  }
}
walk(root);
files.sort((a, b) => a.path.localeCompare(b.path));
const manifest = {
  application: 'Stockland Garment ERP',
  version: pkg.version,
  build_date: new Date().toISOString().slice(0, 10),
  runtime_requirement: pkg.engines.node,
  runtime_verified: `Node.js v${process.versions.node}`,
  schema_version: 2,
  application_tables: 45,
  package_type: 'full-stack local source package',
  verification_commands: ['npm run check', 'npm test', 'npm run release:gate', 'npm run verify:manifest'],
  manifest_algorithm: 'SHA-256',
  manifest_scope_note: 'Lists every maintained source, documentation, template and workflow file except BUILD_MANIFEST.json itself. Environment-specific database files, installed dependencies, coverage output and generated delivery archives are managed separately.',
  file_count: files.length,
  files
};
fs.writeFileSync(path.join(root, 'BUILD_MANIFEST.json'), `${JSON.stringify(manifest, null, 2)}\n`);
console.log(`Built SHA-256 manifest for ${files.length} files (version ${pkg.version}).`);
