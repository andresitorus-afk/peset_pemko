<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PoiController extends Controller
{
    public function index(Request $request)
    {
        return Poi::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama', 'ilike', "%{$s}%"))
            ->orderBy('nama')
            ->paginate($request->get('per_page', 15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string|in:kampus,sekolah,mal,pasar,rumah_sakit,puskesmas,kantor,perumahan,stasiun,lainnya',
            'alamat' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'aktif' => 'nullable|boolean',
        ]);

        $poi = Poi::create($validated);
        return response()->json($poi, 201);
    }

    public function show(Poi $poi): JsonResponse
    {
        return response()->json($poi);
    }

    public function update(Request $request, Poi $poi): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'tipe' => 'sometimes|required|string|in:kampus,sekolah,mal,pasar,rumah_sakit,puskesmas,kantor,perumahan,stasiun,lainnya',
            'alamat' => 'nullable|string|max:255',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'aktif' => 'nullable|boolean',
        ]);

        $poi->update($validated);
        return response()->json($poi);
    }

    public function destroy(Poi $poi): JsonResponse
    {
        $poi->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
