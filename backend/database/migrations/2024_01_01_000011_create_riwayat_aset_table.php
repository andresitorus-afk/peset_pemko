<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_aset', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('aset_id')->constrained('aset')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('aksi')->comment('Pemanfaatan, Pemeliharaan, Mutasi, Penghapusan, Revaluasi');
            $table->text('deskripsi')->nullable();
            // Sesuaikan tipe kolom users.id (bigint) jika tabel users memakai id default Laravel.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('aksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_aset');
    }
};
