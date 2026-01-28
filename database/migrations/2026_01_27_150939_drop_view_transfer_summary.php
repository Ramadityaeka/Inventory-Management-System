<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop view_transfer_summary yang tidak terpakai karena tabel transfers sudah dihapus
     */
    public function up(): void
    {
        // Drop view if exists
        DB::statement('DROP VIEW IF EXISTS view_transfer_summary');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu recreate view karena tabel transfers tidak ada
        // View ini sudah tidak digunakan dalam aplikasi
    }
};
