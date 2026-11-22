# Local Development — First Executable Foundation

This guide runs the first Laravel/PostgreSQL tenant foundation and Angular workspace. It is a developer environment only, not a production deployment recipe.

## 1. Requirements

- Git checkout of this repository.
- Docker Engine with Docker Compose v2.
- Node.js 26.13.1 LTS and npm.
- PHP 8.5 with PostgreSQL PDO extension.
- Composer 2.
- Ports 5432, 6379, 8000 and 4200 available.

The repository's pinned versions are the baseline. Do not downgrade to the host machine's older PHP/Node and then call the build verified.

## 2. Start local infrastructure

From repository root:

\`\`\`bash
docker compose up -d postgres redis
docker compose ps
\`\`\`

PostgreSQL initialization creates two non-superuser roles:
- \`erp_migrator\`: owns schema migrations and runs controlled provisioning.
- \`app_runtime\`: used by the Laravel API and has no PostgreSQL RLS bypass.

The local SQL script and Compose file contain development-only passwords. They are public, intentionally disposable local values. Never use them on a network-exposed or production database. Replace role creation with the organization's secret-management process for each real environment.

## 3. Configure Laravel

\`\`\`bash
cp backend/.env.example backend/.env
cd backend
composer install
php artisan key:generate
\`\`\`

The copied \`.env\` is local-only and ignored by Git. For local Compose defaults it uses the local PostgreSQL/Redis host ports and the two limited DB roles.

Run schema migrations using the migration role, not the runtime role:

\`\`\`bash
DB_CONNECTION=pgsql_migrator MIGRATION_DB_USERNAME=erp_migrator MIGRATION_DB_PASSWORD=local-migrator-password php artisan migrate:fresh --force
\`\`\`

The migration creates:
- Tenant and membership identity records.
- Legal entities and site/operational-node distinctions.
- Branch registration evidence and optional inventory locations.
- Append-only audit events.
- PostgreSQL row-level security policies with forced RLS.

## 4. Provision the first owner

This initial bootstrap command is deliberately interactive and succeeds only while the tenant table is empty:

\`\`\`bash
MIGRATION_DB_USERNAME=erp_migrator MIGRATION_DB_PASSWORD=local-migrator-password php artisan platform:provision-tenant-owner stockland-dev "Local Garment ERP Workspace"
\`\`\`

Enter the initial owner email/name and a password of at least 16 characters when prompted. The password is hashed before insert and is never written to command output. Record the generated tenant UUID securely for local API calls.

For a new tenant after initial setup, use the future controlled tenant-administration workflow; do not rerun the one-time bootstrap command.

## 5. Run the backend

\`\`\`bash
php artisan serve --host=0.0.0.0 --port=8000
\`\`\`

Health check:

\`\`\`bash
curl http://127.0.0.1:8000/api/v1/health
\`\`\`

Issue a short-scope personal access token:

\`\`\`bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/tokens \\
  -H "Content-Type: application/json" \\
  -d '{"email":"owner@example.test","password":"YOUR_LOCAL_PASSWORD","device_name":"local-browser"}'
\`\`\`

Use the returned access token and the tenant UUID from provisioning:

\`\`\`bash
curl http://127.0.0.1:8000/api/v1/enterprise/legal-entities \\
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \\
  -H "X-Tenant-Id: YOUR_TENANT_UUID" \\
  -H "Accept: application/json"
\`\`\`

The legal-entity list starts empty. Create a draft entity only with accurate legal data through the authenticated owner/admin API; do not seed fictitious statutory records.

## 6. Run the Angular workspace

In another terminal, from repository root:

\`\`\`bash
cd frontend
npm install
npm start
\`\`\`

Open \`http://localhost:4200\`. The development proxy forwards \`/api\` to Laravel at \`127.0.0.1:8000\`. The overview displays actual API health status and labels domain modules according to their implementation state; it does not fabricate customer operations, production KPIs or inventory.

## 7. Run checks

From repository root:

\`\`\`bash
make api-lint
make api-test
make web-test
make web-build
\`\`\`

Or run the complete current scaffold gate:

\`\`\`bash
make test
\`\`\`

The CI workflow uses PHP 8.5, PostgreSQL 18, Redis 8.2, Node 26.13.1 and installs dependencies. Until the required workflow jobs complete successfully on the candidate commit, these commands/tests must be reported as NOT VERIFIED.

## 8. Tenant security model

The API requires authentication and an explicit \`X-Tenant-Id\` selector. Laravel checks active tenant membership, then sets \`app.current_tenant_id\` as a transaction-local PostgreSQL setting on the same runtime database connection. Tenant-owned table RLS policies default-deny when the setting is absent or mismatched. The runtime DB user is not table owner, superuser or \`BYPASSRLS\`.

Do not:
- Pass tenant IDs from client input without membership validation.
- Use superuser/admin credentials in the running API.
- Run production migrations as the application role.
- disable FORCE ROW LEVEL SECURITY for convenience.
- Add tenant-owned tables without \`tenant_id\`, composite tenant-scoped foreign keys, RLS and cross-tenant tests.
- Put passwords, tokens, bank data or personal data in logs, source or issues.

## 9. Scope and current limitations

This is the initial platform foundation only. It does not yet implement the end-to-end domain workflows specified in WP-04–WP-15. Current API capabilities are health, token issue/revoke, current user/tenant context and draft legal-entity list/create. It has no tenant settings UI, full identity management, branch licensing review UI, approval engine, domain modules, customer connectors or production deployment.

The next vertical slices should build from this tested foundation, delivering real tables, API commands, frontend screens, permissions, events and end-to-end acceptance by bounded context. No statutory tax/payroll rule should become active without the WP-02 evidence and qualified review requirements.
