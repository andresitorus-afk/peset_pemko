<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\JenisPemanfaatan;
use App\Models\Opd;
use App\Models\Pemanfaatan;
use App\Models\PihakKetiga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanController extends Controller
{
    public function statistik(Request $request): JsonResponse
    {
        $tahun = $request->integer('tahun') ?: null;

        $pemanfaatan = Pemanfaatan::query()
            ->when($tahun, fn ($q) => $q->whereYear('tanggal_mulai', $tahun));

        $tren = $pemanfaatan->clone()
            ->selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as tahun, count(*) as jumlah')
            ->whereNotNull('tanggal_mulai')
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->pluck('jumlah', 'tahun');

        $kontribusiPerTahun = Pemanfaatan::query()
            ->selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as tahun, sum(kontribusi_tahunan) as total')
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('kontribusi_tahunan')
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->pluck('total', 'tahun');

        $perJenis = $pemanfaatan->clone()
            ->select('jenis_pemanfaatan.nama', DB::raw('count(*) as total'))
            ->join('jenis_pemanfaatan', 'pemanfaatan.jenis_id', '=', 'jenis_pemanfaatan.id')
            ->groupBy('jenis_pemanfaatan.nama')
            ->orderByDesc('total')
            ->get();

        $perOpd = $pemanfaatan->clone()
            ->select('opd.nama_opd', DB::raw('count(*) as total'))
            ->join('aset', 'pemanfaatan.aset_id', '=', 'aset.id')
            ->join('opd', 'aset.opd_id', '=', 'opd.id')
            ->groupBy('opd.nama_opd')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $asetPerKategori = Aset::select('kategori_aset.nama_kategori', DB::raw('count(*) as total'))
            ->join('kategori_aset', 'aset.kategori_id', '=', 'kategori_aset.id')
            ->groupBy('kategori_aset.nama_kategori')
            ->orderByDesc('total')
            ->get();

        $kontrakBerakhir = Pemanfaatan::with(['aset', 'pihakKetiga'])
            ->where('status', 'Aktif')
            ->whereBetween('tanggal_selesai', [now(), now()->addDays(90)])
            ->orderBy('tanggal_selesai')
            ->limit(20)
            ->get()
            ->map(fn ($p) => [
                'aset' => $p->aset?->nama_barang,
                'pihak_ketiga' => $p->pihakKetiga?->nama,
                'nomor_perjanjian' => $p->nomor_perjanjian,
                'tanggal_selesai' => $p->tanggal_selesai?->format('Y-m-d'),
            ]);

        return response()->json([
            'tren_pemanfaatan' => $tren,
            'kontribusi_per_tahun' => $kontribusiPerTahun,
            'pemanfaatan_per_jenis' => $perJenis,
            'pemanfaatan_per_opd' => $perOpd,
            'aset_per_kategori' => $asetPerKategori,
            'kontrak_berakhir' => $kontrakBerakhir,
        ]);
    }

    public function exportAset(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = Aset::with(['opd', 'kategori'])->orderBy('kode_barang')->get();

        return $this->streamExcel('laporan_aset.xlsx', function (Spreadsheet $spreadsheet) use ($rows) {
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['Kode Barang', 'Register', 'Nama Barang', 'OPD', 'Kategori', 'KIB', 'Tahun', 'Nilai Perolehan', 'Nilai Buku', 'Luas (m2)', 'Kondisi', 'Status', 'Alamat', 'Keterangan'];
            $sheet->fromArray($headers, null, 'A1');
            $sheet->getStyle('A1:N1')->getFont()->setBold(true);

            $row = 2;
            foreach ($rows as $aset) {
                $sheet->fromArray([
                    $aset->kode_barang, $aset->register, $aset->nama_barang,
                    $aset->opd?->nama_opd, $aset->kategori?->nama_kategori, $aset->kategori?->kode_kib,
                    $aset->tahun_perolehan, $aset->nilai_perolehan, $aset->nilai_buku, $aset->luas,
                    $aset->kondisi, $aset->status, $aset->alamat, $aset->keterangan,
                ], null, "A{$row}");
                $row++;
            }
        });
    }

    public function exportPemanfaatan(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = Pemanfaatan::with(['aset', 'jenis', 'pihakKetiga'])->orderByDesc('created_at')->get();

        return $this->streamExcel('laporan_pemanfaatan.xlsx', function (Spreadsheet $spreadsheet) use ($rows) {
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['Aset', 'Jenis', 'Pihak Ketiga', 'No. Perjanjian', 'Mulai', 'Selesai', 'Nilai Kontrak', 'Kontribusi/Tahun', 'Peruntukan', 'Status', 'Catatan'];
            $sheet->fromArray($headers, null, 'A1');
            $sheet->getStyle('A1:K1')->getFont()->setBold(true);

            $row = 2;
            foreach ($rows as $p) {
                $sheet->fromArray([
                    $p->aset?->nama_barang, $p->jenis?->nama, $p->pihakKetiga?->nama,
                    $p->nomor_perjanjian, $p->tanggal_mulai?->format('Y-m-d'), $p->tanggal_selesai?->format('Y-m-d'),
                    $p->nilai_kontrak, $p->kontribusi_tahunan, $p->peruntukan, $p->status, $p->catatan,
                ], null, "A{$row}");
                $row++;
            }
        });
    }

    public function exportMaster(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->streamExcel('master_pemko.xlsx', function (Spreadsheet $spreadsheet) {
            $spreadsheet->removeSheetByIndex(0);

            $this->appendSheet($spreadsheet, 'OPD', ['Kode', 'Nama OPD'],
                Opd::orderBy('kode_opd')->get()->map(fn ($o) => [$o->kode_opd, $o->nama_opd]));

            $this->appendSheet($spreadsheet, 'Jenis Pemanfaatan', ['Nama'],
                JenisPemanfaatan::orderBy('nama')->get()->map(fn ($j) => [$j->nama]));

            $this->appendSheet($spreadsheet, 'Pihak Ketiga', ['Nama', 'Telepon', 'Email'],
                PihakKetiga::orderBy('nama')->get()->map(fn ($p) => [$p->nama, $p->telepon, $p->email]));
        });
    }

    protected function appendSheet(Spreadsheet $spreadsheet, string $title, array $headers, $rows): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($title);
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1')->getFont()->setBold(true);
        $row = 2;
        foreach ($rows as $r) {
            $sheet->fromArray(array_values($r), null, "A{$row}");
            $row++;
        }
    }

    protected function streamExcel(string $filename, callable $build): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($build) {
            $spreadsheet = new Spreadsheet();
            $build($spreadsheet);
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
