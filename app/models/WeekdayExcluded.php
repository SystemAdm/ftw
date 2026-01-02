<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class WeekdayExcluded extends Model
{
    use HasFactory;

    protected $table = 'weekday_excluded';

    protected $fillable = [
        'weekday_id',
        'excluded_date',
    ];

    protected $appends = [
        'excluded_date_formatted',
    ];

    protected function casts(): array
    {
        return [
            'weekday_id' => 'integer',
            'excluded_date' => 'date',
        ];
    }

    public function weekday(): BelongsTo
    {
        return $this->belongsTo(Weekday::class);
    }

    public function getExcludedDateFormattedAttribute(): string
    {
        $date = $this->excluded_date instanceof Carbon
            ? $this->excluded_date
            : Carbon::parse($this->excluded_date);

        // Example: Fri, 12 Dec 2025 (locale-aware if app locale is set)
        return $date->translatedFormat('D, d M Y');
    }
}
