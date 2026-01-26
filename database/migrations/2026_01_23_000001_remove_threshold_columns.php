<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop view first
        DB::statement('DROP VIEW IF EXISTS view_stock_overview');
        
        // Drop threshold column from stock_alerts if exists
        if (Schema::hasColumn('stock_alerts', 'threshold')) {
            Schema::table('stock_alerts', function (Blueprint $table) {
                $table->dropColumn('threshold');
            });
        }
        
        // Drop threshold column from items table if exists
        if (Schema::hasColumn('items', 'min_threshold')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('min_threshold');
            });
        }
        
        // Recreate view without threshold
        DB::statement("
            CREATE VIEW view_stock_overview AS
            SELECT 
                s.id,
                i.code AS item_code,
                i.name AS item_name,
                c.name AS category_name,
                w.name AS warehouse_name,
                s.quantity,
                i.unit,
                CASE 
                    WHEN s.quantity = 0 THEN 'Out of Stock'
                    ELSE 'Normal'
                END AS stock_status,
                s.last_updated
            FROM stocks s
            JOIN items i ON s.item_id = i.id
            JOIN categories c ON i.category_id = c.id
            JOIN warehouses w ON s.warehouse_id = w.id
            WHERE i.is_active = 1
        ");
        
        // Update trigger to remove threshold logic
        DB::unprepared('DROP TRIGGER IF EXISTS after_stock_update');
        
        DB::unprepared("
            CREATE TRIGGER after_stock_update
            AFTER UPDATE ON stocks
            FOR EACH ROW
            BEGIN
                IF NEW.quantity = 0 THEN
                    INSERT INTO stock_alerts 
                        (item_id, warehouse_id, alert_type, current_stock)
                    VALUES 
                        (NEW.item_id, NEW.warehouse_id, 'out_of_stock', NEW.quantity)
                    ON DUPLICATE KEY UPDATE 
                        current_stock = NEW.quantity,
                        resolved_at = NULL,
                        created_at = NOW();
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back threshold columns
        Schema::table('items', function (Blueprint $table) {
            $table->integer('min_threshold')->default(0)->after('unit');
        });
        
        Schema::table('stock_alerts', function (Blueprint $table) {
            $table->integer('threshold')->after('current_stock');
        });
        
        // Drop and recreate view with threshold
        DB::statement('DROP VIEW IF EXISTS view_stock_overview');
        
        DB::statement("
            CREATE VIEW view_stock_overview AS
            SELECT 
                s.id,
                i.code AS item_code,
                i.name AS item_name,
                c.name AS category_name,
                w.name AS warehouse_name,
                s.quantity,
                i.unit,
                i.min_threshold,
                CASE 
                    WHEN s.quantity = 0 THEN 'Out of Stock'
                    WHEN s.quantity <= i.min_threshold THEN 'Low Stock'
                    ELSE 'Normal'
                END AS stock_status,
                s.last_updated
            FROM stocks s
            JOIN items i ON s.item_id = i.id
            JOIN categories c ON i.category_id = c.id
            JOIN warehouses w ON s.warehouse_id = w.id
            WHERE i.is_active = 1
        ");
    }
};
