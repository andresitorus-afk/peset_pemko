<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AsetResource;
use App\Models\Aset;
use App\Models\Opd;
use App\Models\RekomendasiAi;
use App\Services\RekomendasiLocalService;
use Illuminate\Http\JsonResponse;
use Throwable;

class PublicController extends Controller
{
    public function aset()
    {
        $aset = Aset::query()
            ->with(['opd', 'kategori', 'foto'])
            ->when(!request()->boolean('include_dummy'), fn ($q) => $q->where('kode_barang', 'not like', 'DMY%'))
            ->when(request('search'), fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('nama_barang', 'ilike', "%{$s}%")
                  ->orWhere('kode_barang', 'ilike', "%{$s}%")
                  ->orWhere('alamat', 'ilike', "%{$s}%");
            }))
            ->when(request('kategori'), fn ($q, $v) => $q->where('kategori_id', $v))
            ->when(request('kib'), fn ($q, $v) => $q->whereHas('kategori', fn ($q) => $q->whereIn('kode_kib', explode(',', $v))))
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->when(request('opd'), fn ($q, $v) => $q->where('opd_id', $v))
            ->orderBy('nama_barang')
            ->paginate(request('per_page', 12));

        return AsetResource::collection($aset);
    }

    public function asetDetail(string $id): JsonResponse
    {
        $aset = Aset::with(['opd', 'kategori', 'foto', 'pemanfaatan.jenis', 'pemanfaatan.pihakKetiga', 'gisAset.layer'])
            ->findOrFail($id);

        return response()->json([
            'data' => new AsetResource($aset),
            'foto' => $aset->foto,
            'pemanfaatan' => $aset->pemanfaatan,
        ]);
    }

    public function statistik(): JsonResponse
    {
        $total = Aset::whereHas('kategori', fn ($q) => $q->whereIn('kode_kib', ['KIB A', 'KIB C']))->count();
        $nilai = Aset::whereHas('kategori', fn ($q) => $q->whereIn('kode_kib', ['KIB A', 'KIB C']))->sum('nilai_perolehan');
        $tersedia = Aset::where('status', 'Idle')
            ->whereHas('kategori', fn ($q) => $q->whereIn('kode_kib', ['KIB A', 'KIB C']))->count();
        $opd = Opd::count();

        return response()->json(['data' => compact('total', 'nilai', 'tersedia', 'opd')]);
    }

    public function rekomendasi(string $id): JsonResponse
    {
        $aset = Aset::findOrFail($id);

        try {
            $hasil = app(RekomendasiLocalService::class)->recommend($aset);
        } catch (Throwable) {
            $hasil = RekomendasiAi::where('aset_id', $aset->id)
                ->where('status', 'sukses')
                ->orderByDesc('created_at')
                ->value('hasil');
        }

        return response()->json(['data' => $hasil]);
    }
}
