<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PostCodeController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\UploadController;

Route::name('admin.')->prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('postcodes', PostCodeController::class);
    Route::post('postcodes/{id}/restore', [PostCodeController::class, 'restore'])->name('postcodes.restore');
    Route::delete('postcodes/{id}/force', [PostCodeController::class, 'forceDestroy'])->name('postcodes.force-destroy');

    Route::resource('locations', LocationController::class);
    Route::post('locations/{id}/restore', [LocationController::class, 'restore'])->name('locations.restore');
    Route::delete('locations/{id}/force', [LocationController::class, 'forceDestroy'])->name('locations.force-destroy');

    Route::resource('events', EventController::class);
    Route::post('events/{id}/restore', [EventController::class, 'restore'])->name('events.restore');
    Route::delete('events/{id}/force', [EventController::class, 'forceDestroy'])->name('events.force-destroy');

    Route::resource('blogs', BlogController::class);

    // Admin uploads
    Route::post('uploads/images', [UploadController::class, 'storeImage'])->name('uploads.images');
});
