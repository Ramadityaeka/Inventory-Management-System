<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $this->seedCategory($now);
    }

    private function seedCategory($now): void
    {
        // ─── LEVEL 1 ───────────────────────────────────────────────
        $l1 = $this->upsertCategory('1', 'PERSEDIAAN', null, $now);

        // ─── LEVEL 2 ───────────────────────────────────────────────
        $l2_01 = $this->upsertCategory('1.01', 'BARANG PAKAI HABIS', $l1, $now);
        $l2_02 = $this->upsertCategory('1.02', 'BARANG TAK HABIS PAKAI', $l1, $now);
        $l2_03 = $this->upsertCategory('1.03', 'BARANG BEKAS DIPAKAI', $l1, $now);

        // ─── LEVEL 3 ───────────────────────────────────────────────
        $bahan        = $this->upsertCategory('1.01.01', 'BAHAN', $l2_01, $now);
        $sukuCadang   = $this->upsertCategory('1.01.02', 'SUKU CADANG', $l2_01, $now);
        $alatKantor   = $this->upsertCategory('1.01.03', 'ALAT/BAHAN UNTUK KEGIATAN KANTOR', $l2_01, $now);
        $obat         = $this->upsertCategory('1.01.03.14', 'OBAT-OBATAN', $alatKantor, $now);
        $dijual       = $this->upsertCategory('1.01.05', 'PERSEDIAAN UNTUK DIJUAL/DISERAHKAN', $l2_01, $now);
        $strategis    = $this->upsertCategory('1.01.06', 'PERSEDIAAN UNTUK TUJUAN STRATEGIS/BERJAGA-JAGA', $l2_01, $now);
        $natura       = $this->upsertCategory('1.01.07', 'NATURA DAN PAKAN', $l2_01, $now);
        $penelitian   = $this->upsertCategory('1.01.08', 'PERSEDIAAN PENELITIAN BIOLOGI', $l2_01, $now);

        $komponen     = $this->upsertCategory('1.02.01', 'KOMPONEN', $l2_02, $now);
        $pipa         = $this->upsertCategory('1.02.02', 'P I P A', $l2_02, $now);
        $rambu        = $this->upsertCategory('1.02.03', 'RAMBU-RAMBU', $l2_02, $now);

        $komponenBekas = $this->upsertCategory('1.03.01', 'KOMPONEN BEKAS DAN PIPA BEKAS', $l2_03, $now);

        // ═══════════════════════════════════════════════════════════
        // 1.01.01 BAHAN
        // ═══════════════════════════════════════════════════════════
        $bahanBangunan = $this->upsertCategory('1.01.01.01', 'BAHAN BANGUNAN DAN KONSTRUKSI', $bahan, $now);
        $this->insertChildren($bahanBangunan, [
            ['1.01.01.01.001', 'Aspal'],
            ['1.01.01.01.002', 'Semen'],
            ['1.01.01.01.003', 'Kaca'],
            ['1.01.01.01.004', 'Pasir'],
            ['1.01.01.01.005', 'Batu'],
            ['1.01.01.01.006', 'Cat'],
            ['1.01.01.01.007', 'Seng'],
            ['1.01.01.01.008', 'Baja'],
            ['1.01.01.01.009', 'Electro Dalas'],
            ['1.01.01.01.010', 'Patok Beton'],
            ['1.01.01.01.011', 'Tiang Beton'],
            ['1.01.01.01.012', 'Besi Beton'],
            ['1.01.01.01.013', 'Tegel'],
            ['1.01.01.01.014', 'Genteng'],
            ['1.01.01.01.015', 'Bis Beton'],
            ['1.01.01.01.016', 'Plat'],
            ['1.01.01.01.017', 'Steel Sheet Pile'],
            ['1.01.01.01.018', 'Concrete Sheet Pile'],
            ['1.01.01.01.019', 'Kawat Bronjong'],
            ['1.01.01.01.020', 'Karung'],
            ['1.01.01.01.021', 'Minyak Cat/Thinner'],
            ['1.01.01.01.999', 'Bahan Bangunan Dan Konstruksi Lainnya'],
        ], $now);

        $bahanKimia = $this->upsertCategory('1.01.01.02', 'BAHAN KIMIA', $bahan, $now);
        $this->insertChildren($bahanKimia, [
            ['1.01.01.02.001', 'Bahan Kimia Padat'],
            ['1.01.01.02.002', 'Bahan Kimia Cair'],
            ['1.01.01.02.003', 'Bahan Kimia Gas'],
            ['1.01.01.02.005', 'Bahan Kimia Nuklir'],
            ['1.01.01.02.999', 'Bahan Kimia Lainnya'],
        ], $now);

        $bahanPeledak = $this->upsertCategory('1.01.01.03', 'BAHAN PELEDAK', $bahan, $now);
        $this->insertChildren($bahanPeledak, [
            ['1.01.01.03.001', 'Anfo'],
            ['1.01.01.03.002', 'Detonator'],
            ['1.01.01.03.003', 'Dinamit'],
            ['1.01.01.03.004', 'Gelatine'],
            ['1.01.01.03.005', 'Sumbu Ledak/Api'],
            ['1.01.01.03.006', 'Amunisi'],
            ['1.01.01.03.999', 'Bahan Peledak Lainnya'],
        ], $now);

        $bahanBakar = $this->upsertCategory('1.01.01.04', 'BAHAN BAKAR DAN PELUMAS', $bahan, $now);
        $this->insertChildren($bahanBakar, [
            ['1.01.01.04.001', 'Bahan Bakar Minyak'],
            ['1.01.01.04.002', 'Minyak Pelumas'],
            ['1.01.01.04.003', 'Minyak Hydrolis'],
            ['1.01.01.04.004', 'Bahan Bakar Gas'],
            ['1.01.01.04.005', 'Batubara'],
            ['1.01.01.04.999', 'Bahan Bakar Dan Pelumas Lainnya'],
        ], $now);

        $bahanBaku = $this->upsertCategory('1.01.01.05', 'BAHAN BAKU', $bahan, $now);
        $this->insertChildren($bahanBaku, [
            ['1.01.01.05.001', 'Kawat'],
            ['1.01.01.05.002', 'Kayu'],
            ['1.01.01.05.003', 'Logam/Metalorgi'],
            ['1.01.01.05.004', 'Latex'],
            ['1.01.01.05.005', 'Biji Plastik'],
            ['1.01.01.05.006', 'Karet (Bahan Baku)'],
            ['1.01.01.05.999', 'Bahan Baku Lainnya'],
        ], $now);

        $bahanKimiaNuklir = $this->upsertCategory('1.01.01.06', 'BAHAN KIMIA NUKLIR', $bahan, $now);
        $this->insertChildren($bahanKimiaNuklir, [
            ['1.01.01.06.001', 'Uranium - 233'],
            ['1.01.01.06.002', 'Uranium - 235'],
            ['1.01.01.06.003', 'Uranium - 238'],
            ['1.01.01.06.004', 'Plutonium (PU)'],
            ['1.01.01.06.005', 'Neptarim (NP)'],
            ['1.01.01.06.006', 'Uranium Dioksida'],
            ['1.01.01.06.007', 'Thorium'],
            ['1.01.01.06.999', 'Bahan Kimia Nuklir Lainnya'],
        ], $now);

        $barangDalamProses = $this->upsertCategory('1.01.01.07', 'BARANG DALAM PROSES', $bahan, $now);
        $this->insertChildren($barangDalamProses, [
            ['1.01.01.07.001', 'Barang Dalam Proses'],
            ['1.01.01.07.999', 'Barang Dalam Proses Lainnya'],
        ], $now);

        $bahanLainnya = $this->upsertCategory('1.01.01.99', 'BAHAN LAINNYA', $bahan, $now);
        $this->insertChildren($bahanLainnya, [
            ['1.01.01.99.999', 'Bahan Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.01.02 SUKU CADANG
        // ═══════════════════════════════════════════════════════════
        $scAngkutan = $this->upsertCategory('1.01.02.01', 'SUKU CADANG ALAT ANGKUTAN', $sukuCadang, $now);
        $this->insertChildren($scAngkutan, [
            ['1.01.02.01.001', 'Suku Cadang Alat Angkutan Darat Bermotor'],
            ['1.01.02.01.002', 'Suku Cadang Alat Angkutan Darat Tak Bermotor'],
            ['1.01.02.01.003', 'Suku Cadang Alat Angkutan Apung Bermotor'],
            ['1.01.02.01.004', 'Suku Cadang Alat Angkutan Apung Tak Bermotor'],
            ['1.01.02.01.005', 'Suku Cadang Alat Angkutan Udara Bermotor'],
            ['1.01.02.01.999', 'Suku Cadang Alat Angkutan Lainnya'],
        ], $now);

        $scBesar = $this->upsertCategory('1.01.02.02', 'SUKU CADANG ALAT BESAR', $sukuCadang, $now);
        $this->insertChildren($scBesar, [
            ['1.01.02.02.001', 'Suku Cadang Alat Besar Darat'],
            ['1.01.02.02.002', 'Suku Cadang Alat Besar Apung'],
            ['1.01.02.02.003', 'Suku Cadang Alat Besar Bantu'],
            ['1.01.02.02.999', 'Suku Cadang Alat Besar Lainnya'],
        ], $now);

        $scKedokteran = $this->upsertCategory('1.01.02.03', 'SUKU CADANG ALAT KEDOKTERAN', $sukuCadang, $now);
        $this->insertChildren($scKedokteran, [
            ['1.01.02.03.001', 'Suku Cadang Alat Kedokteran Umum'],
            ['1.01.02.03.002', 'Suku Cadang Alat Kedokteran Gigi'],
            ['1.01.02.03.003', 'Suku Cadang Alat Kedokteran Keluarga Berencana'],
            ['1.01.02.03.004', 'Suku Cadang Alat Kedokteran Bedah'],
            ['1.01.02.03.005', 'Suku Cadang Alat Kedokteran Kebidanan Dan Penyakit Kandungan'],
            ['1.01.02.03.006', 'Suku Cadang Alat Kedokteran THT'],
            ['1.01.02.03.007', 'Suku Cadang Alat Kedokteran Mata'],
            ['1.01.02.03.008', 'Suku Cadang Alat Kedokteran Penyakit Dalam'],
            ['1.01.02.03.009', 'Suku Cadang Alat Kedokteran Alat Kesehatan Anak'],
            ['1.01.02.03.010', 'Suku Cadang Alat Kedokteran Poliklinik Set'],
            ['1.01.02.03.011', 'Suku Cadang Alat Kedokteran Untuk Penderita Cacat Tubuh'],
            ['1.01.02.03.012', 'Suku Cadang Alat Kedokteran Syaraf'],
            ['1.01.02.03.013', 'Suku Cadang Alat Kedokteran Jantung'],
            ['1.01.02.03.014', 'Suku Cadang Alat Kedokteran Nuklir'],
            ['1.01.02.03.015', 'Suku Cadang Alat Kedokteran Radiologi'],
            ['1.01.02.03.016', 'Suku Cadang Alat Kedokteran Kulit Dan Kelamin'],
            ['1.01.02.03.017', 'Suku Cadang Alat Kedokteran Ugd'],
            ['1.01.02.03.018', 'Suku Cadang Alat Kedokteran Hematologi'],
            ['1.01.02.03.019', 'Suku Cadang Alat Kedokteran Hewan'],
            ['1.01.02.03.999', 'Suku Cadang Alat Kedokteran Lainnya'],
        ], $now);

        $scLab = $this->upsertCategory('1.01.02.04', 'SUKU CADANG ALAT LABORATORIUM', $sukuCadang, $now);
        $this->insertChildren($scLab, [
            ['1.01.02.04.001', 'Suku Cadang Alat Laboratorium Kimia Air Taknik Penyehatan'],
            ['1.01.02.04.002', 'Suku Cadang Alat Laboratorium Micro Biologi Penyehatan'],
            ['1.01.02.04.003', 'Suku Cadang Alat Laboratorium Hidro Kimia'],
            ['1.01.02.04.004', 'Suku Cadang Alat Laboratorium Model Hidrolika'],
            ['1.01.02.04.005', 'Suku Cadang Alat Laboratorium Batuan/Geologi'],
            ['1.01.02.04.006', 'Suku Cadang Alat Laboratorium Bahan Bangunan Konstruksi'],
            ['1.01.02.04.007', 'Suku Cadang Alat Laboratorium Aspal, Cat Dan Kimia'],
            ['1.01.02.04.008', 'Suku Cadang Alat Laboratorium Mekanika Tanah Dan Batuan'],
            ['1.01.02.04.009', 'Suku Cadang Alat Laboratorium Cocok Tanam'],
            ['1.01.02.04.010', 'Suku Cadang Alat Laboratorium Logam, Mesin Dan Listrik'],
            ['1.01.02.04.011', 'Suku Cadang Alat Laboratorium Umum'],
            ['1.01.02.04.012', 'Suku Cadang Alat Laboratorium Microbiologi'],
            ['1.01.02.04.013', 'Suku Cadang Alat Laboratorium Kimia'],
            ['1.01.02.04.014', 'Suku Cadang Alat Laboratorium Patologi'],
            ['1.01.02.04.015', 'Suku Cadang Alat Laboratorium Immunologi'],
            ['1.01.02.04.016', 'Suku Cadang Alat Laboratorium Film'],
            ['1.01.02.04.017', 'Suku Cadang Alat Laboratorium Radio Isotop'],
            ['1.01.02.04.018', 'Suku Cadang Alat Laboratorium Makanan'],
            ['1.01.02.04.019', 'Suku Cadang Alat Laboratorium Aero Dinamika'],
            ['1.01.02.04.020', 'Suku Cadang Alat Laboratorium Standarisasi Kaliberasi Dan Instrum'],
            ['1.01.02.04.021', 'Suku Cadang Alat Laboratorium Farmasi'],
            ['1.01.02.04.022', 'Suku Cadang Alat Laboratorium Pemantauan Kualitas Udara'],
            ['1.01.02.04.023', 'Suku Cadang Alat Laboratorium Fisika'],
            ['1.01.02.04.024', 'Suku Cadang Alat Laboratorium Hidrodinamika'],
            ['1.01.02.04.025', 'Suku Cadang Alat Laboratorium Pengkajian Teknik Pantai'],
            ['1.01.02.04.026', 'Suku Cadang Alat Laboratorium Kematologi'],
            ['1.01.02.04.027', 'Suku Cadang Alat Laboratorium Proses Peleburan'],
            ['1.01.02.04.028', 'Suku Cadang Alat Laboratorium Pasir'],
            ['1.01.02.04.029', 'Suku Cadang Alat Laboratorium Proses Pembuatan Cetakan'],
            ['1.01.02.04.030', 'Suku Cadang Alat Laboratorium Proses Pembuatan Pola'],
            ['1.01.02.04.031', 'Suku Cadang Alat Laboratorium Metalography'],
            ['1.01.02.04.032', 'Suku Cadang Alat Laboratorium Proses Pengelasan'],
            ['1.01.02.04.033', 'Suku Cadang Alat Laboratorium Uji Proses Pengelasan'],
            ['1.01.02.04.034', 'Suku Cadang Alat Laboratorium Proses Pembuatan Logam'],
            ['1.01.02.04.035', 'Suku Cadang Alat Laboratorium Metrologie'],
            ['1.01.02.04.036', 'Suku Cadang Alat Laboratorium Proses Pelapisan Logam'],
            ['1.01.02.04.037', 'Suku Cadang Alat Laboratorium Proses Pengolahan Panas'],
            ['1.01.02.04.038', 'Suku Cadang Alat Laboratorium Proses Teknologi Tekstil'],
            ['1.01.02.04.039', 'Suku Cadang Alat Laboratorium Uji Tekstil'],
            ['1.01.02.04.040', 'Suku Cadang Alat Laboratorium Proses Teknologi Keramik'],
            ['1.01.02.04.041', 'Suku Cadang Alat Laboratorium Proses Teknologi Kulit Karet'],
            ['1.01.02.04.042', 'Suku Cadang Alat Laboratorium Uji Kulit Karet Dan Plastik'],
            ['1.01.02.04.043', 'Suku Cadang Alat Laboratorium Alat Uji Keramik'],
            ['1.01.02.04.044', 'Suku Cadang Alat Laboratorium Proses Teknologi Selulosa'],
            ['1.01.02.04.045', 'Suku Cadang Alat Laboratorium Paska Panen'],
            ['1.01.02.04.046', 'Suku Cadang Alat Laboratorium Pertanian'],
            ['1.01.02.04.047', 'Suku Cadang Alat Laboratorium Kualitas Air'],
            ['1.01.02.04.048', 'Suku Cadang Alat Laboratorium Elektronika Dan Daya'],
            ['1.01.02.04.049', 'Suku Cadang Alat Laboratorium Energi Surya'],
            ['1.01.02.04.050', 'Suku Cadang Alat Laboratorium Konversi Batubara Dan Bioma'],
            ['1.01.02.04.051', 'Suku Cadang Alat Laboratorium Oceanografi'],
            ['1.01.02.04.052', 'Suku Cadang Alat Laboratorium Perairan'],
            ['1.01.02.04.053', 'Suku Cadang Alat Laboratorium Biologi'],
            ['1.01.02.04.054', 'Suku Cadang Alat Laboratorium Geofisika'],
            ['1.01.02.04.055', 'Suku Cadang Alat Laboratorium Tambang'],
            ['1.01.02.04.056', 'Suku Cadang Alat Laboratorium Tambang Proses/Teknik Kimia'],
            ['1.01.02.04.057', 'Suku Cadang Alat Laboratorium Proses Industri'],
            ['1.01.02.04.058', 'Suku Cadang Alat Laboratorium Kesehatan Kerja'],
            ['1.01.02.04.059', 'Suku Cadang Alat Laboratorium Kearsipan'],
            ['1.01.02.04.060', 'Suku Cadang Alat Laboratorium Perikanan dan Kelautan'],
            ['1.01.02.04.999', 'Suku Cadang Alat Laboratorium Lainnya'],
        ], $now);

        $scPemancar = $this->upsertCategory('1.01.02.05', 'SUKU CADANG ALAT PEMANCAR', $sukuCadang, $now);
        $this->insertChildren($scPemancar, [
            ['1.01.02.05.001', 'Suku Cadang Alat Pemancar MF/MW'],
            ['1.01.02.05.002', 'Suku Cadang Alat Pemancar HF/SW'],
            ['1.01.02.05.003', 'Suku Cadang Alat Pemancar FHF/MF'],
            ['1.01.02.05.004', 'Suku Cadang Alat Pemancar UHF'],
            ['1.01.02.05.005', 'Suku Cadang Alat Pemancar SHF'],
            ['1.01.02.05.999', 'Suku Cadang Alat Pemancar Lainnya'],
        ], $now);

        $scStudio = $this->upsertCategory('1.01.02.06', 'SUKU CADANG ALAT STUDIO DAN KOMUNIKASI', $sukuCadang, $now);
        $this->insertChildren($scStudio, [
            ['1.01.02.06.001', 'Suku Cadang Alat Studio'],
            ['1.01.02.06.002', 'Suku Cadang Alat Komunikasi'],
            ['1.01.02.06.999', 'Suku Cadang Alat Studio Dan Komunikasi Lainnya'],
        ], $now);

        $scPertanian = $this->upsertCategory('1.01.02.07', 'SUKU CADANG ALAT PERTANIAN', $sukuCadang, $now);
        $this->insertChildren($scPertanian, [
            ['1.01.02.07.001', 'Suku Cadang Alat Pengolahan Ternak Dan Tanaman'],
            ['1.01.02.07.002', 'Suku Cadang Alat Pemeliharaan Tanaman/Ikan/Ternak'],
            ['1.01.02.07.003', 'Suku Cadang Alat Panen'],
            ['1.01.02.07.004', 'Suku Cadang Alat Penyimpanan Hasil Percobaan Pertanian'],
            ['1.01.02.07.005', 'Suku Cadang Alat Laboratorium Pertanian'],
            ['1.01.02.07.006', 'Suku Cadang Alat Prossesing'],
            ['1.01.02.07.007', 'Suku Cadang Alat Paska Panen'],
            ['1.01.02.07.008', 'Suku Cadang Alat Produksi'],
            ['1.01.02.07.999', 'Suku Cadang Alat Pertanian Lainnya'],
        ], $now);

        $scBengkel = $this->upsertCategory('1.01.02.08', 'SUKU CADANG ALAT BENGKEL', $sukuCadang, $now);
        $this->insertChildren($scBengkel, [
            ['1.01.02.08.001', 'Suku Cadang Alat Bengkel Bermesin'],
            ['1.01.02.08.002', 'Suku Cadang Alat Bengkel Tidak Bermesin'],
            ['1.01.02.08.999', 'Suku Cadang Alat Bengkel Lainnya'],
        ], $now);

        $scLainnya = $this->upsertCategory('1.01.02.99', 'SUKU CADANG LAINNYA', $sukuCadang, $now);
        $this->insertChildren($scLainnya, [
            ['1.01.02.99.999', 'Suku Cadang Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.01.03 ALAT/BAHAN UNTUK KEGIATAN KANTOR
        // ═══════════════════════════════════════════════════════════
        $atk = $this->upsertCategory('1.01.03.01', 'ALAT TULIS KANTOR', $alatKantor, $now);
        $this->insertChildren($atk, [
            ['1.01.03.01.001', 'Alat Tulis'],
            ['1.01.03.01.002', 'Tinta Tulis, Tinta Stempel'],
            ['1.01.03.01.003', 'Penjepit Kertas'],
            ['1.01.03.01.004', 'Penghapus/Korektor'],
            ['1.01.03.01.005', 'Buku Tulis'],
            ['1.01.03.01.006', 'Ordner Dan Map'],
            ['1.01.03.01.007', 'Penggaris'],
            ['1.01.03.01.008', 'Cutter (Alat Tulis Kantor)'],
            ['1.01.03.01.009', 'Pita Mesin Ketik'],
            ['1.01.03.01.010', 'Alat Perekat'],
            ['1.01.03.01.011', 'Stadler HD'],
            ['1.01.03.01.012', 'Staples'],
            ['1.01.03.01.013', 'Isi Staples'],
            ['1.01.03.01.014', 'Barang Cetakan'],
            ['1.01.03.01.015', 'Seminar Kit'],
            ['1.01.03.01.999', 'Alat Tulis Kantor Lainnya'],
        ], $now);

        $kertas = $this->upsertCategory('1.01.03.02', 'KERTAS DAN COVER', $alatKantor, $now);
        $this->insertChildren($kertas, [
            ['1.01.03.02.001', 'Kertas HVS'],
            ['1.01.03.02.002', 'Berbagai Kertas'],
            ['1.01.03.02.003', 'Kertas Cover'],
            ['1.01.03.02.004', 'Amplop'],
            ['1.01.03.02.005', 'Kop Surat'],
            ['1.01.03.02.999', 'Kertas Dan Cover Lainnya'],
        ], $now);

        $bahanCetak = $this->upsertCategory('1.01.03.03', 'BAHAN CETAK', $alatKantor, $now);
        $this->insertChildren($bahanCetak, [
            ['1.01.03.03.001', 'Transparant Sheet'],
            ['1.01.03.03.002', 'Tinta Cetak'],
            ['1.01.03.03.003', 'Plat Cetak'],
            ['1.01.03.03.004', 'Stensil Sheet'],
            ['1.01.03.03.005', 'Chenical/Bahan Kimia Cetak'],
            ['1.01.03.03.006', 'Film Cetak'],
            ['1.01.03.03.999', 'Bahan Cetak Lainnya'],
        ], $now);

        $bahanKomputer = $this->upsertCategory('1.01.03.04', 'BAHAN KOMPUTER', $alatKantor, $now);
        $this->insertChildren($bahanKomputer, [
            ['1.01.03.04.001', 'Continuous Form'],
            ['1.01.03.04.002', 'Computer File/Tempat Disket'],
            ['1.01.03.04.003', 'Pita Printer'],
            ['1.01.03.04.004', 'Tinta/Toner Printer'],
            ['1.01.03.04.005', 'Disket'],
            ['1.01.03.04.006', 'USB/Flash Disk'],
            ['1.01.03.04.007', 'kartu Memori'],
            ['1.01.03.04.008', 'CD/DVD Drive'],
            ['1.01.03.04.009', 'Harddisk Internal'],
            ['1.01.03.04.010', 'Mouse'],
            ['1.01.03.04.011', 'CD/DVD'],
            ['1.01.03.04.999', 'Bahan Komputer Lainnya'],
        ], $now);

        $perabotKantor = $this->upsertCategory('1.01.03.05', 'PERABOT KANTOR', $alatKantor, $now);
        $this->insertChildren($perabotKantor, [
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
        ], $now);

        $alatListrik = $this->upsertCategory('1.01.03.06', 'ALAT LISTRIK', $alatKantor, $now);
        $this->insertChildren($alatListrik, [
            ['1.01.03.06.001', 'Kabel Listrik'],
            ['1.01.03.06.002', 'Lampu Listrik'],
            ['1.01.03.06.003', 'Stop Kontak'],
            ['1.01.03.06.004', 'Saklar'],
            ['1.01.03.06.005', 'Stacker'],
            ['1.01.03.06.006', 'Balast'],
            ['1.01.03.06.007', 'Starter'],
            ['1.01.03.06.008', 'Vitting'],
            ['1.01.03.06.009', 'Accu'],
            ['1.01.03.06.010', 'Batu Baterai'],
            ['1.01.03.06.011', 'Stavol'],
            ['1.01.03.06.999', 'Alat Listrik Lainnya'],
        ], $now);

        $perlengkapanDinas = $this->upsertCategory('1.01.03.07', 'PERLENGKAPAN DINAS', $alatKantor, $now);
        $this->insertChildren($perlengkapanDinas, [
            ['1.01.03.07.001', 'Bahan Baku Pakaian'],
            ['1.01.03.07.002', 'Penutup Kepala'],
            ['1.01.03.07.003', 'Penutup Badan'],
            ['1.01.03.07.004', 'Penutup Tangan'],
            ['1.01.03.07.005', 'Penutup Kaki'],
            ['1.01.03.07.006', 'Atribut'],
            ['1.01.03.07.007', 'Perlengkapan Lapangan'],
            ['1.01.03.07.999', 'Perlengkapan Dinas Lainnya'],
        ], $now);

        $kaporlap = $this->upsertCategory('1.01.03.08', 'KAPORLAP DAN PERLENGKAPAN SATWA', $alatKantor, $now);
        $this->insertChildren($kaporlap, [
            ['1.01.03.08.001', 'Kaporlap dan Perlengkapan Satwa Anjing'],
            ['1.01.03.08.002', 'Kaporlap dan Perlengkapan Satwa Kuda'],
            ['1.01.03.08.999', 'Kaporlap Dan Perlengkapan Satwa Lainnya'],
        ], $now);

        $alatKantorLainnya = $this->upsertCategory('1.01.03.99', 'ALAT/BAHAN UNTUK KEGIATAN KANTOR LAINNYA', $alatKantor, $now);
        $this->insertChildren($alatKantorLainnya, [
            ['1.01.03.99.999', 'Alat/bahan Untuk Kegiatan Kantor Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.01.03.14 OBAT-OBATAN
        // ═══════════════════════════════════════════════════════════
        $this->insertChildren($obat, [
            ['1.01.03.14.001', 'Obat Cair'],
            ['1.01.03.14.002', 'Obat Padat'],
            ['1.01.03.14.003', 'Obat Gas'],
            ['1.01.03.14.004', 'Obat Serbuk/Tepung'],
            ['1.01.03.14.005', 'Obat Gel/Salep'],
            ['1.01.03.14.006', 'Alat/Obat Kontrasepsi Keluarga Berencana'],
            ['1.01.03.14.007', 'Non Alat/Obat Kontrasepsi Keluarga Berencana'],
            ['1.01.03.14.999', 'Obat Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.01.05 PERSEDIAAN UNTUK DIJUAL/DISERAHKAN
        // ═══════════════════════════════════════════════════════════
        $dijualKpd = $this->upsertCategory('1.01.05.01', 'PERSEDIAAN UNTUK DIJUAL/DISERAHKAN KEPADA MASYARAKAT', $dijual, $now);
        $this->insertChildren($dijualKpd, [
            ['1.01.05.01.001', 'Pita Cukai, Materai, Leges'],
            ['1.01.05.01.002', 'Tanah dan Bangunan'],
            ['1.01.05.01.003', 'Hewan dan Tanaman'],
            ['1.01.05.01.004', 'Peralatan dan Mesin'],
            ['1.01.05.01.005', 'Jalan, Irigasi, dan Jaringan'],
            ['1.01.05.01.006', 'Aset Tetap Lainnya'],
            ['1.01.05.01.007', 'Aset Lain-lain'],
            ['1.01.05.01.008', 'Barang Persediaan'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.01.06 PERSEDIAAN TUJUAN STRATEGIS
        // ═══════════════════════════════════════════════════════════
        $strategisSub = $this->upsertCategory('1.01.06.01', 'PERSEDIAAN UNTUK TUJUAN STRATEGIS/BERJAGA-JAGA', $strategis, $now);
        $this->insertChildren($strategisSub, [
            ['1.01.06.01.001', 'Cadangan Energi'],
            ['1.01.06.01.002', 'Cadangan Pangan'],
            ['1.01.06.01.999', 'Persediaan Untuk Tujuan Strategis/Berjaga-jaga Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.01.07 NATURA DAN PAKAN
        // ═══════════════════════════════════════════════════════════
        $naturaSub = $this->upsertCategory('1.01.07.01', 'NATURA', $natura, $now);
        $this->insertChildren($naturaSub, [
            ['1.01.07.01.001', 'Makanan/Sembako'],
            ['1.01.07.01.002', 'Minuman'],
            ['1.01.07.01.999', 'Natura Lainnya'],
        ], $now);

        $pakanSub = $this->upsertCategory('1.01.07.02', 'PAKAN', $natura, $now);
        $this->insertChildren($pakanSub, [
            ['1.01.07.02.001', 'Pakan Hewan'],
            ['1.01.07.02.002', 'Pakan Ikan'],
            ['1.01.07.02.999', 'Pakan Lainnya'],
        ], $now);

        $naturaLainnya = $this->upsertCategory('1.01.07.99', 'NATURA DAN PAKAN LAINNYA', $natura, $now);
        $this->insertChildren($naturaLainnya, [
            ['1.01.07.99.999', 'Natura Dan Pakan Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.01.08 PERSEDIAAN PENELITIAN BIOLOGI
        // ═══════════════════════════════════════════════════════════
        $penelitianSub = $this->upsertCategory('1.01.08.01', 'PERSEDIAAN PENELITIAN BIOLOGI', $penelitian, $now);
        $this->insertChildren($penelitianSub, [
            ['1.01.08.01.001', 'Hewan/Ternak'],
            ['1.01.08.01.002', 'Biota Laut/Ikan'],
            ['1.01.08.01.003', 'Tanaman'],
            ['1.01.08.01.999', 'Persediaan Penelitian Biologi Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.02.01 KOMPONEN
        // ═══════════════════════════════════════════════════════════
        $komponenJembatanBaja = $this->upsertCategory('1.02.01.01', 'KOMPONEN JEMBATAN BAJA', $komponen, $now);
        $this->insertChildren($komponenJembatanBaja, [
            ['1.02.01.01.001', 'Komponen Jembatan Bailley'],
            ['1.02.01.01.002', 'Komponen Jembatan Baja Prefab'],
            ['1.02.01.01.999', 'Komponen Jembatan Baja Lainnya'],
        ], $now);

        $komponenJembatanPratekan = $this->upsertCategory('1.02.01.02', 'KOMPONEN JEMBATAN PRATEKAN', $komponen, $now);
        $this->insertChildren($komponenJembatanPratekan, [
            ['1.02.01.02.001', 'Komponen Jembatan Pratekan Prefab'],
            ['1.02.01.02.999', 'Komponen Jembatan Pratekan Lainnya'],
        ], $now);

        $komponenPeralatan = $this->upsertCategory('1.02.01.03', 'KOMPONEN PERALATAN', $komponen, $now);
        $this->insertChildren($komponenPeralatan, [
            ['1.02.01.03.001', 'Dinamo Amper'],
            ['1.02.01.03.002', 'Dinamo Start'],
            ['1.02.01.03.003', 'Transmisi'],
            ['1.02.01.03.004', 'Injection Pump'],
            ['1.02.01.03.005', 'Karburator Unit'],
            ['1.02.01.03.006', 'Motor Hidrolik'],
            ['1.02.01.03.007', 'Engine Bensin'],
            ['1.02.01.03.008', 'Engine Diesel'],
            ['1.02.01.03.999', 'Komponen Peralatan Lainnya'],
        ], $now);

        $komponenRambu = $this->upsertCategory('1.02.01.04', 'KOMPONEN RAMBU-RAMBU', $komponen, $now);
        $this->insertChildren($komponenRambu, [
            ['1.02.01.04.001', 'Komponen Rambu-Rambu Darat'],
            ['1.02.01.04.002', 'Komponen Rambu-Rambu Udara'],
            ['1.02.01.04.999', 'Komponen Rambu-Rambu Lainnya'],
        ], $now);

        $attachment = $this->upsertCategory('1.02.01.05', 'ATTACHMENT', $komponen, $now);
        $this->insertChildren($attachment, [
            ['1.02.01.05.001', 'Blade'],
            ['1.02.01.05.002', 'Boom'],
            ['1.02.01.05.003', 'Bucket'],
            ['1.02.01.05.004', 'Scarifier'],
            ['1.02.01.05.999', 'Attachment Lainnya'],
        ], $now);

        $komponenLainnya = $this->upsertCategory('1.02.01.99', 'KOMPONEN LAINNYA', $komponen, $now);
        $this->insertChildren($komponenLainnya, [
            ['1.02.01.99.999', 'Komponen Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.02.02 PIPA
        // ═══════════════════════════════════════════════════════════
        $pipaDci = $this->upsertCategory('1.02.02.01', 'PIPA AIR BESI TUANG (DCI)', $pipa, $now);
        $this->insertChildren($pipaDci, [
            ['1.02.02.01.001', 'DCI Filter'],
            ['1.02.02.01.002', 'Pipa Air Besi Tuang'],
            ['1.02.02.01.999', 'Pipa Air Besi Tuang (DCI) Lainnya'],
        ], $now);

        $pipaAcp = $this->upsertCategory('1.02.02.02', 'PIPA ASBES SEMEN (ACP)', $pipa, $now);
        $this->insertChildren($pipaAcp, [
            ['1.02.02.02.001', 'A C P 1,0'],
            ['1.02.02.02.002', 'A C P 1,5'],
            ['1.02.02.02.003', 'A C P 2,0'],
            ['1.02.02.02.004', 'A C P 2,5'],
            ['1.02.02.02.005', 'A C P 3,0'],
            ['1.02.02.02.999', 'Pipa Asbes Semen (ACP) Lainnya'],
        ], $now);

        $pipaBaja = $this->upsertCategory('1.02.02.03', 'PIPA BAJA', $pipa, $now);
        $this->insertChildren($pipaBaja, [
            ['1.02.02.03.001', 'Pipa Baja Gelombang'],
            ['1.02.02.03.002', 'Pipa Baja Konstruksi (CSP)'],
            ['1.02.02.03.003', 'Pipa Baja Lapis Polyethelene'],
            ['1.02.02.03.004', 'Pipa Baja Lapis Seng (GIP)'],
            ['1.02.02.03.999', 'Pipa Baja Lainnya'],
        ], $now);

        $pipaBeton = $this->upsertCategory('1.02.02.04', 'PIPA BETON PRATEKAN', $pipa, $now);
        $this->insertChildren($pipaBeton, [
            ['1.02.02.04.001', 'Fitter Pipa Beton Pratekan'],
            ['1.02.02.04.002', 'Pipa Beton Pratekan'],
            ['1.02.02.04.999', 'Pipa Beton Pratekan Lainnya'],
        ], $now);

        $pipaFiber = $this->upsertCategory('1.02.02.05', 'PIPA FIBER GLASS', $pipa, $now);
        $this->insertChildren($pipaFiber, [
            ['1.02.02.05.001', 'Filter Pipa Fiber Glass'],
            ['1.02.02.05.002', 'Pipa Fiber Glass'],
            ['1.02.02.05.999', 'Pipa Fiber Glass Lainnya'],
        ], $now);

        $pipaPvc = $this->upsertCategory('1.02.02.06', 'PIPA PLASTIK PVC (UPVC)', $pipa, $now);
        $this->insertChildren($pipaPvc, [
            ['1.02.02.06.001', 'Pipa Plastik PVC'],
            ['1.02.02.06.002', 'UPVC Fitter'],
            ['1.02.02.06.999', 'Pipa Plastik PVC (UPVC) Lainnya'],
        ], $now);

        $pipaLainnya = $this->upsertCategory('1.02.02.99', 'P I P A LAINNYA', $pipa, $now);
        $this->insertChildren($pipaLainnya, [
            ['1.02.02.99.999', 'P I P A Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.02.03 RAMBU-RAMBU
        // ═══════════════════════════════════════════════════════════
        $rambuSub = $this->upsertCategory('1.02.03.01', 'RAMBU-RAMBU', $rambu, $now);
        $this->insertChildren($rambuSub, [
            ['1.02.03.01.001', 'Rambu - Rambu Lalu Lintas'],
            ['1.02.03.01.999', 'Rambu-rambu Lainnya'],
        ], $now);

        // ═══════════════════════════════════════════════════════════
        // 1.03.01 KOMPONEN BEKAS DAN PIPA BEKAS
        // ═══════════════════════════════════════════════════════════
        $komponenBekasDetail = $this->upsertCategory('1.03.01.01', 'KOMPONEN BEKAS', $komponenBekas, $now);
        $this->insertChildren($komponenBekasDetail, [
            ['1.03.01.01.001', 'Komponen Jembatan Baja Bekas'],
            ['1.03.01.01.002', 'Komponen Jembatan Pratekan Bekas'],
            ['1.03.01.01.003', 'Komponen Peralatan Bekas'],
            ['1.03.01.01.004', 'Attachment Bekas'],
            ['1.03.01.01.005', 'Kotak dan Bilik Suara'],
            ['1.03.01.01.999', 'Komponen Bekas Lainnya'],
        ], $now);

        $pipaBekas = $this->upsertCategory('1.03.01.02', 'PIPA BEKAS', $komponenBekas, $now);
        $this->insertChildren($pipaBekas, [
            ['1.03.01.02.001', 'Pipa Air Besi Tuang Bekas'],
            ['1.03.01.02.002', 'Pipa Asbes Semen Bekas'],
            ['1.03.01.02.003', 'Pipa Baja Bekas'],
            ['1.03.01.02.004', 'Pipa Beton Pratekan Bekas'],
            ['1.03.01.02.005', 'Pipa Fiber Gelas Bekas'],
            ['1.03.01.02.006', 'Pipa Plastik PVC (UPVC) Bekas'],
            ['1.03.01.02.999', 'Pipa Bekas Lainnya'],
        ], $now);

        $komponenBekasLainnya = $this->upsertCategory('1.03.01.99', 'KOMPONEN BEKAS DAN PIPA BEKAS LAINNYA', $komponenBekas, $now);
        $this->insertChildren($komponenBekasLainnya, [
            ['1.03.01.99.999', 'Komponen Bekas Dan Pipa Bekas Lainnya'],
        ], $now);
    }

    /**
     * Cari kategori by code; jika belum ada, insert dan return id-nya.
     * Jika sudah ada, skip insert dan return id yang sudah ada.
     */
    private function upsertCategory(string $code, string $name, ?int $parentId, $now): int
    {
        $existing = DB::table('categories')->where('code', $code)->value('id');

        if ($existing) {
            echo "  SKIP: {$code} {$name} (sudah ada, id={$existing})\n";
            return $existing;
        }

        $id = DB::table('categories')->insertGetId([
            'code'        => $code,
            'name'        => $name,
            'parent_id'   => $parentId,
            'description' => null,
            'is_active'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        echo "  + ADD : {$code} {$name} (id={$id})\n";
        return $id;
    }

    /**
     * Insert child items, skip jika code sudah ada.
     */
    private function insertChildren(int $parentId, array $items, $now): void
    {
        foreach ($items as [$code, $name]) {
            $exists = DB::table('categories')->where('code', $code)->exists();

            if ($exists) {
                echo "    SKIP: {$code} {$name}\n";
                continue;
            }

            DB::table('categories')->insert([
                'code'        => $code,
                'name'        => $name,
                'parent_id'   => $parentId,
                'description' => null,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            echo "    + ADD : {$code} {$name}\n";
        }
    }

    public function down(): void
    {
        // Hapus dari level terdalam ke atas berdasarkan prefix code
        $prefixes = ['1.03.01', '1.02.03', '1.02.02', '1.02.01', '1.01.08', '1.01.07',
                     '1.01.06', '1.01.05', '1.01.04', '1.01.03', '1.01.02', '1.01.01',
                     '1.01', '1.02', '1.03', '1'];

        foreach ($prefixes as $prefix) {
            $deleted = DB::table('categories')->where('code', 'like', $prefix . '%')->delete();
            if ($deleted) {
                echo "✓ Dihapus kategori dengan prefix: {$prefix} ({$deleted} rows)\n";
            }
        }
    }
};