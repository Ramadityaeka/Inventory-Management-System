<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call default users seeder to create initial accounts
        $this->call(DefaultUsersSeeder::class);
        
        // Call supplier seeder to create sample suppliers
        $this->call(SupplierSeeder::class);
    }
}
