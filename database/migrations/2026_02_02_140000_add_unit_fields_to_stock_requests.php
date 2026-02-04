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
            $table->string('unit_name', 50)->nullable()->after('quantity');
            $table->integer('conversion_factor')->default(1)->after('unit_name');
            $table->integer('base_quantity')->nullable()->after('conversion_factor')
                  ->comment('Quantity in base unit (quantity * conversion_factor)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn(['unit_name', 'conversion_factor', 'base_quantity']);
        });
    }
};
