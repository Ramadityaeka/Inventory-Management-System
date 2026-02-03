<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestConversionSeeder extends Seeder
{
    public function run(): void
    {
        // Find some existing ids to reuse
        $categoryId = DB::table('categories')->value('id') ?? 1;
        $warehouseId = null;
        if (\Schema::hasTable('warehouses')) {
            $warehouseId = DB::table('warehouses')->value('id');
        } elseif (\Schema::hasTable('units')) {
            $warehouseId = DB::table('units')->value('id');
        }
        $supplierId = \Schema::hasTable('suppliers') ? DB::table('suppliers')->value('id') : null;
        $staffId = \Schema::hasTable('users') ? DB::table('users')->value('id') : 1;
        $adminId = $staffId;

        // Create or reuse test item
        $existingItem = DB::table('items')->where('code', 'TEST-PULPEN')->first();
        if ($existingItem) {
            $itemId = $existingItem->id;
        } else {
            $itemId = DB::table('items')->insertGetId([
                'code' => 'TEST-PULPEN',
                'name' => 'Pulpen Test',
                'category_id' => $categoryId,
                'unit' => 'Pcs',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert or ensure item unit Box=12
        if (!DB::table('item_units')->where('item_id', $itemId)->where('name', 'Box')->exists()) {
            DB::table('item_units')->insert([
                'item_id' => $itemId,
                'name' => 'Box',
                'conversion_factor' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insert submission: 5 Box (use unit_id column name)
        $submissionData = [
            'item_id' => $itemId,
            'item_name' => 'Pulpen Test',
            'quantity' => 5,
            'unit' => 'Box',
            'conversion_factor' => 12,
            'unit_price' => null,
            'total_price' => null,
            'supplier_id' => $supplierId,
            'unit_id' => $warehouseId,
            'staff_id' => $staffId,
            'nota_number' => 'TEST-INV',
            'receive_date' => now()->toDateString(),
            'notes' => 'Seeder test',
            'invoice_photo' => null,
            'status' => 'pending',
            'is_draft' => false,
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $submissionId = DB::table('submissions')->insertGetId($submissionData);

        echo "Created item_id={$itemId}, submission_id={$submissionId}\n";

        // Recreate trigger that uses unit_id (in case trigger references warehouse_id)
        try {
            DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');
            DB::unprepared('CREATE TRIGGER after_insert_approvals AFTER INSERT ON approvals FOR EACH ROW BEGIN
                DECLARE item_id_var INT; DECLARE unit_id_var INT; DECLARE quantity_var INT; DECLARE conversion_factor_var INT DEFAULT 1; DECLARE total_qty INT;
                SELECT s.item_id, s.unit_id, s.quantity, COALESCE(s.conversion_factor,1) INTO item_id_var, unit_id_var, quantity_var, conversion_factor_var FROM submissions s WHERE s.id = NEW.submission_id;
                SET total_qty = quantity_var * conversion_factor_var;
                IF NEW.action = \'approved\' AND item_id_var IS NOT NULL THEN
                    INSERT INTO stocks (item_id, unit_id, quantity, last_updated) VALUES (item_id_var, unit_id_var, total_qty, NOW()) ON DUPLICATE KEY UPDATE quantity = quantity + total_qty, last_updated = NOW();
                    INSERT INTO stock_movements (item_id, unit_id, quantity, movement_type, reference_type, reference_id, created_by, created_at) VALUES (item_id_var, unit_id_var, total_qty, \'in\', \'submission\', NEW.submission_id, NEW.admin_id, NOW());
                END IF;
            END');
        } catch (\Exception $e) {
            echo "Failed to recreate trigger: " . $e->getMessage() . "\n";
        }

        // Insert approval to trigger stock update
        DB::table('approvals')->insert([
            'submission_id' => $submissionId,
            'admin_id' => $adminId,
            'action' => 'approved',
            'notes' => 'Seeder-approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Check stock
        $stock = DB::table('stocks')->where('item_id', $itemId)->where('unit_id', $warehouseId)->first();
        $qty = $stock->quantity ?? 0;
        echo "Stock after approval for item {$itemId} in warehouse {$warehouseId} = {$qty}\n";

        // Check stock movements
        $movement = DB::table('stock_movements')->where('reference_type', 'submission')->where('reference_id', $submissionId)->first();
        if ($movement) {
            echo "Stock movement created with quantity: {$movement->quantity}\n";
        } else {
            echo "No stock movement found for submission {$submissionId}\n";
        }
    }
}
