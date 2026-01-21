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
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'item_name')) {
                $table->string('item_name')->nullable()->after('item_id');
            }
            
            // Make item_id nullable so user can enter item name manually
            $table->unsignedBigInteger('item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (Schema::hasColumn('submissions', 'item_name')) {
                $table->dropColumn('item_name');
            }
        });
    }
};
