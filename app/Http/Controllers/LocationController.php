<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    /**
     * Display a listing of public locations (only active, not trashed).
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'sort_by' => $request->string('sort_by')->toString() ?: 'name',
            'sort_dir' => strtolower($request->string('sort_dir')->toString() ?: 'asc'),
        ];

        $query = Location::query()->where('active', true);

        if ($filters['search']) {
            $q = '%' . $filters['search'] . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', $q)
                    ->orWhere('postal_code', 'like', $q);
            });
        }

        $sortable = ['name', 'postal_code', 'created_at'];
        $by = in_array($filters['sort_by'], $sortable, true) ? $filters['sort_by'] : 'name';
        $dir = $filters['sort_dir'] === 'desc' ? 'desc' : 'asc';
        $query->orderBy($by, $dir);

        $locations = $query->get();

        return Inertia::render('Location/Index', [
            'locations' => $locations,
            'filters' => [
                'search' => $filters['search'],
            ],
            'sort' => [
                'by' => $by,
                'dir' => $dir,
            ],
        ]);
    }

    /**
     * Display the specified public location.
     */
    public function show(Location $location)
    {
        if (!$location->active) {
            abort(404);
        }

        return Inertia::render('Location/Show', [
            'location' => $location,
        ]);
    }
}
