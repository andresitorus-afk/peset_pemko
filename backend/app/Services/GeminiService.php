<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\Poi;
use RuntimeException;

class GeminiService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key');
        $this->model = (string) config('services.gemini.model', 'gemini-2.0-flash');
    }

    public function jarakKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $r * asin(sqrt($a));
    }

    public function nearbyPois(?float $lat, ?float $lon, int $radiusKm = 3, int $limit = 5): array
    {
        if ($lat === null || $lon === null) {
            return [];
        }
        return Poi::query()
            ->where('aktif', true)
            ->get()
            ->map(fn (Poi $p) => [
                'nama' => $p->nama,
                'tipe' => $p->tipe,
                'jarak' => round($this->jarakKm($lat, $lon, (float) $p->latitude, (float) $p->longitude), 2),
            ])
            ->filter(fn (array $p) => $p['jarak'] <= $radiusKm)
            ->sortBy('jarak')
            ->take($limit)
            ->values()
            ->all();
    }

    public function buildPrompt(Aset $aset, array $pois): string
    {
        $kib = ($aset->kategori?->kode_kib ?? '-') . ' (' . ($aset->kategori?->nama_kategori ?? '-') . ')';
        $alamat = $aset->alamat ?: '-';
        $keterangan = $aset->keterangan ?: '-';
        $poiLines = $pois
            ? implode("\n", array_map(fn ($p) => "- {$p['nama']} ({$p['tipe']}) — {$p['jarak']} km", $pois))
            : '- Tidak ada POI terdekat.';

        return <<<PROMPT
Kamu asisten rekomendasi pemanfaatan aset daerah. Berikan rekomendasi paling detail dan spesifik.

DATA ASET:
- Nama: {$aset->nama_barang}
- Kategori: {$kib}
- Luas: {$aset->luas} m²
- Kondisi: {$aset->kondisi} | Status: {$aset->status}
- Alamat: {$alamat}
- Keterangan: {$keterangan}

POI TERDEKAT (hasil pengukuran backend, bukan tebakan):
{$poiLines}

PETA KEBUTUHAN (acu untuk alasan):
- kampus: laundry, fotokopi, kos, coffee shop, warung makan, minimarket
- sekolah: kantin, jasa fotokopi, tempat les, bimbel, koperasi
- mal: kuliner, fashion, kafe, parkir, penyimpanan
- pasar: kuliner, gudang, kios, parkir
- rumah_sakit: apotek, kantin, kos karyawan, parkir, laundry
- puskesmas: apotek, kantin
- perumahan: minimarket, warung, laundry, tempat penitipan anak
- stasiun: parkir, kuliner, penginapan
- tempat_budaya: kafe, toko suvenir, galeri, ruang serbaguna

JENIS PEMANFAATAN LEGAL (pilih kode):
SEWA, PKP (Pinjam Pakai), KSP (Kerja Sama Pemanfaatan), BGS (Bangun Guna Serah), BSG (Bangun Serah Guna), KSPI (KSP untuk Infrastruktur).

WAJIB: jawab HANYA JSON valid, tanpa markdown:
{
  "jenis_pemanfaatan": "kode",
  "ide_utama": "ide usaha/pemanfaatan paling tepat",
  "alasan": ["alasan berbasis data aset & POI"],
  "alternatif": [{"ide": "...", "alasan": "..."}],
  "perkiraan_permintaan": "rendah/sedang/tinggi + penjelasan",
  "potensi_kontribusi": "estimasi dalam Rp, beri rentang",
  "catatan_legal": "persyaratan/dasar hukum"
}

Alasan wajib merujuk data yang dikirim (POI/jarak/luas/kondisi), dilarang mengarang.
PROMPT;
    }

    public function generate(string $prompt): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum diatur di .env');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
        $payload = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.4],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 45,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Gagal terhubung ke Gemini');
        }

        $data = json_decode((string) $response, true);
        if ($httpCode >= 400) {
            throw new RuntimeException($data['error']['message'] ?? "Gemini error HTTP {$httpCode}");
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($text) || $text === '') {
            throw new RuntimeException('Respons Gemini tidak valid');
        }

        return $text;
    }

    public function parseJson(string $text): array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\s*```$/', '', $clean) ?? $clean;
        $data = json_decode(trim($clean), true);

        if (!is_array($data)) {
            $start = strpos($clean, '{');
            $end = strrpos($clean, '}');
            if ($start !== false && $end !== false) {
                $data = json_decode(substr($clean, $start, $end - $start + 1), true);
            }
        }

        if (!is_array($data)) {
            throw new RuntimeException('Respons Gemini bukan JSON valid');
        }

        return $data;
    }

    public function validateHasil(array $hasil): void
    {
        if (!isset($hasil['jenis_pemanfaatan']) || !is_string($hasil['jenis_pemanfaatan'])
            || !isset($hasil['ide_utama']) || !is_string($hasil['ide_utama'])
            || !isset($hasil['alasan']) || !is_array($hasil['alasan'])) {
            throw new RuntimeException('Respons Gemini tidak memiliki struktur yang valid');
        }
    }
}
