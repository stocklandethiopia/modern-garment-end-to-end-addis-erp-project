# Platform administration and tenant entitlement bootstrap

**Work package:** WP-03 — Enterprise, tenant, organisation and identity  
**Status:** PLATFORM LIFECYCLE/ENTITLEMENT FOUNDATION INTEGRATED; TWO-PERSON OPERATOR-GRANT LIFECYCLE AND PLATFORM UI ADDED ON WP-03 CANDIDATE BRANCH — EXACT-HEAD CI PENDING  
**Code boundary:** Laravel platform API + PostgreSQL fixed-search-path SECURITY DEFINER functions  
**Authority boundary:** Platform administration is separate from tenant OWNER/ADMIN authority.

## 1. Role separation

| Role | Scope | Permitted actions |
|---|---|---|
| Tenant OWNER | One tenant | Tenant configuration and delegated business administration. Cannot grant platform privileges, change tenant platform status or assign platform plan/features. |
| Tenant ADMIN | One tenant | Delegated tenant member/domain workflows. Cannot manage platform entitlements or suspend another tenant. |
| PLATFORM_SUPPORT | Global platform | Read the platform tenant register only. Cannot change tenant lifecycle, entitlements or operator grants. |
| PLATFORM_ADMIN | Global platform | Read the tenant register; request, independently approve and revoke operator grants; change tenant lifecycle and plan/features/limits, subject to reason, database checks and immutable platform audit. |
| DBA / controlled bootstrap operator | Database authority | Appoint the first PLATFORM_ADMIN and perform approved break-glass recovery if no active administrator remains. Routine subsequent grants use the application’s two-person workflow. This is not a tenant role. |

The platform grant table has no application INSERT/UPDATE/DELETE grant. `app_runtime` cannot execute the identity-sensitive platform or tenant-scope mutation functions. Laravel invokes those narrow, fixed-search-path SECURITY DEFINER APIs through a separate `app_control` login; that login has no table DML, schema CREATE, superuser, role-management or RLS-bypass privileges. The actor and tenant GUCs are set transaction-locally from the authenticated HTTP request by parameterized service code. SQL executed with ordinary `app_runtime` credentials cannot forge those values and call the privileged functions because it has no EXECUTE permission. Store the `app_control` credential only in the deployment secret manager. No controller or Angular permission is trusted as the final authority. A tenant OWNER/ADMIN cannot turn themselves into a platform operator.

## 2. Initial platform operator bootstrap

No platform operator is automatically created by the migration, owner provisioning command or tenant OWNER role. Existing legal/customer/production identity is not guessed and no email is hard-coded.

The accountable DBA performs a single, controlled two-person bootstrap that provisions **at least two distinct active PLATFORM_ADMIN identities** before enabling the operator workbench. Each grant must reference a documented appointment and approval record and use two different, active global officer identities for `granted_by` and `approved_by`, neither of which is the target operator. This minimum roster is required because every later approval must be made by a different active administrator. A one-admin-only bootstrap cannot grow its own roster through the application workflow. The application never seeds a real platform operator automatically. After the two-admin bootstrap, routine grants are handled through the two-person application workflow below.

Illustrative SQL only — substitute verified database UUIDs and the approved validity date after the separate appointment/approval record exists:

```sql
BEGIN;

INSERT INTO public.platform_operator_grants (
    user_id, role_code, status, valid_from, valid_to,
    grant_reason, granted_by, approved_by, approved_at
) VALUES
    (
        '<first-platform-admin-user-uuid>', 'PLATFORM_ADMIN', 'ACTIVE',
        CURRENT_DATE, CURRENT_DATE + 365,
        'Reference first separately approved platform-operator appointment.',
        '<first-granting-officer-user-uuid>', '<first-independent-approver-user-uuid>', now()
    ),
    (
        '<second-platform-admin-user-uuid>', 'PLATFORM_ADMIN', 'ACTIVE',
        CURRENT_DATE, CURRENT_DATE + 365,
        'Reference second separately approved platform-operator appointment.',
        '<second-granting-officer-user-uuid>', '<second-independent-approver-user-uuid>', now()
    );

COMMIT;
```

Do not execute this example with placeholder values. Confirm the two target operator IDs are distinct; for each row, the granting and approving officers are different, active and independently authorized; neither target approves their own grant; each target has a signed appointment; and both grants are current. The same authorized two-officer pair may process both appointments where the approved bootstrap procedure permits it. Verify two active administrators exist before enabling the platform workbench. Do not use the example SQL for routine grants after the two-administrator bootstrap has been completed.


## 2.1 Routine two-person operator-grant lifecycle

