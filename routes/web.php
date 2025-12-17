<?php

use App\Http\Controllers\Auth\UsersController;
use App\Http\Controllers\Pages\ContactController;
use App\Http\Controllers\Pages\LegalController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

Route::get('/', [UsersController::class, 'welcome'])->name('home');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';

// Stripe Webhook (Cashier) - exclude CSRF so Stripe can POST
Route::post('stripe/webhook', [CashierWebhookController::class, 'handleWebhook'])
    ->withoutMiddleware(VerifyCsrfToken::class);

// Legal pages
Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');
Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
Route::get('/cookie', [LegalController::class, 'cookie'])->name('cookie');

// Contact page
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
