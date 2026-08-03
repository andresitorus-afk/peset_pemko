<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AsetResource;
use App\Imports\AsetImport;
use App\Models\Aset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;


class AsetController extends Controller
{
    public function index(Request $request)
    {
        $aset = Aset::query()
            ->with(['opd', 'kategori', 'gisAset.layer'])
            ->search($request->search)
            ->filter($request->only(['opd_id', 'kategori_id', 'kondisi', 'status']))
            ->orderBy('kode_barang')
            ->paginate($request->get('per_page', 15));

        return AsetResource::collection($aset);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opd_id' => 'required|uuid|exists:opd,id',
            'kategori_id' => 'required|uuid|exists:kategori_aset,id',
            'kode_barang' => 'required|string|max:255|unique:aset,kode_barang',
            'register' => 'nullable|string|max:255',
            'nama_barang' => 'required|string|max:255',
            'tahun_perolehan' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nilai_perolehan' => 'nullable|numeric|min:0',
            'nilai_buku' => 'nullable|numeric|min:0',
            'luas' => 'nullable|numeric|min:0',
            'kondisi' => 'required|string|in:Baik,Rusak_Ringan,Rusak_Berat',
            'status' => 'nullable|string|in:Aktif,Idle,Dimanfaatkan',
            'alamat' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $aset = Aset::create($validated);

        $this->handleGis($request, $aset);

        return (new AsetResource($aset->load(['opd', 'kategori'])))
            ->response()->setStatusCode(201);
    }

    public function show(Aset $aset): AsetResource
    {
        $aset->load(['opd', 'kategori', 'foto', 'gisAset.layer', 'pemanfaatan.jenis', 'pemanfaatan.pihakKetiga']);
        return new AsetResource($aset);
    }

