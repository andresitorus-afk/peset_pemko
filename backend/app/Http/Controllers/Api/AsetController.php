<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AsetResource;
use App\Models\Aset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\AnonymousResourceCollection;

class AsetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $aset = Aset::query()
            ->with(['opd', 'kategori'])
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

        return (new AsetResource($aset->load(['opd', 'kategori'])))
            ->response()->setStatusCode(201);
    }

    public function show(Aset $aset): AsetResource
    {
        $aset->load(['opd', 'kategori', 'gisAset.layer', 'pemanfaatan.jenis', 'pemanfaatan.pihakKetiga']);
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

        $oldStatus = $aset->status;
        $aset->update($validated);

        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $aset->riwayat()->create([
                'aksi' => 'Pemanfaatan',
                'deskripsi' => "Status berubah dari {$oldStatus} ke {$validated['status']}",
                'user_id' => $request->user()->id,
            ]);
        }

        return new AsetResource($aset->fresh(['opd', 'kategori']));
    }

    public function destroy(Aset $aset): JsonResponse
    {
        $aset->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function pemanfaatan(Aset $aset): AnonymousResourceCollection
    {
        return \App\Http\Resources\PemanfaatanResource::collection(
            $aset->pemanfaatan()->with(['jenis', 'pihakKetiga'])->orderByDesc('created_at')->paginate(15)
        );
    }

    public function foto(Aset $aset): AnonymousResourceCollection
    {
        return \App\Http\Resources\FotoAsetResource::collection(
            $aset->foto()->orderByDesc('created_at')->paginate(15)
        );
    }

    public function riwayat(Aset $aset): AnonymousResourceCollection
    {
        return \App\Http\Resources\RiwayatAsetResource::collection(
            $aset->riwayat()->with('user')->orderByDesc('created_at')->paginate(15)
        );
    }
}
