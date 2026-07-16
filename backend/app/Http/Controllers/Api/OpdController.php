<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OpdResource;
use App\Models\Opd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class OpdController extends Controller
{
    public function index(Request $request)
    {
        $opd = Opd::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama_opd', 'ilike', "%{$s}%"))
            ->orderBy('nama_opd')
            ->paginate($request->get('per_page', 15));

        return OpdResource::collection($opd);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_opd' => 'required|string|max:255|unique:opd,kode_opd',
            'nama_opd' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:255',
            'kepala_opd' => 'nullable|string|max:255',
            'nip_kepala' => 'nullable|string|max:255',
        ]);

        $opd = Opd::create($validated);

        return (new OpdResource($opd))->response()->setStatusCode(201);
    }

    public function show(Opd $opd): OpdResource
    {
        return new OpdResource($opd);
    }

    public function update(Request $request, Opd $opd): OpdResource
    {
        $validated = $request->validate([
            'kode_opd' => 'sometimes|required|string|max:255|unique:opd,kode_opd,' . $opd->id . ',id',
            'nama_opd' => 'sometimes|required|string|max:255',
            'alamat' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:255',
            'kepala_opd' => 'nullable|string|max:255',
            'nip_kepala' => 'nullable|string|max:255',
        ]);

        $opd->update($validated);

        return new OpdResource($opd);
    }

    public function destroy(Opd $opd): JsonResponse
    {
        $opd->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
