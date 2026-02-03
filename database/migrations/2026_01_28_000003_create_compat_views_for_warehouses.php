<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create a read-only view `warehouses` from `units` to support legacy code
        try {
            DB::unprepared('DROP VIEW IF EXISTS warehouses');
            DB::unprepared(<<<SQL
                CREATE VIEW warehouses AS
                SELECT
                    id,
                    code,
                    name,
                    location,
                    address,
                    pic_name,
                    pic_phone,
                    is_active,
                    created_at,
                    updated_at
                FROM units;
            SQL
            );
        } catch (\Exception $e) {
            // ignore view creation errors
        }

        // Create a view `user_warehouses` mapping from `user_unit` pivot
        try {
            DB::unprepared('DROP VIEW IF EXISTS user_warehouses');
            DB::unprepared(<<<SQL
                CREATE VIEW user_warehouses AS
                SELECT
                    user_id,
                    unit_id AS warehouse_id,
                    created_at
                FROM user_unit;
            SQL
            );
        } catch (\Exception $e) {
            // ignore
        }
    }

    public function down(): void
    {
        try { DB::unprepared('DROP VIEW IF EXISTS user_warehouses'); } catch (\Exception $e) { }
        try { DB::unprepared('DROP VIEW IF EXISTS warehouses'); } catch (\Exception $e) { }
    }
};
