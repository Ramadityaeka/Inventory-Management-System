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
            if (!Schema::hasColumn('submissions', 'unit')) {
                $table->string('unit')->default('pcs')->after('quantity');
            }
            if (!Schema::hasColumn('submissions', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->nullable()->after('unit');
            }
            if (!Schema::hasColumn('submissions', 'total_price')) {
                $table->decimal('total_price', 15, 2)->nullable()->after('unit_price');
            }
            if (!Schema::hasColumn('submissions', 'invoice_photo')) {
                $table->string('invoice_photo')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $columns = ['unit_price', 'total_price', 'invoice_photo'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('submissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
