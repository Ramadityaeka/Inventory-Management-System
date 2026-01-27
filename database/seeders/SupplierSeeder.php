<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP001',
                'name' => 'PT Supplier Utama Indonesia',
                'contact_person' => 'Budi Santoso',
                'phone' => '021-12345678',
                'email' => 'info@supplierutama.co.id',
                'address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'code' => 'SUP002',
                'name' => 'CV Mitra Sejahtera',
                'contact_person' => 'Siti Aminah',
                'phone' => '021-87654321',
                'email' => 'contact@mitrasejahtera.co.id',
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta Pusat',
                'is_active' => true,
            ],
            [
                'code' => 'SUP003',
                'name' => 'PT Abadi Jaya Sentosa',
                'contact_person' => 'Ahmad Fauzi',
                'phone' => '021-55555555',
                'email' => 'sales@abadijaya.co.id',
                'address' => 'Jl. Thamrin No. 67, Jakarta Pusat',
                'is_active' => true,
            ],
            [
                'code' => 'SUP004',
                'name' => 'UD Berkah Mandiri',
                'contact_person' => 'Dewi Kartika',
                'phone' => '021-66666666',
                'email' => 'info@berkahmandiri.co.id',
                'address' => 'Jl. HR Rasuna Said No. 89, Jakarta Selatan',
                'is_active' => true,
            ],
            [
                'code' => 'SUP005',
                'name' => 'PT Global Trading Nusantara',
                'contact_person' => 'Eko Prasetyo',
                'phone' => '021-77777777',
                'email' => 'trading@globalnusantara.co.id',
                'address' => 'Jl. Kuningan Raya No. 12, Jakarta Selatan',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('Successfully seeded ' . count($suppliers) . ' suppliers.');
    }
}
