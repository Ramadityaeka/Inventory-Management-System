<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambahkan kategori PERABOT KANTOR (1.01.03.05) beserta sub-kategorinya.
     */
    public function up(): void
    {
        $now = now();

        // Parent: 1.01.03
        $parentId = DB::table('categories')->where('code', '1.01.03')->value('id');

        if (!$parentId) {
            echo "WARN: kategori 1.01.03 tidak ditemukan, menggunakan id=1 sebagai fallback.\n";
            $parentId = 1;
        }

        // Hapus lama jika ada (sub-kategori dulu, lalu parent)
        $oldId = DB::table('categories')->where('code', '1.01.03.05')->value('id');
        if ($oldId) {
            DB::table('categories')->where('parent_id', $oldId)->delete();
            DB::table('categories')->where('id', $oldId)->delete();
            echo "✓ Kategori perabot lama dihapus (id={$oldId}).\n";
        }

        // Insert parent PERABOT KANTOR
        $perabotId = DB::table('categories')->insertGetId([
            'code'        => '1.01.03.05',
            'name'        => 'PERABOT KANTOR',
            'parent_id'   => $parentId,
            'description' => null,
            'is_active'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        echo "✓ Dibuat: 1.01.03.05 PERABOT KANTOR (id={$perabotId})\n";

        // Sub-kategori
        $subCategories = [
            ['1.01.03.05.001', 'Sapu Dan Sikat'],
            ['1.01.03.05.002', 'Alat-Alat Pel Dan Lap'],
            ['1.01.03.05.003', 'Ember, Slang, Dan Tempat Air Lainnya'],
            ['1.01.03.05.004', 'Keset Dan Tempat Sampah'],
            ['1.01.03.05.005', 'Kunci, Kran Dan Semprotan'],
            ['1.01.03.05.006', 'Alat Pengikat'],
            ['1.01.03.05.007', 'Peralatan Ledeng'],
            ['1.01.03.05.008', 'Bahan Kimia Untuk Pembersih'],
            ['1.01.03.05.009', 'Alat Untuk Makan Dan Minum'],
            ['1.01.03.05.010', 'Kaos Lampu Petromak'],
            ['1.01.03.05.011', 'Kaca Lampu Petromak'],
            ['1.01.03.05.012', 'Pengharum Ruangan'],
            ['1.01.03.05.013', 'Kuas'],
            ['1.01.03.05.014', 'Segel/Tanda Pengaman'],
            ['1.01.03.05.999', 'Perabot Kantor Lainnya'],
        ];

        $inserts = [];
        foreach ($subCategories as [$code, $name]) {
            $inserts[] = [
                'code'        => $code,
                'name'        => $name,
                'parent_id'   => $perabotId,
                'description' => null,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        DB::table('categories')->insert($inserts);

        echo "✓ Berhasil menambahkan " . count($inserts) . " sub-kategori Perabot Kantor\n";
    }

    public function down(): void
    {
        // Hapus sub-kategori dulu (child), lalu parent
        $perabotId = DB::table('categories')->where('code', '1.01.03.05')->value('id');

        if ($perabotId) {
            DB::table('categories')->where('parent_id', $perabotId)->delete();
            DB::table('categories')->where('id', $perabotId)->delete();
            echo "✓ Kategori PERABOT KANTOR dan sub-kategorinya dihapus.\n";
        }
    }
};
