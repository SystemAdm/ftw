<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
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

        $query = Permission::query()->select('id', 'name', 'guard_name', 'created_at');

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

        $permissions = $query->paginate(15)->withQueryString();

        // Add roles count for each permission
        $permissions->setCollection($permissions->getCollection()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'created_at' => $permission->created_at,
                'roles_count' => $permission->roles()->count(),
            ];
        }));

        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Permission/Index', [
            'permissions' => $permissions,
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
        $roles = Role::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Permission/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $permission = Permission::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($data['roles'])) {
            $permission->syncRoles($data['roles']);
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created');
    }

    public function show(Permission $permission): Response
    {
        $roles = $permission->roles()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Permission/Show', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
                'created_at' => $permission->created_at,
                'updated_at' => $permission->updated_at,
            ],
            'roles' => $roles,
        ]);
    }

    public function edit(Permission $permission): Response
    {
        $allRoles = Role::orderBy('name')->get(['id', 'name']);
        $permissionRoleIds = $permission->roles()->pluck('id')->toArray();

        return Inertia::render('Admin/Permission/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
            ],
            'roles' => $allRoles,
            'permissionRoles' => $permissionRoleIds,
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,' . $permission->id],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $permission->update([
            'name' => $data['name'],
        ]);

        $permission->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted');
    }
}
