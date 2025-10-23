<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GuardianController extends Controller
{
    /**
     * Display the guardian management page.
     */
    public function edit(Request $request)
    {
        $user = Auth::user();

        // Load guardians with pivot data
        $guardians = $user->guardians()
            ->get()
            ->map(function ($guardian) {
                return [
                    'id' => $guardian->id,
                    'name' => $guardian->name,
                    'email' => $guardian->email,
                    'relationship' => $guardian->pivot->relationship,
                    'confirmed_guardian' => (bool) $guardian->pivot->confirmed_guardian,
                    'confirmed_admin' => (bool) $guardian->pivot->confirmed_admin,
                ];
            });

        // Load minors (children) with pivot data
        $minors = $user->minors()
            ->get()
            ->map(function ($minor) {
                return [
                    'id' => $minor->id,
                    'name' => $minor->name,
                    'email' => $minor->email,
                    'relationship' => $minor->pivot->relationship,
                    'confirmed_guardian' => (bool) $minor->pivot->confirmed_guardian,
                    'confirmed_admin' => (bool) $minor->pivot->confirmed_admin,
                ];
            });

        return Inertia::render('settings/Guardian', [
            'guardians' => $guardians,
            'minors' => $minors,
        ]);
    }

    /**
     * Add a new guardian or child connection.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:guardian,minor'],
            'email' => ['required', 'email', 'exists:users,email'],
            'relationship' => ['required', 'string', 'in:father,mother,uncle,aunt,grand_mother,grand_father,other,child,ward'],
        ]);

        $user = Auth::user();
        $targetUser = User::where('email', $validated['email'])->firstOrFail();

        // Prevent adding self
        if ($targetUser->id === $user->id) {
            return back()->withErrors(['email' => 'You cannot add yourself as a guardian or child.']);
        }

        if ($validated['type'] === 'guardian') {
            // Adding a guardian (current user is the minor)
            // Check if relationship already exists
            if ($user->guardians()->where('guardian_id', $targetUser->id)->exists()) {
                return back()->withErrors(['email' => 'This guardian is already connected.']);
            }

            $user->guardians()->attach($targetUser->id, [
                'relationship' => $validated['relationship'],
                'confirmed_guardian' => false,
                'confirmed_admin' => false,
            ]);
        } else {
            // Adding a minor (current user is the guardian)
            // Check if relationship already exists
            if ($user->minors()->where('minor_id', $targetUser->id)->exists()) {
                return back()->withErrors(['email' => 'This child is already connected.']);
            }

            $user->minors()->attach($targetUser->id, [
                'relationship' => $validated['relationship'],
                'confirmed_guardian' => false,
                'confirmed_admin' => false,
            ]);
        }

        return back()->with('success', 'Connection request sent successfully.');
    }

    /**
     * Remove a guardian or child connection.
     */
    public function destroy(Request $request, User $user)
    {
        $currentUser = Auth::user();
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:guardian,minor'],
        ]);

        if ($validated['type'] === 'guardian') {
            // Remove guardian connection (current user is the minor)
            $currentUser->guardians()->detach($user->id);
        } else {
            // Remove minor connection (current user is the guardian)
            $currentUser->minors()->detach($user->id);
        }

        return back()->with('success', 'Connection removed successfully.');
    }
}
