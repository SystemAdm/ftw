<?php

use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PostCodeController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\TeamsController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\WeekdaysController;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Users
    Route::resource('users', UsersController::class)->only(['index', 'show', 'edit', 'update']);
    Route::post('users/{user}/verify', [UsersController::class, 'verify'])->name('users.verify');
    Route::post('users/{user}/ban', [UsersController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [UsersController::class, 'unban'])->name('users.unban');
    Route::get('users/{user}/bans', [UsersController::class, 'bans'])->name('users.bans');

    // Admin Roles & PermissionsController
    Route::resource('roles', RolesController::class);
    Route::get('roles/{role}/users/search', [RolesController::class, 'searchUsers'])->name('roles.users.search');
    Route::post('roles/{role}/users', [RolesController::class, 'assignUser'])->name('roles.users.assign');
    Route::delete('roles/{role}/users/{user}', [RolesController::class, 'removeUser'])->name('roles.users.remove');
    Route::resource('permissions', PermissionsController::class);

    Route::resource('postcodes', PostCodeController::class);
    Route::post('postcodes/{id}/restore', [PostCodeController::class, 'restore'])->name('postcodes.restore');
    Route::delete('postcodes/{id}/force', [PostCodeController::class, 'forceDestroy'])->name('postcodes.force-destroy');

    Route::resource('locations', LocationController::class);
    Route::post('locations/{id}/restore', [LocationController::class, 'restore'])->name('locations.restore');
    Route::delete('locations/{id}/force', [LocationController::class, 'forceDestroy'])->name('locations.force-destroy');

    Route::resource('events', EventController::class);
    Route::post('events/{id}/restore', [EventController::class, 'restore'])->name('events.restore');
    Route::delete('events/{id}/force', [EventController::class, 'forceDestroy'])->name('events.force-destroy');

    // Event attendance management (AJAX JSON)
    Route::post('events/{event}/attend/lookup', [EventController::class, 'attendLookup'])->name('events.attend.lookup');
    Route::post('events/{event}/attend', [EventController::class, 'attend'])->name('events.attend');

    // Event reservation management (AJAX JSON)
    Route::post('events/{event}/reserve/lookup', [EventController::class, 'reserveLookup'])->name('events.reserve.lookup');
    Route::post('events/{event}/reserve', [EventController::class, 'reserve'])->name('events.reserve');

    // Removal endpoints
    Route::delete('events/{event}/attendees/{user}', [EventController::class, 'removeAttendee'])->name('events.attendees.remove');
    Route::delete('events/{event}/reservations/{user}', [EventController::class, 'removeReservation'])->name('events.reservations.remove');
    Route::delete('events/{event}/inside/{user}', [EventController::class, 'removeInside'])->name('events.inside.remove');

    // Copy reservation to attendees
    Route::post('events/{event}/reservations/{user}/attend', [EventController::class, 'copyReservationToAttendee'])->name('events.reservations.copy-to-attendees');
    // Copy attendee to inside
    Route::post('events/{event}/attendees/{user}/inside', [EventController::class, 'copyAttendeeToInside'])->name('events.attendees.copy-to-inside');

    // Force time controls for events
    Route::post('events/{event}/force-signup-begin', [EventController::class, 'forceSignupBegin'])->name('events.force-signup-begin');
    Route::post('events/{event}/force-signup-end', [EventController::class, 'forceSignupEnd'])->name('events.force-signup-end');
    Route::post('events/{event}/force-event-begin', [EventController::class, 'forceEventBegin'])->name('events.force-event-begin');
    Route::post('events/{event}/force-event-end', [EventController::class, 'forceEventEnd'])->name('events.force-event-end');

    Route::get('events/{event}/scanner', [EventController::class, 'scanner'])->name('events.scanner');
    Route::post('events/{event}/scanner', [EventController::class, 'scanner'])->name('events.scanner.post');

    // Event logs
    Route::get('events/{event}/users/{user}/logs', [EventController::class, 'userLogsForEvent'])->name('events.user-logs');

    Route::resource('blogs', BlogController::class);

    // Admin Teams
    Route::resource('teams', TeamsController::class);
    Route::post('teams/{id}/restore', [TeamsController::class, 'restore'])->name('teams.restore');
    Route::delete('teams/{id}/force', [TeamsController::class, 'forceDestroy'])->name('teams.force-destroy');

    // Admin Weekdays
    Route::resource('weekdays', WeekdaysController::class);
    Route::post('weekdays/{weekday}/exclusions', [WeekdaysController::class, 'addExclusion'])->name('weekdays.exclusions.add');
    Route::delete('weekdays/{weekday}/exclusions/{exclusion}', [WeekdaysController::class, 'removeExclusion'])->name('weekdays.exclusions.remove');

    // Admin uploads
    Route::get('uploads/images', [UploadController::class, 'listImages'])->name('uploads.images.index');
    Route::post('uploads/images', [UploadController::class, 'storeImage'])->name('uploads.images');
});
