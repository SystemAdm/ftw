<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if ($user && !empty($user->google_id)) {
            return redirect()->route('profile.edit')->with('error', __('Your account is managed by Google. Password settings are unavailable.'));
        }

        return Inertia::render('settings/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user && !empty($user->google_id)) {
            return redirect()->route('profile.edit')->with('error', __('Your account is managed by Google. You cannot set a password.'));
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
