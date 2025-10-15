<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [RegisteredUserController::class, 'create'])
        ->name('register');

    // Registration identify step (API JSON) - support both GET and POST to avoid method issues
    Route::match(['GET','POST'], 'register/identify', [RegisteredUserController::class, 'identify'])
        ->name('register.identify');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->name('register.store');

    /*Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');*/

    // Multi-step login endpoints
    Route::get('login/identify', function () {
        // Show step 1 page; reuse the existing login page view
        return Inertia\Inertia::render('auth/LoginSteps', [
            'step' => 1,
            'canResetPassword' => \Illuminate\Support\Facades\Route::has('password.request'),
        ]);
    })->name('login.identify');
    Route::post('login/identify', [\App\Http\Controllers\Auth\MultiStepLoginController::class, 'identify'])->name('login.identify.post');
    Route::post('login/select', [\App\Http\Controllers\Auth\MultiStepLoginController::class, 'select'])->name('login.select');
    Route::post('login/confirm', [\App\Http\Controllers\Auth\MultiStepLoginController::class, 'confirm'])->name('login.confirm');
    Route::post('login/create', [\App\Http\Controllers\Auth\MultiStepLoginController::class, 'createUser'])->name('login.create');

    // Socialite Google
    Route::get('auth/google', [\App\Http\Controllers\Auth\MultiStepLoginController::class, 'google'])->name('login.google');
    Route::get('auth/google/callback', [\App\Http\Controllers\Auth\MultiStepLoginController::class, 'googleCallback'])->name('login.google.callback');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.confirm.store');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    Route::post('identify', [AuthenticatedSessionController::class, 'identify']);
});
