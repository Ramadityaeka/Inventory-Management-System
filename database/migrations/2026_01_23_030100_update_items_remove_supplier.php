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
            // Make supplier_id nullable if not already
            if (Schema::hasColumn('items', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->change();
            }
            
            // Update code column to varchar(50)
            $table->string('code', 50)->change();
            
            // Add unique index for code if not exists
            if (!Schema::hasIndex('items', 'idx_item_code')) {
                $table->unique('code', 'idx_item_code');
            }
            
            // Add index for category_id if not exists
            if (!Schema::hasIndex('items', 'idx_item_category')) {
                $table->index('category_id', 'idx_item_category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Revert changes if needed
        });
    }
};
