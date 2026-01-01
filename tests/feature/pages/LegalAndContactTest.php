<?php

use App\Mail\ContactMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

it('shows legal pages', function (): void {
    $this->get('/privacy')->assertSuccessful();
    $this->get('/terms')->assertSuccessful();
    $this->get('/cookie')->assertSuccessful();
});

it('shows contact page', function (): void {
    $this->get('/contact')->assertSuccessful();
});

it('validates contact form input', function (): void {
    // Ensure captcha is configured so the field is required
    config()->set('services.turnstile.site_key', 'test-site');
    config()->set('services.turnstile.secret_key', 'test-secret');

    $response = $this->post('/contact', []);
    $response->assertSessionHasErrors(['name', 'email', 'subject', 'message', 'cf-turnstile-response']);
});

it('sends contact email', function (): void {
    Mail::fake();
    // Provide Turnstile secret in config for verification
    config()->set('services.turnstile.secret_key', 'test-secret');
    // Fake Turnstile verification
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true], 200),
    ]);

    $payload = [
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'subject' => 'Hello',
        'message' => 'This is a test',
        'cf-turnstile-response' => 'test-token',
    ];

    $this->post('/contact', $payload)
        ->assertRedirect('/contact');

    Mail::assertSent(ContactMail::class);
});

it('flashes error when mail sending fails', function (): void {
    // Configure captcha bypass for testing (non-empty token is accepted)
    config()->set('services.turnstile.secret_key', 'test-secret');

    // Force Mail::send to throw an exception
    Mail::shouldReceive('to')->andReturnSelf();
    Mail::shouldReceive('send')->andThrow(new Exception('SMTP down'));

    $payload = [
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'subject' => 'Hello',
        'message' => 'This is a test',
        'cf-turnstile-response' => 'test-token',
    ];

    $response = $this->from('/contact')->post('/contact', $payload);

    $response->assertRedirect('/contact');
    $response->assertSessionHas('error', trans('ui.contact.email_error'));
});
