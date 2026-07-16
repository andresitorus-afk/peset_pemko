<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DokumenPemanfaatanResource;
use App\Models\DokumenPemanfaatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokumenPemanfaatanController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pemanfaatan_id' => 'required|uuid|exists:pemanfaatan,id',
            'jenis_dokumen' => 'required|string|max:255',
            'nomor_dokumen' => 'nullable|string|max:255',
            'tanggal_dokumen' => 'nullable|date',
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('dokumen', 'public');

        $dokumen = DokumenPemanfaatan::create([
            'pemanfaatan_id' => $validated['pemanfaatan_id'],
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'nomor_dokumen' => $validated['nomor_dokumen'] ?? null,
            'tanggal_dokumen' => $validated['tanggal_dokumen'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
        ]);

        return (new DokumenPemanfaatanResource($dokumen))
            ->response()->setStatusCode(201);
    }

    public function destroy(DokumenPemanfaatan $dokumen_pemanfaatan): JsonResponse
    {
        Storage::disk('public')->delete($dokumen_pemanfaatan->file_path);
        $dokumen_pemanfaatan->delete();

        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
