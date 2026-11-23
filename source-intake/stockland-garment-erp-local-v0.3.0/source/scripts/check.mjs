import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const files=[];
function walk(dir){for(const ent of fs.readdirSync(dir,{withFileTypes:true})){const p=path.join(dir,ent.name);if(ent.isDirectory()){if(!['data','node_modules'].includes(ent.name))walk(p);}else if(/\.(mjs|js)$/.test(ent.name))files.push(p);}}
walk(root);
for(const file of files){execFileSync(process.execPath,['--check',file],{stdio:'pipe'});console.log(`PASS syntax ${path.relative(root,file)}`);}
console.log(`PASS syntax checks: ${files.length} files`);
