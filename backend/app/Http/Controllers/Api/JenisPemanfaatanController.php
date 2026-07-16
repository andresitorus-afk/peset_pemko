<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JenisPemanfaatanResource;
use App\Models\JenisPemanfaatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class JenisPemanfaatanController extends Controller
{
    public function index(Request $request)
    {
        $jenis = JenisPemanfaatan::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama', 'ilike', "%{$s}%"))
            ->orderBy('kode')
            ->paginate($request->get('per_page', 15));

        return JenisPemanfaatanResource::collection($jenis);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'dasar_hukum' => 'nullable|string',
            'ketentuan' => 'nullable|string',
        ]);

        $jenis = JenisPemanfaatan::create($validated);

        return (new JenisPemanfaatanResource($jenis))->response()->setStatusCode(201);
    }

    public function show(JenisPemanfaatan $jenis_pemanfaatan): JenisPemanfaatanResource
    {
        return new JenisPemanfaatanResource($jenis_pemanfaatan);
    }

    public function update(Request $request, JenisPemanfaatan $jenis_pemanfaatan): JenisPemanfaatanResource
    {
        $validated = $request->validate([
            'kode' => 'sometimes|required|string|max:255',
            'nama' => 'sometimes|required|string|max:255',
            'dasar_hukum' => 'nullable|string',
            'ketentuan' => 'nullable|string',
        ]);

        $jenis_pemanfaatan->update($validated);

        return new JenisPemanfaatanResource($jenis_pemanfaatan);
    }

    public function destroy(JenisPemanfaatan $jenis_pemanfaatan): JsonResponse
    {
        $jenis_pemanfaatan->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
