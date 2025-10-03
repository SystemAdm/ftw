<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PhoneNumber;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Propaganistas\LaravelPhone\Rules\Phone as PhoneRule;

class MultiStepLoginController extends Controller
{
    /**
     * Normalize an email for deduplication.
     * Lowercase, and for gmail.com/googlemail.com use substring after '+' in local part.
     */
    private function normalizeEmail(?string $email): ?string
    {
        if (!is_string($email)) return null;
        $email = trim(mb_strtolower($email));
        if ($email === '' || !str_contains($email, '@')) return $email ?: null;
        [$local, $domain] = explode('@', $email, 2);
        if (in_array($domain, ['gmail.com','googlemail.com'], true)) {
            $pos = mb_strpos($local, '+');
            if ($pos !== false) {
                $local = mb_substr($local, $pos + 1) ?: '';
            }
        }
        return ($local !== '' ? $local : '') . '@' . $domain;
    }
    /**
     * Handle Step 1 identifier submission: phone/email/username
     */
    public function identify(Request $request): RedirectResponse|Response
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'prefer_new' => ['sometimes', 'boolean'],
        ]);

        $identifier = trim($validated['identifier']);
        $preferNew = (bool) $request->boolean('prefer_new');

        // Detect phone identifier first, and handle via PhoneNumber relations
        $e164 = null;
        try {
            // Try formatting as NO by default; adjust country as needed
            $e164 = phone($identifier, 'NO')->formatE164();
        } catch (\Throwable $e) {
            $e164 = null;
        }

        if ($e164) {
            $phone = PhoneNumber::where('e164', $e164)->first();
            $usersByPhone = $phone ? $phone->users()->get(['users.id', 'users.name', 'users.email', 'users.username', 'users.avatar', 'users.password', 'users.google_id']) : collect();

            // If user prefers to create and it's allowed with multiple users per phone, go to create mode
            if ($preferNew && (bool) config('custom.multiple_users_per_phone', false) && (bool) config('custom.allow_new_users', false)) {
                return Inertia::render('auth/LoginSteps', [
                    'step' => 2,
                    'mode' => 'create',
                    'identifier' => $identifier,
                    'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
                ]);
            }

            if ($usersByPhone->isNotEmpty()) {
                // Always present selection when logging in by phone (even if only one)
                $candidates = $usersByPhone->map(function ($u) {
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'username' => $u->username,
                        'avatar' => $u->avatar,
                        'has_password' => ! empty($u->password),
                        'google_id' => $u->google_id,
                    ];
                })->values();

                return Inertia::render('auth/LoginSteps', [
                    'step' => 2,
                    'mode' => 'select',
                    'identifier' => $identifier,
                    'candidates' => $candidates,
                    'canCreate' => (bool) config('custom.multiple_users_per_phone', false) && (bool) config('custom.allow_new_users', false),
                    'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
                ]);
            }

            // No users linked to this phone
            if (! (bool) config('custom.allow_new_users', false)) {
                return Inertia::render('auth/LoginSteps', [
                    'step' => 1,
                    'errorsBag' => ['identifier' => __('No account found for the given phone number.')],
                    'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
                ]);
            }

            // Allowed to create a new account
            return Inertia::render('auth/LoginSteps', [
                'step' => 2,
                'mode' => 'create',
                'identifier' => $identifier,
                'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
            ]);
        }

        // Not a phone: Build query for possible matches (email/username/optional users.phone column)
        $query = User::query();
        $query->where(function ($q) use ($identifier) {
            // email
            $q->orWhere('email', $identifier)
              ->orWhere('email_normalized', mb_strtolower($identifier));
            // username
            $q->orWhere('username', $identifier);

            // phone (if the column exists)
            if (Schema::hasColumn('users', 'phone')) {
                $q->orWhere('phone', $identifier);
            }
        });

        $users = $query->get(['id', 'name', 'email', 'username', 'avatar', 'password', 'google_id']);

        if ($users->isEmpty()) {
            $allowNew = (bool) config('custom.allow_new_users', false);
            if (! $allowNew) {
                // Re-render step 1 with error
                return Inertia::render('auth/LoginSteps', [
                    'step' => 1,
                    'errorsBag' => ['identifier' => __('No account found for the given identifier.')],
                    'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
                ]);
            }

            // If allowed, go to create user step (step 2 create)
            return Inertia::render('auth/LoginSteps', [
                'step' => 2,
                'mode' => 'create',
                'identifier' => $identifier,
                'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
            ]);
        }

        // If one match, progress to step 3 (confirm password if needed)
        if ($users->count() === 1) {
            $user = $users->first();
            $request->session()->put('login.user_id', $user->id);
            return Inertia::render('auth/LoginSteps', [
                'step' => 3,
                'selectedUser' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'avatar' => $user->avatar,
                ],
                'needsPassword' => ! empty($user->password),
                'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
            ]);
        }

        // Multiple matches: step 2 select
        $candidates = $users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'username' => $u->username,
                'avatar' => $u->avatar,
                'has_password' => ! empty($u->password),
                'google_id' => $u->google_id,
            ];
        })->values();

        return Inertia::render('auth/LoginSteps', [
            'step' => 2,
            'mode' => 'select',
            'identifier' => $identifier,
            'candidates' => $candidates,
            'canCreate' => (bool) config('custom.allow_new_users', false),
            'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
        ]);
    }

    /**
     * Step 2: select a specific user from candidates
     */
    public function select(Request $request): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::select(['id', 'name', 'email', 'username', 'avatar', 'password', 'google_id'])->find($data['user_id']);

        // If user has neither a password nor a Google ID, log them in immediately
        if (empty($user->password) && empty($user->google_id)) {
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->forget('login.user_id');

            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Otherwise, proceed to step 3 (password confirmation if needed)
        $request->session()->put('login.user_id', $user->id);

        return Inertia::render('auth/LoginSteps', [
            'step' => 3,
            'selectedUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'avatar' => $user->avatar,
            ],
            'needsPassword' => ! empty($user->password),
            'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
        ]);
    }

    /**
     * Step 3: confirm password if needed and log in
     */
    public function confirm(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $userId = $request->session()->get('login.user_id') ?: $request->input('user_id');
        if (! $userId) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Login session not initialized.',
                ], 400);
            }
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'User not found.',
                ], 400);
            }
            return redirect()->route('login');
        }

        if (! empty($user->password)) {
            $validated = $request->validate([
                'password' => ['required', 'string'],
                'remember' => ['sometimes', 'boolean'],
            ]);

            if (! Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => __('auth.password'),
                ]);
            }
        }

        Auth::login($user, (bool) $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->forget('login.user_id');

        $redirect = route('dashboard', absolute: false);
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'redirect' => $redirect,
            ]);
        }

        return redirect()->intended($redirect);
    }

    /**
     * Create a new user if allowed by config and then continue to login
     */
    public function createUser(Request $request): Response|RedirectResponse
    {
        abort_unless((bool) config('custom.allow_new_users', false), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            // Accept either a single phone string or an array of phones; both optional
            'phone' => ['nullable', 'string', new PhoneRule(['NO'])],
            'phones' => ['nullable', 'array'],
            'phones.*' => ['nullable', 'string', new PhoneRule(['NO'])],
            'primary_phone' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'username' => $data['username'] ?? null,
            'password' => $data['password'] ?? null,
        ]);

        // Optional: attach phone numbers if provided (validation with Laravel-Phone handled below)
        try {
            $this->attachPhonesToUser($request, $user);
        } catch (\Throwable $e) {
            // Fail silently to not block signup; alternatively, you may choose to report()
            // report($e);
        }

        // If no password was provided, log in immediately and redirect
        if (empty($user->password)) {
            Auth::login($user, true);
            $request->session()->regenerate();
            $request->session()->forget('login.user_id');
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Otherwise proceed to step 3 (confirm password)
        $request->session()->put('login.user_id', $user->id);

        return Inertia::render('auth/LoginSteps', [
            'step' => 3,
            'selectedUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'avatar' => $user->avatar,
            ],
            'needsPassword' => ! empty($user->password),
            'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
        ]);
    }

    /**
     * Socialite Google redirect (Step 1 option)
     */
    public function google(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->stateless()
            ->redirect();
    }

    /**
     * Socialite Google callback
     */
    public function googleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('status', __('Unable to authenticate with Google.'));
        }

        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?: ($googleUser->user['given_name'] ?? '');
        $avatar = $googleUser->getAvatar();
        $nickname = $googleUser->getNickname();
        $verified = (bool) ($googleUser->user['email_verified'] ?? false);
        $googleId = $googleUser->getId();

        $user = null;
        if ($email) {
            $user = User::where(function($q) use ($email) {
                $q->where('email', $email)->orWhere('email_normalized', $this->normalizeEmail($email));
            })->first();
        }

        // If we already have a user with this email AND google_id matches → log in
        if ($user && $user->google_id && (string) $user->google_id === (string) $googleId) {
            // Update verification/normalization if needed
            $changed = false;
            if ($verified && $user->email && empty($user->email_verified_at)) {
                $user->email_verified_at = now();
                $changed = true;
            }
            if ($user->email) {
                $norm = $this->normalizeEmail($user->email);
                if ($user->email_normalized !== $norm) {
                    $user->email_normalized = $norm;
                    $changed = true;
                }
            }
            if ($changed) {
                try { $user->save(); } catch (\Throwable $e) { /* ignore */ }
            }

            Auth::login($user, true);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Otherwise, start/continue the registration flow at Step 4 with Google prefill
        if (! (bool) config('custom.allow_new_users', false) && ! $user) {
            // If signups are closed and no user exists, bail out to login
            return redirect()->route('login')->with('status', __('No account associated with this Google account.'));
        }

        $request->session()->put('google_signup', [
            'email' => $email,
            'name' => $name,
            'avatar' => $avatar,
            'nickname' => $nickname,
            'verified' => $verified,
            'google_id' => $googleId,
        ]);

        return redirect()->route('register');
    }

    /**
     * Attach provided phone(s) to a user, validate with Laravel-Phone, normalize to E.164,
     * and set pivot attributes (primary if specified or if only one number).
     */
    private function attachPhonesToUser(Request $request, User $user): void
    {
        // Gather raw inputs
        $raws = collect([]);
        $single = trim((string) $request->input('phone', ''));
        if ($single !== '') {
            $raws->push($single);
        }
        $arr = $request->input('phones', []);
        if (is_array($arr)) {
            foreach ($arr as $r) {
                $r = trim((string) $r);
                if ($r !== '') {
                    $raws->push($r);
                }
            }
        }

        if ($raws->isEmpty()) {
            return;
        }

        // Validate each using Propaganistas/Laravel-Phone rules (country hint NO by default)
        foreach ($raws as $raw) {
            validator(['phone' => $raw], ['phone' => ['required', 'string', new PhoneRule(['NO'])]])->validate();
        }

        // Normalize and create/find phone number records
        $pairs = [];
        $primaryInput = trim((string) $request->input('primary_phone', ''));
        $e164Primary = null;
        if ($primaryInput !== '') {
            try {
                $e164Primary = phone($primaryInput, 'NO')->formatE164();
            } catch (\Throwable $e) {
                // ignore bad primary hint
            }
        }

        foreach ($raws->unique() as $raw) {
            try {
                $e164 = phone($raw, 'NO')->formatE164();
            } catch (\Throwable $e) {
                // Skip any that cannot be formatted (shouldn't happen after validation)
                continue;
            }

            $phoneModel = PhoneNumber::firstOrCreate(['e164' => $e164], ['raw' => $raw]);
            $pairs[$phoneModel->id] = [
                'primary' => ($e164Primary && $e164Primary === $e164) ? true : false,
                'verified_at' => null,
                'verified_by' => null,
            ];
        }

        // If no explicit primary and exactly one phone, mark it primary
        if (! $e164Primary && count($pairs) === 1) {
            $onlyId = array_key_first($pairs);
            if ($onlyId !== null) {
                $pairs[$onlyId]['primary'] = true;
            }
        }

        if (empty($pairs)) {
            return;
        }

        // Attach without detaching existing phones
        $user->phoneNumbers()->syncWithoutDetaching($pairs);
    }
}
