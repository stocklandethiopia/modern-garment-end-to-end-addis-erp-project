import readline from 'node:readline/promises';
import { stdin as input, stdout as output } from 'node:process';
import { openDatabase, seedLocations } from '../src/db.mjs';
import { hashPassword } from '../src/security.mjs';

async function askHiddenPassword(prompt) {
  if (!input.isTTY || typeof input.setRawMode !== 'function') {
    const rl = readline.createInterface({ input, output });
    try { return await rl.question(prompt); } finally { rl.close(); }
  }
  output.write(prompt);
  input.setRawMode(true);
  input.resume();
  return await new Promise((resolve, reject) => {
    let value = '';
    const cleanup = () => {
      input.off('data', onData);
      input.setRawMode(false);
      output.write('\n');
    };
    const onData = (buffer) => {
      for (const ch of buffer.toString('utf8')) {
        if (ch === '\u0003') { cleanup(); reject(new Error('Bootstrap cancelled.')); return; }
        if (ch === '\r' || ch === '\n') { cleanup(); resolve(value); return; }
        if (ch === '\u007f' || ch === '\b') { value = value.slice(0, -1); continue; }
        if (ch >= ' ' && ch !== '\u007f') value += ch;
      }
    };
    input.on('data', onData);
  });
}

const rl=readline.createInterface({input,output});
const db=openDatabase();
try {
  const count=Number(db.prepare('SELECT COUNT(*) AS n FROM users').get().n);
  if(count>0){console.error('Bootstrap stopped: a user account already exists.');process.exitCode=1;}
  else {
    const fullName=(await rl.question('Owner full name: ')).trim();
    const email=(await rl.question('Owner email: ')).trim().toLowerCase();
    rl.close();
    const password=await askHiddenPassword('Owner password (12+ characters): ');
    if(!fullName||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) throw new Error('A valid full name and email are required.');
    const passwordHash=await hashPassword(password);
    seedLocations(db);
    const central=db.prepare('SELECT id FROM locations WHERE code=?').get('CENTRAL_WAREHOUSE');
    db.prepare('INSERT INTO users(email,full_name,password_hash,role,location_id) VALUES(?,?,?,?,?)').run(email,fullName,passwordHash,'owner',central.id);
    console.log('Owner account created and five Stockland locations initialized. Start the server with npm start.');
  }
} catch(error){ console.error(error.message); process.exitCode=1; }
finally { rl.close(); db.close(); }
