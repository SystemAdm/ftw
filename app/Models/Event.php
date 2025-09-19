<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'description',
        'image_path',
        'location_id',
        'event_start',
        'event_end',
        'signup_needed',
        'signup_start',
        'signup_end',
        'age_min',
        'age_max',
        'number_of_seats',
        'status',
    ];

    protected $casts = [
        'event_start' => 'datetime',
        'event_end' => 'datetime',
        'signup_needed' => 'boolean',
        'signup_start' => 'datetime',
        'signup_end' => 'datetime',
        'age_min' => 'integer',
        'age_max' => 'integer',
        'number_of_seats' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
