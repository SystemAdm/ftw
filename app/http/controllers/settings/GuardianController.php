<?php

namespace App\http\controllers\settings;

use App\http\controllers\Controller;
use App\models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function addGuardian(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'relationship' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $guardian = User::where('email', $request->email)->firstOrFail();

        if ($user->id === $guardian->id) {
            return back()->withErrors(['email' => __('You cannot be your own guardian.')]);
        }

        if ($user->guardians()->where('guardian_id', $guardian->id)->exists()) {
            return back()->withErrors(['email' => __('This user is already your guardian.')]);
        }

        $user->guardians()->attach($guardian->id, [
            'relationship' => $request->relationship,
            'verified_user_at' => now(),
        ]);

        $guardian->notify(new \App\Notifications\Auth\GuardianRelationConfirmation($user));

        return back()->with('success', __('Guardian added successfully. Please wait for their verification.'));
    }

    public function removeGuardian(Request $request, User $guardian): RedirectResponse
    {
        $user = $request->user();
        $user->guardians()->detach($guardian->id);

        return back()->with('success', __('Guardian removed.'));
    }

    public function verifyMinor(Request $request, User $minor): RedirectResponse
    {
        $user = $request->user();
        $user->minors()->updateExistingPivot($minor->id, [
            'verified_guardian_at' => now(),
            'confirmed_guardian' => true,
        ]);

        return back()->with('success', __('Relationship verified.'));
    }

    public function removeMinor(Request $request, User $minor): RedirectResponse
    {
        $user = $request->user();
        $user->minors()->detach($minor->id);

        return back()->with('success', __('Minor removed.'));
    }
}
