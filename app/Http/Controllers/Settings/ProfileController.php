<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateAppearanceRequest;
use App\Http\Requests\Settings\UpdateAvatarRequest;
use App\Http\Requests\Settings\UpdateHeaderImageRequest;
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
        $user = $request->user()->loadMissing(['postalCode', 'guardians', 'minors']);
        $subscription = $user->subscription('default');

        return Inertia::render('settings/Profile', [
            'user' => [
                'name' => $user->name,
                'given_name' => $user->given_name,
                'middle_name' => $user->middle_name,
                'family_name' => $user->family_name,
                'name_public' => $user->name_public,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'header_image' => $user->header_image,
                'birthday' => optional($user->birthday)?->toDateString(),
                'birthday_visibility' => $user->birthday_visibility,
                'postal_code' => $user->postal_code,
                'postal_code_visibility' => $user->postal_code_visibility,
                'about' => $user->about,
                'postal' => $user->postalCode ? [
                    'postal_code' => $user->postalCode->postal_code,
                    'city' => $user->postalCode->city,
                    'country' => $user->postalCode->country,
                ] : null,
                'appearance' => $user->appearance ?? 'dark',
                'phone_public' => $user->phone_public,
                'email_public' => $user->email_public,
            ],
            'guardians' => $user->guardians->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'email' => $g->email,
                'relationship' => $g->pivot->relationship,
                'verified_user_at' => $g->pivot->verified_user_at,
                'verified_guardian_at' => $g->pivot->verified_guardian_at,
                'verified_at' => $g->pivot->verified_at,
            ]),
            'minors' => $user->minors->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'email' => $m->email,
                'relationship' => $m->pivot->relationship,
                'verified_user_at' => $m->pivot->verified_user_at,
                'verified_guardian_at' => $m->pivot->verified_guardian_at,
                'verified_at' => $m->pivot->verified_at,
            ]),
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

        $user->given_name = $data['given_name'];
        $user->middle_name = $data['middle_name'] ?? null;
        $user->family_name = $data['family_name'];
        $user->name = trim($user->given_name.' '.($user->middle_name ? $user->middle_name.' ' : '').$user->family_name);
        $user->birthday = $data['birthday'];
        $user->birthday_visibility = $data['birthday_visibility'];
        $user->postal_code = $data['postal_code'];
        $user->postal_code_visibility = $data['postal_code_visibility'];
        $user->phone_public = $request->boolean('phone_public');
        $user->email_public = $request->boolean('email_public');
        $user->name_public = $request->boolean('name_public');
        $user->about = $data['about'] ?? null;
        $user->save();

        return back()->with('success', __('pages.settings.profile.messages.updated'));
    }

    public function updateAppearance(UpdateAppearanceRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->appearance = $request->validated('appearance');
        $user->save();

        return back()->with('success', __('pages.settings.profile.messages.appearance_updated'));
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->password = Hash::make($request->validated('password'));
        $user->save();

        Auth::logoutOtherDevices($request->validated('password'));

        return back()->with('success', __('pages.settings.profile.messages.password_updated'));
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

        return back()->with('success', __('pages.settings.profile.messages.avatar_updated'));
    }

    public function updateHeaderImage(UpdateHeaderImageRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->hasFile('header_image')) {
            if (! empty($user->header_image)) {
                if (str_starts_with((string) $user->header_image, 'storage/')) {
                    $relative = str_replace('storage/', '', (string) $user->header_image);
                    Storage::disk('public')->delete($relative);
                }
            }

            $path = $request->file('header_image')->store('header_images', ['disk' => 'public']);
            $user->header_image = Storage::url($path);
            $user->save();
        }

        return back()->with('success', __('pages.settings.profile.messages.header_image_updated'));
    }
}
