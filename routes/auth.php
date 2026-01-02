<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegistrationOtpController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\UserLookupController;
use App\Http\Controllers\Auth\UsersController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('guest')->group(function () {
    Route::get('login', [UsersController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('users/lookup', UserLookupController::class)->name('auth.users.lookup');

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::post('register/otp/send', [RegistrationOtpController::class, 'send'])->name('register.otp.send');
    Route::post('register/otp/verify', [RegistrationOtpController::class, 'verify'])->name('register.otp.verify');

    Route::get('forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.store');

    // Social: OAuth
    Route::get('auth/{provider}', [SocialiteController::class, 'redirectTo'])->name('social');
    Route::get('auth/{provider}/callback', [SocialiteController::class, 'handleCallback'])->name('social.callback');

    // Specific named routes for Google for testing and backward compatibility
    Route::get('auth/google', [SocialiteController::class, 'redirectTo'])->name('social.google');
    Route::get('auth/google/callback', [SocialiteController::class, 'handleCallback'])->name('social.google.callback');
});

Route::resource('locations', LocationController::class)->only(['index', 'show']);
Route::resource('teams', TeamController::class)->only(['index', 'show']);

Route::middleware('auth')->group(function () {
    Route::post('logout', [UsersController::class, 'logout'])->name('logout');
    Route::get('dashboard', [UsersController::class, 'dashboard'])->name('dashboard');

    Route::get('notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationsController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/read-all', [NotificationsController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('notifications/{id}', [NotificationsController::class, 'destroy'])->name('notifications.destroy');

    Route::get('email/verify', function (Request $request) {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(route('dashboard', absolute: false))
            : Inertia::render('auth/VerifyEmail');
    })->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verify-pin', [EmailVerificationController::class, 'verify'])->name('verification.verify-pin');

    Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [UsersController::class, 'confirmPasswordForm'])->name('password.confirm');
    Route::post('confirm-password', [AuthenticatedSessionController::class, 'confirmPassword'])->name('password.confirm.store');
});
