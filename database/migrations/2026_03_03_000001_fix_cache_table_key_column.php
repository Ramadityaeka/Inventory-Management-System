<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename 'key_col' back to 'key' in the cache and cache_locks tables.
     * MySQL reserved-word collision caused the column to be stored as 'key_col'.
     */
    public function up(): void
    {
        // Fix cache table (column is 'key_col', needs to be 'key')
        if (Schema::hasColumn('cache', 'key_col') && !Schema::hasColumn('cache', 'key')) {
            DB::statement('ALTER TABLE `cache` DROP PRIMARY KEY');
            DB::statement('ALTER TABLE `cache` CHANGE `key_col` `key` VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE `cache` ADD PRIMARY KEY (`key`)');
        }

        // Fix cache_locks table (column is 'cache_key', needs to be 'key')
        if (Schema::hasColumn('cache_locks', 'cache_key') && !Schema::hasColumn('cache_locks', 'key')) {
            DB::statement('ALTER TABLE `cache_locks` DROP PRIMARY KEY');
            DB::statement('ALTER TABLE `cache_locks` CHANGE `cache_key` `key` VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE `cache_locks` ADD PRIMARY KEY (`key`)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cache', 'key') && !Schema::hasColumn('cache', 'key_col')) {
            DB::statement('ALTER TABLE `cache` DROP PRIMARY KEY');
            DB::statement('ALTER TABLE `cache` CHANGE `key` `key_col` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasColumn('cache_locks', 'key') && !Schema::hasColumn('cache_locks', 'cache_key')) {
            DB::statement('ALTER TABLE `cache_locks` DROP PRIMARY KEY');
            DB::statement('ALTER TABLE `cache_locks` CHANGE `key` `cache_key` VARCHAR(255) NOT NULL');
        }
    }
};
