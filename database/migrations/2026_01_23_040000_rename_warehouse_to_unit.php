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
        // Rename warehouses table to units
        Schema::rename('warehouses', 'units');
        
        // Update foreign key columns in related tables
        $tables = [
            'stocks' => 'warehouse_id',
            'submissions' => 'warehouse_id',
            'user_warehouse' => 'warehouse_id',
            'stock_requests' => 'warehouse_id',
            'stock_movements' => 'warehouse_id',
        ];

        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->renameColumn($column, 'unit_id');
                });
            }
        }

        // Handle transfers table with multiple warehouse columns
        if (Schema::hasTable('transfers')) {
            Schema::table('transfers', function (Blueprint $table) {
                if (Schema::hasColumn('transfers', 'from_warehouse_id')) {
                    $table->renameColumn('from_warehouse_id', 'from_unit_id');
                }
                if (Schema::hasColumn('transfers', 'to_warehouse_id')) {
                    $table->renameColumn('to_unit_id', 'to_unit_id');
                }
            });
        }

        // Rename user_warehouse pivot table to user_unit
        if (Schema::hasTable('user_warehouse')) {
            Schema::rename('user_warehouse', 'user_unit');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse transfers table columns
        if (Schema::hasTable('transfers')) {
            Schema::table('transfers', function (Blueprint $table) {
                if (Schema::hasColumn('transfers', 'from_unit_id')) {
                    $table->renameColumn('from_unit_id', 'from_warehouse_id');
                }
                if (Schema::hasColumn('transfers', 'to_unit_id')) {
                    $table->renameColumn('to_unit_id', 'to_warehouse_id');
                }
            });
        }

        // Reverse column renames in related tables
        $tables = [
            'stocks' => 'unit_id',
            'submissions' => 'unit_id',
            'user_unit' => 'unit_id',
            'stock_requests' => 'unit_id',
            'stock_movements' => 'unit_id',
        ];

        foreach ($tables as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->renameColumn($column, 'warehouse_id');
                });
            }
        }

        // Rename user_unit back to user_warehouse
        if (Schema::hasTable('user_unit')) {
            Schema::rename('user_unit', 'user_warehouse');
        }

        // Rename units table back to warehouses
        Schema::rename('units', 'warehouses');
    }
};
