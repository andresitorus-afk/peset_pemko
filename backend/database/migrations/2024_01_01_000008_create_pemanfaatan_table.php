<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemanfaatan', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('aset_id')->constrained('aset')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('jenis_id')->constrained('jenis_pemanfaatan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('pihak_ketiga_id')->constrained('pihak_ketiga')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nomor_perjanjian')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->decimal('nilai_kontrak', 20, 2)->nullable();
            $table->decimal('kontribusi_tahunan', 20, 2)->nullable();
            $table->string('peruntukan')->nullable();
            $table->string('status')->default('Aktif')->comment('Aktif, Berakhir, Dibatalkan');
            $table->text('catatan')->nullable();
            // Sesuaikan tipe kolom users.id (bigint) jika tabel users memakai id default Laravel.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('status');
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemanfaatan');
    }
};
