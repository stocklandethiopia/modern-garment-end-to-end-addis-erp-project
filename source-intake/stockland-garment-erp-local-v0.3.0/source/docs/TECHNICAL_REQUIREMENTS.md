# Technical requirements

**Document ID:** STK-TECH-001  
**Application version:** 0.3.0

## Runtime requirements

| Component | Requirement | Verified environment |
|---|---|---|
| Node.js | 22.5.0 or newer | v22.16.0 |
| npm | 10 or newer | 10.9.2 |
| Database | Node built-in `node:sqlite` / SQLite | Schema v2; 45 tables |
| Browser | Current Chromium, Firefox or Safari | Local HTTP application and static asset smoke tests |
| OS | Linux, Windows or macOS | Linux local runtime |
| Frontend | Native HTML, CSS and browser JavaScript | No frontend build step |
| Runtime packages | Node built-in modules | No third-party npm runtime dependencies |

## Environment variables

| Variable | Default | Purpose |
|---|---|---|
| `NODE_ENV` | `development` | Runtime mode and secure-cookie behavior |
| `HOST` | `127.0.0.1` | HTTP listener address |
| `PORT` | `3000` | HTTP listener port |
| `DATABASE_PATH` | `./data/stockland.sqlite` | SQLite database file |
| `SESSION_TTL_HOURS` | `12` | Session expiration duration |
| `APP_SECRET` | Local development value | Session-token digest pepper; production requires an explicit secret of 32+ characters |

`.env.example` documents the supported configuration. The application reads environment variables from the process environment; the operator's service manager or shell provides environment loading.

## Data standards

- Monetary amounts use integer minor units in storage and decimal ETB major units in the API.
- Quantities allow up to three decimal places.
- Dates use ISO calendar dates; timestamps use ISO 8601.
- Product identifiers use unique SKU values; optional barcode values are unique.
- Location identifiers are the five canonical Stockland codes.
- Database foreign keys are enabled; file-backed mode uses WAL and a 5-second busy timeout.
- Schema version is reported by `PRAGMA user_version` and is currently `2`.

## Resource and input limits

- General JSON body limit: 3 MiB.
- POS CSV text limit: 2 MiB.
- Session TTL is configurable.
- Report lists are capped at bounded record counts in route handlers.
- Monetary values and quantities are range-checked at API boundaries.

## Installation and validation

```bash
node --version
npm run bootstrap
npm start
```

Open `http://127.0.0.1:3000`. The quality and release commands are:

```bash
npm run check
npm test
npm run release:gate
npm run verify:manifest
```

## Runtime architecture

The Node HTTP server serves the browser interface and same-origin REST API. SQLite stores master data, document workflows, stock movements, double-entry journals and audit history. PWA shell assets are under `public/`; API calls remain network requests. No database seed product data is inserted: Stockland product records are created from the Product Master workflow.
