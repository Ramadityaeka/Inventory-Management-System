<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing trigger
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');
        
        // Recreate trigger with NULL item_id check
        DB::unprepared('
            CREATE TRIGGER after_insert_approvals
            AFTER INSERT ON approvals
            FOR EACH ROW
            BEGIN
                DECLARE item_id_var INT;
                DECLARE warehouse_id_var INT;
                DECLARE quantity_var INT;
                
                -- Get submission details
                SELECT s.item_id, s.warehouse_id, s.quantity 
                INTO item_id_var, warehouse_id_var, quantity_var
                FROM submissions s
                WHERE s.id = NEW.submission_id;
                
                -- Only update stock if approved AND item_id is not NULL
                -- (Skip for manual item entries without item_id)
                IF NEW.action = "approved" AND item_id_var IS NOT NULL THEN
                    -- Insert or update stock
                    INSERT INTO stocks (item_id, warehouse_id, quantity, last_updated)
                    VALUES (item_id_var, warehouse_id_var, quantity_var, NOW())
                    ON DUPLICATE KEY UPDATE 
                        quantity = quantity + quantity_var,
                        last_updated = NOW();
                    
                    -- Insert stock movement
                    INSERT INTO stock_movements (item_id, warehouse_id, quantity, type, reference_type, reference_id, user_id, created_at)
                    VALUES (
                        item_id_var, 
                        warehouse_id_var, 
                        quantity_var, 
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');
    }
};
