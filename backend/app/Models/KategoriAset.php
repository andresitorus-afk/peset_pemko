<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KategoriAset extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'kategori_aset';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['kode_kib', 'kode_kategori', 'nama_kategori', 'keterangan', 'parent_id', 'is_leaf'];

    public function aset()
    {
        return $this->hasMany(Aset::class, 'kategori_id');
    }

    public function parent()
    {
        return $this->belongsTo(KategoriAset::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(KategoriAset::class, 'parent_id');
    }
}
