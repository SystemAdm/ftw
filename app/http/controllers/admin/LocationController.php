<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLocationRequest;
use App\Http\Requests\Admin\UpdateLocationRequest;
use App\Models\Location;
use App\Models\PostalCode;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(): mixed
    {
        $locations = Location::query()
            // Limit relation payload to the fields we actually need in the table
            ->with(['postalCode:postal_code,city'])
            ->withTrashed()
            ->latest('id')
            ->paginate(15);

        // Append computed attributes without globally adding to all queries
        $locations->getCollection()->each->append('postal');

        return Inertia::render('admin.locations.Index', compact('locations'));
    }

    public function create(): Response
    {
        $postalCodes = PostalCode::query()->orderBy('postal_code')->get(['postal_code', 'city']);

        return Inertia::render('admin/locations/Create', compact('postalCodes'));
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $location = Location::query()->create($request->validated());

        return redirect()->route('admin.locations.show', $location)->with('success', __('pages.settings.locations.messages.created'));
    }

    public function show(Location $location): Response
    {
        $location->load(['postalCode:postal_code,city'])->append('postal');

        return Inertia::render('admin/locations/Show', compact('location'));
    }

    public function edit(Location $location): Response
    {
        $postalCodes = PostalCode::query()->orderBy('postal_code')->get(['postal_code', 'city']);
        $location->load(['postalCode:postal_code,city'])->append('postal');

        return Inertia::render('admin/locations/Edit', compact('location', 'postalCodes'));
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()->route('admin.locations.show', $location)->with('success', __('pages.settings.locations.messages.updated'));
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', __('pages.settings.locations.messages.deleted'));
    }

    public function restore(int $id): RedirectResponse
    {
        $location = Location::withTrashed()->findOrFail($id);
        $location->restore();

        return redirect()->route('admin.locations.index')->with('success', __('pages.settings.locations.messages.restored'));
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $location = Location::withTrashed()->findOrFail($id);
        $location->forceDelete();

        return redirect()->route('admin.locations.index')->with('success', __('pages.settings.locations.messages.force_deleted'));
    }
}
