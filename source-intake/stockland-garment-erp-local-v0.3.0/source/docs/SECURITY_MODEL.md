# Security model

**Document ID:** STK-SEC-001  
**Application version:** 0.3.0

## Security principals

- Owner: company-wide administration and initial system authority.
- Admin: company-wide operational administration.
- Branch Manager: assigned retail branch workflows, branch POS and final POS-import release.
- Warehouse Manager: assigned warehouse or branch stock workflows.
- Cashier: assigned branch retail operations.
- Accountant: company finance operations and finance reporting.
- Auditor: read-focused reporting and audit review.

## Identity and session controls

- Initial Owner setup is permitted only while the users table is empty.
- Passwords are derived with Node.js `scrypt`, per-password random salt and timing-safe verification.
- Session values are random opaque tokens; only a keyed SHA-256 digest is stored in `sessions`.
- Cookies are HttpOnly and SameSite=Strict; production mode enables Secure cookies.
- Session expiry is configurable using `SESSION_TTL_HOURS`.
- Login attempts are rate-limited by remote address in the running process.
- Deactivated or expired sessions are not accepted.

## Request controls

- Authenticated state-changing requests carry `X-CSRF-Token`.
- JSON content type is enforced for API bodies.
- Request body size is bounded.
- SQL values are bound through prepared statements.
- Role checks occur in the API layer; navigation visibility is not an authorization boundary.
- Location scope is checked by server handlers for assigned roles.
- Error responses avoid returning internal stack traces to the browser.

## Browser response controls

The server sets Content Security Policy, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, Referrer-Policy and a Permissions-Policy. HTML rendering uses the frontend escaping helper for user-controlled text. Application assets are served from the `public/` root only; path traversal is rejected.

## Data integrity controls

- `inventory_movements` is append-only and protected against update/delete by SQLite triggers.
- `audit_log` is append-only and protected against update/delete by triggers.
- Inventory balance is computed from signed movement entries.
- Critical writes are grouped in `BEGIN IMMEDIATE` transactions.
- Transfer documents post two legs together.
- POS sale identity is unique within a location.
- POS import digest is unique; Branch Manager release is idempotent.
- Journal posting validates exact debit/credit equality.

## Secret and deployment configuration

`APP_SECRET` is required in production mode and must contain at least 32 characters. Keep `.env` outside source control. Set `NODE_ENV=production`, configure `APP_SECRET`, use HTTPS termination and protect the SQLite data directory. See [Technical requirements](TECHNICAL_REQUIREMENTS.md) and [Operations runbook](OPERATIONS_RUNBOOK.md).

## Access-control verification

`tests/erp.test.mjs` exercises CSRF rejection, required location assignment, branch scope, Warehouse Manager scope, Owner release denial and Branch Manager-only release. The role matrix is documented alongside workflows and API routes.
