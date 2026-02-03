<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing trigger
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');
        
        // Recreate trigger with conversion_factor support
        DB::unprepared('
            CREATE TRIGGER after_insert_approvals
            AFTER INSERT ON approvals
            FOR EACH ROW
            BEGIN
                DECLARE item_id_var INT;
                DECLARE warehouse_id_var INT;
                DECLARE quantity_var INT;
                DECLARE conversion_factor_var INT;
                DECLARE base_quantity INT;
                
                -- Get submission details including conversion_factor
                SELECT s.item_id, s.warehouse_id, s.quantity, COALESCE(s.conversion_factor, 1)
                INTO item_id_var, warehouse_id_var, quantity_var, conversion_factor_var
                FROM submissions s
                WHERE s.id = NEW.submission_id;
                
                -- Calculate base quantity: quantity * conversion_factor
                SET base_quantity = quantity_var * conversion_factor_var;
                
                -- Only update stock if approved AND item_id is not NULL
                -- (Skip for manual item entries without item_id)
                IF NEW.action = "approved" AND item_id_var IS NOT NULL THEN
                    -- Insert or update stock (using base quantity)
                    INSERT INTO stocks (item_id, warehouse_id, quantity, last_updated)
                    VALUES (item_id_var, warehouse_id_var, base_quantity, NOW())
                    ON DUPLICATE KEY UPDATE 
                        quantity = quantity + base_quantity,
                        last_updated = NOW();
                    
                    -- Insert stock movement with base quantity
                    INSERT INTO stock_movements (item_id, warehouse_id, quantity, movement_type, reference_type, reference_id, created_by, created_at)
                    VALUES (
                        item_id_var, 
                        warehouse_id_var, 
                        base_quantity, 
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
        // Drop the trigger
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');
        
        // Recreate the old trigger without conversion_factor
        DB::unprepared('
            CREATE TRIGGER after_insert_approvals
            AFTER INSERT ON approvals
            FOR EACH ROW
            BEGIN
                DECLARE item_id_var INT;
                DECLARE warehouse_id_var INT;
                DECLARE quantity_var INT;
                
                SELECT s.item_id, s.warehouse_id, s.quantity 
                INTO item_id_var, warehouse_id_var, quantity_var
                FROM submissions s
                WHERE s.id = NEW.submission_id;
                
                IF NEW.action = "approved" AND item_id_var IS NOT NULL THEN
                    INSERT INTO stocks (item_id, warehouse_id, quantity, last_updated)
                    VALUES (item_id_var, warehouse_id_var, quantity_var, NOW())
                    ON DUPLICATE KEY UPDATE 
                        quantity = quantity + quantity_var,
                        last_updated = NOW();
                    
                    INSERT INTO stock_movements (item_id, warehouse_id, quantity, movement_type, reference_type, reference_id, created_by, created_at)
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
};
