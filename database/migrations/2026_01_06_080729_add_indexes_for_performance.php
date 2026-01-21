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
        // Add indexes for stocks table - check if not exists
        Schema::table('stocks', function (Blueprint $table) {
            if (!$this->indexExists('stocks', 'idx_stocks_warehouse_id')) {
                $table->index('warehouse_id', 'idx_stocks_warehouse_id');
            }
            if (!$this->indexExists('stocks', 'idx_stocks_item_id')) {
                $table->index('item_id', 'idx_stocks_item_id');
            }
            if (!$this->indexExists('stocks', 'idx_stocks_warehouse_item')) {
                $table->index(['warehouse_id', 'item_id'], 'idx_stocks_warehouse_item');
            }
        });

        // Add indexes for submissions table
        Schema::table('submissions', function (Blueprint $table) {
            if (!$this->indexExists('submissions', 'idx_submissions_staff_id')) {
                $table->index('staff_id', 'idx_submissions_staff_id');
            }
            if (!$this->indexExists('submissions', 'idx_submissions_warehouse_id')) {
                $table->index('warehouse_id', 'idx_submissions_warehouse_id');
            }
            if (!$this->indexExists('submissions', 'idx_submissions_status')) {
                $table->index('status', 'idx_submissions_status');
            }
            if (!$this->indexExists('submissions', 'idx_submissions_is_draft')) {
                $table->index('is_draft', 'idx_submissions_is_draft');
            }
            if (!$this->indexExists('submissions', 'idx_submissions_warehouse_status')) {
                $table->index(['warehouse_id', 'status'], 'idx_submissions_warehouse_status');
            }
            if (!$this->indexExists('submissions', 'idx_submissions_submitted_at')) {
                $table->index('submitted_at', 'idx_submissions_submitted_at');
            }
        });

        // Add indexes for transfers table
        Schema::table('transfers', function (Blueprint $table) {
            if (!$this->indexExists('transfers', 'idx_transfers_from_warehouse')) {
                $table->index('from_warehouse_id', 'idx_transfers_from_warehouse');
            }
            if (!$this->indexExists('transfers', 'idx_transfers_to_warehouse')) {
                $table->index('to_warehouse_id', 'idx_transfers_to_warehouse');
            }
            if (!$this->indexExists('transfers', 'idx_transfers_status')) {
                $table->index('status', 'idx_transfers_status');
            }
            if (!$this->indexExists('transfers', 'idx_transfers_requested_by')) {
                $table->index('requested_by', 'idx_transfers_requested_by');
            }
            if (!$this->indexExists('transfers', 'idx_transfers_requested_at')) {
                $table->index('requested_at', 'idx_transfers_requested_at');
            }
        });

        // Add indexes for stock_movements table
        Schema::table('stock_movements', function (Blueprint $table) {
            if (!$this->indexExists('stock_movements', 'idx_stock_movements_warehouse_id')) {
                $table->index('warehouse_id', 'idx_stock_movements_warehouse_id');
            }
            if (!$this->indexExists('stock_movements', 'idx_stock_movements_item_id')) {
                $table->index('item_id', 'idx_stock_movements_item_id');
            }
            if (!$this->indexExists('stock_movements', 'idx_stock_movements_created_by')) {
                $table->index('created_by', 'idx_stock_movements_created_by');
            }
            if (!$this->indexExists('stock_movements', 'idx_stock_movements_created_at')) {
                $table->index('created_at', 'idx_stock_movements_created_at');
            }
            if (!$this->indexExists('stock_movements', 'idx_stock_movements_warehouse_date')) {
                $table->index(['warehouse_id', 'created_at'], 'idx_stock_movements_warehouse_date');
            }
        });

        // Add indexes for notifications table
        Schema::table('notifications', function (Blueprint $table) {
            if (!$this->indexExists('notifications', 'idx_notifications_user_id')) {
                $table->index('user_id', 'idx_notifications_user_id');
            }
            if (!$this->indexExists('notifications', 'idx_notifications_is_read')) {
                $table->index('is_read', 'idx_notifications_is_read');
            }
            if (!$this->indexExists('notifications', 'idx_notifications_user_read')) {
                $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
            }
            if (!$this->indexExists('notifications', 'idx_notifications_created_at')) {
                $table->index('created_at', 'idx_notifications_created_at');
            }
        });

        // Add indexes for items table
        Schema::table('items', function (Blueprint $table) {
            if (!$this->indexExists('items', 'idx_items_category_id')) {
                $table->index('category_id', 'idx_items_category_id');
            }
            if (!$this->indexExists('items', 'idx_items_supplier_id')) {
                $table->index('supplier_id', 'idx_items_supplier_id');
            }
            if (!$this->indexExists('items', 'idx_items_min_threshold')) {
                $table->index('min_threshold', 'idx_items_min_threshold');
            }
        });
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        
        // SQLite-specific index check
        if ($driver === 'sqlite') {
            $query = "SELECT COUNT(*) as count FROM sqlite_master 
                      WHERE type = 'index' AND name = ?";
            $result = $connection->selectOne($query, [$index]);
            return $result->count > 0;
        }
        
        // MySQL-specific index check
        $databaseName = $connection->getDatabaseName();
        $query = "SELECT COUNT(*) as count FROM information_schema.statistics 
                  WHERE table_schema = ? AND table_name = ? AND index_name = ?";
        
        $result = $connection->selectOne($query, [$databaseName, $table, $index]);
        
        return $result->count > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_category_id');
            $table->dropIndex('idx_items_supplier_id');
            $table->dropIndex('idx_items_min_threshold');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_id');
            $table->dropIndex('idx_notifications_is_read');
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_created_at');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_stock_movements_warehouse_id');
            $table->dropIndex('idx_stock_movements_item_id');
            $table->dropIndex('idx_stock_movements_created_by');
            $table->dropIndex('idx_stock_movements_created_at');
            $table->dropIndex('idx_stock_movements_warehouse_date');
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->dropIndex('idx_transfers_from_warehouse');
            $table->dropIndex('idx_transfers_to_warehouse');
            $table->dropIndex('idx_transfers_status');
            $table->dropIndex('idx_transfers_requested_by');
            $table->dropIndex('idx_transfers_requested_at');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('idx_submissions_staff_id');
            $table->dropIndex('idx_submissions_warehouse_id');
            $table->dropIndex('idx_submissions_status');
            $table->dropIndex('idx_submissions_is_draft');
            $table->dropIndex('idx_submissions_warehouse_status');
            $table->dropIndex('idx_submissions_submitted_at');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('idx_stocks_warehouse_id');
            $table->dropIndex('idx_stocks_item_id');
            $table->dropIndex('idx_stocks_warehouse_item');
        });
    }
};
