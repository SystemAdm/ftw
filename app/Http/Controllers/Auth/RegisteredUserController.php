<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuardianInvitationMail;

class RegisteredUserController extends Controller
{
    /**
     * API: Check existing accounts for the given identifier (email or phone)
     * and return selection/create options as JSON (no navigation).
     */
    public function identify(Request $request): \Illuminate\Http\JsonResponse
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
            $usersByPhone = $phone ? $phone->users()->get(['users.id', 'users.name', 'users.email', 'users.username', 'users.avatar','users.password','users.google_id']) : collect();

            if ($usersByPhone->isNotEmpty()) {
                $candidates = $usersByPhone->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'username' => $u->username,
                        'avatar' => $u->avatar,
                        'has_password' => (bool) ($u->password ?? false),
                        'google_id' => $u->google_id,
                    ];
                })->values();

                return response()->json([
                    'step' => 2,
                    'mode' => 'select',
                    'identifier' => $identifier,
                    'candidates' => $candidates,
                    'canCreate' => (bool) config('custom.multiple_users_per_phone', false),
                ]);
            }

            // No users by this phone
            return response()->json([
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

        $users = $query->get(['id', 'name', 'email', 'username', 'avatar','password','google_id']);

        if ($users->isEmpty()) {
            return response()->json([
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
                'has_password' => (bool) ($u->password ?? false),
                'google_id' => $u->google_id,
            ];
        })->values();

        return response()->json([
            'step' => 2,
            'mode' => 'select',
            'identifier' => $identifier,
            'candidates' => $candidates,
            // If identifier is an email and accounts exist, do not allow creating a new account
            'canCreate' => (bool) config('custom.allow_new_users', false) && ! (bool) filter_var($identifier, FILTER_VALIDATE_EMAIL),
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
        $validated = $request->validate([
            // Basic identity
            'first_name' => ['required','string','max:255'],
            'last_name' => ['required','string','max:255'],

            // Birthday: must be provided and user must be at least 5 years old
            'birthday' => ['required','date','before_or_equal:'.now()->subYears(5)->toDateString()],

            // Postal code: 4 to 6 digits
            'postal_code' => ['required','regex:/^\\d{4,6}$/'],

            // Contact: at least one of email or phone required
            'email' => ['required_without:phone','nullable','string','lowercase','email','max:255','unique:'.User::class],
            // Laravel-Phone rule, accept any country (auto-detect) and E.164/strict validation
            'phone' => ['required_without:email','nullable','phone'],

            // Password optional (will be generated if omitted)
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],

            // Guardian (either create new by fields or select existing by id)
            'guardian_id' => ['nullable','integer','exists:users,id'],
            'guardian_first_name' => ['nullable','string','max:255'],
            'guardian_last_name' => ['nullable','string','max:255'],
            'guardian_email' => ['nullable','email','max:255'],
            // Relationship label stored on guardian_user pivot
            'guardian_relationship' => ['nullable','string','in:father,mother,uncle,aunt,grand_mother,grand_father,other'],
        ]);

        // If under 18, ensure guardian data is present (either id or fields)
        $birth = Carbon::parse($validated['birthday']);
        $isMinor = $birth && $birth->diffInYears() < 18;
        if ($isMinor) {
            $minorData = $request->validate([
                'guardian_id' => ['nullable','integer','exists:users,id'],
                // Require either selecting existing guardian or providing details for a new one
                'guardian_first_name' => ['required_without:guardian_id','nullable','string','max:255'],
                'guardian_last_name' => ['required_without:guardian_id','nullable','string','max:255'],
                'guardian_email' => ['required_without:guardian_id','nullable','email','max:255'],
                'guardian_relationship' => ['required','string','in:father,mother,uncle,aunt,grand_mother,grand_father,other'],
            ]);
            $validated = array_merge($validated, $minorData);
        }

        // Create the minor user
        $user = User::create([
            'name' => trim(($validated['first_name'] ?? '')." ".($validated['last_name'] ?? '')),
            'email' => $validated['email'] ?? null,
            'password' => (!empty($validated['password']) ? Hash::make($validated['password']) : null),
        ]);
        // Persist additional fields
        $user->birthday = Carbon::parse($validated['birthday'])->toDateString();
        $user->postal_code = isset($validated['postal_code']) ? (int) $validated['postal_code'] : null;
        if ($user->email) {
            $user->email_normalized = mb_strtolower($user->email);
        }
        $user->save();

        // Assign default role to every new user
        try {
            $user->assignRole('guest');
        } catch (\Throwable $e) {
            // ignore if roles not seeded yet
        }

        // Attach phone number if provided
        if (!empty($validated['phone'])) {
            try {
                $e164 = phone($validated['phone'])->formatE164();
                if ($e164) {
                    $phone = PhoneNumber::firstOrCreate(['e164' => $e164], ['raw' => $validated['phone']]);
                    $user->phoneNumbers()->syncWithoutDetaching([$phone->id => ['primary' => true]]);
                }
            } catch (\Throwable $e) {
                // ignore phone normalization errors
            }
        }

        // If minor, handle guardian relation (create or attach)
        if ($isMinor) {
            $guardian = null;
            if (!empty($validated['guardian_id'])) {
                $guardian = User::find((int) $validated['guardian_id']);
            } else {
                // Try to find existing guardian by email (normalized) first
                $gEmail = $validated['guardian_email'] ?? null;
                if ($gEmail) {
                    $guardian = User::where('email_normalized', mb_strtolower($gEmail))
                        ->orWhere('email', $gEmail)
                        ->first();
                }
                if (!$guardian) {
                    // Create new guardian user
                    $guardian = new User();
                    $guardian->name = trim(($validated['guardian_first_name'] ?? '')." ".($validated['guardian_last_name'] ?? ''));
                    if ($gEmail) {
                        $guardian->email = $gEmail;
                        $guardian->email_normalized = mb_strtolower($gEmail);
                    }
                    $guardian->password = Hash::make(Str::random(16));
                    $guardian->save();
                    if (!empty($guardian->email)) {
                        // Send a more informative invitation email including password setup link
                        try {
                            $token = Password::broker()->createToken($guardian);
                            $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $guardian->email], false));
                            $relationship = $validated['guardian_relationship'] ?? 'other';
                            Mail::to($guardian->email)->send(new GuardianInvitationMail($guardian, $user, $relationship, $resetUrl));
                        } catch (\Throwable $e) {
                            // ignore mail transport issues
                        }
                    }
                }

            }

            if ($guardian) {
                // Ensure guardian has proper roles: guest + guardian
                try {
                    $guardian->assignRole('guest');
                    $guardian->assignRole('guardian');
                } catch (\Throwable $e) {
                    // ignore if roles not seeded yet
                }

                // Set up relation between guardian and minor with relationship label
                $relationship = $validated['guardian_relationship'] ?? 'other';
                $user->guardians()->syncWithoutDetaching([
                    $guardian->id => ['relationship' => $relationship],
                ]);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
