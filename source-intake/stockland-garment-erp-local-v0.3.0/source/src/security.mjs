import crypto from 'node:crypto';

const scryptAsync = (password, salt, keylen = 64) => new Promise((resolve, reject) => {
  crypto.scrypt(password, salt, keylen, { N: 1 << 15, r: 8, p: 1, maxmem: 64 * 1024 * 1024 }, (error, key) => error ? reject(error) : resolve(key));
});

export async function hashPassword(password) {
  if (typeof password !== 'string' || password.length < 12 || password.length > 128) {
    throw new Error('Password must be 12–128 characters.');
  }
  const salt = crypto.randomBytes(16);
  const key = await scryptAsync(password, salt);
  return `scrypt$32768$${salt.toString('hex')}$${key.toString('hex')}`;
}

export async function verifyPassword(password, encoded) {
  if (typeof password !== 'string' || typeof encoded !== 'string') return false;
  const [scheme, cost, saltHex, keyHex] = encoded.split('$');
  if (scheme !== 'scrypt' || cost !== '32768' || !saltHex || !keyHex) return false;
  try {
    const expected = Buffer.from(keyHex, 'hex');
    const actual = await scryptAsync(password, Buffer.from(saltHex, 'hex'), expected.length);
    return expected.length === actual.length && crypto.timingSafeEqual(expected, actual);
  } catch { return false; }
}

export function randomToken(bytes = 32) { return crypto.randomBytes(bytes).toString('base64url'); }
export function sha256(value) { return crypto.createHash('sha256').update(value).digest('hex'); }
export function newId() { return crypto.randomUUID(); }

export function parseCookies(header = '') {
  const out = {};
  for (const item of header.split(';')) {
    const i = item.indexOf('=');
    if (i < 0) continue;
    const name = item.slice(0, i).trim();
    const value = item.slice(i + 1).trim();
    try { out[name] = decodeURIComponent(value); } catch {}
  }
  return out;
}

export function cookieHeader(name, value, { maxAge = 43200, secure = false, httpOnly = true } = {}) {
  return `${name}=${encodeURIComponent(value)}; Path=/; SameSite=Strict; Max-Age=${maxAge}${httpOnly ? '; HttpOnly' : ''}${secure ? '; Secure' : ''}`;
}

export function clearCookieHeader(name, secure = false) {
  return `${name}=; Path=/; SameSite=Strict; Max-Age=0; HttpOnly${secure ? '; Secure' : ''}`;
}
