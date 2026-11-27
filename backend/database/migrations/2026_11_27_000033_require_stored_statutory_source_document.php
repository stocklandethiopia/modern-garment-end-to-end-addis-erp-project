<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Statutory source evidence requires PostgreSQL.');
        }

        DB::statement("ALTER TABLE statutory_rule_versions ADD COLUMN source_document_reference varchar(255)");

        DB::statement(<<<'SQL'
ALTER TABLE statutory_rule_versions
ADD CONSTRAINT statutory_rule_approved_source_document_required
CHECK (
    verification_status <> 'PROFESSIONAL_APPROVED'
    OR (
        source_document_reference IS NOT NULL
        AND btrim(source_document_reference) <> ''
    )
)
SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE statutory_rule_versions DROP CONSTRAINT IF EXISTS statutory_rule_approved_source_document_required');
        DB::statement('ALTER TABLE statutory_rule_versions DROP COLUMN IF EXISTS source_document_reference');
    }
};
