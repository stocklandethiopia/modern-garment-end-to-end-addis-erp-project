# Operations runbook

**Document ID:** STK-OPS-001  
**Application version:** 0.3.0

## Prerequisites

- Node.js 22.5.0 or newer.
- Terminal access to the project directory.
- A current browser.
- A protected, writable directory for the SQLite database.

## First-time setup

```bash
npm run bootstrap
```

Enter Owner name, email and password. Use a unique password of at least 12 characters. The command initializes the canonical five Stockland locations and creates the initial Owner.

## Start the application

```bash
npm start
```

Default address: `http://127.0.0.1:3000`. Change `HOST` and `PORT` through process environment variables when required. `DATABASE_PATH` chooses the persistent database location.

## User administration

Owner/Admin creates user accounts and assigns each operational role to its location. Branch Manager and Cashier accounts are assigned to a branch. Warehouse Manager accounts are assigned to a location. Accountant and Auditor roles use company-level finance/report privileges.

## Daily operating sequence

1. Confirm the branch location and sign-in role.
2. Review product master and stock balances.
3. Receive stock or transfer stock to the required location.
4. Process retail or wholesale transactions.
5. Stage and reconcile POS imports; the assigned Branch Manager performs final release.
6. Review stock, sales and financial reports.
7. Record attendance and payroll during the relevant period.
8. Submit and close the branch daily cash reconciliation.

## Database backup

For a consistent SQLite backup, stop the application first or use SQLite's online backup capability. Copy the database and keep a dated checksum. If using file copies while the database is online, account for WAL and shared-memory files; do not copy only the `.sqlite` main file while uncheckpointed WAL transactions are active.

Example stopped-process copy:

```bash
mkdir -p backups
cp data/stockland.sqlite "backups/stockland-$(date +%Y%m%d-%H%M%S).sqlite"
sha256sum backups/*.sqlite
```

## Restore

1. Stop the application.
2. Preserve the current database as a separate recovery copy.
3. Copy the selected backup into the configured `DATABASE_PATH`.
4. Start the application and check `/api/health`.
5. Log in and confirm locations, user access, recent stock balances and reports.
6. Record the restore timestamp and backup checksum in the operations log.

## Maintenance and support

- Run `npm run check`, `npm test`, `npm run release:gate` and `npm run verify:manifest` after source changes.
- Keep application files and the database in separate protected locations.
- Rotate `APP_SECRET` under a planned session renewal procedure.
- Keep the Node.js runtime in the supported major/minor range.
- Review audit history after key approvals, imports, adjustments and finance postings.
