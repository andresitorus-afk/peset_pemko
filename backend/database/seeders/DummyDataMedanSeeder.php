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
use App\Models\Poi;
use Illuminate\Database\Seeder;

class DummyDataMedanSeeder extends Seeder
{
    public function run(): void
    {
        if (Aset::where('kode_barang', 'like', 'DMY%')->exists()) {
            $this->command?->info('Data dummy Medan sudah ada, dilewati.');
            return;
        }

        $this->call(JenisPemanfaatanSeeder::class);

        $opds = Opd::pluck('id')->all();
        $katTanah = KategoriAset::where('kode_kib', 'KIB A')->first()?->id;
        $katGedung = KategoriAset::where('kode_kib', 'KIB C')->first()?->id;
        $layerTanah = GisLayer::whereRaw('lower(nama_layer) like ?', ['%tanah%'])->value('id');
        $layerBangunan = GisLayer::whereRaw('lower(nama_layer) like ?', ['%gedung%'])->value('id')
            ?? GisLayer::whereRaw('lower(nama_layer) like ?', ['%bangunan%'])->value('id');
        $layerTanah ??= GisLayer::value('id');
        $layerBangunan ??= $layerTanah;
        $jenis = JenisPemanfaatan::pluck('id', 'kode')->all();
        $pihakKetiga = PihakKetiga::pluck('id')->all();
        if (!$pihakKetiga) {
            $pihakKetiga = [PihakKetiga::create([
                'nama' => 'PT. Dummy Mitra Medan', 'jenis' => 'Badan_Hukum',
                'npwp' => '00.000.000.0-000.000', 'alamat' => 'Jl. Dummy Medan', 'penanggung_jawab' => 'Dummy',
            ])->id];
        }
        $pois = Poi::where('aktif', true)->get(['latitude', 'longitude', 'tipe']);

        $count = (int) env('DUMMY_DATA_COUNT', 300);
        $statuses = ['Aktif', 'Idle', 'Dimanfaatkan'];
        $kondisi = ['Baik', 'Rusak_Ringan', 'Rusak_Berat'];
        $tipePoi = $pois->pluck('tipe')->all();

        for ($i = 1; $i <= $count; $i++) {
            $isTanah = mt_rand(0, 100) < 55;
            $kategoriId = $isTanah ? $katTanah : $katGedung;
            $luas = $isTanah ? max(150, (int) round(exp(mt_rand(650, 900) / 100))) : max(60, (int) round(exp(mt_rand(550, 700) / 100)));

            $anchor = $pois->random();
            $lat = (float) $anchor->latitude + (mt_rand(-600, 600) / 100000);
            $lon = (float) $anchor->longitude + (mt_rand(-600, 600) / 100000);

            $aset = Aset::create([
                'opd_id' => $opds[array_rand($opds)],
                'kategori_id' => $kategoriId,
                'kode_barang' => 'DMY-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'register' => 'DMY.' . $i,
                'nama_barang' => ($isTanah ? 'Tanah ' : 'Gedung ') . 'Dummy Medan No. ' . $i,
                'tahun_perolehan' => mt_rand(1998, 2020),
                'nilai_perolehan' => mt_rand(1, 50) * 1000000000,
                'nilai_buku' => mt_rand(1, 40) * 1000000000,
                'luas' => $luas,
                'kondisi' => $kondisi[mt_rand(0, 2)],
                'status' => $statuses[mt_rand(0, 2)],
                'alamat' => 'Jl. Dummy Medan No. ' . $i . ', Kota Medan',
                'keterangan' => null,
            ]);

            GisAset::create([
                'aset_id' => $aset->id,
                'layer_id' => $isTanah ? $layerTanah : $layerBangunan,
                'latitude' => $lat,
                'longitude' => $lon,
                'tipe_geometri' => 'Point',
                'sumber_koordinat' => 'Dummy',
            ]);

            $jenisKode = $this->pickJenis($isTanah, $anchor->tipe);
            Pemanfaatan::create([
                'aset_id' => $aset->id,
                'jenis_id' => $jenis[$jenisKode],
                'pihak_ketiga_id' => $pihakKetiga[array_rand($pihakKetiga)] ?? null,
                'nomor_perjanjian' => 'DMY/' . $i . '/' . mt_rand(2019, 2025),
                'tanggal_mulai' => '2025-01-01',
                'tanggal_selesai' => '2026-12-31',
                'nilai_kontrak' => (int) round($luas * mt_rand(200000, 800000), -5),
                'kontribusi_tahunan' => (int) round($luas * mt_rand(100000, 400000), -5),
                'peruntukan' => 'Pemanfaatan dummy ' . $jenisKode,
                'status' => 'Aktif',
                'created_by' => null,
            ]);
        }

        $this->command?->info("Seeder dummy selesai: {$count} aset + pemanfaatan Medan.");
    }

    private function pickJenis(bool $isTanah, string $tipePoi): string
    {
        if (!$isTanah) {
            return in_array($tipePoi, ['rumah_sakit', 'puskesmas']) ? 'PKP' : 'BSG';
        }
        if (in_array($tipePoi, ['kampus', 'sekolah'])) {
            return 'SEWA';
        }
        if (in_array($tipePoi, ['rumah_sakit', 'puskesmas', 'kantor'])) {
            return 'KSP';
        }
        if (in_array($tipePoi, ['pasar', 'mal'])) {
            return 'SEWA';
        }
        return mt_rand(0, 1) ? 'SEWA' : 'KSP';
    }
}