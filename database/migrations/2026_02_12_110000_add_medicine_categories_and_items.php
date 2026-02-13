<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  
    public function up(): void
    {
        if (!DB::table('categories')->where('code', '1.01.03.14')->exists()) {
            DB::table('categories')->insert([
                'code'       => '1.01.03.14',
                'name'       => 'OBAT-OBATAN',
                'parent_id'  => 1, // Asumsi parent_id 1 = kategori 1.01.03
                'description'=> 'Kategori untuk semua jenis obat-obatan',
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $parentCategoryId = DB::table('categories')->where('code', '1.01.03.14')->value('id');

        $subCategories = [
            ['code' => '1.01.03.14.001', 'name' => 'Obat Cair',                                    'description' => 'Obat dalam bentuk cair/sirup/injeksi'],
            ['code' => '1.01.03.14.002', 'name' => 'Obat Padat',                                   'description' => 'Obat dalam bentuk tablet/kapsul'],
            ['code' => '1.01.03.14.003', 'name' => 'Obat Gas',                                     'description' => 'Obat dalam bentuk gas/inhaler/spray'],
            ['code' => '1.01.03.14.004', 'name' => 'Obat Serbuk/Tepung',                           'description' => 'Obat dalam bentuk serbuk/powder'],
            ['code' => '1.01.03.14.005', 'name' => 'Obat Gel/Salep',                               'description' => 'Obat dalam bentuk salep/krim/gel'],
            ['code' => '1.01.03.14.006', 'name' => 'Alat/Obat Kontrasepsi Keluarga Berencana',     'description' => 'Alat dan obat untuk KB'],
            ['code' => '1.01.03.14.007', 'name' => 'Non Alat/Obat Kontrasepsi Keluarga Berencana', 'description' => 'Non KB'],
            ['code' => '1.01.03.14.999', 'name' => 'Obat Lainnya',                                 'description' => 'Obat yang tidak termasuk kategori lain'],
        ];

        foreach ($subCategories as $category) {
            if (!DB::table('categories')->where('code', $category['code'])->exists()) {
                DB::table('categories')->insert([
                    'code'       => $category['code'],
                    'name'       => $category['name'],
                    'parent_id'  => $parentCategoryId,
                    'description'=> $category['description'],
                    'is_active'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }


        $categoryIds = [
            '1.01.03.14.001' => DB::table('categories')->where('code', '1.01.03.14.001')->value('id'),
            '1.01.03.14.002' => DB::table('categories')->where('code', '1.01.03.14.002')->value('id'),
            '1.01.03.14.003' => DB::table('categories')->where('code', '1.01.03.14.003')->value('id'),
            '1.01.03.14.005' => DB::table('categories')->where('code', '1.01.03.14.005')->value('id'),
            '1.01.03.14.999' => DB::table('categories')->where('code', '1.01.03.14.999')->value('id'),
        ];


        $items = [
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000018', 'name' => 'TOLAK ANGIN @ 12 SACHET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000028', 'name' => 'OBAT KUMUR TOTAL CARE COOL MINT @250 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000029', 'name' => 'CENFRESH TM MINIDOSE 0,6 ML @20 BUAH', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000033', 'name' => 'CAIRAN INFUS NACL @100 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000034', 'name' => 'CAIRAN INFUS NACL @500 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000035', 'name' => 'CAIRAN INFUS RINGER LAKTAT @500 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000065', 'name' => 'CERINI SIRUP@60 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000072', 'name' => 'TEMPRA SIRUP ANAK @ 60 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000075', 'name' => 'MINYAK KAYU PUTIH CAP LANG @ 15 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000078', 'name' => 'NEUROBION 5000 MG INJEKSI', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000086', 'name' => 'SUCRALFAT 100 ML', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000095', 'name' => 'TANTRUM VERDE @60 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000110', 'name' => 'HUFAGRIP FLU & BATUK SIRUP @60ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000114', 'name' => 'TOLAK ANGIN SIDO MUNCUL @ 12 SACHET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000116', 'name' => 'DECADRYL EXPECTORANT SYRUP @ 60 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000119', 'name' => 'LAPISIV SYRUP @ 100 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000124', 'name' => 'BETADINE KUMUR @ 100 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000127', 'name' => 'GINGGIVAL KIN', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000130', 'name' => 'CERNEVIT MULTIVITAMIN FOR IV @10 VIAL', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000131', 'name' => 'CETIRIZIN SYRUP', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000132', 'name' => 'ONOIWA MX @ 3 SACHET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000140', 'name' => 'Madu Uray natural Raw Honey in Stick 10x12 gr', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000153', 'name' => 'Nacl 0,9% ampul @ 25 ml', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000154', 'name' => 'Ranitidine Injeksi', 'unit' => 'Ampul'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000158', 'name' => 'SANADRYL EXP SYRUP @ 60 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000161', 'name' => 'PLANTACYD SYRUP 100 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000162', 'name' => 'Cendo Xytrol minidose @ 20 Strip', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000166', 'name' => 'SANADRYL DMP SYRUP @ 60 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000173', 'name' => 'Ecodin @ 10ml', 'unit' => 'Botol'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000174', 'name' => 'Otolin TT', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000176', 'name' => 'Actifed Plus Cought Merah 60 ml', 'unit' => 'Botol'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000177', 'name' => 'Bisolvon Extra Syrup 60 ml', 'unit' => 'Botol'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000178', 'name' => 'Episan 500mg/5ml 100 ml Syrup', 'unit' => 'Botol'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000179', 'name' => 'Extrace 500mg/5ml injeksi', 'unit' => 'box'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000180', 'name' => 'Topazol inj 40 mg', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.001', 'code' => '1.01.03.14.001.000181', 'name' => 'RANTIN INJ', 'unit' => 'box'],
 

            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000006', 'name' => 'ASCARDIA 80 MG @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000011', 'name' => 'DEXTEEM PLUS @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000016', 'name' => 'LAPIFED @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000023', 'name' => 'NEW DIATAB @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000025', 'name' => 'POLYSILANE TABLET KUNYAH @ 40 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000026', 'name' => 'SPASMINAL @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000039', 'name' => 'BISOPROLOL 5 MG @ 30 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000042', 'name' => 'CEFADROXIL 500 MG @ 100 CAPS', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000049', 'name' => 'GLIMEPIRID 1 MG @ 50 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000050', 'name' => 'GLIMEPIRID 2 MG @ 50 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000067', 'name' => 'RANITIDIN 150 MG @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000071', 'name' => 'ARDIUM 500MG @ 60 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000072', 'name' => 'BIOSANBE @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000082', 'name' => 'LACOLDIN @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000084', 'name' => 'LAPIBION @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000091', 'name' => 'OSKOM @ 30 KAPLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000093', 'name' => 'RHINOS SR @ 50 KAPSUL', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000095', 'name' => 'SURBEX T @ 30 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000099', 'name' => 'NEUROSANBE 5000 @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000100', 'name' => 'ALLUPURINOL 100 MG @100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000101', 'name' => 'AMLODIPINE 5 MG @100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000102', 'name' => 'AMLODIPINE 10 MG @100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000119', 'name' => 'DULCOLAX SUPPOSITORIA', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000126', 'name' => 'MERISLON 6 MG @100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000127', 'name' => 'NATUR E ADVANCE @32 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000131', 'name' => 'IMBOOST FORCE', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000146', 'name' => 'LASGAN 30 MG @20 KAPSUL', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000148', 'name' => 'MYONEP @100 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000152', 'name' => 'OXCAL @100 KAPSUL', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000153', 'name' => 'SUMAGESTIC 600 MG @100 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000161', 'name' => 'BLACKMORES MULTIVITAMIN + MINERAL @30 KAPSUL', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000167', 'name' => 'NEW DIATABS @100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000168', 'name' => 'SARI KUNYIT SIDO MUNCUL @50 KAPSUL', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000195', 'name' => 'ALPHAMOL 600 MG @ 150 KAP', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000201', 'name' => 'AVIGAN 200 MG @ 100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000203', 'name' => 'METRONIDAZOLE 500 MG', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000212', 'name' => 'ATORVASTATIN 20 MG @ 30 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000214', 'name' => 'CEFILA 100 MG @30 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000219', 'name' => 'LANSOPRAZOLE 30 MG @20 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000229', 'name' => 'NEUROBION FORTE 5000 @50 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000237', 'name' => 'RAMIPRIL 5MG @100', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000239', 'name' => 'ACLAM @30', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000243', 'name' => 'GARLIC KAPSUL SIDO MUNCUL 3500 MG@30 KAP', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000248', 'name' => 'BOOST D3 5000 @30 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000249', 'name' => 'DULCOLAX 5MG @4O TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000251', 'name' => 'EPERISONE HCI 50MG @100 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000256', 'name' => 'ONDANSETRON 4MG @30 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000267', 'name' => 'SURBEX-T @ 30 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000270', 'name' => 'FG TROCHES @ 120 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000285', 'name' => 'AMLODIPINE 5MG @ 100TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000289', 'name' => 'DIFLAM 50 MG', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000293', 'name' => 'VESPERUM 10 MG @ 100 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000294', 'name' => 'ASTHIN FORCE 4 MG @ 20 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000296', 'name' => 'CEFIXIM 100MG', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000298', 'name' => 'Fenofibrat 300 mg @ 30 tablet', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000303', 'name' => 'TAMCOCIN 500 MG @ 100 KAPSUL', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000312', 'name' => 'Remafar 8 mg', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000314', 'name' => 'ACETILSISTEIN 200 Mg @ 100 KAPSUL', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000315', 'name' => 'GLUMIN XR 500 MG @ 30 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000316', 'name' => 'RHEMAFAR 8 MG @ 100 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000318', 'name' => 'LASAL 4 MG', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000320', 'name' => 'CANDESARTAN 8 MG @ 3O TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000321', 'name' => 'EXAFLAM 50 MG @ 50 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000322', 'name' => 'HALOWELL D3 1000 UI @ 20 TAB', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000326', 'name' => 'Eperison Hcl 50 mg @ 50 tablet', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000327', 'name' => 'METFORMIN 500 MG @ 200 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000329', 'name' => 'Arcoxia 90 mg', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000330', 'name' => 'ANTARTIC KRILL OIL HEALTH & HAPPINESS @ 30 KAPSUL', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000331', 'name' => 'CLOPIDOGREL 75 MG @ 100 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000334', 'name' => 'REDOXON @ 24', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000335', 'name' => 'RENOVIT GOLD TABLET @ 100 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000336', 'name' => 'SIMVASTATIN 20 MG @ 100 TABLET', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000337', 'name' => 'VITALONG C TABLET 25 X 4', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000338', 'name' => 'VITAMIN D3 5000 IU NOW @ 120', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000340', 'name' => 'Rhemafar 4 mg @ 100 Tab', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000341', 'name' => 'Aswaganda', 'unit' => 'Botol'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000342', 'name' => 'cardioaspirin 100 mg @ 30 tab', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000343', 'name' => 'D3K2 5000IU @ 60 kapsul', 'unit' => 'Botol'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000344', 'name' => 'Galsuvmet 50/500mg @ 30 Tab', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000345', 'name' => 'Histigo 6mg @ 100 Tab', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000346', 'name' => 'Lerzin 10 mg @ 100 Tab', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000347', 'name' => 'Melatonin', 'unit' => 'Botol'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000348', 'name' => 'Mixalgin @ 100 Tab', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000349', 'name' => 'Hotin Dcl @30gr', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000354', 'name' => 'Ricoxa 90 mg', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000356', 'name' => 'Fluimucil 200 Mg @60', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000357', 'name' => 'Trolip 300 mg @30', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000358', 'name' => 'Truvaz 20 mg Tablet @30', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000359', 'name' => 'Canderin 8 Mg @30 Tablet', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000360', 'name' => 'Canderin 16 Mg @30 Tablet', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000361', 'name' => 'Amaryl 1Mg @50', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000362', 'name' => 'Blackmores Vitamin C 500 mg @60 Tablet', 'unit' => 'Botol'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000363', 'name' => 'Diagit @100 tablet', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000364', 'name' => 'Rantin Tablet @100', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000365', 'name' => 'Xonce Vitamin C 500 mg Tablet Hisap', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000366', 'name' => 'Hexavask 10 mg', 'unit' => 'box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000367', 'name' => 'Hexavask 5 mg', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000368', 'name' => 'Omegacor', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000370', 'name' => 'Candesartan 16 mg @30 Tab', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.002', 'code' => '1.01.03.14.002.000371', 'name' => 'Rillus tab', 'unit' => 'Box'],

            ['category_code' => '1.01.03.14.003', 'code' => '1.01.03.14.003.000005', 'name' => 'VICKS INHALER @0,5ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.003', 'code' => '1.01.03.14.003.000007', 'name' => 'NO PAIN SPRAY @ 100 ML', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.003', 'code' => '1.01.03.14.003.000008', 'name' => 'Oxycan', 'unit' => 'botol'],


            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000006', 'name' => 'GENTAMISIN SALEP KULIT 0,1% @ 5 GR', 'unit' => 'TUBE'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000015', 'name' => 'MOLAKRIM @ 30 GR', 'unit' => 'TUBE'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000026', 'name' => 'PASTA GIGI SENSODYNE @100 GR', 'unit' => 'TUBE'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000036', 'name' => 'HYDROCORTISON CREAM @ 5 GR', 'unit' => 'TUBE'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000049', 'name' => 'SCANDERMA PLUS SALEP 10 MG', 'unit' => 'TUBE'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000056', 'name' => 'SENSODYNE REPAIR', 'unit' => 'TUBE'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000073', 'name' => 'Enzym @ 63 gr', 'unit' => 'Pcs'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000078', 'name' => 'Fungiderm 1% @ 5 gr', 'unit' => 'Tube'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000079', 'name' => 'THROMBOPHOP GEL @ 10 GR', 'unit' => 'TUBE'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000080', 'name' => 'BETAMETASON KRIM 0,1% 5 GR', 'unit' => 'TUBE'],
            ['category_code' => '1.01.03.14.005', 'code' => '1.01.03.14.005.000084', 'name' => 'Sagestam Salep Kulit @10Gram (Ointment)', 'unit' => 'Tube'],


            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000081', 'name' => 'GLUCO DR (FAMILY DR) @ 25', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000085', 'name' => 'URIC ACID (FAMILY DR) @ 25 PCS', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000157', 'name' => 'HANSAPLAST PLESTER @100', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000215', 'name' => 'BLOOD LANCET @100 PCS', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000228', 'name' => 'STRIP TEST GLUCO DR @50 STRIP', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000230', 'name' => 'STRIP TEST FAMILY DR LIPID PRO @10 STRIP', 'unit' => 'BOTOL'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000234', 'name' => 'ONESWAB @100 PCS', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000255', 'name' => 'VICKS INHALER', 'unit' => 'PCS'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000265', 'name' => 'SIKAT GIGI SYSTEMA LION JAPAN WINGS', 'unit' => 'PCS'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000299', 'name' => 'DERMAFIX S IV 6 X 7 CM @ 50', 'unit' => 'BOX'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000318', 'name' => 'Infus set dewasa', 'unit' => 'Pcs'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000320', 'name' => 'Putih', 'unit' => 'Box'],
            ['category_code' => '1.01.03.14.999', 'code' => '1.01.03.14.999.000324', 'name' => 'FreshCare Double Inhaler+roll On', 'unit' => 'Buah'],
        ];

        // Map category_code ke category_id dan insert hanya jika belum ada
        foreach (array_chunk($items, 50) as $chunk) {
            foreach ($chunk as $item) {
                // Skip jika item sudah ada
                if (DB::table('items')->where('code', $item['code'])->exists()) {
                    continue;
                }

                // Get category_id dari code jika menggunakan category_code
                if (isset($item['category_code'])) {
                    $categoryId = DB::table('categories')->where('code', $item['category_code'])->value('id');
                    unset($item['category_code']);
                    $item['category_id'] = $categoryId;
                }

                // Insert item
                DB::table('items')->insert(array_merge($item, [
                    'supplier_id' => null,
                    'description' => null,
                    'is_active'   => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]));
            }
        }
    }


    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('items')->where('code', 'LIKE', '1.01.03.14.%')->delete();

        DB::table('categories')->whereIn('code', [
            '1.01.03.14',
            '1.01.03.14.001',
            '1.01.03.14.002',
            '1.01.03.14.003',
            '1.01.03.14.004',
            '1.01.03.14.005',
            '1.01.03.14.006',
            '1.01.03.14.007',
            '1.01.03.14.999',
        ])->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
