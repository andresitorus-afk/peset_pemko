<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriAsetResource;
use App\Models\KategoriAset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class KategoriAsetController extends Controller
{
    public function index(Request $request)
    {
        $kategori = KategoriAset::query()
            ->with('children')
            ->when($request->search, fn ($q, $s) => $q->where('nama_kategori', 'ilike', "%{$s}%"))
            ->orderBy('kode_kib')
            ->paginate($request->get('per_page', 15));

        return KategoriAsetResource::collection($kategori);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_kib' => 'required|string|max:255',
            'kode_kategori' => 'nullable|string|max:255',
            'nama_kategori' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'parent_id' => 'nullable|uuid|exists:kategori_aset,id',
            'is_leaf' => 'boolean',
        ]);

        $kategori = KategoriAset::create($validated);

        return (new KategoriAsetResource($kategori))->response()->setStatusCode(201);
    }

    public function show(KategoriAset $kategori_aset): KategoriAsetResource
    {
        return new KategoriAsetResource($kategori_aset);
    }

    public function update(Request $request, KategoriAset $kategori_aset): KategoriAsetResource
    {
        $validated = $request->validate([
            'kode_kib' => 'sometimes|required|string|max:255',
            'kode_kategori' => 'nullable|string|max:255',
            'nama_kategori' => 'sometimes|required|string|max:255',
            'keterangan' => 'nullable|string|max:255',
            'parent_id' => 'nullable|uuid|exists:kategori_aset,id',
            'is_leaf' => 'boolean',
        ]);

        $kategori_aset->update($validated);

        return new KategoriAsetResource($kategori_aset);
    }

    public function destroy(KategoriAset $kategori_aset): JsonResponse
    {
        $kategori_aset->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
