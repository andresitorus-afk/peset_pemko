<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\RekomendasiAi;
use Illuminate\Support\Facades\Log;
use Throwable;

class RekomendasiAsetService
{
    public function __construct(private readonly GeminiService $gemini)
    {
    }

    public function generate(Aset $aset, ?int $userId = null): void
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
                'created_by' => $userId,
            ]);
        } catch (Throwable $e) {
            RekomendasiAi::create([
                'aset_id' => $aset->id,
                'status' => 'gagal',
                'error' => $e->getMessage(),
                'created_by' => $userId,
            ]);
            Log::error('Rekomendasi AI gagal untuk aset ' . $aset->id . ': ' . $e->getMessage());
        }
    }
}
