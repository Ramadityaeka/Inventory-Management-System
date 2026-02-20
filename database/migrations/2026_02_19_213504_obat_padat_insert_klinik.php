<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Migration: Isi stok awal OBAT PADAT klinik
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
     *       - Category: "Obat Padat" (code: 1.01.03.14.002) atau fallback OBAT-OBATAN
     *       - Code: Auto-generated berdasarkan prefix category
     *
     * Sumber data: Tabel_OBAT_PADAT.xlsx — Sheet "OBAT_PADAT"
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
            // Cari category "Obat Padat"
            $category = DB::table('categories')
                ->where('code', '1.01.03.14.002')
                ->orWhere('name', 'Obat Padat')
                ->first();

            if (!$category) {
                // Fallback ke parent category OBAT-OBATAN
                $category = DB::table('categories')
                    ->where('code', '1.01.03.14')
                    ->orWhere('name', 'LIKE', '%OBAT-OBATAN%')
                    ->first();
            }

            $categoryId   = $category ? $category->id   : 60;
            $categoryCode = $category ? $category->code : '1.01.03.14.002';

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
        // Data stok awal obat padat klinik
        // Sumber: Tabel_OBAT_PADAT.xlsx — Sheet "OBAT_PADAT"
        // Semua item obat padat menggunakan unit BOX
        // ================================================================
        $stockData = [
            // Kode: 000006
            ['item_name' => 'ASCARDIA 80 MG @100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 129000],
            // Kode: 000011
            ['item_name' => 'DEXTEEM PLUS @100 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 40500],
            // Kode: 000016
            ['item_name' => 'LAPIFED @100 TAB', 'quantity' => 11, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 462000],
            // Kode: 000023
            ['item_name' => 'NEW DIATAB @100 TAB', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 70800],
            // Kode: 000025
            ['item_name' => 'POLYSILANE TABLET KUNYAH @40 TAB', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 53391],
            // Kode: 000026
            ['item_name' => 'SPASMINAL @100 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 128250],
            // Kode: 000039
            ['item_name' => 'BISOPROLOL 5 MG @30 TAB', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 48000],
            // Kode: 000042
            ['item_name' => 'CEFADROXIL 500 MG @100 CAPS', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 1438000],
            // Kode: 000049
            ['item_name' => 'GLIMEPIRID 1 MG @50 TAB', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 45600],
            // Kode: 000050
            ['item_name' => 'GLIMEPIRID 2 MG @50 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 81000],
            // Kode: 000067
            ['item_name' => 'RANITIDIN 150 MG @100 TAB', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 50000],
            // Kode: 000071
            ['item_name' => 'ARDIUM 500MG @60 TAB', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 663000],
            // Kode: 000072
            ['item_name' => 'BIOSANBE @100 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 165000],
            // Kode: 000082
            ['item_name' => 'LACOLDIN @100 TAB', 'quantity' => 18, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 360000],
            // Kode: 000084
            ['item_name' => 'LAPIBION @100 TAB', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 217800],
            // Kode: 000091
            ['item_name' => 'OSKOM @30 KAPLET', 'quantity' => 16, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 342448],
            // Kode: 000093
            ['item_name' => 'RHINOS SR @50 KAPSUL', 'quantity' => 13, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 465000],
            // Kode: 000095
            ['item_name' => 'SURBEX T @30 TAB', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 86800],
            // Kode: 000099
            ['item_name' => 'NEUROSANBE 5000 @100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 369600],
            // Kode: 000100
            ['item_name' => 'ALLUPURINOL 100 MG @100 TAB', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 35600],
            // Kode: 000101
            ['item_name' => 'AMLODIPINE 5 MG @100 TAB', 'quantity' => 12, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 165000],
            // Kode: 000102
            ['item_name' => 'AMLODIPINE 10 MG @100 TAB', 'quantity' => 38, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 90500],
            // Kode: 000119
            ['item_name' => 'DULCOLAX SUPPOSITORIA', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 28500],
            // Kode: 000126
            ['item_name' => 'MERISLON 6 MG @100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 707514],
            // Kode: 000127
            ['item_name' => 'NATUR E ADVANCE @32 TAB', 'quantity' => 12, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 86500],
            // Kode: 000131
            ['item_name' => 'IMBOOST FORCE', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 210800],
            // Kode: 000146
            ['item_name' => 'LASGAN 30 MG @20 KAPSUL', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 533799],
            // Kode: 000148
            ['item_name' => 'MYONEP @100 TABLET', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 884892],
            // Kode: 000152
            ['item_name' => 'OXCAL @100 KAPSUL', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 561000],
            // Kode: 000153
            ['item_name' => 'SUMAGESTIC 600 MG @100 TAB', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 77589],
            // Kode: 000167
            ['item_name' => 'NEW DIATABS @100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 70800],
            // Kode: 000168
            ['item_name' => 'SARI KUNYIT SIDO MUNCUL @50 KAPSUL', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 101200],
            // Kode: 000195
            ['item_name' => 'ALPHAMOL 600 MG @150 KAP', 'quantity' => 24, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 57400],
            // Kode: 000201
            ['item_name' => 'AVIGAN 200 MG @100 TAB', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 2250000],
            // Kode: 000203
            ['item_name' => 'METRONIDAZOLE 500 MG', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 77900],
            // Kode: 000212
            ['item_name' => 'ATORVASTATIN 20 MG @30 TAB', 'quantity' => 35, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 59400],
            // Kode: 000214
            ['item_name' => 'CEFILA 100 MG @30 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 1053501],
            // Kode: 000219
            ['item_name' => 'LANSOPRAZOLE 30 MG @20 TAB', 'quantity' => 33, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 33600],
            // Kode: 000229
            ['item_name' => 'NEUROBION FORTE 5000 @50 TAB', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 290931],
            // Kode: 000237
            ['item_name' => 'RAMIPRIL 5MG @100', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 76500],
            // Kode: 000239
            ['item_name' => 'ACLAM @30', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 633600],
            // Kode: 000243
            ['item_name' => 'GARLIC KAPSUL SM 3500 MG @30', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 58000],
            // Kode: 000248
            ['item_name' => 'BOOST D3 5000 @30 TAB', 'quantity' => 150, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 91500],
            // Kode: 000249
            ['item_name' => 'DULCOLAX 5MG @40 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 28500],
            // Kode: 000251
            ['item_name' => 'EPERISONE HCI 50MG @100 TAB', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 92000],
            // Kode: 000256
            ['item_name' => 'ONDANSETRON 4MG @30 TAB', 'quantity' => 11, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 55700],
            // Kode: 000267
            ['item_name' => 'SURBEX-T @30 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 86800],
            // Kode: 000270
            ['item_name' => 'FG TROCHES @120 TAB', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 233000],
            // Kode: 000285
            ['item_name' => 'AMLODIPINE 5MG @100 TAB', 'quantity' => 19, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 165000],
            // Kode: 000289
            ['item_name' => 'DIFLAM 50 MG', 'quantity' => 33, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 311000],
            // Kode: 000293
            ['item_name' => 'VESPERUM 10 MG @100 TAB', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 42000],
            // Kode: 000294
            ['item_name' => 'ASTHIN FORCE 4 MG @20 TAB', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 235000],
            // Kode: 000296
            ['item_name' => 'CEFIXIM 100MG', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 105500],
            // Kode: 000298
            ['item_name' => 'FENOFIBRAT 300 MG @30 TAB', 'quantity' => 28, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 175000],
            // Kode: 000303
            ['item_name' => 'TAMCOCIN 500 MG @100 KAPSUL', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 925000],
            // Kode: 000312
            ['item_name' => 'REMAFAR 8 MG', 'quantity' => 40, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 59500],
            // Kode: 000314
            ['item_name' => 'ACETILSISTEIN 200 MG @100 KAPSUL', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 66000],
            // Kode: 000315
            ['item_name' => 'GLUMIN XR 500 MG @30 TAB', 'quantity' => 32, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 87000],
            // Kode: 000316
            ['item_name' => 'RHEMAFAR 8 MG @100 TAB', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 59500],
            // Kode: 000318
            ['item_name' => 'LASAL 4 MG', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 326500],
            // Kode: 000320
            ['item_name' => 'CANDESARTAN 8 MG @30 TAB', 'quantity' => 13, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 55000],
            // Kode: 000321
            ['item_name' => 'EXAFLAM 50 MG @50 TAB', 'quantity' => 7, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 184500],
            // Kode: 000322
            ['item_name' => 'HALOWELL D3 1000 UI @20 TAB', 'quantity' => 63, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 39600],
            // Kode: 000326
            ['item_name' => 'EPERISON HCL 50 MG @50 TAB', 'quantity' => 7, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 92000],
            // Kode: 000327
            ['item_name' => 'METFORMIN 500 MG @200 TAB', 'quantity' => 15, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 45000],
            // Kode: 000329
            ['item_name' => 'ARCOXIA 90 MG', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 693000],
            // Kode: 000330
            ['item_name' => 'ANTARTIC KRILL OIL HEALTH & HAPPINESS @ 30 KAPSUL', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 309000],
            // Kode: 000331
            ['item_name' => 'CLOPIDOGREL 75 MG @100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 160000],
            // Kode: 000334
            ['item_name' => 'REDOXON @24', 'quantity' => 25, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 130800],
            // Kode: 000335
            ['item_name' => 'RENOVIT GOLD @100 TAB', 'quantity' => 30, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 357000],
            // Kode: 000336
            ['item_name' => 'SIMVASTATIN 20 MG @100 TAB', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 123000],
            // Kode: 000337
            ['item_name' => 'VITALONG C 25x4', 'quantity' => 34, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 165000],
            // Kode: 000338
            ['item_name' => 'VITAMIN D3 5000 IU NOW @120', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 169000],
            // Kode: 000340
            ['item_name' => 'RHEMAFAR 4 MG @100 TAB', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 71500],
            // Kode: 000341
            ['item_name' => 'ASWAGANDA', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 450000],
            // Kode: 000342
            ['item_name' => 'CARDIOASPIRIN 100 MG @30 TAB', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 82300],
            // Kode: 000343
            ['item_name' => 'D3K2 5000IU @60 KAPSUL', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 249500],
            // Kode: 000344
            ['item_name' => 'GALSUVMET 50/500 MG @30 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 290000],
            // Kode: 000345
            ['item_name' => 'HISTIGO 6MG @100 TAB', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 95000],
            // Kode: 000346
            ['item_name' => 'LERZIN 10 MG @100 TAB', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 52000],
            // Kode: 000347
            ['item_name' => 'MELATONIN', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 295000],
            // Kode: 000348
            ['item_name' => 'MIXALGIN @100 TAB', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 115000],
            // Kode: 000349
            ['item_name' => 'HOTIN DCL @30GR', 'quantity' => 49, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 23700],
            // Kode: 000354
            ['item_name' => 'RICOXA 90 MG', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 353400],
            // Kode: 000356
            ['item_name' => 'FLUIMUCIL 200 MG @60', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 606060],
            // Kode: 000357
            ['item_name' => 'TROLIP 300 MG @30', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 583305],
            // Kode: 000358
            ['item_name' => 'TRUVAZ 20 MG @30', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 931512],
            // Kode: 000359
            ['item_name' => 'CANDERIN 8 MG @30', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 337107],
            // Kode: 000360
            ['item_name' => 'CANDERIN 16 MG @30', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 425796],
            // Kode: 000361
            ['item_name' => 'AMARYL 1MG @50', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 285270],
            // Kode: 000362
            ['item_name' => 'BLACKMORES VIT C 500 MG @60', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 167832],
            // Kode: 000363
            ['item_name' => 'DIAGIT @100 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 449439],
            // Kode: 000364
            ['item_name' => 'RANTIN TABLET @100', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 916638],
            // Kode: 000365
            ['item_name' => 'XONCE VIT C HISAP', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 139638],
            // Kode: 000366
            ['item_name' => 'HEXAVASK 10 MG', 'quantity' => 14, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 176000],
            // Kode: 000367
            ['item_name' => 'HEXAVASK 5 MG', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 101000],
            // Kode: 000368
            ['item_name' => 'OMEGACOR', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 568000],
            // Kode: 000370
            ['item_name' => 'CANDESARTAN 16 MG @30 TAB', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 75000],
            // Kode: 000371
            ['item_name' => 'RILLUS TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 369000],
            // Kode: 000161
            ['item_name' => 'BLACKMORES MULTIVITAMIN + MINERAL @30 KAPSUL', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 215000],
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
            ->where('code', 'LIKE', '1.01.03.14.002.%')
            ->pluck('id');

        if ($autoGeneratedItemIds->isNotEmpty()) {
            DB::table('item_units')
                ->whereIn('item_id', $autoGeneratedItemIds)
                ->delete();
        }

        DB::table('items')
            ->where('code', 'LIKE', '1.01.03.14.002.%')
            ->delete();
    }
};