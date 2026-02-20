<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hapus semua items yang tidak memiliki stok (orphaned/duplikat sisa sebelum clear).
     * Items tanpa stok = tidak berguna dan menyebabkan duplikat di sistem.
     */
    public function up(): void
    {
        // Cari semua item_id yang PUNYA stok (valid)
        $itemIdsWithStock = DB::table('stocks')
            ->pluck('item_id')
            ->unique()
            ->toArray();

        // Cari semua items yang TIDAK punya stok
        $orphanedItems = DB::table('items')
            ->whereNotIn('id', $itemIdsWithStock)
            ->pluck('id');

        if ($orphanedItems->isEmpty()) {
            echo "Tidak ada item orphaned yang perlu dihapus.\n";
            return;
        }

        $count = $orphanedItems->count();

        // Hapus item_units dulu (child)
        DB::table('item_units')
            ->whereIn('item_id', $orphanedItems)
            ->delete();

        // Hapus submissions yang merujuk item orphaned (tidak ada stok = tidak valid)
        DB::table('submissions')
            ->whereIn('item_id', $orphanedItems)
            ->delete();

        // Hapus stock_movements yang merujuk item orphaned
        DB::table('stock_movements')
            ->whereIn('item_id', $orphanedItems)
            ->delete();

        // Hapus items orphaned
        DB::table('items')
            ->whereIn('id', $orphanedItems)
            ->delete();

        echo "✓ Berhasil menghapus {$count} item orphaned (tanpa stok) beserta data terkaitnya.\n";
    }

    public function down(): void
    {
        // Tidak bisa di-rollback — data yang sudah dihapus tidak bisa dikembalikan
        echo "PERINGATAN: Data orphaned yang sudah dihapus tidak dapat dikembalikan.\n";
    }
};
