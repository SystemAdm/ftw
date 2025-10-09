<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'banned_at',
        'banned_to',
        'banned_by',
        'reason',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'banned_to' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }
}
