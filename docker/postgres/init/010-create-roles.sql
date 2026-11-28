-- Local/CI-only database roles. Replace credentials through your deployment secret manager in real environments.
CREATE ROLE erp_migrator LOGIN PASSWORD 'local-migrator-password' NOSUPERUSER NOCREATEDB NOCREATEROLE BYPASSRLS;
CREATE ROLE app_runtime LOGIN PASSWORD 'local-runtime-password' NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS;
CREATE ROLE erp_test_admin LOGIN PASSWORD 'local-test-admin-password' NOSUPERUSER NOCREATEDB NOCREATEROLE BYPASSRLS;

GRANT CONNECT ON DATABASE garment_erp TO erp_migrator, app_runtime, erp_test_admin;
GRANT USAGE, CREATE ON SCHEMA public TO erp_migrator;
GRANT USAGE ON SCHEMA public TO app_runtime, erp_test_admin;

-- CI/local test fixtures only. This login can bypass RLS but is never used by the API.
ALTER DEFAULT PRIVILEGES FOR ROLE erp_migrator IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO erp_test_admin;
ALTER DEFAULT PRIVILEGES FOR ROLE erp_migrator IN SCHEMA public
    GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO erp_test_admin;

