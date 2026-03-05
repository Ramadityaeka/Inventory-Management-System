<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix: Update semua items.category_id berdasarkan prefix code item → code category
     *
     * Contoh: item code "1.01.03.14.001.000028"
     *   → category code = "1.01.03.14.001" (hapus .XXXXXX terakhir)
     *   → cari category_id dari tabel categories where code = "1.01.03.14.001"
     *   → update items.category_id
     */
    public function up(): void
    {
        $items = DB::table('items')->get(['id', 'code', 'category_id']);
        $categories = DB::table('categories')->pluck('id', 'code'); // code => id

        $fixed = 0;
        $skipped = 0;
        $notFound = 0;

        foreach ($items as $item) {
            // Ambil category code dari item code
            // Item code format: X.XX.XX.XX.XXX.XXXXXX → category = X.XX.XX.XX.XXX
            $parts = explode('.', $item->code);

            // Coba dari prefix terpanjang ke terpendek sampai ketemu
            $newCategoryId = null;
            $matchedCode = null;

            // Hapus bagian terakhir (nomor item 6 digit) untuk mendapatkan category code
            // Misal: 1.01.03.14.001.000028 → coba 1.01.03.14.001 dulu
            for ($len = count($parts) - 1; $len >= 1; $len--) {
                $prefix = implode('.', array_slice($parts, 0, $len));
                if (isset($categories[$prefix])) {
                    $newCategoryId = $categories[$prefix];
                    $matchedCode = $prefix;
                    break;
                }
            }

            if ($newCategoryId === null) {
                echo "  ✗ NOT FOUND: Item #{$item->id} code={$item->code}\n";
                $notFound++;
                continue;
            }

            if ($item->category_id == $newCategoryId) {
                $skipped++;
                continue;
            }

            DB::table('items')->where('id', $item->id)->update([
                'category_id' => $newCategoryId,
                'updated_at'  => now(),
            ]);

            echo "  ✓ Item #{$item->id}: cat_id {$item->category_id} → {$newCategoryId} (matched: {$matchedCode})\n";
            $fixed++;
        }

        echo "\n=== Hasil ===\n";
        echo "Fixed: {$fixed} | Skipped (sudah benar): {$skipped} | Not found: {$notFound}\n";
    }

    public function down(): void
    {
        echo "WARNING: Rollback tidak tersedia untuk migration ini.\n";
    }
};
