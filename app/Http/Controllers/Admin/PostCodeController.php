<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostalCode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'country' => $request->string('country')->toString(),
            'state' => $request->string('state')->toString(),
            'trashed' => $request->string('trashed')->toString(), // all | only | without
        ];
        $sort = [
            'by' => $request->string('sort_by')->toString() ?: 'postal_code',
            'dir' => strtolower($request->string('sort_dir')->toString() ?: 'asc'), // asc | desc
        ];

        $query = PostalCode::query();

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

        // search across postal_code and city
        if ($filters['search']) {
            $q = '%' . $filters['search'] . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('postal_code', 'like', $q)
                    ->orWhere('city', 'like', $q);
            });
        }

        if ($filters['country']) {
            $query->where('country', 'like', '%' . $filters['country'] . '%');
        }
        if ($filters['state']) {
            $query->where('state', 'like', '%' . $filters['state'] . '%');
        }

        // whitelist sortable columns
        $sortable = ['postal_code', 'city', 'state', 'country', 'county', 'created_at'];
        $by = in_array($sort['by'], $sortable, true) ? $sort['by'] : 'postal_code';
        $dir = $sort['dir'] === 'desc' ? 'desc' : 'asc';
        $query->orderBy($by, $dir);

        $codes = $query->get();

        return Inertia::render('Admin/PostCode/Index', [
            'codes' => $codes,
            'filters' => $filters,
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/PostCode/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'postal_code' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
        ]);

        PostalCode::create($validated);

        return redirect()->route('admin.postcodes.index')
            ->with('success', 'Postal code created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $code = PostalCode::withTrashed()->findOrFail($id);
        return Inertia::render('Admin/PostCode/Edit', compact('code'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'postal_code' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
        ]);

        $code = PostalCode::withTrashed()->findOrFail($id);
        $code->update($validated);

        return redirect()->route('admin.postcodes.index')
            ->with('success', 'Postal code updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $code = PostalCode::findOrFail($id);
        $code->delete();

        return redirect()->route('admin.postcodes.index')
            ->with('success', 'Postal code deleted.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $code = PostalCode::withTrashed()->findOrFail($id);
        if ($code->trashed()) {
            $code->restore();
        }

        return redirect()->route('admin.postcodes.index')
            ->with('success', 'Postal code restored.');
    }

    /**
     * Permanently delete the specified resource from storage.
     */
    public function forceDestroy(string $id)
    {
        $code = PostalCode::withTrashed()->findOrFail($id);
        if ($code->trashed()) {
            $code->forceDelete();
        } else {
            // If it's not trashed, do a force delete anyway only if you allow that
            $code->forceDelete();
        }

        return redirect()->route('admin.postcodes.index')
            ->with('success', 'Postal code permanently deleted.');
    }
}
