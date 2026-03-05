<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code', 30)->unique();
            $table->uuid('token')->unique();
            $table->string('requester_name');
            $table->foreignId('warehouse_id')->constrained('units');
            $table->foreignId('pic_user_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'partial', 'rejected', 'completed'])
                  ->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_requests');
    }
};
