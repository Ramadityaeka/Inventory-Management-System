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
        // Drop old trigger
        DB::unprepared('DROP TRIGGER IF EXISTS after_update_stocks_alert');
        
        // Create new trigger without min_threshold logic
        DB::unprepared('
            CREATE TRIGGER after_update_stocks_alert 
            AFTER UPDATE ON stocks 
            FOR EACH ROW
            BEGIN
                -- Only create alert when stock reaches 0
                IF NEW.quantity = 0 AND OLD.quantity != 0 THEN
                    INSERT INTO stock_alerts 
                    (item_id, warehouse_id, alert_type, current_stock, threshold, created_at, updated_at)
                    VALUES 
                    (NEW.item_id, NEW.warehouse_id, "out_of_stock", 0, 0, NOW(), NOW());
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new trigger
        DB::unprepared('DROP TRIGGER IF EXISTS after_update_stocks_alert');
        
        // Restore old trigger (but it won't work since min_threshold column is gone)
        // This is just for reference
        DB::unprepared('
            CREATE TRIGGER after_update_stocks_alert 
            AFTER UPDATE ON stocks 
            FOR EACH ROW
            BEGIN
                IF NEW.quantity = 0 THEN
                    INSERT INTO stock_alerts 
                    (item_id, warehouse_id, alert_type, current_stock, threshold, created_at, updated_at)
                    VALUES 
                    (NEW.item_id, NEW.warehouse_id, "out_of_stock", 0, 0, NOW(), NOW());
                END IF;
            END
        ');
    }
};
