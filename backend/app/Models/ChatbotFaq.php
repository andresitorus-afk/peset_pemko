<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatbotFaq extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'chatbot_faqs';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['keywords', 'answer', 'aktif'];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'aktif' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }
}
