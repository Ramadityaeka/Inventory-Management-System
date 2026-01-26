<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old columns if exist
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'code_prefix')) {
                $table->dropColumn('code_prefix');
            }
            if (Schema::hasColumn('categories', 'prefix')) {
                $table->dropColumn('prefix');
            }
        });

        // Add new structure columns
        Schema::table('categories', function (Blueprint $table) {
            // Add code column (hierarchical code like 1.01.03.01.001)
            $table->string('code', 50)->after('id')->nullable();
            
            // Add parent_id for hierarchical structure
            $table->unsignedBigInteger('parent_id')->nullable()->after('description');
            
            // Add unique index for code
            $table->unique('code', 'idx_category_code');
            
            // Add index for parent_id
            $table->index('parent_id', 'idx_category_parent');
            
            // Add foreign key constraint
            $table->foreign('parent_id', 'fk_category_parent')
                  ->references('id')->on('categories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign('fk_category_parent');
            $table->dropIndex('idx_category_parent');
            $table->dropIndex('idx_category_code');
            $table->dropColumn(['code', 'parent_id']);
            
            // Restore old columns
            $table->string('code_prefix', 5)->nullable()->after('name');
            $table->string('prefix', 10)->nullable()->after('code_prefix');
        });
    }
};
