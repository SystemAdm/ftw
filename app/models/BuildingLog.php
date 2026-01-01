<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'action'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
