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
        Schema::table('users', function (Blueprint $table) {
            // Add role if not exists
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['super_admin', 'admin_gudang', 'staff_gudang'])->after('password');
            }
            
            // Add phone if not exists
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('role');
            }
            
            // Add is_active if not exists
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('phone');
            }
            
            // Add remember_token if not exists
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
            
            // Add email_verified_at if not exists  
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            
            // Add soft deletes
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            // We won't drop other columns in down as they might contain important data
        });
    }
};
