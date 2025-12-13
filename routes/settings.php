<?php

use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    // Settings: Profile page
    Route::get('settings/profile', [ProfileController::class, 'show'])
        ->name('settings.profile');

    // Update Profile basics (birthdate, postal code)
    Route::patch('settings/profile', [ProfileController::class, 'updateProfile'])
        ->name('settings.profile.update');

    // Update Appearance (theme)
    Route::patch('settings/appearance', [ProfileController::class, 'updateAppearance'])
        ->name('settings.appearance.update');

    // Update Password
    Route::patch('settings/password', [ProfileController::class, 'updatePassword'])
        ->name('settings.password.update');

    // Update Avatar
    Route::post('settings/avatar', [ProfileController::class, 'updateAvatar'])
        ->name('settings.avatar.update');
});
