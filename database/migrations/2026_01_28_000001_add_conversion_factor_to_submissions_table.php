<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'conversion_factor')) {
                $table->unsignedInteger('conversion_factor')->default(1)->after('unit')->comment('How many base units per selected unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (Schema::hasColumn('submissions', 'conversion_factor')) {
                $table->dropColumn('conversion_factor');
            }
        });
    }
};
