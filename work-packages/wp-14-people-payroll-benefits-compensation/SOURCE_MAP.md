# WP-14 — people, payroll, benefits and compensation source map

**Disposition:** current canonical delivery contains a payroll review foundation and HR/people specifications; the complete workforce lifecycle and legally approved Ethiopia payroll runtime are not yet in delivery.

## Canonical source paths
- Baseline migration: `backend/database/migrations/2026_11_26_000020_create_finance_and_payroll_review_foundation.php`.
- Specs: `docs/hr/workforce-payroll-pension-benefits-compensation.md`, `docs/hr/payroll-and-variable-compensation.md`, `docs/localization/ethiopia.md`, `docs/localization/ethiopia-legal-regulatory-register.md`.
- Shared domain dependencies: `backend/app/Services/Planning/IndustrialEngineeringService.php`, `backend/app/Services/Finance/FinanceControlService.php`, `backend/app/Services/Authorization/DomainCapabilityAuthorizer.php`.

## Collected/candidate source
- Local v0.3: `src/modules/enterprise.mjs`, `src/migrations/002_enterprise.sql`, `docs/modules/HR_PAYROLL.md`, `templates/imports/employee-template.csv` — basic employee/contract/attendance/payroll only.
- Candidate PR #20 branch `work/wp14-ethiopia-payroll-localization-2026-11-27`: `backend/app/Services/Payroll/EthiopiaPayrollCalculator.php`, `backend/config/payroll.php`, candidate calculation tests/fixtures and migrations numbered 000033/000034/000036. Candidate only; re-sequence before integration.
- Candidate plan and organogram documents are on `work/wp03-wp14-organogram-payroll-plan-2026-11-27`.

## Remaining build / verification
Implement person/employment/contracts/assignments, HR position/org connection, recruitment/onboarding, skills/training, time/attendance, shift/leave/overtime, benefits/pension, grievance/due process, exit/final pay, secured evidence and audit. Add distinct payee classification for employee, contractor, creator, reseller, subcontractor and supplier; effective-dated Ethiopian rules, independent source hashes, payroll-to-GL and remittance reconciliation. Do not activate candidate tax/pension parameters until qualified legal/payroll review and exact rule-vector approval.


## Candidate verification checkpoint — 28 November 2026
PR #20 head `4b89e9f7cbbc939d3080b70e3fc48b247e72abb5` remains stale/diverged against current delivery (69 ahead / 63 behind; merge base `fc01eced62b97c4ec27f0075264fc8ba0fb11851`). Its associated ERP CI #46024115391 and WP verification #46024115411 concluded FAILURE, but job details returned no step summaries (`steps: null`); the available evidence does not establish that PHP, PostgreSQL, application or source tests actually ran. Rebase and verify on a fresh exact head before assigning a code-test disposition. Candidate migration identities 000033, 000034 and 000036 collide with other work. Both employee-master release and statutory activation remain false; no legal or professional approval is implied. See CAP-006/007/024/030 in the execution ledger.
