<?php

namespace App\Http\Controllers\Crew;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class TeamsController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $myTeams = $user->teams()
            ->withPivot(['role', 'status', 'application'])
            ->get();

        $myTeamIds = $myTeams->pluck('id');

        $availableTeams = Team::query()
            ->where('active', true)
            ->where('applications_enabled', true)
            ->whereNotIn('id', $myTeamIds)
            ->get();

        return Inertia::render('crew/teams/Index', [
            'myTeams' => $myTeams,
            'availableTeams' => $availableTeams,
        ]);
    }

    public function show(Team $team): Response
    {
        $user = auth()->user();

        // Ensure user is member of team or is admin
        $isMember = $user->teams()->where('team_id', $team->id)->wherePivot('status', 'approved')->exists();
        $isAdmin = $user->hasRole([RolesEnum::ADMIN->value, RolesEnum::OWNER->value]);

        if (! $isMember && ! $isAdmin) {
            abort(403);
        }

        $team->load(['users' => function ($query) {
            $query->select('users.id', 'users.name', 'users.email', 'users.avatar')
                ->withPivot(['role', 'status', 'application']);
        }]);

        // Check if user is "privileged" (Global Admin, or Board Chairman of this team)
        $isLeader = $user->teams()
            ->where('team_id', $team->id)
            ->wherePivot('role', RolesEnum::BOARD_CHAIRMAN->value)
            ->wherePivot('status', 'approved')
            ->exists();

        $availableRoles = Role::where('team_id', $team->id)->pluck('name');

        return Inertia::render('crew/teams/Show', [
            'team' => $team,
            'isPrivileged' => $isAdmin || $isLeader,
            'availableRoles' => $availableRoles,
        ]);
    }

    public function apply(\Illuminate\Http\Request $request, Team $team): RedirectResponse
    {
        $user = auth()->user();

        if (! $team->applications_enabled) {
            return back()->with('error', 'Applications are currently disabled for this team.');
        }

        if ($user->teams()->where('team_id', $team->id)->exists()) {
            return back()->with('error', 'You have already applied for or are a member of this team.');
        }

        $validated = $request->validate([
            'application' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $user->teams()->attach($team->id, [
            'status' => 'pending',
            'role' => RolesEnum::CREW->value,
            'application' => $validated['application'],
        ]);

        return back()->with('success', 'Your application for '.$team->name.' has been submitted.');
    }

    public function leave(Team $team): RedirectResponse
    {
        $user = auth()->user();

        $user->teams()->detach($team->id);

        setPermissionsTeamId($team->id);
        $user->roles()->detach();

        return back()->with('success', 'You have left '.$team->name.'.');
    }
}
