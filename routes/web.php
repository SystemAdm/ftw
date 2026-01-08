<?php

use App\Http\Controllers\Auth\UsersController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\Pages\ContactController;
use App\Http\Controllers\Pages\LegalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

Route::get('/', [UsersController::class, 'welcome'])->name('home');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/crew.php';
require __DIR__.'/mod.php';

// Stripe Webhook (Cashier) - exclude CSRF so Stripe can POST
Route::post('stripe/webhook', [CashierWebhookController::class, 'handleWebhook'])
    ->withoutMiddleware(VerifyCsrfToken::class)->name('cashier.webhook');

// Manual debug route for testing webhooks (only in local)
if (app()->isLocal()) {
    Route::get('stripe/debug', function () {
        return response()->json([
            'stripe_key_set' => ! empty(config('cashier.key')),
            'stripe_secret_set' => ! empty(config('cashier.secret')),
            'webhook_secret_set' => ! empty(config('cashier.webhook.secret')),
            'app_url' => config('app.url'),
        ]);
    });
}

// Legal pages
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');
Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
Route::get('/cookie', [LegalController::class, 'cookie'])->name('cookie');

// Contact page
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Public Profile
Route::get('/profile/{user?}', [ProfileController::class, 'show'])->name('profile.show');

// Public Events
Route::get('/events', [EventsController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventsController::class, 'show'])->name('events.show');
Route::post('/events/{event}/signup', [EventsController::class, 'signup'])
    ->middleware(['auth', 'verified'])
    ->name('events.signup');
Route::delete('/events/{event}/signup', [EventsController::class, 'cancelSignup'])
    ->middleware(['auth', 'verified'])
    ->name('events.cancelSignup');
