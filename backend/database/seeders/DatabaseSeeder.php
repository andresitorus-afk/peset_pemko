<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\DokumenPemanfaatan;
use App\Models\FotoAset;
use App\Models\GisAset;
use App\Models\GisLayer;
use App\Models\JenisPemanfaatan;
use App\Models\KategoriAset;
use App\Models\Opd;
use App\Models\Pemanfaatan;
use App\Models\PihakKetiga;
use App\Models\RiwayatAset;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Petugas', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->call(KategoriAsetSeeder::class);

        $admin = User::create(['name' => 'Admin Pemko', 'email' => 'admin@pemkomedan.go.id', 'password' => bcrypt('password')]);
        $petugas = User::create(['name' => 'Petugas Aset', 'email' => 'petugas@pemkomedan.go.id', 'password' => bcrypt('password')]);

        $superAdminRole = DB::table('roles')->where('name', 'Super Admin')->first()->id;
        $petugasRole = DB::table('roles')->where('name', 'Petugas')->first()->id;
        $adminRole = DB::table('roles')->where('name', 'Admin')->first()->id;

        DB::table('users')->where('email', 'admin@pemkomedan.go.id')->update(['role_id' => $superAdminRole]);
        DB::table('users')->where('email', 'petugas@pemkomedan.go.id')->update(['role_id' => $petugasRole]);

        DB::table('role_email_domains')->insert([
            ['domain' => 'pemkomedan.go.id', 'role_id' => $petugasRole, 'created_at' => now(), 'updated_at' => now()],
            ['domain' => 'admin.pemkomedan.go.id', 'role_id' => $adminRole, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // OPD
        $opd1 = Opd::create(['kode_opd' => 'OPD.001', 'nama_opd' => 'Dinas Pendidikan', 'kepala_opd' => 'Dr. H. Ahmad Siregar, M.Pd', 'nip_kepala' => '196805151993011001']);
        $opd2 = Opd::create(['kode_opd' => 'OPD.002', 'nama_opd' => 'Dinas Kesehatan', 'kepala_opd' => 'dr. Hj. Wahyu Wibisana, M.Kes', 'nip_kepala' => '197001231998031001']);
        $opd3 = Opd::create(['kode_opd' => 'OPD.003', 'nama_opd' => 'Dinas PUPR', 'kepala_opd' => 'Ir. H. Budi Setiawan, MT', 'nip_kepala' => '196703011992031001']);
        $opd4 = Opd::create(['kode_opd' => 'OPD.004', 'nama_opd' => 'Dinas Kebersihan & Pertamanan', 'kepala_opd' => 'Ir. Hj. Sri Wahyuni, MT', 'nip_kepala' => '197105101999032001']);
        $opd5 = Opd::create(['kode_opd' => 'OPD.005', 'nama_opd' => 'Dinas Perhubungan', 'kepala_opd' => 'Ir. H. Zulfadmi, MT', 'nip_kepala' => '196906121995031001']);
        $opd6 = Opd::create(['kode_opd' => 'OPD.006', 'nama_opd' => 'Dinas Pengendalian Penduduk & KB', 'kepala_opd' => 'Drs. H. Muhammad Ismail, MM', 'nip_kepala' => '196604011991031001']);

        // Kategori Aset
        $katTanah = KategoriAset::create(['kode_kib' => 'KIB A', 'nama_kategori' => 'Tanah', 'keterangan' => 'Tanah untuk keperluan dinas']);
        $katPeralatan = KategoriAset::create(['kode_kib' => 'KIB B', 'nama_kategori' => 'Peralatan dan Mesin', 'keterangan' => 'Peralatan kantor, kendaraan, mesin']);
        $katGedung = KategoriAset::create(['kode_kib' => 'KIB C', 'nama_kategori' => 'Gedung dan Bangunan', 'keterangan' => 'Gedung kantor, rumah dinas, bangunan lain']);
        $katJalan = KategoriAset::create(['kode_kib' => 'KIB D', 'nama_kategori' => 'Jalan, Irigasi, dan Jaringan', 'keterangan' => 'Jalan, jaringan air bersih, jaringan listrik']);
        $katAsetDlmPengerjaan = KategoriAset::create(['kode_kib' => 'KIB E', 'nama_kategori' => 'Aset Tetap Dalam Pengerjaan', 'keterangan' => 'Proyek pembangunan yang sedang berjalan']);
        $katLainnya = KategoriAset::create(['kode_kib' => 'KIB F', 'nama_kategori' => 'Aset Lainnya', 'keterangan' => 'Aset tak berwujud dan lain-lain']);

        // GIS Layers
        $layerTanah = GisLayer::create(['nama_layer' => 'Tanah', 'warna' => '#4CAF50', 'icon_marker' => 'landmark']);
        $layerBangunan = GisLayer::create(['nama_layer' => 'Bangunan', 'warna' => '#2196F3', 'icon_marker' => 'building']);
        $layerJalan = GisLayer::create(['nama_layer' => 'Jalan', 'warna' => '#FF9800', 'icon_marker' => 'road']);
        $layerFasilitas = GisLayer::create(['nama_layer' => 'Fasilitas Umum', 'warna' => '#9C27B0', 'icon_marker' => 'facility']);

        // Jenis Pemanfaatan
        $jSewa = JenisPemanfaatan::create(['kode' => 'SEWA', 'nama' => 'Sewa', 'dasar_hukum' => 'Perda No. 12/2016 tentang Retribusi Pemakaian Kekayaan Daerah']);
        $jPKP = JenisPemanfaatan::create(['kode' => 'PKP', 'nama' => 'Pinjam Pakai', 'dasar_hukum' => 'Peraturan Menteri Keuangan RI No. 205/PMK.06/2016']);
        $jKSP = JenisPemanfaatan::create(['kode' => 'KSP', 'nama' => 'Kerja Sama Pemanfaatan', 'dasar_hukum' => 'Perwal No. 4/2020 tentang Kerja Sama Pemanfaatan Aset Daerah']);
        $jBGS = JenisPemanfaatan::create(['kode' => 'BGS', 'nama' => 'Bagi Hasil', 'dasar_hukum' => 'Perda No. 12/2016 tentang Retribusi']);
        $jBSG = JenisPemanfaatan::create(['kode' => 'BSG', 'nama' => 'Bantuan Social', 'dasar_hukum' => 'Peraturan Gubernur Sumut']);

        // Pihak Ketiga
        $pk1 = PihakKetiga::create(['nama' => 'PT. Bank Sumut', 'jenis' => 'Badan_Hukum', 'npwp' => '01.234.567.8-012.000', 'alamat' => 'Jl. Pangeran Diponegoro No.30', 'telepon' => '061-4157111', 'email' => 'info@banksumut.co.id', 'penanggung_jawab' => 'H. Muchlis M. Thamrin']);
        $pk2 = PihakKetiga::create(['nama' => 'PT. Telkom Witel Sumut', 'jenis' => 'Badan_Hukum', 'npwp' => '01.345.678.9-012.000', 'alamat' => 'Jl. Balai Kota No.1', 'telepon' => '061-4567890', 'email' => 'weltsel@telkom.co.id', 'penanggung_jawab' => 'Ir. Bambang Setiawan']);
        $pk3 = PihakKetiga::create(['nama' => 'PD. Tirtanadi', 'jenis' => 'Pemda', 'npwp' => '01.456.789.0-012.000', 'alamat' => 'Jl. Sisingamangaraja No.92', 'telepon' => '061-7861111', 'email' => 'info@tirtanadi.co.id', 'penanggung_jawab' => 'Ir. H. Fauzi Siregar, MT']);
        $pk4 = PihakKetiga::create(['nama' => 'PT. Pertamina (Persero) MOR I', 'jenis' => 'Badan_Hukum', 'npwp' => '01.567.890.1-012.000', 'alamat' => 'Jl. Imam Bonjol No.24', 'telepon' => '061-4523333', 'email' => 'patra_daratan_i@pertamina.com', 'penanggung_jawab' => 'Alimudin Simanjuntak']);
        $pk5 = PihakKetiga::create(['nama' => 'Budi Santoso', 'jenis' => 'Perorangan', 'alamat' => 'Jl. Gatot Subroto No.45', 'telepon' => '0812-6543-2100']);
        $pk6 = PihakKetiga::create(['nama' => 'Yayasan Pendidikan Harapan', 'jenis' => 'Badan_Hukum', 'npwp' => '01.678.901.2-012.000', 'alamat' => 'Jl. HM Yamin No.65', 'telepon' => '061-3456789', 'email' => 'yayasan.ph@gmail.com', 'penanggung_jawab' => 'Drs. HM. Rizal Nasution']);

        // Aset
        $a1 = Aset::create(['opd_id' => $opd1->id, 'kategori_id' => $katTanah->id, 'kode_barang' => 'TAN-2019-001', 'register' => 'I.01001.001', 'nama_barang' => 'Tanah Kantor Dinas Pendidikan', 'tahun_perolehan' => 2005, 'nilai_perolehan' => 15000000000, 'nilai_buku' => 15000000000, 'luas' => 5000, 'kondisi' => 'Baik', 'status' => 'Dimanfaatkan', 'alamat' => 'Jl. HB Yamin No.8, Kota Medan']);
        $a2 = Aset::create(['opd_id' => $opd2->id, 'kategori_id' => $katTanah->id, 'kode_barang' => 'TAN-2019-002', 'register' => 'I.01002.001', 'nama_barang' => 'Tanah RSUD Pirngadi Medan', 'tahun_perolehan' => 1998, 'nilai_perolehan' => 25000000000, 'nilai_buku' => 25000000000, 'luas' => 45000, 'kondisi' => 'Baik', 'status' => 'Dimanfaatkan', 'alamat' => 'Jl. Brigjen Katamso No.1, Kota Medan']);
        $a3 = Aset::create(['opd_id' => $opd3->id, 'kategori_id' => $katTanah->id, 'kode_barang' => 'TAN-2020-001', 'register' => 'I.01003.001', 'nama_barang' => 'Tanah Gudang Dinas PUPR', 'tahun_perolehan' => 2010, 'nilai_perolehan' => 8000000000, 'nilai_buku' => 8000000000, 'luas' => 3000, 'kondisi' => 'Baik', 'status' => 'Idle', 'alamat' => 'Jl. Williem Iskandar, Kota Medan']);
        $a4 = Aset::create(['opd_id' => $opd1->id, 'kategori_id' => $katGedung->id, 'kode_barang' => 'GBD-2019-001', 'register' => 'II.01001.001', 'nama_barang' => 'Gedung Kantor Dinas Pendidikan Lantai 1-3', 'tahun_perolehan' => 2008, 'nilai_perolehan' => 12000000000, 'nilai_buku' => 9500000000, 'luas' => 2500, 'kondisi' => 'Baik', 'status' => 'Dimanfaatkan', 'alamat' => 'Jl. HB Yamin No.8, Kota Medan']);
        $a5 = Aset::create(['opd_id' => $opd2->id, 'kategori_id' => $katGedung->id, 'kode_barang' => 'GBD-2019-002', 'register' => 'II.01002.001', 'nama_barang' => 'Gedung Puskesmas Medan Barat', 'tahun_perolehan' => 2015, 'nilai_perolehan' => 5000000000, 'nilai_buku' => 4200000000, 'luas' => 800, 'kondisi' => 'Baik', 'status' => 'Dimanfaatkan', 'alamat' => 'Jl. Mangkubumi No.75, Kota Medan']);
        $a6 = Aset::create(['opd_id' => $opd4->id, 'kategori_id' => $katGedung->id, 'kode_barang' => 'GBD-2020-001', 'register' => 'II.01004.001', 'nama_barang' => 'Gudang Dinas Kebersihan', 'tahun_perolehan' => 2012, 'nilai_perolehan' => 3000000000, 'nilai_buku' => 2100000000, 'luas' => 1500, 'kondisi' => 'Rusak_Ringan', 'status' => 'Dimanfaatkan', 'alamat' => 'Jl. Pertahanan No.5, Kota Medan']);
        $a7 = Aset::create(['opd_id' => $opd1->id, 'kategori_id' => $katPeralatan->id, 'kode_barang' => 'PRM-2022-001', 'register' => 'III.01001.001', 'nama_barang' => 'Mobil Dinas Toyota Innova', 'tahun_perolehan' => 2022, 'nilai_perolehan' => 550000000, 'nilai_buku' => 440000000, 'kondisi' => 'Baik', 'status' => 'Aktif']);
        $a8 = Aset::create(['opd_id' => $opd3->id, 'kategori_id' => $katPeralatan->id, 'kode_barang' => 'PRM-2021-001', 'register' => 'III.01003.001', 'nama_barang' => 'Mobil Dinas Mitsubishi Pajero', 'tahun_perolehan' => 2021, 'nilai_perolehan' => 650000000, 'nilai_buku' => 455000000, 'kondisi' => 'Baik', 'status' => 'Aktif']);
        $a9 = Aset::create(['opd_id' => $opd5->id, 'kategori_id' => $katPeralatan->id, 'kode_barang' => 'PRM-2023-001', 'register' => 'III.01005.001', 'nama_barang' => 'Mobil Patroli Dinas Perhubungan', 'tahun_perolehan' => 2023, 'nilai_perolehan' => 420000000, 'nilai_buku' => 378000000, 'kondisi' => 'Baik', 'status' => 'Aktif']);
        $a10 = Aset::create(['opd_id' => $opd6->id, 'kategori_id' => $katPeralatan->id, 'kode_barang' => 'PRM-2020-001', 'register' => 'III.01006.001', 'nama_barang' => 'Meja Kerja Stainless Steel', 'tahun_perolehan' => 2020, 'nilai_perolehan' => 2500000, 'nilai_buku' => 1750000, 'kondisi' => 'Baik', 'status' => 'Aktif']);

        // GIS Aset
        GisAset::create(['aset_id' => $a1->id, 'layer_id' => $layerTanah->id, 'latitude' => 3.5917, 'longitude' => 98.6753, 'tipe_geometri' => 'Polygon', 'luas_gis' => 5000, 'polygon_geojson' => json_encode(['type' => 'Polygon', 'coordinates' => [[[98.67498,3.59138],[98.67562,3.59138],[98.67562,3.59202],[98.67498,3.59202],[98.67498,3.59138]]]]), 'sumber_koordinat' => 'GPS', 'surveyed_at' => '2024-03-15 09:00:00']);
        GisAset::create(['aset_id' => $a2->id, 'layer_id' => $layerTanah->id, 'latitude' => 3.5850, 'longitude' => 98.6720, 'tipe_geometri' => 'Polygon', 'luas_gis' => 45000, 'polygon_geojson' => json_encode(['type' => 'Polygon', 'coordinates' => [[[98.67104,3.58404],[98.67296,3.58404],[98.67296,3.58596],[98.67104,3.58596],[98.67104,3.58404]]]]), 'sumber_koordinat' => 'Survey', 'surveyed_at' => '2024-04-20 10:30:00']);
        GisAset::create(['aset_id' => $a4->id, 'layer_id' => $layerBangunan->id, 'latitude' => 3.5920, 'longitude' => 98.6760, 'tipe_geometri' => 'Polygon', 'luas_gis' => 2500, 'polygon_geojson' => json_encode(['type' => 'Polygon', 'coordinates' => [[[98.67545,3.59165],[98.67655,3.59165],[98.67655,3.59235],[98.67545,3.59235],[98.67545,3.59165]]]]), 'sumber_koordinat' => 'GPS', 'surveyed_at' => '2024-05-10 14:00:00']);
        GisAset::create(['aset_id' => $a5->id, 'layer_id' => $layerBangunan->id, 'latitude' => 3.5780, 'longitude' => 98.6600, 'tipe_geometri' => 'Polygon', 'luas_gis' => 800, 'polygon_geojson' => json_encode(['type' => 'Polygon', 'coordinates' => [[[98.65955,3.57765],[98.66045,3.57765],[98.66045,3.57835],[98.65955,3.57835],[98.65955,3.57765]]]]), 'sumber_koordinat' => 'GoogleMaps', 'surveyed_at' => '2024-06-01 08:00:00']);

        // Pemanfaatan
        $p1 = Pemanfaatan::create(['aset_id' => $a1->id, 'jenis_id' => $jSewa->id, 'pihak_ketiga_id' => $pk1->id, 'nomor_perjanjian' => 'PKS/2024/001', 'tanggal_mulai' => '2024-01-01', 'tanggal_selesai' => '2025-12-31', 'nilai_kontrak' => 2500000000, 'kontribusi_tahunan' => 1250000000, 'peruntukan' => 'ATM Center dan Kantor Cabang', 'status' => 'Aktif', 'catatan' => 'Sewa lahan untuk ATM dan kantor cabang Bank Sumut', 'created_by' => $admin->id]);
        $p2 = Pemanfaatan::create(['aset_id' => $a2->id, 'jenis_id' => $jKSP->id, 'pihak_ketiga_id' => $pk3->id, 'nomor_perjanjian' => 'KSP/2024/001', 'tanggal_mulai' => '2024-06-01', 'tanggal_selesai' => '2029-05-31', 'nilai_kontrak' => 5000000000, 'kontribusi_tahunan' => 1000000000, 'peruntukan' => 'Pipa Air Bersih', 'status' => 'Aktif', 'catatan' => 'Kerja sama pemasangan jaringan pipa PD. Tirtanadi', 'created_by' => $admin->id]);
        $p3 = Pemanfaatan::create(['aset_id' => $a3->id, 'jenis_id' => $jSewa->id, 'pihak_ketiga_id' => $pk4->id, 'nomor_perjanjian' => 'SEWA/2023/002', 'tanggal_mulai' => '2023-07-01', 'tanggal_selesai' => '2024-06-30', 'nilai_kontrak' => 800000000, 'kontribusi_tahunan' => 800000000, 'peruntukan' => 'Depot BBM', 'status' => 'Berakhir', 'catatan' => 'Sewa lahan gudang untuk depot Pertamina', 'created_by' => $petugas->id]);
        $p4 = Pemanfaatan::create(['aset_id' => $a5->id, 'jenis_id' => $jBSG->id, 'pihak_ketiga_id' => $pk5->id, 'nomor_perjanjian' => 'BGS/2024/001', 'tanggal_mulai' => '2024-03-01', 'tanggal_selesai' => '2027-02-28', 'nilai_kontrak' => 1500000000, 'kontribusi_tahunan' => 500000000, 'peruntukan' => 'Ruang Praktik', 'status' => 'Aktif', 'catatan' => 'Bantuan social berupa ruang praktik siswa', 'created_by' => $admin->id]);

        // Dokumen Pemanfaatan
        DokumenPemanfaatan::create(['pemanfaatan_id' => $p1->id, 'jenis_dokumen' => 'SK', 'nomor_dokumen' => 'SK/2024/001', 'tanggal_dokumen' => '2024-01-01', 'file_path' => 'dokumen/sk-pks-2024-001.pdf', 'file_name' => 'SK Pemanfaatan PKS/2024/001.pdf']);
        DokumenPemanfaatan::create(['pemanfaatan_id' => $p1->id, 'jenis_dokumen' => 'Perjanjian', 'nomor_dokumen' => 'PKS/2024/001', 'tanggal_dokumen' => '2024-01-01', 'file_path' => 'dokumen/perjanjian-pks-2024-001.pdf', 'file_name' => 'Perjanjian Kerja Sama PKS/2024/001.pdf']);
        DokumenPemanfaatan::create(['pemanfaatan_id' => $p2->id, 'jenis_dokumen' => 'SK', 'nomor_dokumen' => 'SK/2024/002', 'tanggal_dokumen' => '2024-06-01', 'file_path' => 'dokumen/sk-ksp-2024-001.pdf', 'file_name' => 'SK Pemanfaatan KSP/2024/001.pdf']);

        // Foto Aset
        FotoAset::create(['aset_id' => $a1->id, 'file_path' => 'foto/tan-2019-001-depan.jpg', 'caption' => 'Tampak depan Tanah Kantor Dinas Pendidikan', 'tipe' => 'Depan', 'tanggal_foto' => '2024-06-15']);
        FotoAset::create(['aset_id' => $a4->id, 'file_path' => 'foto/gbd-2019-001-depan.jpg', 'caption' => 'Tampak depan Gedung Kantor Dinas Pendidikan', 'tipe' => 'Depan', 'tanggal_foto' => '2024-06-15']);
        FotoAset::create(['aset_id' => $a5->id, 'file_path' => 'foto/gbd-2019-002-samping.jpg', 'caption' => 'Tampak samping Gedung Puskesmas Medan Barat', 'tipe' => 'Samping', 'tanggal_foto' => '2024-07-01']);

        // Riwayat Aset
        RiwayatAset::create(['aset_id' => $a1->id, 'aksi' => 'Pemanfaatan', 'deskripsi' => 'Pemanfaatan aset Tanah Kantor Dinas Pendidikan disetujui', 'user_id' => $admin->id]);
        RiwayatAset::create(['aset_id' => $a1->id, 'aksi' => 'Pemeliharaan', 'deskripsi' => 'Pemeliharaan berkala dilakukan', 'user_id' => $admin->id]);
        RiwayatAset::create(['aset_id' => $a3->id, 'aksi' => 'Mutasi', 'deskripsi' => 'Aset dipindahkan dari Dinas PUPR', 'user_id' => $admin->id]);
    }
}
