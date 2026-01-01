<?php

namespace App\models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingInside extends Model
{
    use HasFactory;

    protected $table = 'building_inside';

    protected $fillable = ['user_id', 'entered_at'];

    protected function casts(): array
    {
        return [
            'entered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
