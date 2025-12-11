<?php

use App\Http\Controllers\Auth\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UsersController::class, 'welcome'])->name('home');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
