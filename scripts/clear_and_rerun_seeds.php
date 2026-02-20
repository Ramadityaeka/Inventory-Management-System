<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting clear_and_rerun_seeds script\n";
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
$tables = ['stock_movements','stock_alerts','stocks','approvals','submissions','stock_requests','item_units','items'];
foreach ($tables as $t) {
    if (Schema::hasTable($t) ?? true) {
        try {
            DB::table($t)->truncate();
            echo "Truncated {$t}\n";
        } catch (Exception $e) {
            echo "Failed to truncate {$t}: " . $e->getMessage() . "\n";
        }
    }
}
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

// Remove migration records so seed migrations can run again
$toDelete = [
    '2026_02_19_213104_obat_cair_insert_klinik',
    '2026_02_19_213504_obat_padat_insert_klinik',
    '2026_02_19_214522_obat_gas_insert_klinik',
    '2026_02_19_214555_obat_lainnya_insert_klinik',
    '2026_02_19_214919_obat_gel_salep_insert_klinik',
    '2026_02_19_224212_cleanup_orphaned_duplicate_items',
    '2026_02_19_211107_clear_all_inventory_data'
];
foreach ($toDelete as $m) {
    DB::table('migrations')->where('migration', $m)->delete();
    echo "Removed migration record: {$m}\n";
}

echo "Done. Now run: php artisan migrate --force to re-run seed migrations.\n";