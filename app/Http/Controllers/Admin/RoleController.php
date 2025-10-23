<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
        ];
        $sort = [
            'by' => $request->string('sort_by')->toString() ?: 'name',
            'dir' => strtolower($request->string('sort_dir')->toString() ?: 'asc'),
        ];

        $query = Role::query()->select('id', 'name', 'guard_name', 'created_at');

        if ($filters['search']) {
            $q = '%' . $filters['search'] . '%';
            $query->where('name', 'like', $q);
        }

        $sortable = ['name', 'created_at', 'id'];
        $by = in_array($sort['by'], $sortable, true) ? $sort['by'] : 'name';
        $dir = $sort['dir'] === 'desc' ? 'desc' : 'asc';

        // case-insensitive for name
        if ($by === 'name') {
            $query->orderByRaw('LOWER(' . $by . ') ' . ($dir === 'asc' ? 'ASC' : 'DESC'));
        } else {
            $query->orderBy($by, $dir);
        }

        $roles = $query->paginate(15)->withQueryString();

        // Add permissions count for each role
        $roles->setCollection($roles->getCollection()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'created_at' => $role->created_at,
                'permissions_count' => $role->permissions()->count(),
            ];
        }));

        return Inertia::render('Admin/Role/Index', [
            'roles' => $roles,
            'filters' => $filters,
            'sort' => [
                'by' => $by,
                'dir' => $dir,
            ],
        ]);
    }

    public function create(): Response
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Role/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created');
    }

    public function show(Role $role): Response
    {
        $permissions = $role->permissions()->orderBy('name')->get(['id', 'name']);

        // Get users with this role
        $users = User::role($role->name)
            ->select('id', 'name', 'email', 'created_at')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Role/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
            ],
            'permissions' => $permissions,
            'users' => $users,
        ]);
    }

    public function edit(Role $role): Response
    {
        $allPermissions = Permission::orderBy('name')->get(['id', 'name']);
        $rolePermissionIds = $role->permissions()->pluck('id')->toArray();

        return Inertia::render('Admin/Role/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
            ],
            'permissions' => $allPermissions,
            'rolePermissions' => $rolePermissionIds,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update([
            'name' => $data['name'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.edit', $role)->with('success', 'Role updated');
    }

    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deletion of critical system roles
        $protectedRoles = ['admin', 'super-admin'];

        if (in_array($role->name, $protectedRoles, true)) {
            return back()->with('error', 'Cannot delete protected system role');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted');
    }

    /**
     * Search for users to assign the role
     */
    public function searchUsers(Request $request, Role $role)
    {
        $search = $request->string('search')->toString();

        if (empty($search)) {
            return response()->json(['users' => []]);
        }

        $users = User::where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->select('id', 'name', 'email')
            ->limit(10)
            ->get();

        return response()->json(['users' => $users]);
    }

    /**
     * Assign role to a user
     */
    public function assignUser(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::findOrFail($data['user_id']);

        if (!$user->hasRole($role->name)) {
            $user->assignRole($role->name);
        }

        return back()->with('success', 'Role assigned to user');
    }

    /**
     * Remove role from a user
     */
    public function removeUser(Request $request, Role $role, User $user): RedirectResponse
    {
        if ($user->hasRole($role->name)) {
            $user->removeRole($role->name);
        }

        return back()->with('success', 'Role removed from user');
    }
}
