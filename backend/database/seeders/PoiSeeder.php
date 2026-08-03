<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoiSeeder extends Seeder
{
    public function run(): void
    {
        $pois = [
            ['USU', 'kampus', 3.5630, 98.6568, 'Jl. Dr. A. Sofyan, Padang Bulan'],
            ['UMSU', 'kampus', 3.5899, 98.6779, 'Jl. Kapten Muktar Basri No.3'],
            ['UNIMED', 'kampus', 3.6060, 98.6790, 'Jl. Willem Iskandar Psr V'],
            ['Politeknik Negeri Medan', 'kampus', 3.5956, 98.6883, 'Jl. Medan Tenggara No.2'],
            ['UIN Sumatera Utara', 'kampus', 3.6320, 98.6810, 'Jl. Lapangan Golf No.120'],

            ['Mal Medan Fair', 'mal', 3.5830, 98.6750, 'Jl. Kapten Maulana Lubis No.8'],
            ['Sun Plaza', 'mal', 3.5816, 98.6810, 'Jl. Zainul Arifin No.7'],
            ['Cambridge City Square', 'mal', 3.5874, 98.6790, 'Jl. S. Parman No.217'],
            ['Delipark Mall', 'mal', 3.6003, 98.6720, 'Jl. Putri Hijau No.10'],
            ['Ring Road City Walks', 'mal', 3.6260, 98.6710, 'Jl. Ring Road No.17'],
            ['Centre Point Mall', 'mal', 3.5890, 98.6790, 'Jl. Jawa'],
            ['Grand City Mall', 'mal', 3.5945, 98.6920, 'Jl. Gatot Subroto'],
            ['Hermes Palace Mall', 'mal', 3.5670, 98.6680, 'Jl. Gaharu No.100'],

            ['SMA Negeri 1 Medan', 'sekolah', 3.5895, 98.6840, 'Jl. Cik Ditiro No.1'],
            ['SMA Negeri 2 Medan', 'sekolah', 3.6050, 98.6880, 'Jl. Karang Sari'],
            ['SMA Negeri 3 Medan', 'sekolah', 3.5930, 98.6720, 'Jl. HOS Cokroaminoto'],
            ['SMP Negeri 1 Medan', 'sekolah', 3.5880, 98.6830, 'Jl. Teuku Cik Ditiro No.4'],
            ['SMA Sutomo 1 Medan', 'sekolah', 3.5910, 98.6740, 'Jl. S. Parman No.130'],
            ['SMA Methodist 1 Medan', 'sekolah', 3.5920, 98.6760, 'Jl. Thamrin No.16'],
            ['Perguruan Buddhi', 'sekolah', 3.5820, 98.6950, 'Jl. Imam Bonjol No.41'],

            ['RSUD Pirngadi Medan', 'rumah_sakit', 3.5850, 98.6720, 'Jl. Brigjen Katamso No.1'],
            ['RS Haji Medan', 'rumah_sakit', 3.6070, 98.6840, 'Jl. Rumah Sakit Haji No.2'],
            ['RS Adam Malik', 'rumah_sakit', 3.6100, 98.6810, 'Jl. Bunga Lau No.17'],
            ['RS Mitra Medika', 'rumah_sakit', 3.5830, 98.6930, 'Jl. Aksara No.85'],
            ['RS Murni Teguh', 'rumah_sakit', 3.5880, 98.6850, 'Jl. Imam Bonjol No.6'],
            ['RS Santa Elisabeth', 'rumah_sakit', 3.5890, 98.6650, 'Jl. Haji Misbah No.36'],
            ['RS Permata Bunda', 'rumah_sakit', 3.6080, 98.6960, 'Jl. Gagak Hitam No.39'],
            ['RS Columbia Asia', 'rumah_sakit', 3.5960, 98.6810, 'Jl. Listrik No.2A'],
            ['Puskesmas Medan Baru', 'puskesmas', 3.5950, 98.6650, 'Jl. Sultan Hasanuddin'],
            ['Puskesmas Padang Bulan', 'puskesmas', 3.5560, 98.6650, 'Jl. Dr. Mansyur'],

            ['Stasiun Medan', 'stasiun', 3.5900, 98.6770, 'Jl. Stasiun Kereta Api'],
            ['Kantor Walikota Medan', 'kantor', 3.5940, 98.6750, 'Jl. Kapten Maulana Lubis No.2'],

            ['Pasar Padang Bulan', 'pasar', 3.5549, 98.6637, 'Jl. Pendidikan'],
            ['Pasar Petisah', 'pasar', 3.5930, 98.6710, 'Jl. Haji Adam Malik'],
            ['Pasar Aksara', 'pasar', 3.5820, 98.6930, 'Jl. Aksara No.88'],

            ['Istana Maimun', 'tempat_budaya', 3.5764, 98.6842, 'Jl. Brigjen Katamso'],
            ['Masjid Raya Al Mashun', 'tempat_budaya', 3.5751, 98.6839, 'Jl. Sisingamangaraja No.1'],
            ['Tjong A Fie Mansion', 'tempat_budaya', 3.5870, 98.6820, 'Jl. Ahmad Yani No.105'],
            ['Museum Negeri Sumatera Utara', 'tempat_budaya', 3.5890, 98.6550, 'Jl. H.M. Joni No.51'],
            ['Taman Budaya Sumatera Utara', 'tempat_budaya', 3.5940, 98.6680, 'Jl. Perintis Kemerdekaan No.33'],
            ['Lapangan Merdeka Medan', 'tempat_budaya', 3.5922, 98.6782, 'Jl. Balai Kota'],
            ['Gedung London Sumatra', 'tempat_budaya', 3.5880, 98.6760, 'Jl. Imam Bonjol No.1'],
        ];

        foreach ($pois as [$nama, $tipe, $lat, $lon, $alamat]) {
            DB::table('poi')->updateOrInsert(
                ['nama' => $nama],
                [
                    'tipe' => $tipe,
                    'alamat' => $alamat,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'aktif' => true,
                    'updated_at' => now(),
                ],
            );
        }
    }
}
