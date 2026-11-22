<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tenantTables = [
        'tenant_memberships',
        'legal_entities',
        'business_sites',
        'operational_nodes',
        'branch_registrations',
        'inventory_locations',
        'audit_events',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('The enterprise foundation requires PostgreSQL with Row-Level Security.');
        }

        Schema::create('tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 80)->unique();
            $table->string('name', 200);
            $table->string('status', 20)->default('ACTIVE');
            $table->string('locale', 12)->default('en');
            $table->string('timezone', 64)->default('Africa/Addis_Ababa');
            $table->timestampsTz();
        });

        Schema::create('tenant_memberships', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id');
            $table->string('membership_role', 24)->default('MEMBER');
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampTz('joined_at')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->timestampsTz();
            $table->unique(['tenant_id', 'user_id']);
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->index(['user_id', 'status']);
        });

        Schema::create('legal_entities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('registration_number', 100);
            $table->string('legal_name', 255);
            $table->string('legal_form', 100)->nullable();
            $table->char('country_code', 2);
            $table->string('tax_identifier', 100)->nullable();
            $table->char('base_currency', 3);
            $table->string('status', 20)->default('DRAFT');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'registration_number']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('business_sites', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('legal_entity_id')->nullable();
            $table->string('code', 80);
            $table->string('site_type', 40);
            $table->string('name', 180);
            $table->char('country_code', 2);
            $table->string('region', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('address_line', 255)->nullable();
            $table->string('timezone', 64)->default('Africa/Addis_Ababa');
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'legal_entity_id'])
                ->references(['tenant_id', 'id'])->on('legal_entities')->restrictOnDelete();
            $table->index(['tenant_id', 'site_type', 'status']);
        });

        Schema::create('operational_nodes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('business_site_id');
            $table->uuid('legal_entity_id')->nullable();
            $table->string('code', 80);
            $table->string('node_type', 40);
            $table->string('name', 180);
            $table->jsonb('capabilities')->default(DB::raw("'{}'::jsonb"));
            $table->boolean('stock_holding_enabled')->default(false);
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'business_site_id'])
                ->references(['tenant_id', 'id'])->on('business_sites')->restrictOnDelete();
            $table->foreign(['tenant_id', 'legal_entity_id'])
                ->references(['tenant_id', 'id'])->on('legal_entities')->restrictOnDelete();
            $table->index(['tenant_id', 'node_type', 'status']);
        });

        Schema::create('branch_registrations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('legal_entity_id');
            $table->uuid('business_site_id');
            $table->string('commercial_register_reference', 120);
            $table->jsonb('activity_codes')->default(DB::raw("'[]'::jsonb"));
            $table->string('issuing_authority', 180)->nullable();
            $table->date('registered_at')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('status', 24)->default('REVIEW_REQUIRED');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'legal_entity_id'])
                ->references(['tenant_id', 'id'])->on('legal_entities')->restrictOnDelete();
            $table->foreign(['tenant_id', 'business_site_id'])
                ->references(['tenant_id', 'id'])->on('business_sites')->restrictOnDelete();
            $table->index(['tenant_id', 'status', 'valid_to']);
        });

        Schema::create('inventory_locations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('operational_node_id');
            $table->uuid('parent_id')->nullable();
            $table->string('code', 100);
            $table->string('location_type', 40);
            $table->boolean('allow_stock')->default(true);
            $table->string('status', 20)->default('ACTIVE');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'operational_node_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'operational_node_id'])
                ->references(['tenant_id', 'id'])->on('operational_nodes')->restrictOnDelete();
            $table->foreign(['tenant_id', 'parent_id'])
                ->references(['tenant_id', 'id'])->on('inventory_locations')->restrictOnDelete();
        });

        Schema::create('audit_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('actor_user_id')->nullable();
            $table->string('action', 120);
            $table->string('object_type', 120);
            $table->uuid('object_id')->nullable();
            $table->jsonb('scope')->default(DB::raw("'{}'::jsonb"));
            $table->string('reason', 500)->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->timestampTz('occurred_at');
            $table->timestampsTz();
            $table->unique(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['tenant_id', 'object_type', 'object_id']);
            $table->index(['tenant_id', 'occurred_at']);
        });

        // Fail closed: runtime has no default table grants. Every new domain
        // migration must explicitly grant the minimum privileges after enabling RLS.
        DB::statement('REVOKE ALL ON tenants, users, tenant_memberships, legal_entities, business_sites, operational_nodes, branch_registrations, inventory_locations, audit_events FROM app_runtime');

        // Authentication and tenant selection may read global identities and only
        // the current user's memberships. Membership/identity mutation stays privileged.
        DB::statement('GRANT SELECT ON tenants, users, tenant_memberships TO app_runtime');

        // Legal entities can be registered as drafts; activation and amendment require
        // a separate approval workflow, so the runtime cannot update or delete them.
        DB::statement('GRANT SELECT, INSERT ON legal_entities TO app_runtime');

        // Site/network setup can create and amend records but cannot erase history.
        DB::statement('GRANT SELECT, INSERT, UPDATE ON business_sites, operational_nodes, branch_registrations, inventory_locations TO app_runtime');

        // Audit events are append-only. The trigger and SQL grants both deny mutation.
        DB::statement('GRANT SELECT, INSERT ON audit_events TO app_runtime');
        DB::statement('REVOKE ALL ON migrations FROM app_runtime');

        // Tenant roots are visible only for the selected tenant or to a user
        // with an active membership. This protects the tenant selector itself.
        DB::statement('ALTER TABLE tenants ENABLE ROW LEVEL SECURITY');
        DB::statement('ALTER TABLE tenants FORCE ROW LEVEL SECURITY');
        DB::unprepared(<<<'SQL'
            CREATE POLICY tenant_isolation_tenants ON tenants
            USING (
                id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );

            CREATE POLICY tenant_self_membership_access ON tenants
            FOR SELECT
            USING (
                EXISTS (
                    SELECT 1
                    FROM tenant_memberships m
                    WHERE m.tenant_id = tenants.id
                      AND m.user_id = NULLIF(current_setting('app.current_user_id', true), '')::uuid
                      AND m.status = 'ACTIVE'
                )
            );
        SQL);

        foreach ($this->tenantTables as $table) {
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::statement(
                "CREATE POLICY tenant_isolation_{$table} ON {$table}
                 USING (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
                 WITH CHECK (tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)"
            );
        }

        // A user may list their own memberships across tenants before selecting
        // one tenant. This policy does not expose another user's memberships.
        DB::statement(
            "CREATE POLICY tenant_membership_self_select ON tenant_memberships FOR SELECT
             USING (user_id = NULLIF(current_setting('app.current_user_id', true), '')::uuid)"
        );
        DB::statement('REVOKE ALL ON migrations FROM app_runtime');

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION prevent_audit_event_mutation()
            RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'audit_events are append-only; write a compensating event instead';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER audit_events_append_only
            BEFORE UPDATE OR DELETE ON audit_events
            FOR EACH ROW EXECUTE FUNCTION prevent_audit_event_mutation();
        SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS audit_events_append_only ON audit_events');
            DB::unprepared('DROP FUNCTION IF EXISTS prevent_audit_event_mutation()');
        }

        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('inventory_locations');
        Schema::dropIfExists('branch_registrations');
        Schema::dropIfExists('operational_nodes');
        Schema::dropIfExists('business_sites');
        Schema::dropIfExists('legal_entities');
        Schema::dropIfExists('tenant_memberships');
        Schema::dropIfExists('tenants');
    }
};
