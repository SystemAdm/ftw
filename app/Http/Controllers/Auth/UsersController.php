<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function loginForm(): Response
    {
        return Inertia::render('auth/LoginForm');
    }

    public function dashboard(): Response
    {
        // list weekdays 0 - 7

        return Inertia::render('Dashboard');
    }

    public function welcome(): Response
    {
        return Inertia::render('Welcome');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
