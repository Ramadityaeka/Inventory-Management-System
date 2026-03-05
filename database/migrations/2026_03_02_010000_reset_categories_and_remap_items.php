<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration: Reset tabel categories & remap items obat kembali ke 1.01.03.14
     *
     * 1. Remap items yang sudah diubah ke 1.01.14.01 kembali ke 1.01.03.14
     * 2. Truncate categories
     * 3. Hapus record migration kategori_update agar bisa di-run ulang
     */
    public function up(): void
    {
        // ================================================================
        // STEP 1: Remap items dari 1.01.14.01.xxx KEMBALI ke 1.01.03.14.xxx
        // (karena migration sebelumnya sudah mengubahnya)
        // ================================================================
        $reverseMapping = [
            '1.01.14.01.001' => '1.01.03.14.001',
            '1.01.14.01.002' => '1.01.03.14.002',
            '1.01.14.01.003' => '1.01.03.14.003',
            '1.01.14.01.004' => '1.01.03.14.004',
            '1.01.14.01.005' => '1.01.03.14.005',
            '1.01.14.01.006' => '1.01.03.14.006',
            '1.01.14.01.007' => '1.01.03.14.007',
            '1.01.14.01.999' => '1.01.03.14.999',
        ];

        $remappedItems = DB::table('items')
            ->where('code', 'LIKE', '1.01.14.01.%')
            ->get(['id', 'code']);

        echo "=== STEP 1: Remap {$remappedItems->count()} items kembali ke 1.01.03.14 ===\n";

        foreach ($remappedItems as $item) {
            foreach ($reverseMapping as $newPrefix => $oldPrefix) {
                if (str_starts_with($item->code, $newPrefix . '.')) {
                    $suffix = substr($item->code, strlen($newPrefix));
                    $restoredCode = $oldPrefix . $suffix;

                    DB::table('items')->where('id', $item->id)->update([
                        'code' => $restoredCode,
                        'updated_at' => now(),
                    ]);

                    echo "  ✓ Item #{$item->id}: {$item->code} → {$restoredCode}\n";
                    break;
                }
            }
        }

        // ================================================================
        // STEP 2: Truncate categories
        // ================================================================
        echo "\n=== STEP 2: Truncate categories ===\n";
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        echo "  ✓ Tabel categories di-truncate\n";

        // ================================================================
        // STEP 3: Hapus record migration kategori_update agar bisa re-run
        // ================================================================
        DB::table('migrations')
            ->where('migration', '2026_02_20_145600_kategori_update')
            ->delete();

        // Hapus juga migration perabot kantor jika ada
        DB::table('migrations')
            ->where('migration', '2026_02_20_000001_add_perabot_kantor_categories')
            ->delete();

        echo "  ✓ Migration kategori_update di-reset, siap dijalankan ulang\n";
        echo "\n=== SELESAI! Jalankan 'php artisan migrate' lagi untuk seed categories ===\n";
    }

    public function down(): void
    {
        echo "WARNING: Rollback tidak mengembalikan data categories lama.\n";
    }
};
