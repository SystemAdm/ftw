<?php

use App\Http\Controllers\Mod\OpenController;
use Illuminate\Support\Facades\Route;

Route::name('mod.')->prefix('mod')->middleware(['auth', 'role:Moderator|Admin'])->group(function () {
    Route::get('open', [OpenController::class, 'index'])->name('open.index');
    Route::post('open', [OpenController::class, 'store'])->name('open.store');
    Route::delete('open/{user}', [OpenController::class, 'destroy'])->name('open.destroy');
    Route::get('users/search', [OpenController::class, 'searchUsers'])->name('users.search');
});
