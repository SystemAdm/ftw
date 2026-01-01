<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Weekday;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    /**
     * Display a listing of public locations.
     */
    public function index(): Response
    {
        /** @var LengthAwarePaginator $locations */
        $locations = Location::query()
            ->with(['postalCode'])
            ->where('active', true)
            ->orderBy('name')
            ->paginate(15)
            ->through(function (Location $loc): array {
                return [
                    'id' => $loc->id,
                    'name' => $loc->name,
                    'postal' => $loc->postal, // accessor combining code + city
                    'street_address' => $loc->street_address,
                    'street_number' => $loc->street_number,
                    'latitude' => $loc->latitude,
                    'longitude' => $loc->longitude,
                ];
            });

        return Inertia::render('locations/Index', [
            'locations' => $locations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display a specific location with upcoming weekdays at this location.
     */
    public function show(Location $location): Response
    {
        // Prepare upcoming assignments for the next 14 days at this location
        $today = Carbon::today();

        // Preload all weekdays for this location grouped by weekday number
        $weekdays = Weekday::query()
            ->where('location_id', $location->id)
            ->with(['exclusions', 'team:id,name,slug'])
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

            // Exclude dates explicitly excluded
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
                    'team' => $first->team ? [
                        'id' => $first->team->id,
                        'name' => $first->team->name,
                        'slug' => $first->team->slug,
                    ] : null,
                ];
            }
        }

        // Shape location details for the page
        $loc = [
            'id' => $location->id,
            'name' => $location->name,
            'description' => $location->description,
            'postal' => $location->postal,
            'street_address' => $location->street_address,
            'street_number' => $location->street_number,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'google_maps_url' => $location->google_maps_url,
            'link' => $location->link,
        ];

        return Inertia::render('locations/Show', [
            'location' => $loc,
            'upcoming' => $upcoming,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
