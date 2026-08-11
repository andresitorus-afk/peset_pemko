<?php

namespace Database\Seeders;

use App\Models\JenisPemanfaatan;
use Illuminate\Database\Seeder;

class JenisPemanfaatanSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['SEWA', 'Sewa', 'Perda No. 12/2016 tentang Retribusi Pemakaian Kekayaan Daerah; dimaksimalkan masa sewa 5 tahun.'],
            ['PKP', 'Pinjam Pakai', 'Peraturan Menteri Keuangan RI No. 205/PMK.06/2016 tentang Pinjam Pakai Barang Milik Daerah.'],
            ['KSP', 'Kerja Sama Pemanfaatan', 'Perwal No. 4/2020 tentang Kerja Sama Pemanfaatan Aset Daerah; pola bagi hasil, diawasi BPKAD.'],
            ['BGS', 'Bangun Guna Serah', 'Perda No. 12/2016; mitra membangun, mengelola dalam jangka waktu tertentu, lalu diserahkan.'],
            ['BSG', 'Bangun Serah Guna', 'Peraturan Gubernur Sumut; pemda membangun, mitra mengelola, kemudian dikembalikan.'],
            ['KSPI', 'KSP untuk Infrastruktur', 'Peraturan Menteri Dalam Negeri; kerja sama pemanfaatan lahan untuk penyediaan infrastruktur.'],
        ];
        foreach ($rows as [$kode, $nama, $ketentuan]) {
            JenisPemanfaatan::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'ketentuan' => $ketentuan]
            );
        }
    }
}