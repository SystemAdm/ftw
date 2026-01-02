<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): mixed
    {
        $roles = Role::query()
            ->withCount('users')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('admin/roles/Index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $permissions = Permission::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/roles/Create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $permissionIds = $data['permissions'] ?? [];
        unset($data['permissions']);

        if (empty($data['guard_name'])) {
            $data['guard_name'] = config('auth.defaults.guard', 'web');
        }

        $role = Role::query()->create($data);
        if (! empty($permissionIds)) {
            $role->syncPermissions(Permission::query()->whereIn('id', $permissionIds)->pluck('name')->all());
        }

        return redirect()->route('admin.roles.show', $role)->with('success', __('pages.settings.roles.messages.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): Response
    {
        $role->load(['permissions:id,name', 'users:id,name,email']);
        $permissions = Permission::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/roles/Show', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): Response
    {
        $permissions = Permission::query()->select('id', 'name')->orderBy('name')->get();
        $role->load('permissions:id');

        return Inertia::render('admin/roles/Edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        $permissionIds = $data['permissions'] ?? null;
        unset($data['permissions']);

        if (empty($data['guard_name'])) {
            $data['guard_name'] = config('auth.defaults.guard', 'web');
        }

        $role->update($data);
        if ($permissionIds !== null) {
            $role->syncPermissions(Permission::query()->whereIn('id', $permissionIds)->pluck('name')->all());
        }

        return redirect()->route('admin.roles.show', $role)->with('success', __('pages.settings.roles.messages.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', __('pages.settings.roles.messages.deleted'));
    }

    public function searchUsers(Role $role): JsonResponse
    {
        $term = request('q');
        $query = User::query()->select('id', 'name', 'email')->orderBy('name');
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }
        // Exclude users already having this role
        $userIdsWithRole = $role->users()->pluck('users.id')->all();
        if (! empty($userIdsWithRole)) {
            $query->whereNotIn('id', $userIdsWithRole);
        }

        $users = $query->limit(10)->get();

        return response()->json(['data' => $users]);
    }

    public function assignUser(Role $role): RedirectResponse
    {
        $validated = request()->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->assignRole($role);

        return redirect()->route('admin.roles.show', $role)->with('success', __('pages.settings.roles.messages.assigned'));
    }

    public function removeUser(Role $role, User $user): RedirectResponse
    {
        $user->removeRole($role);

        return redirect()->route('admin.roles.show', $role)->with('success', __('pages.settings.roles.messages.removed'));
    }
}
