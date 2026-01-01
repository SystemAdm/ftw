<?php

use App\Http\Controllers\Mod\OpenController;
use Illuminate\Support\Facades\Route;

Route::name('mod.')->prefix('mod')->middleware(['auth', 'role:mod|admin'])->group(function () {
    Route::get('open', [OpenController::class, 'index'])->name('open.index');
});
