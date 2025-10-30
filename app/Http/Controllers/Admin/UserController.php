<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
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

        $query = User::query()->with('roles:id,name')->select('id', 'name', 'email', 'avatar', 'created_at', 'banned_at', 'banned_to');

        if ($filters['search']) {
            $q = '%' . $filters['search'] . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', $q)
                    ->orWhere('email', 'like', $q);
            });
        }

        $sortable = ['name', 'email', 'created_at', 'id'];
        $by = in_array($sort['by'], $sortable, true) ? $sort['by'] : 'name';
        $dir = $sort['dir'] === 'desc' ? 'desc' : 'asc';

        // case-insensitive for name/email
        if (in_array($by, ['name', 'email'], true)) {
            $query->orderByRaw('LOWER(' . $by . ') ' . ($dir === 'asc' ? 'ASC' : 'DESC'));
        } else {
            $query->orderBy($by, $dir);
        }

        $users = $query->paginate(15)->withQueryString();
        // Append is_banned boolean for each user in the paginated collection
        $users->setCollection($users->getCollection()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avatar' => $u->avatar,
                'created_at' => $u->created_at,
                'is_banned' => method_exists($u, 'isBanned') ? $u->isBanned() : false,
                'roles' => $u->roles->map(fn($role) => ['id' => $role->id, 'name' => $role->name]),
            ];
        }));

        return Inertia::render('Admin/User/Index', [
            'users' => $users,
            'filters' => $filters,
            'sort' => [
                'by' => $by,
                'dir' => $dir,
            ],
        ]);
    }

    public function show(User $user): Response
    {
        $user->load('postalCode');
        $verifier = $user->verified_by ? User::query()->select('id', 'name')->find($user->verified_by) : null;
        $bans = $user->bans()->with('admin:id,name')->orderByDesc('banned_at')->get()->map(function($ban) {
            return [
                'id' => $ban->id,
                'banned_at' => $ban->banned_at,
                'banned_to' => $ban->banned_to,
                'reason' => $ban->reason,
                'admin' => $ban->admin ? ['id' => $ban->admin->id, 'name' => $ban->admin->name] : null,
            ];
        });

        return Inertia::render('Admin/User/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'birthday' => $user->birthday?->format('Y-m-d'),
                'postal_code' => $user->postalCode ? [
                    'code' => $user->postalCode->postal_code,
                    'city' => $user->postalCode->city,
                ] : null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'verified_at' => $user->verified_at,
                'verified_by' => $user->verified_by,
                'verified_by_user' => $verifier ? [
                    'id' => $verifier->id,
                    'name' => $verifier->name,
                ] : null,
                'banned_at' => $user->banned_at,
                'banned_to' => $user->banned_to,
                'ban_reason' => $user->ban_reason,
                'is_banned' => method_exists($user, 'isBanned') && $user->isBanned(),
            ],
            'bans' => $bans,
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load('postalCode');
        $allRoles = \Spatie\Permission\Models\Role::all(['id', 'name']);
        $userRoleIds = $user->roles->pluck('id')->toArray();

        return Inertia::render('Admin/User/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'birthday' => $user->birthday?->format('Y-m-d'),
                'postal_code' => $user->postal_code,
                'role_ids' => $userRoleIds,
            ],
            'roles' => $allRoles->map(fn($role) => ['id' => $role->id, 'name' => $role->name]),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'avatar' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'postal_code' => ['nullable', 'integer', 'exists:postal_codes,postal_code'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'avatar' => $data['avatar'] ?? $user->avatar,
            'birthday' => $data['birthday'] ?? $user->birthday,
            'postal_code' => $data['postal_code'] ?? $user->postal_code,
        ]);

        // Sync roles
        if (isset($data['role_ids'])) {
            $user->syncRoles($data['role_ids']);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('admin.users.edit', $user)->with('success', 'User updated');
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        if (!$user->verified_at) {
            $user->verified_at = now();
            $user->verified_by = $request->user()->id;
            $user->save();
        }
        return redirect()->route('admin.users.show', $user)->with('success', 'User verified');
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
            'banned_to' => ['nullable', 'date'],
        ]);

        $user->banned_at = now();
        $user->banned_by = $request->user()->id;
        $user->banned_to = $data['banned_to'] ?? null;
        $user->ban_reason = $data['reason'] ?? null; // keep column name ban_reason
        $user->save();

        // Log ban history
        \App\Models\UserBan::create([
            'user_id' => $user->id,
            'banned_at' => $user->banned_at,
            'banned_to' => $user->banned_to,
            'banned_by' => $user->banned_by,
            'reason' => $user->ban_reason,
        ]);

        return back()->with('success', 'User banned');
    }

    public function unban(Request $request, User $user): RedirectResponse
    {
        $user->banned_at = null;
        $user->banned_by = null;
        $user->banned_to = null;
        $user->ban_reason = null;
        $user->save();

        return back()->with('success', 'User unbanned');
    }

    public function bans(Request $request, User $user)
    {
        $bans = $user->bans()->with('admin:id,name')->orderByDesc('banned_at')->get()->map(function($ban) {
            return [
                'id' => $ban->id,
                'banned_at' => $ban->banned_at,
                'banned_to' => $ban->banned_to,
                'reason' => $ban->reason,
                'admin' => $ban->admin ? ['id' => $ban->admin->id, 'name' => $ban->admin->name] : null,
            ];
        });
        return response()->json(['data' => $bans]);
    }
}
