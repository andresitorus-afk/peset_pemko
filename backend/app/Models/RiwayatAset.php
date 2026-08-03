<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RiwayatAset extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'riwayat_aset';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['aset_id', 'aksi', 'deskripsi', 'detail', 'user_id'];

    protected $casts = [
        'detail' => 'array',
    ];

    public const UPDATED_AT = null;

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
