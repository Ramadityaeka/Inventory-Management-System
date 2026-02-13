<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menghapus semua data di tabel items beserta relasi-nya
     * untuk diganti dengan data obat-obatan
     * 
     * PERINGATAN: Migration ini akan menghapus SEMUA data items yang ada!
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Hapus semua data terkait items (urutan penting untuk menghindari constraint error)
        DB::table('item_units')->delete();
        DB::table('stock_alerts')->delete();
        DB::table('stock_movements')->delete();
        DB::table('stocks')->delete();
        DB::table('stock_requests')->delete();
        DB::table('transfers')->delete();
        DB::table('submissions')->delete();
        DB::table('items')->delete();

        // Reset auto increment items table
        DB::statement('ALTER TABLE items AUTO_INCREMENT = 1');

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada rollback untuk deletion
        // Restore hanya bisa dilakukan dari backup database
    }
};
