<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FotoAsetResource;
use App\Models\FotoAset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoAsetController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'aset_id' => 'required|uuid|exists:aset,id',
            'caption' => 'nullable|string|max:255',
            'tipe' => 'required|string|in:Depan,Samping,Udara,Lainnya',
            'tanggal_foto' => 'nullable|date',
            'file' => 'required|image|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('foto', 'public');

        $foto = FotoAset::create([
            'aset_id' => $validated['aset_id'],
            'file_path' => $path,
            'caption' => $validated['caption'] ?? null,
            'tipe' => $validated['tipe'],
            'tanggal_foto' => $validated['tanggal_foto'] ?? null,
        ]);

        return (new FotoAsetResource($foto))
            ->response()->setStatusCode(201);
    }

    public function destroy(FotoAset $foto_aset): JsonResponse
    {
        Storage::disk('public')->delete($foto_aset->file_path);
        $foto_aset->delete();

        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