Once at least two active PLATFORM_ADMIN identities exist, subsequent platform operator grants must use the application workflow and not direct table writes:

- `GET /api/v1/platform/context` resolves the signed-in identity's currently active, date-effective platform roles. It requires authenticated global identity context and does not require tenant membership.
- `GET /api/v1/platform/operators/grants` lists the grant register for PLATFORM_ADMIN only. PLATFORM_SUPPORT receives 403.
- `POST /api/v1/platform/operators/grants` creates a PENDING grant for an existing ACTIVE global identity. The request specifies role, validity start/end and a 10–500 character justification. No new user, tenant or login credential is created by this endpoint. **The end date is mandatory for application requests and cannot be later than 365 days after the start date.** The initial first-admin DBA bootstrap is separately reviewed and is the only approved exception to that application limit.
- `POST /api/v1/platform/operators/grants/{grantId}/approve` activates a PENDING grant only when a different active PLATFORM_ADMIN approves it with an independent reason. The requester and grant recipient are both barred from approving it.
- `POST /api/v1/platform/operators/grants/{grantId}/revoke` revokes a PENDING or ACTIVE grant, requires a reason and prevents an operator from revoking their own grant.

Only `PLATFORM_ADMIN` may request, approve, revoke or list the global grant register. `PLATFORM_SUPPORT` remains read-only. A customer-tenant owner/admin does not receive these endpoints by virtue of their tenant role. The initial two-admin appointment and exceptional recovery remain controlled DBA processes, not routine app actions. If the roster falls to one active administrator, use a separately approved two-person DBA handoff to restore a second administrator; do not weaken the application separation-of-duties rule.

Each accepted transition appends an immutable `platform_audit_events` action:
`PLATFORM_OPERATOR_GRANT_REQUESTED`, `PLATFORM_OPERATOR_GRANT_APPROVED`, `PLATFORM_OPERATOR_GRANT_REVOKED`. The event records the actor, grant ID, before/after state and reason. The grant row separately records requester, approver, revoker and timestamps.

Authorization checks the grant date on every `platform_current_operator_roles()` lookup, so access is ineffective once the approved `valid_to` date has elapsed even if the stored status has not yet been swept. The status/audit expiry transition is currently performed lazily when an authorized PLATFORM_ADMIN lists the grant register or requests/approves/revokes a grant; a scheduled expiry worker is not yet claimed as implemented. The next scheduler/recovery design must use a separately constrained system actor and must not let app_runtime forge arbitrary audit actors.

## 3. Tenant lifecycle model

- `ACTIVE → SUSPENDED`: temporary access hold; transactional tenant context is denied. Data is retained.
- `SUSPENDED → ACTIVE`: permitted only by PLATFORM_ADMIN with a documented reason; the prior entitlement status is restored (including REVIEW_REQUIRED).
- `ACTIVE → CLOSED` or `SUSPENDED → CLOSED`: terminal administrative closure; application access is disabled and data remains retained for the approved legal/retention period.
- `CLOSED → ACTIVE`: denied by this implementation. A formal recovery process requires a separately approved, versioned recovery design and legal/data-integrity review.
- A tenant lifecycle change updates `lifecycle_version`, timestamp/reason fields and writes an append-only `platform_audit_events` record.
- Closing or suspending a tenant does not delete tenant data, reverse journals, change payroll history or silently alter operational events.

The migration refuses to create these controls if existing tenant status records cannot be reconciled. Current tenants receive an explicit `UNASSIGNED / REVIEW_REQUIRED` entitlement record; no commercial plan or feature allowance is inferred.

## 4. Entitlement policy

Plans accepted by the schema are `STARTER`, `BUSINESS`, `ENTERPRISE` and `CUSTOM`. The initial `UNASSIGNED` state is a migration/provisioning state, not a purchasable/activated plan.

Entitlement records contain:
- plan code and lifecycle status (`REVIEW_REQUIRED`, `ACTIVE`, `SUSPENDED`, `EXPIRED`);
- boolean feature flags;
- non-negative integer limits;
- effective-from/effective-to dates;
- actor, reason and timestamps.

The database rejects non-object feature/limit payloads, non-boolean feature values, negative/non-integer limits, invalid effective dates, and an ACTIVE entitlement outside its effective period. An inactive tenant cannot receive ACTIVE entitlements.

