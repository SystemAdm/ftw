<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [UsersController::class, 'loginForm'])->name('login');

    // Social: Google OAuth
    Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('social.google');
    Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('social.google.callback');

});

Route::middleware('auth')->group(function () {
    Route::post('logout', [UsersController::class, 'logout'])->name('logout');
    Route::get('dashboard', [UsersController::class, 'dashboard'])->name('dashboard');
});
