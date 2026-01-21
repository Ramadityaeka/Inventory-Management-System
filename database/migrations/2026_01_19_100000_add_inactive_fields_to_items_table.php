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
        Schema::table('items', function (Blueprint $table) {
            // Tipe deactivation: discontinued, wrong_input, seasonal
            $table->enum('inactive_reason', ['discontinued', 'wrong_input', 'seasonal'])->nullable()->after('is_active');
            
            // Catatan tambahan untuk deactivation
            $table->text('inactive_notes')->nullable()->after('inactive_reason');
            
            // Timestamp dan user yang melakukan deactivation
            $table->timestamp('deactivated_at')->nullable()->after('inactive_notes');
            $table->unsignedBigInteger('deactivated_by')->nullable()->after('deactivated_at');
            
            // ID item pengganti (untuk kasus wrong_input)
            $table->unsignedBigInteger('replaced_by_item_id')->nullable()->after('deactivated_by');
            
            // Foreign keys
            $table->foreign('deactivated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('replaced_by_item_id')->references('id')->on('items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['deactivated_by']);
            $table->dropForeign(['replaced_by_item_id']);
            
            $table->dropColumn([
                'inactive_reason',
                'inactive_notes',
                'deactivated_at',
                'deactivated_by',
                'replaced_by_item_id'
            ]);
        });
    }
};
