<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->string('received_proof_image')->nullable()->after('approved_at')->comment('Bukti penerimaan barang');
            $table->timestamp('received_at')->nullable()->after('received_proof_image')->comment('Waktu barang diterima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn(['received_proof_image', 'received_at']);
        });
    }
};
