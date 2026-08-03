<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiwayatAsetResource;
use App\Models\RiwayatAset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class RiwayatAsetController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatAset::with(['aset', 'user']);

        if ($request->aset_id) $query->where('aset_id', $request->aset_id);
        if ($request->aksi) $query->where('aksi', $request->aksi);
        if ($request->user_id) $query->where('user_id', $request->user_id);
        if ($request->tanggal_dari) $query->whereDate('created_at', '>=', $request->tanggal_dari);
        if ($request->tanggal_sampai) $query->whereDate('created_at', '<=', $request->tanggal_sampai);

        return RiwayatAsetResource::collection(
            $query->orderByDesc('created_at')->paginate($request->get('per_page', 15))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'aset_id' => 'required|uuid|exists:aset,id',
            'aksi' => 'required|string|in:Pemanfaatan,Pemeliharaan,Mutasi,Penghapusan,Revaluasi',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;
        $riwayat = RiwayatAset::create($validated);

        return (new RiwayatAsetResource($riwayat->load('user')))
            ->response()->setStatusCode(201);
    }
}
