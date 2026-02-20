<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Mengosongkan semua data inventory
     * 
     * PERINGATAN: Migration ini akan menghapus SEMUA data dari sistem inventory.
     * Data yang akan dihapus:
     * - Stock Movements (Riwayat pergerakan stok)
     * - Stock Alerts (Peringatan stok)
     * - Stocks (Data stok)
     * - Approvals (Persetujuan)
     * - Submissions (Pengadaan barang)
     * - Stock Requests (Permintaan stok)
     * - Item Units (Satuan barang)
     * - Notifications (Notifikasi terkait inventory)
     * 
     * Gunakan dengan HATI-HATI! Data yang dihapus tidak dapat dikembalikan.
     */
    public function up(): void
    {
        // Disable foreign key checks untuk menghindari constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            echo "Menghapus semua data inventory...\n\n";

            // 1. Hapus Stock Movements (child table dari stocks, submissions, stock_requests)
            $count = DB::table('stock_movements')->count();
            DB::table('stock_movements')->truncate();
            echo "✓ Berhasil menghapus {$count} data dari tabel stock_movements\n";

            // 2. Hapus Stock Alerts
            if (Schema::hasTable('stock_alerts')) {
                $count = DB::table('stock_alerts')->count();
                DB::table('stock_alerts')->truncate();
                echo "✓ Berhasil menghapus {$count} data dari tabel stock_alerts\n";
            }

            // 3. Hapus Stocks
            $count = DB::table('stocks')->count();
            DB::table('stocks')->truncate();
            echo "✓ Berhasil menghapus {$count} data dari tabel stocks\n";

            // 4. Hapus Approvals
            if (Schema::hasTable('approvals')) {
                $count = DB::table('approvals')->count();
                DB::table('approvals')->truncate();
                echo "✓ Berhasil menghapus {$count} data dari tabel approvals\n";
            }

            // 5. Hapus Submissions
            $count = DB::table('submissions')->count();
            DB::table('submissions')->truncate();
            echo "✓ Berhasil menghapus {$count} data dari tabel submissions\n";

            // 6. Hapus Stock Requests
            if (Schema::hasTable('stock_requests')) {
                $count = DB::table('stock_requests')->count();
                DB::table('stock_requests')->truncate();
                echo "✓ Berhasil menghapus {$count} data dari tabel stock_requests\n";
            }

            // 7. Hapus Item Units
            $count = DB::table('item_units')->count();
            DB::table('item_units')->truncate();
            echo "✓ Berhasil menghapus {$count} data dari tabel item_units\n";


            // 9. Hapus Notifications yang terkait dengan inventory
            if (Schema::hasTable('notifications')) {
                $count = DB::table('notifications')
                    ->whereIn('type', [
                        'App\\Notifications\\LowStockAlert',
                        'App\\Notifications\\StockRequestNotification',
                        'App\\Notifications\\SubmissionNotification',
                    ])
                    ->delete();
                echo "✓ Berhasil menghapus {$count} notifikasi terkait inventory\n";
            }

            echo "\n✓✓✓ SELESAI! Semua data inventory telah dihapus.\n";
            
        } catch (\Exception $e) {
            echo "\n✗ ERROR: " . $e->getMessage() . "\n";
            throw $e;
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     * 
     * CATATAN: Data yang sudah dihapus tidak dapat dikembalikan.
     * Method down() ini hanya untuk memenuhi struktur migration Laravel.
     */
    public function down(): void
    {
        echo "\n";
        echo "============================================\n";
        echo "PERINGATAN: Data yang sudah dihapus tidak dapat dikembalikan!\n";
        echo "============================================\n";
        echo "\n";
        echo "Jika Anda ingin mengembalikan data, Anda harus:\n";
        echo "1. Restore dari backup database\n";
        echo "2. Atau jalankan migration seeder untuk mengisi data awal\n";
        echo "\n";
    }
};
