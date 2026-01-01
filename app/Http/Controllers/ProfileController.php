<?php

namespace App\Http\Controllers;

use App\Enums\BirthdayVisibility;
use App\Enums\PostalCodeVisibility;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the specified user's public profile.
     */
    public function show(Request $request, ?User $user = null): Response
    {
        // If no user is specified, show the current user's profile
        if (! $user) {
            $user = $request->user();
        }

        if (! $user) {
            abort(404);
        }

        $isOwnProfile = $request->user() && $request->user()->id === $user->id;

        $birthday = null;
        if ($isOwnProfile || $user->birthday_visibility !== BirthdayVisibility::Off) {
            if ($user->birthday) {
                if ($user->birthday_visibility === BirthdayVisibility::Birthdate || $isOwnProfile) {
                    $birthday = $user->birthday->toDateString();
                } elseif ($user->birthday_visibility === BirthdayVisibility::Birthyear) {
                    $birthday = $user->birthday->format('Y');
                } elseif ($user->birthday_visibility === BirthdayVisibility::Age) {
                    $birthday = $user->birthday->age;
                }
            }
        }

        $displayName = $user->name;
        if (! $isOwnProfile && ! $user->name_public) {
            $parts = explode(' ', $user->name);
            $displayName = $parts[0];
        }

        $postalCode = null;
        $city = null;
        $municipality = null;
        $country = null;

        if ($isOwnProfile || $user->postal_code_visibility !== PostalCodeVisibility::Off) {
            if ($user->postalCode) {
                if ($isOwnProfile || $user->postal_code_visibility === PostalCodeVisibility::PostalCode) {
                    $postalCode = $user->postalCode->postal_code;
                }
                if ($isOwnProfile || in_array($user->postal_code_visibility, [PostalCodeVisibility::PostalCode, PostalCodeVisibility::City])) {
                    $city = $user->postalCode->city;
                }
                if ($isOwnProfile || in_array($user->postal_code_visibility, [PostalCodeVisibility::PostalCode, PostalCodeVisibility::City, PostalCodeVisibility::Municipality])) {
                    $municipality = $user->postalCode->municipality;
                }
                if ($isOwnProfile || in_array($user->postal_code_visibility, [PostalCodeVisibility::PostalCode, PostalCodeVisibility::City, PostalCodeVisibility::Municipality, PostalCodeVisibility::Country])) {
                    $country = $user->postalCode->country;
                }
            }
        }

        return Inertia::render('profile/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $displayName,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'header_image' => $user->header_image,
                'about' => $user->about,
                'email' => ($isOwnProfile || $user->email_public) ? $user->email : null,
                'phone' => ($isOwnProfile || $user->phone_public) ? $user->phoneNumbers()->wherePivot('primary', true)->first()?->e164 : null,
                'birthday' => $birthday,
                'birthday_visibility' => $user->birthday_visibility,
                'postal_code' => $postalCode,
                'city' => $city,
                'municipality' => $municipality,
                'country' => $country,
                'postal_code_visibility' => $user->postal_code_visibility,
                'email_public' => $user->email_public,
                'phone_public' => $user->phone_public,
                'name_public' => $user->name_public,
            ],
            'isOwnProfile' => $isOwnProfile,
        ]);
    }
}
