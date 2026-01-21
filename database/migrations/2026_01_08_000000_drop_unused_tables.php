-- ====================================================
-- CREATE MIGRATION: Drop Unused Tables
-- Tanggal: 8 Januari 2026
-- ====================================================
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tabel yang tidak diperlukan
        Schema::dropIfExists('export_logs');
        Schema::dropIfExists('internal_movements');
        Schema::dropIfExists('backup_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('system_settings');
    }

    public function down(): void
    {
        // Tidak perlu rollback karena kita sengaja menghapus
    }
};
