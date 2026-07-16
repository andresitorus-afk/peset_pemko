<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PihakKetigaResource;
use App\Models\PihakKetiga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PihakKetigaController extends Controller
{
    public function index(Request $request)
    {
        $pihak = PihakKetiga::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama', 'ilike', "%{$s}%"))
            ->orderBy('nama')
            ->paginate($request->get('per_page', 15));

        return PihakKetigaResource::collection($pihak);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jenis' => 'required|string|in:Perorangan,Badan_Hukum,Pemda',
            'npwp' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'penanggung_jawab' => 'nullable|string|max:255',
        ]);

        $pihak = PihakKetiga::create($validated);

        return (new PihakKetigaResource($pihak))->response()->setStatusCode(201);
    }

    public function show(PihakKetiga $pihak_ketiga): PihakKetigaResource
    {
        return new PihakKetigaResource($pihak_ketiga);
    }

    public function update(Request $request, PihakKetiga $pihak_ketiga): PihakKetigaResource
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'jenis' => 'sometimes|required|string|in:Perorangan,Badan_Hukum,Pemda',
            'npwp' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'penanggung_jawab' => 'nullable|string|max:255',
        ]);

        $pihak_ketiga->update($validated);

        return new PihakKetigaResource($pihak_ketiga);
    }

    public function destroy(PihakKetiga $pihak_ketiga): JsonResponse
    {
        $pihak_ketiga->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
