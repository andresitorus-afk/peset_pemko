<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AsetResource;
use App\Models\Aset;
use Illuminate\Http\JsonResponse;

class PublicController extends Controller
{
    public function aset()
    {
        $aset = Aset::query()
            ->with(['opd', 'kategori', 'foto'])
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
}
