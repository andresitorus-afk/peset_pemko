<?php

namespace App\Imports;

use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\Opd;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;

class AsetImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public array $results = [
        'success' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    private array $opdCache = [];
    private array $kategoriCache = [];

    public function collection(Collection $rows): void
    {
        DB::beginTransaction();

        try {
            $this->preloadCaches();

            foreach ($rows as $index => $row) {
                $line = $index + 2; // Excel row (1-based + header)

                try {
                    $opdId = $this->resolveOpd($row);
                    $kategoriId = $this->resolveKategori($row);

                    if (!$opdId) {
                        throw new \Exception("OPD tidak ditemukan: " . ($row['opd'] ?? $row['nama_opd'] ?? ''));
                    }
                    if (!$kategoriId) {
                        throw new \Exception("Kategori tidak ditemukan: " . ($row['kategori'] ?? $row['kode_kib'] ?? ''));
                    }

                    $kodeBarang = trim($row['kode_barang'] ?? '');
                    if (empty($kodeBarang)) {
                        throw new \Exception("Kode barang kosong");
                    }

                    // Skip if already exists
                    if (Aset::where('kode_barang', $kodeBarang)->exists()) {
                        $this->results['errors'][] = "Baris {$line}:.kode barang '{$kodeBarang}' sudah ada, dilewati";
                        $this->results['failed']++;
                        continue;
                    }

                    Aset::create([
                        'opd_id'         => $opdId,
                        'kategori_id'    => $kategoriId,
                        'kode_barang'    => $kodeBarang,
                        'register'       => trim($row['register'] ?? ''),
                        'nama_barang'    => trim($row['nama_barang'] ?? ''),
                        'tahun_perolehan'=> $this->cleanInt($row['tahun_perolehan'] ?? null),
                        'nilai_perolehan'=> $this->cleanDecimal($row['nilai_perolehan'] ?? null),
                        'nilai_buku'     => $this->cleanDecimal($row['nilai_buku'] ?? null),
                        'luas'           => $this->cleanDecimal($row['luas'] ?? null),
                        'kondisi'        => $this->normalizeKondisi($row['kondisi'] ?? 'Baik'),
                        'status'         => $this->normalizeStatus($row['status'] ?? 'Aktif'),
                        'alamat'         => trim($row['alamat'] ?? ''),
                        'keterangan'     => trim($row['keterangan'] ?? ''),
                    ]);

                    $this->results['success']++;
                } catch (\Exception $e) {
                    $this->results['errors'][] = "Baris {$line}: " . $e->getMessage();
                    $this->results['failed']++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function preloadCaches(): void
    {
        $this->opdCache = Opd::all()->mapWithKeys(fn($o) => [
            strtolower($o->kode_opd) => $o->id,
            strtolower($o->nama_opd) => $o->id,
        ])->toArray();

        $this->kategoriCache = KategoriAset::all()->mapWithKeys(fn($k) => [
            strtolower($k->kode_kib) => $k->id,
            strtolower($k->nama_kategori) => $k->id,
            strtolower($k->kode_kategori ?? '') => $k->id,
        ])->toArray();
    }

    private function resolveOpd($row): ?string
    {
        $candidates = [
            $row['kode_opd'] ?? null,
            $row['nama_opd'] ?? null,
            $row['opd'] ?? null,
        ];

        foreach ($candidates as $val) {
            if ($val && isset($this->opdCache[strtolower(trim($val))])) {
                return $this->opdCache[strtolower(trim($val))];
            }
        }

        return null;
    }

    private function resolveKategori($row): ?string
    {
        // Try kode_kategori (full code like "1.3.1.01.01.01.001"), then kode_kib, nama_kategori, kategori
        $candidates = [
            $row['kode_kategori'] ?? null,
            $row['kode_kib'] ?? null,
            $row['nama_kategori'] ?? null,
            $row['kategori'] ?? null,
        ];

        foreach ($candidates as $val) {
            if ($val && isset($this->kategoriCache[strtolower(trim($val))])) {
                return $this->kategoriCache[strtolower(trim($val))];
            }
        }

        return null;
    }

    private function cleanInt($val): ?int
    {
        $val = trim((string) ($val ?? ''));
        $val = preg_replace('/[^0-9]/', '', $val);
        return $val !== '' ? (int) $val : null;
    }

    private function cleanDecimal($val): ?string
    {
        $val = trim((string) ($val ?? ''));
        $val = preg_replace('/[^0-9.\-]/', '', $val);
        return $val !== '' ? $val : null;
    }

    private function normalizeKondisi(string $val): string
    {
        $val = strtolower(trim($val));
        return match(true) {
            str_contains($val, 'baik')      => 'Baik',
            str_contains($val, 'berat')     => 'Rusak_Berat',
            str_contains($val, 'ringan')    => 'Rusak_Ringan',
            str_contains($val, 'rusak')     => 'Rusak_Ringan',
            default => 'Baik',
        };
    }

    private function normalizeStatus(string $val): string
    {
        $val = strtolower(trim($val));
        return match(true) {
            str_contains($val, 'manfaat')   => 'Dimanfaatkan',
            str_contains($val, 'idle')      => 'Idle',
            str_contains($val, 'aktif')     => 'Aktif',
            default => 'Aktif',
        };
    }
}
