<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        // Use stateless to avoid SameSite/state cookie issues that can appear as CORS problems
        // Also force the callback URL to our named route to avoid any misconfigured env redirect URIs
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to authenticate with Google.',
            ]);
        }
        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->withErrors([
                'email' => 'Your Google account does not have an email address.',
            ]);
        }

        /** @var Authenticatable|User|null $user */
        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();
            if (! $user) {
                dd('no user');
            }
            $user->forceFill(['google_id' => $googleUser->getId(), 'avatar' => (string) ($googleUser->avatar ?? '')])->save();
        }

        // Link google_id if not already set
        $user->forceFill([
            'avatar' => (string) ($googleUser->avatar ?? ''),
        ])->save();

        Auth::login($user, remember: true);

        request()->session()->regenerate();

        return to_route('dashboard');
    }
}
