<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DokumenPemanfaatan extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'dokumen_pemanfaatan';

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
        'pemanfaatan_id', 'jenis_dokumen', 'nomor_dokumen', 'tanggal_dokumen', 'file_path', 'file_name',
    ];

    protected $casts = ['tanggal_dokumen' => 'date'];

    public function pemanfaatan(): BelongsTo { return $this->belongsTo(Pemanfaatan::class); }
}
