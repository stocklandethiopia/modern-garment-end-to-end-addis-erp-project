<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
ALTER TABLE finished_goods_reservations
    ADD COLUMN source_location_id uuid;
ALTER TABLE finished_goods_reservations
    ADD CONSTRAINT finished_goods_reservations_location_fk
    FOREIGN KEY (tenant_id, source_location_id)
    REFERENCES inventory_locations(tenant_id, id) ON DELETE RESTRICT;
CREATE INDEX finished_goods_reservations_location_idx
    ON finished_goods_reservations (tenant_id, source_location_id, finished_goods_lot_id, status);

CREATE OR REPLACE FUNCTION guard_finished_goods_transaction_immutable() RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    RAISE EXCEPTION 'Finished-goods transactions are append-only; corrections require a compensating transaction.'
        USING ERRCODE = '55000';
END;
$$;
CREATE TRIGGER finished_goods_transactions_immutable
BEFORE UPDATE OR DELETE ON finished_goods_transactions
FOR EACH ROW EXECUTE FUNCTION guard_finished_goods_transaction_immutable();

CREATE OR REPLACE FUNCTION guard_finished_goods_reservation_transition() RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF TG_OP = 'DELETE' THEN
        RAISE EXCEPTION 'Finished-goods reservations cannot be deleted; release or cancel them with an audited status transition.'
            USING ERRCODE = '55000';
    END IF;
    IF OLD.status IN ('FULFILLED','RELEASED','CANCELLED','EXPIRED') AND NEW.status IS DISTINCT FROM OLD.status THEN
        RAISE EXCEPTION 'A closed finished-goods reservation cannot be reopened or rewritten.'
            USING ERRCODE = '55000';
    END IF;
    IF NEW.tenant_id IS DISTINCT FROM OLD.tenant_id
       OR NEW.customer_order_line_id IS DISTINCT FROM OLD.customer_order_line_id
       OR NEW.finished_goods_lot_id IS DISTINCT FROM OLD.finished_goods_lot_id
       OR NEW.source_location_id IS DISTINCT FROM OLD.source_location_id
       OR NEW.quantity IS DISTINCT FROM OLD.quantity
       OR NEW.reservation_key IS DISTINCT FROM OLD.reservation_key THEN
        RAISE EXCEPTION 'Reservation identity and quantity are immutable; use a new reservation.'
            USING ERRCODE = '55000';
    END IF;
    RETURN NEW;
END;
$$;
CREATE TRIGGER finished_goods_reservation_transition_guard
BEFORE UPDATE OR DELETE ON finished_goods_reservations
FOR EACH ROW EXECUTE FUNCTION guard_finished_goods_reservation_transition();
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP FUNCTION IF EXISTS guard_finished_goods_transaction_immutable() CASCADE');
        DB::statement('DROP FUNCTION IF EXISTS guard_finished_goods_reservation_transition() CASCADE');
        DB::statement('ALTER TABLE finished_goods_reservations DROP CONSTRAINT IF EXISTS finished_goods_reservations_location_fk');
        DB::statement('ALTER TABLE finished_goods_reservations DROP COLUMN IF EXISTS source_location_id');
    }
};
