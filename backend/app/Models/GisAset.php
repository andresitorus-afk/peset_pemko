<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GisAset extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'gis_aset';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'aset_id', 'layer_id', 'latitude', 'longitude', 'polygon_geojson',
        'luas_gis', 'tipe_geometri', 'foto_udara_url', 'sumber_koordinat', 'surveyed_at',
    ];

    protected $casts = [
        'polygon_geojson' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'surveyed_at' => 'datetime',
    ];

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function layer(): BelongsTo { return $this->belongsTo(GisLayer::class, 'layer_id'); }

    public function toGeoJsonGeometry()
    {
        if ($this->tipe_geometri === 'Point') {
            return ['type' => 'Point', 'coordinates' => [(float) $this->longitude, (float) $this->latitude]];
        }
        if ($this->polygon_geojson) {
            return $this->polygon_geojson;
        }
        return null;
    }
}
