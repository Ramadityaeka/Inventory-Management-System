<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If table already exists (manually created), skip
        if (Schema::hasTable('item_units')) {
            return;
        }

        Schema::create('item_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('name');
            $table->unsignedInteger('conversion_factor')->default(1)->comment('How many base units per this unit');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->unique(['item_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
