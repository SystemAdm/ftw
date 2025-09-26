<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\PhoneNumber;
use Illuminate\Support\Facades\Schema;

class RegisteredUserController extends Controller
{
    /**
     * Step 2 of registration: check existing accounts for the given identifier (email or phone)
     * and return selection/create options.
     */
    public function identify(Request $request): Response
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
        ]);

        $identifier = trim($validated['identifier']);

        // Try phone first (normalize to E.164 like login flow)
        $e164 = null;
        try {
            $e164 = phone($identifier, 'NO')->formatE164();
        } catch (\Throwable $e) {
            $e164 = null;
        }

        if ($e164) {
            $phone = PhoneNumber::where('e164', $e164)->first();
            $usersByPhone = $phone ? $phone->users()->get(['users.id', 'users.name', 'users.email', 'users.username', 'users.avatar']) : collect();

            if ($usersByPhone->isNotEmpty()) {
                $candidates = $usersByPhone->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'username' => $u->username,
                        'avatar' => $u->avatar,
                    ];
                })->values();

                return Inertia::render('auth/RegisterSteps', [
                    'step' => 2,
                    'mode' => 'select',
                    'identifier' => $identifier,
                    'candidates' => $candidates,
                    // Map requested config keys
                    'canCreate' => (bool) config('custom.multiple_users_per_phone', false),
                ]);
            }

            // No users by this phone
            return Inertia::render('auth/RegisterSteps', [
                'step' => 2,
                'mode' => 'create',
                'identifier' => $identifier,
                'canCreate' => (bool) config('custom.allow_new_users', false),
            ]);
        }

        // Not a phone: check email (and optionally username/phone column like login)
        $query = User::query();
        $query->where(function ($q) use ($identifier) {
            $q->orWhere('email', $identifier)
              ->orWhere('email_normalized', mb_strtolower($identifier));
            if (Schema::hasColumn('users', 'username')) {
                $q->orWhere('username', $identifier);
            }
            if (Schema::hasColumn('users', 'phone')) {
                $q->orWhere('phone', $identifier);
            }
        });

        $users = $query->get(['id', 'name', 'email', 'username', 'avatar']);

        if ($users->isEmpty()) {
            return Inertia::render('auth/RegisterSteps', [
                'step' => 2,
                'mode' => 'create',
                'identifier' => $identifier,
                'canCreate' => (bool) config('custom.allow_new_users', false),
            ]);
        }

        $candidates = $users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'username' => $u->username,
                'avatar' => $u->avatar,
            ];
        })->values();

        return Inertia::render('auth/RegisterSteps', [
            'step' => 2,
            'mode' => 'select',
            'identifier' => $identifier,
            'candidates' => $candidates,
            'canCreate' => (bool) config('custom.allow_new_users', false) && (bool) config('custom.multiple_users_per_phone', false),
        ]);
    }
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/RegisterSteps');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            // Basic identity
            'first_name' => ['required','string','max:255'],
            'last_name' => ['required','string','max:255'],

            // Birthday: must be provided and user must be at least 10 years old
            'birthday' => ['required','date','before_or_equal:'.now()->subYears(10)->toDateString()],

            // Postal code: 4 to 6 digits
            'postal_code' => ['nullable','regex:/^\\d{4,6}$/'],

            // Contact: at least one of email or phone required
            'email' => ['required_without:phone','nullable','string','lowercase','email','max:255','unique:'.User::class],
            // Laravel-Phone rule, accept any country (auto-detect) and E.164/strict validation
            'phone' => ['required_without:email','nullable','phone'],

            // Password optional (will be generated if omitted)
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],

            // Guardian (same checks) - only required if under 18, but we allow nullable here and enforce in after validation
            'guardian_first_name' => ['nullable','string','max:255'],
            'guardian_last_name' => ['nullable','string','max:255'],
            'guardian_email' => ['nullable','email','max:255'],
            'guardian_phone' => ['nullable','phone'],
        ]);

        // If under 18, guardian fields must be provided with same email/phone either-or requirement
        $birth = \Carbon\Carbon::parse($request->input('birthday'));
        $isMinor = $birth && $birth->diffInYears() < 18;
        if ($isMinor) {
            $request->validate([
                'guardian_first_name' => ['required','string','max:255'],
                'guardian_last_name' => ['required','string','max:255'],
                'guardian_email' => ['required_without:guardian_phone','nullable','email','max:255'],
                'guardian_phone' => ['required_without:guardian_email','nullable','phone'],
            ]);
        }

        $user = User::create([
            'name' => trim($request->input('first_name')." ".$request->input('last_name')),
            'email' => $request->email ?: null,
            'password' => Hash::make($request->password ?: Str::random(12)),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
