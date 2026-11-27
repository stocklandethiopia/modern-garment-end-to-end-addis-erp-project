# CI Runner Continuity and Exact-Head Verification

## 1. Purpose

Keep ERP engineering verification available without making the product's test standard dependent on one runner provider. This operating note covers runner selection, hosted-compute usage, a controlled self-hosted route, efficient workflow routing and exact-source evidence.

The quality gates remain unchanged. Backend/PostgreSQL, tenant RLS, Angular tests/build, dependency audits, WP-01 source integrity, combined packaging and provenance must still execute and pass for the source being verified. No skipped job, earlier green run or local-only result is substituted for exact-head evidence.

## 2. Evidence from 27–28 November 2026

The following runs recorded backend and frontend jobs with `runner_id: 0`, blank runner names and no executed steps:

- ERP CI #45915273928 on delivery source `fc01eced62b97c4ec27f0075264fc8ba0fb11851`.
- ERP CI #45915554309 on WP-03 candidate `8ae8536bccfd3924017b12905ea57fce2641fb46`.
- Combined verification #45915554192 on the WP-03 candidate.

The combined packaging job was skipped because its required verification jobs did not execute. These records describe runner allocation, not a Laravel, PostgreSQL, Angular or source-package test result.

Earlier runs #45900565705 and #45900565706 did execute successfully: the ERP CI backend and frontend steps completed, and the combined verification completed backend, frontend, WP-01 integrity and candidate packaging. This confirms that the source and workflow have previously executed on hosted runners; it does not prove the later source SHAs.

The connector does not expose the repository owner's billing usage or the precise Actions policy decision for the later runs. Treat included-compute exhaustion, spending budgets/payment configuration, Actions policy and a temporary GitHub service-capacity condition as investigation paths, not as a confirmed root cause.

### 2.1 Cross-PR retry sweep — 28 November 2026

A repository-wide retry and job-record inspection was completed for the current open PR heads. The following required runs again ended before any job step executed: each backend, Angular and WP-01 integrity job reported `runner_id: 0`, blank runner name and zero steps; dependent artifact/combined-packaging jobs were skipped.

| PR | Candidate area | Retried workflow run IDs | Execution result |
|---:|---|---|---|
| 13 | WP-03 platform/scoped-access | #46077281009 | Backend/Angular runner allocation failed; WP-01/package skipped |
| 18 | WP-00–WP-16 governance/audit | #46077078173, #46077077971 | Backend/Angular/R3 allocation failed; package skipped |
| 27 | WP-15/WP-16 document storage/RLS | #46024604836, #46024604984 | Backend/Angular allocation failed; dependent package skipped |
| 19 | WP-03/WP-14 plan and payroll gate docs | #46076772106, #46076772079 | Backend/Angular/R3 allocation failed; package skipped |
| 20 | WP-14 payroll localization | #46024115391, #46024115411 | Backend/Angular/R3 allocation failed; package skipped |
| 17 | WP-01 R3 reconciliation | #46022938308, #46022938299 | Backend/Angular/R3 allocation failed; package skipped |
| 16 | WP-03 organogram/DoA design | #46022932005, #46022932016 | Backend/Angular/R3 allocation failed; package skipped |
| 21 | WP-03 organization runtime | #46018149774, #46018149715 | Backend/Angular/R3 allocation failed; package skipped |
| 25 | WP-03 engineering LLD | #46017647171, #46017647142 | Backend/Angular/R3 allocation failed; package skipped |
| 24 | WP-03 engineering design | #46017389962, #46017390073 | Backend/Angular/R3 allocation failed; package skipped |
| 23 | WP-15 agent/device least privilege | #46016837226, #46016837218 | Backend/Angular allocation failed; package skipped |
| 22 | CI runner continuity | #46014760259 | Backend/Angular/R3 allocation failed; package skipped |
| 14 | Reference-material reconciliation | #45919106528, #45919106597 | Backend/Angular/R3 allocation failed; package skipped |
| 1 | Delivery foundation PR | #45915273928 | Backend/Angular allocation failed; lockfile job skipped |

These failures do not establish that any source/test assertion failed. They establish that the required checks were **NOT RUN**. PR #3 and PR #2 had no workflow runs associated with their current exact heads in the scan; no CI pass is inferred for them.

The latest PR #13 retry of combined WP verification was #46077281009. Its final retry still returned runner ID 0 for both backend and Angular and skipped WP-01 integrity/packaging. Its exact source head therefore remains unverified.

### 2.2 Alternate runner route — operational handoff

The workflows already contain the safe manual `workflow_dispatch` runner selector. The repository-connected GitHub actions available to this project session can inspect runs and rerun failed jobs, but cannot register a new self-hosted runner or initiate a `workflow_dispatch` with its `execution_runner` input. The controlled fallback therefore requires a repository administrator to perform the documented runner registration on a disposable Linux x64 VM, confirm that the runner is Idle with labels `self-hosted, linux, x64, erp-ci`, then manually dispatch both workflows for a trusted frozen branch/SHA with `execution_runner=self-hosted-linux-x64`.

Do not route automatic pull-request events to a self-hosted machine, do not post or store registration tokens in the repository/chat, and do not broaden runner access across projects. After dispatch, record the run IDs, exact head SHA, runner name, executed steps, test counts and artifact checksums. If the runner is not provisioned, preserve the job state as NOT RUN and continue safe source work.

## 3. Hosted-compute and policy checks

For a private repository, GitHub-hosted runner usage draws on the repository owner's included compute allowance. GitHub's current billing documentation lists $12 USD of included monthly compute for GitHub Free; this is a monetary allowance rather than a fixed minutes quota. Standard Linux runner use is currently priced at $0.006 per minute as a reference rate. Eligible cloud sandbox compute also draws from the shared included compute amount. If the included amount is consumed and there is no valid payment method, further usage is blocked; configured budgets can also limit additional spend.

