<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_aset', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('aset_id')->constrained('aset')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->string('tipe')->comment('Depan, Samping, Udara, Lainnya');
            $table->date('tanggal_foto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_aset');
    }
};
