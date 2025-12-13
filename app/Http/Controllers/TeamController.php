<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Weekday;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(): Response
    {
        /** @var LengthAwarePaginator $teams */
        $teams = Team::query()
            ->where('active', true)
            ->orderBy('name')
            ->paginate(15)
            ->through(function (Team $team): array {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'slug' => $team->slug,
                    'logo' => $team->logo,
                    'description' => $team->description,
                ];
            });

        return Inertia::render('teams/index', [
            'teams' => $teams,
        ]);
    }

    // Public controller only needs index/show

    // Public controller only needs index/show

    public function show(Team $team): Response
    {
        $today = Carbon::today();

        // Get this team's weekdays grouped by weekday number
        $weekdays = Weekday::query()
            ->where('team_id', $team->id)
            ->with(['exclusions', 'location:id,name'])
            ->get()
            ->groupBy('weekday');

        $upcoming = [];
        for ($i = 0; $i < 14; $i++) {
            $date = $today->copy()->addDays($i);
            $weekdayNumber = $date->dayOfWeek; // 0..6

            $candidates = $weekdays->get($weekdayNumber, collect());

            // Filter eligible by active/date range
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

                return true;
            });

            // Exclude explicitly excluded dates
            $eligibleNotExcluded = $eligible->filter(function (Weekday $w) use ($date): bool {
                $isExcluded = $w->exclusions->contains(function ($exclusion) use ($date): bool {
                    $excluded = $exclusion->excluded_date instanceof Carbon
                        ? $exclusion->excluded_date
                        : Carbon::parse($exclusion->excluded_date);

                    return $excluded->isSameDay($date);
                });

                return $isExcluded === false;
            });

            if ($eligibleNotExcluded->isNotEmpty()) {
                /** @var Weekday $first */
                $first = $eligibleNotExcluded->first();
                $upcoming[] = [
                    'date' => $date->toDateString(),
                    'label' => $date->translatedFormat('d M'),
                    'weekday' => $weekdayNumber,
                    'weekday_label' => $date->translatedFormat('l'),
                    'name' => $first->name,
                    'description' => $first->description,
                    'start_time' => $first->start_time,
                    'end_time' => $first->end_time,
                    'location' => $first->location ? [
                        'id' => $first->location->id,
                        'name' => $first->location->name,
                    ] : null,
                ];
            }
        }

        $t = [
            'id' => $team->id,
            'name' => $team->name,
            'slug' => $team->slug,
            'description' => $team->description,
            'logo' => $team->logo,
            'active' => (bool) $team->active,
        ];

        return Inertia::render('teams/show', [
            'team' => $t,
            'upcoming' => $upcoming,
        ]);
    }

    // Public controller only needs index/show

    // Public controller only needs index/show

    // Public controller only needs index/show
}
