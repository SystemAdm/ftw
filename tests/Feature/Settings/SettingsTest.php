<?php

use App\Models\PostalCode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

it('shows the settings page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/profile');

    $response->assertSuccessful();
});

it('updates appearance', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/appearance', [
        'appearance' => 'dark',
    ]);

    $response->assertRedirect();

    expect($user->fresh()->appearance)->toBe('dark');
});

it('updates profile birthdate and postal code', function (): void {
    $user = User::factory()->create();
    $postal = PostalCode::factory()->create();

    $payload = [
        'birthday' => '2000-01-02',
        'postal_code' => $postal->postal_code,
    ];

    $response = $this->actingAs($user)->patch('/settings/profile', $payload);
    $response->assertRedirect();

    $user->refresh();
    expect($user->birthday?->toDateString())->toBe('2000-01-02');
    expect($user->postal_code)->toBe($postal->postal_code);
});

it('validates postal code exists', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/profile', [
        'birthday' => '2000-01-02',
        'postal_code' => 999999,
    ]);

    $response->assertSessionHasErrors(['postal_code']);
});

it('updates password with current password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user)->patch('/settings/password', [
        'current_password' => 'old-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertRedirect();
    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

it('rejects wrong current password', function (): void {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    $response = $this->actingAs($user)->patch('/settings/password', [
        'current_password' => 'wrong-password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertSessionHasErrors(['current_password']);
});

it('uploads avatar image', function (): void {
    Storage::fake('public');
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('avatar.jpg', 300, 300);

    $response = $this->actingAs($user)->post('/settings/avatar', [
        'avatar' => $file,
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    expect($user->avatar)->toContain('storage/avatars/');

    // Verify file exists on the public disk
    $relative = str_replace('storage/', '', (string) $user->avatar);
    Storage::disk('public')->assertExists($relative);
});
