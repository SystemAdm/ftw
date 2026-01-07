<?php

use App\Http\Controllers\Crew\DashboardController;
use App\Http\Controllers\Crew\EventsController;
use App\Http\Controllers\Crew\TeamMembersController;
use App\Http\Controllers\Crew\TeamsController;
use App\Http\Controllers\Crew\WeekdaysController;
use Illuminate\Support\Facades\Route;

Route::name('crew.')->prefix('crew')->middleware(['auth', 'verified', 'crew', 'team.context'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('events', EventsController::class);
    Route::resource('weekdays', WeekdaysController::class);
    Route::post('weekdays/{weekday}/exclusions', [WeekdaysController::class, 'addExclusion'])->name('weekdays.exclusions.add');
    Route::delete('weekdays/{weekday}/exclusions/{exclusion}', [WeekdaysController::class, 'removeExclusion'])->name('weekdays.exclusions.remove');

    Route::get('teams', [TeamsController::class, 'index'])->name('teams.index');
    Route::get('teams/{team}', [TeamsController::class, 'show'])->name('teams.show');
    Route::post('teams/{team}/apply', [TeamsController::class, 'apply'])->name('teams.apply');
    Route::delete('teams/{team}/leave', [TeamsController::class, 'leave'])->name('teams.leave');

    Route::post('teams/{team}/members/{user}', [TeamMembersController::class, 'update'])->name('teams.members.update');
    Route::delete('teams/{team}/members/{user}', [TeamMembersController::class, 'destroy'])->name('teams.members.destroy');
});
