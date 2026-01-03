<?php

namespace App\Http\Controllers\Crew;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamMembersController extends Controller
{
    /**
     * Update a member's role or status in the team.
     */
    public function update(Request $request, Team $team, User $user): RedirectResponse
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser->hasRole(RolesEnum::ADMIN->value);
        $isLeader = $currentUser->teams()
            ->where('team_id', $team->id)
            ->wherePivot('role', RolesEnum::BOARD_CHAIRMAN->value)
            ->wherePivot('status', 'approved')
            ->exists();

        if (! $isAdmin && ! $isLeader) {
            abort(403);
        }

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
            $user->assignRole($validated['role']);
        } else {
            $user->roles()->detach();
        }

        return redirect()->back()->with('success', __('pages.settings.teams.messages.member_updated'));
    }

    /**
     * Remove a member from the team.
     */
    public function destroy(Team $team, User $user): RedirectResponse
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser->hasRole(RolesEnum::ADMIN->value);
        $isLeader = $currentUser->teams()
            ->where('team_id', $team->id)
            ->wherePivot('role', RolesEnum::BOARD_CHAIRMAN->value)
            ->wherePivot('status', 'approved')
            ->exists();

        if (! $isAdmin && ! $isLeader) {
            abort(403);
        }

        // Cannot remove self if leader? Or maybe just allow it.
        // Actually, preventing removing the only leader might be good, but let's keep it simple.

        $team->users()->detach($user->id);

        setPermissionsTeamId($team->id);
        $user->roles()->detach();

        return redirect()->back()->with('success', __('pages.settings.teams.messages.member_removed'));
    }
}
