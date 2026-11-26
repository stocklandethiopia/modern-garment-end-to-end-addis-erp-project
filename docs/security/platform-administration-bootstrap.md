# Platform administration and tenant entitlement bootstrap

**Work package:** WP-03 — Enterprise, tenant, organisation and identity  
**Status:** IMPLEMENTATION CANDIDATE — exact-head CI and operational acceptance required  
**Code boundary:** Laravel platform API + PostgreSQL fixed-search-path SECURITY DEFINER functions  
**Authority boundary:** Platform administration is separate from tenant OWNER/ADMIN authority.

## 1. Role separation

| Role | Scope | Permitted actions |
|---|---|---|
| Tenant OWNER | One tenant | Tenant configuration and delegated business administration. Cannot grant platform privileges, change tenant platform status or assign platform plan/features. |
| Tenant ADMIN | One tenant | Delegated tenant member/domain workflows. Cannot manage platform entitlements or suspend another tenant. |
| PLATFORM_SUPPORT | Global platform | Read the platform tenant register; cannot change tenant lifecycle or entitlements. |
| PLATFORM_ADMIN | Global platform | Change tenant lifecycle and platform plan/features/limits, subject to reason, database checks and immutable platform audit. |
| DBA / controlled bootstrap operator | Database authority | Provision/revoke platform operator grants through a reviewed, two-person controlled process. This is not an application role and must not be exposed as a tenant UI action. |

The platform grant table has no application INSERT/UPDATE/DELETE grant. The regular `app_runtime` role can only read its own grant rows (RLS filtered by the authenticated identity) and can invoke the narrow role-checked functions. A tenant OWNER cannot turn themselves into a platform operator.

## 2. Initial platform operator bootstrap

No platform operator is automatically created by the migration, owner provisioning command or tenant OWNER role. Existing legal/customer/production identity is not guessed and no email is hard-coded.

After the organisation appoints a named platform operator, the accountable DBA performs a reviewed grant insert under the controlled migration/DBA connection. It must use **two different, active global user identities** for `granted_by` and `approved_by`, with a documented reason and validity period. The application does not expose a route for this grant operation.

Illustrative SQL only — substitute verified database UUIDs and the approved validity date after the separate appointment/approval record exists:

```sql
BEGIN;

INSERT INTO public.platform_operator_grants (
    user_id, role_code, status, valid_from, valid_to,
    grant_reason, granted_by, approved_by, approved_at
) VALUES (
    '<approved-operator-user-uuid>',
    'PLATFORM_ADMIN',
    'ACTIVE',
    CURRENT_DATE,
    NULL,
    'Reference the separately approved platform-operator appointment record.',
    '<granting-officer-user-uuid>',
    '<independent-approver-user-uuid>',
    now()
);

COMMIT;
```

Do not execute this example with placeholder values. Verify the resulting user, two-person approval, role, effective period and audit/approval record before enabling platform administration. Revoke or expire the operator grant promptly when the appointment ends.

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
- `GET /api/v1/platform/tenants` — PLATFORM_ADMIN or PLATFORM_SUPPORT;
- `PATCH /api/v1/platform/tenants/{tenantId}/lifecycle` — PLATFORM_ADMIN only;
- `PUT /api/v1/platform/tenants/{tenantId}/entitlements` — PLATFORM_ADMIN only.

These routes require an authenticated identity context. The database functions independently validate the platform grant, so controller/middleware checks are not the only security boundary.

## 5. Database and audit controls

Migration: `backend/database/migrations/2026_11_26_000032_add_platform_entitlements_and_tenant_lifecycle.php`.

Tables:
- `platform_operator_grants`: global operator grants; self-read RLS; no runtime write privileges.
- `platform_tenant_entitlements`: tenant-scoped entitlement records; forced RLS and tenant read-only access for app_runtime.
- `platform_audit_events`: global append-only platform audit; no direct app_runtime table privileges.

Functions:
- `public.platform_list_tenants()`
- `public.platform_change_tenant_status(uuid,text,text)`
- `public.platform_set_tenant_entitlements(uuid,text,text,jsonb,jsonb,date,date,text)`
- `public.platform_get_current_entitlement(uuid)`
- private `public.require_platform_operator(text)`

All exposed functions use SECURITY DEFINER with fixed `search_path = pg_catalog, public`; PUBLIC EXECUTE is revoked and only app_runtime EXECUTE is granted to the four intended API functions. The privileged functions re-check global operator status/role/validity from PostgreSQL. Direct updates to tenants, entitlements, grants or platform audit remain unavailable to app_runtime.

Migration rollback is intentionally blocked when grants, configured entitlements or audit history exist; this preserves platform governance and audit evidence.

## 6. Operational gates still outstanding

- Assign the real PLATFORM_ADMIN and PLATFORM_SUPPORT identities using the controlled two-person process; this repository intentionally seeds none.
- Complete a formal entitlement catalogue/plan definition with the product owner; no feature is enabled merely because a plan name exists.
- Define tenant closure retention schedule, legal hold, data export and approved recovery workflow.
- Add MFA/OIDC/SSO and dedicated platform administration Angular workflows.
- Run tenant lifecycle and entitlement test matrix, independent authorization review, concurrency tests and backup/restore rehearsal.
- Do not use tenant OWNER or ADMIN identities as a substitute for platform authority.
