<?php

namespace App\Http\Controllers\Crew;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crew\StoreWeekdayRequest;
use App\Http\Requests\Crew\UpdateWeekdayRequest;
use App\Models\Team;
use App\Models\Weekday;
use App\Models\WeekdayExcluded;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeekdaysController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        $weekdays = Weekday::query()
            ->with(['team:id,name', 'location:id,name'])
            ->when(! $isGlobalAdmin, function ($query) use ($user) {
                $query->whereIn('team_id', $user->teams->pluck('id'));
            })
            ->orderBy('weekday')
            ->paginate(15);

        return Inertia::render('crew/weekdays/Index', compact('weekdays'));
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        $teams = Team::query()
            ->when(! $isGlobalAdmin, function ($query) use ($user) {
                $query->whereIn('id', $user->teams->pluck('id'));
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $locations = \App\Models\Location::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('crew/weekdays/Create', compact('teams', 'locations'));
    }

    public function store(StoreWeekdayRequest $request): RedirectResponse
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);
        $data = $request->validated();

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $data['team_id'])) {
            abort(403);
        }

        /** @var Weekday $weekday */
        $weekday = Weekday::query()->create($data);

        return redirect()->route('crew.weekdays.show', $weekday)->with('success', __('pages.settings.weekdays.messages.created'));
    }

    public function show(Request $request, Weekday $weekday): Response
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $weekday->team_id)) {
            abort(403);
        }

        $weekday->load(['team:id,name', 'location:id,name', 'exclusions' => function ($q) {
            $q->orderBy('excluded_date');
        }]);

        return Inertia::render('crew/weekdays/Show', compact('weekday'));
    }

    public function edit(Request $request, Weekday $weekday): Response
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $weekday->team_id)) {
            abort(403);
        }

        $teams = Team::query()
            ->when(! $isGlobalAdmin, function ($query) use ($user) {
                $query->whereIn('id', $user->teams->pluck('id'));
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $locations = \App\Models\Location::query()->select('id', 'name')->orderBy('name')->get();
        $weekday->load(['exclusions' => function ($q) {
            $q->orderByDesc('excluded_date');
        }]);

        return Inertia::render('crew/weekdays/Edit', compact('weekday', 'teams', 'locations'));
    }

    public function update(UpdateWeekdayRequest $request, Weekday $weekday): RedirectResponse
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $weekday->team_id)) {
            abort(403);
        }

        $data = $request->validated();

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $data['team_id'])) {
            abort(403);
        }

        $weekday->update($data);

        return redirect()->route('crew.weekdays.show', $weekday)->with('success', __('pages.settings.weekdays.messages.updated'));
    }

    public function destroy(Request $request, Weekday $weekday): RedirectResponse
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $weekday->team_id)) {
            abort(403);
        }

        $weekday->delete();

        return redirect()->route('crew.weekdays.index')->with('success', __('pages.settings.weekdays.messages.deleted'));
    }

    public function addExclusion(Request $request, Weekday $weekday): RedirectResponse
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $weekday->team_id)) {
            abort(403);
        }

        $data = $request->validate([
            'excluded_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $weekday->exclusions()->firstOrCreate([
            'excluded_date' => $data['excluded_date'],
        ]);

        return back()->with('success', __('pages.settings.weekdays.messages.exclusion_added'));
    }

    public function removeExclusion(Request $request, Weekday $weekday, WeekdayExcluded $exclusion): RedirectResponse
    {
        $user = $request->user();
        $isGlobalAdmin = RolesEnum::userIsGlobalAdmin($user);

        if (! $isGlobalAdmin && ! $user->teams->contains('id', $weekday->team_id)) {
            abort(403);
        }

        // Ensure the exclusion belongs to this weekday
        if ($exclusion->weekday_id === $weekday->id) {
            $exclusion->delete();
        }

        return back()->with('success', __('pages.settings.weekdays.messages.exclusion_removed'));
    }
}
