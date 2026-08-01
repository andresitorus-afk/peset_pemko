<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RekomendasiAi extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'rekomendasi_ai';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['aset_id', 'hasil', 'status', 'error', 'created_by'];

    protected $casts = ['hasil' => 'array'];

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
