<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\OpenController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PostCodeController;
use App\Http\Controllers\Admin\RelationController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\TeamsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\WeekdaysController;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->prefix('admin')->middleware(['auth', 'verified', 'team.context', 'role:Admin|Owner'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Admin Relations
    Route::get('relations/search-users', [RelationController::class, 'searchUsers'])->name('relations.search-users');
    Route::post('relations/{guardian}/{minor}/verify', [RelationController::class, 'verify'])->name('relations.verify');
    Route::delete('relations/{guardian}/{minor}', [RelationController::class, 'destroy'])->name('relations.destroy');
    Route::resource('relations', RelationController::class)->except(['destroy', 'show', 'edit', 'update']);

    Route::post('users/{user}/verify', [UsersController::class, 'verify'])->name('users.verify');
    Route::post('users/{user}/reset-password', [UsersController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('users/{user}/ban', [UsersController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [UsersController::class, 'unban'])->name('users.unban');
    Route::post('users/{id}/restore', [UsersController::class, 'restore'])->name('users.restore');
    Route::delete('users/{id}/force', [UsersController::class, 'forceDestroy'])->name('users.force-destroy');
    Route::resource('users', UsersController::class);

    // Admin Roles & PermissionsController
    Route::resource('roles', RolesController::class);
    Route::get('roles/{role}/users/search', [RolesController::class, 'searchUsers'])->name('roles.users.search');
    Route::post('roles/{role}/users', [RolesController::class, 'assignUser'])->name('roles.users.assign');
    Route::delete('roles/{role}/users/{user}', [RolesController::class, 'removeUser'])->name('roles.users.remove');
    Route::resource('permissions', PermissionsController::class);

    // Admin Postcodes
    Route::resource('postcodes', PostCodeController::class);
    Route::post('postcodes/{id}/restore', [PostCodeController::class, 'restore'])->name('postcodes.restore');
    Route::delete('postcodes/{id}/force', [PostCodeController::class, 'forceDestroy'])->name('postcodes.force-destroy');

    // Admin Locations
    Route::resource('locations', LocationController::class);
    Route::post('locations/{id}/restore', [LocationController::class, 'restore'])->name('locations.restore');
    Route::delete('locations/{id}/force', [LocationController::class, 'forceDestroy'])->name('locations.force-destroy');

    // Admin Teams
    Route::post('teams/{team}/members/{user}', [TeamsController::class, 'updateMember'])->name('teams.members.update');
    Route::delete('teams/{team}/members/{user}', [TeamsController::class, 'removeMember'])->name('teams.members.remove');
    Route::resource('teams', TeamsController::class);
    Route::post('teams/{id}/restore', [TeamsController::class, 'restore'])->name('teams.restore');
    Route::delete('teams/{id}/force', [TeamsController::class, 'forceDestroy'])->name('teams.force-destroy');

    // Admin Weekdays
    Route::resource('weekdays', WeekdaysController::class);
    Route::post('weekdays/{weekday}/exclusions', [WeekdaysController::class, 'addExclusion'])->name('weekdays.exclusions.add');
    Route::delete('weekdays/{weekday}/exclusions/{exclusion}', [WeekdaysController::class, 'removeExclusion'])->name('weekdays.exclusions.remove');

    // Admin Events
    Route::get('events/images', [EventsController::class, 'images'])->name('events.images');
    Route::post('events/images', [EventsController::class, 'uploadImage'])->name('events.images.upload');
    Route::resource('events', EventsController::class);
    Route::post('events/{id}/restore', [EventsController::class, 'restore'])->name('events.restore');
    Route::delete('events/{id}/force', [EventsController::class, 'forceDestroy'])->name('events.force-destroy');

    // Admin Open
    Route::get('open', [OpenController::class, 'index'])->name('open.index');
    Route::post('open', [OpenController::class, 'store'])->name('open.store');

});
