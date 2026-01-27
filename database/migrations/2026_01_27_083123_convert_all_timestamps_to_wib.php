<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * PENTING: Migration ini akan mengkonversi SEMUA timestamp dari UTC ke WIB (+7 jam)
     * Pastikan sudah backup database sebelum menjalankan!
     */
    public function up(): void
    {
        // Check if database supports CONVERT_TZ function
        $supportsConvertTz = true;
        
        try {
            DB::select("SELECT CONVERT_TZ(NOW(), '+00:00', '+07:00') as test");
        } catch (\Exception $e) {
            $supportsConvertTz = false;
            echo "WARNING: CONVERT_TZ tidak didukung. Menggunakan DATE_ADD sebagai alternatif.\n";
        }
        
        $tables = [
            'users' => ['created_at', 'updated_at'],
            'warehouses' => ['created_at', 'updated_at'],
            'categories' => ['created_at', 'updated_at'],
            'suppliers' => ['created_at', 'updated_at'],
            'items' => ['created_at', 'updated_at', 'deactivated_at'],
            'stocks' => ['created_at', 'updated_at', 'last_updated'],
            'stock_movements' => ['created_at', 'updated_at'],
            'submissions' => ['created_at', 'updated_at', 'submitted_at'],
            'submission_photos' => ['created_at', 'updated_at'],
            'approvals' => ['created_at', 'updated_at'],
            'stock_requests' => ['created_at', 'updated_at', 'approved_at', 'rejected_at', 'fulfilled_at'],
            'stock_alerts' => ['created_at', 'updated_at', 'resolved_at'],
            'notifications' => ['created_at', 'updated_at', 'read_at'],
        ];
        
        foreach ($tables as $table => $columns) {
            if (!$this->tableExists($table)) {
                echo "Skipping table: {$table} (tidak ditemukan)\n";
                continue;
            }
            
            echo "Converting timestamps in table: {$table}\n";
            
            foreach ($columns as $column) {
                if (!$this->columnExists($table, $column)) {
                    continue;
                }
                
                if ($supportsConvertTz) {
                    // Menggunakan CONVERT_TZ (lebih akurat)
                    DB::statement("
                        UPDATE {$table} 
                        SET {$column} = CONVERT_TZ({$column}, '+00:00', '+07:00')
                        WHERE {$column} IS NOT NULL
                    ");
                } else {
                    // Alternatif: tambahkan 7 jam secara manual
                    DB::statement("
                        UPDATE {$table} 
                        SET {$column} = DATE_ADD({$column}, INTERVAL 7 HOUR)
                        WHERE {$column} IS NOT NULL
                    ");
                }
                
                echo "  - Converted column: {$column}\n";
            }
        }
        
        echo "\n✓ Konversi timezone selesai!\n";
        echo "Semua timestamp telah dikonversi dari UTC ke WIB (+07:00)\n\n";
    }

    /**
     * Reverse the migrations.
     * 
     * PERINGATAN: Ini akan mengembalikan semua timestamp ke UTC (-7 jam dari WIB)
     */
    public function down(): void
    {
        echo "ROLLBACK: Mengembalikan timezone dari WIB ke UTC...\n\n";
        
        $supportsConvertTz = true;
        
        try {
            DB::select("SELECT CONVERT_TZ(NOW(), '+07:00', '+00:00') as test");
        } catch (\Exception $e) {
            $supportsConvertTz = false;
        }
        
        $tables = [
            'users' => ['created_at', 'updated_at'],
            'warehouses' => ['created_at', 'updated_at'],
            'categories' => ['created_at', 'updated_at'],
            'suppliers' => ['created_at', 'updated_at'],
            'items' => ['created_at', 'updated_at', 'deactivated_at'],
            'stocks' => ['created_at', 'updated_at', 'last_updated'],
            'stock_movements' => ['created_at', 'updated_at'],
            'submissions' => ['created_at', 'updated_at', 'submitted_at'],
            'submission_photos' => ['created_at', 'updated_at'],
            'approvals' => ['created_at', 'updated_at'],
            'stock_requests' => ['created_at', 'updated_at', 'approved_at', 'rejected_at', 'fulfilled_at'],
            'stock_alerts' => ['created_at', 'updated_at', 'resolved_at'],
            'notifications' => ['created_at', 'updated_at', 'read_at'],
        ];
        
        foreach ($tables as $table => $columns) {
            if (!$this->tableExists($table)) {
                continue;
            }
            
            foreach ($columns as $column) {
                if (!$this->columnExists($table, $column)) {
                    continue;
                }
                
                if ($supportsConvertTz) {
                    DB::statement("
                        UPDATE {$table} 
                        SET {$column} = CONVERT_TZ({$column}, '+07:00', '+00:00')
                        WHERE {$column} IS NOT NULL
                    ");
                } else {
                    DB::statement("
                        UPDATE {$table} 
                        SET {$column} = DATE_SUB({$column}, INTERVAL 7 HOUR)
                        WHERE {$column} IS NOT NULL
                    ");
                }
            }
        }
        
        echo "✓ Rollback selesai. Timezone dikembalikan ke UTC.\n";
    }
    
    /**
     * Check if table exists
     */
    private function tableExists(string $table): bool
    {
        return DB::getSchemaBuilder()->hasTable($table);
    }
    
    /**
     * Check if column exists in table
     */
    private function columnExists(string $table, string $column): bool
    {
        return DB::getSchemaBuilder()->hasColumn($table, $column);
    }
};
