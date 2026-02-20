<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Migration: Isi stok awal OBAT CAIR klinik
     * Unit Klinik : units.id = 10 (GD-010 / Klinik)
     *
     * Migration ini akan:
     *   0. CREATE items (jika belum ada di database)
     *   1. INSERT item_units  — satuan per item (skip jika sudah ada)
     *   2. INSERT submissions — pengadaan awal status 'approved'
     *   3. INSERT stocks      — stok langsung
     *   4. INSERT stock_movements — catatan pergerakan stok masuk
     *
     * NOTE: Jika item belum ada di database, akan otomatis dibuat dengan:
     *       - Category: "Obat Cair" (code: 1.01.03.14.001)
     *       - Code: Auto-generated berdasarkan prefix 1.01.03.14.001.XXXXXX
     *
     * Sumber data: Tabel_Obat_Cair_2025.xlsx
     */
    public function up(): void
    {
        $now    = Carbon::now();
        $today  = $now->toDateString();
        $unitId = 10;  // units.id Klinik
        $staffId = 1;  // Super Admin
        $adminId = 1;  // Super Admin

        // Helper: Get or create item, return item ID
        $getOrCreateItemId = function (string $name, string $unit) use ($now): int {
            $item = DB::table('items')->where('name', $name)->first();
            if ($item) {
                return $item->id;
            }

            // Cari category "Obat Cair"
            $category = DB::table('categories')
                ->where('code', '1.01.03.14.001')
                ->orWhere('name', 'Obat Cair')
                ->first();

            if (!$category) {
                // Fallback ke parent category OBAT-OBATAN
                $category = DB::table('categories')
                    ->where('code', '1.01.03.14')
                    ->orWhere('name', 'LIKE', '%OBAT-OBATAN%')
                    ->first();
            }

            $categoryId   = $category ? $category->id  : 60;
            $categoryCode = $category ? $category->code : '1.01.03.14.001';

            // Generate kode unik
            $lastItem = DB::table('items')
                ->where('code', 'LIKE', $categoryCode . '.%')
                ->orderBy('code', 'desc')
                ->first();

            if ($lastItem && preg_match('/(\d{6})$/', $lastItem->code, $matches)) {
                $newNumber = str_pad((int) $matches[1] + 1, 6, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '000001';
            }

            $code = $categoryCode . '.' . $newNumber;

            $itemId = DB::table('items')->insertGetId([
                'category_id' => $categoryId,
                'code'        => $code,
                'name'        => $name,
                'unit'        => $unit,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            echo "INFO: Created new item: {$name} (ID: {$itemId}, Code: {$code})\n";

            return $itemId;
        };

        // ================================================================
        // Data stok awal obat cair klinik
        // Sumber: Tabel_Obat_Cair_2025.xlsx — Sheet "Obat Cair 2025"
        // Format: [item_name, quantity, conversion_factor, unit, unit_price]
        // ================================================================
        $stockData = [
            // Kode: 000018
            ['item_name' => 'Tolak Angin @12 Sachet', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 53400],
            // Kode: 000028
            ['item_name' => 'Obat Kumur Total Care Cool Mint @250 ML', 'quantity' => 50, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 18000],
            // Kode: 000029
            ['item_name' => 'Cenfresh TM Minidose @20 Buah', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 609627],
            // Kode: 000033
            ['item_name' => 'Cairan Infus NaCl @100 ML', 'quantity' => 13, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 17000],
            // Kode: 000034
            ['item_name' => 'Cairan Infus NaCl @500 ML', 'quantity' => 7, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 22600],
            // Kode: 000035
            ['item_name' => 'Cairan Infus Ringer Laktat @500 ML', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 22600],
            // Kode: 000065
            ['item_name' => 'Cerini Sirup @60 ML', 'quantity' => 11, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 66489],
            // Kode: 000072
            ['item_name' => 'Tempra Sirup Anak @60 ML', 'quantity' => 30, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 57200],
            // Kode: 000075
            ['item_name' => 'Minyak Kayu Putih Cap Lang @15 ML', 'quantity' => 120, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 6500],
            // Kode: 000078
            ['item_name' => 'Neurobion 5000 mg Injeksi', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 419770],
            // Kode: 000086
            ['item_name' => 'Sucralfat 100 ML', 'quantity' => 20, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 26000],
            // Kode: 000095
            ['item_name' => 'Tantum Verde @60 ML', 'quantity' => 32, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 29500],
            // Kode: 000110
            ['item_name' => 'Hufagrip Flu & Batuk Sirup @60 ML', 'quantity' => 23, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 25800],
            // Kode: 000114
            ['item_name' => 'Tolak Angin Sido Muncul @12 Sachet', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 53400],
            // Kode: 000116
            ['item_name' => 'Decadryl Expectorant Syrup @60 ML', 'quantity' => 100, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 16500],
            // Kode: 000119
            ['item_name' => 'Lapisiv Syrup @100 ML', 'quantity' => 165, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 36900],
            // Kode: 000124
            ['item_name' => 'Betadine Kumur @100 ML', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 28500],
            // Kode: 000127
            ['item_name' => 'Ginggival Kin', 'quantity' => 75, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 175800],
            // Kode: 000130
            ['item_name' => 'Cernevit Multivitamin for IV', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 3993000],
            // Kode: 000131
            ['item_name' => 'Cetirizin Syrup', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 12500],
            // Kode: 000132
            ['item_name' => 'Onoiwa MX @3 Sachet', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 257400],
            // Kode: 000140
            ['item_name' => 'Madu Uray Natural Honey Stick', 'quantity' => 21, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 43500],
            // Kode: 000153
            ['item_name' => 'NaCl 0,9% Ampul @25 ML', 'quantity' => 143, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 5530],
            // Kode: 000154
            ['item_name' => 'Ranitidine Injeksi', 'quantity' => 20, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 3500],
            // Kode: 000158
            ['item_name' => 'Sanadryl Exp Syrup @60 ML', 'quantity' => 140, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 16500],
            // Kode: 000161
            ['item_name' => 'Plantacyd Syrup 100 ML', 'quantity' => 41, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 15800],
            // Kode: 000162
            ['item_name' => 'Cendo Xytrol Minidose @20 Strip', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 667650],
            // Kode: 000166
            ['item_name' => 'Sanadryl DMP Syrup @60 ML', 'quantity' => 192, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 19800],
            // Kode: 000173
            ['item_name' => 'Ecodin @10 ML', 'quantity' => 30, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 3200],
            // Kode: 000174
            ['item_name' => 'Otolin TT', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 62000],
            // Kode: 000176
            ['item_name' => 'Actifed Plus Cough Merah 60 ML', 'quantity' => 7, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 75924],
            // Kode: 000177
            ['item_name' => 'Bisolvon Extra Syrup 60 ML', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 66045],
            // Kode: 000178
            ['item_name' => 'Episan 500mg/5ml Syrup', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 79809],
            // Kode: 000179
            ['item_name' => 'Extrace 500mg/5ml Injeksi', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 184000],
            // Kode: 000180
            ['item_name' => 'Topazol Inj 40 MG', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 231000],
            // Kode: 000181
            ['item_name' => 'Rantin Inj', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 198000],
        ];

        // ================================================================
        // 1. INSERT ITEM_UNITS (skip jika sudah ada)
        // ================================================================
        foreach ($stockData as $data) {
            $itemId = $getOrCreateItemId($data['item_name'], $data['unit']);

            $exists = DB::table('item_units')
                ->where('item_id', $itemId)
                ->where('name', $data['unit'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('item_units')->insert([
                'item_id'           => $itemId,
                'name'              => $data['unit'],
                'conversion_factor' => $data['conversion_factor'],
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ================================================================
        // 2. INSERT SUBMISSIONS (pengadaan awal, status approved)
        // ================================================================
        foreach ($stockData as $data) {
            $itemId     = $getOrCreateItemId($data['item_name'], $data['unit']);
            $item       = DB::table('items')->where('id', $itemId)->first();
            $totalPrice = $data['unit_price'] * $data['quantity'];

            DB::table('submissions')->insert([
                'item_id'           => $itemId,
                'item_name'         => $item ? $item->name : $data['item_name'],
                'unit_id'           => $unitId,
                'warehouse_id'      => $unitId,
                'staff_id'          => $staffId,
                'quantity'          => $data['quantity'],
                'conversion_factor' => $data['conversion_factor'],
                'unit'              => $data['unit'],
                'unit_price'        => $data['unit_price'],
                'total_price'       => $totalPrice,
                'receive_date'      => $today,
                'status'            => 'approved',
                'is_draft'          => 0,
                'submitted_at'      => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ================================================================
        // 3. INSERT / UPDATE STOCKS
        // ================================================================
        foreach ($stockData as $data) {
            $itemId  = $getOrCreateItemId($data['item_name'], $data['unit']);
            $qty     = $data['quantity'];

            $existingStock = DB::table('stocks')
                ->where('item_id', $itemId)
                ->where('unit_id', $unitId)
                ->first();

            if ($existingStock) {
                DB::table('stocks')
                    ->where('id', $existingStock->id)
                    ->update([
                        'quantity'     => DB::raw("quantity + {$qty}"),
                        'last_updated' => $now,
                        'updated_at'   => $now,
                    ]);
            } else {
                DB::table('stocks')->insert([
                    'item_id'      => $itemId,
                    'unit_id'      => $unitId,
                    'warehouse_id' => $unitId,
                    'quantity'     => $qty,
                    'last_updated' => $now,
                    'updated_at'   => $now,
                ]);
            }
        }

        // ================================================================
        // 4. INSERT STOCK_MOVEMENTS
        // ================================================================
        $submissionIds = DB::table('submissions')
            ->where('unit_id', $unitId)
            ->where('created_at', '>=', $now->copy()->subMinutes(5))
            ->pluck('id', 'item_id');

        foreach ($stockData as $data) {
            $itemId = $getOrCreateItemId($data['item_name'], $data['unit']);

            DB::table('stock_movements')->insert([
                'item_id'        => $itemId,
                'unit_id'        => $unitId,
                'warehouse_id'   => $unitId,
                'quantity'       => $data['quantity'],
                'movement_type'  => 'in',
                'reference_type' => 'submission',
                'reference_id'   => $submissionIds[$itemId] ?? null,
                'created_by'     => $adminId,
                'created_at'     => $now,
            ]);
        }
    }

    public function down(): void
    {
        $unitId = 10; // Klinik

        DB::table('stock_movements')
            ->where('unit_id', $unitId)
            ->where('movement_type', 'in')
            ->delete();

        DB::table('stocks')
            ->where('unit_id', $unitId)
            ->delete();

        DB::table('submissions')
            ->where('unit_id', $unitId)
            ->delete();

        // Hapus item_units & items yang auto-generated dari migration ini
        $autoGeneratedItemIds = DB::table('items')
            ->where('code', 'LIKE', '1.01.03.14.001.%')
            ->pluck('id');

        if ($autoGeneratedItemIds->isNotEmpty()) {
            DB::table('item_units')
                ->whereIn('item_id', $autoGeneratedItemIds)
                ->delete();
        }

        DB::table('items')
            ->where('code', 'LIKE', '1.01.03.14.001.%')
            ->delete();
    }
};