The current tenant can read its own entitlement summary at `GET /api/v1/tenant/entitlements`; it cannot change it. Platform administration endpoints are:
- `GET /api/v1/platform/context` — resolves active global operator roles for an authenticated identity;
- `GET /api/v1/platform/tenants` — PLATFORM_ADMIN or PLATFORM_SUPPORT, read only and audit logged;
- `PATCH /api/v1/platform/tenants/{tenantId}/lifecycle` — PLATFORM_ADMIN only;
- `PUT /api/v1/platform/tenants/{tenantId}/entitlements` — PLATFORM_ADMIN only;
- `GET/POST /api/v1/platform/operators/grants` — PLATFORM_ADMIN only;
- `POST /api/v1/platform/operators/grants/{grantId}/approve` — independent PLATFORM_ADMIN approval;
- `POST /api/v1/platform/operators/grants/{grantId}/revoke` — reasoned PLATFORM_ADMIN revocation.

These routes require an authenticated identity context. The database functions independently validate the platform grant, so controller/middleware checks are not the only security boundary.

The global tenant-register read is itself privileged metadata access. The follow-on audit control appends `PLATFORM_TENANT_REGISTER_VIEWED` to `platform_audit_events` for both PLATFORM_ADMIN and read-only PLATFORM_SUPPORT. The event records the actor and aggregate tenant count only; it does not copy tenant names, contacts, customer details or transaction contents. Runtime roles cannot modify or delete the event. The assertions in `PlatformAdministrationTest.php` require a read event for both roles. PR #11 source SHA `92d99588f10a281ef41ae87e27deeb1b6a31ff70` passed ERP CI #45900565705 (**93 tests / 1,081 assertions**) and combined WP verification #45900565706 (all four jobs passed; artifact ID `15155567006`).

## 5. Database and audit controls

Foundation migration: `backend/database/migrations/2026_11_26_000032_add_platform_entitlements_and_tenant_lifecycle.php`. Operator grant lifecycle migration: `backend/database/migrations/2026_11_27_000033_create_platform_operator_grant_lifecycle.php`. Tenant scope mutation hardening: `2026_11_27_000034_secure_tenant_access_scope_mutations.php`. Dedicated control-function privilege isolation: `2026_11_27_000037_isolate_identity_control_functions.php`.

Tables:
- `platform_operator_grants`: global operator grants; self-read RLS; no runtime write privileges.
- `platform_tenant_entitlements`: tenant-scoped entitlement records; forced RLS and tenant read-only access for app_runtime.
- `platform_audit_events`: global append-only platform audit; no direct app_runtime table privileges.

Functions:
- `public.platform_list_tenants()`
- `public.platform_change_tenant_status(uuid,text,text)`
- `public.platform_set_tenant_entitlements(uuid,text,text,jsonb,jsonb,date,date,text)`
- `public.platform_get_current_entitlement(uuid)`
- `public.platform_current_operator_roles()`
- `public.platform_list_operator_grants()`
- `public.platform_request_operator_grant(text,text,date,date,text)`
- `public.platform_approve_operator_grant(uuid,text)`
- `public.platform_revoke_operator_grant(uuid,text)`
- internal `public.platform_expire_operator_grants(uuid)`
- private `public.require_platform_operator(text)`

All exposed functions use SECURITY DEFINER with fixed `search_path = pg_catalog, public`. PUBLIC and app_runtime EXECUTE are revoked for identity-sensitive administrative functions; only the isolated app_control role receives EXECUTE on the narrow intended API functions. The internal expiry helper is not executable by PUBLIC, app_runtime or app_control directly. Privileged functions re-check global operator status/role/validity from PostgreSQL. Neither app_runtime nor app_control receives direct table DML on platform/tenant-scope control tables.

Migration rollback is intentionally blocked when grants, configured entitlements or audit history exist; this preserves platform governance and audit evidence.

## 6. Operational gates still outstanding

- Provision the separate non-superuser `app_control` database login through deployment secrets before applying migration 000037; give it CONNECT, schema USAGE and only the explicit function EXECUTE grants. Do not share its credential with browser clients, users or unrelated workers.
- Assign at least two distinct real PLATFORM_ADMIN identities and any PLATFORM_SUPPORT identities using the controlled two-person bootstrap; this repository intentionally seeds none.
- Complete a formal entitlement catalogue/plan definition with the product owner; no feature is enabled merely because a plan name exists.
- Define tenant closure retention schedule, legal hold, data export and approved recovery workflow.
- Complete scheduled persisted expiry sweeps with a non-forgeable system audit identity; current role authorization already stops access at the validity date, and persisted EXPIRED status is swept on authorized admin activity.
- Add MFA/OIDC/SSO and complete platform administration Angular workflows (read-only tenant register and operator-grant workbench have been added on the candidate branch; exact-head CI is pending).
- Run tenant lifecycle and entitlement test matrix, independent authorization review, concurrency tests and backup/restore rehearsal.
- Do not use tenant OWNER or ADMIN identities as a substitute for platform authority.
