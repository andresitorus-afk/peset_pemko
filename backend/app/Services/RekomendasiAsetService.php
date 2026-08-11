<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\RekomendasiAi;
use Illuminate\Support\Facades\Log;
use Throwable;

class RekomendasiAsetService
{
    public function __construct(
        private readonly RekomendasiLocalService $local,
    ) {
    }

    public function generate(Aset $aset, ?int $userId = null): void
    {
        try {
            $hasil = $this->local->recommend($aset);

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