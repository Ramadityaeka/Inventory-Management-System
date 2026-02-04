<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Recreate trigger to use conversion_factor
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');

        DB::unprepared('
            CREATE TRIGGER after_insert_approvals
            AFTER INSERT ON approvals
            FOR EACH ROW
            BEGIN
                DECLARE item_id_var INT;
                DECLARE warehouse_id_var INT;
                DECLARE quantity_var INT;
                DECLARE conversion_factor_var INT DEFAULT 1;
                DECLARE total_qty INT;

                SELECT s.item_id, s.warehouse_id, s.quantity, COALESCE(s.conversion_factor, 1)
                INTO item_id_var, warehouse_id_var, quantity_var, conversion_factor_var
                FROM submissions s
                WHERE s.id = NEW.submission_id;

                SET total_qty = quantity_var * conversion_factor_var;

                IF NEW.action = "approved" AND item_id_var IS NOT NULL THEN
                    INSERT INTO stocks (item_id, warehouse_id, quantity, last_updated)
                    VALUES (item_id_var, warehouse_id_var, total_qty, NOW())
                    ON DUPLICATE KEY UPDATE
                        quantity = quantity + total_qty,
                        last_updated = NOW();

                    INSERT INTO stock_movements (item_id, warehouse_id, quantity, movement_type, reference_type, reference_id, created_by, created_at)
                    VALUES (
                        item_id_var,
                        warehouse_id_var,
                        total_qty,
                        "in",
                        "submission",
                        NEW.submission_id,
                        NEW.admin_id,
                        NOW()
                    );
                END IF;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');
    }
};
