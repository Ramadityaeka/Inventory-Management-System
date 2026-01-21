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
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');
        
        DB::unprepared("
            CREATE TRIGGER after_insert_approvals
            AFTER INSERT ON approvals
            FOR EACH ROW
            BEGIN
                DECLARE v_item_id BIGINT;
                DECLARE v_warehouse_id BIGINT;
                DECLARE v_quantity INT;
                
                -- Get submission details
                SELECT item_id, warehouse_id, quantity 
                INTO v_item_id, v_warehouse_id, v_quantity
                FROM submissions
                WHERE id = NEW.submission_id;
                
                IF NEW.action = 'approved' THEN
                    -- Update submission status
                    UPDATE submissions 
                    SET status = 'approved'
                    WHERE id = NEW.submission_id;
                    
                    -- Barang Masuk: Tambah stok
                    INSERT INTO stocks (item_id, warehouse_id, quantity)
                    VALUES (v_item_id, v_warehouse_id, v_quantity)
                    ON DUPLICATE KEY UPDATE quantity = quantity + v_quantity;
                    
                    -- Record stock movement IN
                    INSERT INTO stock_movements 
                    (item_id, warehouse_id, movement_type, quantity, reference_type, reference_id, notes, created_by)
                    VALUES (
                        v_item_id, 
                        v_warehouse_id, 
                        'in', 
                        v_quantity, 
                        'submission', 
                        NEW.submission_id,
                        'Penerimaan barang approved', 
                        NEW.admin_id
                    );
                ELSE
                    -- Rejected
                    UPDATE submissions 
                    SET status = 'rejected'
                    WHERE id = NEW.submission_id;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_insert_approvals');
        
        // Restore the old trigger with transaction_type handling
        DB::unprepared("
            CREATE TRIGGER after_insert_approvals
            AFTER INSERT ON approvals
            FOR EACH ROW
            BEGIN
                DECLARE v_transaction_type VARCHAR(10);
                DECLARE v_item_id BIGINT;
                DECLARE v_warehouse_id BIGINT;
                DECLARE v_quantity INT;
                
                SELECT transaction_type, item_id, warehouse_id, quantity 
                INTO v_transaction_type, v_item_id, v_warehouse_id, v_quantity
                FROM submissions
                WHERE id = NEW.submission_id;
                
                IF NEW.action = 'approved' THEN
                    UPDATE submissions SET status = 'approved' WHERE id = NEW.submission_id;
                    
                    IF v_transaction_type = 'in' THEN
                        INSERT INTO stocks (item_id, warehouse_id, quantity)
                        VALUES (v_item_id, v_warehouse_id, v_quantity)
                        ON DUPLICATE KEY UPDATE quantity = quantity + v_quantity;
                        
                        INSERT INTO stock_movements 
                        (item_id, warehouse_id, movement_type, quantity, reference_type, reference_id, notes, created_by)
                        VALUES (v_item_id, v_warehouse_id, 'in', v_quantity, 'submission', NEW.submission_id, 'Penerimaan barang approved', NEW.admin_id);
                    ELSEIF v_transaction_type = 'out' THEN
                        UPDATE stocks SET quantity = GREATEST(0, quantity - v_quantity)
                        WHERE item_id = v_item_id AND warehouse_id = v_warehouse_id;
                        
                        INSERT INTO stock_movements 
                        (item_id, warehouse_id, movement_type, quantity, reference_type, reference_id, notes, created_by)
                        VALUES (v_item_id, v_warehouse_id, 'out', v_quantity, 'submission', NEW.submission_id, 'Pengeluaran barang approved', NEW.admin_id);
                    END IF;
                ELSE
                    UPDATE submissions SET status = 'rejected' WHERE id = NEW.submission_id;
                END IF;
            END
        ");
    }
};
