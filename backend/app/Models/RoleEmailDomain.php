<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleEmailDomain extends Model
{
    protected $fillable = ['domain', 'role_id'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
