<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_movements')) {
            return;
        }

        if (!Schema::hasColumn('stock_movements', 'warehouse_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('unit_id');
            });

            // Populate from unit_id
            try {
                DB::statement('UPDATE stock_movements SET warehouse_id = unit_id WHERE warehouse_id IS NULL AND unit_id IS NOT NULL');
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Create triggers to keep unit_id and warehouse_id in sync
        try {
            DB::unprepared('DROP TRIGGER IF EXISTS stock_movements_before_insert_compat');
            DB::unprepared('DROP TRIGGER IF EXISTS stock_movements_before_update_compat');

            DB::unprepared('\nCREATE TRIGGER stock_movements_before_insert_compat BEFORE INSERT ON stock_movements FOR EACH ROW\nBEGIN\n    IF NEW.warehouse_id IS NULL AND NEW.unit_id IS NOT NULL THEN\n        SET NEW.warehouse_id = NEW.unit_id;\n    END IF;\n    IF NEW.unit_id IS NULL AND NEW.warehouse_id IS NOT NULL THEN\n        SET NEW.unit_id = NEW.warehouse_id;\n    END IF;\nEND\n');

            DB::unprepared('\nCREATE TRIGGER stock_movements_before_update_compat BEFORE UPDATE ON stock_movements FOR EACH ROW\nBEGIN\n    IF NEW.warehouse_id IS NULL AND NEW.unit_id IS NOT NULL THEN\n        SET NEW.warehouse_id = NEW.unit_id;\n    END IF;\n    IF NEW.unit_id IS NULL AND NEW.warehouse_id IS NOT NULL THEN\n        SET NEW.unit_id = NEW.warehouse_id;\n    END IF;\nEND\n');
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function down(): void
    {
        try { DB::unprepared('DROP TRIGGER IF EXISTS stock_movements_before_insert_compat'); } catch (\Exception $e) {}
        try { DB::unprepared('DROP TRIGGER IF EXISTS stock_movements_before_update_compat'); } catch (\Exception $e) {}

        if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'warehouse_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
