<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWeekdayRequest;
use App\Http\Requests\Admin\UpdateWeekdayRequest;
use App\Models\Team;
use App\Models\Weekday;
use App\Models\WeekdayExcluded;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WeekdaysController extends Controller
{
    public function index(): mixed
    {
        $weekdays = Weekday::query()
            ->with(['team:id,name', 'location:id,name'])
            ->orderBy('weekday')
            ->paginate(15);

        return Inertia::render('admin/weekdays/index', compact('weekdays'));
    }

    public function create(): Response
    {
        $teams = Team::query()->select('id', 'name')->orderBy('name')->get();
        $locations = \App\Models\Location::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/weekdays/create', compact('teams', 'locations'));
    }

    public function store(StoreWeekdayRequest $request): RedirectResponse
    {
        $data = $request->validated();

        /** @var Weekday $weekday */
        $weekday = Weekday::query()->create($data);

        return redirect()->route('admin.weekdays.show', $weekday)->with('success', 'Weekday created.');
    }

    public function show(Weekday $weekday): Response
    {
        $weekday->load(['team:id,name', 'location:id,name', 'exclusions' => function ($q) {
            $q->orderBy('excluded_date');
        }]);

        return Inertia::render('admin/weekdays/show', compact('weekday'));
    }

    public function edit(Weekday $weekday): Response
    {
        $teams = Team::query()->select('id', 'name')->orderBy('name')->get();
        $locations = \App\Models\Location::query()->select('id', 'name')->orderBy('name')->get();
        $weekday->load(['exclusions' => function ($q) {
            $q->orderByDesc('excluded_date');
        }]);

        return Inertia::render('admin/weekdays/edit', compact('weekday', 'teams', 'locations'));
    }

    public function update(UpdateWeekdayRequest $request, Weekday $weekday): RedirectResponse
    {
        $weekday->update($request->validated());

        return redirect()->route('admin.weekdays.show', $weekday)->with('success', 'Weekday updated.');
    }

    public function destroy(Weekday $weekday): RedirectResponse
    {
        $weekday->delete();

        return redirect()->route('admin.weekdays.index')->with('success', 'Weekday deleted.');
    }

    public function addExclusion(Request $request, Weekday $weekday): RedirectResponse
    {
        $data = $request->validate([
            'excluded_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $weekday->exclusions()->firstOrCreate([
            'excluded_date' => $data['excluded_date'],
        ]);

        return back()->with('success', 'Exclusion added.');
    }

    public function removeExclusion(Weekday $weekday, WeekdayExcluded $exclusion): RedirectResponse
    {
        // Ensure the exclusion belongs to this weekday
        if ($exclusion->weekday_id === $weekday->id) {
            $exclusion->delete();
        }

        return back()->with('success', 'Exclusion removed.');
    }
}
