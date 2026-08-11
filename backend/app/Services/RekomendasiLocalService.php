<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\JenisPemanfaatan;
use App\Models\Poi;
use RuntimeException;

class RekomendasiLocalService
{
    private string $modelDir;
    private array $meta;
    private const RING = 6371.0;

    public function __construct(string $modelDir = '')
    {
        $this->modelDir = $modelDir ?: (string) storage_path('app/ml');
        $this->meta = $this->loadJson('meta.json');
    }

    private function loadJson(string $name): array
    {
        $path = "{$this->modelDir}/{$name}";
        $raw = @file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("File model tidak ditemukan: {$path}");
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException("Model tidak valid: {$path}");
        }
        return $data;
    }

    public function jarakKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * self::RING * asin(sqrt($a));
    }

    /** @return array<string,int|float> fitur sesuai feature_names di meta.json */
    public function extractFeatures(Aset $aset): array
    {
        $lat = $aset->gisAset?->latitude !== null ? (float) $aset->gisAset->latitude : null;
        $lon = $aset->gisAset?->longitude !== null ? (float) $aset->gisAset->longitude : null;

        $perType = array_fill_keys($this->meta['poi_types'], ['n' => 0, 'd' => 5.0]);
        $total = 0;
        $radius = (float) ($this->meta['radius_km'] ?? 3.0);
        if ($lat !== null && $lon !== null) {
            foreach (Poi::where('aktif', true)->get() as $poi) {
                $d = $this->jarakKm($lat, $lon, (float) $poi->latitude, (float) $poi->longitude);
                $t = $poi->tipe;
                if (!isset($perType[$t])) {
                    continue;
                }
                if ($d <= $radius) {
                    $perType[$t]['n']++;
                    $total++;
                }
                if ($d < $perType[$t]['d'] && $d <= 5.0) {
                    $perType[$t]['d'] = round($d, 4);
                }
            }
        }

        $features = [
            'opd_id' => $aset->opd_id ? (abs(crc32((string) $aset->opd_id)) % 6) + 1 : 1,
            'kategori_kib' => $this->meta['features_json'][1]['map'][$aset->kategori?->kode_kib] ?? 0,
            'kondisi' => $this->meta['features_json'][2]['map'][$aset->kondisi] ?? 0,
            'status' => $this->meta['features_json'][3]['map'][$aset->status] ?? 1,
            'luas_m2' => (float) ($aset->luas ?: 0),
            'umur_aset' => max(0, 2026 - (int) ($aset->tahun_perolehan ?: 2026)),
        ];
        foreach ($this->meta['poi_types'] as $t) {
            $features["poi_{$t}_n"] = $perType[$t]['n'];
            $features["poi_{$t}_d"] = $perType[$t]['d'];
        }
        $features['poi_total'] = $total;

        return $features;
    }

    public function recommend(Aset $aset): array
    {
        $aset->load(['kategori', 'gisAset']);
        $features = $this->extractFeatures($aset);

        $scorerY1 = XgboostScorer::fromMeta(
            "{$this->modelDir}/jenis_pemanfaatan.json",
            $this->meta['base_score_y1'],
            (int) $this->meta['num_class_y1']
        );
        $scorerY2 = XgboostScorer::fromMeta(
            "{$this->modelDir}/potensi_kontribusi.json",
            (float) $this->meta['base_score_y2'],
            1
        );
        $scorerY3 = XgboostScorer::fromMeta(
            "{$this->modelDir}/perkiraan_permintaan.json",
            $this->meta['base_score_y3'],
            (int) $this->meta['num_class_y3']
        );

        $jenisKode = $this->meta['jenis'][$this->argmax($scorerY1->predict($features))];
        $permintaanLabel = $this->meta['permintaan'][$this->argmax($scorerY3->predict($features))];
        $kontribusi = (int) round(10 ** $scorerY2->raw($features));

        return $this->buildHasil($aset, $features, $jenisKode, $permintaanLabel, $kontribusi);
    }

    private function argmax(array $probs): int
    {
        return (int) array_search(max($probs), $probs, true);
    }

    private function formatRupiah(float $v): string
    {
        return number_format((float) round($v), 0, ',', '.');
    }

    private function nearestPoi(array $features): ?array
    {
        $best = null;
        foreach ($this->meta['poi_types'] as $t) {
            $d = $features["poi_{$t}_d"];
            if ($d < 5.0 && ($best === null || $d < $best['jarak'])) {
                $best = ['tipe' => $t, 'jarak' => $d];
            }
        }
        return $best;
    }

    private function kebutuhanByType(string $tipe): array
    {
        return match ($tipe) {
            'kampus' => ['kos', 'laundry', 'coffee shop', 'percetakan/fotokopi', 'warung makan'],
            'sekolah' => ['kantin', 'tempat les/bimbel', 'koperasi', 'jasa fotokopi'],
            'rumah_sakit' => ['apotek', 'kos karyawan', 'parkir', 'laundry'],
            'puskesmas' => ['apotek', 'kantin'],
            'pasar' => ['kios kuliner', 'gudang', 'parkir'],
            'mal' => ['kios kuliner', 'kafe', 'gudang', 'parkir'],
            'stasiun' => ['parkir', 'kuliner', 'penginapan'],
            'kantor' => ['kantin', 'ruang rapat sewaan', 'parkir'],
            'tempat_budaya' => ['kafe', 'toko suvenir', 'galeri', 'ruang serbaguna'],
            default => ['ruang usaha umum', 'parkir', 'kios'],
        };
    }

    private function ideUtama(string $jenisKode, string $kategori, ?array $poi): string
    {
        $kebutuhan = $poi ? ($this->kebutuhanByType($poi['tipe'])[0] ?? 'ruang usaha') : 'ruang usaha';
        return match ($jenisKode) {
            'SEWA' => "Pemanfaatan {$kategori} sebagai {$kebutuhan} berstatus sewa berbayar",
            'PKP' => "Pinjam pakai {$kategori} untuk kegiatan penunjang berupa {$kebutuhan}",
            'KSP' => "Kerja sama pemanfaatan {$kategori} untuk pengembangan {$kebutuhan} dengan pola bagi hasil",
            'BGS' => "Pembangunan fasilitas {$kebutuhan} di atas {$kategori} dengan skema bangun-guna-serah",
            'BSG' => "Pemanfaatan sosial {$kategori} untuk ruang layanan publik",
            default => "Kerja sama infrastruktur di atas {$kategori} untuk mendukung {$kebutuhan}",
        };
    }

    private function buildHasil(Aset $aset, array $f, string $jenisKode, string $permintaan, int $kontribusi): array
    {
        $kib = $aset->kategori?->kode_kib ?? '-';
        $kategori = strtolower($aset->kategori?->nama_kategori ?? 'aset');
        $poi = $this->nearestPoi($f);
        $jenis = JenisPemanfaatan::where('kode', $jenisKode)->first();
        $kontribusiMin = (int) round($kontribusi * 0.75);
        $kontribusiMax = (int) round($kontribusi * 1.25);
        $kategoriNama = $aset->kategori?->nama_kategori ?? '-';

        $alasan = [
            "Aset berjenis {$kib} ({$kategoriNama}) seluas {$aset->luas} m² dengan kondisi {$aset->kondisi}.",
        ];
        if ($poi) {
            $kebutuhan = $this->kebutuhanByType($poi['tipe']);
            $alasan[] = "Lokasi dekat {$poi['tipe']} (jarak ~{$poi['jarak']} km), potensi pasar dari kebutuhan seperti " . implode(', ', array_slice($kebutuhan, 0, 3)) . '.';
        } else {
            $alasan[] = 'Belum ada POI dalam radius 3 km; permintaan ditopang karakteristik aset itu sendiri.';
        }
        $alasan[] = "Konteks sekitar (jumlah POI {$f['poi_total']} dalam 3 km) mendukung jenis pemanfaatan {$jenisKode}.";
        if ($jenis?->dasar_hukum) {
            $alasan[] = "Legalitas mengacu pada {$jenis->dasar_hukum}.";
        }

        $alt = [];
        foreach (['KSP', 'SEWA', 'PKP'] as $i => $k) {
            if ($k === $jenisKode || $i >= 2) {
                continue;
            }
            $alt[] = [
                'ide' => "Alternatif berupa skema {$k} untuk kebutuhan yang sama",
                'alasan' => "Mengurangi risiko bila pola utama {$jenisKode} belum optimal secara legal maupun pasar.",
            ];
        }

        return [
            'jenis_pemanfaatan' => $jenisKode,
            'ide_utama' => $this->ideUtama($jenisKode, $kategori, $poi),
            'alasan' => $alasan,
            'alternatif' => $alt,
            'perkiraan_permintaan' => $permintaan . ' — ' . ($f['poi_total'] > 3
                ? 'didukung kepadatan fasilitas/POI di sekitar lokasi.'
                : 'berdasarkan karakteristik aset dan area sekitar yang belum padat.'),
            'potensi_kontribusi' => "Rp {$this->formatRupiah($kontribusiMin)} - Rp {$this->formatRupiah($kontribusiMax)} per tahun",
            'catatan_legal' => $jenis?->dasar_hukum ?? 'Konsultasikan skema pemanfaatan dengan BPKAD/Kasubbag Hukum.',
        ];
    }
}
