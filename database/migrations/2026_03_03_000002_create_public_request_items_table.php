<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_request_id')
                  ->constrained('public_requests')
                  ->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('quantity_requested');
            $table->integer('quantity_approved')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_request_items');
    }
};
