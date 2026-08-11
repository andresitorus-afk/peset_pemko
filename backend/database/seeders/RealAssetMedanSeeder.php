<?php

namespace Database\Seeders;

use App\Models\Aset;
use App\Models\GisAset;
use App\Models\GisLayer;
use App\Models\JenisPemanfaatan;
use App\Models\KategoriAset;
use App\Models\Opd;
use App\Models\Pemanfaatan;
use App\Models\PihakKetiga;
use Illuminate\Database\Seeder;

class RealAssetMedanSeeder extends Seeder
{
    // nama, alamat, lat, lon, kib (A tanah / C gedung), luas m2, tahun, nilai (Rp), kondisi, status
    private const ASET = [
        ['Tanah Taman Merdeka', 'Jl. Merdeka No. 1, Medan Petisah, Kota Medan', 3.5893, 98.6819, 'A', 4200, 1980, 85000000000, 'Baik', 'Idle'],
        ['Lapangan Benteng', 'Jl. Diponegoro No. 1, Medan Baru, Kota Medan', 3.5883, 98.6835, 'A', 2800, 1975, 62000000000, 'Baik', 'Idle'],
        ['Tanah Pasar Petisah', 'Jl. Petisah No. 10, Medan Petisah, Kota Medan', 3.6060, 98.6620, 'A', 1600, 1992, 21000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Pusat Pasar', 'Jl. Sutomo No. 5, Pusat Pasar, Kota Medan', 3.5960, 98.6760, 'A', 2400, 1988, 35000000000, 'Rusak_Ringan', 'Idle'],
        ['Tanah Museum Sumut', 'Jl. H.M. Joni No. 51, Medan Polonia, Kota Medan', 3.5690, 98.6880, 'A', 3800, 1982, 78000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Kampus USU', 'Jl. Universitas No. 9, Padang Bulan, Kota Medan', 3.5624, 98.6611, 'A', 12000, 1965, 450000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Sun Plaza', 'Jl. Zainul Arifin No. 7, Petisah Tengah, Kota Medan', 3.5877, 98.6740, 'A', 2100, 1995, 92000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Centre Point', 'Jl. Jend. Gatot Subroto No. 26, Sei Sikambing D., Kota Medan', 3.5876, 98.6748, 'A', 1800, 1998, 76000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Cambridge Square', 'Jl. S. Parman No. 217, Petisah Tengah, Kota Medan', 3.5860, 98.6830, 'A', 1500, 1996, 68000000000, 'Rusak_Ringan', 'Idle'],
        ['Tanah Ring Road City Walk', 'Jl. Ring Road No. 22, Medan Petisah, Kota Medan', 3.5920, 98.6540, 'A', 2600, 2000, 88000000000, 'Baik', 'Idle'],
        ['Tanah Stasiun Medan', 'Jl. Balai Kota No. 2, Kesawan, Kota Medan', 3.5902, 98.6800, 'A', 950, 1905, 40000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Tanah Terminal Amplas', 'Jl. Sisingamangaraja No. 222, Amplas, Kota Medan', 3.5200, 98.6600, 'A', 5200, 1985, 97000000000, 'Rusak_Berat', 'Idle'],
        ['Tanah Terminal Pinang Baris', 'Jl. Pinang Baris No. 1, Medan Sunggal, Kota Medan', 3.6090, 98.6400, 'A', 6000, 1983, 105000000000, 'Rusak_Berat', 'Idle'],
        ['Tanah Pajak Ikan', 'Jl. Kolam No. 8, Pasar Merah, Kota Medan', 3.5980, 98.6830, 'A', 1400, 1990, 19000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Tanah Pasar Simpang Limun', 'Jl. Letda Sujono No. 18, Medan Tembung, Kota Medan', 3.5840, 98.7120, 'A', 1900, 1994, 25000000000, 'Baik', 'Idle'],
        ['Tanah Pasar Kampus', 'Jl. Kampus No. 4, Padang Bulan, Kota Medan', 3.5680, 98.6650, 'A', 1300, 1997, 16000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Gedung DPRD', 'Jl. Cut Nyak Dien No. 2, Petisah Tengah, Kota Medan', 3.5960, 98.6610, 'A', 1600, 1988, 52000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Rumah Dinas Walikota', 'Jl. Imam Bonjol No. 6, Petisah Tengah, Kota Medan', 3.5990, 98.6630, 'A', 2200, 1968, 48000000000, 'Rusak_Ringan', 'Idle'],
        ['Tanah RS Adam Malik', 'Jl. Bunga Lau No. 17, Kemenangan Tani, Kota Medan', 3.5946, 98.6685, 'A', 3800, 1987, 140000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah RS Elisabeth', 'Jl. H. Misbah No. 10, Jati, Kota Medan', 3.5850, 98.6740, 'A', 2900, 1985, 95000000000, 'Rusak_Ringan', 'Idle'],
        ['Tanah Kampus Nommensen', 'Jl. Sutomo No. 4A, Perintis, Kota Medan', 3.5910, 98.6630, 'A', 3400, 1954, 89000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Politeknik Medan', 'Jl. Almamater No. 1, Padang Bulan, Kota Medan', 3.5940, 98.6720, 'A', 2800, 1980, 76000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Lapangan Suka Ramai', 'Jl. HOS. Cokroaminoto No. 3, Medan Perjuangan, Kota Medan', 3.6040, 98.6720, 'A', 3100, 1986, 44000000000, 'Baik', 'Idle'],
        ['Tanah Belawan Baru', 'Jl. Belawan Bahari No. 12, Belawan I, Kota Medan', 3.7750, 98.6900, 'A', 4800, 1991, 68000000000, 'Rusak_Berat', 'Idle'],
        ['Tanah KIM Marindal', 'Jl. KIM No. 2, Mabar Hilir, Kota Medan', 3.6480, 98.6630, 'A', 7200, 1996, 125000000000, 'Baik', 'Idle'],
        ['Tanah RSUD Pirngadi', 'Jl. Prof. H.M. Yamin SH No. 47, Perintis, Kota Medan', 3.5860, 98.6850, 'A', 2600, 1970, 88000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Tanah SMA Negeri 1 Medan', 'Jl. Cirebon No. 9, Medan Barat, Kota Medan', 3.5820, 98.6750, 'A', 1500, 1958, 34000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Tanah Masjid Raya Al Mashun', 'Jl. Sisingamangaraja No. 91, Mesjid, Kota Medan', 3.5750, 98.6880, 'A', 2200, 1909, 56000000000, 'Baik', 'Dimanfaatkan'],
        ['Tanah Istana Maimun', 'Jl. Sultan Ma\'mun Al Rasyid No. 66, Aur, Kota Medan', 3.5734, 98.6847, 'A', 1900, 1891, 47000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Gedung Kantor BKD', 'Jl. Stasiun No. 21, Kesawan, Kota Medan', 3.5895, 98.6805, 'C', 2100, 1990, 38000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Balai Kota Lama', 'Jl. Balai Kota No. 3, Kesawan, Kota Medan', 3.5905, 98.6790, 'C', 1800, 1910, 32000000000, 'Rusak_Berat', 'Idle'],
        ['Gedung PDAM Tirtanadi', 'Jl. H.M. Joni No. 1, Medan Polonia, Kota Medan', 3.5910, 98.6830, 'C', 2600, 1920, 41000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Gedung Tjong A Fie', 'Jl. Jend. A. Yani No. 105, Kesawan, Kota Medan', 3.5907, 98.6795, 'C', 1400, 1900, 28000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Museum BKS', 'Jl. Balaikota No. 6, Kesawan, Kota Medan', 3.5898, 98.6812, 'C', 1600, 1935, 22000000000, 'Rusak_Ringan', 'Idle'],
        ['Gedung Kantor Gubernur', 'Jl. Diponegoro No. 30, Medan Baru, Kota Medan', 3.5915, 98.6835, 'C', 3400, 1980, 96000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung RSUP Adam Malik', 'Jl. Bunga Lau No. 17, Kemenangan Tani, Kota Medan', 3.5952, 98.6678, 'C', 6800, 1994, 240000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung RS Elisabeth', 'Jl. H. Misbah No. 10, Jati, Kota Medan', 3.5847, 98.6747, 'C', 4200, 1998, 130000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung USU Rectorate', 'Jl. Universitas No. 9, Padang Bulan, Kota Medan', 3.5620, 98.6617, 'C', 5200, 1960, 170000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Gedung Pusat Bahasa USU', 'Jl. Prof. A. Sofyan No. 3, Padang Bulan, Kota Medan', 3.5630, 98.6630, 'C', 1800, 1985, 45000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Aula Kampus USU', 'Jl. Dr. Mansyur No. 9, Padang Bulan, Kota Medan', 3.5650, 98.6600, 'C', 2400, 1978, 52000000000, 'Rusak_Berat', 'Idle'],
        ['Gedung Sun Plaza Mall', 'Jl. Zainul Arifin No. 7, Petisah Tengah, Kota Medan', 3.5880, 98.6735, 'C', 8800, 2003, 380000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Centre Point Mall', 'Jl. Jend. Gatot Subroto No. 26, Sei Sikambing D., Kota Medan', 3.5872, 98.6755, 'C', 7400, 2010, 320000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Medan Fair Plaza', 'Jl. Jend. Gatot Subroto No. 30, Sei Sikambing D., Kota Medan', 3.5900, 98.6760, 'C', 5600, 2012, 250000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Plaza Indonesia Medan', 'Jl. Iskandar Muda No. 1, Merdeka, Kota Medan', 3.5840, 98.6700, 'C', 4800, 2005, 210000000000, 'Rusak_Ringan', 'Idle'],
        ['Gedung Hotel Grand Mercure', 'Jl. Jend. Sudirman No. 46, Petisah Tengah, Kota Medan', 3.5940, 98.6680, 'C', 3800, 2015, 150000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Hotel Danau Toba', 'Jl. Imam Bonjol No. 4, Petisah Tengah, Kota Medan', 3.6000, 98.6620, 'C', 3200, 1995, 88000000000, 'Rusak_Ringan', 'Idle'],
        ['Gedung Wisma Kinasih', 'Jl. Thamrin No. 7, Petisah Tengah, Kota Medan', 3.5890, 98.6660, 'C', 2100, 1992, 42000000000, 'Rusak_Berat', 'Idle'],
        ['Gedung Graha Merah Putih', 'Jl. Putri Hijau No. 10, Medan Petisah, Kota Medan', 3.6010, 98.6550, 'C', 2600, 2000, 56000000000, 'Baik', 'Idle'],
        ['Gedung Graha Manunggal', 'Jl. Asrama No. 22, Medan Sunggal, Kota Medan', 3.6080, 98.6480, 'C', 1900, 1998, 38000000000, 'Rusak_Ringan', 'Idle'],
        ['Gedung Kantor Wilayah Pajak', 'Jl. Ahmad Yani No. 15, Kesawan, Kota Medan', 3.5900, 98.6780, 'C', 2800, 1994, 64000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Kantor Pos Besar', 'Jl. Balai Kota No. 1, Kesawan, Kota Medan', 3.5912, 98.6818, 'C', 2300, 1920, 36000000000, 'Rusak_Ringan', 'Idle'],
        ['Gedung Bioskop Kesawan', 'Jl. Jend. A. Yani No. 11, Kesawan, Kota Medan', 3.5917, 98.6810, 'C', 1200, 1938, 15000000000, 'Rusak_Berat', 'Idle'],
        ['Gedung Pasar Baru', 'Jl. Gang Baru No. 2, Petisah Tengah, Kota Medan', 3.5930, 98.6710, 'C', 3100, 1985, 47000000000, 'Rusak_Berat', 'Dimanfaatkan'],
        ['Gedung Pasar Petisah', 'Jl. Petisah No. 10, Medan Petisah, Kota Medan', 3.6055, 98.6615, 'C', 2600, 1996, 52000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Pasar Halat', 'Jl. Halat No. 3, Medan Area, Kota Medan', 3.5800, 98.6900, 'C', 1700, 1988, 24000000000, 'Rusak_Berat', 'Idle'],
        ['Gedung Pasar Aksara', 'Jl. Aksara No. 4, Medan Area, Kota Medan', 3.5780, 98.6920, 'C', 1500, 1990, 21000000000, 'Rusak_Ringan', 'Idle'],
        ['Gedung SMA Santo Thomas', 'Jl. Jend. Sudirman No. 70, Medan Petisah, Kota Medan', 3.5960, 98.6650, 'C', 2000, 1945, 34000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Gedung SMP Negeri 3 Medan', 'Jl. Pelajar No. 8, Sukaraja, Kota Medan', 3.6050, 98.6750, 'C', 1300, 1965, 19000000000, 'Rusak_Ringan', 'Idle'],
        ['Gedung SDN 060856 Medan', 'Jl. Cinta Karya No. 5, Sukaramai, Kota Medan', 3.6070, 98.6800, 'C', 900, 1970, 11000000000, 'Rusak_Ringan', 'Dimanfaatkan'],
        ['Gedung Gedung PKM Sering', 'Jl. Sering No. 12, Sering, Kota Medan', 3.5990, 98.6850, 'C', 1100, 1992, 16000000000, 'Baik', 'Dimanfaatkan'],
        ['Gedung Toko Tjong A Fie', 'Jl. Jend. A. Yani No. 97, Kesawan, Kota Medan', 3.5903, 98.6798, 'C', 800, 1902, 12000000000, 'Rusak_Ringan', 'Idle'],
    ];

    public function run(): void
    {
        if (Aset::where('kode_barang', 'like', 'REAL%')->exists()) {
            $this->command?->info('Data aset realistis Medan sudah ada, dilewati.');
            return;
        }

        $opds = Opd::pluck('id')->all();
        $katTanah = KategoriAset::where('kode_kib', 'KIB A')->value('id');
        $katGedung = KategoriAset::where('kode_kib', 'KIB C')->value('id');
        $layerTanah = GisLayer::whereRaw('lower(nama_layer) like ?', ['%tanah%'])->value('id');
        $layerBangunan = GisLayer::whereRaw('lower(nama_layer) like ?', ['%gedung%'])->value('id')
            ?? GisLayer::whereRaw('lower(nama_layer) like ?', ['%bangunan%'])->value('id');
        $layerTanah ??= GisLayer::value('id');
        $layerBangunan ??= $layerTanah;

        $pihakKetiga = PihakKetiga::pluck('id')->first();
        if (!$pihakKetiga) {
            $pihakKetiga = PihakKetiga::create([
                'nama' => 'PT. Mitra Aset Medan', 'jenis' => 'Badan_Hukum',
                'npwp' => '01.234.567.8-001.000', 'alamat' => 'Jl. Merdeka No. 1, Medan', 'penanggung_jawab' => 'Dinas Aset',
            ])->id;
        }

        $i = 0;
        foreach (self::ASET as [$nama, $alamat, $lat, $lon, $kib, $luas, $tahun, $nilai, $kondisi, $status]) {
            $i++;
            $isTanah = $kib === 'A';
            $aset = Aset::create([
                'opd_id' => $opds[array_rand($opds)],
                'kategori_id' => $isTanah ? $katTanah : $katGedung,
                'kode_barang' => 'REAL-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'register' => 'REAL.' . $i,
                'nama_barang' => $nama,
                'tahun_perolehan' => $tahun,
                'nilai_perolehan' => $nilai,
                'nilai_buku' => (int) round($nilai * 0.7, -6),
                'luas' => $luas,
                'kondisi' => $kondisi,
                'status' => $status,
                'alamat' => $alamat,
                'keterangan' => 'Aset pemetaan lokasi realistis Kota Medan (demonstrasi).',
            ]);

            GisAset::create([
                'aset_id' => $aset->id,
                'layer_id' => $isTanah ? $layerTanah : $layerBangunan,
                'latitude' => $lat,
                'longitude' => $lon,
                'tipe_geometri' => 'Point',
                'sumber_koordinat' => 'Landmark',
            ]);

            if ($status === 'Dimanfaatkan') {
                Pemanfaatan::create([
                    'aset_id' => $aset->id,
                    'jenis_id' => $isTanah ? JenisPemanfaatan::where('kode', 'SEWA')->value('id') : JenisPemanfaatan::where('kode', 'BSG')->value('id'),
                    'pihak_ketiga_id' => $pihakKetiga,
                    'nomor_perjanjian' => 'REAL/' . $i . '/' . mt_rand(2019, 2025),
                    'tanggal_mulai' => '2025-01-01',
                    'tanggal_selesai' => '2026-12-31',
                    'nilai_kontrak' => (int) round($luas * mt_rand(200000, 600000), -5),
                    'kontribusi_tahunan' => (int) round($luas * mt_rand(100000, 300000), -5),
                    'peruntukan' => 'Pemanfaatan ' . $nama,
                    'status' => 'Aktif',
                    'created_by' => null,
                ]);
            }
        }

        $this->command?->info('Seeder aset realistis Medan selesai: ' . count(self::ASET) . ' aset + pemanfaatan.');
    }
}