The repository owner should inspect:

1. GitHub account **Settings → Billing & licensing → Usage** and the current billing cycle's Actions/compute usage.
2. **Budgets and alerts**, any Actions spending limit, and whether a valid payment method is configured. Do not add a payment method or increase a budget without the owner's separate financial decision.
3. Repository **Settings → Actions → General** for whether Actions and standard GitHub-hosted runners are allowed, and whether any account or repository policy limits workflows.
4. GitHub Status for a service incident matching the run timestamps.

References: [GitHub Actions billing](https://docs.github.com/en/billing/concepts/product-billing/github-actions), [Actions billing and usage](https://docs.github.com/en/actions/concepts/billing-and-usage).

## 4. Workflow routing and hosted-minute efficiency

- Automatic pull requests targeting `main` use **ERP Continuous Integration**.
- Automatic pull requests targeting `delivery/autonomous-wp-01-16` use the **combined WP-01–WP-16 verification** workflow. That workflow already repeats the backend and frontend checks, verifies WP-01 source integrity and assembles the combined candidate. The separate ERP CI PR trigger is therefore limited to `main` to avoid running the same backend and Angular lanes twice for one delivery PR.
- Direct pushes to `main` or `delivery/autonomous-wp-01-16` continue to run ERP CI. The delivery lockfile job remains attached to the delivery push workflow.
- Both workflows expose a manual `execution_runner` choice. `github-hosted` remains the default. `self-hosted-linux-x64` selects the dedicated runner labels `self-hosted`, `linux`, `x64` and `erp-ci`.
- Pull-request and push events always use GitHub-hosted runners. The self-hosted route is available only through an explicit manual workflow dispatch, so an untrusted PR event is never automatically routed to the repository machine.

## 5. Dedicated Linux runner setup

A repository administrator can add a single-repository runner from:

**Repository → Settings → Actions → Runners → New self-hosted runner → Linux → x64.**

Use the configuration instructions generated by GitHub and add the `erp-ci` label. Registration tokens are short-lived; generate them in GitHub when configuring the machine. Never put a runner registration token, personal access token, private key or environment secret in this repository, an issue, a workflow variable or a chat message.

The machine must have:

- Linux x64 and a supported shell environment.
- Docker Engine available to the runner account, because the backend job uses PostgreSQL and Redis service containers.
- Network access to GitHub Actions and the package registries needed by Composer/npm.
- Sufficient disk and memory for PHP 8.5, Node 26, PostgreSQL 18, Redis 8.2 and the WP-01 source/package checks. Workflow setup actions install the PHP and Node toolchains.

Security controls:

- Restrict the runner to this private repository; do not share it across unrelated projects.
- Prefer a disposable VM or a runner image that is reset between verification sessions. GitHub recommends ephemeral runners for autoscaling; a persistent workstation runner does not provide a guaranteed clean environment.
- Run under a dedicated least-privilege OS account. Do not store production credentials, payroll files, customer data, deployment SSH keys or personal browser sessions on it.
- Keep production secrets out of CI verification. Keep runner network access limited to source/package access and required test services.
- Use the self-hosted choice only for a trusted, owner-selected branch. A single runner can process the combined workflow's jobs sequentially; additional matching runners provide parallel capacity.

References: [Self-hosted runner requirements and routing](https://docs.github.com/en/actions/reference/runners/self-hosted-runners), [Adding a self-hosted runner](https://docs.github.com/en/actions/how-tos/manage-runners/self-hosted-runners/add-runners), [Secure use of GitHub Actions](https://docs.github.com/en/actions/reference/security/secure-use).

## 6. How to run the fallback

After the dedicated runner is online and shows as **Idle**:

1. Open **Actions → ERP Continuous Integration** or **ERP WP-01–WP-16 completion verification**.
2. Choose **Run workflow** and select the trusted branch whose head is the source SHA being verified.
3. Set `execution_runner` to `self-hosted-linux-x64`. Leave it at `github-hosted` for the default hosted route.
4. Record the workflow run URL, event, branch, exact head SHA, runner name, completed job steps, test totals and artifact checksum.
5. Run both ERP CI and combined verification against the same frozen source SHA before integrating it. After integration, run destination-branch verification on the resulting merge SHA.

A workflow dispatch verifies the branch head captured by that dispatch. If a particular historical SHA is required, first point a dedicated verification branch at that SHA and dispatch from that branch; do not assume a moving branch still refers to the earlier commit.

## 7. Local developer verification

The repository's [local development guide](../development/local-development.md) documents the PHP 8.5, Node 26, Docker Compose, PostgreSQL and Redis environment. After preparing that environment, `make test` runs the documented local source, backend and frontend checks.

Local verification is a useful independent engineering route when hosted runners are unavailable. It is recorded as local evidence, not as a GitHub Actions check, not as a signed combined artifact and not as permission to skip the exact-SHA workflow evidence required for an integrated candidate.

## 8. Exact-head evidence and candidate packaging

For each integrated candidate, retain:

- Exact source SHA and branch/ref.
- ERP CI run and combined verification run for that same SHA.
- Backend and frontend job step evidence, test counts and dependency-audit results.
- Database migration and forced-RLS verification results.
- WP-01 source archive checksum and strict audit result.
- Combined candidate artifact, SHA-256 checksum, workflow run and expiry.
- A destination-branch verification run for the merge SHA.

An earlier successful run remains historical evidence for its own SHA only. Runner availability is repaired through account settings or the configured runner; tests, security checks, artifact provenance and product scope are not weakened.
