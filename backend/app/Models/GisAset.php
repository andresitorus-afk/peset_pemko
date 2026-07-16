<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GisAset extends Model
{
    use HasUuid;

    protected $table = 'gis_aset';

    protected $fillable = [
        'aset_id', 'layer_id', 'latitude', 'longitude', 'polygon_geojson',
        'luas_gis', 'tipe_geometri', 'foto_udara_url', 'sumber_koordinat', 'surveyed_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'luas_gis' => 'decimal:2',
        'polygon_geojson' => 'array',
        'surveyed_at' => 'datetime',
    ];

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function layer(): BelongsTo { return $this->belongsTo(GisLayer::class, 'layer_id'); }

    public function toGeoJsonGeometry(): array
    {
        if ($this->tipe_geometri === 'Polygon' && $this->polygon_geojson) {
            return $this->polygon_geojson;
        }
        return [
            'type' => 'Point',
            'coordinates' => [(float) $this->longitude, (float) $this->latitude],
        ];
    }
}
