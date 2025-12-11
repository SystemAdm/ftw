<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [UsersController::class, 'loginForm'])->name('login');

    // Social: OAuth
    Route::get('auth/{provider}', [SocialiteController::class, 'redirectTo'])->name('social');
    Route::get('auth/{provider}/callback', [SocialiteController::class, 'handleCallback'])->name('social.callback');

});

Route::middleware('auth')->group(function () {
    Route::post('logout', [UsersController::class, 'logout'])->name('logout');
    Route::get('dashboard', [UsersController::class, 'dashboard'])->name('dashboard');
});
