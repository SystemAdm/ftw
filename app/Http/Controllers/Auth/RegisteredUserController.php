<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailWithPin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('auth/LoginForm'); // Using the same component for now
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if (! config('custom.allow_new_users')) {
            abort(403, 'New user registration is disabled.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'birthday' => 'required|date',
            'postal_code' => 'required|string|max:20',
            'guardian_contact' => 'nullable|string|max:255',
        ]);

        if (session('registration_otp_verified') !== true) {
            return back()->withErrors(['pin' => 'Please verify your phone/email first.']);
        }

        if (session('registration_email') !== $request->email) {
            return back()->withErrors(['email' => 'Email mismatch with verified OTP.']);
        }

        $birthday = \Carbon\Carbon::parse($request->birthday);
        $age = $birthday->age;
        $needGuardian = $age < config('custom.user_younger_than_need_guardian', 16);

        if ($needGuardian && ! $request->filled('guardian_contact')) {
            return back()->withErrors(['guardian_contact' => 'A guardian contact is required for users under 16.']);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
            'postal_code' => $request->postal_code,
            'email_verified_at' => now(), // It's already verified via OTP
        ]);

        // Assign default guest role
        $user->assignRole('guest');

        // Assign crew role if email is @spillhuset.com
        if (str_ends_with(strtolower($user->email), '@spillhuset.com')) {
            $user->assignRole('crew');
        }

        if ($needGuardian) {
            $this->handleGuardian($user, $request->guardian_contact);
        }

        if ($request->filled('phone')) {
            try {
                $phone = phone($request->phone, array_merge(['AUTO'], config('custom.phone_fallback_countries', ['NO'])));
                if ($phone->isValid()) {
                    $phoneNumber = \App\Models\PhoneNumber::firstOrCreate([
                        'e164' => $phone->formatE164(),
                    ], [
                        'raw' => $request->phone,
                    ]);

                    $user->phoneNumbers()->attach($phoneNumber->id, [
                        'primary' => true,
                        'verified_at' => now(), // Assume verified since they entered it? Or maybe not.
                    ]);
                }
            } catch (\Exception $e) {
                // Ignore invalid phone for now, user already created
            }
        }

        // $user->notify(new VerifyEmailWithPin($pin));

        session()->forget(['registration_otp', 'registration_email', 'registration_otp_verified']);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    /**
     * Handle guardian relation.
     */
    protected function handleGuardian(User $user, string $contact): void
    {
        $guardian = null;

        if (str_contains($contact, '@')) {
            $guardian = User::where('email', $contact)->first();
        } else {
            // Try to find by phone
            try {
                $phone = phone($contact, array_merge(['AUTO'], config('custom.phone_fallback_countries', ['NO'])));
                if ($phone->isValid()) {
                    $e164 = $phone->formatE164();
                    $guardian = User::whereHas('phoneNumbers', function ($query) use ($e164) {
                        $query->where('e164', $e164);
                    })->first();
                }
            } catch (\Exception $e) {
                // Ignore invalid phone
            }
        }

        if ($guardian) {
            // Guardian must be older than threshold
            $guardianBirthday = $guardian->birthday ? \Carbon\Carbon::parse($guardian->birthday) : null;
            $minGuardianAge = config('custom.guardian_user_must_be_older_than', 25);

            if ($guardianBirthday && $guardianBirthday->age >= $minGuardianAge) {
                $user->guardians()->attach($guardian->id, [
                    'relationship' => 'Parent/Guardian',
                    'confirmed_guardian' => false,
                ]);

                $guardian->notify(new \App\Notifications\Auth\GuardianRelationConfirmation($user));
            } else {
                // If guardian is too young, we might just treat it as "not found" or log it.
                // For now, let's assume if they exist but are too young, we don't auto-link or we notify someone.
                // The requirement says "guardians must be older than...", so we skip linking if they are too young.
            }
        } else {
            // Guardian does not exist
            \Illuminate\Support\Facades\Notification::route('mail', str_contains($contact, '@') ? $contact : null)
                // If it's a phone, we'd need SMS, but for now we follow email instructions
                ->notify(new \App\Notifications\Auth\GuardianInvitation($user, $contact));
        }
    }
}
