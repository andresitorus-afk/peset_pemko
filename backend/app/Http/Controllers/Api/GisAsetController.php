<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GisAset;
use App\Models\GisLayer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GisAsetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GisAset::with(['aset.opd', 'layer']);

        if ($request->bbox) {
            $coords = explode(',', $request->bbox);
            if (count($coords) === 4) {
                [$west, $south, $east, $north] = array_map('floatval', $coords);
                $query->whereBetween('longitude', [$west, $east])
                      ->whereBetween('latitude', [$south, $north]);
            }
        }

        if ($request->layer_id) $query->where('layer_id', $request->layer_id);

        $features = $query->get()->map(function ($gis) {
            return [
                'type' => 'Feature',
                'geometry' => $gis->toGeoJsonGeometry(),
                'properties' => [
                    'id' => $gis->aset->id ?? null,
                    'gis_id' => $gis->id,
                    'kode_barang' => $gis->aset->kode_barang ?? null,
                    'nama_barang' => $gis->aset->nama_barang ?? null,
                    'status' => $gis->aset->status ?? null,
                    'kondisi' => $gis->aset->kondisi ?? null,
                    'layer' => $gis->layer->nama_layer ?? null,
                    'layer_id' => $gis->layer_id,
                    'luas_gis' => $gis->luas_gis,
                    'tipe_geometri' => $gis->tipe_geometri,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $gis = GisAset::with(['aset', 'layer'])->where('aset_id', $id)->firstOrFail();

        return response()->json([
            'type' => 'Feature',
            'geometry' => $gis->toGeoJsonGeometry(),
            'properties' => array_merge($gis->toArray(), [
                'aset' => $gis->aset->only(['id', 'kode_barang', 'nama_barang', 'status', 'kondisi']),
                'layer' => $gis->layer->only(['id', 'nama_layer']),
            ]),
        ]);
    }

    public function byLayer(GisLayer $gis_layer): JsonResponse
    {
        $features = $gis_layer->gisAset()
            ->with(['aset.opd'])
            ->get()
            ->map(function ($gis) {
                return [
                    'type' => 'Feature',
                    'geometry' => $gis->toGeoJsonGeometry(),
                    'properties' => [
                        'id' => $gis->aset->id ?? null,
                        'nama_barang' => $gis->aset->nama_barang ?? null,
                        'status' => $gis->aset->status ?? null,
                    ],
                ];
            });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
