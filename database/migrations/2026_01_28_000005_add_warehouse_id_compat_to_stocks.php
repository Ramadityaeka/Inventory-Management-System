<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stocks')) {
            return;
        }

        if (!Schema::hasColumn('stocks', 'warehouse_id')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('unit_id');
            });

            // Populate warehouse_id from unit_id when possible
            try {
                DB::statement('UPDATE stocks SET warehouse_id = unit_id WHERE warehouse_id IS NULL AND unit_id IS NOT NULL');
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Create triggers to keep unit_id and warehouse_id in sync
        try {
            DB::unprepared('DROP TRIGGER IF EXISTS stocks_before_insert_compat');
            DB::unprepared('DROP TRIGGER IF EXISTS stocks_before_update_compat');

            DB::unprepared('\nCREATE TRIGGER stocks_before_insert_compat BEFORE INSERT ON stocks FOR EACH ROW\nBEGIN\n    IF NEW.warehouse_id IS NULL AND NEW.unit_id IS NOT NULL THEN\n        SET NEW.warehouse_id = NEW.unit_id;\n    END IF;\n    IF NEW.unit_id IS NULL AND NEW.warehouse_id IS NOT NULL THEN\n        SET NEW.unit_id = NEW.warehouse_id;\n    END IF;\nEND\n');

            DB::unprepared('\nCREATE TRIGGER stocks_before_update_compat BEFORE UPDATE ON stocks FOR EACH ROW\nBEGIN\n    IF NEW.warehouse_id IS NULL AND NEW.unit_id IS NOT NULL THEN\n        SET NEW.warehouse_id = NEW.unit_id;\n    END IF;\n    IF NEW.unit_id IS NULL AND NEW.warehouse_id IS NOT NULL THEN\n        SET NEW.unit_id = NEW.warehouse_id;\n    END IF;\nEND\n');
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function down(): void
    {
        try { DB::unprepared('DROP TRIGGER IF EXISTS stocks_before_insert_compat'); } catch (\Exception $e) {}
        try { DB::unprepared('DROP TRIGGER IF EXISTS stocks_before_update_compat'); } catch (\Exception $e) {}

        if (Schema::hasTable('stocks') && Schema::hasColumn('stocks', 'warehouse_id')) {
            Schema::table('stocks', function (Blueprint $table) {
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
