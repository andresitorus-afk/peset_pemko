<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            ['Pasar Padang Bulan', 'pasar', 3.5549, 98.6637, 'Jl. Pendidikan'],
            ['Pasar Petisah', 'pasar', 3.5930, 98.6710, 'Jl. Haji Adam Malik'],
            ['RSUD Pirngadi Medan', 'rumah_sakit', 3.5850, 98.6720, 'Jl. Brigjen Katamso No.1'],
            ['RS Haji Medan', 'rumah_sakit', 3.6070, 98.6840, 'Jl. Rumah Sakit Haji No.2'],
            ['Stasiun Medan', 'stasiun', 3.5900, 98.6770, 'Jl. Stasiun Kereta Api'],
            ['Kantor Walikota Medan', 'kantor', 3.5940, 98.6750, 'Jl. Kapten Maulana Lubis No.2'],
        ];

        foreach ($pois as [$nama, $tipe, $lat, $lon, $alamat]) {
            DB::table('poi')->insert([
                'id' => (string) Str::uuid(),
                'nama' => $nama,
                'tipe' => $tipe,
                'alamat' => $alamat,
                'latitude' => $lat,
                'longitude' => $lon,
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
