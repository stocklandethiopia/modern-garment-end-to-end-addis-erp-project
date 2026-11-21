# SaaS Security and Data Protection Standard

## 1. Trust boundaries

Treat browser input, uploaded files, webhooks, integrations, queue messages and AI outputs as untrusted. The server authenticates the caller, resolves the tenant context, authorizes the action and validates the business transition before data access or mutation.

## 2. Identity and access

- Support secure password hashing, MFA and enterprise SSO integration.
- Use short-lived access credentials and secure session refresh/revocation.
- Enforce tenant, legal entity, site, department, warehouse and record-level scopes.
- Use RBAC for role assignment and contextual policy checks (ABAC/ReBAC where needed).
- Apply least privilege to users, service accounts, integrations and background workers.
- Separate platform support administration from tenant administration.
- Record privileged access, role changes, failed authorization and sensitive exports.

## 3. Tenant isolation

- Tenant scope is mandatory for every tenant-owned resource and job.
- Tenant context must be established from verified identity/session claims and server-side membership.
- Database policies provide defence in depth; application authorization remains mandatory.
- Cache, object storage, search indexes, exports, event topics and logs must not leak across tenants.
- Add adversarial tests for cross-tenant IDOR, guessed IDs, bulk exports, background jobs and report filters.
- Support personnel must use explicit, time-limited, audited break-glass access.

## 4. Application and infrastructure

- Follow OWASP ASVS and OWASP Top 10 as verification references.
- Use HTTPS/TLS, secure cookies, CSRF protections appropriate to the auth model, CSP and security headers.
- Apply rate limits, request size limits, upload scanning, MIME/content validation and safe file download headers.
- Use parameterized queries and output encoding. Protect against SSRF, injection, path traversal and unsafe deserialization.
- Store secrets in secret management systems; rotate keys and credentials.
- Encrypt backups and sensitive object storage; define key ownership and rotation.
- Generate audit events for finance, payroll, stock, HR records, permissions and configuration changes.
- Minimize personal data in logs and analytics; mask sensitive fields.

## 5. Secure delivery

- Review dependencies, licenses, vulnerabilities and transitive supply-chain risk.
- Scan source, dependencies, secrets and container images in CI.
- Produce an SBOM for each release; sign release images/artifacts where supported.
- Protect the default branch and require successful checks and review.
- No tokens, .env files, private keys, real employee information or production exports in commits.
- Security exceptions require owner, rationale, expiry and compensating control.

## 6. Privacy and retention

Classify data as public, internal, confidential or restricted. HR, payroll, identity, bank, medical/fitness and disciplinary records are restricted and require purpose-limited access.

Define lawful purpose, retention period, correction process, export, deletion/anonymization and breach-response handling for each data category. Cross-border hosting and subprocessors require a documented legal/privacy review before production use.

## 7. AI controls

- AI services receive only the minimum tenant-authorized context required.
- Never use one tenant's records to answer another tenant's query.
- Treat retrieved documents and model output as untrusted content.
- Human approval is required for payroll, hiring/rejection, disciplinary action, performance rating, financial posting, stock adjustment and other consequential decisions.
- Record model/service version, prompt/template version, source references, approver and resulting action where AI affects a business workflow.
