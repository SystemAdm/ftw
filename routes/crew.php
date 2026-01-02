<?php

use App\Http\Controllers\Crew\EventsController;
use Illuminate\Support\Facades\Route;

Route::name('crew.')->prefix('crew')->middleware(['auth', 'verified', 'role:Crew|Admin'])->group(function () {
    Route::resource('events', EventsController::class)->only(['index', 'show']);
});