    public function update(Request $request, Aset $aset): AsetResource
    {
        $validated = $request->validate([
            'opd_id' => 'sometimes|required|uuid|exists:opd,id',
            'kategori_id' => 'sometimes|required|uuid|exists:kategori_aset,id',
            'kode_barang' => 'sometimes|required|string|max:255|unique:aset,kode_barang,' . $aset->id . ',id',
            'register' => 'nullable|string|max:255',
            'nama_barang' => 'sometimes|required|string|max:255',
            'tahun_perolehan' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nilai_perolehan' => 'nullable|numeric|min:0',
            'nilai_buku' => 'nullable|numeric|min:0',
            'luas' => 'nullable|numeric|min:0',
            'kondisi' => 'sometimes|required|string|in:Baik,Rusak_Ringan,Rusak_Berat',
            'status' => 'sometimes|required|string|in:Aktif,Idle,Dimanfaatkan',
            'alamat' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $old = $aset->getAttributes();
        $aset->update($validated);

        \App\Services\AuditTrail::catat($aset, $aset, 'Pemeliharaan', $old, $aset->fresh()->getAttributes(), $request->user()->id);

        $this->handleGis($request, $aset);

        return new AsetResource($aset->fresh(['opd', 'kategori']));
    }

    protected function handleGis(Request $request, Aset $aset): void
    {
        $gis = $request->input('gis');
        if (!$gis || !isset($gis['latitude'], $gis['longitude'], $gis['layer_id'])) return;

        $aset->gisAset()->updateOrCreate(
            ['aset_id' => $aset->id],
            [
                'latitude' => $gis['latitude'],
                'longitude' => $gis['longitude'],
                'layer_id' => $gis['layer_id'],
                'tipe_geometri' => $gis['tipe_geometri'] ?? 'Point',
                'polygon_geojson' => $gis['polygon_geojson'] ?? null,
                'luas_gis' => $gis['luas_gis'] ?? null,
                'sumber_koordinat' => 'GoogleMaps',
            ]
        );
    }

    public function destroy(Aset $aset): JsonResponse
    {
        if ($aset->pemanfaatan()->exists()) {
            throw ValidationException::withMessages([
                'aset' => 'Aset masih memiliki pemanfaatan. Tidak bisa dihapus.',
            ]);
        }
        $aset->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function pemanfaatan(Aset $aset)
    {
        return \App\Http\Resources\PemanfaatanResource::collection(
            $aset->pemanfaatan()->with(['jenis', 'pihakKetiga'])->orderByDesc('created_at')->paginate(15)
        );
    }

    public function foto(Aset $aset)
    {
        return \App\Http\Resources\FotoAsetResource::collection(
            $aset->foto()->orderByDesc('created_at')->paginate(15)
        );
    }

    public function riwayat(Aset $aset)
    {
        return \App\Http\Resources\RiwayatAsetResource::collection(
            $aset->riwayat()->with('user')->orderByDesc('created_at')->paginate(15)
        );
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new AsetImport;
        Excel::import($import, $request->file('file'));

        return response()->json([
            'message' => 'Import selesai',
            'success' => $import->results['success'],
            'failed'  => $import->results['failed'],
            'errors'  => $import->results['errors'],
        ]);
    }

    public function template()
    {
        $opdList = \App\Models\Opd::pluck('nama_opd', 'kode_opd');
        $kategoriLeaf = \App\Models\KategoriAset::where('is_leaf', true)->orderBy('kode_kategori')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['kode_barang', 'register', 'nama_barang', 'opd', 'kode_kategori', 'tahun_perolehan', 'nilai_perolehan', 'nilai_buku', 'luas', 'kondisi', 'status', 'alamat', 'keterangan'];
        foreach ($headers as $col => $header) {
            $cell = $sheet->getCellByColumnAndRow($col + 1, 1);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true);
        }

        // Contoh baris
        $sampleKode = $kategoriLeaf->first()?->kode_kategori ?? '1.3.1.01.01.01.001';
        $sample = ['TAN-2024-001', '.01001.001', 'Tanah Kantor Dinas', 'Dinas Pendidikan', $sampleKode, 2024, 15000000000, 15000000000, 5000, 'Baik', 'Aktif', 'Jl. Contoh No.1', 'Contoh data'];
        foreach ($sample as $col => $val) {
            $sheet->getCellByColumnAndRow($col + 1, 2)->setValue($val);
        }

        // List OPD di sheet 2
        $opdSheet = $spreadsheet->createSheet();
        $opdSheet->setTitle('Data OPD');
        $opdSheet->getCell('A1')->setValue('Kode OPD');
        $opdSheet->getCell('B1')->setValue('Nama OPD');
        $opdSheet->getCell('A1')->getStyle()->getFont()->setBold(true);
        $opdSheet->getCell('B1')->getStyle()->getFont()->setBold(true);
        $row = 2;
        foreach ($opdList as $kode => $nama) {
            $opdSheet->getCell("A{$row}")->setValue($kode);
            $opdSheet->getCell("B{$row}")->setValue($nama);
            $row++;
        }

        // List Kategori (leaf nodes) di sheet 3
        $katSheet = $spreadsheet->createSheet();
        $katSheet->setTitle('Data Kategori');
        $katSheet->getCell('A1')->setValue('Kode Kategori');
        $katSheet->getCell('B1')->setValue('Nama Kategori');
        $katSheet->getCell('C1')->setValue('Kode KIB');
        $katSheet->getCell('A1')->getStyle()->getFont()->setBold(true);
        $katSheet->getCell('B1')->getStyle()->getFont()->setBold(true);
        $katSheet->getCell('C1')->getStyle()->getFont()->setBold(true);
        $row = 2;
        foreach ($kategoriLeaf as $k) {
            $katSheet->getCell("A{$row}")->setValue($k->kode_kategori);
            $katSheet->getCell("B{$row}")->setValue($k->nama_kategori);
            $katSheet->getCell("C{$row}")->setValue($k->kode_kib);
            $row++;
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'template_import_aset.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
