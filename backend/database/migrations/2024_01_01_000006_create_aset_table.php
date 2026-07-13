<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aset', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('opd_id')->constrained('opd')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('kategori_id')->constrained('kategori_aset')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('kode_barang')->unique();
            $table->string('register')->nullable();
            $table->string('nama_barang');
            $table->integer('tahun_perolehan')->nullable();
            $table->decimal('nilai_perolehan', 20, 2)->nullable();
            $table->decimal('nilai_buku', 20, 2)->nullable();
            $table->decimal('luas', 15, 2)->nullable()->comment('m2 untuk tanah/bangunan');
            $table->string('kondisi')->comment('Baik, Rusak_Ringan, Rusak_Berat');
            $table->string('status')->default('Aktif')->comment('Aktif, Idle, Dimanfaatkan');
            $table->string('alamat')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('kondisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aset');
    }
};
