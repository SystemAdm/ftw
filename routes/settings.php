<?php

use App\Http\Controllers\Settings\BillingController;
use App\Http\Controllers\Settings\GuardianController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    // Settings: Profile page
    Route::get('settings/profile', [ProfileController::class, 'show'])
        ->name('settings.profile');

    // Guardians
    Route::post('settings/guardians', [GuardianController::class, 'addGuardian'])->name('settings.guardians.add');
    Route::delete('settings/guardians/{guardian}', [GuardianController::class, 'removeGuardian'])->name('settings.guardians.remove');
    Route::post('settings/minors/{minor}/verify', [GuardianController::class, 'verifyMinor'])->name('settings.minors.verify');
    Route::delete('settings/minors/{minor}', [GuardianController::class, 'removeMinor'])->name('settings.minors.remove');

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

    // Update Header Image
    Route::post('settings/header-image', [ProfileController::class, 'updateHeaderImage'])
        ->name('settings.header-image.update');

    // Settings: Billing
    Route::get('settings/billing', [BillingController::class, 'index'])->name('settings.billing');
    Route::post('settings/billing/checkout', [BillingController::class, 'checkout'])->name('settings.billing.checkout');
    Route::post('settings/billing/portal', [BillingController::class, 'portal'])->name('settings.billing.portal');
    Route::get('settings/billing/success', [BillingController::class, 'success'])->name('settings.billing.success');
    Route::get('settings/billing/cancel', [BillingController::class, 'cancel'])->name('settings.billing.cancel');
});
