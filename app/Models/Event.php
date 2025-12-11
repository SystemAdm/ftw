<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_start' => 'datetime',
            'event_end' => 'datetime',
            'signup_needed' => 'boolean',
            'signup_start' => 'datetime',
            'signup_end' => 'datetime',
            'age_min' => 'integer',
            'age_max' => 'integer',
            'number_of_seats' => 'integer',
        ];
    }

    /**
     * Location where event happens (nullable).
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Logs for this event.
     *
     * @return HasMany<EventLog>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(EventLog::class);
    }

    /**
     * Users who reserved a seat for this event.
     *
     * @return BelongsToMany<User>
     */
    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_reservation')->withTimestamps();
    }

    /**
     * Users who are marked as attendees of this event.
     *
     * @return BelongsToMany<User>
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_attendee')->withTimestamps();
    }

    /**
     * Users currently inside the event.
     *
     * @return BelongsToMany<User>
     */
    public function inside(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_inside')->withTimestamps();
    }
}
