<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateAppearanceRequest;
use App\Http\Requests\Settings\UpdateAvatarRequest;
use App\Http\Requests\Settings\UpdatePasswordRequest;
use App\Http\Requests\Settings\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user()->loadMissing('postalCode');
        $subscription = $user->subscription('default');

        return Inertia::render('settings/profile', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'birthday' => optional($user->birthday)?->toDateString(),
                'postal_code' => $user->postal_code,
                'postal' => $user->postalCode ? [
                    'postal_code' => $user->postalCode->postal_code,
                    'city' => $user->postalCode->city,
                    'country' => $user->postalCode->country,
                ] : null,
                'appearance' => $user->appearance ?? 'system',
            ],
            'subscription' => [
                'active' => $user->subscribed('default'),
                'ends_at' => $subscription?->ends_at?->toIso8601String(),
                'on_grace_period' => $subscription?->onGracePeriod() ?? false,
                'next_billing_date' => $user->getSubscriptionNextBillingDate(),
                'time_left' => $user->getSubscriptionTimeLeft(),
            ],
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validated();

        $user->birthday = $data['birthday'];
        $user->postal_code = $data['postal_code'];
        $user->save();

        return back()->with('success', 'Profile updated');
    }

    public function updateAppearance(UpdateAppearanceRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->appearance = $request->validated('appearance');
        $user->save();

        return back()->with('success', 'Appearance updated');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->password = Hash::make($request->validated('password'));
        $user->save();

        Auth::logoutOtherDevices($request->validated('password'));

        return back()->with('success', 'Password updated');
    }

    public function updateAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            if (! empty($user->avatar)) {
                // Best-effort delete of old avatar if stored locally
                if (str_starts_with((string) $user->avatar, 'storage/')) {
                    $relative = str_replace('storage/', '', (string) $user->avatar);
                    Storage::disk('public')->delete($relative);
                }
            }

            $path = $request->file('avatar')->store('avatars', ['disk' => 'public']);
            $user->avatar = Storage::url($path);
            $user->save();
        }

        return back()->with('success', 'Avatar updated');
    }
}
