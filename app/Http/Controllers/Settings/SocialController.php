<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    /**
     * Redirect to Discord OAuth page
     */
    public function discordRedirect(): RedirectResponse
    {
        return Socialite::driver('discord')
            ->withConsent()
            ->scopes(['identify'])
            ->redirect();
    }

    /**
     * Handle Discord OAuth callback
     */
    public function discordCallback(Request $request): RedirectResponse
    {
        try {
            $discordUser = Socialite::driver('discord')->user();

            $user = $request->user();

            // Check if Discord account is already connected to another user
            $existingUser = User::where('discord_id', $discordUser->id)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                return redirect()->route('profile.edit')
                    ->with('error', 'This Discord account is already connected to another user.');
            }

            // Update user's Discord information
            $user->discord_id = $discordUser->id;
            $user->save();

            return redirect()->route('profile.edit')
                ->with('success', 'Discord account connected successfully!');

        } catch (\Exception $e) {
            return redirect()->route('profile.edit')
                ->with('error', 'Failed to connect Discord account. Please try again.');
        }
    }

    /**
     * Disconnect Discord account
     */
    public function discordDisconnect(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->discord_id = null;
        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Discord account disconnected successfully!');
    }
}
