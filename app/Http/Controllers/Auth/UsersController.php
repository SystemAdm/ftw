<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\OpeningHours;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UsersController extends Controller
{
    public function loginForm(): Response
    {
        return Inertia::render('auth/LoginForm');
    }

    public function dashboard(Request $request, OpeningHours $openingHours): Response
    {
        // Build a 7-day window starting at "today + (7 * week)" where week is an offset from query.
        $week = (int) $request->query('week', 0);
        if ($week < 0) {
            $week = 0;
        }

        $days = $openingHours->getForWeek($week);

        $encryptedId = \Illuminate\Support\Facades\Crypt::encryptString((string) $request->user()->id);
        $qrCode = QrCode::size(200)->generate($encryptedId)->toHtml();

        return Inertia::render('Dashboard', [
            'days' => $days,
            'week' => $week,
            'qr_code' => $qrCode,
            'qr_code_value' => app()->isLocal() ? $encryptedId : null,
        ]);
    }

    public function welcome(): Response
    {
        return Inertia::render('Welcome');
    }

    public function confirmPasswordForm(): Response
    {
        return Inertia::render('auth/ConfirmPassword');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}
