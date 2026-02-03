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

        // Add new structure columns only if they don't exist
        Schema::table('categories', function (Blueprint $table) {
            // Add code column (hierarchical code like 1.01.03.01.001)
            if (!Schema::hasColumn('categories', 'code')) {
                $table->string('code', 50)->after('id')->nullable();
            }

            // Add parent_id for hierarchical structure
            if (!Schema::hasColumn('categories', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('description');
            }
        });

        // Add indexes and foreign keys separately guarded
        if (!\Schema::hasColumn('categories', 'code') || !\DB::getSchemaBuilder()->hasColumn('categories', 'code')) {
            // ensure index only if column exists
            try {
                Schema::table('categories', function (Blueprint $table) {
                    if (!\DB::getSchemaBuilder()->hasColumn('categories', 'code')) return;
                    $table->unique('code', 'idx_category_code');
                });
            } catch (\Exception $e) {
                // ignore if index exists or cannot be created
            }
        } else {
            try {
                Schema::table('categories', function (Blueprint $table) {
                    if (Schema::hasColumn('categories', 'code')) {
                        $table->unique('code', 'idx_category_code');
                    }
                });
            } catch (\Exception $e) {
                // ignore
            }
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'parent_id')) return;
                $table->index('parent_id', 'idx_category_parent');
                $table->foreign('parent_id', 'fk_category_parent')
                      ->references('id')->on('categories')
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // ignore if index or foreign key already exists
        }
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
