<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GisLayerResource;
use App\Models\GisLayer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\AnonymousResourceCollection;

class GisLayerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $layer = GisLayer::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama_layer', 'ilike', "%{$s}%"))
            ->orderBy('nama_layer')
            ->paginate($request->get('per_page', 15));

        return GisLayerResource::collection($layer);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_layer' => 'required|string|max:255',
            'warna' => 'nullable|string|max:255',
            'icon_marker' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $layer = GisLayer::create($validated);

        return (new GisLayerResource($layer))->response()->setStatusCode(201);
    }

    public function show(GisLayer $gis_layer): GisLayerResource
    {
        return new GisLayerResource($gis_layer);
    }

    public function update(Request $request, GisLayer $gis_layer): GisLayerResource
    {
        $validated = $request->validate([
            'nama_layer' => 'sometimes|required|string|max:255',
            'warna' => 'nullable|string|max:255',
            'icon_marker' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $gis_layer->update($validated);

        return new GisLayerResource($gis_layer);
    }

    public function destroy(GisLayer $gis_layer): JsonResponse
    {
        $gis_layer->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
