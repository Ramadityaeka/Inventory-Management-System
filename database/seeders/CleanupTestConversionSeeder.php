<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanupTestConversionSeeder extends Seeder
{
    public function run(): void
    {
        $item = DB::table('items')->where('code', 'TEST-PULPEN')->first();
        if (! $item) {
            echo "No TEST-PULPEN item found. Nothing to clean.\n";
            return;
        }

        // Drop triggers that reference old column names to avoid errors during manual stock adjustments
        $triggersToDrop = ['after_insert_approvals', 'after_stock_update', 'after_update_stocks_alert'];
        foreach ($triggersToDrop as $t) {
            try { DB::unprepared("DROP TRIGGER IF EXISTS {$t}"); } catch (\Exception $e) { /* ignore */ }
        }

        DB::beginTransaction();
        try {
            $submissionIds = DB::table('submissions')->where('item_id', $item->id)->pluck('id')->toArray();

            // For each submission, revert stock movements (subtract movement quantities)
            $totalAdjusted = 0;
            foreach ($submissionIds as $sid) {
                $movements = DB::table('stock_movements')->where('reference_type', 'submission')->where('reference_id', $sid)->get();
                foreach ($movements as $m) {
                    // Determine column name for warehouse/unit in stocks
                    $stockQuery = DB::table('stocks')->where('item_id', $item->id);
                    if (DB::getSchemaBuilder()->hasColumn('stocks', 'unit_id')) {
                        $stockQuery->where('unit_id', $m->unit_id ?? $m->warehouse_id ?? null);
                    } else {
                        $stockQuery->where('warehouse_id', $m->warehouse_id ?? $m->unit_id ?? null);
                    }

                    $stock = $stockQuery->first();
                    if ($stock) {
                        $newQty = max(0, $stock->quantity - $m->quantity);
                        DB::table('stocks')->where('id', $stock->id)->update(['quantity' => $newQty, 'last_updated' => now()]);
                        $totalAdjusted += $m->quantity;
                    }
                }

                // delete stock movements for this submission
                DB::table('stock_movements')->where('reference_type', 'submission')->where('reference_id', $sid)->delete();
                // delete approvals
                DB::table('approvals')->where('submission_id', $sid)->delete();
            }

            // delete submissions
            DB::table('submissions')->where('item_id', $item->id)->delete();

            // delete item units
            DB::table('item_units')->where('item_id', $item->id)->delete();

            // delete item
            DB::table('items')->where('id', $item->id)->delete();

            DB::commit();
            // Recreate standard approval trigger (uses unit_id and conversion_factor)
            try {
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
                // ignore recreation errors
            }

            // Recreate stocks alert triggers using NEW.unit_id mapped to stock_alerts.warehouse_id
            try {
                DB::unprepared('CREATE TRIGGER after_stock_update AFTER UPDATE ON stocks FOR EACH ROW BEGIN
                    IF NEW.quantity = 0 THEN
                        INSERT INTO stock_alerts (item_id, warehouse_id, alert_type, current_stock) VALUES (NEW.item_id, NEW.unit_id, \'out_of_stock\', NEW.quantity) ON DUPLICATE KEY UPDATE current_stock = NEW.quantity, resolved_at = NULL, created_at = NOW();
                    END IF;
                END');
            } catch (\Exception $e) { /* ignore */ }

            try {
                DB::unprepared('CREATE TRIGGER after_update_stocks_alert AFTER UPDATE ON stocks FOR EACH ROW BEGIN
                    IF NEW.quantity = 0 AND OLD.quantity != 0 THEN
                        INSERT INTO stock_alerts (item_id, warehouse_id, alert_type, current_stock, threshold, created_at, updated_at) VALUES (NEW.item_id, NEW.unit_id, "out_of_stock", 0, 0, NOW(), NOW());
                    END IF;
                END');
            } catch (\Exception $e) { /* ignore */ }

            echo "Cleaned TEST-PULPEN and related data. Adjusted stock by removing total movements: {$totalAdjusted}\n";
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Cleanup failed: " . $e->getMessage() . "\n";
        }
    }
}
