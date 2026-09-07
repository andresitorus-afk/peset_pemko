<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OpdController;
use App\Http\Controllers\Api\KategoriAsetController;
use App\Http\Controllers\Api\GisLayerController;
use App\Http\Controllers\Api\JenisPemanfaatanController;
use App\Http\Controllers\Api\PihakKetigaController;
use App\Http\Controllers\Api\AsetController;
use App\Http\Controllers\Api\GisAsetController;
use App\Http\Controllers\Api\PemanfaatanController;
use App\Http\Controllers\Api\DokumenPemanfaatanController;
use App\Http\Controllers\Api\FotoAsetController;
use App\Http\Controllers\Api\RiwayatAsetController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RoleEmailDomainController;
use App\Http\Controllers\Api\PoiController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RekomendasiAiController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ChatbotFaqController;
use App\Http\Controllers\Api\PublicChatController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\BroadcastAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/public/aset', [\App\Http\Controllers\Api\PublicController::class, 'aset']);
Route::get('/public/statistik', [\App\Http\Controllers\Api\PublicController::class, 'statistik']);
Route::get('/public/aset/{id}', [\App\Http\Controllers\Api\PublicController::class, 'asetDetail']);
Route::get('/public/aset/{id}/rekomendasi', [\App\Http\Controllers\Api\PublicController::class, 'rekomendasi']);
Route::get('/public/gis/aset', [GisAsetController::class, 'index']);

Route::post('/public/chat/sessions', [PublicChatController::class, 'store'])->middleware('throttle:20,1');
Route::get('/public/chat/{session}/messages', [PublicChatController::class, 'index'])->middleware('throttle:60,1');
Route::post('/public/chat/{session}/messages', [PublicChatController::class, 'storeMessage'])->middleware('throttle:10,1');

Route::post('/broadcasting/auth', [BroadcastAuthController::class, 'authorize'])->middleware('throttle:120,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::middleware('super_admin')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('users', UserController::class);
        Route::apiResource('role-email-domains', RoleEmailDomainController::class);
    });
    Route::apiResource('opd', OpdController::class);
    Route::apiResource('kategori-aset', KategoriAsetController::class);
    Route::apiResource('gis-layer', GisLayerController::class);
    Route::apiResource('jenis-pemanfaatan', JenisPemanfaatanController::class);
    Route::apiResource('pihak-ketiga', PihakKetigaController::class);
    Route::apiResource('poi', PoiController::class);

    Route::apiResource('aset', AsetController::class);
    Route::post('/aset/import', [AsetController::class, 'import']);
    Route::get('/aset/template', [AsetController::class, 'template']);
    Route::get('/aset/{id}/pemanfaatan', [AsetController::class, 'pemanfaatan']);
    Route::get('/aset/{id}/foto', [AsetController::class, 'foto']);
    Route::get('/aset/{id}/riwayat', [AsetController::class, 'riwayat']);

    Route::apiResource('pemanfaatan', PemanfaatanController::class);
    Route::get('/pemanfaatan/{id}/dokumen', [PemanfaatanController::class, 'dokumen']);

    Route::post('/dokumen-pemanfaatan', [DokumenPemanfaatanController::class, 'store']);
    Route::delete('/dokumen-pemanfaatan/{id}', [DokumenPemanfaatanController::class, 'destroy']);
    Route::post('/foto-aset', [FotoAsetController::class, 'store']);
    Route::delete('/foto-aset/{foto_aset}', [FotoAsetController::class, 'destroy']);

    Route::get('/riwayat-aset', [RiwayatAsetController::class, 'index']);
    Route::post('/riwayat-aset', [RiwayatAsetController::class, 'store']);

    Route::get('/laporan/statistik', [LaporanController::class, 'statistik']);
    Route::get('/laporan/aset', [LaporanController::class, 'exportAset']);
    Route::get('/laporan/pemanfaatan', [LaporanController::class, 'exportPemanfaatan']);
    Route::get('/laporan/master', [LaporanController::class, 'exportMaster']);

    Route::post('/rekomendasi-ai/{aset}', [RekomendasiAiController::class, 'store']);
    Route::get('/rekomendasi-ai/{aset}', [RekomendasiAiController::class, 'index']);

    Route::get('/chat/sessions', [ChatController::class, 'index']);
    Route::get('/chat/sessions/{session}', [ChatController::class, 'show']);
    Route::post('/chat/sessions/{session}/messages', [ChatController::class, 'storeMessage']);
    Route::post('/chat/sessions/{session}/read', [ChatController::class, 'markRead']);
    Route::post('/chat/sessions/{session}/close', [ChatController::class, 'close']);
    Route::delete('/chat/sessions/{session}', [ChatController::class, 'destroy']);
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount']);
    Route::apiResource('chat/faqs', ChatbotFaqController::class);

    Route::prefix('gis')->group(function () {
        Route::get('/aset', [GisAsetController::class, 'index']);
        Route::get('/aset/{id}', [GisAsetController::class, 'show']);
        Route::get('/layer/{id}', [GisAsetController::class, 'byLayer']);
    });
});
