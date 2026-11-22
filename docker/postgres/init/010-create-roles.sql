-- Local/CI-only database roles. Replace credentials through your deployment secret manager in real environments.
CREATE ROLE erp_migrator LOGIN PASSWORD 'local-migrator-password' NOSUPERUSER NOCREATEDB NOCREATEROLE;
CREATE ROLE app_runtime LOGIN PASSWORD 'local-runtime-password' NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS;

GRANT CONNECT ON DATABASE garment_erp TO erp_migrator, app_runtime;
GRANT USAGE, CREATE ON SCHEMA public TO erp_migrator;
GRANT USAGE ON SCHEMA public TO app_runtime;

ALTER DEFAULT PRIVILEGES FOR ROLE erp_migrator IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE ON TABLES TO app_runtime;
ALTER DEFAULT PRIVILEGES FOR ROLE erp_migrator IN SCHEMA public
    GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO app_runtime;
