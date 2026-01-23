<?php

namespace App\Support;

use App\Models\Weekday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OpeningHours
{
    /**
     * Get the opening hours for a 7-day window.
     */
    public function getForWeek(int $weekOffset = 0): array
    {
        if ($weekOffset < 0) {
            $weekOffset = 0;
        }

        $start = Carbon::today()->copy()->addDays($weekOffset * 7);

        // Preload weekdays with exclusions to avoid N+1 queries
        $weekdays = Weekday::query()
            ->with(['exclusions', 'team:id,name,slug'])
            ->get()
            ->groupBy('weekday'); // 0 = Sunday ... 6 = Saturday

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $weekdayNumber = $date->dayOfWeek; // 0..6

            /** @var Collection<int, Weekday> $candidates */
            $candidates = $weekdays->get($weekdayNumber, collect());

            // Filter candidates that are active, not ended, and within optional date range
            $eligible = $candidates->filter(function (Weekday $w) use ($date): bool {
                if ($w->active !== true || $w->is_ended === true) {
                    return false;
                }
                if ($w->event_start !== null && $date->lt(Carbon::parse($w->event_start)->startOfDay())) {
                    return false;
                }
                if ($w->event_end !== null && $date->gt(Carbon::parse($w->event_end)->endOfDay())) {
                    return false;
                }

                // Filter by week type (all, odd, even)
                if ($w->week_type !== 'all') {
                    $isOddWeek = $date->weekOfYear % 2 !== 0;
                    if ($w->week_type === 'odd' && ! $isOddWeek) {
                        return false;
                    }
                    if ($w->week_type === 'even' && $isOddWeek) {
                        return false;
                    }
                }

                // Filter by month occurrence (all, first, second, third, fourth, last)
                if ($w->month_occurrence !== 'all') {
                    $occurrence = (int) ceil($date->day / 7);
                    $isLast = $date->copy()->addWeek()->month !== $date->month;

                    if ($w->month_occurrence === 'first' && $occurrence !== 1) {
                        return false;
                    }
                    if ($w->month_occurrence === 'second' && $occurrence !== 2) {
                        return false;
                    }
                    if ($w->month_occurrence === 'third' && $occurrence !== 3) {
                        return false;
                    }
                    if ($w->month_occurrence === 'fourth' && $occurrence !== 4) {
                        return false;
                    }
                    if ($w->month_occurrence === 'last' && ! $isLast) {
                        return false;
                    }
                }

                return true;
            });

            // Separate those not excluded for this exact calendar date
            $eligibleNotExcluded = $eligible->filter(function (Weekday $w) use ($date): bool {
                $isExcluded = $w->exclusions->contains(function ($exclusion) use ($date): bool {
                    $excluded = $exclusion->excluded_date instanceof Carbon
                        ? $exclusion->excluded_date
                        : Carbon::parse($exclusion->excluded_date);

                    return $excluded->isSameDay($date);
                });

                return $isExcluded === false;
            });

            $hasWeekday = $eligibleNotExcluded->isNotEmpty();
            $isExcluded = $hasWeekday === false && $eligible->isNotEmpty();

            // Prepare entries for all eligible weekdays
            $entries = $eligible->map(function (Weekday $w) use ($date) {
                $isThisExcluded = $w->exclusions->contains(function ($exclusion) use ($date): bool {
                    $excluded = $exclusion->excluded_date instanceof Carbon
                        ? $exclusion->excluded_date
                        : Carbon::parse($exclusion->excluded_date);

                    return $excluded->isSameDay($date);
                });

                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'description' => $w->description,
                    'start_time' => $w->start_time,
                    'end_time' => $w->end_time,
                    'week_type' => $w->week_type,
                    'month_occurrence' => $w->month_occurrence,
                    'is_excluded' => $isThisExcluded,
                    'team' => $w->team ? [
                        'id' => $w->team->id,
                        'name' => $w->team->name,
                        'slug' => $w->team->slug,
                        'description' => $w->team->description,
                        'logo' => $w->team->logo,
                        'created_at' => $w->team->created_at?->translatedFormat('F Y'),
                    ] : null,
                ];
            })->values()->all();

            $days[] = [
                'date' => $date->toDateString(),
                'weekday' => $weekdayNumber,
                'label' => $date->translatedFormat('d M'),
                'has_weekday' => $hasWeekday,
                'is_excluded' => $isExcluded,
                'weekday_label' => $date->translatedFormat('l'), // Full day name, locale aware
                'entries' => $entries,
            ];
        }

        return $days;
    }
}
