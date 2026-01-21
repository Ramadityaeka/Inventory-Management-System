<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super admin
        User::updateOrCreate([
            'email' => 'admin@test.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        // Admin Gudang
        User::updateOrCreate([
            'email' => 'admingudang@gmail.com'
        ], [
            'name' => 'Admin Gudang',
            'password' => Hash::make('admin123'),
            'role' => User::ROLE_ADMIN_GUDANG,
            'is_active' => true,
        ]);

        // Staff / regular user
        User::updateOrCreate([
            'email' => 'gilbert@gmail.com'
        ], [
            'name' => 'Gilbert',
            'password' => Hash::make('12345678'),
            'role' => User::ROLE_STAFF_GUDANG,
            'is_active' => true,
        ]);
    }
}
