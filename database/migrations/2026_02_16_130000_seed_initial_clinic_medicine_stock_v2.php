<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Migration: Isi stok awal obat klinik
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
     *       - Category: "Obat-obatan" atau category pertama yang tersedia
     *       - Code: Auto-generated berdasarkan prefix 1.01.03.14.001.XXXXXX
     */
    public function up(): void
    {
        $now      = Carbon::now();
        $today    = $now->toDateString();
        $unitId   = 10;  // units.id Klinik
        $staffId  = 1;   // Super Admin
        $adminId  = 1;   // Super Admin

        // Helper function untuk get item ID by name
        $getItemId = function($name) {
            $item = DB::table('items')->where('name', $name)->first();
            return $item ? $item->id : null;
        };

        // Helper function untuk create item jika belum ada
        $getOrCreateItemId = function($name, $unit) use ($now) {
            $item = DB::table('items')->where('name', $name)->first();
            if ($item) {
                return $item->id;
            }

            // Item tidak ada, create baru
            // Gunakan category "Obat Cair" (code: 1.01.03.14.001)
            $category = DB::table('categories')
                ->where('code', '1.01.03.14.001')
                ->orWhere('name', 'Obat Cair')
                ->first();
            
            if (!$category) {
                // Fallback ke parent category "OBAT-OBATAN"
                $category = DB::table('categories')
                    ->where('code', '1.01.03.14')
                    ->orWhere('name', 'LIKE', '%OBAT-OBATAN%')
                    ->first();
            }
            
            $categoryId = $category ? $category->id : 60; // Default ke OBAT-OBATAN jika tidak ketemu

            // Generate unique code dengan format category code
            $categoryCode = $category ? $category->code : '1.01.03.14.001';
            $lastItem = DB::table('items')
                ->where('code', 'LIKE', $categoryCode . '.%')
                ->orderBy('code', 'desc')
                ->first();
            
            if ($lastItem && preg_match('/(\d{6})$/', $lastItem->code, $matches)) {
                $lastNumber = intval($matches[1]);
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '000001';
            }
            
            $code = $categoryCode . '.' . $newNumber;

            // Insert item baru
            $itemId = DB::table('items')->insertGetId([
                'category_id' => $categoryId,
                'code' => $code,
                'name' => $name,
                'unit' => $unit,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            echo "INFO: Created new item: $name (ID: $itemId, Code: $code)\n";
            
            return $itemId;
        };

        // ================================================================
        // Data stok awal obat klinik
        // Format: [nama_item, satuan, qty, unit_price, total_price]
        // Jika item belum ada di database, gunakan 'item_name' di submission
        // ================================================================

                        $stockData = [
            ['item_name' => 'TOLAK ANGIN @ 12 SACHET', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 53400],
            ['item_name' => 'OBAT KUMUR TOTAL CARE COOL MINT 250Ml', 'quantity' => 50, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 18000], // NOT FOUND IN DB
            ['item_name' => 'CENFRESH TM MINIDOSE 0,6 ML @20 BUAH', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 609627],
            ['item_name' => 'CAIRAN INFUS NACL @100 ML', 'quantity' => 13, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 17000],
            ['item_name' => 'CAIRAN INFUS NACL @500 ML', 'quantity' => 7, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 22600],
            ['item_name' => 'CAIRAN INFUS RINGER LAKTAT @500 ML', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 22600],
            ['item_name' => 'CERINI SIRUP@60 ML', 'quantity' => 11, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 66489],
            ['item_name' => 'TEMPRA SIRUP ANAK @ 60 ML', 'quantity' => 30, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 57200],
            ['item_name' => 'MINYAK KAYU PUTIH CAP LANG @ 15 ML', 'quantity' => 120, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 6500],
            ['item_name' => 'NEUROBION 5000 MG INJEKSI', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 419770],
            ['item_name' => 'SUCRALFAT 100 ML', 'quantity' => 20, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 26000],
            ['item_name' => 'TANTRUM VERDE @60 ML', 'quantity' => 32, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 29500],
            ['item_name' => 'HUFAGRIP FLU & BATUK SIRUP @60ML', 'quantity' => 23, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 25800],
            ['item_name' => 'TOLAK ANGIN SIDO MUNCUL @ 12 SACHET', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 53400],
            ['item_name' => 'DECADRYL EXPECTORANT SYRUP @ 60 ML', 'quantity' => 100, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 16500],
            ['item_name' => 'LAPISIV SYRUP @ 100 ML', 'quantity' => 165, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 36900],
            ['item_name' => 'BETADINE KUMUR @ 100 ML', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 28500],
            ['item_name' => 'GINGGIVAL KIN', 'quantity' => 75, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 175800],
            ['item_name' => 'CERNEVIT MULTIVITAMIN FOR IV @10 VIAL', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 3993000],
            ['item_name' => 'CETIRIZIN SYRUP', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 12500],
            ['item_name' => 'ONOIWA MX @ 3 SACHET', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 257400],
            ['item_name' => 'Madu Uray natural Raw Honey in Stick 10x12 gr', 'quantity' => 21, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 43500],
            ['item_name' => 'Nacl 0,9% ampul @ 25 ml', 'quantity' => 143, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 5528],
            ['item_name' => 'Ranitidine Injeksi', 'quantity' => 20, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 3500],
            ['item_name' => 'SANADRYL EXP SYRUP @ 60 ML', 'quantity' => 140, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 16500],
            ['item_name' => 'PLANTACYD SYRUP 100 ML', 'quantity' => 41, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 15800],
            ['item_name' => 'Cendo Xytrol minidose @ 20 Strip', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 667650],
            ['item_name' => 'SANADRYL DMP SYRUP @ 60 ML', 'quantity' => 192, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 19800],
            ['item_name' => 'Ecodin @ 10ml', 'quantity' => 30, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 3200],
            ['item_name' => 'Otolin TT', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 62000],
            ['item_name' => 'Actifed Plus Cought Merah 60 ml', 'quantity' => 7, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 75924],
            ['item_name' => 'Bisolvon Extra Syrup 60 ml', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 66045],
            ['item_name' => 'Episan 500mg/5ml 100 ml Syrup', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 79809],
            ['item_name' => 'Extrace 500mg/5ml injeksi', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOTOL', 'unit_price' => 184000],
            ['item_name' => 'Topazol inj 40 mg', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 231000],
            ['item_name' => 'RANTIN INJ', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 198000],
            ['item_name' => 'ASCARDIA 80 MG @ 100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 129000],
            ['item_name' => 'DEXTEEM PLUS @ 100 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 40500],
            ['item_name' => 'LAPISTAN 500 MG @ 100 TAB', 'quantity' => 11, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 462000], // NOT FOUND IN DB
            ['item_name' => 'NEW DIATAB @ 100 TAB', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 70800],
            ['item_name' => 'POLYSILANE TABLET KUNYAH @ 40 TAB', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 53391],
            ['item_name' => 'SPASMINAL @ 100 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 128250],
            ['item_name' => 'BISOPROLOL 5 MG @ 30 TAB', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 48000],
            ['item_name' => 'CEFADROXIL 500 MG @ 100 CAPS', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 1430000],
            ['item_name' => 'GLIMEPIRID 1 MG @ 50 TAB', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 45600],
            ['item_name' => 'GLIMEPIRID 2 MG @ 50 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 81000],
            ['item_name' => 'RANITIDIN 150 MG @ 100 TAB', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 50000],
            ['item_name' => 'ARDIUM 500MG @ 60 TABLET', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 663000],
            ['item_name' => 'BIOSANBE @ 100 TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 165000],
            ['item_name' => 'LACOLDIN @ 100 TAB', 'quantity' => 18, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 360000],
            ['item_name' => 'LAPIBION @ 100 TAB', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 217800],
            ['item_name' => 'OSKOM @ 30 KAPLET', 'quantity' => 16, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 342449],
            ['item_name' => 'RHINOS SR @ 50 KAPSUL', 'quantity' => 13, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 465000],
            ['item_name' => 'SURBEX T @ 30 TAB', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 86800],
            ['item_name' => 'NEUROSANBE 5000 @ 100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 363600],
            ['item_name' => 'ALLUPURINOL 100 MG @100 TAB', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 35600],
            ['item_name' => 'AMLODIPINE 5 MG @100 TAB', 'quantity' => 12, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 165000],
            ['item_name' => 'AMLODIPINE 10 MG @100 TAB', 'quantity' => 38, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 90500],
            ['item_name' => 'DULCOLAX SUPPOSITORIA', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 38500],
            ['item_name' => 'MERISLON 6 MG @100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 707514],
            ['item_name' => 'NATUR E ADVANCE @32 TAB', 'quantity' => 12, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 86500],
            ['item_name' => 'IMBOOST FORCE', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 210800],
            ['item_name' => 'LASGAN 30 MG @20 KAPSUL', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 533799],
            ['item_name' => 'MYONEP @100 TABLET', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 884892],
            ['item_name' => 'OXCAL @100 KAPSUL', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 561000],
            ['item_name' => 'SUMAGESTIC 600 MG @100 TABLET', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 77589],
            ['item_name' => 'BLACKMORES MULTIVITAMIN + MINERAL @30 KAPSUL', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 215000],
            ['item_name' => 'NEW DIATABS @100 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 70800],
            ['item_name' => 'SARI KUNYIT SIDO MUNCUL @50 KAPSUL', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 101200],
            ['item_name' => 'ALPHAMOL 600 MG @ 150 KAP', 'quantity' => 24, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 57400],
            ['item_name' => 'AVIGAN 200 MG @ 100 TAB', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 2250000],
            ['item_name' => 'METRONIDAZOLE 500 MG', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 77900],
            ['item_name' => 'ATORVASTATIN 20 MG @ 30 TAB', 'quantity' => 25, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 59400],
            ['item_name' => 'CEFILA 100 MG @30 TAB', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 1053501],
            ['item_name' => 'LANSOPRAZOLE 30 MG @20 TAB', 'quantity' => 33, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 33600],
            ['item_name' => 'NEUROBION FORTE 5000 @50 TAB', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 290931],
            ['item_name' => 'RAMIPRIL 5MG @100', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 76500],
            ['item_name' => 'ACLAM @30', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 633600],
            ['item_name' => 'GARLIC KAPSUL SIDO MUNCUL 3500 MG@30 KAP', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 58000],
            ['item_name' => 'BOOST D3 5000 @30 TAB', 'quantity' => 150, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 91500],
            ['item_name' => 'DULCOLAX 5MG @4O TAB', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 28500],
            ['item_name' => 'EPERISONE HCI 50MG @100 TAB', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 92000],
            ['item_name' => 'ONDANSETRON 4MG @30 TAB', 'quantity' => 11, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 55700],
            ['item_name' => 'SURBEX-T @ 30 TABLET', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 86800],
            ['item_name' => 'FG TROCHES @ 120 TABLET', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 233000],
            ['item_name' => 'AMLODIPINE 5MG @ 100TABLET', 'quantity' => 19, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 165000],
            ['item_name' => 'DIFLAM 50 MG', 'quantity' => 33, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 311000],
            ['item_name' => 'VESPERUM 10 MG @ 100 TABLET', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 42000],
            ['item_name' => 'ASTHIN FORCE 4 MG @ 20 TABLET', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 235000],
            ['item_name' => 'CEFIXIM 100MG', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 105500],
            ['item_name' => 'Fenofibrat 300 mg @ 30 tablet', 'quantity' => 28, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 175000],
            ['item_name' => 'TAMCOCIN 500 MG @ 100 KAPSUL', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 925000],
            ['item_name' => 'Remafar 8 mg', 'quantity' => 40, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 59500],
            ['item_name' => 'ACETILSISTEIN 200 Mg @ 100 KAPSUL', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 66000],
            ['item_name' => 'GLUMIN XR 500 MG @ 30 TABLET', 'quantity' => 32, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 87000],
            ['item_name' => 'RHEMAFAR 8 MG @ 100 TABLET', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 59500],
            ['item_name' => 'LASAL 4 MG', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 326500],
            ['item_name' => 'CANDESARTAN 8 MG @ 3O TABLET', 'quantity' => 13, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 55000],
            ['item_name' => 'EXAFLAM 50 MG @ 50 TABLET', 'quantity' => 7, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 184500],
            ['item_name' => 'HALOWELL D3 1000 UI @ 20 TAB', 'quantity' => 63, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 39600],
            ['item_name' => 'Eperison Hcl 50 mg @ 50 tablet', 'quantity' => 7, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 92000],
            ['item_name' => 'METFORMIN 500 MG @ 200 TABLET', 'quantity' => 15, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 45000],
            ['item_name' => 'Arcoxia 90 mg', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 693000],
            ['item_name' => 'ANTARTIC KRILL OIL HEALTH & HAPPINESS @ 30 KAPSUL', 'quantity' => 1, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 309000],
            ['item_name' => 'REDOXON @ 24', 'quantity' => 25, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 130800],
            ['item_name' => 'RENOVIT GOLD TABLET @ 100 TABLET', 'quantity' => 30, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 357000],
            ['item_name' => 'SIMVASTATIN 20 MG @ 100 TABLET', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 123000],
            ['item_name' => 'VITALONG C TABLET 25 X 4', 'quantity' => 34, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 165000],
            ['item_name' => 'VITAMIN D3 5000 IU NOW @ 120', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 169000],
            ['item_name' => 'CLOPIDOGREL 75 MG @ 100 TABLET', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 160000],
            ['item_name' => 'Rhemafar 4 mg @ 100 Tab', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 71500],
            ['item_name' => 'Aswaganda', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 450000],
            ['item_name' => 'cardioaspirin 100 mg @ 30 tab', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 82300],
            ['item_name' => 'D3K2 5000IU @ 60 kapsul', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 249500],
            ['item_name' => 'Galsuvmet 50/500mg @ 30 Tab', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 290000],
            ['item_name' => 'Histigo 6mg @ 100 Tab', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 95000],
            ['item_name' => 'Lerzin 10 mg @ 100 Tab', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 52000],
            ['item_name' => 'Melatonin', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 295000],
            ['item_name' => 'Mixalgin @ 100 Tab', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 115000],
            ['item_name' => 'Hotin Dcl @30gr', 'quantity' => 49, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 23700],
            ['item_name' => 'Ricoxa 90 mg', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 353400],
            ['item_name' => 'Fluimucil 200 Mg @60', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 606060],
            ['item_name' => 'Trolip 300 mg @30', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 583305],
            ['item_name' => 'Truvaz 20 mg Tablet @30', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 931512],
            ['item_name' => 'Canderin 8 Mg @30 Tablet', 'quantity' => 5, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 337107],
            ['item_name' => 'Canderin 16 Mg @30 Tablet', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 425796],
            ['item_name' => 'Amaryl 1Mg @50', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 285270],
            ['item_name' => 'Blackmores Vitamin C 500 mg @60 Tablet', 'quantity' => 9, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 167823],
            ['item_name' => 'Diagit @100 tablet', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 449439],
            ['item_name' => 'Rantin Tablet @100', 'quantity' => 4, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 916638],
            ['item_name' => 'Xonce Vitamin C 500 mg Tablet Hisap', 'quantity' => 3, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 139638],
            ['item_name' => 'Hexavask 10 mg', 'quantity' => 14, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 176000],
            ['item_name' => 'Hexavask 5 mg', 'quantity' => 10, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 101000],
            ['item_name' => 'Omegacor', 'quantity' => 8, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 568000],
            ['item_name' => 'Candesartan 16 mg @30 Tab', 'quantity' => 6, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 75000],
            ['item_name' => 'Rillus tab', 'quantity' => 2, 'conversion_factor' => 1, 'unit' => 'BOX', 'unit_price' => 369000],
        ];



        // ================================================================
        // 1. INSERT ITEM_UNITS (skip jika sudah ada)
        // ================================================================
        
        foreach ($stockData as $data) {
            $itemName = $data['item_name'];
            $unit = $data['unit'];
            
            // Get or create item
            $itemId = $getOrCreateItemId($itemName, $unit);
            
            // Skip jika item_units sudah ada
            $exists = DB::table('item_units')
                ->where('item_id', $itemId)
                ->where('name', $unit)
                ->exists();
            
            if ($exists) {
                continue;
            }
            
            DB::table('item_units')->insert([
                'item_id' => $itemId,
                'name' => $unit,
                'conversion_factor' => $data['conversion_factor'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ================================================================
        // 2. INSERT SUBMISSIONS
        // ================================================================
        
        foreach ($stockData as $data) {
            $itemName = $data['item_name'];
            $unit = $data['unit'];
            $qty = $data['quantity'];
            $unitPrice = $data['unit_price'];
            $totalPrice = $unitPrice * $qty;
            
            // Get or create item
            $itemId = $getOrCreateItemId($itemName, $unit);
            
            // Get item_name from items table based on item_id
            $item = DB::table('items')->where('id', $itemId)->first();
            $actualItemName = $item ? $item->name : $itemName;
            
            $submissionData = [
                'item_id' => $itemId,
                'item_name' => $actualItemName, // Ambil dari relasi items
                'unit_id' => $unitId,
                'warehouse_id' => $unitId,
                'staff_id' => $staffId,
                'quantity' => $qty,
                'conversion_factor' => $data['conversion_factor'],
                'unit' => $unit,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'receive_date' => $today,
                'status' => 'approved',
                'is_draft' => 0,
                'submitted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            
            DB::table('submissions')->insert($submissionData);
        }

        // ================================================================
        // 3. INSERT STOCKS
        // ================================================================
        
        foreach ($stockData as $data) {
            $itemName = $data['item_name'];
            $qty = $data['quantity'];
            
            // Get or create item
            $itemId = $getOrCreateItemId($itemName, $data['unit']);
            
            // Skip jika stock sudah ada
            $existingStock = DB::table('stocks')
                ->where('item_id', $itemId)
                ->where('unit_id', $unitId)
                ->first();
            
            if ($existingStock) {
                // Update quantity jika sudah ada
                DB::table('stocks')
                    ->where('id', $existingStock->id)
                    ->update([
                        'quantity' => DB::raw("quantity + $qty"),
                        'last_updated' => $now,
                        'updated_at' => $now,
                    ]);
            } else {
                // Insert baru
                DB::table('stocks')->insert([
                    'item_id' => $itemId,
                    'unit_id' => $unitId,
                    'warehouse_id' => $unitId,
                    'quantity' => $qty,
                    'last_updated' => $now,
                    'updated_at' => $now,
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
            $itemName = $data['item_name'];
            $qty = $data['quantity'];
            
            // Get or create item
            $itemId = $getOrCreateItemId($itemName, $data['unit']);
            
            $submissionId = $submissionIds[$itemId] ?? null;
            
            DB::table('stock_movements')->insert([
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'warehouse_id' => $unitId,
                'quantity' => $qty,
                'movement_type' => 'in',
                'reference_type' => 'submission',
                'reference_id' => $submissionId,
                'created_by' => $adminId,
                'created_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $unitId = 10; // Klinik

        // Hapus semua data yang terkait dengan migration ini
        // Note: Tidak menggunakan timestamp karena migration bisa di-rollback kapan saja
        
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

        // Hapus item_units untuk items yang auto-generated
        $autoGeneratedItemIds = DB::table('items')
            ->where('code', 'LIKE', '1.01.03.14.001.%')
            ->pluck('id');
        
        if ($autoGeneratedItemIds->isNotEmpty()) {
            DB::table('item_units')
                ->whereIn('item_id', $autoGeneratedItemIds)
                ->delete();
        }

        // Hapus items yang auto-generated
        DB::table('items')
            ->where('code', 'LIKE', '1.01.03.14.001.%')
            ->delete();
    }
};
