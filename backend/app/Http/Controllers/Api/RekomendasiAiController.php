<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\RekomendasiAi;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class RekomendasiAiController extends Controller
{
    public function __construct(private readonly GeminiService $gemini)
    {
    }

    public function store(Request $request, Aset $aset): JsonResponse
    {
        $aset->load(['kategori', 'gisAset']);
        $pois = $this->gemini->nearbyPois(
            isset($aset->gisAset->latitude) ? (float) $aset->gisAset->latitude : null,
            isset($aset->gisAset->longitude) ? (float) $aset->gisAset->longitude : null
        );

        try {
            $text = $this->gemini->generate($this->gemini->buildPrompt($aset, $pois));
            $hasil = $this->gemini->parseJson($text);
            $this->gemini->validateHasil($hasil);
            RekomendasiAi::create([
                'aset_id' => $aset->id,
                'hasil' => $hasil,
                'status' => 'sukses',
                'created_by' => $request->user()?->id,
            ]);
            return response()->json(['data' => $hasil]);
        } catch (Throwable $e) {
            RekomendasiAi::create([
                'aset_id' => $aset->id,
                'status' => 'gagal',
                'error' => $e->getMessage(),
                'created_by' => $request->user()?->id,
            ]);
            Log::error('Rekomendasi AI gagal untuk aset ' . $aset->id . ': ' . $e->getMessage());

            return response()->json(['message' => 'Rekomendasi gagal, coba lagi.'], 502);
        }
    }

    public function index(Aset $aset): JsonResponse
    {
        $items = RekomendasiAi::where('aset_id', $aset->id)
            ->orderByDesc('created_at')
            ->get(['id', 'hasil', 'status', 'error', 'created_at']);

        return response()->json(['data' => $items]);
    }
}
