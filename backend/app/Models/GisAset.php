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
        static::saving(function ($model) {
            if ($model->polygon_geojson && !$model->luas_gis) {
                $model->luas_gis = $model->calculateAreaFromPolygon();
            }
        });
    }

    protected function calculateAreaFromPolygon(): ?float
    {
        $geo = $this->polygon_geojson;
        if (!$geo || !isset($geo['type'])) return null;
        if (!in_array($geo['type'], ['Polygon', 'MultiPolygon'])) return null;

        $rings = $geo['type'] === 'Polygon'
            ? $geo['coordinates']
            : $geo['coordinates'][0] ?? [];

        // Shoelace formula (WGS84 approximate)
        $area = 0;
        foreach ($rings as $ring) {
            $n = count($ring);
            if ($n < 3) continue;
            $s = 0;
            for ($i = 0; $i < $n - 1; $i++) {
                $s += deg2rad($ring[$i][1]) * deg2rad($ring[$i + 1][0]);
                $s -= deg2rad($ring[$i + 1][1]) * deg2rad($ring[$i][0]);
            }
            $area += abs($s) / 2 * (6371000 * 6371000);
        }
        return round($area, 2);
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
