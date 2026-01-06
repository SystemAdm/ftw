<?php

use App\Http\Controllers\Crew\DashboardController;
use App\Http\Controllers\Crew\EventsController;
use App\Http\Controllers\Crew\TeamMembersController;
use App\Http\Controllers\Crew\TeamsController;
use Illuminate\Support\Facades\Route;

Route::name('crew.')->prefix('crew')->middleware(['auth', 'verified', 'crew', 'team.context'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('events', EventsController::class);

    Route::get('teams', [TeamsController::class, 'index'])->name('teams.index');
    Route::get('teams/{team}', [TeamsController::class, 'show'])->name('teams.show');
    Route::post('teams/{team}/apply', [TeamsController::class, 'apply'])->name('teams.apply');
    Route::delete('teams/{team}/leave', [TeamsController::class, 'leave'])->name('teams.leave');

    Route::post('teams/{team}/members/{user}', [TeamMembersController::class, 'update'])->name('teams.members.update');
    Route::delete('teams/{team}/members/{user}', [TeamMembersController::class, 'destroy'])->name('teams.members.destroy');
});
