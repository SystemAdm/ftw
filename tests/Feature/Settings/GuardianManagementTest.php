<?php

use App\Models\User;
use App\Notifications\Auth\GuardianRelationConfirmation;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

it('can add a guardian and sends notification', function () {
    Notification::fake();

    $user = User::factory()->create();
    $guardian = User::factory()->create();

    actingAs($user)
        ->post(route('settings.guardians.add'), [
            'email' => $guardian->email,
            'relationship' => 'Parent',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->guardians)->toHaveCount(1);
    expect($user->guardians->first()->id)->toBe($guardian->id);
    expect($user->guardians->first()->pivot->relationship)->toBe('Parent');
    expect($user->guardians->first()->pivot->verified_user_at)->not->toBeNull();

    Notification::assertSentTo(
        $guardian,
        GuardianRelationConfirmation::class,
        fn ($notification) => $notification->minor->id === $user->id
    );
});

it('can remove a guardian', function () {
    $user = User::factory()->create();
    $guardian = User::factory()->create();
    $user->guardians()->attach($guardian->id, ['relationship' => 'Parent']);

    actingAs($user)
        ->delete(route('settings.guardians.remove', $guardian))
        ->assertRedirect();

    expect($user->refresh()->guardians)->toHaveCount(0);
});

it('can verify a minor', function () {
    $guardian = User::factory()->create();
    $minor = User::factory()->create();
    $minor->guardians()->attach($guardian->id, [
        'relationship' => 'Child',
        'verified_user_at' => now(),
    ]);

    actingAs($guardian)
        ->post(route('settings.minors.verify', $minor))
        ->assertRedirect();

    $pivot = $guardian->minors()->where('minor_id', $minor->id)->first()->pivot;
    expect($pivot->verified_guardian_at)->not->toBeNull();
    expect((bool) $pivot->confirmed_guardian)->toBeTrue();
});

it('can remove a minor', function () {
    $guardian = User::factory()->create();
    $minor = User::factory()->create();
    $guardian->minors()->attach($minor->id, ['relationship' => 'Child']);

    actingAs($guardian)
        ->delete(route('settings.minors.remove', $minor))
        ->assertRedirect();

    expect($guardian->refresh()->minors)->toHaveCount(0);
});

it('cannot add self as guardian', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('settings.guardians.add'), [
            'email' => $user->email,
            'relationship' => 'Self',
        ])
        ->assertSessionHasErrors('email');

    expect($user->guardians)->toHaveCount(0);
});
