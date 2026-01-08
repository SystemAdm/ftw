<?php

namespace App\Http\Controllers;

use App\Enums\RolesEnum;
use App\Models\Team;
use App\Models\Weekday;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return Inertia::render('teams/Index', [
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
            'applications_enabled' => (bool) $team->applications_enabled,
        ];

        $isMember = false;
        if (auth()->check()) {
            $isMember = auth()->user()->teams()->where('team_id', $team->id)->exists();
        }

        return Inertia::render('teams/Show', [
            'team' => $t,
            'upcoming' => $upcoming,
            'isMember' => $isMember,
        ]);
    }

    public function apply(Request $request, Team $team): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (! $team->applications_enabled) {
            return back()->with('error', 'Applications are currently disabled for this team.');
        }

        if ($user->teams()->where('team_id', $team->id)->exists()) {
            return back()->with('error', 'You have already applied for or are a member of this team.');
        }

        $validated = $request->validate([
            'application' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $user->teams()->attach($team->id, [
            'status' => 'pending',
            'role' => RolesEnum::CREW->value,
            'application' => $validated['application'],
        ]);

        return back()->with('success', 'Your application for '.$team->name.' has been submitted.');
    }

    // Public controller only needs index/show

    // Public controller only needs index/show

    // Public controller only needs index/show
}
