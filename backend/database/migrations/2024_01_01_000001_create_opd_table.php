<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opd', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('kode_opd')->unique();
            $table->string('nama_opd');
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('kepala_opd')->nullable();
            $table->string('nip_kepala')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opd');
    }
};
