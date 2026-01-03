<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeamRequest;
use App\Http\Requests\Admin\UpdateTeamRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): mixed
    {
        $teams = Team::query()->withTrashed()->paginate(10);

        return Inertia::render('admin/teams/Index', compact('teams'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $users = User::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('admin/teams/Create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('teams/logos', 'public');
            $data['logo'] = Storage::url($path);
        }

        $userIds = $data['users'] ?? [];
        unset($data['users']);

        $team = Team::query()->create($data);
        if (! empty($userIds)) {
            $team->users()->sync($userIds);
        }

        return redirect()->route('admin.teams.show', $team)->with('success', __('pages.settings.teams.messages.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team): Response
    {
        $team->load(['users' => function ($query) {
            $query->select('users.id', 'users.name', 'users.email')
                ->withPivot(['role', 'status', 'application']);
        }]);

        $availableRoles = \Spatie\Permission\Models\Role::where('team_id', $team->id)->pluck('name');

        return Inertia::render('admin/teams/Show', [
            'team' => $team,
            'availableRoles' => $availableRoles,
        ]);
    }

    /**
     * Update a member's role or status in the team.
     */
    public function updateMember(\Illuminate\Http\Request $request, Team $team, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['nullable', 'string', 'in:'.RolesEnum::BOARD_CHAIRMAN->value.','.RolesEnum::BOARD_MEMBER->value.','.RolesEnum::CREW->value.','.RolesEnum::INSTRUCTOR->value],
            'status' => ['required', 'string', 'in:pending,approved,rejected'],
        ]);

        $team->users()->updateExistingPivot($user->id, [
            'status' => $validated['status'],
            'role' => $validated['role'],
        ]);

        setPermissionsTeamId($team->id);
        if ($validated['status'] === 'approved' && ! empty($validated['role'])) {
            // Sync role to Spatie too
            $user->assignRole($validated['role']);
        } else {
            // Remove role from Spatie if rejected or no role
            $user->roles()->detach();
        }

        return redirect()->back()->with('success', __('pages.settings.teams.messages.member_updated'));
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, User $user): RedirectResponse
    {
        $team->users()->detach($user->id);

        setPermissionsTeamId($team->id);
        $user->roles()->detach(); // Remove all roles for this team context

        return redirect()->back()->with('success', __('pages.settings.teams.messages.member_removed'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team): Response
    {
        $users = User::query()->select('id', 'name')->orderBy('name')->get();
        $team->load('users:id');

        return Inertia::render('admin/teams/Edit', compact('team', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('logo')) {
            if ($team->logo && str_contains($team->logo, 'storage/teams/logos')) {
                $oldPath = str_replace(Storage::url(''), '', $team->logo);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('logo')->store('teams/logos', 'public');
            $data['logo'] = Storage::url($path);
        }

        $userIds = $data['users'] ?? null;
        unset($data['users']);

        $team->update($data);
        if ($userIds !== null) {
            $team->users()->sync($userIds);
        }

        return redirect()->route('admin.teams.show', $team)->with('success', __('pages.settings.teams.messages.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();

        return redirect()->route('admin.teams.index')->with('success', __('pages.settings.teams.messages.deleted'));
    }

    public function restore(int $id): RedirectResponse
    {
        $team = Team::withTrashed()->findOrFail($id);
        $team->restore();

        return redirect()->route('admin.teams.index')->with('success', __('pages.settings.teams.messages.restored'));
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $team = Team::withTrashed()->findOrFail($id);
        $team->forceDelete();

        return redirect()->route('admin.teams.index')->with('success', __('pages.settings.teams.messages.force_deleted'));
    }
}
