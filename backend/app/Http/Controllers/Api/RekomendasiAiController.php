<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\RekomendasiAi;
use App\Services\RekomendasiAsetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RekomendasiAiController extends Controller
{
    public function __construct(private readonly RekomendasiAsetService $service)
    {
    }

    public function store(Request $request, Aset $aset): JsonResponse
    {
        $this->service->generate($aset, $request->user()?->id);

        $last = RekomendasiAi::where('aset_id', $aset->id)->orderByDesc('created_at')->first();

        return $last && $last->status === 'sukses'
            ? response()->json(['data' => $last->hasil])
            : response()->json(['message' => 'Rekomendasi gagal, coba lagi.'], 502);
    }

    public function index(Aset $aset): JsonResponse
    {
        $items = RekomendasiAi::where('aset_id', $aset->id)
            ->orderByDesc('created_at')
            ->get(['id', 'hasil', 'status', 'error', 'created_at']);

        return response()->json(['data' => $items]);
    }
}
