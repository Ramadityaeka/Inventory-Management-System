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
        Schema::table('approvals', function (Blueprint $table) {
            // Change rejection_reason from enum to string to handle longer values
            if (Schema::hasColumn('approvals', 'rejection_reason')) {
                DB::statement('ALTER TABLE approvals MODIFY rejection_reason VARCHAR(100) NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            // Revert back if needed
        });
    }
};
