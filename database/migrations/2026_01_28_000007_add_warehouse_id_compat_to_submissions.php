<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('submissions')) {
            return;
        }

        if (!Schema::hasColumn('submissions', 'warehouse_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('item_id');
            });

            // Populate from unit_id when present
            try {
                DB::statement('UPDATE submissions SET warehouse_id = unit_id WHERE warehouse_id IS NULL AND unit_id IS NOT NULL');
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Create triggers to keep warehouse_id and unit_id in sync
        try {
            DB::unprepared('DROP TRIGGER IF EXISTS submissions_before_insert_compat');
            DB::unprepared('DROP TRIGGER IF EXISTS submissions_before_update_compat');

            DB::unprepared('\nCREATE TRIGGER submissions_before_insert_compat BEFORE INSERT ON submissions FOR EACH ROW\nBEGIN\n    IF NEW.warehouse_id IS NULL AND NEW.unit_id IS NOT NULL THEN\n        SET NEW.warehouse_id = NEW.unit_id;\n    END IF;\n    IF NEW.unit_id IS NULL AND NEW.warehouse_id IS NOT NULL THEN\n        SET NEW.unit_id = NEW.warehouse_id;\n    END IF;\nEND\n');

            DB::unprepared('\nCREATE TRIGGER submissions_before_update_compat BEFORE UPDATE ON submissions FOR EACH ROW\nBEGIN\n    IF NEW.warehouse_id IS NULL AND NEW.unit_id IS NOT NULL THEN\n        SET NEW.warehouse_id = NEW.unit_id;\n    END IF;\n    IF NEW.unit_id IS NULL AND NEW.warehouse_id IS NOT NULL THEN\n        SET NEW.unit_id = NEW.warehouse_id;\n    END IF;\nEND\n');
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function down(): void
    {
        try { DB::unprepared('DROP TRIGGER IF EXISTS submissions_before_insert_compat'); } catch (\Exception $e) {}
        try { DB::unprepared('DROP TRIGGER IF EXISTS submissions_before_update_compat'); } catch (\Exception $e) {}

        if (Schema::hasTable('submissions') && Schema::hasColumn('submissions', 'warehouse_id')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
