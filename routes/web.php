<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LocationController as PublicLocationController;
use App\Http\Controllers\BlogController as PublicBlogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\BlogReactionController;
use App\Http\Controllers\EventController as PublicEventController;
use App\Http\Controllers\QrController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Public Locations
Route::resource('locations', PublicLocationController::class)->only(['index', 'show']);

// Public Events
Route::resource('events', PublicEventController::class)->only(['index', 'show']);

// Public Blogs
Route::get('/blog', [PublicBlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blog:slug}', [PublicBlogController::class, 'show'])->name('blog.show');

// Interactions (auth required)
Route::middleware(['auth','verified'])->group(function() {
    Route::post('/blog/{blog:slug}/comments', [CommentController::class, 'store'])->name('blog.comments.store');
    Route::delete('/blog/{blog:slug}/comments/{comment}', [CommentController::class, 'destroy'])->name('blog.comments.destroy');
    Route::post('/blog/{blog:slug}/reactions', [BlogReactionController::class, 'react'])->name('blog.reactions.react');
    Route::delete('/blog/{blog:slug}/reactions', [BlogReactionController::class, 'unreact'])->name('blog.reactions.unreact');

    // Public Event reservations
    Route::post('/events/{event}/reserve', [PublicEventController::class, 'reserve'])->name('events.reserve');
    Route::delete('/events/{event}/reserve', [PublicEventController::class, 'unreserve'])->name('events.unreserve');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// QR code endpoint for authenticated user
Route::get('/qr/user', [QrController::class, 'user'])->middleware(['auth', 'verified'])->name('qr.user');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
