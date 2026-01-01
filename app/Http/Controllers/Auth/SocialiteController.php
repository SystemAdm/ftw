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
    public function redirectTo($provider): RedirectResponse
    {
        // Use stateless to avoid SameSite/state cookie issues that can appear as CORS problems
        // Also force the callback URL to our named route to avoid any misconfigured env redirect URIs
        switch ($provider) {
            case 'battlenet':

                return Socialite::driver('battlenet')->stateless()->redirect();

            case 'discord':

                return Socialite::driver('discord')->stateless()->redirect();

            case 'facebook':

                return Socialite::driver('facebook')->stateless()->redirect();

            case 'github':

                return Socialite::driver('github')->stateless()->redirect();

            case 'google':
                return Socialite::driver('google')->stateless()
                    ->redirect();

            case 'steam':

                return Socialite::driver('steam')->stateless()->redirect();

            case 'twitch':

                return Socialite::driver('twitch')->stateless()->redirect();

            default:
                return redirect()->route('login');
        }
    }

    public function handleCallback($provider): RedirectResponse
    {
        if ($provider == 'battlenet') {
        } elseif ($provider == 'discord') {
            try {
                $discordUser = Socialite::driver('discord')->stateless()->user();
            } catch (\Throwable $e) {
                return redirect()->route('login')->withErrors([
                    'email' => __('pages.auth.login.messages.social_error', ['provider' => 'Discord']),
                ]);
            }

            $email = $discordUser->getEmail();

            if (! $email) {
                return redirect()->route('login')->withErrors([
                    'email' => __('pages.auth.login.messages.social_no_email', ['provider' => 'Discord']),
                ]);
            }

            $user = User::where('discord_id', $discordUser->getId())->first();

            if (! $user) {
                $user = User::where('email', $discordUser->getEmail())->first();
                if (! $user) {
                    return redirect()->route('login')->withErrors([
                        'email' => __('pages.auth.login.messages.social_error', ['provider' => 'Discord']),
                    ]);
                }
                $user->forceFill(['discord_id' => $discordUser->getId()])->save();
            }

            $user->forceFill(['avatar' => $discordUser->getAvatar(), 'name' => $discordUser->getName()])->save();
        } elseif ($provider == 'facebook') {
        } elseif ($provider == 'github') {
        } elseif ($provider == 'google') {
            try {
                $googleUser = Socialite::driver('google')->stateless()->user();
            } catch (\Throwable $e) {
                return redirect()->route('login')->withErrors([
                    'email' => __('pages.auth.login.messages.social_error', ['provider' => 'Google']),
                ]);
            }
            $email = $googleUser->getEmail();

            if (! $email) {
                return redirect()->route('login')->withErrors([
                    'email' => __('pages.auth.login.messages.social_no_email', ['provider' => 'Google']),
                ]);
            }

            /** @var Authenticatable|User|null $user */
            $user = User::where('google_id', $googleUser->getId())->first();

            if (! $user) {
                $user = User::where('email', $googleUser->getEmail())->first();
                if (! $user) {
                    return redirect()->route('login')->withErrors([
                        'email' => __('pages.auth.login.messages.social_error', ['provider' => 'Google']),
                    ]);
                }
                $user->forceFill(['google_id' => $googleUser->getId()])->save();
            }

            // Link google_id if not already set
            $user->forceFill([
                'avatar' => (string) ($googleUser->avatar ?? ''),
            ])->save();
        } elseif ($provider == 'steam') {
        } elseif ($provider == 'twitch') {
        } else {
            return redirect()->route('login');
        }

        if ($user && $user->isBanned()) {
            $message = trans('pages.auth.login.messages.banned', [
                'reason' => $user->ban_reason ?: 'No reason provided',
            ]);

            if ($user->banned_to) {
                $message .= ' '.trans('pages.auth.login.messages.banned_until', [
                    'until' => $user->banned_to->diffForHumans(),
                ]);
            }

            return redirect('/')->with('ban_message', $message);
        }

        Auth::login($user, remember: true);

        request()->session()->regenerate();

        return to_route('dashboard');
    }
}
