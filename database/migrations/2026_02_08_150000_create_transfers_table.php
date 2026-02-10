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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('units')->onDelete('set null');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('units')->onDelete('set null');
            $table->integer('quantity');
            $table->text('reason')->nullable();
            $table->enum('status', [
                'draft',
                'waiting_review',
                'waiting_approval',
                'approved',
                'in_transit',
                'waiting_receive',
                'completed',
                'rejected',
                'cancelled'
            ])->default('draft');
            
            // User references
            $table->foreignId('requested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Timestamps for workflow
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Rejection info
            $table->enum('rejection_stage', ['review', 'approval', 'receive'])->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Additional notes
            $table->text('notes')->nullable();
            $table->text('shipping_note')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('from_warehouse_id');
            $table->index('to_warehouse_id');
            $table->index('status');
            $table->index('requested_by');
            $table->index('requested_at');
        });

        // Create transfer photos table
        Schema::create('transfer_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->onDelete('cascade');
            $table->string('photo_path');
            $table->enum('photo_type', ['packing', 'shipping', 'receiving'])->default('packing');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_photos');
        Schema::dropIfExists('transfers');
    }
};
