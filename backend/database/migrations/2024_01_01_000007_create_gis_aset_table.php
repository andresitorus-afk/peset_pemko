<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gis_aset', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('aset_id')->unique()->constrained('aset')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUuid('layer_id')->constrained('gis_layer')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->jsonb('polygon_geojson')->nullable()->comment('Batas area polygon');
            $table->decimal('luas_gis', 15, 2)->nullable()->comment('m2 dari pengukuran');
            $table->string('tipe_geometri')->comment('Point, Polygon, Polyline');
            $table->string('foto_udara_url')->nullable();
            $table->string('sumber_koordinat')->nullable()->comment('GPS, Survey, GoogleMaps');
            $table->timestamp('surveyed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gis_aset');
    }
};
