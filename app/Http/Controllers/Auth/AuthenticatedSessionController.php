<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/LoginSteps', [
            'step' => 1,
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function identify(Request $request): JsonResponse
    {
        $validated = $request->validate(['input' => ['required', 'string']]);
        $input = trim($validated['input']);

        // Try to interpret input as a phone number first (normalize to E.164)
        $candidates = collect();
        $e164 = null;
        try {
            $e164 = phone($input, 'NO')->formatE164();
        } catch (\Throwable $e) {
            $e164 = null;
        }

        if ($e164) {
            $phone = PhoneNumber::where('e164', $e164)->first();
            if ($phone) {
                $candidates = $phone->users()->get(['users.id', 'users.name', 'users.email', 'users.username', 'users.avatar', 'users.password', 'users.google_id']);
            }
        }

        // If no candidates by phone, fall back to email/username (and optional users.phone column)
        if ($candidates->isEmpty()) {
            $query = User::query();
            $query->where(function ($q) use ($input) {
                $q->orWhere('email', $input)
                  ->orWhere('email_normalized', mb_strtolower($input));
                if (Schema::hasColumn('users', 'username')) {
                    $q->orWhere('username', $input);
                }
                if (Schema::hasColumn('users', 'phone')) {
                    $q->orWhere('phone', $input);
                }
            });
            $candidates = $query->get(['id', 'name', 'email', 'username', 'avatar', 'password', 'google_id']);
        }

        $candidates = $candidates->map(function ($u) {
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

        return response()->json(['candidates' => $candidates]);
    }
}
