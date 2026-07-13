<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_pemanfaatan', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('kode')->comment('SEWA, PKP, KSP, BGS, BSG, KSPI');
            $table->string('nama')->comment('Sewa, Pinjam Pakai, KSP, dll');
            $table->text('dasar_hukum')->nullable();
            $table->text('ketentuan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pemanfaatan');
    }
};
