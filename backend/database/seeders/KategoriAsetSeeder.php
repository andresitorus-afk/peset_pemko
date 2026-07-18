<?php

namespace Database\Seeders;

use App\Models\KategoriAset;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KategoriAsetSeeder extends Seeder
{
    public function run(): void
    {
        $base = base_path();

        // Seed KIB A
        $this->importKib($base . '/KIBA.xlsx', 'KIB A', 'Tanah');

        // Seed KIB C
        $this->importKib($base . '/KIBC.xlsx', 'KIB C', 'Gedung dan Bangunan');
    }

    private function importKib(string $filePath, string $kodeKib, string $parentName): void
    {
        if (!file_exists($filePath)) {
            $this->command?->warn("File not found: {$filePath}");
            return;
        }

        $reader = IOFactory::createReader('Xlsx');
        $sheet = $reader->load($filePath)->getActiveSheet();
        $maxRow = $sheet->getHighestRow();

        // Find the header row (look for 'Uraian' or 'Akun')
        $headerRow = 1;
        for ($row = 1; $row <= min(10, $maxRow); $row++) {
            for ($col = 1; $col <= 26; $col++) {
                $val = trim((string) ($sheet->getCellByColumnAndRow($col, $row)->getValue() ?? ''));
                if ($val === 'Uraian') {
                    $headerRow = $row;
                    break 2;
                }
            }
        }

        // Find the 'Uraian' column index
        $urainCol = 0;
        for ($col = 1; $col <= 26; $col++) {
            $val = trim((string) ($sheet->getCellByColumnAndRow($col, $headerRow)->getValue() ?? ''));
            if ($val === 'Uraian') {
                $urainCol = $col;
                break;
            }
        }

        if ($urainCol === 0) {
            $this->command?->warn("Uraian column not found in {$filePath}");
            return;
        }

        // Determine data columns based on KIB type
        // KIBA: Col1=Akun, Col2=Kelompok, Col3=Jenis, Col4=Objek, Col5=Rincian, Col6=Sub, Col7=SubSub, Col8=Uraian
        // KIBC: Col6=Akun, Col7=Kelompok, Col8=Jenis, Col9=Objek, Col10=Rincian, Col11=Sub, Col12=SubSub, Col13=Uraian
        if ($kodeKib === 'KIB A') {
            $codeCols = [1, 2, 3, 4, 5, 6, 7]; // Akun, Kelompok, Jenis, Objek, Rincian, Sub, SubSub
        } else {
            $codeCols = [6, 7, 8, 9, 10, 11, 12]; // KIBC has different column positions
        }

        // Create parent category
        $parent = KategoriAset::firstOrCreate(
            ['kode_kib' => $kodeKib],
            ['nama_kategori' => $parentName, 'keterangan' => "Kategori induk {$parentName}"]
        );

        $count = 0;
        $startRow = $headerRow + 1;

        for ($row = $startRow; $row <= $maxRow; $row++) {
            $uraian = trim((string) ($sheet->getCellByColumnAndRow($urainCol, $row)->getValue() ?? ''));
            if (empty($uraian) || $uraian === 'Dst….' || $uraian === 'Dst....') {
                continue;
            }

            // Build kode_kategori from code columns
            $parts = [];
            foreach ($codeCols as $col) {
                $val = trim((string) ($sheet->getCellByColumnAndRow($col, $row)->getValue() ?? ''));
                $parts[] = $val;
            }
            $kodeKategori = implode('.', $parts);

            // Skip if already exists
            if (KategoriAset::where('kode_kategori', $kodeKategori)->exists()) {
                continue;
            }

            // Determine if this is a leaf node (has SubSub value, meaning it's the most detailed)
            $hasSubSub = !empty(trim((string) ($sheet->getCellByColumnAndRow(end($codeCols), $row)->getValue() ?? '')));
            // If it has a real value (not *), it's a leaf
            $subSubVal = trim((string) ($sheet->getCellByColumnAndRow(end($codeCols), $row)->getValue() ?? ''));
            $isLeaf = $hasSubSub && $subSubVal !== '*' && $subSubVal !== '';

            KategoriAset::create([
                'kode_kib'      => $kodeKib,
                'kode_kategori' => $kodeKategori,
                'nama_kategori' => $uraian,
                'parent_id'     => $parent->id,
                'is_leaf'       => $isLeaf,
            ]);

            $count++;
        }

        $this->command?->info("Imported {$count} sub-kategori for {$kodeKib} ({$parentName})");
    }
}
