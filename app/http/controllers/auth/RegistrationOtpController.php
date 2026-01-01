<?php

namespace App\http\controllers\auth;

use App\http\controllers\Controller;
use App\notifications\auth\VerifyEmailWithPin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class RegistrationOtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string',
        ]);

        $pin = (string) rand(100000, 999999);
        session(['registration_otp' => $pin]);
        session(['registration_email' => $request->email]);

        // Send PIN to email
        Notification::route('mail', $request->email)->notify(new VerifyEmailWithPin($pin));

        // In a real app, we might send SMS if phone is provided
        // if ($request->filled('phone')) { ... }

        return response()->json(['message' => 'OTP sent successfully.']);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|string|size:6',
        ]);

        if (session('registration_otp') !== $request->pin) {
            throw ValidationException::withMessages([
                'pin' => [__('pages.auth.login.messages.pin_invalid')],
            ]);
        }

        session(['registration_otp_verified' => true]);

        return response()->json(['message' => 'OTP verified successfully.']);
    }
}
