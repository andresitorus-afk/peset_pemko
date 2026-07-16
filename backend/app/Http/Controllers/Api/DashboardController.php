<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Pemanfaatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalAset = Aset::count();

        $asetPerKategori = Aset::selectRaw('kategori_aset.nama_kategori, count(*) as total')
            ->join('kategori_aset', 'aset.kategori_id', '=', 'kategori_aset.id')
            ->groupBy('kategori_aset.nama_kategori')
            ->get();

        $asetPerStatus = Aset::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalNilaiPerolehan = Aset::sum('nilai_perolehan');
        $totalNilaiBuku = Aset::sum('nilai_buku');

        $pemanfaatanAktif = Pemanfaatan::where('status', 'Aktif')->count();
        $pemanfaatanSegeraBerakhir = Pemanfaatan::where('status', 'Aktif')
            ->whereBetween('tanggal_selesai', [now(), now()->addDays(30)])
            ->count();

        $pemanfaatanPerJenis = Pemanfaatan::select('jenis_pemanfaatan.nama', DB::raw('count(*) as total'))
            ->join('jenis_pemanfaatan', 'pemanfaatan.jenis_id', '=', 'jenis_pemanfaatan.id')
            ->where('pemanfaatan.status', 'Aktif')
            ->groupBy('jenis_pemanfaatan.nama')
            ->get();

        $pihakKetigaTerbanyak = Pemanfaatan::select('pihak_ketiga.nama', DB::raw('count(*) as total'))
            ->join('pihak_ketiga', 'pemanfaatan.pihak_ketiga_id', '=', 'pihak_ketiga.id')
            ->where('pemanfaatan.status', 'Aktif')
            ->groupBy('pihak_ketiga.nama')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json([
            'total_aset' => $totalAset,
            'aset_per_kategori' => $asetPerKategori,
            'aset_per_status' => $asetPerStatus,
            'total_nilai_perolehan' => $totalNilaiPerolehan,
            'total_nilai_buku' => $totalNilaiBuku,
            'pemanfaatan_aktif' => $pemanfaatanAktif,
            'pemanfaatan_segera_berakhir' => $pemanfaatanSegeraBerakhir,
            'pemanfaatan_per_jenis' => $pemanfaatanPerJenis,
            'pihak_ketiga_terbanyak' => $pihakKetigaTerbanyak,
        ]);
    }
}
