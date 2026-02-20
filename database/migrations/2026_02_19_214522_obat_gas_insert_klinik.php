<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Migration: Isi stok awal OBAT GAS klinik
     * Unit Klinik : units.id = 10 (GD-010 / Klinik)
     *
     * Migration ini akan:
     *   0. CREATE items (jika belum ada di database)
     *        — Jika sudah ada (match by name), langsung pakai ID existing
     *   1. INSERT item_units  — satuan per item (skip jika sudah ada)
     *   2. INSERT submissions — pengadaan awal status 'approved'
     *   3. INSERT stocks      — stok langsung (update qty jika sudah ada)
     *   4. INSERT stock_movements — catatan pergerakan stok masuk
     *
     * NOTE: Item baru yang di-create otomatis menggunakan:
     *       - Category: "Obat Gas" (code: 1.01.03.14.003) atau fallback OBAT-OBATAN
     *       - Code: Auto-generated berdasarkan prefix category
     *
     * Sumber data: Obat_Gas.xlsx — Sheet "Obat Gas"
     */
    public function up(): void
    {
        $now     = Carbon::now();
        $today   = $now->toDateString();
        $unitId  = 10; // units.id Klinik
        $staffId = 1;  // Super Admin
        $adminId = 1;  // Super Admin

        // Helper: Get or create item, return item ID
        // Jika item sudah ada (match by name) → langsung return ID existing, tidak buat baru
        $getOrCreateItemId = function (string $name, string $unit) use ($now): int {
            $item = DB::table('items')->where('name', $name)->first();
            if ($item) {
                return $item->id;
            }

            // Item belum ada → buat baru
            // Cari category "Obat Gas"
            $category = DB::table('categories')
                ->where('code', '1.01.03.14.003')
                ->orWhere('name', 'Obat Gas')
                ->first();

            if (!$category) {
                // Fallback ke parent category OBAT-OBATAN
                $category = DB::table('categories')
                    ->where('code', '1.01.03.14')
                    ->orWhere('name', 'LIKE', '%OBAT-OBATAN%')
                    ->first();
            }

            $categoryId   = $category ? $category->id   : 60;
            $categoryCode = $category ? $category->code : '1.01.03.14.003';

            // Generate kode unik berurutan
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
        // Data stok awal obat gas klinik
        // Sumber: Obat_Gas.xlsx — Sheet "Obat Gas"
        // ================================================================
        $stockData = [
            // Kode: 000005
            ['item_name' => 'VICKS INHALER @0,5ML', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'PCS', 'unit_price' => 18100],
            // Kode: 000007
            ['item_name' => 'NO PAIN SPRAY @100 ML', 'quantity' => 17, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 193400],
            // Kode: 000008
            ['item_name' => 'Oxycan', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'PCS', 'unit_price' => 55000],
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
            $itemId = $getOrCreateItemId($data['item_name'], $data['unit']);
            $qty    = $data['quantity'];

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
            ->where('code', 'LIKE', '1.01.03.14.003.%')
            ->pluck('id');

        if ($autoGeneratedItemIds->isNotEmpty()) {
            DB::table('item_units')
                ->whereIn('item_id', $autoGeneratedItemIds)
                ->delete();
        }

        DB::table('items')
            ->where('code', 'LIKE', '1.01.03.14.003.%')
            ->delete();
    }
};