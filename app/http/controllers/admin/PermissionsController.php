<?php

namespace App\http\controllers\admin;

use App\http\controllers\Controller;
use App\http\requests\admin\StorePermissionRequest;
use App\http\requests\admin\UpdatePermissionRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): mixed
    {
        $permissions = Permission::query()
            ->withCount(['roles', 'users'])
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('admin/permissions/Index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/permissions/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['guard_name'])) {
            $data['guard_name'] = config('auth.defaults.guard', 'web');
        }

        $permission = Permission::query()->create($data);

        return redirect()->route('admin.permissions.show', $permission)->with('success', __('pages.settings.permissions.messages.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission): Response
    {
        $permission->load(['roles:id,name', 'users:id,name,email']);

        return Inertia::render('admin/permissions/Show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission): Response
    {
        return Inertia::render('admin/permissions/Edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['guard_name'])) {
            $data['guard_name'] = config('auth.defaults.guard', 'web');
        }

        $permission->update($data);

        return redirect()->route('admin.permissions.show', $permission)->with('success', __('pages.settings.permissions.messages.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', __('pages.settings.permissions.messages.deleted'));
    }
}
