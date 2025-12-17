<?php

use App\Mail\ContactMail;
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
    $response = $this->post('/contact', []);
    $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
});

it('sends contact email', function (): void {
    Mail::fake();

    $payload = [
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'subject' => 'Hello',
        'message' => 'This is a test',
    ];

    $this->post('/contact', $payload)
        ->assertRedirect('/contact');

    Mail::assertSent(ContactMail::class);
});
