<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatSession extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'chat_sessions';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['token', 'visitor_name', 'status', 'needs_attention', 'last_admin_seen_at', 'closed_at'];

    protected function casts(): array
    {
        return [
            'needs_attention' => 'boolean',
            'last_admin_seen_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
