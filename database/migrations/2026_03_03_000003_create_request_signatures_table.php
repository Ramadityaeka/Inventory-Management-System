<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_request_id')
                  ->constrained('public_requests')
                  ->onDelete('cascade');
            $table->enum('signer_type', ['requester', 'pic']);
            $table->string('signer_name');
            $table->longText('signature_data');
            $table->timestamp('signed_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_signatures');
    }
};
