<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FotoAset extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'foto_aset';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['aset_id', 'file_path', 'caption', 'tipe', 'tanggal_foto'];

    protected $casts = ['tanggal_foto' => 'date'];

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
}
