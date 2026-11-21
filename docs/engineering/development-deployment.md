# Development, Containers and Deployment Standard

## 1. One source of truth

The GitHub repository is the canonical source. Codespaces and local developer environments clone this repository; they are not separate competing sources. Do not create another workspace or copy an untracked ZIP as the product baseline.

Every change is made on a named branch, reviewed, tested and merged through the protected default branch. Release artifacts are built from a tagged commit and must identify the source SHA.

## 2. Developer environment

The intended developer environment provides:

- Node.js 26.13.1 LTS and npm for Angular CLI.
- PHP 8.5, Composer and Laravel 13 tooling.
- Python 3.15 and a locked virtual environment for specialist services.
- Docker Engine and Docker Compose v2.
- PostgreSQL 18 and Redis 8.2 services.
- Git, GitHub CLI where authorized, and the repository's documented quality commands.

A future .devcontainer/devcontainer.json must reproduce this environment. It must be tested in a clean Codespace before being described as ready. Never install global tools or change the user's Codespace without an explicit, reviewable setup.

## 3. Local development lifecycle

The application will expose consistent commands through documented scripts/Make targets. Expected workflow:

1. Clone the repository and inspect the documented prerequisites.
2. Copy example environment files; populate local-only secrets.
3. Start dependencies with Docker Compose.
4. Install frontend and backend dependencies from committed lockfiles.
5. Run database migrations and non-production seed data.
6. Start the API, frontend and specialist workers.
7. Run the complete required quality suite before committing.

Do not use production credentials or production customer data in a developer environment.

## 4. Container requirements

- Multi-stage Dockerfiles: build dependencies in builder stages and copy only runtime artifacts to the final image.
- Use small, maintained base images and pin versions/digests for release builds.
- Run as a non-root user; use read-only filesystems where practical.
- Add health checks, graceful shutdown and resource limits.
- Do not bake secrets into image layers or build arguments.
- Use .dockerignore; do not copy .git, local caches, .env, test secrets or customer uploads into images.
- Separate web/API, queue worker, scheduler and Python specialist worker processes.
- Use named persistent volumes only for stateful local dependencies; production state belongs in managed/persistent services with backup.
- Keep development Compose configuration distinct from production deployment overlays.

## 5. CI pipeline

Every pull request should run:

1. Repository structure and formatting checks.
2. Angular lint, type/template checks, unit tests and production build.
3. Laravel Pint, PHPStan or Larastan, PHPUnit/Pest and application tests.
4. Python Ruff, type checks and pytest for specialist services.
5. Database migration checks and integration tests.
6. Tenant isolation, authorization and critical workflow tests.
7. Secret scanning, dependency scanning, SAST and container image scanning.
8. Docker image build and software bill of materials generation.

The pipeline must fail closed for required checks. A skipped test is not a pass.

## 6. Release and deployment

- Build once and promote the same immutable image/artifact through environments.
- Deploy to staging first; run migration checks, smoke tests, queue checks and critical business workflow tests.
- Production deployment requires an approved release, backup/restore readiness, migration plan, rollback plan and health verification.
- Database migrations must be backward-compatible with the rolling deployment strategy. Use expand/migrate/contract for risky schema changes.
- Run migrations once as a controlled deployment job, not independently in every web container.
- Never run destructive seeders or reset commands against production.
- Keep application, database, queue, object storage and infrastructure secrets in an approved secret manager.
- Monitor errors, latency, queue lag, database health, failed jobs, tenant-specific failures and audit events.
- Define recovery objectives and regularly test restoration.

## 7. Hosting topology

Start with containerized application services and managed PostgreSQL/Redis/object storage where available. Use a managed container platform or a well-operated VM/container host for the first production deployment. Adopt Kubernetes only when availability, scaling, isolation or operations requirements justify its complexity.

Docker Compose is the local development and integration environment; it is not by itself a high-availability production architecture.

## 8. Release evidence

Every release record must include:

- Git tag and full source commit SHA.
- Build workflow run and artifact/image digests.
- Dependency/SBOM and security scan results.
- Test report and known exceptions.
- Database migration version and backup evidence.
- Deployment environment, timestamp, operator/approval and smoke-test result.
- Rollback/recovery instructions.

A repository commit is not a production deployment. A successful container build is not proof of a successful business workflow.
