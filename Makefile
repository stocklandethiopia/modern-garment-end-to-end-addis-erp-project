.PHONY: infra-up infra-down api-install api-key api-migrate api-test api-lint api-serve web-install web-test web-build web-serve check-no-loopback-defaults test

infra-up:
	docker compose up -d postgres redis

infra-down:
	docker compose down

api-install:
	cd backend && composer install

api-key:
	cd backend && php artisan key:generate

api-migrate:
	cd backend && DB_CONNECTION=pgsql_migrator MIGRATION_DB_USERNAME=erp_migrator MIGRATION_DB_PASSWORD=local-migrator-password php artisan migrate --force

api-test:
	cd backend && php artisan test

api-lint:
	cd backend && php artisan pint --test

api-serve:
	cd backend && php artisan serve --port=8000

web-install:
	cd frontend && npm install

web-test:
	cd frontend && npm test -- --watch=false

web-build:
	cd frontend && npm run build

web-serve:
	cd frontend && npm start

check-no-loopback-defaults:
	php scripts/check-no-loopback-defaults.php

test: check-no-loopback-defaults api-lint api-test web-test web-build
