<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Weekday extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekday',
        'team_id',
        'location_id',
        'active',
        'event_start',
        'event_end',
        'start_time',
        'end_time',
    ];

    protected $appends = [
        'is_ended',
        'status_label',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'team_id' => 'integer',
            'location_id' => 'integer',
            'active' => 'boolean',
            'start_time' => 'string',
            'end_time' => 'string',
            'event_start' => 'date',
            'event_end' => 'date',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function exclusions(): HasMany
    {
        return $this->hasMany(WeekdayExcluded::class);
    }

    public function getIsEndedAttribute(): bool
    {
        // Consider ended when event_end is set and strictly before today
        if ($this->event_end === null) {
            return false;
        }

        // event_end is cast to date; compare with today() to mark ended starting the day after end
        return $this->event_end->lt(now()->startOfDay());
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_ended) {
            return 'Ended';
        }

        return $this->active ? 'Active' : 'Inactive';
    }

    /**
     * Format the start_time attribute as HH:MM for display.
     */
    public function startTime(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->formatTime($value),
        );
    }

    /**
     * Format the end_time attribute as HH:MM for display.
     */
    public function endTime(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->formatTime($value),
        );
    }

    /**
     * Normalize a database time string to HH:MM.
     */
    protected function formatTime(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Already in HH:MM
        if (preg_match('/^\d{2}:\d{2}$/', $value) === 1) {
            return $value;
        }

        // Common MySQL TIME format HH:MM:SS → trim seconds
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value) === 1) {
            return substr($value, 0, 5);
        }

        // Fallback: attempt to parse and format
        try {
            return \Carbon\Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return $value;
        }
    }
}
