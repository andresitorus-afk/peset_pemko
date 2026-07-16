<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemanfaatanResource;
use App\Models\Aset;
use App\Models\Pemanfaatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PemanfaatanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pemanfaatan::query()
            ->with(['aset', 'jenis', 'pihakKetiga']);

        if ($request->aset_id) $query->where('aset_id', $request->aset_id);
        if ($request->status) $query->where('status', $request->status);
        if ($request->jenis_id) $query->where('jenis_id', $request->jenis_id);
        if ($request->pihak_ketiga_id) $query->where('pihak_ketiga_id', $request->pihak_ketiga_id);

        return PemanfaatanResource::collection(
            $query->orderByDesc('created_at')->paginate($request->get('per_page', 15))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'aset_id' => 'required|uuid|exists:aset,id',
            'jenis_id' => 'required|uuid|exists:jenis_pemanfaatan,id',
            'pihak_ketiga_id' => 'required|uuid|exists:pihak_ketiga,id',
            'nomor_perjanjian' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'nilai_kontrak' => 'nullable|numeric|min:0',
            'kontribusi_tahunan' => 'nullable|numeric|min:0',
            'peruntukan' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:Aktif,Berakhir,Dibatalkan',
            'catatan' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;
        $pemanfaatan = Pemanfaatan::create($validated);

        if (($validated['status'] ?? 'Aktif') === 'Aktif') {
            $aset = Aset::findOrFail($validated['aset_id']);
            $aset->update(['status' => 'Dimanfaatkan']);
            $aset->riwayat()->create([
                'aksi' => 'Pemanfaatan',
                'deskripsi' => "Pemanfaatan aktif: {$pemanfaatan->nomor_perjanjian}",
                'user_id' => $request->user()->id,
            ]);
        }

        return (new PemanfaatanResource($pemanfaatan->load(['jenis', 'pihakKetiga'])))
            ->response()->setStatusCode(201);
    }

    public function show(Pemanfaatan $pemanfaatan): PemanfaatanResource
    {
        $pemanfaatan->load(['aset', 'jenis', 'pihakKetiga', 'dokumen']);
        return new PemanfaatanResource($pemanfaatan);
    }

    public function update(Request $request, Pemanfaatan $pemanfaatan): PemanfaatanResource
    {
        $validated = $request->validate([
            'jenis_id' => 'sometimes|required|uuid|exists:jenis_pemanfaatan,id',
            'pihak_ketiga_id' => 'sometimes|required|uuid|exists:pihak_ketiga,id',
            'nomor_perjanjian' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'nilai_kontrak' => 'nullable|numeric|min:0',
            'kontribusi_tahunan' => 'nullable|numeric|min:0',
            'peruntukan' => 'nullable|string|max:255',
            'status' => 'sometimes|required|string|in:Aktif,Berakhir,Dibatalkan',
            'catatan' => 'nullable|string',
        ]);

        $pemanfaatan->update($validated);
        return new PemanfaatanResource($pemanfaatan->fresh(['jenis', 'pihakKetiga']));
    }

    public function destroy(Pemanfaatan $pemanfaatan): JsonResponse
    {
        $aset = $pemanfaatan->aset;
        $pemanfaatan->delete();

        $hasActive = $aset->pemanfaatan()->where('status', 'Aktif')->exists();
        if (!$hasActive && $aset->status === 'Dimanfaatkan') {
            $aset->update(['status' => 'Aktif']);
            $aset->riwayat()->create([
                'aksi' => 'Pemanfaatan',
                'deskripsi' => 'Pemanfaatan dihapus, status kembali Aktif',
            ]);
        }

        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function dokumen(Pemanfaatan $pemanfaatan)
    {
        return \App\Http\Resources\DokumenPemanfaatanResource::collection(
            $pemanfaatan->dokumen()->orderByDesc('created_at')->paginate(15)
        );
    }
}
