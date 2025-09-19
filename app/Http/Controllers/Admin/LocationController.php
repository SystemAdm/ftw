<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'active' => $request->has('active') ? $request->boolean('active') : null,
            'trashed' => $request->string('trashed')->toString(), // all | only | without
        ];
        $sort = [
            'by' => $request->string('sort_by')->toString() ?: 'name',
            'dir' => strtolower($request->string('sort_dir')->toString() ?: 'asc'), // asc | desc
        ];

        $query = Location::query();

        // trashed filter
        switch ($filters['trashed'] === 'without' ? '' : $filters['trashed']) {
            case 'only':
                $query->onlyTrashed();
                break;
            case 'all':
                $query->withTrashed();
                break;
            default:
                // without trashed by default
                break;
        }

        // search across name and postal_code
        if ($filters['search']) {
            $q = '%' . $filters['search'] . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', $q)
                    ->orWhere('postal_code', 'like', $q);
            });
        }

        // active filter
        if ($filters['active'] !== null) {
            $query->where('active', $filters['active']);
        }

        // whitelist sortable columns
        $sortable = ['name', 'active', 'postal_code', 'created_at'];
        $by = in_array($sort['by'], $sortable, true) ? $sort['by'] : 'name';
        $dir = $sort['dir'] === 'desc' ? 'desc' : 'asc';
        $query->orderBy($by, $dir);

        $locations = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Location/Index', [
            'locations' => $locations,
            'filters' => $filters,
            'sort' => [
                'by' => $by,
                'dir' => $dir,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Location/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'postal_code' => ['required', 'integer', 'exists:postal_codes,postal_code'],
            'name' => ['required', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'string', 'max:2048'],
            'images' => ['nullable', 'string'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:2048'],
        ]);

        // Ensure active is set (unchecked checkboxes may be missing)
        $validated['active'] = (bool) ($validated['active'] ?? false);

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $location = Location::withTrashed()->findOrFail($id);
        return Inertia::render('Admin/Location/Edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'postal_code' => ['required', 'integer', 'exists:postal_codes,postal_code'],
            'name' => ['required', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'string', 'max:2048'],
            'images' => ['nullable', 'string'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:2048'],
        ]);

        $validated['active'] = (bool) ($validated['active'] ?? false);

        $location = Location::withTrashed()->findOrFail($id);
        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $location = Location::withTrashed()->findOrFail($id);
        if ($location->trashed()) {
            $location->restore();
        }

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location restored.');
    }

    /**
     * Permanently delete the specified resource from storage.
     */
    public function forceDestroy(string $id)
    {
        $location = Location::withTrashed()->findOrFail($id);
        if ($location->trashed()) {
            $location->forceDelete();
        } else {
            $location->forceDelete();
        }

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location permanently deleted.');
    }
}
