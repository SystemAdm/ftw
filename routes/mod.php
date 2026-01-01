<?php

use App\http\controllers\mod\OpenController;
use Illuminate\Support\Facades\Route;

Route::name('mod.')->prefix('mod')->middleware(['auth', 'role:Moderator|Admin'])->group(function () {
    Route::get('open', [OpenController::class, 'index'])->name('open.index');
});
